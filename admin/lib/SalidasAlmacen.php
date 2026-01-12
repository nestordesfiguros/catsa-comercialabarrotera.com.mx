<?php
// lib/SalidasAlmacen.php

class SalidasAlmacen
{
    private $db;

    public function __construct($clsConsulta)
    {
        $this->db = $clsConsulta;
    }

    private function firstRow($rs)
    {
        if (!is_array($rs)) return null;
        if (isset($rs[1]) && is_array($rs[1])) return $rs[1];
        if (isset($rs[0]) && is_array($rs[0])) return $rs[0];
        $first = reset($rs);
        return is_array($first) ? $first : null;
    }

    private function tableExists(string $name): bool
    {
        $name = addslashes($name);
        $rs = $this->db->consultaGeneral("SHOW TABLES LIKE '{$name}'");
        return ($this->db->numrows > 0);
    }

    private function getSalidaByRemision(int $idEmpresa, int $idRemision): int
    {
        // Busca en salida_remisiones
        if ($this->tableExists('salida_remisiones')) {
            $rs = $this->db->consultaGeneral("
                SELECT s.id
                FROM cab_salidas_almacen s
                INNER JOIN salida_remisiones r ON r.id_salida = s.id
                WHERE s.id_empresa = {$idEmpresa}
                  AND r.id_remision = {$idRemision}
                LIMIT 1
            ");
            if ($this->db->numrows > 0) {
                $row = $this->firstRow($rs);
                return (int)($row['id'] ?? 0);
            }
        }

        // Busca en rel_salidas_remisiones
        if ($this->tableExists('rel_salidas_remisiones')) {
            $rs = $this->db->consultaGeneral("
                SELECT s.id
                FROM cab_salidas_almacen s
                INNER JOIN rel_salidas_remisiones r ON r.id_salida = s.id
                WHERE s.id_empresa = {$idEmpresa}
                  AND r.id_remision = {$idRemision}
                LIMIT 1
            ");
            if ($this->db->numrows > 0) {
                $row = $this->firstRow($rs);
                return (int)($row['id'] ?? 0);
            }
        }

        return 0;
    }

    private function linkSalidaRemision(int $idSalida, int $idRemision): void
    {
        // Inserta en la(s) tabla(s) de relación disponibles
        if ($this->tableExists('salida_remisiones')) {
            $this->db->aplicaQuery("
                INSERT INTO salida_remisiones (id_salida, id_remision)
                VALUES ({$idSalida}, {$idRemision})
            ");
        }
        if ($this->tableExists('rel_salidas_remisiones')) {
            // PK compuesta, no permite duplicados
            $this->db->aplicaQuery("
                INSERT INTO rel_salidas_remisiones (id_salida, id_remision)
                VALUES ({$idSalida}, {$idRemision})
            ");
        }
    }

    private function inventarioDisponible(int $idEmpresa, int $idAlmacen, int $idProducto): float
    {
        $rs = $this->db->consultaGeneral("
            SELECT cantidad
            FROM inventarios
            WHERE id_empresa = {$idEmpresa}
              AND id_almacen = {$idAlmacen}
              AND id_producto = {$idProducto}
            LIMIT 1
        ");
        if ($this->db->numrows < 1) return 0.0;
        $row = $this->firstRow($rs);
        return (float)($row['cantidad'] ?? 0);
    }

    private function updateInventario(int $idEmpresa, int $idAlmacen, int $idProducto, float $nuevo): void
    {
        $nuevo = (float)$nuevo;

        // Si existe registro, update; si no existe, insert
        $rs = $this->db->consultaGeneral("
            SELECT id
            FROM inventarios
            WHERE id_empresa = {$idEmpresa}
              AND id_almacen = {$idAlmacen}
              AND id_producto = {$idProducto}
            LIMIT 1
        ");

        if ($this->db->numrows > 0) {
            $row = $this->firstRow($rs);
            $idInv = (int)($row['id'] ?? 0);
            if ($idInv > 0) {
                $this->db->aplicaQuery("
                    UPDATE inventarios
                    SET cantidad = {$nuevo}
                    WHERE id = {$idInv}
                      AND id_empresa = {$idEmpresa}
                ");
            }
        } else {
            $this->db->aplicaQuery("
                INSERT INTO inventarios (id_empresa, id_almacen, id_producto, cantidad)
                VALUES ({$idEmpresa}, {$idAlmacen}, {$idProducto}, {$nuevo})
            ");
        }
    }

    private function bitacora(int $idEmpresa, int $idProducto, int $idAlmacen, float $cantidad, string $tipo, string $referencia, int $idUsuario): void
    {
        $tipo = addslashes($tipo);
        $referencia = addslashes($referencia);

        $this->db->aplicaQuery("
            INSERT INTO inventario_bitacora (id_empresa, id_producto, id_almacen, cantidad, tipo_movimiento, referencia, id_usuario)
            VALUES ({$idEmpresa}, {$idProducto}, {$idAlmacen}, {$cantidad}, '{$tipo}', '{$referencia}', {$idUsuario})
        ");
    }

    /**
     * Crea salida desde remisión y (por defecto) la deja PROCESADA (descuenta inventario).
     * - Multiempresa: valida id_empresa
     * - Multialmacén: usa cab_remisiones.id_almacen
     */
    public function crearDesdeRemision(int $idEmpresa, int $idUsuario, int $idRemision, bool $procesar = true): array
    {
        if ($idEmpresa <= 0 || $idUsuario <= 0 || $idRemision <= 0) {
            return ['ok' => false, 'msg' => 'Parámetros inválidos.'];
        }

        // Ya existe?
        $idSalidaExiste = $this->getSalidaByRemision($idEmpresa, $idRemision);
        if ($idSalidaExiste > 0) {
            return ['ok' => true, 'msg' => 'Ya existe salida para esta remisión.', 'id_salida' => $idSalidaExiste];
        }

        // Cabecera remisión
        $rsCab = $this->db->consultaGeneral("
            SELECT *
            FROM cab_remisiones
            WHERE id = {$idRemision}
              AND id_empresa = {$idEmpresa}
            LIMIT 1
        ");
        if ($this->db->numrows < 1) return ['ok' => false, 'msg' => 'Remisión no encontrada en esta empresa.'];

        $cab = $this->firstRow($rsCab);

        $idAlmacen = (int)($cab['id_almacen'] ?? 0);
        $idCliente = (int)($cab['id_cliente'] ?? 0);
        $idVendedor = (int)($cab['id_vendedor'] ?? 0);
        $fecha = (string)($cab['fecha'] ?? date('Y-m-d H:i:s'));
        $fechaSalida = date('Y-m-d', strtotime($fecha));

        if ($idAlmacen <= 0) return ['ok' => false, 'msg' => 'La remisión no tiene almacén asignado.'];

        // Validar almacén pertenece a empresa y activo
        $rsAlm = $this->db->consultaGeneral("
            SELECT id
            FROM cat_almacenes
            WHERE id = {$idAlmacen}
              AND id_empresa = {$idEmpresa}
              AND estatus = 1
            LIMIT 1
        ");
        if ($this->db->numrows < 1) return ['ok' => false, 'msg' => 'Almacén inválido o no pertenece a la empresa.'];

        // Detalle remisión
        $rsDet = $this->db->consultaGeneral("
            SELECT id_producto, cantidad, precio_unitario
            FROM mov_remisiones
            WHERE id_remision = {$idRemision}
        ");
        if ($this->db->numrows < 1 || !is_array($rsDet)) return ['ok' => false, 'msg' => 'La remisión no tiene productos.'];

        $items = [];
        foreach ($rsDet as $r) {
            $pid = (int)($r['id_producto'] ?? 0);
            $qty = (float)($r['cantidad'] ?? 0);
            $pu  = (float)($r['precio_unitario'] ?? 0);
            if ($pid > 0 && $qty > 0) $items[] = ['id_producto' => $pid, 'cantidad' => $qty, 'precio_unitario' => $pu];
        }
        if (count($items) < 1) return ['ok' => false, 'msg' => 'Detalle inválido.'];

        // Validar stock si se va a procesar
        if ($procesar) {
            foreach ($items as $it) {
                $exist = $this->inventarioDisponible($idEmpresa, $idAlmacen, (int)$it['id_producto']);
                if ((float)$it['cantidad'] > $exist) {
                    return [
                        'ok' => false,
                        'msg' => 'Stock insuficiente en almacén. Producto ' . (int)$it['id_producto'] . " (existencia: {$exist})"
                    ];
                }
            }
        }

        // Crear salida
        $estatus = $procesar ? 'procesada' : 'pendiente';
        $ref = "REM-" . $idRemision;

        $obs = addslashes("Salida generada automáticamente desde remisión #{$idRemision}");

        $sqlCabSalida = "
            INSERT INTO cab_salidas_almacen
            (id_empresa, id_almacen, id_cliente, destino_libre, fecha, id_usuario, estatus, tipo_salida, referencia, observaciones)
            VALUES
            ({$idEmpresa}, {$idAlmacen}, " . ($idCliente > 0 ? $idCliente : "NULL") . ", NULL, '{$fechaSalida}', {$idUsuario}, '{$estatus}', 'venta', '{$ref}', '{$obs}')
        ";

        $ok = $this->db->guardarGeneral($sqlCabSalida);
        $idSalida = (int)$this->db->ultimoid;

        if (!$ok || $idSalida <= 0) {
            return ['ok' => false, 'msg' => 'No se pudo crear la salida.'];
        }

        // Link salida-remisión
        $this->linkSalidaRemision($idSalida, $idRemision);

        // Insert movimientos + afectar inventario si procesada
        foreach ($items as $it) {
            $pid = (int)$it['id_producto'];
            $qty = (float)$it['cantidad'];
            $pu  = (float)$it['precio_unitario'];

            // Inserta movimiento (si agregaste precio_unitario en mov_salidas_almacen, lo guardamos; si no, solo cantidad)
            $hasPrecioCol = false;
            $rsCol = $this->db->consultaGeneral("SHOW COLUMNS FROM mov_salidas_almacen LIKE 'precio_unitario'");
            if ($this->db->numrows > 0) $hasPrecioCol = true;

            if ($hasPrecioCol) {
                $this->db->aplicaQuery("
                    INSERT INTO mov_salidas_almacen (id_salida, id_producto, cantidad, precio_unitario)
                    VALUES ({$idSalida}, {$pid}, {$qty}, {$pu})
                ");
            } else {
                $this->db->aplicaQuery("
                    INSERT INTO mov_salidas_almacen (id_salida, id_producto, cantidad)
                    VALUES ({$idSalida}, {$pid}, {$qty})
                ");
            }

            if ($procesar) {
                $exist = $this->inventarioDisponible($idEmpresa, $idAlmacen, $pid);
                $nuevo = $exist - $qty;
                if ($nuevo < 0) $nuevo = 0;

                $this->updateInventario($idEmpresa, $idAlmacen, $pid, $nuevo);
                $this->bitacora($idEmpresa, $pid, $idAlmacen, $qty, 'SALIDA', $ref, $idUsuario);

                // Compatibilidad: si sigues usando cantidad_disponible global en cat_productos (no por almacén)
                $rsProd = $this->db->consultaGeneral("
                    SELECT cantidad_disponible
                    FROM cat_productos
                    WHERE id_producto = {$pid} AND (id_empresa IS NULL OR id_empresa = {$idEmpresa})
                    LIMIT 1
                ");
                if ($this->db->numrows > 0) {
                    $rowp = $this->firstRow($rsProd);
                    $disp = (float)($rowp['cantidad_disponible'] ?? 0);
                    $dispNueva = $disp - $qty;
                    if ($dispNueva < 0) $dispNueva = 0;
                    $this->db->aplicaQuery("
                        UPDATE cat_productos
                        SET cantidad_disponible = {$dispNueva}
                        WHERE id_producto = {$pid}
                    ");
                }
            }
        }

        // Mantener remisión en procesada si procesamos la salida
        if ($procesar) {
            $this->db->aplicaQuery("
                UPDATE cab_remisiones
                SET estatus = 'procesada'
                WHERE id = {$idRemision}
                  AND id_empresa = {$idEmpresa}
            ");
        }

        return ['ok' => true, 'msg' => 'Salida creada desde remisión.', 'id_salida' => $idSalida];
    }
}
