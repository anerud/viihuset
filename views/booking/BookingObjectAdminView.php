<?php
require_once("interfaces/iView.php");
require_once("views/listview/ListView.php");
require_once("views/listview/TextListItem.php");
require_once("views/listview/JSDeleteListItem.php");
require_once("views/listview/LinkListItem.php");
require_once("views/listview/TitleSubtitleListItem.php");
require_once("views/listview/JSCheckboxListItem.php");
require_once("views/listview/JSDropdownListItem.php");
require_once("views/moduleInfo/ModuleInfoAdminView.php");

class BookingObjectAdminView implements iView{

	private $maxPage;
	private $errors;
	private $bookingObjects;
	private $moduleInfo;
	private $currentObject;
	private $bookingObjectColors;

	public function __construct(
		$bookingObjects,
		$currentObject,
		$maxPage,
		$moduleInfo,
		$bookingObjectColors,
		$errors
	) {
		$this->maxPage = $maxPage;
		$this->bookingObjects = $bookingObjects;
		$this->moduleInfo = $moduleInfo;
		$this->currentObject = $currentObject;
		$this->bookingObjectColors = $bookingObjectColors;
		$this->errors = $errors;
	}

	public function render(){

		if($this->currentObject->id == null){
			$moduleView = new ModuleInfoAdminView($this->moduleInfo);
			$moduleView->render();
		}

		if($this->errors && sizeof($this->errors) > 0){errors($this->errors);}

		if($this->currentObject->id == null){
			echo '<a id="createNewBookingObjectLink" class="button-blue" href="#">Lägg till bokningsobjekt</a>';
			echo '<script>'	;
			echo '$("#createNewBookingObjectLink").click(
                    function(){
                        $(this).hide();
                        $("#moduleInfoAdminView").hide();
                        $("#createbookingForm").show();
                        $(document).scrollTop(0);
                    }
                );';
			echo '</script>';
		}
		?>
		<form id="createbookingForm" <?php if($this->currentObject->id == null){echo 'style="display:none;"'; } ?>  method='post' class='form' autocomplete='off'>
            <input type='hidden' name="returnUrl" value="<?php echo $_SERVER["REQUEST_URI"]; ?>"/>
            <div class="form-section">
                    <div class="form-input-whole">
                    <h5 class='form-description'>Bokningsobjekt (benämning)</h5>
                    <input type='text' name='name' value='<?php echo $this->currentObject->name;?>' placeholder='Titel' tabindex='1' class="form-input-basic"/>
                </div>
                <div class="form-input-whole">
                    <h5 class='form-description'>Beskrivning av objektet</h5>
                    <textarea type='text' name='description' value='' placeholder='Meddelande' tabindex='3' class="form-input-area"><?php echo $this->currentObject->description;?></textarea>
                </div>
                    <div class="form-input-whole">
                    <h5 class='form-description'>Välj markeringsfärg för objektet</h5>
                    <div class="JSDropdownListItem">
                        <select name="color" class="select select-flat">
                        <?php
                        foreach($this->bookingObjectColors as $color) {
                            echo '<option value="'.$color->color.'" '.($color->color == $this->currentObject->color ? 'selected' : '').'>'.$color->color.'</option>';
                        }
                        ?>
                        </select>
                    </div>
                </div>
                <div class="form-input-whole">
                    <h5 class='form-description'>Skicka bokningskopia (via e-post) till styrelsen</h5>
                    <input type='checkbox' name='notifyBoard' <?php echo $this->currentObject->notifyBoard ? "checked" : "" ?>/>
                </div>
                <div class="form-input-whole">
                    <h5 class='form-description'>Bekräftelse (via e-post) till den som bokar tid</h5>
                    <input type='checkbox' name='sendConfirmation' <?php echo $this->currentObject->sendConfirmation ? "checked" : "" ?>/>
                </div>
                    <div class="form-input-whole">
                    <h5 class='form-description'>Meddelande som inkluderas i användarens bekräftelsebrev</h5>
                    <input class="form-input-basic" type='text' name='confirmationMessage' value='<?php echo $this->currentObject->confirmationMessage;?>' placeholder='' tabindex='1' />
                </div>

                <input class="button-blue" type='submit' name='submit' value='SPARA' tabindex='5' /></td>
            </div>
		</form>

		<?php
		if($this->currentObject->id == null && count($this->bookingObjects) > 0){

			echo '<hr>';
			$headers = array("Inlagda bokningsobject","radera");
            $headerStyles = array("text-align: left","text-align: right");
			$rows = array();
			foreach ($this->bookingObjects as $bo) {
				$values = array();
				array_push($values, new LinkListItem($bo->brf."/bokning/".$bo->id ,new TextListItem($bo->name, $headerStyles[0]),false));
				array_push($values, new JSDeleteListItem("/bokning/".$bo->id, $headerStyles[1]));

				$row = array("values" => $values );
				array_push($rows,$row);
			}

			$listView = new ListView($headers, $rows, $headerStyles);
			$listView->render();

			require_once("views/PaginationView.php");
			$viewObject = new PaginationView($this->maxPage);
			$viewObject->render();

		}
	}

}

?>
