<?php
	define("DB_HOST", "localhost:3308");
	define("DB_USR", "root");
	define("DB_PASS", "hola");
	define("DB_DB", "voyentaxi");
	//define(DB_TYPE, "mysql");

	$template_config = 
    array(
        'template_dir' => 'vistas/',
        'compile_dir' => 'libs/smarty/templates_c/',
        'cache_dir' => 'libs/smarty/cache/',
        'config_dir' => 'libs/smarty/configs/',
        );
    define ("URL_BASE","/VoyEnTaxiWS/");
?>