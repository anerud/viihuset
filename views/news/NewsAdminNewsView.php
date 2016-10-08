<?php
require_once("interfaces/iView.php");
require_once("views/moduleInfo/ModuleInfoAdminView.php");
require_once("views/listview/LinkListItem.php");
require_once("views/listview/JSDeleteListItem.php");
require_once("views/listview/JSCheckboxListItem.php");

class NewsAdminNewsView implements iView{

	private $errors;
    private $news;

	public function __construct($news, $errors){
        $this->news = $news;
		$this->errors = $errors;
	}

	public function render(){

		if($this->errors && sizeof($this->errors) > 0){
            errors($this->errors);
        }

        $newsId = $this->news == null ? null : $this->news->id;
        $newsTitle = $this->news == null ? "" : $this->news->title;
        $newsText = $this->news == null ? "" : $this->news->text;
        $userLevel = $this->news == null ? "" : $this->news->userlevel;
        $showPeriod = $this->news == null ? false : $this->news->show_period;
        $showFrom = $this->news == null ? "" : explode(" ", $this->news->show_start)[0];
        $showTo = $this->news == null ? "" : explode(" ", $this->news->show_to)[0];
        $showCalendar = $this->news == null ? false : $this->news->show_calendar;
        $showCalendarDate = $this->news == null ? "" : explode(" ", $this->news->show_calendar_date)[0];

        ?>
        <form action='<?php echo "/nyheter/".$newsId; ?>' method='post' class='form moduleInfoForm' autocomplete='off'>
			<h3>Titel</h3>
			<div class="input-wrapper"><input type='text' name='title' value='<?php echo $newsTitle;?>' placeholder='' tabindex='1' /></div>
			<h3>Nyhetstext</h3>
			<textarea name='text' placeholder='' tabindex='2' ><?php echo $newsText;?></textarea>
			<input type='hidden' name="returnUrl" value="<?php echo $_SERVER["REQUEST_URI"]; ?>"/>
			<br>
			<h3>Lösenordsnivå för att visa just denna nyhet</h3>
			<div class="JSDropdownListItem">
			<select name="userLevel" class="select select-flat">
			<?php
			foreach(UserLevels::$userLevels as $key => $val){
				if($val >= UserLevels::$userLevels["brf"] && $val <= UserLevels::$userLevels["admin"]) {
					echo '<option value="'.$key.'" '.($key == $userLevel ? 'selected' : '').'>'.UserLevels::$namesToDescription[$key].'</option>';
				}
			}
			?>
			</select>
            <hr>
			</div>

            <table>
                <tr>
                    <td>
                        <input type='checkbox' name='showPeriod' <?php echo $showPeriod ? "checked" : "" ?>>
							<span class="textSize13px textcolorGray">Visa nyhet under denna period</span>
						</input>
                    </td>
					<td>
                        <input type='date' name='showFrom' class="form-input-basic" value='<?php echo $showFrom; ?>' placeholder="ÅÅÅÅ-MM-DD" class="form-booking-input-basic">
                    </td>
                    <td>
                        <span>-</span>
                    </td>
                    <td>
                        <input type='date' name='showTo' class="form-input-basic" value='<?php echo $showTo; ?>' placeholder="ÅÅÅÅ-MM-DD" class="form-booking-input-basic">
                    </td>
                </tr>

                <tr>
                    <td>
                        <input type='checkbox' name='showCalendar' <?php echo $showCalendar ? "checked" : "" ?>>
							<span class="textSize13px textcolorGray">Visa som kalenderhändelse</span>
						</input>
                    </td>
                    <td>
                        <input type='date' name='showCalendarDate' class="form-input-basic" value='<?php echo $showCalendarDate; ?>' placeholder="ÅÅÅÅ-MM-DD" class="form-booking-input-basic">
                    </td>
                </tr>

                <tr>
                    <td colspan="3">
                        <input class="button-blue" type='submit' value="SPARA"/>
                    </td>
                </tr>
            </table>
		</form>
		<hr/>

        <?php
	}

}

?>
