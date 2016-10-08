<?php
require_once("interfaces/iController.php");
require_once("interfaces/AbstractModule.php");

final class PhotoAlbumController extends AbstractModule {

	private $dbContext;

	public function init($router, $dbContext){
		parent::init($router, $dbContext);

		$this->dbContext = $dbContext;

		//Admin page (GET)
		$router->map('GET', '/fotoalbum/[i:photoAlbumID]?',
			function() {

				$args = func_get_args();
				$photoAlbumID = null;
				if(sizeof($args) == 0){
					require_once("model/photoAlbum/PhotoAlbum.php");
					$currentPhotoAlbum = new PhotoAlbum();
				} else {
					$photoAlbumID = $args[0];
					$user = $_SESSION["user"];
					$brf = $user->brf;
					$currentPhotoAlbum = $this->dbContext->getPhotoAlbumByID($photoAlbumID, $brf);
					if($currentPhotoAlbum == NULL) {
						header("Location: /errors/404");
						return;
					}
				}

				if($photoAlbumID != null) {
					$currentPhotoAlbum->images = $this->dbContext->getPhotoAlbumImagesByAlbumID($photoAlbumID);
				}

				//Get the logged in user
				$user = $_SESSION["user"];

				//Query database for correct info
				require_once("model/module/ModuleInfo.php");
				$module = $this->dbContext->getModule($user->brf,"photoalbum");

				//Get current page in pagination
				$page = intval(isset($_GET["page"]) ? $_GET["page"] : 1);
				$lastPage = null;
				$pageResults = null;
				if($photoAlbumID == null) {
					//Collect documents for the view
					list($lastPage, $pageResults) = $this->dbContext->getPagedPhotoAlbumsByBrf($user->brf, $page);
				}

				//Render view
				require_once("views/photoAlbum/PhotoAlbumAdminView.php");
				$viewObject = new PhotoAlbumAdminView($currentPhotoAlbum, $pageResults, $lastPage, getErrors() ,$module);
				$viewObject->render();
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];}
		);

		//Download an image from photo album
		$router->map('GET', '/[a:brf]/fotoalbum/[i:photoAlbumID]/[i:imageID]',
			function($brf, $photoAlbumID, $imageID) {

				$image = $this->dbContext->getPhotoAlbumImage($brf,$photoAlbumID,$imageID);
				if($image == null) {
					header("Location: /errors/404");
				}

				$file = fopen($image->filepath,"r");
				$content = fread($file, filesize($image->filepath));

				header('Content-Type: '.$image->contentType);
				header('Content-Disposition: attachment; filename='.$image->title);

				echo $content;
			},
			NULL,
			function(){
				return 1;
			},
			"",
			false
		);

		//Admin page (POST) update booking object
		$router->map('POST', '/fotoalbum/[i:fotoAlbumID]?',
			function() {

				$user = $_SESSION["user"];
				$brf = $user->brf;

				$errors = array();

				if(!isset($_POST["title"]) || empty($_POST["title"])) {
					array_push($errors, "Du måste ange en titel för albumet!");
				}

				$args = func_get_args();
				$photoAlbumID = sizeof($args) == 0 ? NULL : $args[0];

				if(!empty($errors)) {
					setErrors($errors);
					header("Location: /fotoalbum".$photoAlbumID != NULL ? "/".$photoAlbumID : "");
					return;
				}

				$title = $_POST["title"];
				$description = isset($_POST["title"]) ? $_POST["description"] : "";
				$fileName  = $_FILES['fileToUpload']['name'];
				$fileEnding = pathinfo($fileName, PATHINFO_EXTENSION);
				$tmpName  = $_FILES['fileToUpload']['tmp_name'];
				$fileSize = $_FILES['fileToUpload']['size'];
				$fileType = $_FILES['fileToUpload']['type'];

				if($photoAlbumID == NULL){
					//Create new photo album
					$photoAlbumID = $this->dbContext->createPhotoAlbum($brf,$title,$description);
				} else {
					//Update existing photo album
					$this->dbContext->updatePhotoAlbum($photoAlbumID, $brf, $title, $description);
				}

				if($fileSize > 0) {
                    // Read file
					$fp = fopen($tmpName, 'r');
					$content = fread($fp, $fileSize);
					fclose($fp);

                    // Set up file name and paths
                    $newFileName = str_replace(' ', '-', $brf."_image_".microtime());
					$newFilePath = "uploads/".$newFileName.".".$fileEnding;
                    $newThumbPath = "uploads/".$newFileName."_thumb.".$fileEnding;

                    // Create the acutal image file on disk
					$newFile = fopen($newFilePath,'w');
					fwrite($newFile,$content);
					fclose($newFile);

                    /* Create thumbnail on disk start { */
                    list($width, $height) = getimagesize($newFilePath);
                    $newwidth = 130;
                    $newheight = 130;
                    $thumb = imagecreatetruecolor($newwidth, $newheight);

                    switch ($fileType) {
                        case "image/gif":
                            $source = imagecreatefromgif($newFilePath);
                            break;
                        case "image/jpeg":
                            $source = imagecreatefromjpeg($newFilePath);
                            break;
                        case "image/png":
                            $source = imagecreatefrompng($newFilePath);
                            break;
                    }

                    // Resize
                    imagecopyresized(
                        $thumb,
                        $source,
                        0,
                        0,
                        0,
                        0,
                        $newwidth,
                        $newheight,
                        $width,
                        $height
                    );

                    switch ($fileType) {
                        case "image/gif":
                            imagegif($thumb, $newThumbPath);
                            break;
                        case "image/jpeg":
                            imagejpeg($thumb, $newThumbPath);
                            break;
                        case "image/png":
                            imagepng($thumb, $newThumbPath);
                            break;
                    }

                    /* } Create thumbnail on disk end */

					$this->dbContext->uploadImageToPhotoAlbum(
						$brf,
						$photoAlbumID,
						$fileName,
						$newFilePath,
						$fileType,
                        $newThumbPath
					);
				}

				header("Location: /fotoalbum/".$photoAlbumID);
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];}
		);

		//Portal page (GET)
		$router->map('GET', '/[a:brf]/fotoalbum',
			function($brf) {

				//Query database for correct info
				require_once("model/module/ModuleInfo.php");
				$module = $this->dbContext->getModule($brf,"photoalbum");

				if($module == NULL || !$module->visible){
					header("Location: /errors/404");
				} else {
					//Collect threads for the message board
					$page = intval(isset($_GET["page"]) ? $_GET["page"] : 1);
					list($lastPage, $pageResults) = $this->dbContext->getPagedPhotoAlbumsByBrf($brf, $page);

					require_once("views/photoAlbum/PhotoAlbumView.php");
					$viewObject = new PhotoAlbumView($module,$pageResults,$lastPage,NULL);
					$viewObject->render();
				}
			},
			NULL,
			function($brf){
				$module = $this->dbContext->getModule($brf,"messageboard");
				$level = $module->userlevel;
				return UserLevels::$userLevels[$level];
			}
		);

        // Photo album portal view
		$router->map('GET', '/[a:brf]/fotoalbum/[i:albumID]',
			function($brf,$albumID) {

                // TODO: CHECK THAT USER LEVEL IS OK.

				require_once("model/module/ModuleInfo.php");
				$module = $this->dbContext->getModule($brf,"photoalbum");

				if($module == NULL || !$module->visible){
					header("Location: /errors/404");
                    return;
				}
                //Collect replies for the message board
                $album = $this->dbContext->getPhotoAlbumByID($albumID, $brf);
                if($album == NULL) {
                    header("Location: /errors/404");
                }

                $album->images = $this->dbContext->getPhotoAlbumImagesByAlbumID($albumID);

                require_once("views/photoAlbum/PhotoAlbumAlbumView.php");
                $viewObject = new PhotoAlbumAlbumView($album, NULL);
                $viewObject->render();

			},
			NULL,
			function($brf){
				$module = $this->dbContext->getModule($brf,"messageboard");
				$level = $module->userlevel;
				return UserLevels::$userLevels[$level];
			}
		);

		//Delete album
		$router->map('DELETE', '/fotoalbum/[i:albumID]',
			function($albumID) {
				//Get the logged in user
				$user = $_SESSION["user"];

				//Collect documents for the view
				$this->dbContext->deletePhotoAlbum($user->brf, $albumID);

				header("HTTP/1.1 200 OK");
				return;
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];},
			"",
			false
		);

		//Change user level
		$router->map('POST', '/fotoalbum/changeLevel/[i:albumID]',
			function($albumID) {
				//Get the logged in user
				$user = $_SESSION["user"];

				if(!isset($_POST["value"])){
					http_response_code(400);
					return;
				}
				//Collect documents for the view
				$this->dbContext->changePhotoAlbumUserLevel($user->brf, $albumID, $_POST["value"]);

				header("HTTP/1.1 200 OK");
				return;
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];},
			"",
			false
		);

		//Toggle viibility
		$router->map('POST', '/fotoalbum/toggleVisibility/[i:albumID]',
			function($albumID) {
				//Get the logged in user
				$user = $_SESSION["user"];

				//Collect documents for the view
				$this->dbContext->togglePhotoAlbumVisibility($user->brf, $albumID);

				header("HTTP/1.1 200 OK");
				return;
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];},
			"",
			false
		);

        //Admin page delete thread reply
		$router->map('DELETE', '/fotoalbum/[i:albumId]/[i:photoId]',
			function($albumId, $photoId) {
				//Get the logged in user
				$user = $_SESSION["user"];
				$brf = $user->brf;
				$this->dbContext->deletePhotoAlbumImage($albumId, $photoId, $brf);
                header("HTTP/1.1 200 OK");
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];},
			"",
			false
		);

        $router->map('POST', '/fotoalbum/changeLevel/[i:albumId]',
			function($albumId) {

				if(!isset($_POST["value"])){
					http_response_code(400);
					return;
				}

				$user = $_SESSION["user"];
                $userlevel = $_POST["value"];
				$this->dbContext->changePhotoAlbumUserLevel($brf, $albumId, $userlevel);

				header("HTTP/1.1 200 OK");
				return;
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];},
			"",
			false
		);

	}


	/*
	* Implementing abstract methods
	*/

	public function getDomain() {
		return "portal";
	}

	public function getModuleName() {
		return "photoalbum";
	}

	public function getBaseUrl() {
		return "fotoalbum";
	}

	public function getLinkTitle() {
		return "Fotoalbum";
	}

	public function getDefaultModule() {
		require_once("model/module/ModuleInfo.php");
		$module = new ModuleInfo();
        $module->name = "photoalbum";
		$module->brf = null;
		$module->title = "Fotoalbum";
        $module->description = "Här visas olika album med foton!";
		$module->userlevel = "brf";
		$module->visible = 1;
		$module->sortindex = 4;
		$module->rightcol_sortindex = null;
		return $module;
	}

}

?>
