<li class="nav-item <?php echo $menuConfig; ?>">
    <a href="#" class="nav-link <?PHP echo $usuarios; ?>">
      <i class="nav-icon fas fa-cogs"></i>
      <p>
        Configuración
        <i class="right fas fa-angle-left"></i>
      </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item"> <!-- Accesos -->
            <a href="usuarios" class="nav-link <?PHP echo $usuarios; ?>">
                <i class="nav-icon far fa-circle text-default"></i>
                <p>Usuarios</p>
            </a>
        </li>
        <!--li class="nav-item"> 
            <a href="clientes" class="nav-link <?PHP echo $ruta.$roles; ?>">
                <i class="nav-icon far fa-circle text-default"></i>
                <p>Roles</p>
            </a>
        </li-->
    </ul>
</li>
<li class="nav-item <?php echo $menuClientes; ?>">
    <a href="#" class="nav-link <?PHP echo $clientes; ?>">
      <i class="nav-icon fas fa-users"></i>
      <p>
        Clientes
        <i class="right fas fa-angle-left"></i>
      </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item"> 
            <a href="clientes" class="nav-link active">
                <i class="nav-icon far fa-circle text-default"></i>
                <p>Clientes</p>
            </a>
        </li>
        <!--li class="nav-item"> 
            <a href="clientes" class="nav-link <?PHP echo $ruta.$roles; ?>">
                <i class="nav-icon far fa-circle text-default"></i>
                <p>Roles</p>
            </a>
        </li-->
    </ul>
</li>





<!--li class="nav-item"> <!-- Catalogo de personal -->
    <!--a href="personal" class="nav-link <? // echo $personal; ?>">
        <i class="nav-icon far fa-circle text-default"></i>
        <p>Personal</p>
    </a>
</li-->

