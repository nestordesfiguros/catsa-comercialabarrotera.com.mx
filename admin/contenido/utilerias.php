<!-- utilerias.php -->
<!-- Content Header (Page header) -->
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="active breadcrumb-item" aria-current="page"> Utilerias</li>
        </ol>
    </nav>
</div>
<!-- /.content-header -->
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card animation slide-in-down">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fa-cogs fas"></i> &nbsp; Utilerías / Configuración </h6>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <?php
                        $__layout = 'grid';
                        $__archivoActual = $nav ?? '';
                        include 'php/submenu_botones.php';
                        ?>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>