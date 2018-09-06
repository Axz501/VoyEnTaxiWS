<?php

require_once('libs/Slim/Slim.php');
require_once('controladores/ctrl_index.php');
require_once('controladores/ctrl_usuario.php');

ini_set("default_socket_timeout", 6000);

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

$app->get(
	'/logout', function(){
		Session::init();
		Session::destroy();
		echo json_encode(array("result:"=>true));
	}
);

$app->get(
	'/login', function(){
		$request = \Slim\Slim::getInstance() -> request();
	
		$cedula = $request -> params('cedula');
		$contrasenia = $request -> params('contrasenia');

		$ctrl = new ControladorUsuario();
		echo $ctrl->loginWS(array($cedula, $contrasenia));
	}
);

$app->get(
	'/SesionVideoLlamada', function(){
		$request = \Slim\Slim::getInstance() -> request();
	
		$idsesion = "";
		$token = "";
		$iduser = $request -> params('userid');
		$idsesion = $request -> params('sessionid');
		$token = $request -> params('token');

		$ctrl = new ControladorUsuario();
		echo $ctrl->SesionAlerta(array($iduser, $idsesion,$token));
	}
);

$app->get(
	'/DatosLlamada', function(){
		$request = \Slim\Slim::getInstance() -> request();
	
		$fechahora = "";
		$latitud = "";
		$longitud = "";
		$iduser = $request -> params('userid');
		$fechahora = $request -> params('date');
		$latitud = $request -> params('latitud');
		$longitud = $request -> params('longitud');
		$url_llamada = $request -> params('url_llamada');
		$url_finalizar = $request -> params('url_finalizar');

		$ctrl = new ControladorUsuario();
		echo $ctrl->DatosLlamada(array($iduser, $fechahora,$latitud,$longitud,$url_llamada,$url_finalizar));
		
	}
);

$app->get(
	'/FinLlamada', function(){
		$request = \Slim\Slim::getInstance() -> request();
	
		$idllamada="";
		$fechahora = "";
		$latitud = "";
		$longitud = "";
		$url = "";
		$idllamada = $request -> params('callid');
		$fechahora = $request -> params('date');
		$latitud = $request -> params('latitud');
		$longitud = $request -> params('longitud');
		$url = $request -> params('url');

		$ctrl = new ControladorUsuario();
		echo $ctrl->FinLlamada(array($idllamada,$url,$fechahora,$latitud,$longitud));
		
	}
);

$app->get(
	'/ClavesTokBox', function(){
		$request = \Slim\Slim::getInstance() -> request();
	
		$apiKey="";
		$projectKey = "";
		$apiKey = $request -> params('apiKey');
		$projectKey = $request -> params('projectKey');

		$ctrl = new ControladorUsuario();
		echo $ctrl->CambiarClaveTokBox(array($apiKey,$projectKey));
		
	}
);

$app->get(
	'/VideollamadasActuales', function(){
		$request = \Slim\Slim::getInstance() -> request();

		$ctrl = new ControladorUsuario();
		echo $ctrl->ListadoLlamadas();
	}
);

$app->get(
	'/ListadoUsuarios',function(){
		$request = \Slim\Slim::getInstance() -> request();

		$ctrl = new ControladorUsuario();
		echo $ctrl->ListadoUsuarios();
	}
);

$app->get(
	'/VideollamadasFinalizadas', function(){
		$request = \Slim\Slim::getInstance() -> request();

		$ctrl = new ControladorUsuario();
		echo $ctrl->ListadoLlamadasFinalizadas();
	}
);

$app->get(
	'/Stats', function(){
		$request = \Slim\Slim::getInstance() -> request();

		$ctrl = new ControladorUsuario();
		echo $ctrl->Estadisticas();
	}
);

/*Fin WS Voy en Taxi*/

//getDatosPorNotificationId

$app->run();

?>