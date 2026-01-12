

<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="inicio" class="brand-link bg-dark">
      <img src="img/petrea_logo_sencillo.png" alt="Jornales Petrea Capital"  >
      <span class="brand-text font-weight-light"><b class="h3">Jornales </b> Petrea</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block"><?PHP echo $nombre; ?></a>
        </div>
      </div>
     
      <!-- SidebarSearch Form -->
      <!--div class="form-inline">
        <div class="input-group" data-widget="sidebar-search">
          <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
          <div class="input-group-append">
            <button class="btn btn-sidebar">
              <i class="fas fa-search fa-fw"></i>
            </button>
          </div>
        </div>
      </div-->

      <!-- Sidebar Menu -->
      <nav class="mt-2">        
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <?PHP
          /* Selecciona el modulo según los permisos seleccionados*/
            /*
          if($administrador==1){
            include 'php/mod_administrador.php';
          }
          */                        
          
          ?>
            <li class="nav-item <?php echo $menuConfig; ?>">
                <a href="#" class="nav-link <?PHP echo $usuarios; ?>">
                  <i class="nav-icon fas fa-cogs"></i>
                  <p>
                    Configuración
                    <i class="fas fa-angle-left right"></i>
                    <!--span class="badge badge-info right">6</span-->
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="usuarios" class="nav-link <?PHP echo $usuarios; ?>">
                      <i class="far fa-circle nav-icon"></i>
                      <p>usuarios</p>
                    </a>
                  </li>
                  <!--li class="nav-item">
                    <a href="pages/layout/top-nav-sidebar.html" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Top Navigation + Sidebar</p>
                    </a>
                  </li-->                  
                </ul>
            </li>
            <!-- Cleientes -->
            <li class="nav-item <?php echo $menuClientes; ?>">
                <a href="#" class="nav-link <?PHP echo $listClientes; ?>">
                  <i class="nav-icon fas fa-users"></i>
                  <p>
                    Clientes
                    <i class="fas fa-angle-left right"></i>
                    <!--span class="badge badge-info right">6</span-->
                  </p>
                </a>
                <ul class="nav nav-treeview ">
                    <li class="nav-item">
                    <a href="clientes-documentos" class="nav-link <?PHP echo $definicion_documentos; ?>">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Definición de documentos</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="clientes" class="nav-link <?PHP echo $clientes; ?>">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Clientes</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="clientes-presupuestos" class="nav-link <?PHP echo $presupuestos; ?>">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Presupuestos</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="clientes-presupuestos" class="nav-link <?PHP echo $contratos; ?>">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Contratos</p>
                    </a>
                  </li>
                  <!--li class="nav-item">
                    <a href="pages/layout/top-nav-sidebar.html" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Top Navigation + Sidebar</p>
                    </a>
                  </li-->                  
                </ul>
            </li>
            <!-- Salir -->    
            <li class="nav-item">
                <a href="salir" class="nav-link">
                  <i class="nav-icon fas fa-power-off text-default"></i>
                  <p>Salir</p>
                </a>
            </li>
            
        </ul>
      </nav>
      <!-- /.sidebar -->
  </div>
</aside>