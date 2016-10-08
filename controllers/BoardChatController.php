<?php
require_once("interfaces/iController.php");
require_once("interfaces/MailingController.php");
require_once("interfaces/AbstractModule.php");

final class BoardChatController extends AbstractModule implements MailingController {

	private $dbContext;
	private $mailClient;
	private $mailDomain;

	public function initMailingClient($mailClient, $mailDomain) {
		$this->mailClient = $mailClient;
		$this->mailDomain = $mailDomain;
	}

	public function init($router, $dbContext){
		parent::init($router, $dbContext);

		$this->dbContext = $dbContext;

		//Admin page (GET)
		$router->map('GET', '/styrelsechat',
			function() {
				$this->showAdminModuleView(getErrors());
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];}
		);

		//Admin page (GET)
		$router->map('GET', '/styrelsechat/[i:threadID]',
			function($threadID) {
				$this->renderMessageBoardAdminReplyView($threadID,getErrors());
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];}
		);

		//Admin page delete thread
		$router->map('DELETE', '/styrelsechat/[i:threadID]',
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
		$router->map('DELETE', '/styrelsechat/[i:threadID]/[i:replyID]',
			function($threadID, $replyID) {
				//Get the logged in user
				$user = $_SESSION["user"];
				$brf = $user->brf;
				$this->dbContext->deleteMessageBoardReply($threadID,$replyID,$brf,"board_member");

				header("HTTP/1.1 200 OK");
				return;
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];},
			"",
			false
		);

		//Brf message board (GET)
		$router->map('GET', '/[a:brf]/styrelsechat',
			function($brf) {

				//Query database for correct info
				require_once("model/module/ModuleInfo.php");
				$module = $this->dbContext->getModule($brf,"boardchat");

				if($module == NULL || !$module->visible){
					header("Location: /errors/404");
				} else {
					//Collect threads for the message board
					$page = intval(isset($_GET["page"]) ? $_GET["page"] : 1);
					list($maxPage, $threads) = $this->dbContext->getPagedMBThreadsByBrfAndRank($brf, "board_member",$page);

					$view = "BoardChatView";
					require_once("views/boardChat/".$view.".php");
					$viewObject = new $view($module,$threads,$maxPage,NULL);
					$viewObject->render();
				}
			},
			NULL,
			function($brf){
				$module = $this->dbContext->getModule($brf,"boardchat");
				$level = $module->userlevel;
				return UserLevels::$userLevels[$level];
			}
		);

		//Brf message board replies (GET)
		$router->map('GET', '/[a:brf]/styrelsechat/[i:threadID]',
			function($brf,$threadID) {

				//Query database for correct info
				require_once("model/module/ModuleInfo.php");
				$module = $this->dbContext->getModule($brf,"boardchat");

				if($module == NULL || !$module->visible){
					header("Location: /errors/404");
				} else {
					//Collect replies for the message board
					$page = intval(isset($_GET["page"]) ? $_GET["page"] : 1);
					$thread = $this->dbContext->getMessageBoardThreadByIDAndRank($threadID, "board_member");
					if($thread == NULL) {
						header("Location: /errors/404");
					}
					list($maxPage, $replies) = $this->dbContext->getPagedMBRepliesByThreadID($threadID, $page);
					$thread->replies = $replies;

					$view = "BoardChatReplyView";
					require_once("views/boardChat/".$view.".php");
					$viewObject = new $view($module,$thread,$maxPage, getErrors());
					$viewObject->render();
				}

			},
			NULL,
			function($brf){
				$module = $this->dbContext->getModule($brf,"boardchat");
				$level = $module->userlevel;
				return UserLevels::$userLevels[$level];
			}
		);

		//Brf message board (POST) a thread
		$router->map('POST', '/[a:brf]/styrelsechat',
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

				//If there were errors.
				if(count($errors) > 0) {
					//Collect threads for the message board
					list($maxPage, $threads) = $this->dbContext->getPagedMBThreadsByBrfAndRank($brf, "board_member",$page);

					$view = "BoardChatView";
					require_once("views/boardChat/".$view.".php");
					$viewObject = new $view($module,$threads,$maxPage,$errors);
					$viewObject->render();
					return;
				}

				//Else get the fields and post new thread.
				$title = $_POST["title"];
				$message = $_POST["message"];
				$poster = $_POST["poster"];
				$email = isset($_POST["email"]) ? $_POST["email"] : null;

				$this->dbContext->postNewMessageBoardThread($title, $message, $poster, $email, $brf, "board_member");

				//Redirect to message board
				header("Location: /".$brf."/styrelsechat");
			}
		);

		$router->map('POST', '/[a:brf]/styrelsechat/[i:threadID]',
			function($brf,$threadID) {

				$errors = array();

				if(!isset($_POST["message"]) || empty($_POST["message"])) {
					array_push($errors, "Du måste ange ett meddelande!");
				}


				if(!isset($_POST["poster"]) || empty($_POST["poster"])) {
					array_push($errors, "Du måste ange ditt namn!");
				}

				//If there were errors.
				if(count($errors) > 0) {
					setErrors($errors);
					header("Location: /".$brf."/styrelsechat/".$threadID);
					return;
				}

				$message = $_POST["message"];
				$poster = $_POST["poster"];
				$email = isset($_POST["email"]) ? $_POST["email"] : null;

				$this->dbContext->postNewMessageBoardReply($message, $poster, $email, $threadID);

				$this->sendMessageBoardEmail($threadID, $poster, $email, $message);

				//Redirect to message thread
				header("Location: /".$brf."/styrelsechat/".$threadID);

			},
			NULL,
			function($brf){
				$module = $this->dbContext->getModule($brf,"boardchat");
				$level = $module->userlevel;
				return UserLevels::$userLevels[$level];
			}
		);

	}
	/*
	* Implementing abstract methods
	*/

	public function getDomain() {
		return "portal";
	}

	public function getModuleName() {
		return "boardchat";
	}

	public function getBaseUrl() {
		return "styrelsechat";
	}

	public function getLinkTitle() {
		return "Styrelsechat";
	}

	public function getDefaultModule() {
		require_once("model/module/ModuleInfo.php");
		$module = new ModuleInfo();
        $module->name = "boardchat";
		$module->brf = null;
		$module->title = "Styrelsechat";
        $module->description = "Skriv något här! (endast för medlemmar av styrelsen)";
		$module->userlevel = "board_member";
		$module->visible = 1;
		$module->sortindex = 6;
		$module->rightcol_sortindex = null;
		return $module;
	}

	private function renderMessageBoardAdminReplyView($threadID, $errors){
		$user = $_SESSION["user"];
		$brf = $user->brf;
		$page = intval(isset($_GET["page"]) ? $_GET["page"] : 1);

		//Query database for correct info
		require_once("model/module/ModuleInfo.php");
		$module = $this->dbContext->getModule($brf,"boardchat");

		//Collect threads for the message board
		$thread = $this->dbContext->getMessageBoardThreadByIDAndRank($threadID, "board_member");
		list($maxPage, $replies) = $this->dbContext->getPagedMBRepliesByThreadID($threadID, $page);
		$thread->replies = $replies;

		//Render view
		$view = "BoardChatAdminReplyView";
		require_once("views/boardChat/".$view.".php");
		$viewObject = new $view($module,$thread,$maxPage,$errors);
		$viewObject->render();
	}

	public function showAdminModuleView($errors){
		$user = $_SESSION["user"];
		$brf = $user->brf;
		$page = intval(isset($_GET["page"]) ? $_GET["page"] : 1);

		//Query database for correct info
		require_once("model/module/ModuleInfo.php");
		$module = $this->dbContext->getModule($brf,"boardchat");

		//Collect threads for the message board
		list($maxPage, $threads) = $this->dbContext->getPagedMBThreadsByBrfAndRank($brf, "board_member",$page);

		//Render view
		$view = "BoardChatAdminView";
		require_once("views/boardChat/".$view.".php");
		$viewObject = new $view($module,$threads,$maxPage,$errors);
		$viewObject->render();
	}

	private function sendMessageBoardEmail($threadId, $poster, $posterEmail, $message) {
		// Get information about the thread
		$thread = $this->dbContext->getMessageBoardThreadByIDAndRank($threadId, "board_member");

		// Create string with all recipients
		$recipients = $this->dbContext->getEmailsInvolvedInThread($threadId, $posterEmail);

		if (count($recipients) <= 0) {
			return;
		}

		$to = $recipients[0]->email;
		for ($x = 1; $x < count($recipients); $x++) {
			$email = $recipients[$x]->email;
		    $to = $to.", ".$email;
		}

		// Construct subject
		$subject = "Styrelsechat: ".$thread->title;

		if ($posterEmail == null) {
			$posterEmail = "ingen email angiven";
		}
		// Contruct html
		$html = "<h5>Meddelande:</h5>"
				.$message
				."<br>"
				."<h5>Avsändare:</h5>"
				."<p>".$poster." (".$posterEmail.")</p>";

		// Send email
		$result = $this->mailClient->sendMessage(
			$this->mailDomain, array(
			    'from'    => 'noreply@'.$this->mailDomain,
			    'to'      => $to,
			    'subject' => $subject,
				'html'	  => $html
			)
		);
	}

}

?>
