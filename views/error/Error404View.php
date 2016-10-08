<?php
require_once("interfaces/iView.php");

class Error404View implements iView{

	public function render(){
		?>		
		<h1>Page not found</h1>
		<?php
	}
	
}

?> 
