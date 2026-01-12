<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

$id_archivo=$_POST['id_archivo'];
$presupuesto_id=$_POST['id_presupuesto'];
$estado=$_POST['estado'];
$ruta='../../documentos/';
$con="SELECT * FROM presupuestos_archivos WHERE id=".$id_archivo;
//echo $con; 
$rs=$clsConsulta->consultaGeneral($con);
if($clsConsulta->numrows>0){
    foreach($rs as $v=>$val){
        $aborrar=$val['archivo'];
    }
}
$borrar=$ruta.$aborrar;
//echo $borrar; 

if(file_exists($borrar)){
 //   unlink($borrar);
    $con="DELETE FROM presupuestos_archivos WHERE id=".$id_archivo;
    echo $con.'<br>';
    $clsConsulta->aplicaQuery($con);
}

?>

<div class="modal-body" id="archivos">
    
        <?php
        
        $con="SELECT * FROM presupuestos_archivos WHERE id_presupuesto=".$presupuesto_id;
   // echo $con;
        $rs=$clsConsulta->consultaGeneral($con);
        if($clsConsulta->numrows>0){
            echo '<div class="row p-1 borde-img text-center ">';
            foreach($rs as $v=>$val){
                $archivo=$val['archivo'];
                $valor="'".$archivo."'";
                $explota=explode('.',$archivo);
                foreach($explota as $e){
                    $ext=$e;
                }                
                echo '<div class="col-2 me-1 mt-3">';
            //    echo '<a style="cursor:pointer" data-bs-toggle="modal" data-bs-target="#modalimagen" onclick="pasavalor('.$valor.')">';
             //   echo '<img class="img-thumbnail mx-auto d-block" src="documentos/'.$archivo.'">';
                switch($ext){
                    case 'docx':  /* word */
                    case 'doc':
                        $presenta= '<a href="documentos/'.$archivo.'"><i class="fa fa-file-word-o fa-4x text-primary" aria-hidden="true"></i>
</a><br><small>'.$archivo.'</small>';
                    break;
                    case 'xlsx':
                    case 'xlm':
                        /* Excel */
                        $presenta= '<a href="documentos/'.$archivo.'"><i class="fa fa-file-excel-o fa-4x text-success" aria-hidden="true"></i>
</a><br><small>'.$archivo.'</small>';
                    break;
                    case 'pdf';
                        /* Excel */
                        $presenta= '<a href="documentos/'.$archivo.'"><i class="fa fa-file-pdf-o fa-4x text-danger" aria-hidden="true"></i>
</a><br><small>'.$archivo.'</small>';
                    break;
                    case 'jpge':
                    case 'jpg':
                    case 'png':
                        $presenta= '<a href="documentos/'.$archivo.'"><i class="fa fa-file-image-o fa-4x text-secondary" aria-hidden="true"></i>
</a><br><small>'.$archivo.'</small>';
                    break;
                    case 'txt':
                        $presenta= '<a href="documentos/'.$archivo.'"><i class="fa fa-file-text-o fa-4x text-dark" aria-hidden="true"></i>
</a><br><small>'.$archivo.'</small>';
                    break;                                        
                }
                $pasaborrar=$presupuesto_id.",".$val['id'].",'".$archivo."','".$estado."'";
                echo '<div class="d-inline-block col-2">
                        <div class="card" style="width: 8rem; text-center">
                          '.$presenta.'
                          <div class="card-body text-center"> 
                            <button class="btn btn-default btn-sm" onclick="pasaidborrar('.$pasaborrar.');"><i class="fa fa-times-circle fa-lg text-danger"></i></button>
                          </div>
                        </div>
                    </div>';
                                
             //   echo '<a>';
                echo '</div>';
            }
            
            echo '</div>';
        }
if($estado!='Aprobado'){        
        ?>                
    <div class="dropzone text-center mt-5" id="myId">
        <div class="dz-message text-dark" data-dz-message><span>Arrastra tus archivos aquí <br> o haz click para subir archivos</span></div>
    </div>
<?php
}
?>
</div>