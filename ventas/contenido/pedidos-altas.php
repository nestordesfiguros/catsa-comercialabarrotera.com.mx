<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <!-- Encabezado -->
                    <div class="text-center mb-4">
                        <i class="fas fa-user-tie fa-3x text-primary mb-3"></i>
                        <h3 class="h4 mb-2">Seleccionar Cliente</h3>
                        <p class="text-muted mb-4">Selecciona un cliente para crear un nuevo pedido</p>
                    </div>

                    <!-- Formulario de selección -->
                    <form id="clienteForm" action="" method="GET">
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">Clientes registrados</h5>
                            <?php
                            if (isset($_SESSION['pedidoActivo'])) {
                                $con = "SELECT id, razon_social FROM cat_clientes WHERE id=" . $_SESSION['pedidoActivo'];
                                $rs = $clsConsulta->consultaGeneral($con);
                                $cliente = $rs[1]['razon_social'];
                                echo '<h3 class="text-danger">Para crear un nuevo pedido tienes que terminar el anterior o cancelarlo </h4>';
                            } else {
                            ?>
                                <!-- Grupo de entrada con icono integrado -->

                                <div class="d-flex align-items-center border rounded">
                                    <span class="px-3 py-2 bg-primary text-white rounded-start">
                                        <i class="fas fa-search"></i>
                                    </span>


                                    <select class="form-select border-0 py-2" id="id_cliente" name="id_cliente" required>
                                        <option value="" disabled selected>Buscar cliente...</option>
                                        <?php
                                        $rs = $clsConsulta->consultaGeneral("SELECT 
                                            cat_clientes.id,
                                            cat_clientes.razon_social,
                                            cat_clientes.nombre_comercial
                                        FROM 
                                            vendedores_clientes
                                            INNER JOIN cat_clientes 
                                                ON (vendedores_clientes.id_cliente = cat_clientes.id) 
                                        WHERE 
                                            vendedores_clientes.id_vendedor=" . $_SESSION['id_vendedor'] . " 
                                        ORDER BY 
                                            razon_social ASC");

                                        if ($rs && count($rs) > 0) {
                                            foreach ($rs as $val) {
                                                $nombreComercial = !empty($val['nombre_comercial']) ? ' / ' . $val['nombre_comercial'] : '';
                                                echo '<option value="' . $val['id'] . '">' .
                                                    htmlspecialchars($val['razon_social'] . $nombreComercial) . '</option>';
                                            }
                                        } else {
                                            echo '<option value="" disabled>No tienes clientes asignados</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <small class="text-muted mt-2 d-block">Puedes empezar a escribir para buscar más rápido</small>

                        </div>

                    <?php }  ?>

                    <!-- Botón de continuar -->

                    <div class="d-grid mt-4">                                        
                        <?php if (!isset($_SESSION['pedidoActivo'])) { ?>
                            <button type="submit" class="btn btn-primary btn-lg py-3">
                                <i class="fas fa-arrow-right me-2"></i> Continuar
                            </button>
                        <?php
                        } else {
                        ?>
                            <a href="pedidos-ver" class="btn btn-primary btn-lg py-3">
                                <i class="fas fa-arrow-right me-2"></i> Continuar
                            </a>
                        <?php
                        }
                        ?>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estilos personalizados para el componente */

    .form-select {

        background-image: none;

        padding-left: 0.5rem;

    }



    .form-select:focus {

        box-shadow: none;

        border-color: #dee2e6;

    }



    .border-rounded {

        border-radius: 0.375rem;

    }
</style>



<!-- Select2 para búsqueda avanzada -->

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>





<script>
    $(document).ready(function() {

        // Inicializar Select2 con búsqueda

        $('#id_cliente').select2({

            placeholder: "Buscar cliente...",

            allowClear: true,

            language: "es",

            width: '100%'

        });



        // Opcional: Redirección automática al seleccionar

        /*

        $('#id_cliente').change(function() {

            if ($(this).val()) {

                // Descomenta la siguiente línea si quieres redirección automática

                // window.location = 'pedidos-nuevo.php?id_cliente=' + $(this).val();

            }

        });

        */

        // Validación del formulario





        $('#clienteForm').validate({

            errorClass: "is-invalid",

            errorElement: "div",

            errorPlacement: function(error, element) {

                // No mostrar el mensaje debajo del campo

                error.remove();

            },

            highlight: function(element) {

                $(element).addClass('is-invalid');

            },

            unhighlight: function(element) {

                $(element).removeClass('is-invalid');

            },

            invalidHandler: function(event, validator) {

                // Mostrar solo el primer error con Alertify

                var errors = validator.numberOfInvalids();

                if (errors) {

                    var message = validator.errorList[0].message;

                    alertify.error(message);

                }

            },

            submitHandler: function(form) {

                var clienteId = $('#id_cliente').val();

                location.href = "pedidos-nuevo/" + clienteId;

            }

        });



    });
</script>



<!-- Estilos adicionales -->

<style>
    .select2-container--default .select2-selection--single {

        height: calc(3.5rem + 2px);

        padding: 0.5rem;

        border: 1px solid #ced4da;

    }



    .select2-container--default .select2-selection--single .select2-selection__arrow {

        height: 3.5rem;

    }



    .card {

        border-radius: 12px;

        transition: all 0.3s ease;

    }



    .card:hover {

        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);

    }
</style>