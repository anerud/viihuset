<?php
require_once("interfaces/iView.php");
require_once("views/moduleInfo/ModuleInfoAdminView.php");

class SysAdminView implements iView{

	private $allBrfs;
	private $currentBrf;

	public function __construct($allBrfs, $currentBrf){
		$this->allBrfs = $allBrfs;

		# remove sysadmin
		foreach ($this->allBrfs as $index => $brf) {
			if ($brf->name == 'sysadmin') {
				unset($this->allBrfs[$index]);
				break;
			}
		}

		# If no currentBrf, set first in list
		if ($currentBrf == null) {
			$this->currentBrf = $allBrfs[0];
		} else {
			$this->currentBrf = $currentBrf;
		}
	}

	public function render(){
		?>

		<!--  Container div -->
		<div class="sysadmin-container">
			<!--  List of brfs -->
			<div class="sysadmin-brf-list">
				<?php
				foreach($this->allBrfs as $brf) {
					echo '<a href="/sysadmin/'.$brf->name.'" class="adminpanellink">';
					echo $brf->name;
					echo "</a>";
					echo "<br>";
				}
				?>
			</div>

			<!--  Current brf info -->
			<div class="sysadmin-brf-info">
				<form method='post' action='/sysadmin/<?php echo $this->currentBrf->name?>' class='form' autocomplete='off'>

		            <table class="basicInfoTable">
		                <col span="1" style="width: 50%;">
		                <col span="1" style="width: 50%;">

		                <tr>
		                    <td class="form-description">Namn på BRF</td>
		                    <td class="form-description">E-Postadress</td>
		                </tr>

		                <tr>
		                    <td><input type='text' class="form-input-basic textcolorLightGray" name='brfName' value='<?php echo $this->currentBrf->name?>' disabled/></td>
		                    <td><input type='text' class="form-input-basic textcolorLightGray" name='email' value='<?php echo $this->currentBrf->email?>' disabled/></td>
		                </tr>
		            </table>

					<hr/>

		            <table class="basicInfoTable">

		                <tr>
		                    <td class="form-description">Giltighetstid</td>
		                    <td class="form-description">Abonemang PÅ/AV</td>
		                </tr>

		                <tr>
							<?php $validity_period = $this->currentBrf->validity_period == null ? "" : explode(" ", $this->currentBrf->validity_period)[0]; ?>
		                    <td><input type='date' class="form-input-basic" name='validity_period' value='<?php echo $validity_period?>'/></td>
		                    <td><input type='checkbox' name='activated' <?php echo $this->currentBrf->activated ? 'checked' : ''?>/></td>
		                </tr>

		                <tr>
							<td><br></td>
		                </tr>

		                <tr>
		                    <td class="form-description">Domännamn</td>
		                </tr>

		                <tr>
		                    <td><input type='text' class="form-input-basic" name='domain_name' value='<?php echo $this->currentBrf->domain_name?>' /></td>
		                </tr>

		                <tr>
							<td><br></td>
		                </tr>

		                <tr>
		                    <td><a class="button-blue" href="/sysadmin/becomebrf/<?php echo $this->currentBrf->name?>">Bli användare</a> </td>
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
			</div>

		</div>

		<?php
	}

}

?>
