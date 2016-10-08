<?php
require_once("interfaces/iView.php");

class UserAgreementView implements iView{

	public function render(){
		?>



		<div class='content'>
			<div class='push'>
				<p class='start'></p>
				<h2>Sekretess &amp; användaravtal</h2>
				<p class="info">Senast uppdaterad: INSERT TIME HERE</p>
				<div class='search'>
					<img src='gfx/search.png' alt='Search' />
					<input type='text' name='search' autocomplete='off' value='' class='normal radius' placeholder='Sök' />
					<div class='results'></div>
				</div>
						<?php if(isset($_SESSION["user"])) { ?>
						<a href='/admin' class='begin radiusBottom'>Kontrollpanel</a>
						<a href='/logout' class='auth radiusBottom'>Logga ut</a>
						<?php } else { ?>
						<a href='/register' class='begin radiusBottom'>Börja här!</a>
						<a href='/auth' class='auth radiusBottom'>Logga in</a>
						<?php } ?>
			</div>
			<div class='main avtal'>
				<?php
					echo "Text from SQLQuery here";
				?>
			</div>
		</div>

		<?php
	}

}

?>
