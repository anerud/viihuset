<?php
require_once("interfaces/iController.php");
require_once("interfaces/AbstractModule.php");

final class RegisterController implements iController{

	private $dbContext;

	public function init($router, $dbContext){

		$this->dbContext = $dbContext;

		$router->map('GET', '/register', function() {

			//Create the sales view
			$view = "RegisterView";
			require_once("views/register/".$view.".php");
			$viewObject = new $view(NULL);
			$viewObject->render();

		},"",0,"Registrera",true,"sales");

		$router->map('POST', '/register', function() {
			$errors = array();

			if(!isset($_POST["reg_brf"]) || empty($_POST["reg_brf"])) {
				array_push($errors, "Du måste skriva in namn på brf!");
			}

			if(!isset($_POST["reg_email"]) || empty($_POST["reg_email"])
										/* || TODO: check valied email */) {
				array_push($errors, "Du måste skriva in en giltig email!");
			}


			if(!isset($_POST["reg_pass"]) || empty($_POST["reg_pass"])) {
				array_push($errors, "Du måste ange ett lösenord!");
			}


			if(!isset($_POST["reg_pass_re"]) || empty($_POST["reg_pass_re"])
											 || !isset($_POST["reg_pass"])
											 || $_POST["reg_pass_re"] != $_POST["reg_pass"]) {
				array_push($errors, "Lösenorden måste matcha!");
			}

			if(count($errors) > 0) {
				$view = "RegisterView";
				require_once("views/register/".$view.".php");
				$viewObject = new $view($errors);
				$viewObject->render();
				return;
			}

			$username = 'admin';
			$brf_original = $_POST["reg_brf"];
			$brf = normalize($brf_original);
			$password = $_POST["reg_pass"];
			$email = $_POST["reg_email"];
			$firstName = "";
			$lastName = "";

			if(isset($_POST["reg_remember"])) {
				$remember = $_POST["reg_remember"];
			}

            $success = $this->dbContext->registerBrf($brf, $brf_original, $email);
            if (!$success) {
                array_push($errors, "Namn på bostadsrättsförening upptaget!");
                $this->returnErrors($errors);
				return;
            }

            //Admin
            $adminUser = new User();
            $adminUser->username = $username;
            $adminUser->brf = $brf;
            $adminUser->password = $password;
            $adminUser->email = $email;
            $adminUser->firstname = $firstName;
            $adminUser->lastname = $lastName;
            $adminUser->userlevel = "admin";
            $adminUser->active = true;
			$success = $this->dbContext->registerUser($adminUser);
            if (!$success) {
                array_push($errors, "Användarnamn upptaget!");
                $this->returnErrors($errors);
				return;
            }

            //Board member
            $user = new User();
            $user->username = "styrelsemedlem";
            $user->brf = $brf;
            $user->password = rand();
            $user->email = null;
            $user->firstname = null;
            $user->lastname = null;
            $user->userlevel = "board_member";
            $user->active = false;
			$this->dbContext->registerUser($user);

            //Board member
            $user = new User();
            $user->username = "föreningsmedlem";
            $user->brf = $brf;
            $user->password = rand();
            $user->email = null;
            $user->firstname = null;
            $user->lastname = null;
            $user->userlevel = "brf_member";
            $user->active = false;
            $this->dbContext->registerUser($user);

            //Create all content a new brf needs
			$userdefined = 0;
            $this->dbContext->createDefaultBanner($brf, $brf_original);
			$controllers = getControllers();
			foreach($controllers as $controller) {
				if($controller instanceof AbstractModule){
					$module = $controller->getDefaultModule();
					if($module != NULL){
						$this->dbContext->createOrUpdateModule(
                            $module->name,
                            $brf,
                            $module->title,
                            $module->description,
                            $module->sortindex,
                            $module->userlevel,
							$userdefined,
                            $module->visible
                        );
					}
				}
			}

			//TODO: Check if "remember me" is checked and set cookie lifetime thereafter.
            $_SESSION["user"] = $adminUser;
			header("Location: /hem");

		},"");

	}

    public function getLinksByLevel($level, $endpointLevel, $brf, $domain){
		$links = array();

		if($domain != "sales") {
			return $links;
		}

		if($level == UserLevels::$userLevels["sales"]) {
			array_push($links,
					array(
						"title" => "Registrera dig",
						"url" => "/register",
						"position" => "navbar|footer",
						"sublinks" => array(),
						"class" => " skapa ",
						"sortindex" => 999
					));
		}

		return $links;
	}

    private function returnErrors($errors) {
        $view = "RegisterView";
        require_once("views/register/".$view.".php");
        $viewObject = new $view($errors);
        $viewObject->render();
    }

}

?>
