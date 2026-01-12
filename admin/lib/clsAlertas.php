<?php
class Alertas{
    
    public function mensaje($msg,$tipo){
        $alert= '<div id="mensaje" class="alert alert-'.$tipo.' alert-dismissible col-6">
                  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                  <h5><i class="icon fas fa-check"></i> Aviso!</h5>
                  '.$msg.'
                </div>';
        $scriptjs=$this->scriptjs();
        $mensaje=$alert.' '.$scriptjs;
        return $mensaje;
    }
    public function scriptjs(){
        $script='
            <script>
                $(document).ready(function() {
                    setTimeout(function() {
                        $("#mensaje").fadeOut(500);
                    },3000);   
                });
            </script>';
        return $script;
    }
}
?>

