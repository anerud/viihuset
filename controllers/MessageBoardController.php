<?php
require_once("interfaces/iController.php");
require_once("interfaces/AbstractModule.php");

final class MessageBoardController extends AbstractModule {

	private $dbContext;

	public function init($router, $dbContext){
		parent::init($router, $dbContext);

		$this->dbContext = $dbContext;

		//Admin page (GET)
		$router->map('GET', '/anslagstavla',
			function() {
				$this->showAdminModuleView(getErrors());
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];}
		);

		//Admin page (GET)
		$router->map('GET', '/anslagstavla/[i:threadID]',
			function($threadID) {
				$this->renderMessageBoardAdminReplyView($threadID,getErrors());
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];}
		);

		//Admin page delete thread
		$router->map('DELETE', '/anslagstavla/[i:threadID]',
			function($threadID) {
				//Get the logged in user
				$user = $_SESSION["user"];
				$brf = $user->brf;
				$this->dbContext->deleteMessageBoardThread($threadID,$brf);

				header("HTTP/1.1 200 OK");
				return;
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];},
			"",
			false
		);

		//Admin page delete thread reply
		$router->map('DELETE', '/anslagstavla/[i:threadID]/[i:replyID]',
			function($threadID, $replyID) {
				//Get the logged in user
				$user = $_SESSION["user"];
				$brf = $user->brf;
				$this->dbContext->deleteMessageBoardReply($threadID,$replyID,$brf,"brf");

				header("HTTP/1.1 200 OK");
				return;
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];},
			"",
			false
		);

		//Brf message board (GET)
		$router->map('GET', '/[a:brf]/anslagstavla',
			function($brf) {

				//Query database for correct info
				require_once("model/module/ModuleInfo.php");
				$module = $this->dbContext->getModule($brf,"messageboard");

				if($module == NULL || !$module->visible){
					header("Location: /errors/404");
				} else {
					//Collect threads for the message board
					$page = intval(isset($_GET["page"]) ? $_GET["page"] : 1);
					list($maxPage, $threads) = $this->dbContext->getPagedMBThreadsByBrfAndRank($brf, "brf",$page);

					$view = "MessageBoardView";
					require_once("views/messageBoard/".$view.".php");
					$viewObject = new $view($module,$threads,$maxPage,NULL);
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

		//Brf message board replies (GET)
		$router->map('GET', '/[a:brf]/anslagstavla/[i:threadID]',
			function($brf,$threadID) {

				//Query database for correct info
				require_once("model/module/ModuleInfo.php");
				$module = $this->dbContext->getModule($brf,"messageboard");

				if($module == NULL || !$module->visible){
					header("Location: /errors/404");
				} else {
					//Collect replies for the message board
					$page = intval(isset($_GET["page"]) ? $_GET["page"] : 1);
					$thread = $this->dbContext->getMessageBoardThreadByIDAndRank($threadID, "brf");
					if($thread == NULL) {
						header("Location: /errors/404");
					}
					list($maxPage, $replies) = $this->dbContext->getPagedMBRepliesByThreadID($threadID, $page);
					$thread->replies = $replies;

					$view = "MessageBoardReplyView";
					require_once("views/messageBoard/".$view.".php");
					$viewObject = new $view($module,$thread,$maxPage, getErrors());
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

		//Brf message board (POST) a thread
		$router->map('POST', '/[a:brf]/anslagstavla',
			function($brf) {

				//Check that all fields of form has been filled correctly
				$errors = array();

				if(!isset($_POST["title"]) || empty($_POST["title"])) {
					array_push($errors, "Du måste ange en titel!");
				}

				if(!isset($_POST["message"]) || empty($_POST["message"])
											/* || TODO: check valied email */) {
					array_push($errors, "Du måste ange ett meddelande!");
				}


				if(!isset($_POST["poster"]) || empty($_POST["poster"])) {
					array_push($errors, "Du måste ange ditt namn!");
				}


				if(!isset($_POST["email"]) || empty($_POST["email"])) {
					array_push($errors, "Du måste ange din email!");
				}

				//If there were errors.
				if(count($errors) > 0) {
					//Collect threads for the message board
                    $page = intval(isset($_GET["page"]) ? $_GET["page"] : 1);
					list($maxPage, $threads) = $this->dbContext->getPagedMBThreadsByBrfAndRank($brf, "brf",$page);

                    $module = $this->dbContext->getModule($brf,"messageboard");
					$view = "MessageBoardView";
					require_once("views/messageBoard/".$view.".php");
					$viewObject = new $view($module,$threads,$maxPage,$errors);
					$viewObject->render();
					return;
				}

				//Else get the fields and post new thread.
				$title = $_POST["title"];
				$message = $_POST["message"];
				$poster = $_POST["poster"];
				$email = $_POST["email"];

				$this->dbContext->postNewMessageBoardThread($title, $message, $poster, $email, $brf, "brf");

				//Redirect to message board
				header("Location: /".$brf."/anslagstavla");
			}
		);

		$router->map('POST', '/[a:brf]/anslagstavla/[i:threadID]',
			function($brf,$threadID) {

				$errors = array();

				if(!isset($_POST["message"]) || empty($_POST["message"])
											/* || TODO: check valied email */) {
					array_push($errors, "Du måste ange ett meddelande!");
				}


				if(!isset($_POST["poster"]) || empty($_POST["poster"])) {
					array_push($errors, "Du måste ange ditt namn!");
				}


				if(!isset($_POST["email"]) || empty($_POST["email"])) {
					array_push($errors, "Du måste ange din email!");
				}

				//If there were errors.
				if(count($errors) > 0) {
					setErrors($errors);
					header("Location: /".$brf."/anslagstavla/".$threadID);
					return;
				}

				$message = $_POST["message"];
				$poster = $_POST["poster"];
				$email = $_POST["email"];

				$this->dbContext->postNewMessageBoardReply($message, $poster, $email, $threadID);

				//Redirect to message thread
				header("Location: /".$brf."/anslagstavla/".$threadID);

			},
			NULL,
			function(){return UserLevels::$userLevels["brf"];}
		);

	}

	/*
	* Implementing abstract methods
	*/

	public function getDomain() {
		return "portal";
	}

	public function getModuleName() {
		return "messageboard";
	}

	public function getBaseUrl() {
		return "anslagstavla";
	}

	public function getLinkTitle() {
		return "Anslagstavla";
	}

	public function getDefaultModule() {
		require_once("model/module/ModuleInfo.php");
		$module = new ModuleInfo();
        $module->name = "messageboard";
		$module->brf = null;
		$module->title = "Anslagstavla";
        $module->description = "Här kan du lägga in offentliga inlägg som alla medlemmar kan se.";
		$module->userlevel = "brf";
		$module->visible = 1;
		$module->sortindex = 2;
		$module->rightcol_sortindex = 2;
		return $module;
	}

	private function renderMessageBoardAdminReplyView($threadID, $errors){
		$user = $_SESSION["user"];
		$brf = $user->brf;
		$page = intval(isset($_GET["page"]) ? $_GET["page"] : 1);

		//Query database for correct info
		require_once("model/module/ModuleInfo.php");
		$module = $this->dbContext->getModule($brf,"messageboard");

		//Collect threads for the message board
		$thread = $this->dbContext->getMessageBoardThreadByIDAndRank($threadID, "brf");
		list($maxPage, $replies) = $this->dbContext->getPagedMBRepliesByThreadID($threadID, $page);
		$thread->replies = $replies;

		//Render view
		$view = "MessageBoardAdminReplyView";
		require_once("views/messageBoard/".$view.".php");
		$viewObject = new $view($module,$thread,$maxPage,$errors);
		$viewObject->render();
	}

	public function showAdminModuleView($errors){
		$user = $_SESSION["user"];
		$brf = $user->brf;
		$page = intval(isset($_GET["page"]) ? $_GET["page"] : 1);

		//Query database for correct info
		require_once("model/module/ModuleInfo.php");
		$module = $this->dbContext->getModule($brf,"messageboard");

		//Collect threads for the message board
		list($maxPage, $threads) = $this->dbContext->getPagedMBThreadsByBrfAndRank($brf, "brf",$page);

		//Render view
		$view = "MessageBoardAdminView";
		require_once("views/messageBoard/".$view.".php");
		$viewObject = new $view($module,$threads,$maxPage,$errors);
		$viewObject->render();
	}

	public function getRightColModule($currentBrf) {
		require_once("views/rightcol/MessageBoardView.php");
		list($lastPage, $pageResults) = $this->dbContext->getPagedMBThreadsByBrfAndRank($currentBrf,"brf",1);
		return new MessageBoardRightColView($pageResults, $currentBrf, $this->getDefaultModule()->name);
	}

}

?>
