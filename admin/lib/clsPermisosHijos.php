<?php
// lib/clsPermisoshijos.php
// Requiere que en $_SESSION existan: modulos, permisos, submodulos (opcional)

class permisosHijos
{
    /**
     * Detecta el ID del módulo activo a partir del nombre de archivo (ruta base),
     * comparando contra $_SESSION['modulos'][...]['archivo'].
     */
    public static function detectarModuloActivoPorArchivo(string $archivoActual): ?int
    {
        if (empty($_SESSION['modulos'])) return null;
        foreach ($_SESSION['modulos'] as $id => $mod) {
            if (!empty($mod['archivo']) && $mod['archivo'] === $archivoActual) {
                return (int)$id;
            }
        }
        return null;
    }

    /**
     * Devuelve el ID del padre: si $id es hijo, regresa su padre; si ya es padre o null, regresa igual.
     */
    public static function obtenerPadre(?int $id): ?int
    {
        if ($id === null) return null;
        $mod = $_SESSION['modulos'][$id] ?? null;
        if (!$mod) return null;
        $padre = (int)($mod['modulo_padre'] ?? 0);
        return $padre > 0 ? $padre : $id;
    }

    /**
     * ¿El rol puede ver este módulo?
     */
    public static function puedeVer(int $id): bool
    {
        return !empty($_SESSION['permisos'][$id]['ver']);
    }

    /**
     * Regresa los IDs de hijos visibles del padre dado, respetando permisos.
     * Intenta leer primero $_SESSION['submodulos'] (si la cargaste al login),
     * y si no existe, construye el arreglo en caliente.
     */
    public static function obtenerHijosVisibles(int $idPadre): array
    {
        $hijos = [];

        if (!empty($_SESSION['submodulos'][$idPadre])) {
            foreach ($_SESSION['submodulos'][$idPadre] as $idHijo) {
                if (self::puedeVer((int)$idHijo)) $hijos[] = (int)$idHijo;
            }
            return $hijos;
        }

        // Fallback si no llenaste $_SESSION['submodulos']
        foreach (($_SESSION['modulos'] ?? []) as $id => $mod) {
            if (!empty($mod['modulo_padre']) && (int)$mod['modulo_padre'] === (int)$idPadre) {
                if (self::puedeVer((int)$id)) $hijos[] = (int)$id;
            }
        }
        return $hijos;
    }

    /**
     * Azúcar sintáctica: ¿tiene hijos visibles?
     */
    public static function tieneHijosVisibles(int $idPadre): bool
    {
        return count(self::obtenerHijosVisibles($idPadre)) > 0;
    }
}
