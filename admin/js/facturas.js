

    function ModalcambiaEstatus(idFactura){
        $("#modalEstatusFacturas").modal('show');
        $("#idfactura").val(idFactura);       
        /*
        $('#modalEstatusFacturas').modal('toggle');
        $('#modalAltaFacturas').modal('show');
        $('#modalAltaFacturas').modal('hide');
        */
    };

    $("#cerrarModificarFacturasX").click(
            function(){
                $('#modalEstatusFacturas').modal('toggle');
            }
        );
    $("#cerrarModificarFacturas").click(
        function(){
            $('#modalEstatusFacturas').modal('toggle');
        }
    );

    
/*
    function buscaCliente(){
        $.ajax({                    
            url: "ajax/facturas/busca-cliente.php",
            type: "POST",
            data: {folio:
            function(){
                return $("#fact_folio").val();
            }},
            success: function(data)
            {              
             //   console.log(data);
                if(data==1){
                    console.log("Fallo el server");
                }else{
                //    console.log(data);
                    var json = JSON.parse(data);                    
                 //   console.log(json);
                    if(json.porcentaje_retencion!='"0"' || json.porcentaje_retencion!=0 ){ 
                        let porcientoString=parseInt(json.porcentaje_retencion);
                        $("#porcentajeRetencion").html('Retención fondo de garantia '+porcientoString+'%');
                    }
                  //  let importe_contratado=json.importe_contratado;                    
                    var monto_anticipo = numeral(json.monto_anticipo).format('$000,000,000.00');
                    var importe_contratado = numeral(json.importe_contratado).format('$000,000,000.00');
                    let razon_social=json.razon_social;
                    let porcentaje_retencion=parseInt(json.porcentaje_retencion);                                          

                    $("#fact_cliente").val(razon_social); 

                    /* calcula la retencion */     
                    /*                
                    $("#montoContrato").html('<div class="text-end">Monto Contratado: '+importe_contratado+'</div><div class="text-end"> Monto Anticipo: '+monto_anticipo+'</div><div class="text-end"> Porcentaje de Retención: '+porcentaje_retencion+'%</div>');
                                                    
                   if($("#fact_monto").val()!=0){
                        let monto=$("#fact_monto").val();
                        monto=monto.replace(/,/g,'');
                        porcentaje_retencion=porcentaje_retencion/100;
                        var x = monto; // Valor conocido después de restar el 3%
                        // Fórmula para encontrar el valor original (y) en jQuery
                        var retencion = (x / (1 - porcentaje_retencion))-monto;

                        //       console.log("Valor original (porcentaje_retencion): " + porcentaje_retencion);
                        $("#fact_retencion").val(retencion);                        
                        var retencion = numeral($("#fact_retencion").val()).format('000,000,000.00');
                        $("#fact_retencion").val(retencion);
                        $("#fact_retencion").addClass('active is-valid'); 
                   }
                }
            }
        }); /* End ajax */
/*
    }; */

    function PorcentajeRetencion(folio){
    //    console.log('-> '+folio);

        $.ajax({                    
            url: "ajax/facturas/obtiene-porcentaje-retencion.php",
            type: "POST",
            data: {folio:folio},
            success: function(data)
            {              
            //    console.log(data);
                if(data==1){
                    console.log("Fallo el server");
                }else{
                    /* calcula la retencion */
                 //  let porcientoString=parseInt(data);
                   /*
                   if (isNaN(retencion)) {
                        porcientoString='';
                   }else{
                    */
                        $("#porcentajeRetencion").html('Retención '+data+'%'); 
                  // }
                   
                   let monto=$("#fact_monto").val();                    
                   let porciento=parseFloat(data)/100;
                   monto=monto.replace(/[$,]/g,'');                    
               //    console.log('Monto: '+monto); 
                   if(monto!=0){
                        var x = monto; // Valor conocido después de restar el 3%
                        // Fórmula para encontrar el valor original (y) en jQuery                        
                        var retencion = (x / (1 - porciento))-monto;
                    //    console.log("Valor original (y): " + retencion);
                        if(porciento!=0){
                            $("#fact_retencion").val(retencion);
                            $("#fact_retencion").addClass('active is-valid'); 
                            var retencion = numeral($("#fact_retencion").val()).format('$000,000,000.00');
                            $("#fact_retencion").val(retencion);
                        }else{
                            $("#fact_retencion").val(0);
                        }
                        
                   }                                   
                   
                }
            }
        }); /* End ajax */
    };
    function guardaFactura(){
        $.ajax({                    
            url: "ajax/facturas/alta-factura.php",
            type: "POST",
            data: {id_cliente:id_cliente},
            success: function(data)
            {              
            //    console.log(data);
                if(data==1){
                    console.log("Fallo el server");
                }else{
                    // Recarga la tabla 
                    var table = $('#tablaFacturas').DataTable();
                    table.ajax.reload( function ( json ) {
                        $('#tablaFacturas').val( json.lastInput );
                    } ); 

                    

                //     alertify.alert('Valor modificado').set('labels', {ok:'Cerrar'}).setHeader('Aviso'); 
                }
            }
        }); /* End ajax */
    };

    function functOrdenRazonSocial(orden){
        $.ajax({                    
            url: "ajax/facturas/ordena-tabla-x-cliente.php",
            type: "POST",
            data: {orden:orden},
            success: function(data)
            {              
             //   console.log(data);
                if(data==1){
                    console.log("Fallo el server");
                }else{
                 //   console.log(data);
                    var json = JSON.parse(data);                    
                 //   console.log(json);
                        // Recarga la tabla 
                    var table = $('#tablaFacturas').DataTable();
                    table.ajax.reload( function ( json ) {
                        $('#tablaFacturas').val( json.lastInput );
                    } );                 
                }
            }
        }); /* End ajax */
        
    };

    function fechaActual(){
        let date = new Date()

        let day = date.getDate()
        let month = date.getMonth() + 1
        let year = date.getFullYear()

        if(month < 10){
        fecha=day+'-0'+month+'-'+year;
        }else{
        fecha=day+'-'+month+'-'+year;
        }
        return fecha;
    };

    function functOrdenTabla(orden){
        $.ajax({                    
            url: "ajax/facturas/guarda-cockie.php",
            type: "POST",
            data: {orden:orden},
            success: function(data)
            {              
             ///   console.log(data);
                if(data==1){
                    console.log("Fallo el server");
                }else{
                 //   console.log(data);
                    var json = JSON.parse(data);                    
                 //   console.log(json);
                    if(json.orden===''){      
                        $("#estatuslabel").html('TODOS');
                        /*
                        $("#todos").removeAttr('class')
                        $("#todos").attr("class", "btn btn-outline-primary me-3 mt-3");
                        $("#activos").removeAttr('class')
                        $("#activos").attr("class", "btn btn-outline-secondary me-3 mt-3");
                        $("#inactivos").removeAttr('class')
                        $("#inactivos").attr("class", "btn btn-outline-secondary me-3 mt-3");
                        */
                    }
                    if(json.orden==='1' || json.orden==="'1'"){
                        $("#estatuslabel").html('COBRADAS');
                        /*
                        $("#todos").removeAttr('class')
                        $("#todos").attr("class", "btn btn-outline-secondary me-3 mt-3");
                        $("#activos").removeAttr('class')
                        $("#activos").attr("class", "btn btn-outline-primary me-3 mt-3");
                        $("#inactivos").removeAttr('class')
                        $("#inactivos").attr("class", "btn btn-outline-secondary me-3 mt-3");
                        */
                    }
                    if(json.orden==='0' || json.orden==="'0'"){
                        $("#estatuslabel").html('PENDIENTES');
                        /*
                        $("#todos").removeAttr('class')
                        $("#todos").attr("class", "btn btn-outline-secondary me-3 mt-3");
                        $("#activos").removeAttr('class')
                        $("#activos").attr("class", "btn btn-outline-secondary me-3 mt-3");
                        $("#inactivos").removeAttr('class')
                        $("#inactivos").attr("class", "btn btn-outline-primary   me-3 mt-3");
                        */
                    }
                    if(json.orden==='2' || json.orden==="'2'"){
                        $("#estatuslabel").html('CANCELADAS');
                        /*
                        $("#todos").removeAttr('class')
                        $("#todos").attr("class", "btn btn-outline-secondary me-3 mt-3");
                        $("#activos").removeAttr('class')
                        $("#activos").attr("class", "btn btn-outline-secondary me-3 mt-3");
                        $("#inactivos").removeAttr('class')
                        $("#inactivos").attr("class", "btn btn-outline-primary   me-3 mt-3");
                        */
                    }
                    
                    // Recarga la tabla 
                    var table = $('#tablaFacturas').DataTable();
                    table.ajax.reload( function ( json ) {
                        $('#tablaFacturas').val( json.lastInput );
                    } ); 

                //     alertify.alert('Valor modificado').set('labels', {ok:'Cerrar'}).setHeader('Aviso'); 
                }
            }
        }); /* End ajax */
        
    };

    // Función para borrar los archivos de Dropzone
    function borrarArchivosDropzoneFacturas() {
        // Obtener el elemento Dropzone
        var dropzoneElement = $("div#myId.dropzone")[0].dropzone;

        // Obtener los archivos subidos en Dropzone
        var archivosSubidos = dropzoneElement.getAcceptedFiles();

        // Eliminar cada archivo subido
        for (var i = 0; i < archivosSubidos.length; i++) {
            dropzoneElement.removeFile(archivosSubidos[i]);
        }            
    }

    function leerXML(){
        $.ajax({                    
            url: "ajax/facturas/leeerXML.php",
            type: "POST",
            data: {archivo:
                function(){
                    return $("#archivo").val(); 
                }
            },
            success: function(data)
            {              
            //    console.log(data);
                if(data==1){
                    console.log("Fallo el server");
                }else{             
                    var json = JSON.parse(data);
                    /* Valida que el xml sea un complemento de pago  */
                    
                    if(json.contenido=='noValido' || json.contenido=="'noValido'"){                                                                                        
                        alertify.alert('Aviso', 'El archivo .xml no es una factura valida', function(){ alertify.success('Aceptar'); }); 
                        $("#archivo").val(''); 
                        $('#folioCaptura').val();
                        // Eliminar el archivo .xml de Dropzone
                    //    borrarArchivosDropzoneFacturas();
                        // Limpiar el contenido del contenedor de Dropzone
                        var dropzoneElement = $("div#myId");
                        dropzoneElement.empty(); // Borra el contenido HTML del elemento
                        $("#myId").append('<div class="dz-message text-dark" data-dz-message><span>Arrastra tus archivos aquí <br> o haz click para subir archivos</span></div>');
                    }
                    /* Valida que el cliente este capturado o activo */
                    
                    if(json.contenido=='noCliente' || json.contenido=="'noCliente'"){
                        alertify.alert('Aviso', 'El archivo .xml no pertenece a un cliente capturado o activo', function(){ alertify.success('Aceptar'); }); 
                        $("#archivo").val(''); 
                        $('#folioCaptura').val();
                        // Eliminar el archivo .xml de Dropzone
                    //    borrarArchivosDropzoneFacturas();
                        // Limpiar el contenido del contenedor de Dropzone
                        var dropzoneElement = $("div#myId");
                        dropzoneElement.empty(); // Borra el contenido HTML del elemento
                        $("#myId").append('<div class="dz-message text-dark" data-dz-message><span>Arrastra tus archivos aquí <br> o haz click para subir archivos</span></div>');
                    }
                    /*
                    var valorInput=$("#archivo").val();
                    var valorTodos=valorInput+'|'+json.archivo;

                    $("#archivo").val(valorTodos);     
                    */               
                  //     console.log(json);
                    if(json.contenido==='Valido'){
                        var y=json.anio;
                        var m=json.mes;
                        var d=json.dia;
                        var newfecha = `${y}-${m}-${d}`;
    
                        $("#fact_fecha").val(newfecha); 
                        $("#fact_fecha").addClass('active is-valid'); 
                        $("#fact_fecha").removeAttr("style");
                        $("#fact_fecha").attr("style","opacity: 1;");
                        $("#fact_serie").val(json.serie);
                        $("#fact_serie").addClass('active is-valid'); 
                        $("#fact_no").val(json.folio);
                        $("#fact_no").addClass('active is-valid'); 
                        $("#fact_cliente").val(json.razon_social); 
                        $("#fact_cliente").addClass('active is-valid');                         
                        $("#fact_monto").addClass('active is-valid'); 
                        var monto = numeral(json.total).format('$000,000,000.00');
                        $("#fact_monto").val(monto);
                        $("#fact_descripcion").val(json.descripcion); 
                        $("#fact_descripcion").addClass('active is-valid');                        

                     //   PorcentajeRetencion(json.total,json.folio);
                        $("#SiguienteFacturas1").removeClass('disabled');

                        $("#archivos").val($("#archivo").val());

                        // json.listaFolios
                     //   console.log(json.listafolios);
                     //   $("#listaFolios").val(json.listafolios);
                        /* Lista de folios  */
                        $.ajax({                    
                            url: "ajax/facturas/autocompletelist.php",
                            type: "POST",
                            data: {idCliente:json.idCliente
                            },
                            success: function(data)
                            {              
                           //     console.log(data);
                                if(data==1){
                                    console.log("Fallo el server");
                                }else{                                               
                                    $("#listaFolios").html(data);
                                }
                            }
                        }); /* End ajax */                   
                    }
                    if(json.contenido==='noCliente'){
                        alertify.alert('El cliente no existe, por favor seleccione otro archivo').set('labels', {ok:'Cerrar'}).setHeader('Aviso');                         
                    }
                                    

                //     alertify.alert('Valor modificado').set('labels', {ok:'Cerrar'}).setHeader('Aviso'); 
                }
            }
        }); /* End ajax */
    }
    

    Dropzone.autoDiscover = false;
    
    $("div#myId").dropzone({ 
        url: "ajax/facturas/upload-files-dropzon.php",
        paramName: "file",        
        addRemoveLinks: true,   
        createImageThumbnails: true,
        acceptedFiles: ".pdf, .xml",
        dictRemoveFile: '<i class="fa fa-trash text-danger"></i>Borrar.',   
        init: function() {
            var xmlFileAdded = false;
            /* Valida que solo sean 2 archivos y que por lo menos uno sea xml*/
    
            this.on("addedfile", function(file) {
                if (this.files.length > 2) {
                    // Mostrar aviso cuando se intenten agregar más de 2 archivos
                    alertify.alert('Aviso', 'No puedes agregar más de 2 archivos.', function(){ alertify.success('Aceptar'); }); 
                    $("#archivo").val(''); 
                    $('#folioCaptura').val();
                    borrarArchivosDropzone();
                    //this.removeFile(file);
                    return;
                }
    
                if (file.name.toLowerCase().endsWith(".xml")) {
                    xmlFileAdded = true;
                }
            });
    
            this.on("processing", function() {
              //  console.log($("#estatusFactura").val());
                if (!xmlFileAdded) {
                    // Mostrar aviso cuando no se agrega ningún archivo .xml
                    alertify.alert('Aviso', 'Debes agregar al menos un archivo .xml.', function(){ alertify.success('Aceptar'); }); 
                    borrarArchivosDropzone();
                   // this.removeAllFiles();                      
                    return;
                }
            });
        },
        success: 
            function (file, response) {           
            //    console.log(response);
                var json = JSON.parse(response);
                var valorInput=$("#archivo").val();
                var valorTodos=valorInput+'|'+json.archivo;
                $("#archivo").val(valorTodos);
            //    console.log('leerXML de dropzone');
                leerXML();
            },
        removedfile: function(file) {
            var filename = file.name; // Obtén el nombre del archivo que se va a eliminar
         //   console.log('Resultado: '+file);
            // Envía una solicitud POST al servidor para eliminar el archivo
            $.ajax({
                url: "ajax/facturas/eliminar-archivo-dropzone.php", // Reemplaza con la ruta correcta en tu servidor
                type: "POST",
                data: {
                filename: filename // Pasa el nombre del archivo al servidor
                },
                success: function(response) {
            //    console.log("Archivo eliminado correctamente");
                },
                error: function(xhr, status, error) {
            //    console.error("Error al eliminar el archivo:", error);
                }
            });
            
            // Elimina el elemento visualmente de la zona de dropzone
            file.previewElement.remove();
        }                  
    }); 

    /*  DROPZONE COMPLEMENTOS DE PAGO  */

    $("div#complementos").dropzone({ 
        url: "ajax/facturas/upload-files-dropzon.php",
        paramName: "file",    
        maxFiles: 2,    
        addRemoveLinks: true,   
        createImageThumbnails: true,
        acceptedFiles: ".pdf, .xml",
        dictRemoveFile: '<i class="fa fa-trash text-danger"></i>Borrar.',  
        init: function() {
            var xmlFileAdded = false;
            /* Valida que solo sean 2 archivos y que por lo menos uno sea xml*/
    
            this.on("addedfile", function(file) {
                if (this.files.length > 2) {
                    // Mostrar aviso cuando se intenten agregar más de 2 archivos
                    alertify.alert('Aviso', 'No puedes agregar más de 2 archivos.', function(){ alertify.success('Aceptar'); }); 
                    this.removeFile(file);
                    return;
                }
    
                if (file.name.toLowerCase().endsWith(".xml")) {
                    xmlFileAdded = true;
                }
            });
    
            this.on("processing", function() {
              //  console.log($("#estatusFactura").val());
                if (!xmlFileAdded) {
                    // Mostrar aviso cuando no se agrega ningún archivo .xml
                    alertify.alert('Aviso', 'Debes agregar al menos un archivo .xml.', function(){ alertify.success('Aceptar'); }); 
                    this.removeAllFiles();
                    return;
                }
            });
    
            this.on("success", function(file, response) {
                var json = JSON.parse(response);
                $("#archivosComplemento").val(json.archivo);
                /* valida que sea un complemento de paago */
                $.ajax({
                    url: "ajax/facturas/leerXMLcomplementopago.php",
                    type: "POST",
                    data: {
                        archivo: json.archivo
                    },
                    success: function(data) {
                     //   console.log(data);
                        var json = JSON.parse(data);
                        /* Valida que el xml sea un complemento de pago  */
                        
                        if(json.contenido=='noValido' || json.contenido=="'noValido'"){                                                                                        
                            alertify.alert('Aviso', 'El archivo .xml no es un complemento de pago', function(){ alertify.success('Aceptar'); }); 
                            // Eliminar el archivo .xml de Dropzone
                            file.previewElement.remove();
                            $(".dz-message").show();
                        }
                        /* Valida que el cliente este capturado o activo */
                        
                        if(json.contenido=='noCliente' || json.contenido=="'noCliente'"){
                            alertify.alert('Aviso', 'El archivo .xml no pertenece a un cliente capturado o activo', function(){ alertify.success('Aceptar'); }); 
                            // Eliminar el archivo .xml de Dropzone
                            file.previewElement.remove();
                            $(".dz-message").show();
                        }
                        
                    }
                });
            });
        }, 
        removedfile: function(file) {
            var filename = file.name;
            
            $.ajax({
                url: "ajax/facturas/eliminar-archivo-dropzone.php",
                type: "POST",
                data: {
                    filename: filename 
                },
                success: function(response) {
                //    console.log("Archivo eliminado correctamente");
                },
                error: function(xhr, status, error) {
                //    console.error("Error al eliminar el archivo:", error);
                }
            });
    
            file.previewElement.remove();
        }
    });
            

    function leerXMLcomplementopago(){
        $.ajax({
            url: "ajax/facturas/leerXMLcomplementopago.php", // ruta en el servidor
            type: "POST",
            data: {
            filename: filename 
            },
            success: function(response) {
        //    console.log("Archivo eliminado correctamente");
            },
            error: function(xhr, status, error) {
            console.error("Error al eliminar el archivo:", error);
            }
        });
    }


    $(document).ready(function() {  
        
        $("#cerrarOrdena").click(
            function(){
                $('#modalOrdenaCompilado').modal('toggle');
                
            }
        );
        $("#cerrarOrdenaX").click(
            function(){
                $('#modalOrdenaCompilado').modal('toggle');
            }
        ); 

        $("#cerrarModificarFacturas").click(
            function(){
                $("#ModificaEstatusFactura")[0].reset(); //Limpiar el formulario
                $("#fechaAccion").removeClass('is-valid');
                $("#estatusFactura").removeClass('is-valid');
                borrarArchivosDropzone();
                // Limpiar el contenido del contenedor de Dropzone
                var dropzoneElement = $("div#complementos");
                dropzoneElement.empty(); // Borra el contenido HTML del elemento
                $("#complementos").append('<div class="dz-message text-dark" data-dz-message><span>Arrastra tus archivos aquí <br> o haz click para subir archivos</span></div>');              
            }
        );
        $("#cerrarModificarFacturasX").click(
            function(){
                $("#ModificaEstatusFactura")[0].reset(); //Limpiar el formulario
                $("#fechaAccion").removeClass('is-valid');
                $("#estatusFactura").removeClass('is-valid');
                borrarArchivosDropzone();
                // Limpiar el contenido del contenedor de Dropzone
                var dropzoneElement = $("div#complementos");
                dropzoneElement.empty(); // Borra el contenido HTML del elemento
                $("#complementos").append('<div class="dz-message text-dark" data-dz-message><span>Arrastra tus archivos aquí <br> o haz click para subir archivos</span></div>');
            }
        );

        // Función para borrar los archivos de Dropzone
        function borrarArchivosDropzone() {
            // Obtener el elemento Dropzone
            var dropzoneElement = $("div#complementos.dropzone")[0].dropzone;

            // Obtener los archivos subidos en Dropzone
            var archivosSubidos = dropzoneElement.getAcceptedFiles();

            // Eliminar cada archivo subido
            for (var i = 0; i < archivosSubidos.length; i++) {
                dropzoneElement.removeFile(archivosSubidos[i]);
            }            
        }

        // Borra el contenido del elemento dropzone sin eliminar los archivos subidos
        function borrarContenidoDropzone() {
            var dropzoneElement = $("div#complementos");
            dropzoneElement.empty(); // Borra el contenido HTML del elemento
            $("#complementos").append('<div class="dz-message text-dark" data-dz-message><span>Arrastra tus archivos aquí <br> o haz click para subir archivos</span></div>');
        }

        $("#fechaAccion").blur(
            function(){
                if($(this).val()!=''){
                    $(this).removeClass('is-invalid');
                    $(this).addClass('is-valid');
                }
            }
        );

        $('#cambiadeclientes').on('input', function() {
            var razon_social = $(this).val();
            $.ajax({
                url: "ajax/facturas/ordena-tabla-x-cliente.php", 
                type: "POST",
                data:{razon_social:razon_social},
                success: function(result){
                  //  console.log(result); 
                    var json = JSON.parse(result);
                    $("#cambiadeclientes").val('');
                    $("#cambiadeclientes").attr("placeholder", json.orden);
                    // Recarga la tabla                     
                    var table = $('#tablaFacturas').DataTable();
                    table.ajax.reload( function ( json ) {
                        $('#tablaFacturas').val( json.lastInput );
                    } ); 
                    $("#cambiadeclientes").addClass('active');  
                }}
            );      
        });

        $("#cambiadeclientes").blur(
            function(){
                $(this).addClass('active');
        });
        
        $("#search").blur(
            function(){
                $(this).addClass('active');
        });
        

        $("#fact_retencion").change(
        function(){                                            
            var string = numeral($("#fact_retencion").val()).format('000,000.00');
            $("#fact_retencion").val(string);
            
        });          

        /* Facturas 1 */

        $("#altaFactura").click(
            function(){
                $("#modalSeleccionaXml").modal('show');                    
                /*
                $('#modalAltaFacturas').modal('toggle');
                $('#modalAltaFacturas').modal('show');
                $('#modalAltaFacturas').modal('hide');    SiguienteFacturas1
                */
            }
        );
        $('#modalAltaFacturas').on('shown.bs.modal', function() {
            $('#fact_serie').focus(); // focus al primer input
        })

        $("#cerrarAltasX").click(
            function(){
                $('#modalAltaFacturas').modal('toggle');
               
            }
        );
        $("#cerrarAltas").click(
            function(){
                $('#modalAltaFacturas').modal('toggle');
            }
        ); 
        $("#cerrarSeleccionaXmlX").click(
            function(){
                $('#modalSeleccionaXml').modal('toggle');
                $("#folioCaptura").val('');             
                $("#folioCaptura").removeClass('is-valid'); 
                $("#archivo").val('');                 
             //   borrarArchivosDropzoneFacturas();
                // Limpiar el contenido del contenedor de Dropzone
                var dropzoneElement = $("div#myId");
                dropzoneElement.empty(); // Borra el contenido HTML del elemento
                $("#myId").append('<div class="dz-message text-dark" data-dz-message><span>Arrastra tus archivos aquí <br> o haz click para subir archivos</span></div>');

            }
        );
        $("#cerrarSeleccionaXml").click(
            function(){
                $('#modalSeleccionaXml').modal('toggle');   
                $("#folioCaptura").val('');             
                $("#folioCaptura").removeClass('is-valid'); 
                $("#archivo").val('');                 
            //    borrarArchivosDropzoneFacturas();
                // Limpiar el contenido del contenedor de Dropzone
                var dropzoneElement = $("div#myId");
                dropzoneElement.empty(); // Borra el contenido HTML del elemento
                $("#myId").append('<div class="dz-message text-dark" data-dz-message><span>Arrastra tus archivos aquí <br> o haz click para subir archivos</span></div>');
            }
        );


        /* Facturas 2 */
        $("#SiguienteFacturas1").click(
            function(){
                var pasa=0;
                if(!$("#folioCaptura").val()){
                    $("#folioCaptura").addClass('is-invalid');
                    $("#spanErrorCaptura").html('<span class="text-danger mt-2 mb-2">El campo es obligatorio</span>');
                }else{
                   $("#fact_folio").val($("#folioCaptura").val()); 
                   PorcentajeRetencion($("#folioCaptura").val());
                   pasa++;
                }
                if(!$('#archivo').val()){
                                       
                }else{
                    pasa++;
                    
                }          
                if(pasa==2){
                    $("#modalAltaFacturas").modal('show');
                    $.ajax({
                        type: "POST",
                        url: "ajax/facturas/toma-valores-factura-cockies.php",
                        data: {folio:
                            function(){
                                return $("#fact_folio").val();
                            }
                        },
                        success: function(data)
                        {               
                       // console.log(data);
                        var json = JSON.parse(data);
                        $("#datosEstimacion").html(json.fact_no_estimacion);
                        }
                    }); /* End ajax */

                    tomaFolio();
                }else{
                    alertify.alert('Aviso', 'El Folio, así como los archivos pdf y xml son obligatorios', function(){ alertify.success('Aceptar'); }); 
                }
            }
        );
         
        function tomaFolio(){
            $.ajax({                    
                url: "ajax/facturas/toma-folio.php",
                type: "POST",
                data: {folio: function(){
                        return $("#fact_folio").val();
                    }
                },
                success: function(data)
                {              
                //    console.log(data);
                    var json = JSON.parse(data);
                        
                    if(data==1){
                        console.log("Fallo el server");
                    }else{                                               
                        $("#fact_fraccionamiento").val(json.fraccionamiento); 
                        $("#fact_fraccionamiento").addClass('active is-valid'); 
                    }
                }
            }); /* End ajax */
        }


        /* Validaciones */
               

        $.validator.addMethod(
            "regex",
            function(value, element, regexp) 
            {
                if (regexp.constructor != RegExp)
                    regexp = new RegExp(regexp);
                else if (regexp.global)
                    regexp.lastIndex = 0;
                return this.optional(element) || regexp.test(value);
            },
            "El Campo debe de tener solo letras y espacios"        
        );
       

        /* Validación de formulario */         
        $("#formAltas").validate(
            {
            rules: { 
                /*
                fact_serie:{
                    required: true,
                    minlength: 1,
                    maxlength: 10
                },                
                fact_no:{
                    required: true,
                    minlength: 2,
                    maxlength: 20,
                    remote: {
                        url: "ajax/facturas/busca-factura.php",
                        type: "POST",
                        data: {          
                            factura: function() {              
                                return $('#fact_no').val();
                            }
                        },                
                        dataFilter: function(data) {    
                        //    console.log(data);               
                            var json = JSON.parse(data);
                        //    console.log(json);
                            if(json.existe === "true" || json.existe === true) {
                                return '"true"';
                            } else {                      
                                return '"El número de factura ya existe"';
                            }
                        }
                    }
                },
                */
                fact_cliente:{
                    required: true,
                    minlength: 8,
                    maxlength: 100
                },
                fact_monto: {
                    required: true,
                    minlength: 1,
                    maxlength: 30         
                }, 
                /* 
                fact_amortizacion_anticipo: {
                    required: true
                },
                */ 
               /*                                                     
                fact_folio:{
                    required: true
                },                                                        
                */
                fact_fecha:{
                    required: true
                }         
            },            
            messages: {
                fact_serie: {
                    required: "Campo obligatorio",
                    minlength: "El mínimo de caracteres es 1",
                    maxlength: "El número máximo de caracteres es 10"
                },fact_no: {
                    required: "Campo obligatorio",
                    minlength: "El mínimo de caracteres es 2",
                    maxlength: "El número máximo de caracteres es 20"
                },
                fact_cliente:{
                    required: "Campo obligatorio",
                    minlength: "El mínimo de caracteres es 8",
                    maxlength: "El número máximo de caracteres es 100"
                },
                fact_monto: {            
                    required: "Campo obligatorio",
                    minlength: "El monto mínimo es de 1",
                    maxlength: "El monto máximo es de 999,999,999,999.99"
                },
                /*
                fact_folio: {           
                    required: "Campo obligatorio"
                },
                */
                fact_fecha: {           
                    required: "Campo obligatorio"
                }
            },        

            errorElement : 'span',  
               errorPlacement: function (error, element) {
                    error.addClass('invalid-feedback');
                    error.addClass('btn btn-danger btn-sm text-white');               
                    element.closest('.form-group').append(error);
                    },
                    highlight: function (element, errorClass, validClass) {
                    $(element).addClass('is-invalid');
                        
                    },
                    unhighlight: function (element, errorClass, validClass, error) {
                    $(element).removeClass('is-invalid');
                    $(element).addClass('is-valid'); 
                    $(error).removeClass('btn btn-danger btn-sm text-white d-flex justify-content-end');
                    },
               
            submitHandler: function(form){
                //submit form
            //    event.preventDefault();
            //   console.log('Entro');

                var datos = $("#formAltas").serialize();

                alertify.confirm('Aviso..!', '¿Deseas guardar la factura?', 
                function(){
                
                    $.ajax({
                        type: "POST",
                        url: "ajax/facturas/guarda-factura.php",
                        data: datos,
                        success: function(data)
                        {               
                    //    console.log(data);                         
                                             
                        if(data==1){
                                alert("Fallo el server");
                            }else{
                             //   $("#formAltas")[0].reset(); //Limpiar el formulario
                                //  location.href = "flujo-de-efectivo-contratos/todos/todos";
                                //   location.reload();    
                                var table = $('#tablaFacturas').DataTable();
                                table.ajax.reload( function ( json ) {
                                    $('#tablaFacturas').val( json.lastInput );
                                } ); 
                                
                                var idfactura = parseInt(data);
                           //     console.log('no factura: '+idfactura);
                                $('#idFacturaCompilaciones').val(idfactura);
                                /* cierra altas y abre compilados */
                                $('#modalAltaFacturas').modal('toggle');                                                                
                                
                                /*
                                $('#modalEstatusFacturas').modal('toggle');
                                $('#modalAltaFacturas').modal('show');
                                $('#modalAltaFacturas').modal('hide');
                                */

                                $("#fact_fecha").removeClass("active is-valid");
                                $("#fact_fecha").attr("style","opacity: 0;");                                    
                                $("#fact_serie").removeClass('active is-valid');                                     
                                $("#fact_no").removeClass('active is-valid');   
                            //    $("#fact_folio").removeClass('active is-valid');  
                                $("#fact_cliente").removeClass('active is-valid');    
                                $("#fact_amortizacion_anticipo").removeClass('active is-valid');
                                $("#fact_retencion").removeClass('active is-valid');                            
                                $("#fact_monto").removeClass('active is-valid');                                     
                                $("#fact_descripcion").removeClass('active is-valid'); 

                                alertify.alert('Aviso', 'Se han guardado los cambios', function(){ alertify.success('Aceptar'); });    
                                var folio=$("#fact_folio").val();
                                var factNo= $("#fact_no").val();
                                var factSerie=$("#fact_serie").val();
                            //    location.href = "estimaciones-ordena-archivos/"+factNo+"/"+factSerie+"/"+folio;
                                
                                $('#modalOrdenaCompilado').modal('show');                                   
                             //   $("#thedialog").attr("src", "contenido/expedientes-ordenar-archivos.php?factNo="+factNo+"&factSerie="+factSerie+"&folio="+encodeURI(folio));
                                fncOrdenaArchivos(factNo,factSerie,folio);
                            }

                        }
                    }); /* End ajax */
                    
                },
                function(){ alertify.error('Cancel')}
                ).set('labels', {ok:'Si', cancel:'No'});
                
            }
            
        });  
        
        
        /* #############   */
        /* Validación estatus factura */
        /* #############   */

        $("#ModificaEstatusFactura").validate(
            {
            rules: {                 
                fechaAccion:{
                    required: true
                },
                estatusFactura: {
                    required: true        
                }
                /*, 
                archivosComplemento:{
                    required: true
                } 
                */        
            },            
            messages: {
                fechaAccion: {
                    required: "Campo obligatorio"
                },
                estatusFactura: {
                    required: "Campo obligatorio"
                }
                /*,
                archivosComplemento:{
                    required: function(){
                        alertify.alert('Campo Obligatorio', 'Tienes que adjuntar el .xml y .pdf del complemento de pago', function(){  })
                    }           
                }
                */
            },        

            errorElement : 'span',  
               errorPlacement: function (error, element) {
                    error.addClass('invalid-feedback');
                    error.addClass('btn btn-danger btn-sm text-white');               
                    element.closest('.form-group').append(error);
                    },
                    highlight: function (element, errorClass, validClass) {
                    $(element).addClass('is-invalid');
                        
                    },
                    unhighlight: function (element, errorClass, validClass, error) {
                    $(element).removeClass('is-invalid');
                    $(element).addClass('is-valid'); 
                    $(error).removeClass('btn btn-danger btn-sm text-white d-flex justify-content-end');
                    },
               
            submitHandler: function(form){
                //submit form
            //    event.preventDefault();
            //   console.log($("#estatusFactura").val());
            //   console.log($("#archivosComplemento").val());
               if($("#estatusFactura").val()==1 && !$("#archivosComplemento").val()){
                    alertify.alert('Aviso', 'Tienes que adjuntar el .xml y .pdf del complemento de pago', function() {  });
               }else{ 
                
                    var datos = $("#ModificaEstatusFactura").serialize();                

                    alertify.confirm('Aviso..!', '¿Deseas realizar el cambio de estatus en la factura?', 
                    function(){
                    
                        $.ajax({
                            type: "POST",
                            url: "ajax/facturas/modifica-estatus-factura.php",
                            data: datos,
                            success: function(data)
                            {               
                        //    console.log(data);                         
                                                
                            if(data==1){
                                    alert("Fallo el server");
                                }else{                                                                
                                    var table = $('#tablaFacturas').DataTable();
                                    table.ajax.reload( function ( json ) {
                                        $('#tablaFacturas').val( json.lastInput );
                                    } ); 
                                                                                                                            
                                    $('#modalEstatusFacturas').modal('toggle');                                                                
                                    
                                    /*
                                    $('#modalEstatusFacturas').modal('toggle');
                                    $('#modalAltaFacturas').modal('show');
                                    $('#modalAltaFacturas').modal('hide');
                                    */

                                    alertify.alert('Aviso', 'Se han guardado los cambios', function() {
                                        alertify.success('Aceptar');
                                        borrarContenidoDropzone(); // Llama a la función para borrar el contenido del elemento dropzone
                                    });
                                    $("#complementos").html('<div class="dz-message text-dark" data-dz-message><span>Arrastra tus archivos aquí <br> o haz click para subir archivos</span></div>');  // muestra el mensje de subir archivos
                                    $("#fechaAccion").removeClass('is-valid');
                                    $("#estatusFactura").removeClass('is-valid');
                                    $("#ModificaEstatusFactura")[0].reset(); //Limpiar el formulario
                                    //  location.href = "flujo-de-efectivo-contratos/todos/todos";
                                    //   location.reload(); 
                                }

                            }
                        }); /* End ajax */
                        
                    },
                    function(){ alertify.error('Cancel')}
                    ).set('labels', {ok:'Si', cancel:'No'});
                }
            }
            
        });     
        
        

        var fechaHoy=fechaActual(); 
        var nombreUsuario = $("#nombreUsuario").text().trim();

        $('#tablaFacturas').DataTable({                                 
            ajax: 'ajax/facturas/tabla-facturas.php',
            ordering: true,
            fixedHeader: true,
            paging: true, // Agregar paginación
            dom: "<'row'<'col-sm-8'l><'col-sm-2'B><'col-sm-2'p>>"+
                "<'row'<'col-sm-12'tr>>"+
                "<'row'<'col-sm-8'l><'col-sm-2'><'col-sm-2'p>>",                                                                    
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: 'Exportar a Excel',
                    filename: 'Estimaciones al '+fechaHoy,
                    header:false,
                    customize: function ( xlsx ) {
                        var sheet = xlsx.xl.worksheets['sheet1.xml'];
                        //Bold Header Row
                        $('row[r=5] c', sheet).attr( 's', '18' );
                        //Make You Input Cells Bold Too
                        /*
                        $('c[r=A1]', sheet).attr( 's', '2' );
                        $('c[r=A2]', sheet).attr( 's', '2' );
                        */
                    },
                    customizeData: function(data){                                        
                        var desc = [
                            [' ',' '],
                            ['Fecha del reporte: ',fechaHoy],
                            ['Usuario: ',nombreUsuario]                        
                        ];
                        data.body.unshift(data.header);
                        for (var i = 0; i < desc.length; i++) {
                            data.body.unshift(desc[i]);
                        };
                    },
                    title: 'Estimaciones',                
                    className: 'btn btn-secondary',
                    
                    /*,
                    exportOptions: {
                        columns: [ 0, 1, 2, 3, 4 ]
                    }
                    */
                }
            ],
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"                
            },
            
        });       

        var oTable = $('#tablaFacturas').DataTable();    
            $('#search').keyup(function(){
            oTable.search( $(this).val() ).draw();
        })
    });

    function obtenerUsuario(){
        $.ajax({
            type: "POST",
            url: "ajax/usuarios/obtener-usuario.php",
            data: '',
            success: function(data)
            {               
           // console.log(data);                                                          
                if(data==1){
                        alert("Fallo el server");
                }else{
                    
                }
                return data;
            }
        });
    };
