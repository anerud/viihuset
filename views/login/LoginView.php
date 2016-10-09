<?php
require_once("interfaces/iView.php");

class LoginView implements iView{

	private $errors;

	public function __construct($errors= null) {
		$this->errors = $errors;
	}

	public function render(){
		?>

		<?php

	if(defined('USER_ID'))
		$g->send('admin');

	$show = " style='display: block;'";
	$hide = " style='display: none;'";

	$log_user = isset($_POST['log_user']) ? $_POST['log_user'] : '';
	//$side = $faq->get($g->page);
	?>

		<div class='content'>
			<div class='push'>
				<h2>Logga in</h2>
			</div>
			<div class='main'>
				<?php if($this->errors && sizeof($this->errors) > 0){errors($this->errors);} ?>
				<div class='con' id='login'>
				<form action='/auth' method='post' class='form' autocomplete='off'>
					<div class="form-description">Föreningens namn</div>
					<input class="form-input-basic" style="width: calc(50% - 2px) !important;" type='text' name='log_user' value='<?php echo $log_user; ?>' placeholder='Föreningens namn eller din e-post' tabindex='1' />
					<br><br>
					<div class="form-description">Lösenord</div>
					<input class="form-input-basic" type='password' name='log_pass' value='' placeholder='Lösenord' tabindex='2' />
					<a href='#password' class='forgot'>Glömt lösenordet?</a>
					<br><br>
					<div class='remember'>
						<input id='r' type='checkbox' name='remember' value='1' tabindex='3' />
						<label for='r'><span class="form-description">Förbli inloggad</span></label>
					</div>
					<input type='hidden' name='returnUrl' value='<?php echo isset($_GET["returnUrl"]) ? $_GET["returnUrl"] : (isset($_POST["returnUrl"]) ? $_POST["returnUrl"] : false) ?>' style="display: none;" />
					<br>
					<input class="button-blue" type='submit' class='submit radius' value="LOGGA IN"/>
				</form>
				</div>

			</div>
		</div>

	<?php
?>


		<?php
	}

}

?>
