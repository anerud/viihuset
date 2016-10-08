
<?php
require_once("interfaces/iController.php");
require_once("interfaces/AbstractModule.php");

final class PasswordController implements iController{
	
	private $dbContext;
	 
	public function init($router, $dbContext){
		
		$this->dbContext = $dbContext;
		
		//Brf info page
		$router->map('GET', '/losenord',
			function() {
				$user = $_SESSION["user"];
				$brf = $user->brf;
				$users = $this->dbContext->getUsers($brf);

				require_once("views/settings/PasswordAdminView.php");
				$view = new PasswordAdminView($users, getErrors());
				$view->render();
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];},
			null,
			true,
			"settings"
		);
		
		//Brf info page
		$router->map('POST', '/losenord',
			function() {
				$user = $_SESSION["user"];
				$brf = $user->brf;
				
				$passwordAdmin = $_POST["passwordAdmin"];
				$passwordBoardMember = $_POST["passwordBoardMember"];
				$activeBoardMember = isset($_POST["activeBoardMember"]);
                $passwordBrfMember = $_POST["passwordBrfMember"];
				$activeBrfMember = isset($_POST["activeBrfMember"]);
                
                //Try update password for admin
                if (!empty($passwordAdmin) 
                    && strcmp($passwordAdmin, '******') != 0
                ) {
                    $this->dbContext->updatePassword("admin", $passwordAdmin, $brf);
                }
                
                //Try update password for board member
                if (!empty($passwordBoardMember) 
                    && strcmp($passwordBoardMember, '******') != 0
                ) {
                    $this->dbContext->updatePassword("board_member", $passwordBoardMember, $brf);
                }
                $this->dbContext->setUserActive("board_member", $brf, $activeBoardMember);
                
                //Try update password for brf member
                if (!empty($passwordBrfMember) 
                    && strcmp($passwordBrfMember, '******') != 0
                ) {
                    $this->dbContext->updatePassword("brf_member", $passwordBrfMember, $brf);
                }
                $this->dbContext->setUserActive("brf_member", $brf, $activeBrfMember);
                
                header("Location: /losenord");
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];}
		);
		
	}
    
    public function getLinksByLevel($level, $endpointLevel, $brf, $domain){
		$links = array();
		
		if($domain != "settings") {
			return $links;
		}
		
		if($level < UserLevels::$userLevels["admin"]){
			return $links;
		}
		
		array_push($links,
				array(
					"title" => "Lösenord & Behörighet",
					"url" => "/losenord",
					"position" => "navbar",
					"sublinks" => array(),
					"class" => " bas ",
					"sortindex" => 1
				));
		
		
		return $links;
	}
	
}

?>