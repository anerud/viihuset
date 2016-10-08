<?php
require_once("interfaces/iView.php");
require_once("views/moduleInfo/ModuleInfoAdminView.php");
require_once("views/listview/LinkListItem.php");
require_once("views/listview/JSDeleteListItem.php");
require_once("views/listview/JSCheckboxListItem.php");

class NewsNewsView implements iView{
	
	private $errors;
    private $news;
    
	public function __construct($news, $errors){
        $this->news = $news; 
		$this->errors = $errors;
	}

	public function render(){
	
		if($this->errors && sizeof($this->errors) > 0){
            errors($this->errors);
        }
        
        $newsTitle = $this->news == null ? "" : $this->news->title;
        $newsText = $this->news == null ? "" : $this->news->text;
        
        echo "<h2>".$newsTitle."</h2>";
        echo "<hr>";
        echo "<p>".$newsText."</p>";
        
	}
	
}

?>