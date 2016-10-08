<?php
require_once("interfaces/iView.php");

class RegisterView implements iView{

	private $errors;

	public function __construct($errors){
		$this->errors = $errors;
	}

	public function render(){

	?>

		<div class='content'>
			<div class='push'>
				<h2>Skapa konto</h2>
			</div>
			<div class='main'>
				<?php if($this->errors && sizeof($this->errors) > 0){errors($this->errors);} ?>
				<div class='con' id='login'>
				<form action='/register' method='post' class='form' autocomplete='off'>
					<div class="form-description"><div>Föreningens namn</div>
					<input type='text' name='reg_brf' class="form-input-basic form-input-half-no-padding" value='' placeholder='Namn på brf' tabindex='2' />

					<br>
					<br>
					<div class="form-description"><div>E-postadress</div>
					<input type='text' name='reg_email' class="form-input-basic form-input-half-no-padding" value='' placeholder='din epost-adress' tabindex='3' />

					<br>
					<br>
					<div class="form-description"><div>Lösenord</div>
					<input type='password' name='reg_pass' class="form-input-basic" value='' placeholder='Lösenord' tabindex='4' />
					<input type='password' name='reg_pass_re' class="form-input-basic" value='' placeholder='Bekräfta lösenord' tabindex='5' />

					<br>
					<br>
					<div class='remember'>
						<input id='r' type='checkbox' name='reg_remember' value='1' tabindex='6' />
						<label for='r'>Kom ihåg mig</label>
					</div>
					<br>
                    <input type="submit" class="button-blue" value="Registrera" tabindex='6'/>
				</form>
				</div>
			</div>
		</div>

		<?php
	}

}

?>
