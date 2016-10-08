<?php
require_once("interfaces/iView.php");

class CalendarView implements iView{
	

    private $module;
	public function __construct($module) {
        $this->module = $module;
	}

	public function render(){
		?>
		<div class='calendarCon rightcolContainer rightModule'>
			<div class="calendarDiv">
					
				<div class="rightcolHeader calendarHeader bgcolor2 textcolor0">
                    <div class='columnarrows'>
                        <div class='columnup' data-module='<?php echo $this->module;  ?>'></div> 
                        <div class='columnarrowspace'></div> 
                        <div class='columndown' data-module='<?php echo $this->module;  ?>'></div> 
                    </div>
                    
                    <div class="title">
					<div class="calendarLeft textcolor3">&lt;</div>
					<div class="calendarRight textcolor3">&gt;</div>
					<div class="calendarTitle textcolor0"></div>
                    </div>
				</div>
				
				<div class="calendarBorder rightcolBorder">
					<table class="calendarWeekdays bgcolor1 textcolor3">
						<td><div class="textcolor3">M</div></td>
						<td><div class="textcolor3">T</div></td>
						<td><div class="textcolor3">O</div></td>
						<td><div class="textcolor3">T</div></td>
						<td><div class="textcolor3">F</div></td>
						<td><div class="textcolor3">L</div></td>
						<td><div class="textcolor3">S</div></td>
					</table>
					<div class="calendarBody rightcolBody">
						
					</div>
				</div>
			</div>
		</div>
		
		
		<?php	
	}
	
}

?>