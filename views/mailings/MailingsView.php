<?php
require_once("interfaces/iView.php");
require_once("views/moduleInfo/ModuleInfoView.php");

class MailingsView implements iView{

	private $errors;
	private $moduleInfo;

	public function __construct($moduleInfo, $errors){
		$this->moduleInfo = $moduleInfo;
		$this->errors = $errors;
	}

	public function render(){

		$moduleView = new ModuleInfoView($this->moduleInfo);
		$moduleView->render();

        if($this->errors && sizeof($this->errors) > 0){
            errors($this->errors);
        }

		?>

        <div id="moduleInfoAdminView">

            <form action='/<?php echo $this->moduleInfo->brf?>/<?php echo $this->moduleInfo->name?>/sendmail' method='post' class='form moduleInfoForm' autocomplete='off'>
                <h3>Ämne</h3>

                <div class="input-wrapper">
                    <input type='text' name='subject' placeholder='Skriv ett ämne' tabindex='1'/>
                </div>

                <br>

                <h3>Meddelande</h3>

                <textarea name='message' placeholder='Skriv ett meddelande' tabindex='2'></textarea>
                <input type='hidden' name="returnUrl" value="<?php echo $_SERVER["REQUEST_URI"]; ?>"/>

                <br>

                <h3>Skicka till</h3>

                <div class="JSDropdownListItem">
                    <select name="send_to" class="select select-flat" tabindex='3'>
                    <?php
                    echo '<option value="board_members" selected>Styrelsemedlemmar</option>';
                    echo '<option value="brf_members">Medlemmar i bostadsrättsförening</option>';
                    echo '<option value="all">Alla</option>';
                    ?>
                    </select>
                </div>
                <input class="button-blue" type='submit' value="SKICKA" tabindex='4'/>
            </form>
            <hr/>

        </div>

		<?php
	}

}

?>
