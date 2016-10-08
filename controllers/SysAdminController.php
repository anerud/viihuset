<?php
require_once("interfaces/iController.php");

final class SysAdminController implements iController{

	private $dbContext;

	public function init($router, $dbContext){

		$this->dbContext = $dbContext;

		$router->map('GET', '/sysadmin',
			function() {
				$allBrfs = $this->dbContext->getAllBrfs();
				require_once("views/sysadmin/SysAdminView.php");
				$sysAdminView = new SysAdminView($allBrfs, null);
				$sysAdminView->render();

			},
			NULL,
			function(){return UserLevels::$userLevels["sysadmin"];}
		);

		$router->map('GET', '/sysadmin/[a:brf]',
			function($brf) {
				$allBrfs = $this->dbContext->getAllBrfs();
				$currentBrf = $this->dbContext->getBrfInfo($brf);
				require_once("views/sysadmin/SysAdminView.php");
				$sysAdminView = new SysAdminView($allBrfs, $currentBrf);
				$sysAdminView->render();

			},
			NULL,
			function(){return UserLevels::$userLevels["sysadmin"];}
		);

		$router->map('POST', '/sysadmin/[a:brf]',
			function($brf) {
				echo $brf;
			},
			NULL,
			function(){return UserLevels::$userLevels["sysadmin"];}
		);

		$router->map('GET', '/sysadmin/becomebrf/[a:brf]',
			function($brf) {
				$user = $this->dbContext->getAdminForBrf($brf);
				$_SESSION["user"] = $user;
				header('Location: /hem');
			},
			NULL,
			function(){return UserLevels::$userLevels["sysadmin"];}
		);

	}

	public function getLinksByLevel($level, $endpointLevel, $brf, $domain){
		return null;
	}


}

?>
