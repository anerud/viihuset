<?php
require_once("interfaces/iView.php");
require_once("views/listview/ListView.php");
require_once("views/listview/TextListItem.php");
require_once("views/listview/JSDeleteListItem.php");
require_once("views/listview/LinkListItem.php");
require_once("views/listview/TitleSubtitleListItem.php");
require_once("views/moduleInfo/ModuleInfoAdminView.php");

class MessageBoardAdminView implements iView{
	
	private $brf;
	private $threads;
	private $moduleInfo;
	private $maxPage;
	private $errors;
	
	public function __construct($moduleInfo,$threads,$maxPage,$errors){
		$this->brf = $moduleInfo->brf;
		$this->threads = $threads;
		$this->moduleInfo = $moduleInfo;
		$this->maxPage = $maxPage;
		$this->errors = $errors;
	}

	public function render(){
	
				if($this->errors && sizeof($this->errors) > 0){errors($this->errors);} 
				
				$moduleView = new ModuleInfoAdminView($this->moduleInfo);
				$moduleView->render();
                
                if(count($this->threads) <= 0) {
                    return;
                }

				$headers = array("anslag/diskussioner","antal inlägg","radera");
                $headerStyles = array("text-align: left", "text-align: right", "text-align: right");
				$rows = array();
				foreach ($this->threads as $thread) {
					$values = array();
                    
					$datetimearray = explode(" ", $thread->posted);
					$date = $datetimearray[0];
                    
					array_push($values, new LinkListItem("/anslagstavla/".$thread->id ,new TitleSubtitleListItem($thread->title, "AV: ".$thread->poster." - ".$date, $headerStyles[0]),false));
                    
					array_push($values, new TextListItem("(".$thread->repliesCount.")", $headerStyles[1]));
                    
					array_push($values, new JSDeleteListItem("/anslagstavla/".$thread->id, $headerStyles[2]));
					
					
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