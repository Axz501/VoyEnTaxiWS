<?php
require "clases/clase_base.php";
require "clases/usuario.php";
require_once('clases/template.php');
require_once('clases/Utils.php');
require_once('clases/session.php');
require_once('clases/auth.php');
require_once('clases/llamadas.php');

class ControladorUsuario extends ControladorIndex {

public function loginWS($params = array()){

		$usuario = new Usuario(array("cedula" => $params[0],
									"contrasenia" => $params[1]));
		$res = $usuario->login();
		if ($res==false)
			return json_encode(array("result" => false));
		else{
			Session::init();
			Session::set("userid",$res->getCedula());
			return json_encode(array("result"=>true,"cedula" => $res->getCedula(),"telefono" => $res->getTelefono(),"email" => $res->getEmail(),"nombre" => $res->getNombre(),"apellido" => $res->getApellido()));
		}
	}

	public function ListadoUsuarios(){
		$usuario = new Usuario();
		$res = $usuario->ListadoUsuarios();
		return json_encode($res);
	}

	public function SesionAlerta($params){

		$usuario = new Usuario();
		Session::init();
		if ($params[0] == Session::get("userid") && $params[1]!="" && $params[2]!=""){ 
			$res = $usuario->insertarSesion($params);
			return json_encode(array("result"=>true,"message"=>$res), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		}
		else
			return json_encode(array("result"=>false,"message"=>"Login Requerido"));	
	}

	public function DatosLlamada($params){
		Session::init();
		if ($params[0] == Session::get("userid") && $params[1]!="" && $params[2]!="" && $params[3]!="" && $params[4]!="" && $params[5]!=""){
			$date=date_create($params[1]);
			if ($date == false){
				return json_encode(array("result"=>false,"mensaje"=>"Fecha Inválida"),JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			} elseif (strpos($params[2], '.') === false || strpos($params[3], '.') ===false) {
				return json_encode(array("result"=>false,"mensaje"=>"Coordenadas Inválidas"),JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			}
			else{
				$fecha = date_format($date,"Y/m/d H:i:s");
				$llamada = new Llamadas(array("cedulaUsuario"=>$params[0],"estado"=>true,"fecha_hora_inicial"=>$fecha,
											"latitud_inicial"=>$params[2],"longitud_inicial"=>$params[3],"url_llamada"=>$params[4]
											,"url_finalizada"=>$params[5]));
				$res = $llamada->InicioLlamada();
				return json_encode(array("result"=>true,"ID Llamada"=>$res));
			}
		}
		else
			return json_encode(array("result"=>false,"message"=>"Login Requerido"));	
	}

	public function FinLlamada($params){
		if ($params[0] != "" && $params[1]!="" && $params[2]!="" && $params[3]!="" && $params[4]!=""){
			$date=date_create($params[2]);
			if ($date == false){
				return json_encode(array("result"=>false,"mensaje"=>"Fecha Inválida"),JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			} elseif (strpos($params[3], '.') === false || strpos($params[4], '.') ===false) {
				return json_encode(array("result"=>false,"mensaje"=>"Coordenadas Inválidas"),JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			} else{
				$fecha = date_format($date,"Y/m/d H:i:s");
				$llamada = new Llamadas(array("id"=>$params[0],"url"=>$params[1],"fecha_hora_final"=>$fecha,
											"latitud_final"=>$params[3],"longitud_final"=>$params[4]));
				$res = $llamada->FinalizarLLamada();
				if ($res == 0){
					return json_encode(array("result"=>false,"message"=>"Llamada No Existente"));
				}
				if ($res == 1){
					return json_encode(array("result"=>false,"message"=>"Llamada Finalizada Previamente"));
				}
				if ($res == 2){
					return json_encode(array("result"=>true,"message"=>"Llamada Finalizada"));
				}
				}
			}
		else
			return json_encode(array("result"=>false));
	}

	public function CambiarClaveTokBox($params){
		if ($params[0] != "" && $params[1]!=""){
			$llamada = new Llamadas();
			$res = $llamada->CambiarClaveTokBox($params);
			if ($res)
				return json_encode(array("result"=>true,"message"=>"Credenciales Actualizadas"));
		}
		else
			return json_encode(array("result"=>false));
	}

	public function ListadoLlamadas(){
		$llamada = new Llamadas();
		$res = $llamada->ListadoLlamadas();
		return json_encode($res);
	}

	public function Estadisticas(){
		$llamada = new Llamadas();
		$res = $llamada->Estadisticas();
		return json_encode($res);
	}

	public function ListadoLlamadasFinalizadas(){
		$llamada = new Llamadas();
		$res = $llamada->ListadoLlamadasFinalizadas();
		return json_encode($res);
	}

/*Fin WS VoyEnTaxi*/

 function listado($params=array()){
 	
 	Auth::estaLogueado();

	$buscar="";
	$titulo="Listado";
	$mensaje="";
	if(!empty($params)){
		if($params[0]=="borrar"){
			$usuario=new Usuario();
			$idABorrar=$params[1];
	 		if($usuario->borrar($idABorrar)){
	 			//Redirigir al listado
	 			//header('Location: index.php');exit;
	 			$this->redirect("usuario","listado");
	 		}else{
	 			//Mostrar error
	 			$usr=$usuario->obtenerPorId($idABorrar);
	 			//$mensaje="Error!! No se pudo borrar el usuario  <b>".$usr->getNombre()." ".$usr->getApellido()."</b>";
	 			$mensaje="ERROR. No existe el usuario";
	 			$usuarios=$usuario->getListado();	
	 		}
		}else if($params[0]=="mail"){
	 		$usuario=new Usuario();
	 		$idAEnviar=$params[1];
	 		$usr=$usuario->obtenerPorId($idAEnviar);

	 		$utils=new Utils();
	 		$res=$utils->enviarEmail($usr->getEmail(),$usr->getNombre()." ".$usr->getApellido());	
	 		if($res){
	 			//Redirigir al listado
	 			$mensaje="Mail enviado!";
	 			$usuarios=$usuario->getListado();
	 		}else{
	 			$mensaje="Error!! No se pudo enviar email al usuario  <b>".$usr->getNombre()." ".$usr->getApellido()."</b>";
	 			$usuarios=$usuario->getListado();	
	 		}
	 	}else{
	 		$usuario=new Usuario();
			$usuarios=$usuario->getListado();	
	 	}
	}else{
 		$usuario=new Usuario();
		$usuarios=$usuario->getListado();	
 	}
	
	//Llamar a la vista
 	$tpl = Template::getInstance();
 	$datos = array(
    'usuarios' => $usuarios,
    'buscar' => $buscar,
    'titulo' => $titulo,
    'mensaje' => $mensaje,
    );

	$tpl->asignar('usuario_nuevo',$this->getUrl("usuario","nuevo"));
	$tpl->mostrar('usuarios_listado',$datos);

}
function buscar($params=array()){
 	
 	Auth::estaLogueado();

	$buscar="";
	$titulo="Listado";
	$mensaje="";
	$usuarios=array();
	if(isset($_POST["buscar"]) && $_POST["buscar"]!="" ){
			$titulo="Buscando..";
	 		$usuario=new Usuario();
	 		$buscar=$_POST["buscar"];
			$usuarios=$usuario->getBusqueda($buscar);	
	}else{
		$usuario=new Usuario();
		$usuarios=$usuario->getListado();
	}
 	
	//Llamar a la vista
	//require_once("vistas/usuarios_listado.php");
	
 	$tpl = Template::getInstance();
 	$datos = array(
    'usuarios' => $usuarios,
    'buscar' => $buscar,
    'titulo' => $titulo,
    'mensaje' => $mensaje,
    );

	
	$tpl->asignar('usuario_nuevo',$this->getUrl("usuario","nuevo"));
	$tpl->mostrar('usuarios_listado',$datos);

}

function nuevo(){
	$mensaje="";
	if(isset($_POST["nombre"])){
		$usr= new Usuario();
		$usr->setNombre($_POST["nombre"]);
		$usr->setApellido($_POST["apellido"]);
		$usr->setCI($_POST["ci"]);
		$usr->setEdad($_POST["edad"]);
		$usr->setEmail($_POST["email"]);
		if($usr->agregar()){
			$this->redirect("usuario","listado");
			exit;
		}else{
			$mensaje="Error! No se pudo agregar el usuario";
		}

		
	}
	$tpl = Template::getInstance();
	$tpl->asignar('titulo',"Nuevo Usuario");
	$tpl->asignar('buscar',"");
	$tpl->asignar('mensaje',$mensaje);
	$tpl->mostrar('usuarios_nuevo',array());

}
function login(){

	$mensaje="";
	session_start();
	
	if(isset($_POST["email"])){
		$usr= new Usuario();
		
		$email=$_POST["email"];
		$pass=sha1($_POST["password"]);

		if($usr->login($email,$pass)){
			$this->redirect("usuario","listado");
			exit;
		}else{
			$mensaje="Error! No se pudo agregar el usuario";
		}

		
	}
	$tpl = Template::getInstance();
	$tpl->asignar('titulo',"Nuevo Usuario");
	$tpl->asignar('loginUrl',$loginUrl);
	$tpl->asignar('buscar',"");
	$tpl->asignar('mensaje',$mensaje);
	$tpl->mostrar('usuarios_login',array());

}
function logout(){
	$usr= new Usuario();
	$usr->logout();
	$this->redirect("usuario","login");
}



function favoritos(){
	$usuario=new Usuario();
	$usuarios=$usuario->getListado();	
	echo json_encode($usuarios);
}

}
?>