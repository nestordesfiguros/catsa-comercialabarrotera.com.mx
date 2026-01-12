<!-- contenido/cxp-pagos.php -->

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <!--li class="breadcrumb-item"><a href="configuracion">Configuracion</a></li-->
            <li class="breadcrumb-item active" aria-current="page"> Pagos</li>
        </ol>
    </nav>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row col-12">
                            <div class="col-12 col-md-8">
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="proveedores"><i class="fas fa-truck-moving"></i> Proveedores </a> &nbsp;
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="ordenes-compra"><i class="fas fa-file-invoice"></i> Órdenes de compra </a> &nbsp;
                                <a type="button" class="btn btn-info btn-fixed mt-2 mt-md-0" href="cxp-pagos"><i class="fa fa-plus"></i> Pagos </a> &nbsp;

                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-outline  mt-2 mt-md-0" data-mdb-input-init>
                                    <input type="text" id="search" class="form-control" />
                                    <label class="form-label" for="form12">Buscar</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <table id="tablaClientes" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">Razón Social</th>
                                    <th class="text-center">Nombre Comercial</th>
                                    <th class="text-center">Contacto</th>
                                    <th class="text-center">Editar</th>
                                    <th class="text-center">Ubicación</th>
                                    <th class="text-center">Estatus</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
</section>