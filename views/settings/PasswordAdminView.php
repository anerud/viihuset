<?php
require_once("interfaces/iView.php");

class PasswordAdminView implements iView{

	private $errors;
	private $boardMemberActive;
    private $brfMemberActive;

	public function __construct($users, $errors){
		$this->errors = $errors;
        foreach($users as $user) {
            if(strcmp($user->userlevel, "board_member") == 0) {
                $this->boardMemberActive = $user->active;
            }
            if(strcmp($user->userlevel, "brf_member") == 0) {
                $this->brfMemberActive = $user->active;
            }
        }
	}

	public function render(){

		?>

        <style>
            input[type='text']{
                background-color: #fAfAfA;
                border: 1px solid #eaeaea;
                padding: 0;
            }
			.myTable {
				border-collapse:separate;
				border-spacing: 10px ;
			}
        </style>

        <h2>Lösenord & Behörigheter</h2>
        <hr>

        <div class="textcolorGray adminPassword">

		<form method='post' class='form' autocomplete='off'>
            <table class="myTable">
                <colgroup>
                    <col span="1" style="width: 10%;">
                    <col span="1" style="width: 45%;">
                    <col span="1" style="width: 45%;">
                </colgroup>
                <!-- Admin -->
                <tr class="text textcolorDarkGray">
                    <td class="center">Aktiva</td>
                    <td>Administratör lösenord</td>
                    <td></td>
                </tr>
                <tr class="input">
                    <td class="center"><input type='checkbox' name='activeAdmin' checked disabled/></td>
                    <td><input type='text' class="textcolorGray form-input-basic" name='passwordAdmin' value='******'/></td>
                    <td class="info textcolorGray">Lösenord fungerar för alla behörigheter</td>
                </tr>

                <!-- Board member -->
                <tr class="text textcolorDarkGray">
                    <td></td>
                    <td>Styrelsemedlem lösenord</td>
                    <td></td>
                </tr>
                <tr  class="input">
                    <td class="center"><input type='checkbox' name='activeBoardMember'<?php echo $this->boardMemberActive ? "checked" : ""?>/></td>
                    <td><input type='text' class="textcolorGray form-input-basic" name='passwordBoardMember' value='<?php echo $this->boardMemberActive ? "******" : ""?>'/></td>
                    <td class="info textcolorGray">Lösenord fungerar för styrelse & föreningsmedlemmar</td>
                </tr>

                <!-- Brf member -->
                <tr class="text textcolorDarkGray">
                    <td></td>
                    <td>Föreningsmedlem lösenord</td>
                    <td></td>
                </tr>
                <tr  class="input">
                    <td class="center"><input type='checkbox' name='activeBrfMember' <?php echo $this->brfMemberActive ? "checked" : ""?>/></td>
                    <td><input type='text' class="textcolorGray form-input-basic" name='passwordBrfMember' value='<?php echo $this->brfMemberActive ? "******" : ""?>'/></td>
                    <td valign="middle" class="info textcolorGray">Lösenord fungerar endast för föreningsmedlemmar</td>
                </tr>
            </table>

            <hr/>

            <div id="savecon">
                <button id="save" class="button-blue">Spara</button>
                <div id="loader" style="display: none;"></div>
            </div>
			<input hidden=true id="submitButton" type='submit' name='submit' value='Spara' />

			<script>
                var saving = false;
	            $("#save").click(function(){
	                if(!saving){
	                    saving = true;
	                    $("#loader").show();
						document.getElementById("submitButton").submit();
	                }
	            });
			</script>
		</form>

        </div>
		<?php
	}

}

?>
