<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

$id_archivo=$_POST['id_archivo'];
$id_contrato=$_POST['id_contrato'];
$ruta='../../documentos/contratos/';
$con="SELECT archivo FROM contratos_archivos WHERE id=".$id_archivo;

$rs=$clsConsulta->consultaGeneral($con);
if($clsConsulta->numrows>0){
    foreach($rs as $v=>$val){
        $aborrar=$val['archivo'];
    }
}
$borrar=$ruta.$aborrar;


if(file_exists($borrar)){
 //   echo $borrar; 
    unlink($borrar);
    $con="DELETE FROM contratos_archivos WHERE id=".$id_archivo;
//    echo $con.'<br>';
    $clsConsulta->aplicaQuery($con);
}


?>

<div id="displayFiles">                                      
<?php
    $con="SELECT * FROM contratos_archivos WHERE id_contrato=".$id_contrato;
//  echo $con.'<br>';
    $rs=$clsConsulta->consultaGeneral($con);
    if($clsConsulta->numrows>0){
        foreach($rs as $v=>$val){  
            $id_archivo=$val['id'];
            $archivo=$val['archivo'];
            $pasafile="'documentos/contratos/'".$archivo."'";
            $valor="'".$archivo."'";
            $estado=$val['estatus'];
            $explota=explode('.',$archivo);
            foreach($explota as $e){
                $ext=$e;
            }
            switch($ext){
                case 'docx':  /* word */
                case 'doc':
                    $presenta= '<td><a title="'.$archivo.'" href="documentos/contratos/'.$archivo.'"  target="_blank"><i class="fa fa-file-word-o fa-2x text-primary" aria-hidden="true"></i>
</a></td><td width="80%" class="text-center"> &nbsp;<small> '.$archivo.'</small></td>';
                break;
                case 'xlsx':  /* Excel */
                case 'xlm':   
                    $presenta= '<td><a title="'.$archivo.'" href="documentos/contratos/'.$archivo.'"  target="_blank"><i class="fa fa-file-excel-o fa-2x text-success" aria-hidden="true"></i>
</a></td><td width="80%" class="text-center"> &nbsp;<small> '.$archivo.'</small></td>';                                                                       
                break;
                case 'pdf'; /* pdf */
                $presenta= '<td><a title="'.$archivo.'" href="documentos/contratos/'.$archivo.'"  target="_blank"><i class="fa fa-file-pdf-o fa-2x text-danger" aria-hidden="true"></i>
                </a></td><td width="80%" class="text-center"> &nbsp;<small> '.$archivo.'</small></td>';                                                               
                break;
                case 'jpge': /* Img */
                case 'jpg':
                case 'png':                                                        
                    $presenta= '<td><a title="'.$archivo.'" href="documentos/contratos/'.$archivo.'"  target="_blank"><i class="fa fa-file-image-o fa-2x text-secondary" aria-hidden="true"></i>
</a></td><td width="80%" class="text-center"> &nbsp;<small> '.$archivo.'</small></td>';  
                break;
                case 'txt': /* txt */
                    $presenta= '<td><a title="'.$archivo.'" href="documentos/contratos/'.$archivo.'"  target="_blank"><i class="fa fa-file-text-o fa-2x text-dark" aria-hidden="true"></i>
</a></td><td width="80%" class="text-center"> &nbsp;<small> '.$archivo.'</small></td>';                                          
                break;

            }              
//      $pasaborrar=$cat.",".$.",'".$archivo."','".$estado."'";
        echo '<div>
                    <table>
                    <tr>
                '.$presenta.'
                    <td>
                    <a class="btn btn-danger btn-sm" onclick="pasaidborrar('.$id_contrato.','.$id_archivo.');"><i class="fa fa-trash fa-lg"></i></a>
                    </td>
                    </tr>
                </table>
                
            </div>';
        }
    }
    
            
        //    echo '<div class="col-4" ><img src="archivos-siroc/'.$file.'" alt="'.$file.'" class="img-thumbnail"></div>';   

?>
</div>