    <?php
    require_once("interfaces/iView.php");

    class DesignPatternView implements iView{

        private $colors;
        private $backgroundPatterns;
        private $errors;
        private $activeDesign;
        public function __construct($colors, $backgroundPatterns,$activeDesign, $errors) {
            $this->colors = $colors;
            $this->activeDesign = $activeDesign;
            $this->backgroundPatterns = $backgroundPatterns;
            $this->errors = $errors;
        }

        public function render(){
            ?>

            <link rel='stylesheet' href="/css/semantic.css" />
            <link rel='stylesheet' href="/css/dropdown.css" />
            <link rel='stylesheet' href="/css/spectrum.css" />
            <script src="/js/spectrum.js"></script>
            <script src="/js/semantic.js"></script>
            <script src="/js/dropdown.js"></script>


            <style>
                .title{
                    font-size: 20px;
                    margin-top: 20px;
                    font-weight: 600;
                }

                .subtitle{
                    font-size: 14px;
                }

                .alternatives{
                    margin-top: 10px;
                    clear: both;
                }

                .alternative{
                    height: 112px;
                    width: 112px;
                    float: left;
                    cursor: pointer;
                }

                #loader{
                    display: inline-block;
                    padding-top: 12px;
                    height: 27px;
                }
            </style>

            <div style="clear: both;" id="colors">
                <div class="title">Välj ett färgtema</div>
                <hr/>
                <div class="subtitle">
                    Färgtemat fördelas i mörka och ljusa kulörer för bl.a bakgrunder, länkar och menyer.
                </div>
                <div class="alternatives">
                    <?php
                        foreach($this->colors as $color){
                            echo "<div data-id='".$color->id."' class='alternative' style='background-image:url(".$color->thumb.")'></div>";
                        }
                    ?>
                </div>
            </div>


            <div style="clear: both; padding-top: 20px;" id="backgrounds">
                <div class="title">Välj en bakgrund</div>
                <hr/>
                <div class="subtitle">
                    Bakgrunderna har olika mönster
                </div>
                <div class="alternatives">
                    <?php
                        foreach($this->backgroundPatterns as $pattern){
                            echo "<div data-id='".$pattern->path."' class='alternative' style='background-image:url(".$pattern->thumb.")'></div>";
                        }
                    ?>
                </div>
            </div>
            <div style="clear: both; padding-top: 20px;">
                <label style=" clear: both;" for="backgroundColor">Bakgrundsfärg:</label>
                <input id="backgroundColor" type = "hidden" name="backgroundColor" value='<?php echo $this->activeDesign->backgroundColor?>'>
            </div>

            <hr>

            <div id="savecon">
                <button id="save" class="button-blue">Spara</button>
                <div id="loader" style="display: none;"></div>
            </div>
            <script>
                var newBg;
                var newColor;
                var newBgColor;
                var saving = false;
                insertLoader($("#loader"));

                $("#save").click(function(){
                    if(!saving){
                        saving = true;
                        $("#loader").show();

                        $.post("/farger",{colorId : newColor == null ? null : newColor.id, path : newBg, bgColor : newBgColor },function(){
                            $("#loader").hide();
                            saving = false;
                        });
                    }
                });

                var colors = <?php echo json_encode($this->colors); ?>;
                $("#colors .alternative").click(function(){
                    var id = $(this).data("id");
                    console.log(id);

                for(var i = 0; i < colors.length ; i++){
                        var c = colors[i];
                        if(c.id == id){
                            setColor(c);
                            newColor = c;
                            break;
                        }
                    }

                });

                $("#backgrounds .alternative").click(function(){
                    var id = $(this).data("id");
                    newBg = id;
                    $(document.body).css("background-image","url("+id+")");
                });


                function setColor(color){
                    updateColor(1,"#"+color.color1);
                    updateColor(2,"#"+color.color2);
                    updateColor(3,"#"+color.color3);
                    updateColor(4,"#"+color.color4);
                }


                function updateColor(index, color){

                    $(".textcolor"+index).each(function(){
                        var t = $(this);
                        var os = t.attr("style");
                        os = os ? os : "";
                        t.css('cssText', os + "color: "+color+" !important;");
                    });

                    $(".bgcolor"+index).each(function(){
                        var t = $(this);
                        var os = t.attr("style");
                        os = os ? os : "";

                        t.css('cssText', os + "background-color: "+color+" !important;");
                    });

                    $(".bordercolor"+index).each(function(){
                        var t = $(this);
                        var os = t.attr("style");
                        os = os ? os : "";

                        t.css('cssText', os + "border-color: "+color+" !important;");
                    });

                }

                $("#backgroundColor").spectrum({
                    color: "#"+$("#backgroundColor").val(),
                    preferredFormat: "hex",
                    showButtons:false,
                    move: function(e){
                        e = e.toHexString();
                        newBgColor = e;
                        $(document.body).css("background-color",e);
                    }
                });


            </script>





            <?php
        }

    }

    ?>
