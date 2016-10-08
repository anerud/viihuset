<?php
require_once("interfaces/iController.php");

final class CalendarController implements iController{

	private $dbContext;

	public function init($router, $dbContext){
		$this->dbContext = $dbContext;

		//Brf page
		$router->map('GET', '/[a:brf]/bookingsThisMonth',
			function($brf) {
				if(!isset($_GET["year"]) && !isset($_GET["month"])) {
					echo json_encode(array());
					return;
				}

				//year and month was provided
				$year = $_GET["year"];
				$month = $_GET["month"];
				header('Content-Type: application/json');
				$bookings = $this->dbContext->getBookingsByYearAndMonth($brf, $year, $month);
				echo json_encode($bookings);
				// foreach ($bookings as $booking) {
				// 	$
				// }
			},
			NULL,
			function(){return 1;},
			"",
			false
		);

	}

	public function getLinksByLevel($level, $endpointLevel, $brf, $domain) {
		return null;
	}

}

?>
