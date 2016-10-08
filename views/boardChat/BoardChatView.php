<?php
require_once("interfaces/iView.php");
require_once("views/listview/LinkListItem.php");
require_once("views/listview/TitleSubtitleListItem.php");
require_once("views/listview/TextListItem.php");
require_once("views/listview/ListView.php");

class BoardChatView implements iView{

	private $errors;
	private $brf;
	private $threads;
	private $moduleInfo;
	private $maxPage;

	public function __construct($moduleInfo,$threads,$maxPage,$errors){
		$this->brf = $moduleInfo->brf;
		$this->moduleInfo = $moduleInfo;
		$this->threads = $threads;
		$this->maxPage = $maxPage;
		$this->errors = $errors;
	}

	public function render(){

	?>

		<div class='content'>
			<div class='push'>
				<?php
				require_once("views/moduleInfo/ModuleInfoView.php");
				(new ModuleInfoView($this->moduleInfo))->render();
				?>
			</div>
			<div class='main'>
				<?php
                    if($this->errors && sizeof($this->errors) > 0){
                        errors($this->errors);
                    }

                    if(count($this->threads) > 0) {
                        echo "<div id='boardChatThreadListView'>";
                        $headers = array("Ärenden","antal inlägg");
                        $headerStyles = array("text-align: left", "text-align: right", "text-align: right");
                        $rows = array();
                        foreach ($this->threads as $thread) {
                            $values = array();

                            $datetimearray = explode(" ", $thread->posted);
                            $date = $datetimearray[0];

                            array_push(
                                $values,
                                new LinkListItem(
                                    "/".$this->brf."/styrelsechat/".$thread->id,
                                    new TitleSubtitleListItem(
                                        $thread->title,
                                        "<span class='textBold'>AV: ".$thread->poster."</span> - ".$date, $headerStyles[0]
                                    ),
                                    false
                                )
                            );

                            array_push($values, new TextListItem("(".$thread->repliesCount.")", $headerStyles[1]));

                            $row = array( "values" => $values );
                            array_push($rows,$row);
                        }


                        $listView = new ListView($headers, $rows, $headerStyles);
                        $listView->render();


                        $view = "PaginationView";
                        require_once("views/".$view.".php");
                        $viewObject = new $view($this->maxPage);
                        $viewObject->render();

                        echo "<hr>";
                        echo "</div>";
                    }
				?>

				<a id="createNewBoardChatThred" class="button-blue" href="#">Gör ett nytt inlägg</a>
                <script>
                    $("#createNewBoardChatThred")
                        .click(
                            function(){
                                $("#boardChatThreadListView").hide();
                                $("#createNewBoardChatThred").hide();
                                $("#createNewBoardChatThredForm").show();
                                $(document).scrollTop(0);
                            }
                        );
                </script>

                <style>
                    .replyTable{
                        width: 100%;
                    }

                    .replyTable td {
                        margin-left: 10px;
                    }
                </style>

				<form id="createNewBoardChatThredForm" action='<?php echo "/".$this->brf;?>/styrelsechat' method='post' style="display:none">
                    <div class="form-section">
                         <div class="form-input-whole">
                            <h5 class='form-description'>Ämne/Ärende</h5>
                            <input type='text' name='title' value='' placeholder='Titel' tabindex='1' class="form-input-basic"/>
				        </div>
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

		<?php
	}

}

?>
