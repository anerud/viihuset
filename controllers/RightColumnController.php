<?php
require_once("interfaces/iController.php");

final class RightColumnController implements iController {

	private $dbContext;

	public function init($router, $dbContext){
		$this->dbContext = $dbContext;

		//Brf page
		$router->map('POST', '/rightcol/toggleVisibility',
			function() {
				$user = $_SESSION["user"];
				$this->dbContext->toggleShowRightCol($user->brf);
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];}
		);
	}

    public function getLinksByLevel($level, $endpointLevel, $brf, $domain) {
		return null;
	}

}

?>
