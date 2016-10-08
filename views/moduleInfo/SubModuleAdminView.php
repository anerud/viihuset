<?php
require_once("interfaces/iView.php");

class SubModuleAdminView implements iView{

	private $moduleInfo;

	public function __construct($moduleInfo) {
		$this->moduleInfo = $moduleInfo;
	}

	public function render(){

		?>

        <div id="moduleInfoAdminView">

            <form action='/<?php echo $this->moduleInfo->parent."/".$this->moduleInfo->name."/moduleInfo"?>' method='post' class='form moduleInfoForm' autocomplete='off'>
                <h3>Rubrik</h3>

                <div class="input-wrapper">
                    <input type='text' name='title' value='<?php echo $this->moduleInfo->title;?>' placeholder='' tabindex='1' />
                </div>

                <br>

                <h3>Beskrivning</h3>

                <textarea name='description' placeholder='' tabindex='2' ><?php echo $this->moduleInfo->description;?></textarea>
                <input type='hidden' name="returnUrl" value="<?php echo $_SERVER["REQUEST_URI"]; ?>"/>

                <br>

                <h3>Lösenordsnivå för att visa <?php echo $this->moduleInfo->title?></h3>

                <div class="JSDropdownListItem">
                    <select name="moduleUserLevel" class="select select-flat">
                    <?php
                    foreach(UserLevels::$userLevels as $key => $val){
                        if($val >= UserLevels::$userLevels["brf"] && $val <= UserLevels::$userLevels["admin"]) {
                            echo '<option value="'.$key.'" '.($key == $this->moduleInfo->userlevel ? 'selected' : '').'>'.UserLevels::$namesToDescription[$key].'</option>';
                        }
                    }
                    ?>
                    </select>
                </div>

                <input class="button-blue" type='submit' value="SPARA"/>
            </form>
            <hr/>

        </div>
		<?php
	}
}
?>
