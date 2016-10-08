<?php
require_once("interfaces/iController.php");
require_once("interfaces/AbstractModule.php");

final class BannerController implements iController{

	private $dbContext;

	public function init($router, $dbContext){

		$this->dbContext = $dbContext;

		//Brf info page
		$router->map('GET', '/sidhuvud',
			function() {
				$user = $_SESSION["user"];
				$brf = $user->brf;
				$banner = $this->dbContext->getBanner($brf);

				require_once("views/design/BannerView.php");
				$view = new BannerView($banner, getErrors());
				$view->render();
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];},
			null,
			true,
			"design"
		);

		//Brf info page
		$router->map('POST', '/sidhuvud',
			function() {
				$user = $_SESSION["user"];
				$brf = $user->brf;

				//External link to banner or upload new one?
				$bannerLink = $_POST["bannerLink"];
				$bannerText = $_POST["bannerText"];
                $font = $_POST["font"];
                $fontSize = $_POST["font-size"];
				$textColor = preg_replace('[\#]', '', $_POST["textColor"]);
				$shadow = isset($_POST["shadow"]);
                $textAlign = $_POST["text-align"];
                $max_width = $_POST["max_width"];

				if(strlen($bannerLink) <= 0) {
					$bannerLink = NULL;
				}

				$fileName  = $_FILES['imageToUpload']['name'];
				$fileEnding = pathinfo($fileName, PATHINFO_EXTENSION);
				$tmpName  = $_FILES['imageToUpload']['tmp_name'];
				$fileSize = $_FILES['imageToUpload']['size'];

                echo $fileName;

                if (!empty($fileName)
                    && $fileName != ""
                    && $fileSize > 0
                    && ($fileEnding == "jpg"
                        || $fileEnding == "jpeg"
                        || $fileEnding == "png"
                        || $fileEnding == "gif"
                    )
                ) {
                    $fp = fopen($tmpName, 'r');
                    $content = fread($fp, $fileSize);
                    fclose($fp);
                    $newFileName = "uploads/".str_replace(' ', '-', $brf."_banner_".microtime()).".".$fileEnding;
                    $newFile = fopen($newFileName,'w');
                    fwrite($newFile,$content);
                    fclose($newFile);
                    $bannerLink = "/".$newFileName;
                }

                require_once("model/brf/Banner.php");
                $banner = new Banner();
                $banner->brf = $brf;
				$banner->bannerLink = $bannerLink;
                $banner->bannerText = $bannerText;
                $banner->font = $font;
                $banner->fontSize = $fontSize;
                $banner->textColor = $textColor;
                $banner->shadow = $shadow;
                $banner->textAlign = $textAlign;
                $banner->max_width = $max_width;

				$this->dbContext->updateBanner($banner);

				header("Location: /sidhuvud");
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];}
		);

	}

    public function getLinksByLevel($level, $endpointLevel, $brf, $domain){
		$links = array();

		if($domain != "design") {
			return $links;
		}

		if($level < UserLevels::$userLevels["admin"]){
			return $links;
		}

		array_push($links,
				array(
					"title" => "Sidhuvud",
					"url" => "/sidhuvud",
					"position" => "navbar",
					"sublinks" => array(),
					"class" => " bas ",
					"sortindex" => 3
				));


		return $links;
	}

	public function getRightColModule($currentBrf) {
		return null;
	}

	public function getRightColSortIndex($currentBrf) {
		return null;
	}
}

?>
