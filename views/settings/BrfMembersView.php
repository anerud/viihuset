<?php
require_once("interfaces/iView.php");

class BrfMembersView implements iView{

	private $errors;
	private $members;

	public function __construct($members, $errors){
		$this->members = $members;
		$this->errors = $errors;
	}

	public function render(){

		?>
        <style>

            h3{
                font-size: 16px;
            }

            input[type='text']{
                background-color: #fAfAfA;
                border: 1px solid #eaeaea;
                padding: 0;
            }

            .memberbox{
                width: 100%;
                background-color: #fAfAfA;
                border: 1px solid #eaeaea;
				border-radius: 4px;
                margin-bottom: 10px;
                padding: 10px;
            }

            .memberbox > .info1{
                width: 300px;
                display:inline-block;
            }

            .memberbox > .info2{
                margin-top: 5px;
                width: calc(100% - 30px);
                display:inline-block;
            }

            .memberbox > .info3{
                margin-top: 5px;
                width: 180px;
                display:inline-block;
            }

            .memberbox > .info4{
                width: 120px;
                display:inline-block;
            }

            .memberbox > .info5{
                width: 180px;
                display:inline-block;
            }

             .memberbox .remove{
                color: red;
                width: 20px;
                display:inline-block;
                font-size: 14px;
                text-align: right;
                cursor: pointer;
            }

            .memberbox .title{
                font-weight: bold;
                font-size: 14px;
            }

             .memberbox .data{
                font-weight: 400;
                font-size: 14px;
                margin-left: 5px;
            }

        </style>

        <script>
            $(function(){
                $(".userleverpicker").change(function(){
                    var t = $(this);
                    var box = t.parent();
                    var id = box.data("id");
                    var newcon = t.val();

                    $.post("/medlemmar/changeLevel/"+id,{value : newcon },function(){

                    });
                    box.appendTo("."+newcon);

                });


                $(".memberbox .remove").click(function(){
                    var t = $(this);
                    var box = t.parent();
                    var id = box.data("id");


                    $.ajax({
                        url: "/medlemmar/"+id,
                        type: 'DELETE',
                        success: function(result) {
                            box.remove();
                        }
                    });


                    insertLoader(t);
                });
            });
        </script>

        <h2>Lägg in ny medlem</h2>

        <hr>

        <form method='post' class='form' autocomplete='off'>
            <div style="width: calc(50% - 20px); display:inline-block;" >
                <h3>För & Efternamn</h3>
                <input type='text' class="form-input-basic" name='name'/>
            </div>
            <div style="width: 50%; margin-left: 10px; display:inline-block;" >
                <h3>Telefonnummer</h3>
                <input type='text' class="form-input-basic" name='phone'/>
            </div>
            <div style="width: calc(50% - 30px); display:inline-block; margin-top: 20px;" >
                <h3>E-postadress</h3>
                <input type='text' class="form-input-basic" name='email'/>
            </div>
            <div style="width: 25%; margin-left: 10px;  display:inline-block;" >
                <h3>Våningsplan</h3>
                <input type='text' class="form-input-basic" name='floor'/>
            </div>
            <div style="width: 25%; margin-left: 10px;  display:inline-block;" >
                <h3>Lägenhet</h3>
                <input type='text' class="form-input-basic" name='apartment'/>
            </div>
            <div style="margin-top: 20px;" >
            <h3>Position i BRF</h3>
                <select class="form-input-basic-select" name="position">
                    <?php
                    foreach(UserLevels::$userLevels as $key => $val){
                        if($val >= UserLevels::$userLevels["brf_member"] && $val <= UserLevels::$userLevels["board_member"]) {
                            echo '<option value="'.$key.'" '.($key == "brf_member" ? 'selected' : '').'>'.UserLevels::$namesToDescription[$key].'</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            <br>
            <input class="button-blue" type='submit' value='LÄGG TIIL SOM NY MEDLEM'/>
			<hr/>
		</form>

        <h3>Styrelsemedlemmar</h3>
        <div class="board_member">
        <?php
        foreach ($this->members as $member) {
            if($member->position == "board_member") {
                ?>
                <div class="memberbox textcolorGray" data-id="<?php echo $member->id; ?>">
                    <div class="info1"><span class="title">Namn:</span><span class="data"><?php echo $member->name; ?></span></div>
                     <select class="userleverpicker form-input-basic-select" name="board_member_position">
                    <?php
                        foreach(UserLevels::$userLevels as $key => $val){
                            if($val >= UserLevels::$userLevels["brf_member"] && $val <= UserLevels::$userLevels["board_member"]) {
                                echo '<option value="'.$key.'" '.($key == $member->position ? 'selected' : '').'>'.UserLevels::$namesToDescription[$key].'</option>';
                            }
                        }
                    ?>
                    </select>
                    <div class="info2"><span class="title">E-post:</span><span class="data"><?php echo $member->email; ?></span></div>
                    <div class="remove">
						<img src="/gfx/delete_icon.png" alt="x" height="13px" width="13px">
					</div>
                    <div class="info3"><span class="title">Telefon:</span><span class="data"><?php echo $member->phone; ?></span></div>
                    <div class="info4"><span class="title">Våningsplan:</span><span class="data"><?php echo $member->floor; ?></span></div>
                    <div class="info5"><span class="title">Lägenhetsnummer:</span><span class="data"><?php echo $member->apartment; ?></span></div>
                </div>
                <?php
            }
        }
        ?>
        </div>

        <hr>

        <h3>Föreningsmedlemmar</h3>
        <div class="brf_member">
        <?php
        foreach ($this->members as $member) {
            if($member->position == "brf_member") {
                ?>
                <div class="memberbox textcolorGray" data-id="<?php echo $member->id; ?>">
                    <div class="info1"><span class="title">Namn:</span><span class="data"><?php echo $member->name; ?></span></div>
                    <select class="userleverpicker form-input-basic-select" name="brf_member_position">
                    <?php
                        foreach(UserLevels::$userLevels as $key => $val){
                            if($val >= UserLevels::$userLevels["brf_member"] && $val <= UserLevels::$userLevels["board_member"]) {
                                echo '<option value="'.$key.'" '.($key == $member->position ? 'selected' : '').'>'.UserLevels::$namesToDescription[$key].'</option>';
                            }
                        }
                    ?>
                    </select>
                    <div class="info2"><span class="title">E-post:</span><span class="data"><?php echo $member->email; ?></span></div>
                    <div class="remove">
						<img src="/gfx/delete_icon.png" alt="x" height="13px" width="13px">
					</div>
                    <div class="info3"><span class="title">Telefon:</span><span class="data"><?php echo $member->phone; ?></span></div>
                    <div class="info4"><span class="title">Våningsplan:</span><span class="data"><?php echo $member->floor; ?></span></div>
                    <div class="info5"><span class="title">Lägenhetsnummer:</span><span class="data"><?php echo $member->apartment; ?></span></div>
                </div>
                <?php
            }
        }
        ?>
        </div>

		<?php
	}

}

?>
