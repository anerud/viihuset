<?php
require_once("views/moduleInfo/ModuleInfoView.php");
require_once("interfaces/iView.php");
require_once("views/listview/ListView.php");
require_once("views/listview/TextListItem.php");
require_once("views/listview/JSDeleteListItem.php");
require_once("views/listview/LinkListItem.php");
require_once("views/listview/TitleSubtitleListItem.php");

class MessageBoardView implements iView{

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
				(new ModuleInfoView($this->moduleInfo))->render();
				?>
			</div>
			<div class='main'>
				<?php if($this->errors && sizeof($this->errors) > 0){errors($this->errors);}

                if(count($this->threads) > 0) {

                    echo "<div id='messageBoardThreadListView'>";
                    $headers = array("anslag/diskussioner","antal inlägg");
                    $headerStyles = array("text-align: left", "text-align: right", "text-align: right");
                    $rows = array();
                    foreach ($this->threads as $thread) {
                        $values = array();

                        $datetimearray = explode(" ", $thread->posted);
                        $date = $datetimearray[0];

                        array_push(
                            $values,
                            new LinkListItem(
                                "/".$this->brf."/anslagstavla/".$thread->id,
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

				<button id="createNewMessageBoardThread" class="button-blue" href="#">Gör ett nytt inlägg</button>

                <script>
                    $("#createNewMessageBoardThread")
                        .click(
                            function(){
                                $("#messageBoardThreadListView").hide();
                                $("#createNewMessageBoardThread").hide();
                                $("#createNewMessageBoardThreadForm").show();
                                $(document).scrollTop(0);
                            }
                        );
                </script>

				<form id="createNewMessageBoardThreadForm" action='<?php echo "/".$this->brf;?>/anslagstavla' method='post' style="display:none">

                    <style>
                        .replyTable{
                            width: 100%;
                        }

                        .replyTable td {
                            margin-left: 10px;
                        }
                    </style>

                    <table class="replyTable textSize13px textcolorGray textUppercase textBold">

                        <tr>
                        <td colspan="2">Ämne/Ärende</td>
                        </tr>

                        <tr>
                        <td colspan="2"><input type='text' name='title' value='' placeholder='Titel' tabindex='1' class="form-input-basic"/></td>
                        </tr>

                        <tr>
                        <td>Avsändare</td>
                        <td>E-postadress</td>
                        </tr>

                        <tr>
                        <td><input type='text' name='poster' value='' placeholder='Ditt namn' tabindex='2' class="form-input-basic"/></td>
                        <td><input type='text' name='email' value='' placeholder='Din e-postadress' tabindex='3' class="form-input-basic"/></td>
                        </tr>

                        <tr>
                        <td colspan="2">Meddelande</td>
                        </tr>

                        <tr>
                        <td colspan="2"><textarea name='message' value='' placeholder='Meddelande' tabindex='4' class="form-input-basic"></textarea></td>
                        </tr>

                        <tr>
                        <td colspan="2"><input type='submit' class="button-blue" name='submit' value='Skicka' tabindex='5' /></td>
                        </tr>

                    </table>

				</form>

			</div>
		</div>

		<?php
	}

}

?>
