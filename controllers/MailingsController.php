<?php
require_once("interfaces/iController.php");
require_once("interfaces/AbstractModule.php");

final class MailingsController extends AbstractModule implements MailingController{

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

		$router->map('GET', '/mailings',
			function() {
				//Get the logged in user
				$user = $_SESSION["user"];
				$brf = $user->brf;

				//Query database for correct info
				require_once("model/module/ModuleInfo.php");
				$module = $this->dbContext->getModule($brf, "mailings");

				//Render view
				$view = "MailingsAdminView";
				require_once("views/mailings/".$view.".php");
				$viewObject = new $view($module, NULL);
				$viewObject->render();
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];}
		);

		$router->map('GET', '/[a:brf]/mailings',
			function($brf) {
				//Query database for correct info
				require_once("model/module/ModuleInfo.php");
				$module = $this->dbContext->getModule($brf, "mailings");

				//Render view
				$view = "MailingsView";
				require_once("views/mailings/".$view.".php");
				$viewObject = new $view($module, getErrors());
				$viewObject->render();
			},
			NULL,
			function(){return UserLevels::$userLevels["board_member"];}
		);

		$router->map('POST', '/[a:brf]/mailings/sendmail',
			function($brf) {

				//Check that all fields of form has been filled correctly
				$errors = array();

				if(!isset($_POST["subject"]) || empty($_POST["subject"])) {
					array_push($errors, "Du måste ange ett ämne!");
				}

				if(!isset($_POST["message"]) || empty($_POST["message"])) {
					array_push($errors, "Du måste ange ett meddelande!");
				}

				//If there were errors.
				if(count($errors) > 0) {
					setErrors($errors);
					header("Location: /".$brf."/mailings");
					return;
				}

				$subject = $_POST["subject"];
				$message = $_POST["message"];
				$send_to = $_POST["send_to"];
				$members = $this->dbContext->getBrfMembers($brf);

				// Filter out members to send to
				if($send_to != "all") {
					// Figure out what position to send to
					if($send_to == "board_members") {
						$position = "board_member";
					} else if ($send_to == "brf_members") {
						$position = "brf_member";
					}

					// Filter out correct members
					$members_to_send = array();
					foreach ($members as $member) {
						if ($member->position == $position) {
							array_push($members_to_send, $member);
						}
					}

					// Update $members
					$members = $members_to_send;
				}

				if (count($members) <= 0) {
					echo "Inga medlemmar inlagda. Mail skickades inte!";
					return;
				}

				// Send mail
				$this->sendMailingsEmail($members, $subject, $message, $send_to);

				echo "Mail skickat! Det kan ta några minuter innan mailet kommer fram.";
			},
			NULL,
			function(){return UserLevels::$userLevels["board_member"];}
		);
	}

	/*
	* Implementing abstract methods
	*/

	public function getDomain() {
		return "portal";
	}

	public function getModuleName() {
		return "mailings";
	}

	public function getBaseUrl() {
		return "mailings";
	}

	public function getLinkTitle() {
		return "Mailutskick";
	}

	public function getDefaultModule() {
		require_once("model/module/ModuleInfo.php");
		$module = new ModuleInfo();
        $module->name = "mailings";
		$module->brf = null;
		$module->title = "Mailutskick";
        $module->description = "Här kan du skicka mail till medlemmar i bostadsrättsföreningen och styrelsen";
		$module->userlevel = "board_member";
		$module->visible = 1;
		$module->sortindex = 7;
		$module->rightcol_sortindex = 1;
		return $module;
	}

	private function sendMailingsEmail($members, $subject, $message, $send_to) {
		$to = $members[0]->email;
		for ($x = 1; $x < count($members); $x++) {
			$email = $members[$x]->email;
		    $to = $to.", ".$email;
		}

		// Send email
		$result = $this->mailClient->sendMessage(
			$this->mailDomain, array(
			    'from'    => 'noreply@'.$this->mailDomain,
			    'to'      => $to,
			    'subject' => $subject,
				'html'	  => $message
			)
		);
	}

}

?>
