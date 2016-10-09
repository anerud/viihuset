<?php
require_once("interfaces/iController.php");

final class LoginController implements iController{

	private $dbContext;

	public function init($router, $dbContext){

		$this->dbContext = $dbContext;

		$router->map('GET', '/logout',
			function() {
				# Destroy session
				session_destroy();

				# If user is set redirect to home page of user
				if (!isset($_SESSION["user"])){
						header("Location: /");
				} else {
					$user = $_SESSION["user"];

					# redirect sysadmin to sales view
					if (UserLevels::$userLevels[$user->userlevel] == UserLevels::$userLevels["sysadmin"]) {
						header("Location: /");
						return;
					}

					# Redirect user to home page of user
					$brf = $user->brf;
					$location = $brf == null ? '' : $brf."/hem";
					header("Location: /".$location);
				}
			},
			"",
			0,
			"Logga ut",
			false
		);

		$router->map('GET', '/auth', function() {

			$view = "views/login/LoginView.php";
			require_once($view);
			$view = "LoginView";
			$viewObject = new $view();
			$viewObject->render();

		},"",0,"Logga in",true,"sales");

		$router->map('GET', '/[a:brf]/auth', function() {

			$view = "views/login/LoginView.php";
			require_once($view);
			$view = "LoginView";
			$viewObject = new $view();
			$viewObject->render();

		},
		NULL,
		function(){return UserLevels::$userLevels["brf"];});

		$router->map('POST', '/auth', function() {

			$username = normalize($_POST["log_user"]);
			$password = $_POST["log_pass"];
			$user = $this->dbContext->getUserByUsernameBrfOrEmail($username, $password);

			if($user == NULL) {
				session_destroy();
				$errors[] = "Adress eller lösenord är felaktigt.";
				$view = "views/login/LoginView.php";
				$_POST["returnUrl"] = isset($_GET["returnUrl"]) ? $_GET["returnUrl"] : (isset($_POST["returnUrl"]) ? $_POST["returnUrl"] : false);

				require_once($view);
				$viewObject = new LoginView($errors);
				$viewObject->render();
			} else{
				$_SESSION["user"] = $user;
				$brf = $user->brf;
				$location = UserLevels::$userLevels[$user->userlevel] == UserLevels::$userLevels["sysadmin"]
					? '/sysadmin'
					: "/".$brf."/hem";
				header("Location: ".$location);
			}
		});

	}

	public function getLinksByLevel($level, $endpointLevel, $brf, $domain){
		$links = array();

		if($domain != "sales" && $domain != "portal") {
			return $links;
		}

		if($level == UserLevels::$userLevels["brf"]){
			array_push($links,
				array(
					"title" => "Logga in här",
					"url" => "/<currentBrf>/auth",
					"position" => "navbar|footer",
					"sublinks" => array(),
					"class" => " nyckel ",
					"sortindex" => 1000
				));
		}else if($level < UserLevels::$userLevels["brf"]){
			array_push($links,
				array(
					"title" => "Logga in här",
					"url" => "/auth",
					"position" => "navbar|footer",
					"sublinks" => array(),
					"class" => " nyckel ",
					"sortindex" => 1000
				));
		}else{
			array_push($links,
				array(
					"title" => "Logga ut",
					"url" => "/logout",
					"position" => "navbar|footer",
					"sublinks" => array(),
					"class" => " nyckel ",
					"sortindex" => 1000
				));
		}

		return $links;
	}


}

?>
