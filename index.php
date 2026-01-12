<?php session_start(); ?>
<!DOCTYPE html>
<?php
include 'admin/php/menu_login.php';
include 'admin/lib/clsConsultas.php';
$clsConsulta = new Consultas();

?>
<html lang="es">

<head>
    <!--base href="<?php // echo $base; 
                    ?>"-->
    <meta charset="utf-8">
    <title>Catsa Comercial Abarrotera </title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="img/icons/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="img/icons/favicon.svg" />
    <link rel="shortcut icon" href="img/icons/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="img/icons/apple-touch-icon.png" />
    <link rel="manifest" href="img/icons/site.webmanifest" />

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="dist/lib/animate/animate.min.css" rel="stylesheet">
    <link href="dist/lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="dist/css/style.css" rel="stylesheet">

    <!-- Jquey -->
    <script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>


    <!-- Validate jq -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/additional-methods.min.js"></script>

</head>

<body>
    <!-- Spinner Start -->
    <div id="spinner"
        class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-grow text-primary" role="status"></div>
    </div>
    <!-- Spinner End -->

    <!-- Navbar Start -->
    <nav class="navbar navbar-expand-lg bg-white navbar-light sticky-top p-0">
        <a href="#"
            class="navbar-brand d-flex align-items-center border-end px-4 px-lg-5 w-90">
            <img src="img/logo-inicio.png" width="50%">
        </a>
        <button type="button" class="navbar-toggler me-4" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <div class="navbar-nav ms-auto p-4 p-lg-0">
                <a href="#" class="nav-item nav-link text-secondary active">Servicios</a>
                <a class="btn btn-secondary py-4 px-lg-5" href="#" data-bs-toggle="modal"
                    data-bs-target="#modal-sesion">
                    <i class="fas fa-cogs start"></i> Iniciar sesión
                </a>
            </div>
        </div>
    </nav>
    <!-- Navbar End -->

    <!-- PC -->
    <div class="container-fluid p-0 wow fadeIn d-none d-lg-block " data-wow-delay="0.1s">
        <div class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img class="w-100" src="img/fondo.png" alt="Image">
                    <div class="carousel-caption">
                        <div class="container">
                            <div class="row justify-content-center">
                                <div class="col-lg-7">
                                    <img class="img-fluid" src="img/logo-front.png" />
                                    <!-- <h1 class="display-2 text-light animated slideInDown">
                                        Catsa Comercial Abarrotera
                                    </h1> -->
                                    <div class="p-5">Bienvenido</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Movil -->
    <div class="container-fluid p-0 wow fadeIn mt-1 d-sm-block d-lg-none" data-wow-delay="0.1s">
        <div class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img class="w-100" src="img/carousel-1.jpg" alt="Image">
                    <div class="carousel-caption">
                        <div class="container mt-5">
                            <div class="row justify-content-center">
                                <div class="col-lg-7">
                                    <img class="w-75" src="img/logo-front.png" />
                                    <!-- <h1 class="display-2 text-light mb-5 animated slideInDown">
                                        Catsa Comercial Abarrotera
                                    </h1> -->
                                    <div class="p-5">Bienvenido</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Carousel End -->

    <!-- Facts Start -->
    <!--div class="container-fluid facts py-5 pt-lg-0" id="servicios">
        <div class="container py-5 pt-lg-0">
            <div class="row gx-0">
                <div class="col-lg-4 wow fadeIn" data-wow-delay="0.1s">
                    <div class="bg-white shadow d-flex align-items-center h-100 p-4" style="min-height: 150px;">
                        <div class="d-flex">
                            <div class="flex-shrink-0 btn-lg-square bg-primary">
                                <i class="fa fa-car text-white"></i>
                            </div>
                            <div class="ps-4">
                                <h5>Control de Estimaciones </h5>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 wow fadeIn" data-wow-delay="0.3s">
                    <div class="bg-white shadow d-flex align-items-center h-100 p-4" style="min-height: 150px;">
                        <div class="d-flex">
                            <div class="flex-shrink-0 btn-lg-square bg-primary">
                                <i class="fa fa-users text-white"></i>
                            </div>
                            <div class="ps-4">
                                <h5>Vinculación</h5>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 wow fadeIn" data-wow-delay="0.5s">
                    <div class="bg-white shadow d-flex align-items-center h-100 p-4" style="min-height: 150px;">
                        <div class="d-flex">
                            <div class="flex-shrink-0 btn-lg-square bg-primary">
                                <i class="fa fa-file-alt text-white"></i>
                            </div>
                            <div class="ps-4">
                                <h5>Obras Extras</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div-->
    <!-- Facts End -->

    <!-- Footer Start -->
    <div class="container-fluid text-light footer my-2 mb-0 py-3 wow fadeIn" data-wow-delay="0.1s">
        <div class="container">
            <div class="row g-5 align-content-center">
                <div class="col-lg-3 text-lg-start">
                    <div class="d-flex justify-content-center">
                        <img src="img/logo-footer.png" class="img-fluid">
                    </div>
                    <div class="d-flex justify-content-center">&copy; Catsa Comercial Abarrotera </div>
                </div>
                <div class="col-lg-3 my-3 my-lg-0">
                    <div class="mt-5">
                        Hermanos Aldama 104-A <br>
                        San Nicolás <br>
                        C.P. 37000 <br>
                        León, Gto., México.
                    </div>
                </div>
                <div class="col-lg-3 my-3 my-lg-0">
                    <div class="mt-5">
                        &nbsp; <br>
                        Central de Abastos <br>
                        C.P. 37000 <br>
                        León, Gto., México.
                    </div>
                </div>
                <div class="col-lg-3 my-3 my-lg-0">
                    <div class="mt-5">
                        477 123 4567 <br>
                        477 123 4567 <br>
                        477 123 4567 <br>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer End -->

    <div class='modal fade' id="modal-sesion" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-user"></i> Acceso Administrativo</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Form -->
                    <form method="POST" action="" id="formAcceso">
                        <input type="hidden" name="_token" value="QayzNS1ctye8arMXw2iJC8l2UiYAoWU7XhA5WYWs">
                        <span class="text-center">
                            <p>Proporcione sus datos de acceso</p>
                        </span>
                        <div class="row mb-3 form-group ">
                            <label for="usr" class="col-md-4 col-form-label text-md-end">Correo Electrónico</label>

                            <div class="col-md-8">
                                <input id="usr" type="email" class="form-control " name="usr" value="" autofocus autocomplete="off">
                            </div>
                        </div>

                        <div class="row form-group  mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">Contraseña</label>

                            <div class="col-md-8">
                                <input id="pwd" type="password" class="form-control " name="pwd" autocomplete="off">
                            </div>
                        </div>



                        <div class="row mb-3 text-center" id="mensaje" style="display:none">
                            <span class="text-danger"> Usuario o contraseña no coinciden </span>
                        </div>

                        <div class="row mb-0">
                            <a class="btn btn-link text-dark col-md-6" data-bs-toggle="modal" data-bs-target="#modal-email">
                                ¿Olvidaste tu contraseña?
                            </a>
                            <div class="col-md-6">
                                <button type="submit" id="entrar" class="btn btn-dark">
                                    Entrar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <div class='modal fade' id="modal-email" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-user"></i> Restablecer Contraseña</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="" id="formRestablece">
                        <input type="hidden" name="_token" value="QayzNS1ctye8arMXw2iJC8l2UiYAoWU7XhA5WYWs">
                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">Correo Electrónico</label>

                            <div class="form-group col-md-8">
                                <input id="email" type="email" class="form-control " name="email"
                                    value="" required autocomplete="email" autofocus>
                            </div>
                        </div>

                        <div class="row mb-3 mt-3">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-info">
                                    Enviar enlace para restablecer contraseña
                                </button>
                            </div>
                            <!--div class="row mb-3 mt-3 text-center" id="mensajeEnviar"  >
                                <span class="text-success"> Se ha enviado un link a tu correo </span>
                                <p>Si no recibes el correo, revisa que esté escrito correctamente y que se haya recibido como spam</p>
                                <p>En cualquier momento puedes solicitar de nuevo el envío de correo</p>
                            </div-->
                            <div class="row mb-3 mt-3 text-center" id="mensajeRecupera"> &nbsp; </div>

                        </div>
                    </form>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <div class="modal fade" id="modal-aviso">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Aviso de privacidad</h4>
                </div>
                <div class="modal-body">
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sit amet neque ligula. Etiam commodo metus sit amet tortor finibus fermentum. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Suspendisse facilisis purus quis risus mollis pretium. Integer condimentum consequat facilisis. Quisque sed magna sollicitudin, egestas enim vitae, dignissim nunc. Sed odio mauris, fringilla ac scelerisque sit amet, elementum ut lacus. Phasellus mattis aliquet pulvinar. Vivamus non tellus lorem. Curabitur ullamcorper pellentesque libero nec sodales. Integer a ultricies felis. Pellentesque lectus quam, tincidunt ut efficitur sit amet, fringilla a lorem.
                    Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Aliquam fringilla odio sit amet congue ullamcorper. Duis leo enim, pellentesque nec diam eget, varius semper nisi. Sed in tempus est, quis laoreet odio. Morbi interdum dui erat, non pellentesque nisi convallis sed. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Fusce ut erat posuere, eleifend nisi quis, tristique dui. Curabitur auctor metus sed lectus consequat fermentum. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Nulla facilisi. Integer at mollis diam, non tristique justo. Aenean semper diam id viverra sollicitudin. Nam nibh neque, venenatis et est eget, malesuada vestibulum sapien.
                    Aliquam nec leo tincidunt, consequat mauris in, aliquet lectus. Maecenas accumsan in magna id commodo. Nunc ultrices lectus enim, vel convallis turpis convallis et. Nullam quis tortor a tortor mollis consequat blandit eu leo. Fusce at nulla hendrerit odio aliquam placerat eu a ex. Nunc ullamcorper mauris in dolor malesuada, sit amet vulputate diam ultrices. Suspendisse viverra augue sit amet leo commodo, ac blandit mauris imperdiet. Vivamus porttitor luctus rutrum.
                    Mauris pulvinar ornare nisi, a convallis dui gravida sed. Donec cursus nunc vel aliquam viverra. Ut id dui id sapien ultricies auctor ac in ex. Praesent ex metus, posuere ut sagittis in, fringilla eget ante. Proin sapien neque, rutrum nec nisl ut, tristique ultrices nibh. Proin efficitur, ante non consectetur malesuada, dui mi pulvinar justo, ac euismod turpis enim venenatis erat. Maecenas auctor enim purus, eget ultrices sapien molestie vitae. Fusce lobortis, dolor venenatis iaculis tincidunt, tortor nunc sollicitudin neque, non porttitor tellus lectus et quam. Suspendisse in sem enim.
                    Nulla feugiat nulla nec felis pretium, vitae gravida odio elementum. Etiam rutrum ipsum eu purus facilisis, non ornare enim ultricies. Etiam in dapibus erat. Suspendisse gravida suscipit dignissim. Quisque tincidunt lectus id efficitur fringilla. Nullam mattis accumsan arcu fringilla tincidunt. Sed convallis quam et mollis vestibulum. Curabitur rutrum justo ultricies metus tincidunt, in interdum enim scelerisque. Curabitur posuere lorem in ligula tristique feugiat. Ut sit amet congue nulla, at consequat nunc. Donec volutpat tellus mi, eu fringilla ipsum luctus in. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Phasellus vel tellus tortor. Nunc a nisl eget tellus faucibus laoreet. Cras sodales accumsan consequat. Duis eu lobortis lorem.
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>

    <!-- Back to Top -->
    <a href="#" class="btn btn-lg btn-secondary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>

    <!-- JavaScript Libraries -->
    <!--script src="https://code.jquery.com/jquery-3.4.1.min.js"></script-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="dist/lib/wow/wow.min.js"></script>
    <script src="dist/lib/easing/easing.min.js"></script>
    <script src="dist/lib/waypoints/waypoints.min.js"></script>
    <script src="dist/lib/owlcarousel/owl.carousel.min.js"></script>
    <!--script src="vendor/ion-sound/ion.sound.min.js"></script-->
    <!-- Template Javascript -->
    <script src="dist/js/main.js"></script>

    <script>
        /* El correo no existe consulta con el administrador */
        function validaCorreo() {
            var correo = $("#email").val();
            console.log(correo);
            if (correo === '') {
                $('mensajeRecupera').html('<p class="text-danger">Campo obligatorio</p>');
            }
        }
        /*
                function enviarEnlace(){
                    $.ajax({
                        url: "ajax/enviar-mensaje-recuperacion.php", 
                        type: "POST",
                        data:{email:
                            function(){
                                return $("#email").val();
                            } 
                        }                                                                   
                    }).done(function(response){
                        console.log(response);
                        if(response==1){
                            console.log('true');
                            $("#mensajeEnlace").hide();
                            $("#mensajeEnviar").show();                                                                                               
                        }else{
                            //  console.log('false');
                          //  $("#email").val(''); 
                            $("#mensajeEnlace").show();  
                            $("#mensajeEnviar").hide();                                            
                        }
                    });

                }
        */
        $(document).ready(function() {
            var nombreUsuario = $("#nombreUsuario").text().trim();

            $("#email").change(
                function() {
                    $("#mensajeRecupera").html('&nbsp;');
                }
            );


            $.validator.addMethod(
                "regex",
                function(value, element, regexp) {
                    if (regexp.constructor != RegExp)
                        regexp = new RegExp(regexp);
                    else if (regexp.global)
                        regexp.lastIndex = 0;
                    return this.optional(element) || regexp.test(value);
                },
                "Escriba un correo valido."
            );


            $("#formAcceso").validate({
                rules: {
                    usr: {
                        required: true,
                        minlength: 8,
                        maxlength: 50,
                        email: true,
                        regex: /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/
                    },
                    pwd: {
                        required: true,
                        minlength: 8,
                        maxlength: 30
                    },
                    empresa: {
                        required: true
                    }
                },
                messages: {
                    usr: {
                        required: "Escribe un correo electrónico",
                        minlength: "Minimo 8 caracteres",
                        maxlength: "Máximo 50 caracteres",
                        email: "Escribe un correo valido.",
                        regex: "Escribe un correo valido."
                    },
                    pwd: {
                        required: "Escriba la contraseña",
                        minlength: "Minimo 8 caracteres",
                        maxlength: "Máximo 30 caracteres"
                    },
                    empresa: "Selecione una epresa"
                },
                errorElement: 'span',
                errorPlacement: function(error, element) {
                    error.addClass('invalid-feedback');
                    error.addClass(' text-danger text-center');
                    element.closest('.form-group').append(error);
                },
                highlight: function(element, errorClass, validClass) {
                    $(element).addClass('is-invalid');

                },
                unhighlight: function(element, errorClass, validClass, error) {
                    $(element).removeClass('is-invalid');
                    $(element).addClass('is-valid');
                    $(error).removeClass('text-danger d-flex justify-content-center');
                },

                submitHandler: function(form) {
                    //submit form
                    event.preventDefault();
                    var datos = $("#formAcceso").serialize();
                    $.ajax({
                        type: "POST",
                        url: "admin/lib/verifica.php",
                        data: datos,
                        success: function(data) {
                            // console.log(data);
                            var json = JSON.parse(data);
                            if (json.existe === "true" || json.existe === true) {
                                location.href = "admin/inicio";
                            } else {
                                $("#mensaje").show();

                            }
                        }
                    }); /* End Ajax */
                }
            });
            /* #######################  */
            /* Form Restablece */
            $("#formRestablece").validate({
                rules: {
                    email: {
                        required: true,
                        minlength: 8,
                        maxlength: 50,
                        email: true,
                        regex: /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/,
                        remote: {
                            url: "ajax/verifica-correo.php",
                            type: "POST",
                            data: {
                                email: function() {
                                    return $('#email').val();
                                }
                            },
                            dataFilter: function(data) {
                                var json = JSON.parse(data);
                                if (json.correo === "true" || json.correo === true) {
                                    return '"true"';
                                } else {
                                    return '"El correo no esta registrado"';
                                }
                            }
                        }
                    }
                },
                messages: {
                    email: {
                        required: "Escribe un correo electrónico",
                        minlength: "Minimo 8 caracteres",
                        maxlength: "Máximo 50 caracteres",
                        email: "Escribe un correo valido.",
                        regex: "Escribe un correo valido."
                    }
                },
                errorElement: 'span',
                errorPlacement: function(error, element) {
                    error.addClass('invalid-feedback');
                    error.addClass(' text-danger text-center');
                    element.closest('.form-group').append(error);
                },
                highlight: function(element, errorClass, validClass) {
                    $(element).addClass('is-invalid');

                },
                unhighlight: function(element, errorClass, validClass, error) {
                    $(element).removeClass('is-invalid');
                    $(element).addClass('is-valid');
                    $(error).removeClass('text-danger d-flex justify-content-center');
                },

                submitHandler: function(form) {
                    //submit form
                    //  event.preventDefault();
                    var datos = $("#formRestablece").serialize();
                    $.ajax({
                        type: "POST",
                        url: "ajax/enviar-mensaje-recuperacion.php",
                        data: datos,
                        success: function(data) {
                            console.log(data);

                            if (data == 1) {
                                $("#mensajeRecupera").html('Sucedió un porblema con el servicio, por favor intenta más tarde');
                            } else {

                                $("#mensajeRecupera").html('<span class="text-success"> Se ha enviado un link a tu correo </span><p>Si no recibes el correo, revisa que esté escrito correctamente y que no esté como spam</p> <p>En cualquier momento puedes solicitar nuevamente el envío de correo</p>');
                                setTimeout("$('#modal-email').modal('hide');", 6000);
                            }
                        }
                    }); /* End Ajax */
                }
            });


        });


        (function($, window) {
            'use strict';

            var MultiModal = function(element) {
                this.$element = $(element);
                this.modalCount = 0;
            };

            MultiModal.BASE_ZINDEX = 1040;

            MultiModal.prototype.show = function(target) {
                var that = this;
                var $target = $(target);
                var modalIndex = that.modalCount++;

                $target.css('z-index', MultiModal.BASE_ZINDEX + (modalIndex * 20) + 10);

                // Bootstrap triggers the show event at the beginning of the show function and before
                // the modal backdrop element has been created. The timeout here allows the modal
                // show function to complete, after which the modal backdrop will have been created
                // and appended to the DOM.
                window.setTimeout(function() {
                    // we only want one backdrop; hide any extras
                    if (modalIndex > 0)
                        $('.modal-backdrop').not(':first').addClass('hidden');

                    that.adjustBackdrop();
                });
            };

            MultiModal.prototype.hidden = function(target) {
                this.modalCount--;

                if (this.modalCount) {
                    this.adjustBackdrop();
                    // bootstrap removes the modal-open class when a modal is closed; add it back
                    $('body').addClass('modal-open');
                }
            };

            MultiModal.prototype.adjustBackdrop = function() {
                var modalIndex = this.modalCount - 1;
                $('.modal-backdrop:first').css('z-index', MultiModal.BASE_ZINDEX + (modalIndex * 20));
            };

            function Plugin(method, target) {
                return this.each(function() {
                    var $this = $(this);
                    var data = $this.data('multi-modal-plugin');

                    if (!data)
                        $this.data('multi-modal-plugin', (data = new MultiModal(this)));

                    if (method)
                        data[method](target);
                });
            }

            $.fn.multiModal = Plugin;
            $.fn.multiModal.Constructor = MultiModal;

            $(document).on('show.bs.modal', function(e) {
                $(document).multiModal('show', e.target);
            });

            $(document).on('hidden.bs.modal', function(e) {
                $(document).multiModal('hidden', e.target);
            });
        }(jQuery, window));
    </script>
</body>

</html>