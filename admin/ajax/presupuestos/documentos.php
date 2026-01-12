<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();
include '../../lib/clsFechas.php';
$clsFecha=new Fechas();
//echo __DIR__.'<br>';
$id=$_POST['id'];
$subcat=$_POST['subcat'];
$id=$_POST['id'];
$con="SELECT 
    presupuestos.*
    , presupuestos.id as presupuesto_id
    , cat_clientes.razon_social
    , cat_clientes.siroc
    , cat_clientes.registro_patronal
    , cat_clientes.expediente
    , cat_clientes.presupuesto
FROM
    presupuestos
    INNER JOIN cat_clientes 
        ON (presupuestos.cliente_id = cat_clientes.id)
WHERE presupuestos.id=".$id;
//echo $con; 
$rs=$clsConsulta->consultaGeneral($con);
foreach($rs as $v=>$val){
     $id = $val['id'];
     $presupuesto_id = $val['presupuesto_id'];
     $folio = $val['folio'];
     $razon_social = $val['razon_social'];     
     $cliente_id = $val['cliente_id'];
     $estado = $val['estado'];
}

?>
<style>
    .dropzone{
        border-style:dashed;
        border-color: grey;  
        background-color:whitesmoke;         
        border-radius: 20px;   
        height: 100%;
    }
    .borde-img{
        
        border-color: grey;  
        background-color:whitesmoke;         
        border-radius: 20px;   
        height: 100%;
    }
</style>    

<div class="modal-header">
    <h5 class="modal-title" id="modalArchivos">Documentos del Folio: <b class="text-danger"> <?php echo $folio; ?></b> | <?php echo $razon_social; ?></h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
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
                        $presenta= '<a href="documentos/'.$archivo.'"><i class="fa-solid fa-file-word fa-4x text-primary" aria-hidden="true"></i>
</a><br><small>'.$archivo.'</small>';
                    break;
                    case 'xlsx':
                    case 'xlm':
                        /* Excel */
                        $presenta= '<a href="documentos/'.$archivo.'"><i class="fa-solid fa-file-excel fa-4x text-success" aria-hidden="true"></i>
</a><br><small>'.$archivo.'</small>';
                    break;
                    case 'pdf';
                        /* Excel */
                        $presenta= '<a href="documentos/'.$archivo.'"><i class="fa-solid fa-file-pdf fa-4x text-danger" aria-hidden="true"></i>
</a><br><small>'.$archivo.'</small>';
                    break;
                    case 'jpge':
                    case 'jpg':
                    case 'png':
                        $presenta= '<a href="documentos/'.$archivo.'"><i class="fa-solid fa-file-image fa-4x text-secondary" aria-hidden="true"></i>
</a><br><small>'.$archivo.'</small>';
                    break;
                    case 'txt':
                        $presenta= '<a href="documentos/'.$archivo.'"><i class="fa-solid fa-file-text fa-4x text-dark" aria-hidden="true"></i>
</a><br><small>'.$archivo.'</small>';
                    break;                                        
                }
                
                $pasaborrar=$presupuesto_id.",".$val['id'].",'".$archivo."','".$estado."'";
                echo '<div class="d-inline-block col-2">
                        <div class="card" style="width: 8rem; text-center">
                          '.$presenta;
                          if($estado!='Aprobado'){                          
                echo '
                          <div class="card-body text-center"> 
                            <button class="btn btn-default btn-sm" onclick="pasaidborrar('.$pasaborrar.');"><i class="fa fa-times-circle fa-lg text-danger"></i></button>
                          </div>
                          ';
                          }
                echo ' </div>
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
<div class="modal-footer">
    <input type="hidden" name="archivo" id="archivo1">
    <input type="hidden" name="id_cliente" value="<?php echo $subcat;  ?>">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>            
    <button type="button" id="guardardrop" class="btn btn-primary">Guardar</button>
</div> 


<!-- Moda limg -->
<!--div class="modal fade" id="modalimagen" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Agregar presupuesto</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>         
        <div class="modal-body">
            <div class="row">
                
                <div id="divimagen"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>            
            <!--button type="button" id="guardar" class="btn btn-primary">Guardar</button-->
        <!--/div>      
    </div>
  </div>
</div-->



<script>
     function pasaidborrar(id_presupuesto,id_archivo,archivo,estado){
         
         alertify.confirm('Aviso..!', '¿Deseas Borrar "'+archivo+'" ?', 
            function(){
                //    alertify.success('Ok') 
                $.ajax({
                    url:'ajax/presupuestos/borra-archivos.php',
                    type:'POST',
                    data:{id_archivo:id_archivo, archivo:archivo, id_presupuesto:id_presupuesto,estado:estado},
                    success:function(result){
                        console.log(result)
                        $("#archivos").html(result);                
                    }
                });       
                       /* End ajax */
                     
                    location.reload();
            },
            function(){ alertify.error('Cancel')}
        );
        
    };
        
        
        
        
       
    
    
    function pasavalor(img){
        document.getElementById('divimagen').innerHTML='<img class="img-thumbnail mx-auto d-block" src="'+img+'">';
    }
    
    Dropzone.autoDiscover = false;
    
    $("div#myId").dropzone({ 
     //   url: "ajax/presupuestos/documentos-guardar.php?id=<?php echo $presupuesto_id.'&folio='.$folio.'&razonsocial='.$razon_social; ?>",
        url:"ajax/presupuestos/documentos-subir.php",
        paramName: "file",    
        addRemoveLinks: true,
        dictRemoveFile: '<i class="fa fa-trash text-danger"></i>Borrar.',        
        success: function (file, response) {           
             console.log(response);
            var json = JSON.parse(response);
            var valorInput=$("#archivo1").val();
            var valorTodos=valorInput+'|'+json.archivo;
            $("#archivo1").val(valorTodos);
        }
        /*
        init: function() {
            thisDropzone = this;
            <!-- 4 -->
            $.ajax({
                URL:'ver-archivos.php',
                type: "POST",
                data:{id,<?php // echo $id; ?>}.
                sucess:
                function(result){
                <!-- 5 -->
                $.each(data, function(key,value){

                    var mockFile = { name: value.name, size: value.size };

                    thisDropzone.options.addedfile.call(thisDropzone, mockFile);

                    thisDropzone.options.thumbnail.call(thisDropzone, mockFile, "documentos/"+value.name);

                });

            });
        }
        */     
    });

    
    $("#guardardrop").click(function(){
        alertify.confirm('Aviso..!', '¿Deseas Guardar los archivos?', 
            function(){
                //    alertify.success('Ok') 
                       var archivos=$("#archivo1").val();
                      $.ajax({
                        type: "POST",
                        url: "ajax/presupuestos/documentos-guardar.php",
                        data: {id:"<?php echo $presupuesto_id; ?>", subcat:"<?php echo $subcat; ?>", folio:"<?php echo $folio; ?>",archivos:archivos},
                        success: function(data)
                        {            
                        //   console.log(data);                        
                            
                           var json = JSON.parse(data);
                           if(json.subcat==0){
                               var subcat='';
                           }else{
                                var subcat='/'+json.subcat;    
                           } 
                           
                           if(data==1){
                                console.log("Fallo el server");
                            }else{
                                
                            }
                          //   return true;    
                            location.href = "clientes-presupuestos/todos"+subcat;
                          //  $("#modalArchivos").modal('hidden'); 
                        }
                    }); /* End ajax */
                     
                  //  location.reload();
            },
            function(){ alertify.error('Cancel')}
        );  
               
       
    });

</script>