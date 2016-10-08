<?php
require_once("interfaces/iController.php");
require_once("interfaces/AbstractModule.php");

final class BrfMembersController implements iController {
	
	private $dbContext;
	 
	public function init($router, $dbContext){
		
		$this->dbContext = $dbContext;
		
		//Admin page
		$router->map('GET', '/medlemmar',
			function() {
				//Get the logged in user
				$user = $_SESSION["user"];
				$brf = $user->brf;
                $members = $this->dbContext->getBrfMembers($brf);
				
				$view = "BrfMembersView";
				require_once("views/settings/".$view.".php");
				$viewObject = new $view($members, getErrors());
				$viewObject->render();
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];},
			null,
			true,
			"settings"
		);
        
		$router->map('POST', '/medlemmar',
			function() {
				$user = $_SESSION["user"];
				$brf = $user->brf;
				
                require_once("model/brf/Member.php");
                $member = new Member();
				$member->name = $_POST["name"];
                $member->email = $_POST["email"];
                $member->phone = $_POST["phone"];
                $member->floor = $_POST["floor"];
                $member->apartment = $_POST["apartment"];
                $member->position = $_POST["position"];
                
                $errors = array();
			
				if(!isset($_POST["name"]) || empty($_POST["name"])) {
					array_push($errors, "Du måste ange ett namn!");
				}
				
				if(!empty($errors)) {
					setErrors($errors);
                    header("Location: /medlemmar");
                    return;
				}          
                
                $this->dbContext->addBrfMember($brf, $member);      
                header("Location: /medlemmar");
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];}
		);
        
        $router->map('DELETE', '/medlemmar/[i:memberId]',
			function($memberId) {
				//Get the logged in user
				$user = $_SESSION["user"];
								
				//Collect documents for the view
				$this->dbContext->deleteBrfMember($user->brf, $memberId);
				
				header("HTTP/1.1 200 OK");
				return;				
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];},
			"",
			false
		);
        
        $router->map('POST', '/medlemmar/changeLevel/[i:memberId]',
			function($memberId) {
				//Get the logged in user
				$user = $_SESSION["user"];
								
				if(!isset($_POST["value"])){
					http_response_code(400);
					return;	
				}
				$this->dbContext->changeBrfMemberUserLevel($user->brf, $memberId, $_POST["value"]);
	
				header("HTTP/1.1 200 OK");
				return;				
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];},
			"",
			false
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
					"title" => "Inlagda medlemmar",
					"url" => "/medlemmar",
					"position" => "navbar",
					"sublinks" => array(),
					"class" => " bas ",
					"sortindex" => 2
				));
		
		
		return $links;
	}
    
}

?>