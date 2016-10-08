<?php
require_once("interfaces/iView.php");


class BookingInformationView implements iView{
	
	private $booking;

	public function __construct($booking) {
		$this->booking = $booking;
	}

	public function render(){
		
		if($this->booking != NULL) {
			echo "<h2>".$this->booking->bookingObjectName."</h2>";
			echo "<hr>";
            echo "<div class='textcolorGray'>";
			echo "<p><span class='textBold'>OBJEKT:</span> ".$this->booking->bookingObjectName."</p>";
			echo "<p><span class='textBold'>BOKNINGSDATUM:</span> ".$this->booking->start." - ".$this->booking->end."</p>";
			echo "<p><span class='textBold'>BOKAD AV:</span> ".$this->booking->firstName." ".$this->booking->lastName."</p>";
			echo "<p><span class='textBold'>E-POST:</span> ".$this->booking->email."</p>";
			echo "<hr/>";
			echo $this->booking->message;
            echo "</div>";
		}
	}
	
}

?>