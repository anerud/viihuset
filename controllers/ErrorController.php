<?php
require_once("interfaces/iController.php");

final class ErrorController implements iController{
	 
	public function init($router, $dbContext){
		
		$router->map('GET', '/errors/404', function() {
			
			$v = "views/error/Error404View.php";
			require_once($v);
			$v = "Error404View";
			$classObject = new $v;
			$classObject->render();
			
		});
		
	}
    
    public function getLinksByLevel($level, $endpointLevel, $brf, $domain){
		
	}
	
}

?>