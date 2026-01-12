<div class="ms-5">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="pruebas">Pruebas</a></li>
    </ol>
  </nav>
</div>



<div class="container">

  <div class="row col-12">
    <div class="form-group">
      <div class="form-outline">
        <input type="text" id="fecha" class="form-control" />
        <label class="form-label" for="fecha">Fecha</label>
      </div>
    </div>
  </div>
  <div>
    <?php
    function generarClaveLicencia($longitud = 64)
    {
      $caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
      $clave = '';
      for ($i = 0; $i < $longitud; $i++) {
        $clave .= $caracteres[rand(0, strlen($caracteres) - 1)];
      }
      return $clave;
    }

    $clave = generarClaveLicencia();
    echo $clave;

    ?>
  </div>







  <!--div class="row">
      <div class="col-12 text-center">
        <iframe src="verpdf/samples/viewing/viewing/index.html"></iframe>
      </div>
  </div-->

  <!-- Button trigger modal -->
  <!--button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
    Ver archivos docx y xmlx en pdf
  </button-->

  <!-- Modal -->
  <!--div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <iframe src="verpdf/samples/viewing/viewing/index.php?file=30" height="900px" width="100%"></iframe>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary">Save changes</button>
        </div>
      </div>
    </div>
  </div-->

</div>

<script>
  /*
let date = new Date()

let day = date.getDate()
let month = date.getMonth() + 1
let year = date.getFullYear()

if(month < 10){
  fecha=day+'-0'+month+'-'+year;
}else{
  fecha=day+'-'+month+'-'+year;
}
$("#fecha").val(fecha);
console.log(fecha);
$( "#autocomplete" ).autocomplete({
  source: [ "c++", "java", "php", "coldfusion", "javascript", "asp", "ruby" ]
});
*/
</script>