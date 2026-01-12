<!-- Navbar Search -->

     <ul class="navbar-nav">
         <li class="nav-item d-none d-sm-inline-block">
             <form method="post" action="buscar-clientes">
                <div class="input-group">
                  <input onkeyup="javascript:this.value=this.value.toUpperCase();" type="text" name="cliente" class="form-control border border-info" placeholder="Clientes" aria-label="Recipient's username" aria-describedby="basic-addon2" required>
                  <div class="input-group-append">
                    <button class="btn btn-info" type="submit"> <i class="fa fa-search" aria-hidden="true"></i>
 Buscar</button>&nbsp; &nbsp; &nbsp;
                  </div>
                </div>
             </form>            
         </li>

         <li class="nav-item d-none d-sm-inline-block ms-5">
             <form method="post" action="buscar-productos">
                <div class="input-group">
                  <input onkeyup="javascript:this.value=this.value.toUpperCase();" type="text" name="producto" class="form-control border border-primary" placeholder="Productos" aria-label="Recipient's username" aria-describedby="basic-addon2" required>
                  <div class="input-group-append">
                    <button class="btn btn-primary" type="submit"> <i class="fa fa-search" aria-hidden="true"></i>
 Buscar</button>
                  </div>
                </div>
             </form>            
         </li>
     </ul>


     <!-- /. Navbar Search -->

<!-- #################################################  -->
<!-- #########  MOVIL -->
<!-- #################################################  -->

 <div class="row">
     <div class="row d-xs-block d-sm-none ps-3">
         <form method="post" action="buscar-clientes">
            <div class="input-group">
              <input onkeyup="javascript:this.value=this.value.toUpperCase();" type="text" name="cliente" class="form-control border border-info" placeholder="Clientes" aria-label="Recipient's username" aria-describedby="basic-addon2" required>
              <div class="input-group-append">
                <button class="btn btn-info" type="submit"> <i class="fa fa-search" aria-hidden="true"></i>
Buscar</button>&nbsp; &nbsp; &nbsp;
              </div>
            </div>
         </form>            
     
         <form method="post" action="buscar-productos">
            <div class="input-group">
              <input onkeyup="javascript:this.value=this.value.toUpperCase();" type="text" name="producto" class="form-control border border-primary" placeholder="Productos" aria-label="Recipient's username" aria-describedby="basic-addon2" required>
              <div class="input-group-append">
                <button class="btn btn-primary" type="submit"> <i class="fa fa-search" aria-hidden="true"></i>
Buscar</button>
              </div>
            </div>
         </form>            
     </div>
 </div>
<br>