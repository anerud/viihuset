<?php
require_once("interfaces/iController.php");

final class BasicInfoController implements iController{

	private $dbContext;

	public function init($router, $dbContext){

		$this->dbContext = $dbContext;

		//Brf info page
		$router->map('GET', '/basinfo',
			function() {
				$user = $_SESSION["user"];
				$brf = $user->brf;
				$brfInfo = $this->dbContext->getBrfInfo($brf);

				$nVisitors = $this->dbContext->getVisitorsByBrf($brf);
				require_once("views/settings/BasicInfoView.php");
				$view = new BasicInfoView($brfInfo, $nVisitors, getErrors());
				$view->render();
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];},
			null,
			true,
			"settings"
		);

		//Brf info page
		$router->map('POST', '/basinfo',
			function() {
				$user = $_SESSION["user"];
				$brf = $user->brf;

				$email = $_POST["email"];
				$brfAddress = $_POST["brfAddress"];
				$brfPostal = $_POST["brfPostal"];
				$visitAddress = $_POST["visitAddress"];
				$visitPostal = $_POST["visitPostal"];
				$city = $_POST["city"];

				$this->dbContext->updateBasicInfo(
					$brf,
					$email,
					$brfAddress,
					$brfPostal,
					$visitAddress,
					$visitPostal,
					$city
				);

				header("Location: /basinfo");
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];}
		);

	}

    public function getLinksByLevel($level, $endpointLevel, $brf, $domain){
		$links = array();

		if($domain != "settings") {
			return $links;
		}

		if($level < UserLevels::$userLevels["admin"]){
			return $links;
		}

		array_push($links,
				array(
					"title" => "Basinformation",
					"url" => "/basinfo",
					"position" => "navbar",
					"sublinks" => array(),
					"class" => " bas ",
					"sortindex" => 0
				));


		return $links;
	}

}

?>
