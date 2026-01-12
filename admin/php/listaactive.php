<?PHP
$inicio = "";

$entradasAlmacenista = '<div class="text-center">
                <a class="btn btn btn-light btn-square-lg" aria-current="page" href="recepcion">                    
                    <i class="fa fa-users fa-lg" aria-hidden="true"></i>
                    <p><small>Recepción de Mercancía</small></p>                    
                </a>
            </div>';

$clientes = '<div class="text-center">
                <a class="btn btn btn-light btn-square-lg" aria-current="page" href="clientes">                    
                    <i class="fa fa-users fa-lg" aria-hidden="true"></i>
                    <p><small>Clientes</small></p>                    
                </a>
            </div>';

$productos = '<div class="text-center">
            <a class="btn btn btn-light btn-square-lg" aria-current="page" href="productos">
                <i class="fab fa-dropbox fa-lg"></i>
                <p><small>Productos</small></p>                    
            </a>
        </div>';


$proveedores = '<div class="text-center">
            <a class=" btn btn btn-light btn-square-lg" aria-current="page" href="proveedores">                    
                <i class="fas fa-truck-moving fa-lg"></i>
                <p><small>Proveedores</small></p>                    
            </a>
        </div>';

$inventarios = '<div class="text-center">
        <a class=" btn btn btn-light btn-square-lg" aria-current="page" href="inventarios">                    
            <i class="fas fa-boxes fa-lg"></i>
            <p><small>Inventarios</small></p>                    
        </a>
    </div>';

$reportes = '<div class="text-center">
        <a class=" btn btn btn-light btn-square-lg" aria-current="page" href="reportes">                    
            <i class="fas fa-file-invoice fa-lg"></i>
            <p><small>Reportes</small></p>                    
        </a>
    </div>';

$utilerias = '<div class="text-center">
    <a class=" btn btn btn-light btn-square-lg" aria-current="page" href="utilerias">                    
        <i class="fas fa-cogs fa-lg"></i>
        <p><small>Utilerias</small></p>                    
    </a>
</div>';

$salir = '<div class="text-center">
    <a class=" btn btn btn-light btn-square-lg" aria-current="page" href="salir">                    
        <i class="fa-solid fa-right-from-bracket fa-lg"></i>
        <p><small>Utilerias</small></p>                    
    </a>
</div>';

$configuracion = '<a class="" aria-current="page" href="configuracion"><button style="width:100px;" type="button" class="btn btn-light  btn-square-lg"><i class="fa fa-cogs fa-2x" aria-hidden="true"></i>
<br><br>config</button></a>';

$facturas = '<div class="text-center"><a class="" aria-current="page" href="facturas"><button style="width:100px;" type="button" class="btn btn-light  btn-square-lgr"><i class="fas fa-file-invoice-dollar fa-2x"></i>
<br><br>Facturas</button></a></div>';

$cartaPorte = '<div class="text-center">
    <a class="btn btn btn-light btn-square-lg" aria-current="page" href="cartas-porte">                    
        <i class="fas fa-file-alt fa-lg"></i>
        <p><small>Cartas Porte</small></p>                    
    </a></div>';

if (isset($nav)) {
    switch ($nav) {
        case 'cartas-porte':
            $cartaPorte = '<div class="text-center">
                <a class="active btn btn btn-secondary btn-square-lg" aria-current="page" href="cartas-porte">                    
                    <i class="fas fa-file-alt fa-lg"></i>
                    <p><small>Cartas Porte</small></p>
                </a>
            </div>';
            break;

        case 'recepcion':
            $entradasAlmacenista = '<div class="text-center">
                <a class="active btn btn btn-secondary btn-square-lg" aria-current="page" href="recepcion">                    
                    <i class="fa fa-users fa-lg" aria-hidden="true"></i>
                    <p><small>Recepción de Mercancía</small></p>                    
                </a>
            </div>';
            break;

        //         case 'facturas':
        //             $facturas = '<a class="active" aria-current="page" href="facturas"><button style="width:100px;" type="button" class="btn btn-secondary  btn-square-lg"><i class="fas fa-file-invoice-dollar fa-2x"></i>
        // <br><br>Facturas</button></a>';
        //             break;

        case 'inicio':
            $inicio = "";
            break;

        case 'clientes':
        case 'clientes-altas':
        case 'clientes-editar':
        case 'clientes-modificar':
        case 'devoluciones':
        case 'devoluciones-altas':
        case 'pedidos':
        case 'pedidos-altas':
        case 'pedidos-editar':
        case 'facturas';
        case 'facturas-altas':
        case 'facturas-editar':
        case 'remisiones':
        case 'remisiones-altas':
        case 'remisiones-editar':
        case 'cxc':
        case 'cxc-altas':
        case 'cxc-editar':
            $clientes = '<div class="text-center">
                <a class="active btn btn btn-info btn-square-lg" aria-current="page" href="clientes">                    
                    <i class="fa fa-users fa-lg" aria-hidden="true"></i>
                    <p><small>Clientes</small></p>                    
                </a>
            </div>';
            break;

        case 'productos':
        case 'almacen-entradas':
        case 'almacen-entradas-altas':
            $productos = '<div class="text-center">
                <a class="active btn btn btn-info btn-square-lg" aria-current="page" href="productos">                    
                    <i class="fab fa-dropbox fa-lg"></i>
                    <p><small>Productos</small></p>                    
                </a>
            </div>';
            break;

        case 'proveedores':
            $proveedores = '<div class="text-center">
                <a class="active btn btn btn-info btn-square-lg" aria-current="page" href="proveedores">                    
                    <i class="fas fa-truck-moving fa-lg"></i>
                    <p><small>proveedores</small></p>                    
                </a>
            </div>';


            break;

        case 'reportes':
            $reportes = '<div class="text-center">
                <a class="active btn btn btn-info btn-square-lg" aria-current="page" href="reportes">                    
                    <i class="fas fa-file-invoice fa-lg"></i>
                    <p><small>Reportes</small></p>                    
                </a>
            </div>';
            break;

        case 'inventarios':
            $inventarios = '<div class="text-center">
                <a class="active btn btn btn-info btn-square-lg" aria-current="page" href="inventarios">                    
                    <i class="fas fa-boxes fa-lg"></i>
                    <p><small>Inventarios</small></p>                    
                </a>
            </div>';
            break;

        case 'utilerias':
        case 'categorias':
        case 'almacenes':
        case 'almacen-altas':
        case 'almacen-modificar':
        case 'unidades-medida':
        case 'usuarios':
        case 'usuarios-pwd':
        case 'vendedores':
        case 'vendedores-editar':
        case 'vendedores-modificar':
        case 'empresa':
        case 'comisiones':
        case 'tipo-cliente':
            $utilerias = '<div class="text-center">
                <a class=" active btn btn btn-info btn-square-lg" aria-current="page" href="utilerias">                    
                    <i class="fas fa-cogs fa-lg"></i>
                    <p><small>Utilerias</small></p>                    
                </a>
            </div>';
            break;

            /*
            $clientes='active';  
            $listClientes='active';
            $menuClientes='menu-open';
        break;        
                                 
            $definicion_documentos='active';  
            $listClientes='active';
            $menuClientes='menu-open';
        break;
        */
    }
}

$rolUsuarios    = 0;
$rolClientes    = 0;


/* Selecciona los permisos para el usuario */
//$con="SELECT * FROM accesos where id='".$_SESSION['id_user']."'";
//  echo $con;
/*
$rs=$clsConsulta->consultaGeneral($con); 				
foreach($rs as $v=>$val){	
	$rolUsuarios				=$val['usuarios'];
    $rolClientes				=$val['clientes'];
    
}
*/
