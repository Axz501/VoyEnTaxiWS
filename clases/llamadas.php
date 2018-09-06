<?php 
require_once("clases/clase_base.php");
require_once("db/db.php");
class Llamadas extends ClaseBase{
	private $id;
	private $cedulaUsuario;
	private $estado;
	private $longitud_inicial;
	private $longitud_final;
	private $latitud_inicial;
	private $latitud_final;
	private $fecha_hora_inicial;
	private $fecha_hora_final;
	private $url;
	private $url_llamada;
	private $url_finalizada;

	/* Constructor */

	public function __construct($obj=NULL){
		if(isset($obj)){
			foreach ($obj as $key => $value) {
				$this->$key=$value;
			}
		}
		$tabla = "llamadas";

		parent::__construct($tabla);
	}

	public function getid(){
		return $this->id;
	}

	public function getEstado(){
		return $this->estado;
	}

	public function getUrl(){
		return $this->url;
	}

	public function getUrlllamada(){
		return $this->url_llamada;
	}

	public function getUrlfinalizada(){
		return $this->url_finalizada;
	}

	public function getCedula(){
		return $this->cedulaUsuario;
	}

	public function getDateI(){
		return $this->fecha_hora_inicial;
	}

	public function getDateF(){
		return $this->fecha_hora_final;
	}

	public function getLongitudI(){
		return $this->longitud_inicial;
	}

	public function getLongitudF(){
		return $this->longitud_final;
	}

	public function getLatitudI(){
		return $this->latitud_inicial;
	}

	public function getLatitudF(){
		return $this->latitud_final;
	}

	public function InicioLlamada(){
		$stmt = DB::conexion()->prepare("INSERT INTO llamadas (cedulaUsuario,estado,fecha_hora_inicial,latitud_inicial,longitud_inicial,url_llamada,url_finalizada) 
			VALUES(?,?,?,?,?,?,?)");
		$userid = $this->getCedula();
		$lat = $this->getLatitudI();
		$long = $this->getLongitudI();
		$date = $this->getDateI();
		$url_fin = $this->getUrlfinalizada();
		$url = $this->getUrlllamada();
		$estado = 1;
		$stmt->bind_param("iisssss",$userid,$estado,$date,$lat,$long,$url,$url_fin);
		$stmt->execute();

		$stmt = DB::conexion()->prepare("SELECT max(id) as id FROM llamadas");
		$stmt->execute();
		$resultado = $stmt->get_result();
		return $resultado->fetch_object()->id;
	}

	public function Estadisticas(){
		// $id = "l123";
		// $idllamada = '666';
		// $stmt = DB::conexion()->query("SET GLOBAL event_scheduler = ON");
		// if ($stmt = DB::conexion()->query("CREATE EVENT IF NOT EXISTS ".$id."
		// 		ON SCHEDULE AT CURRENT_TIMESTAMP + interval 100 second
		// 		DO
		// 		DELETE FROM usuarios_session WHERE session_id = ".$idllamada.";")){
		// 	//$stmt->execute();
		// }
		$resultados = array();
		$anios=array();
		$meses=array();
		$horas=array();
		if ($stmt = DB::conexion()->prepare("SELECT SUBSTRING(fecha_hora_inicial,1,7) as mes, count(SUBSTRING(fecha_hora_inicial,1,7)) as cantidad from llamadas group by mes;")){
			$stmt->execute();
			$resultado = $stmt->get_result();
			while ( $fila = $resultado->fetch_object()){
				$meses[$fila->mes] = $fila->cantidad;
			}
		}
		if ($stmt = DB::conexion()->prepare("SELECT SUBSTRING(fecha_hora_inicial,1,4) as anio, count(SUBSTRING(fecha_hora_inicial,1,4)) as cantidad from llamadas group by anio;")){
			$stmt->execute();
			$resultado = $stmt->get_result();
			while ( $fila = $resultado->fetch_object()){
				$anios[$fila->anio] = $fila->cantidad;
			}
		}
		if ($stmt = DB::conexion()->prepare("SELECT SUBSTRING(fecha_hora_inicial,12,2) as hora, count(SUBSTRING(fecha_hora_inicial,12,2)) as cantidad from llamadas group by hora;")){
			$stmt->execute();
			$resultado = $stmt->get_result();
			while ( $fila = $resultado->fetch_object()){
				$horas[$fila->hora] = $fila->cantidad;
			}
		}
		$resultados["meses"] = $meses;
		$resultados["anios"] = $anios;
		$resultados["horas"] = $horas;
		return $resultados;

	}

	public function FinalizarLLamada(){
		if ($stmt = DB::conexion()->prepare("SELECT * FROM llamadas WHERE id=$this->id")){
			$stmt->execute();
			$resultado = $stmt->get_result();
			if ($resultado->num_rows<1)
				return 0;
			else{
				if ($resultado->fetch_object()->estado==0)
					return 1;
			}
		}
		if ($stmt = DB::conexion()->prepare("UPDATE llamadas SET estado=0,fecha_hora_final=?,latitud_final=?,longitud_final=?,url_video=? WHERE id=$this->id")){
			$stmt->bind_param("ssss",$this->fecha_hora_final,$this->latitud_final,$this->longitud_final,$this->url);
			$stmt->execute();
			return 2; 
		} 
	}

	public function CambiarClaveTokBox($params){
		$stmt = DB::conexion()->prepare("DELETE FROM credenciales_opentok");
		$stmt->execute();
		$stmt = DB::conexion()->prepare("INSERT INTO credenciales_opentok (api_key,secret_key) VALUES(?,?)");
		$stmt->bind_param("ss",$params[0],$params[1]);
		$stmt->execute();
		return true;
	}



	public function ListadoLlamadas(){
		if ($stmt = DB::conexion()->prepare("SELECT id,cedula, concat(nombre,' ',apellido) as nombre,telefono,email,fecha_hora_inicial,latitud_inicial,longitud_inicial, s.session_id, s.token FROM llamadas as l, voyentaxi_usuarios as u, usuarios_session as s 
			WHERE estado = 1 AND l.cedulaUsuario = u.cedula AND u.cedula = s.cedulaUsuario")){
			$stmt->execute();
			$resultado = $stmt->get_result();
			$resultados=array();
			while ( $fila = $resultado->fetch_object()){
				$resultados[] = $fila;
			}
			return $resultados;
		}
	}

	public function ListadoLlamadasFinalizadas(){
		if ($stmt = DB::conexion()->prepare("SELECT id,cedula, concat(nombre,' ',apellido) as nombre,telefono,email,fecha_hora_inicial,fecha_hora_final,latitud_inicial,latitud_final,longitud_inicial,longitud_final,url_video FROM llamadas as l, voyentaxi_usuarios as u 
			WHERE estado = 0 AND l.cedulaUsuario = u.cedula")){
			$stmt->execute();
			$resultado = $stmt->get_result();
			$resultados=array();
			while ( $fila = $resultado->fetch_object()){
				$resultados[] = $fila;
			}
			return $resultados;
		}
	}

}

?>