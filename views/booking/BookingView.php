<?php
require_once("interfaces/iView.php");
require_once("views/listview/ListView.php");
require_once("views/listview/TextListItem.php");
require_once("views/listview/JSDeleteListItem.php");
require_once("views/listview/LinkListItem.php");
require_once("views/listview/TitleSubtitleListItem.php");
require_once("views/listview/JSCheckboxListItem.php");
require_once("views/listview/JSDropdownListItem.php");
require_once("views/moduleInfo/ModuleInfoView.php");

class BookingView implements iView{

	private $maxPage;
	private $errors;
	private $bookings;
    private $bookingObjects;
	private $moduleInfo;

	public function __construct($bookings, $bookingObjects, $maxPage, $moduleInfo, $errors) {
		$this->maxPage = $maxPage;
		$this->bookings = $bookings;
        $this->bookingObjects = $bookingObjects;
		$this->errors = $errors;
		$this->moduleInfo = $moduleInfo;
	}

	public function render(){

        ?>
        <div id='bookingHeader'>
        <?php
		$moduleView = new ModuleInfoView($this->moduleInfo);
		$moduleView->render();

		?>
        <br>
        <br>
        </div>
        <div id='bookingCalendar'>
        <?php

		require_once("views/rightcol/CalendarView.php");
		$v = new CalendarView($this->moduleInfo->name);
		$v->render();

        ?>
        </div>
        <?php

		if($this->errors && sizeof($this->errors) > 0){
            errors($this->errors);
        }

        ?>
        <div id='createBookingButton'>
        <br><br>
		<a class="button-blue" id="createNewBookingLink" href="#">GÖR EN NY BOKNING</a>
        <br><br>
        </div>
		<script>
            $("#createNewBookingLink").click(
                function(){
                    $("#bookingListViewPagination").hide();
                    $("#bookingHeader").hide();
                    $("#bookingCalendar").hide();
                    $("#createBookingButton").hide();
                    $("#createbookingForm").show();
                    $(document).scrollTop(0);
                }
            );
		</script>

		<form id="createbookingForm" style="display:none;" method='post' class='form' autocomplete='off'>
			<br>
			<div class='form-title'>Gör en ny bokning</div>
			<hr>
			<div class="form-section">
				<div class="form-input-large">
					<h6 class='form-description'>FÖRNAMN</h6>
					<input type='text' name='firstName' class="form-input-basic">
				</div>
				<div class="form-input-large">
					<h6 class='form-description'>EFTERNAMN</h6>
					<input type='text' name='lastName' class="form-input-basic">
				</div>
				<div class="form-input-large">
					<h6 class='form-description'>E-POSTADRESS</h6>
					<input type='text' name='email' class="form-input-basic">
				</div>
				<div class="form-input-large">
					<h6 class='form-description'>TELEFONNUMMER</h6>
					<input type='text' name='phone' class="form-input-basic">
				</div>
				<div class="form-input-large">
					<h6 class='form-description'>OBJEKT</h6>
                    <select name='bookingObject' class="form-input-basic-select">
                        <?php
                            foreach($this->bookingObjects as $bookingObject) {
                                echo "<option value='".$bookingObject->id."'>".$bookingObject->name."</option>";
                            }
                        ?>
                    </select>
				</div>
				<div class="form-input-small">
					<h6 class='form-description'>LÄGENHETSNUMMER</h6>
					<input type='text' name='apartment' class="form-input-basic">
				</div>
				<div class="form-input-large">
					<h6 class='form-description'>BOKNINGSDATUM FRÅN</h6>
					<input type='date' name='startDate' placeholder="ÅÅÅÅ-MM-DD" class="form-input-basic">
				</div>
				<div class="form-input-large">
					<h6 class='form-description'>TILL</h6>
					<input type='date' name='endDate' placeholder="ÅÅÅÅ-MM-DD" class="form-input-basic">
				</div>
                <div class="form-input-large">
					<h6 class='form-description'>TIDSINTERVALL FRÅN</h6>
					<input type='number' name='startTime' value=12 min=0 max=23 class="form-input-basic">
				</div>
				<div class="form-input-large">
					<h6 class='form-description'>TILL</h6>
					<input type='number' name='endTime' value=13 min=0 max=23 class="form-input-basic">
				</div>
				<div class="form-input-whole">
					<h6 class='form-description'>MEDDELANDE</h6>
					<input type='text' name='message' class="form-input-basic">
				</div>
				<br>
				<input type='submit' class="button-blue" value="BOKA"/>
			</div>
		</form>



		<?php

        if(count($this->bookings) <= 0) {
            return;
        }

        echo "<div id='bookingListViewPagination'>";

		$headers = array("Bokade tider","Datum & Tidsintervall");
        $headerStyles = array("text-align: left", "text-align: right");

		$rows = array();
		foreach ($this->bookings as $booking) {
			$values = array();
			array_push($values, new LinkListItem("/".$this->moduleInfo->brf."/bokning/".$booking->id ,new TextListItem($booking->bookingObjectName, $headerStyles[0]),true));
			array_push($values, new TextListItem($booking->start." - ".$booking->end, $headerStyles[1]));

			$row = array( "values" => $values );
			array_push($rows,$row);
		}

		$listView = new ListView($headers, $rows, $headerStyles);
		$listView->render();

		require_once("views/PaginationView.php");
		$viewObject = new PaginationView($this->maxPage);
		$viewObject->render();

        echo "</div>";
	}

}

?>
