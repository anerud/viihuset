<?php
require_once("interfaces/iView.php");
require_once("views/moduleInfo/ModuleInfoAdminView.php");
require_once("views/listview/LinkListItem.php");
require_once("views/listview/JSDeleteListItem.php");
require_once("views/listview/JSCheckboxListItem.php");
require_once("views/listview/TitleSubtitleListItem.php");
require_once("views/listview/ListView.php");

class NewsAdminView implements iView{
	
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
	
        $moduleView = new ModuleInfoAdminView($this->moduleInfo);
        $moduleView->render();
	
		if($this->errors && sizeof($this->errors) > 0){
            errors($this->errors);
        }
        
        echo '<a class="button-blue" href="/nyheter/0">Skapa ny nyhet</a>';
        
        if(count($this->news) <= 0) {
            return;
        }
        
        echo "<hr>";
		
        $headers = array("visa","titel","radera");
        $headerStyles = array("text-align: left", "text-align: left", "text-align: right");
        $rows = array();
        foreach ($this->news as $news) {
            $values = array();
            $datetimearray = explode(" ", $news->posted);
            $date = $datetimearray[0];
            array_push($values, new JSCheckboxListItem("/nyheter/toggleVisibility/".$news->id, $news->visible, $headerStyles[0]));
            array_push($values, new LinkListItem("/nyheter/".$news->id , new TitleSubtitleListItem($news->title, $date, $headerStyles[1]), false));
            array_push($values, new JSDeleteListItem("/nyheter/".$news->id, $headerStyles[2]));
            
            $row = array( "values" => $values );
            array_push($rows,$row);
        }
        
        
        $listView = new ListView($headers, $rows, $headerStyles);
        $listView->render();
        
        
        $view = "PaginationView";
        require_once("views/".$view.".php");
        $viewObject = new $view($this->maxPage);
        $viewObject->render();
	}
	
}

?>