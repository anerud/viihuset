<?php
require_once("interfaces/iController.php");

final class DesignController implements iController{

	private $dbContext;

	public function init($router, $dbContext){
		$this->dbContext = $dbContext;

		//Brf page
		$router->map('GET', '/farger',
			function() {
				$user = $_SESSION["user"];
				$brf = $user->brf;
                $colors = $this->dbContext->getAllColors();
				$backgroundPatterns = $this->dbContext->getBackgroundPatterns();
                $activeDesign = $this->dbContext->getActiveDesignPatternByBrf($brf);
				require_once("views/design/DesignPatternView.php");

				$view = new DesignPatternView(
                    $colors,
                    $backgroundPatterns,
                    $activeDesign,
                    getErrors()
                );
				$view->render();
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];},
			null,
			true,
			"design"
		);

        $router->map('GET', '/teman',
			function() {
				$user = $_SESSION["user"];
				$brf = $user->brf;
                $activeDesign = $this->dbContext->getActiveDesignPatternByBrf($brf);
                $designPatterns = $this->dbContext->getAllDesignPatterns();

				require_once("views/design/DesignThemeView.php");
				$view = new DesignThemeView($activeDesign, $designPatterns, getErrors());
				$view->render();
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];},
			null,
			true,
			"design"
		);

		//Brf page
		$router->map('GET', '/getDefaultDesignPatterns',
			function() {
				echo json_encode($this->dbContext->getAllDesignPatterns());
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];},
			"",
			false
		);

		//Admin page (POST) update booking object
		$router->map('POST', '/farger',
			function() {

				$user = $_SESSION["user"];
				$brf = $user->brf;

				$design = $this->dbContext->getActiveDesignPatternByBrf($brf);
                $color1 = null;
                $color2 = null;
                $color3 = null;
                $color4 = null;
                $backgroundColor = null;
                $backgroundPattern = null;

				if(isset($_POST["colorId"]) && !empty($_POST["colorId"])) {
	                $colorId = $_POST["colorId"];
	                $color = $this->dbContext->getColorById($colorId);
					$color1 = $this->toSaveHex($color->color1);
					$color2 = $this->toSaveHex($color->color2);
					$color3 = $this->toSaveHex($color->color3);
	                $color4 = $this->toSaveHex($color->color4);

				}

				if(isset($_POST["bgColor"]) && !empty($_POST["bgColor"])) {
					$backgroundColor = $this->toSaveHex($_POST["bgColor"]);
				}

				if(isset($_POST["path"]) && !empty($_POST["path"])) {
					$backgroundPattern = $this->toSaveBg($_POST["path"]);
				}



                //Check if active pattern is a template pattern
                if($design->brf == null) {

                    //Try to switch to custom pattern
                    $design = $this->dbContext->getCustomDesignPatternByBrf($brf);

                    //If there where no custom pattern --> create one
                    if($design == NULL){
                        $this->dbContext->createCustomDesignPattern($brf);
                        $design = $this->dbContext->getCustomDesignPatternByBrf($brf);
                    }

                    //Set the active pattern to the custom one
                    $this->dbContext->setActiveDesignPattern($brf, $design->id);
                }

                //Update the custom pattern
                $this->dbContext->updateDesignPattern(
                    $design,
                    $color1,
                    $color2,
                    $color3,
                    $color4,
                    $backgroundColor,
                    $backgroundPattern
                );
                header("HTTP/1.1 200 OK");
                return;
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];}
		);


        $router->map('POST', '/teman',
			function() {

				$user = $_SESSION["user"];
				$brf = $user->brf;

				$errors = array();

                if(!isset($_POST["themeId"]) || empty($_POST["themeId"])) {
                    array_push($errors, "Inget tema angivet");
                }

				if(!empty($errors)) {
					setErrors($errors);
					header("Location: /teman");
					return;
				}

                $themeId = $_POST["themeId"];
				$this->dbContext->setActiveDesignPattern($brf,$themeId);

				header("Location: /teman");
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];}
		);

	}

	private function toSaveBg($in){
		$startIndex = strpos($in, "/background/");
		$endIndex = strpos($in, ".png");
		if($startIndex >= 0 && $endIndex >=0){
			return $in;
		}
		return "/background/carbon-fibre.png";
	}

	private function toSaveHex($in){
		$length = strlen($in);
		if($length  == 3 || $length == 6){
			return $in;
		}else if($length == 4 || $length == 7){
			return substr($in, 1);
		}
		return "654654";
	}

    public function getLinksByLevel($level, $endpointLevel, $brf, $domain){
		$links = array();

		if($domain != "design") {
			return $links;
		}

		array_push($links,
				array(
					"title" => "Färdiga designmallar",
					"url" => "/teman",
					"position" => "navbar",
					"sublinks" => array(),
					"class" => " bas ",
                    "sortindex" => 2
				));

        array_push($links,
            array(
                "title" => "Egna färger & mönster",
                "url" => "/farger",
                "position" => "navbar",
                "sublinks" => array(),
                "class" => " bas ",
                "sortindex" => 3
            ));

		return $links;
	}

}

?>
