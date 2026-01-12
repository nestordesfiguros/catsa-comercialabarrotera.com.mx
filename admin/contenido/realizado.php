<?php
if(isset($cat)){
    $documento=$cat;
}
?>
<div class="ms-5">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item" aria-current="page">Inicio</li>    
        <li class="breadcrumb-item active" aria-current="page"> Gracias </li>
      </ol>
    </nav>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-6">
                <div class="card ">                                        
                    <div class="card-header">
                        <h5 class=""><i class="fa fa-calculator" aria-hidden="true"></i> &nbsp; <?php echo $documento; ?>  </h5>                         
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body text-center">
                        <h2>Se han realizado los cambios</h2>
                        <h3>Agradecemos tu colaboración</h3>
                        <div class="content text-center mt-5">
                            <p class="h4">Esta página se redireccionará en <b class="text-secondary" id="seg">15</b> segundos. ó presiona <a class="text-decoration-none link-primary" href="http://petrea-jornales.mustango.com.mx">aquí</a> para ir a petrea capital</p>  
                      </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript"> 
    $(document).ready(function () { 
        setInterval('contador()',1000)
    });
    var num=15; 
    function contador() {         
      num--; 
      if(num==0) location='http://petrea-jornales.mustango.com.mx'; 
      document.getElementById('seg').innerHTML=num; 
      
    } 
</script> 