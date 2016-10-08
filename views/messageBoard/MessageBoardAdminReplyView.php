<?php
require_once("interfaces/iView.php");
require_once("views/listview/ListView.php");
require_once("views/listview/TextListItem.php");
require_once("views/listview/JSDeleteListItem.php");
require_once("views/listview/LinkListItem.php");
require_once("views/listview/TitleSubtitleListItem.php");
require_once("views/moduleInfo/ModuleInfoAdminView.php");

class MessageBoardAdminReplyView implements iView{
	
	private $brf;
	private $thread;
	private $moduleInfo;
	private $maxPage;
	private $errors;
	
	public function __construct($moduleInfo,$thread,$maxPage,$errors){
		$this->brf = $moduleInfo->brf;
		$this->thread = $thread;
		$this->moduleInfo = $moduleInfo;
		$this->maxPage = $maxPage;
		$this->errors = $errors;
	}

	public function render(){
	
				if($this->errors && sizeof($this->errors) > 0){errors($this->errors);} 
				
				echo "<h2>".$this->thread->title."</h2>";
				$headers = array();
				$rows = array();
				
				//Render the thread message
				$values = array();
				$datetimearray = explode(" ", $this->thread->posted);
				$date = $datetimearray[0];
				array_push($values, new TitleSubtitleListItem($this->thread->message,"SKAPAD AV: ".$this->thread->poster." - ".$date, null));	
				$row = array( "values" => $values );
				array_push($rows,$row);
				
				$listView = new ListView($headers, $rows, null);
				$listView->render();
				
				echo "<br></br>";
				
				$headers = array("svar","radera");
                $headerStyles = array("text-align: left", "text-align: right");
				$rows = array();
				foreach ($this->thread->replies as $reply) {
					$values = array();
					
					$datetimearray = explode(" ", $reply->posted);
					$date = $datetimearray[0];
					
					array_push($values, new TitleSubtitleListItem($reply->message,"SVAR AV: ".$reply->poster." - ".$date, $headerStyles[0]));
					array_push($values, new JSDeleteListItem("/anslagstavla/".$this->thread->id."/".$reply->id, $headerStyles[1]));
					
					
					$row = array( "values" => $values );
					array_push($rows,$row);
				}
				
				
				$listView = new ListView($headers, $rows, $headerStyles);
				$listView->render();
				
				
				$view = "PaginationView";
				require_once("views/".$view.".php");
				$viewObject = new $view($this->maxPage);
				$viewObject->render();
				?>
		
		<?php
	}
	
}

?>