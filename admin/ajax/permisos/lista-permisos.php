<?php
// ajax/permisos/lista-permisos.php
require '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$accion = $_POST['accion'] ?? '';

if ($accion === 'listar_roles') {
    $roles = [];

    $sql = "SELECT id, nombre FROM roles ORDER BY nombre";
    $res = $clsConsulta->consultaGeneral($sql);

    foreach ($res as $r) {
        $roles[] = ['id' => $r['id'], 'nombre' => $r['nombre']];
    }

    echo json_encode(['roles' => $roles]);
    exit;
}

if ($accion === 'listar_permisos') {
    $rol = intval($_POST['rol'] ?? 0);
    $modulos = [];

    $sqlModulos = "
        SELECT
            m1.id,
            m1.nombre,
            m1.archivo,
            m1.icono,
            m1.modulo_padre,
            IF(m1.modulo_padre IS NULL, 'Menú principal', 'Submenú') AS tipo_modulo,
            m2.nombre AS nombre_modulo_padre
        FROM modulos m1
        LEFT JOIN modulos m2 ON m1.modulo_padre = m2.id
        ORDER BY m2.nombre, m1.nombre
    ";

    $resModulos = $clsConsulta->consultaGeneral($sqlModulos);

    if (!is_array($resModulos)) {
        echo json_encode(['modulos' => [], 'error' => 'No se pudieron cargar los módulos']);
        exit;
    }

    // Agrupar módulos por modulo_padre considerando índice base 1
    $modulosPorPadre = [];
    for ($i = 1; $i <= count($resModulos); $i++) {
        $modulo = $resModulos[$i];
        $padre = $modulo['modulo_padre'] === null ? 0 : (int)$modulo['modulo_padre'];
        $modulosPorPadre[$padre][] = $modulo;
    }

    // Ordenar: padres y luego sus hijos
    $modulosOrdenados = [];
    if (isset($modulosPorPadre[0])) {
        foreach ($modulosPorPadre[0] as $moduloPadre) {
            $modulosOrdenados[] = $moduloPadre;
            $idPadre = (int)$moduloPadre['id'];
            if (isset($modulosPorPadre[$idPadre])) {
                foreach ($modulosPorPadre[$idPadre] as $submodulo) {
                    $modulosOrdenados[] = $submodulo;
                }
            }
        }
    }

    // Ahora, para cada módulo, carga los permisos
    foreach ($modulosOrdenados as $modulo) {
        $idModulo = $modulo['id'];

        $sqlPerm = "SELECT puede_ver, puede_crear, puede_editar, puede_eliminar
                    FROM permisos_rol_modulo
                    WHERE rol = $rol AND modulo = '$idModulo'
                    LIMIT 1";

        $resPerm = $clsConsulta->consultaGeneral($sqlPerm);

        $perm = [
            'ver' => 0,
            'crear' => 0,
            'editar' => 0,
            'eliminar' => 0
        ];

        if (is_array($resPerm) && isset($resPerm[1])) {
            $perm = [
                'ver' => (int)($resPerm[1]['puede_ver'] ?? 0),
                'crear' => (int)($resPerm[1]['puede_crear'] ?? 0),
                'editar' => (int)($resPerm[1]['puede_editar'] ?? 0),
                'eliminar' => (int)($resPerm[1]['puede_eliminar'] ?? 0)
            ];
        }

        $modulos[] = [
            'id' => $idModulo,
            'nombre' => $modulo['nombre'],
            'archivo' => $modulo['archivo'],
            'icono' => $modulo['icono'],
            'modulo_padre' => $modulo['modulo_padre'],
            'tipo_modulo' => $modulo['tipo_modulo'],
            'nombre_modulo_padre' => $modulo['nombre_modulo_padre'],
            'permisos' => $perm
        ];
    }

    echo json_encode(['modulos' => $modulos]);
    exit;
}

echo json_encode(['error' => 'Acción no válida']);
