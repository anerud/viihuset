<?php
require_once("interfaces/iView.php");
require_once("views/moduleInfo/ModuleInfoView.php");
require_once("views/listview/ListView.php");
require_once("views/listview/LinkListItem.php");
require_once("views/listview/TextListItem.php");

class NewsView implements iView{
	
	private $errors;
    private $news;
    private $maxPage;
	private $moduleInfo;
	
	public function __construct($moduleInfo, $news, $maxPage, $errors){
		$this->moduleInfo = $moduleInfo;
        $this->news = $news;
        $this->maxPage = $maxPage;               
		$this->errors = $errors;
	}

	public function render(){
	
		$moduleView = new ModuleInfoView($this->moduleInfo);
		$moduleView->render();
        $brf = $this->moduleInfo->brf;
        
        if($this->errors && sizeof($this->errors) > 0){errors($this->errors);
            return;
        }
        
        if(count($this->news) <= 0) {
            return;
        }
		
        echo "<div>";
        $headers = array("Senaste nyheterna", "datum");
        $headerStyles = array("text-align: left", "text-align: right");
        $rows = array();
        foreach ($this->news as $news) {
            $values = array();
            
            $datetimearray = explode(" ", $news->posted);
            $date = $datetimearray[0];
            
            array_push($values, new LinkListItem("/".$brf."/nyheter/".$news->id ,new TextListItem($news->title, $headerStyles[0]), false));
            
            array_push($values, new TextListItem($date, $headerStyles[1]));
            
            $row = array( "values" => $values );
            array_push($rows,$row);
        }
        
        
        $listView = new ListView($headers, $rows, $headerStyles);
        $listView->render();
        
        
        $view = "PaginationView";
        require_once("views/".$view.".php");
        $viewObject = new $view($this->maxPage);
        $viewObject->render();
        
        echo "</div>";
	}
	
}

?>