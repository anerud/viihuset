<?php
require_once("interfaces/iView.php");
require_once("views/listview/TextSubtextListItem.php");
require_once("views/listview/ListView.php");

class BoardChatReplyView implements iView{

	private $errors;
	private $brf;
	private $thread;
	private $moduleInfo;
	private $maxPage;

	public function __construct($moduleInfo,$thread,$maxPage,$errors){
		$this->brf = $moduleInfo->brf;
		$this->moduleInfo = $moduleInfo;
		$this->thread = $thread;
		$this->maxPage = $maxPage;
		$this->errors = $errors;
	}

	public function render(){

	?>

		<div class='content'>
			<div class='main'>
                <?php
				if($this->errors && sizeof($this->errors) > 0){
                    errors($this->errors);
                }

                $datetimearray = explode(" ", $this->thread->posted);
                $date = $datetimearray[0];
                ?>

				<h2><?php echo $this->thread->title;?></h2>
                <hr>
                <div class = "titleSubtitleListItem">
				<span><?php echo $this->thread->message?></span>
                <br>
                <span class="subtitle textcolorGray">AV: <?php echo $this->thread->poster." - ".$date?></span>
                </div>

                <hr>

                <?php

                if($this->thread->replies != null && count($this->thread->replies) > 0) {

                    $headers = array();
                    $headerStyles = array("text-align: left");
                    $rows = array();
                    foreach ($this->thread->replies as $reply) {
                        $values = array();

                        $datetimearray = explode(" ", $reply->posted);
                        $date = $datetimearray[0];

                        array_push($values, new TextSubtextListItem($reply->message,"<span class='textBold'>AV: ".$reply->poster."</span> - ".$date, $headerStyles[0]));

                        $row = array( "values" => $values );
                        array_push($rows,$row);
                    }


                    $listView = new ListView($headers, $rows, $headerStyles);
                    $listView->render();


                    $view = "PaginationView";
                    require_once("views/".$view.".php");
                    $viewObject = new $view($this->maxPage);
                    $viewObject->render();

                    echo '<hr>';

                }

                ?>

                <style>
                    .replyTable{
                        width: 100%;
                    }

                    .replyTable td {
                        margin-left: 10px;
                    }
                </style>

				<div class='con' id='login'>
                    <form action='/<?php echo $this->brf;?>/styrelsechat/<?php echo $this->thread->id?>' method='post' class='form' autocomplete='off'>
                     <div class="form-section">

                         <div class="form-input-large">
                            <h5 class='form-description'>Avsändare</h5>
                            <input type='text' name='poster' value='' placeholder='Ditt namn' tabindex='1' class="form-input-basic"/>
				        </div>
                         <div class="form-input-large">
                            <h5 class='form-description'>E-postadress</h5>
                            <input type='text' name='email' value='' placeholder='Din e-postadress' tabindex='2' class="form-input-basic"/>
                        </div>
                         <div class="form-input-whole">
                            <h5 class='form-description'>Meddelande</h5>
                            <textarea type='text' name='message' value='' placeholder='Meddelande' tabindex='3' class="form-input-area"></textarea>
                        </div>
                        <input class="button-blue" type='submit' name='submit' value='Skicka' tabindex='5' /></td>
                    </div>
                    </form>
				</div>
			</div>
		</div>

		<?php
	}

}

?>
