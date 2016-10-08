<?php
require_once("interfaces/iController.php");
require_once("interfaces/MailingController.php");
require_once("interfaces/AbstractModule.php");

final class BookingController extends AbstractModule implements MailingController {

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
		$router->map('GET', '/bokning/[i:bookingObjectID]?',
			function() {
				$args = func_get_args();

				if(sizeof($args) == 0){
					require_once("model/booking/BookingObject.php");
					$currentBookingObject = new BookingObject();
				} else {
					$bookingObjectID = $args[0];
					$user = $_SESSION["user"];
					$brf = $user->brf;
					$currentBookingObject = $this->dbContext->getBookingObjectByIDAndBrf($bookingObjectID, $brf);
					if($currentBookingObject == NULL) {
						header("Location: /errors/404");
						return;
					}
				}
				$bookingObjectColors = $this->dbContext->getBookingObjectColors();
				$this->showAdminModuleView($currentBookingObject, $bookingObjectColors, getErrors());
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];}
		);

		//Admin page (POST) update booking object
		$router->map('POST', '/bokning/[i:bookingObjectID]?',
			function() {

				$user = $_SESSION["user"];
				$brf = $user->brf;

				$errors = array();

				if(!isset($_POST["name"]) || empty($_POST["name"])) {
					array_push($errors, "Bokningsobjektet måste ha ett namn!");
				}

				$args = func_get_args();
				$bookingObjectID = sizeof($args) == 0 ? NULL : $args[0];

				if(!empty($errors)) {
					setErrors($errors);
					header("Location: /bokning".$bookingObjectID != NULL ? "/".$bookingObjectID : "");
					return;
				}

				$name = $_POST["name"];
				$description = $_POST["description"];
				$color = $_POST["color"];
				$notifyBoard = isset($_POST["notifyBoard"]);
				$sendConfirmation = isset($_POST["sendConfirmation"]);
				$confirmationMessage= $_POST["confirmationMessage"];

				$args = func_get_args();
				if(sizeof($args) == 0){
					//Create new booking
					$this->dbContext->createBookingObject(	$brf,
															$color,
															$name,
															$description,
															$notifyBoard,
															$sendConfirmation,
															$confirmationMessage);
				} else {
					//Update existing booking
					$bookingObjectID = $args[0];
					$this->dbContext->updateBookingObjectByIDAndBrf($bookingObjectID,
																	$brf,
																	$color,
																	$name,
																	$description,
																	$notifyBoard,
																	$sendConfirmation,
																	$confirmationMessage);
				}

				header("Location: /bokning");
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];}
		);

		//Admin page (DELETE) booking object
		$router->map('DELETE', '/bokning/[i:bookingObjectID]',
			function($bookingObjectID) {
				$user = $_SESSION["user"];
				$brf = $user->brf;
				$this->dbContext->deleteBookingObjectByIDAndBrf($bookingObjectID, $brf);
				header("HTTP/1.1 200 OK");
				return;
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];}
		);


		//Brf page (GET)
		$router->map('GET', '/[a:brf]/bokning',
			function($brf) {
				require_once("model/module/ModuleInfo.php");
				$moduleInfo = $this->dbContext->getModule($brf,"booking");
				$errors = getErrors();

				require_once("views/booking/BookingView.php");
				$page = intval(isset($_GET["page"]) ? $_GET["page"] : 1);
				list($maxPage, $bookings) = $this->dbContext->getPagedBookingsByBrf($brf, $page);

                $bookingObjects = $this->dbContext->getBookingObjectNamesAndIds($brf);

				$view = new BookingView($bookings, $bookingObjects, $maxPage, $moduleInfo, $errors);
				$view->render();
			},
			NULL,
			function(){return UserLevels::$userLevels["brf"];}
		);

		//Brf page (GET)
		$router->map('GET', '/[a:brf]/bokning/[i:id]',
			function($brf, $bookingID) {
				$booking = $this->dbContext->getBookingByIDAndBrf($bookingID,$brf);
				require_once("views/booking/BookingInformationView.php");
				$view = new BookingInformationView($booking);
				$view->render();
				//TODO: Get the booking with bookingID and brf then render a view for the booking.
			},
			NULL,
			function(){return UserLevels::$userLevels["brf"];}
		);

		//Brf page (POST)
		$router->map('POST', '/[a:brf]/bokning',
			function($brf) {

				$errors = array();

				if(!isset($_POST["firstName"]) || empty($_POST["firstName"])) {
					array_push($errors, "Du måste ange ditt förnamn!");
				}

				if(!isset($_POST["lastName"]) || empty($_POST["lastName"])) {
					array_push($errors, "Du måste ange ditt efternamn!");
				}

				if(!isset($_POST["email"]) || empty($_POST["email"])) {
					array_push($errors, "Du måste ange din email!");
				}

				if(!isset($_POST["phone"]) || empty($_POST["phone"])) {
					array_push($errors, "Du måste ange din telefonnummer!");
				}

				if(!isset($_POST["bookingObject"]) || empty($_POST["bookingObject"])) {
					array_push($errors, "Du måste ange vilket objekt du vill boka!");
				}

				if(!isset($_POST["apartment"]) || empty($_POST["apartment"])) {
					array_push($errors, "Du måste ange ditt lägenhetsnummer!");
				}

				if(!isset($_POST["startDate"]) || empty($_POST["startDate"])) {
					array_push($errors, "Du måste ange startdatum för bokningen!");
				}

				if(!isset($_POST["endDate"]) || empty($_POST["endDate"])) {
					array_push($errors, "Du måste ange slutdatum för bokningen!");
				}

                if(!isset($_POST["startDate"]) || empty($_POST["startDate"])) {
					array_push($errors, "Du måste ange startdatum för bokningen!");
				}

				if(!isset($_POST["endDate"]) || empty($_POST["endDate"])) {
					array_push($errors, "Du måste ange slutdatum för bokningen!");
				}

                if(!isset($_POST["startTime"]) || empty($_POST["startTime"])) {
					array_push($errors, "Du måste ange starttid för bokningen!");
				}

				if(!isset($_POST["endTime"]) || empty($_POST["endTime"])) {
					array_push($errors, "Du måste ange sluttid för bokningen!");
				}

				if(!empty($errors)) {
					setErrors($errors);
					header("Location: /".$brf."/bokning");
					return;
				}

				$firstName = $_POST["firstName"];
				$lastName = $_POST["lastName"];
				$email = $_POST["email"];
				$phone = $_POST["phone"];
				$bookingObject = $_POST["bookingObject"];
				$apartment = $_POST["apartment"];
				$start = $_POST["startDate"]." ".$_POST["startTime"].":00:00";
				$end = $_POST["endDate"]." ".$_POST["endTime"].":00:00";;
				$message = $_POST["message"];
				$accepted = 1;

				$success = $this->dbContext->createBooking(
                    $bookingObject,
                    $firstName,
                    $lastName,
                    $email,
                    $phone,
                    $apartment,
                    $start,
                    $end,
                    $message,
                    $accepted
                );

				header("Location: /".$brf."/bokning");
			},
			NULL,
			function(){return UserLevels::$userLevels["brf"];}
		);

	}

	public function showAdminModuleView($currentBookingObject, $bookingObjectColors, $errors){

		//Get the logged in user
		$user = $_SESSION["user"];

		//Query database for correct info
		require_once("model/module/ModuleInfo.php");
		$module = $this->dbContext->getModule($user->brf, "booking");

		//Get current page in pagination
		$page = intval(isset($_GET["page"]) ? $_GET["page"] : 1);
		//Collect booking objects for the view
		list($lastPage, $pageResults) = $this->dbContext->getPagedBookingObjectsByBrf($user->brf, $page);

		//Render view
		$view = "BookingObjectAdminView";
		require_once("views/booking/".$view.".php");
		$viewObject = new $view($pageResults, $currentBookingObject, $lastPage ,$module, $bookingObjectColors, $errors);

		$viewObject->render();
	}
	/*
	* Implementing abstract methods
	*/

	public function getDomain() {
		return "portal";
	}

	public function getModuleName() {
		return "booking";
	}

	public function getBaseUrl() {
		return "bokning";
	}

	public function getLinkTitle() {
		return "Boka tid";
	}

	public function getDefaultModule() {
		require_once("model/module/ModuleInfo.php");
		$module = new ModuleInfo();
        $module->name = "booking";
		$module->brf = null;
		$module->title = "Boka tid";
        $module->description = "Här kan du boka tider för föreningens olika bokningsobjekt.";
		$module->userlevel = "brf";
		$module->visible = 1;
		$module->sortindex = 5;
		$module->rightcol_sortindex = 0;
		return $module;
	}

	public function getRightColModule($currentBrf) {
		require_once("views/rightcol/CalendarView.php");
		return new CalendarView($this->getDefaultModule()->name);
	}

}

?>
