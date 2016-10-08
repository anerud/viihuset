<?php
require_once("interfaces/iView.php");

class BasicInfoView implements iView{

	private $errors;
	private $brfInfo;
	private $nVisitors;

	public function __construct($brfInfo, $nVisitors, $errors){
		$this->brfInfo = $brfInfo;
		$this->nVisitors = $nVisitors;
		$this->errors = $errors;
	}

	public function render(){

		?>

        <style>
            .basicInfoTable{
                width: 70%;
            }

            .basicInfoTable td {
                margin-left: 10px;
            }

            .basicInfoTable input[type='text']{
                background-color: #fAfAfA;
                border: 1px solid #eaeaea;
                padding: 0;
            }
        </style>

        <h2>Basinformation</h2>
        <hr>

		<form method='post' class='form' autocomplete='off'>

            <table class="basicInfoTable">

                <col span="1" style="width: 50%;">
                <col span="1" style="width: 50%;">

                <tr>
                    <td class="form-description">Namn på BRF</td>
                    <td></td>
                </tr>

                <tr>
                    <td><input type='text' class="form-input-basic textcolorLightGray" name='brfName' value='<?php echo $this->brfInfo->original_name;?>' disabled/></td>
                    <td></td>
                </tr>

                <tr>
                    <td class="form-description">E-Postadress</td>
                    <td></td>
                </tr>

                <tr>
                    <td><input type='text' class="form-input-basic" name='email' value='<?php echo $this->brfInfo->email;?>'/></td>
                    <td></td>
                </tr>

            </table>

			<hr/>

            <table class="basicInfoTable">

                <tr>
                    <td><h2>Föreningadress</h2><hr></td>
                    <td><h2>Besöksadress</h2><hr></td>
                </tr>

                <tr>
                    <td class="form-description">Föreningsadress</td>
                    <td class="form-description">Besöksadress</td>
                </tr>

                <tr>
                    <td><input type='text' class="form-input-basic" name='brfAddress' value='<?php echo $this->brfInfo->brfAddress;?>' /></td>
                    <td><input type='text' class="form-input-basic" name='visitAddress' value='<?php echo $this->brfInfo->visitAddress;?>' /></td>
                </tr>

                <tr>
                    <td class="form-description">Postnummer</td>
                    <td class="form-description">Postnummer</td>
                </tr>

                <tr>
                    <td><input type='text' class="form-input-basic" name='brfPostal' value='<?php echo $this->brfInfo->brfPostal;?>' /></td>
                    <td><input type='text' class="form-input-basic" name='visitPostal' value='<?php echo $this->brfInfo->visitPostal;?>' /></td>
                </tr>

                <tr>
                    <td class="form-description">Ort</td>
                    <td></td>
                </tr>

                <tr>
                    <td><input type='text' class="form-input-basic" name='city' value='<?php echo $this->brfInfo->city;?>' /></td>
                    <td></td>
                </tr>

            </table>

            <br>
            <div id="savecon">
                <button id="save" class="button-blue">Spara</button>
                <div id="loader" style="display: none;"></div>
            </div>
			<input hidden=true id="submitButton" type='submit' name='submit' value='Spara' />
			<script>
	            $("#save").click(
					function(){
						document.getElementById("submitButton").submit();
                	}
				);
			</script>

		</form>

		<hr>


		<table class="basicInfoTable">

            <col span="1" style="width: 20%;">
            <col span="1" style="width: 10%;">
            <col span="1" style="width: 70%;">

            <tr class="textcolorGray">
                <td>Antal besök:</td>
				<td>
					<form class='form'>
						<input type='text' class="form-input-basic" value='<?php echo $this->nVisitors; ?>' disabled/>
					</form>
				</td>
				<?php
                $datetimearray = explode(" ", $this->brfInfo->registered_at);
                $date = $datetimearray[0];
				echo "<td>sedan ".$date."</td>";
				?>
            </tr>

        </table>

		<?php
	}

}

?>
