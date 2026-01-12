<?PHP
//sleep(3);
include 'lib/clsClaves.php';
$clsClaves=new claves();
//$url=$_REQUEST['url'];
$post=$_POST; // Obtiene los valores pasados por post
$hora = date("H:i:s",time());
$fecha= date("Y/m/d"); 
$tabla=$_REQUEST['tabla'];

// var_dump($_POST);

/* ############################################# A L T A S   */
if(isset($_REQUEST['fin']) && $_REQUEST['fin']=='altas'){    
	$a=$clsConsulta->guardar($tabla,$_POST);
    $id=$clsConsulta->ultimoid;
    if($tabla=='usuarios'){$url="usuarios-editar/".$id."/1";}

}
/* ############################################# M O D I F I C A R   */
if(isset($_POST['fin']) && $_POST['fin']=='modificar'){   
 //    echo 'Entro UNO ***<br>';
	$id=$_POST['id'];	
    if($tabla=='usuarios'){
        $correo=strtolower($_POST['usr']);
        $con="SELECT * FROM usuarios WHERE usr='".$correo."'";
     //    echo $con.'<br>';
        $rs=$clsConsulta->consultaGeneral($con);
        if($clsConsulta->numrows>0){  
            foreach($rs as $v=>$val){
                $mail=strtolower($val['usr']);
                $idusr=$val['id'];
            }
            echo $mail.' --> '.$correo.' / '.$id.' --> '.$idusr.'<br>';
            if($mail==$correo && $id==$idusr){
            //    $url="usuarios-editar/".$id."/1";
            //    echo 'Entro UNO ***<br>';
                $clsConsulta->modificar($post,$tabla,$id);
            }else{
                $url="usuarios-editar/".$id."/2";            
            }
            
        }
    }else{
     //    echo 'Entro DOS ***<br>';
        $clsConsulta->modificar($post,$tabla,$id);    
    }
	
}

/* ############################################# B A J A   */
if(isset($_POST['fin']) && $_POST['fin']=='baja'){      
	$id=$_POST['id'];	
	$clsConsulta->baja($tabla,$id);
}

/* ############################################# B O R R A R  */
if(isset($_REQUEST['fin']) && $_REQUEST['fin']=='borrar'){	
	$id=$_REQUEST['id'];
	$a=$clsConsulta->borrar($tabla,$id);
}

if(isset($_REQUEST['fin']) && $_REQUEST['fin']=='pwd'){
	$id=$_REQUEST['id'];
	if($_REQUEST['pwd']==$_REQUEST['pwd2']){
		$clave=$_REQUEST['pwd'];
		$tabla=$_REQUEST['tabla'];
		$id=$_REQUEST['id'];
		$usr=$_REQUEST['usr'];
		$pwd=$clsClaves->codificaPwd($usr,$clave);
		$rs=$clsConsulta->presentaDetalleid($tabla,'id',$id);  // consulta la tabla cuando el id tiene otro nombre
		if($clsConsulta->numrows > 0){
			$con="UPDATE ".$tabla." SET usr='".$usr."', clave='".$clave."', pwd='".$pwd."'  WHERE id=".$id;
			$clsConsulta->aplicaQuery($con);
		}else{			
			$con="INSERT INTO ".$tabla." (id, usr, pwd, clave, ) VALUES (".$id.", '".$usr."', '".$pwd."', '".$clave."')";
			$clsConsulta->aplicaQuery($con);
		}
		$url=$_REQUEST['url'].'/'.$_REQUEST['id'].'/pwdsi';
	//	$clsConsulta->modificar($post,$tabla,$id);
	//	$con=$clsConsulta->consulta;
	//	echo $con;	
		unset ($_SESSION['correo']);
	}else{
		$url=$_REQUEST['url'].'/'.$_REQUEST['id'].'/errpwd';
		$_SESSION['correo']=$_REQUEST['usr'];
	}
}

if(isset($_REQUEST['fin']) && $_REQUEST['fin']=='accesos'){
	$id=$_REQUEST['id'];
	$clsConsulta->accesos($post,$tabla,$id);
//	$con=$clsConsulta->consulta;
//	echo $con;	
}

?>

