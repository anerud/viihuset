<?php
require_once("interfaces/iController.php");
require_once("interfaces/AbstractModule.php");
require_once("model/document/Document.php");

final class DocumentController extends AbstractModule{

	private $dbContext;

	public function init($router, $dbContext){
		parent::init($router, $dbContext);

		$this->dbContext = $dbContext;

		//Admin page (GET)
		$router->map('GET', '/dokument',
			function() {
				$this->showAdminModuleView(getErrors());
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];}
		);


		//Admin page (GET)
		$router->map('POST', '/dokument/toggleVisibility/[i:documentID]',
			function($documentID) {
				//Get the logged in user
				$user = $_SESSION["user"];

				//Collect documents for the view
				$this->dbContext->toggleDocumentVisibility($user->brf, $documentID);

				header("HTTP/1.1 200 OK");
				return;
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];},
			"",
			false
		);

		$router->map('POST', '/dokument/changeLevel/[i:documentID]',
			function($documentID) {
				//Get the logged in user
				$user = $_SESSION["user"];

				if(!isset($_POST["value"])){
					http_response_code(400);
					return;
				}
				//Collect documents for the view
				$this->dbContext->changeDocumentUserLevel($user->brf, $documentID, $_POST["value"]);

				header("HTTP/1.1 200 OK");
				return;
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];},
			"",
			false
		);

		$router->map('DELETE', '/dokument/[i:documentID]',
			function($documentID) {
				//Get the logged in user
				$user = $_SESSION["user"];

				//Collect documents for the view
				$this->dbContext->deleteDocument($user->brf, $documentID);

				header("HTTP/1.1 200 OK");
				return;
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];},
			"",
			false
		);

		//Admin update module
		$router->map('POST', '/dokument',
			function() {

				$fileTitle = $_POST['fileName'];
				$fileName  = $_FILES['fileToUpload']['name'];
				$fileEnding = pathinfo($fileName, PATHINFO_EXTENSION);
				$tmpName  = $_FILES['fileToUpload']['tmp_name'];
				$fileSize = $_FILES['fileToUpload']['size'];
				$fileType = $_FILES['fileToUpload']['type'];
				$user = $_SESSION["user"];
				$userName = $user->username;
				$brf = $user->brf;

				$errors = [];

				if(empty($fileTitle)) {
					array_push($errors, "Du måste ange en titel på dokumentet.");
				}

				if($fileName == "" || $fileSize <= 0) {
					array_push($errors, "Du måste välja en fil.");
				}

				//If there were errors.
				if(count($errors) > 0) {
					$this->showAdminModuleView($errors);
				}

				$fp = fopen($tmpName, 'r');
				$content = fread($fp, $fileSize);
				fclose($fp);
				$newFileName = "uploads/".str_replace(' ', '-', $brf."_doc_".microtime()).".".$fileEnding;
				$newFile = fopen($newFileName,'w');
				fwrite($newFile,$content);
				fclose($newFile);

				$fileTitle = $fileTitle.".".$fileEnding;
				$rank = $_POST["documentUserLevel"];
				$this->dbContext->uploadDocument($brf,$userName,$fileTitle,$newFileName,$fileType,$rank);

				header("Location: /dokument");

			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];}
		);

		//Brf message board (GET)
		$router->map('GET', '/[a:brf]/dokument',
			function($brf) {
				//Get the logged in user
                $user = isset($_SESSION["user"])
                    ? $_SESSION["user"]
                    : null;
                $userlevel = $user != null && $user->brf == $brf
                    ? $user->userlevel
                    : "brf";

				//Query database for correct info
				require_once("model/module/ModuleInfo.php");
				$module = $this->dbContext->getModule($brf,"document");

				//Get current page in pagination
				$page = intval(isset($_GET["page"]) ? $_GET["page"] : 1);

				//Collect documents for the view
				list($lastPage, $documents) = $this->dbContext->getPagedDocumentsByBrf($brf, true, $page);

                $docsToView = array();
                foreach ($documents as $doc) {
                    if(UserLevels::$userLevels[$userlevel] >= UserLevels::$userLevels[$doc->userlevel]) {
                        array_push($docsToView, $doc);
                    }
                }

				//Render view
				$view = "DocumentView";
				require_once("views/document/".$view.".php");
				$viewObject = new $view($docsToView, $lastPage, $module);
				$viewObject->render();
			},
			NULL,
			function(){
				return UserLevels::$userLevels["brf"];
			}
		);

		$router->map('GET', '/[a:brf]/dokument/[i:documentID]',
			function($brf, $documentID) {
				$document = $this->dbContext->getDocumentByIDAndBrf($documentID, $brf);
                if ($document == null){
                    header("Location: /".$brf."/dokument");
                }


                $userlevel = isset($_SESSION["user"]) ? $_SESSION["user"]->userlevel : "brf";
                if (UserLevels::$userLevels[$userlevel] < UserLevels::$userLevels[$document->userlevel]) {
                    header("Location: /".$brf."/dokument");
                }

				$file = fopen($document->filepath,"r");
				$content = fread($file, filesize($document->filepath));

				header('Content-Type: '.$document->extension);
				header('Content-Disposition: attachment; filename='.$document->title);

				echo $content;
			},
			NULL,
			function(){
				return UserLevels::$userLevels["brf"];
			},
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
		return "document";
	}

	public function getBaseUrl() {
		return "dokument";
	}

	public function getLinkTitle() {
		return "Dokumentarkiv";
	}

	public function getDefaultModule() {
		require_once("model/module/ModuleInfo.php");
		$module = new ModuleInfo();
        $module->name = "document";
		$module->brf = null;
		$module->title = "Dokument";
        $module->description = "Här visas Hemliga Dokument!";
		$module->userlevel = "brf";
		$module->visible = 1;
		$module->sortindex = 3;
		$module->rightcol_sortindex = null;
		return $module;
	}


	public function showAdminModuleView($errors){


		//Get the logged in user
		$user = $_SESSION["user"];

		//Query database for correct info
		require_once("model/module/ModuleInfo.php");
		$module = $this->dbContext->getModule($user->brf,"document");

		//Get current page in pagination
		$page = intval(isset($_GET["page"]) ? $_GET["page"] : 1);

		//Collect documents for the view
		list($lastPage, $pageResults) = $this->dbContext->getPagedDocumentsByBrf($user->brf, true, $page);

		//Render view
		$view = "DocumentAdminView";
		require_once("views/document/".$view.".php");
		$viewObject = new $view($pageResults, $lastPage, $errors ,$module);
		$viewObject->render();
	}

}

?>
