<?PHP
class Claves
{
    public function generar_clave()
    {
        $str = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890#*-|})({@";
        $longitud = 12;
        $clave = '';
        do {
            $clave = '';
            for ($i = 0; $i < $longitud; $i++) {
                $clave .= $str[rand(0, strlen($str) - 1)];
            }
        } while (!preg_match('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[#*-|})({@]).{12,}$/', $clave));
        return $clave;
    }

    public function codificaPwd($pwd)
    {
        return password_hash($pwd, PASSWORD_DEFAULT);
    }

    public function verificaPwd($pwd, $hash)
    {
        // Verifica si el hash tiene el formato Bcrypt
        if (strpos($hash, '$2y$') !== 0 && strpos($hash, '$2a$') !== 0) {
            return false; // Rechaza hashes no compatibles
        }
        return password_verify($pwd, $hash);
    }
}
/*
// Ejemplo de uso para generar y guardar una contraseña
$claves = new Claves();
$clave_plana = $claves->generar_clave(); // Genera algo como "D3sf1gur0s##"
$clave_hash = $claves->codificaPwd($clave_plana); // Hashea la clave
*/
