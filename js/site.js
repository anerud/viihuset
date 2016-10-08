var monthNames = new Array();
monthNames[0] = "Januari";
monthNames[1] = "Februari";
monthNames[2] = "Mars";
monthNames[3] = "April";
monthNames[4] = "Maj";
monthNames[5] = "Juni";
monthNames[6] = "Juli";
monthNames[7] = "Augusti";
monthNames[8] = "September";
monthNames[9] = "Oktober";
monthNames[10] = "November";
monthNames[11] = "December";

Date.prototype.getWeek = function() {
	var onejan = new Date(this.getFullYear(), 0, 1);
	return Math.ceil((((this - onejan) / 86400000) + onejan.getDay() + 1) / 7);
}

Date.prototype.isSameDateAs = function(pDate) {
  return (
    this.getFullYear() === pDate.getFullYear() &&
    this.getMonth() === pDate.getMonth() &&
    this.getDate() === pDate.getDate()
  );
}

function insertLoader(con){
		var html =
		'<div class="sk-fading-circle">'+
		'<div class="sk-circle1 sk-circle"></div>'+
		'<div class="sk-circle2 sk-circle"></div>'+
		'<div class="sk-circle3 sk-circle"></div>'+
		'<div class="sk-circle4 sk-circle"></div>'+
		'<div class="sk-circle5 sk-circle"></div>'+
		'<div class="sk-circle6 sk-circle"></div>'+
		'<div class="sk-circle7 sk-circle"></div>'+
		'<div class="sk-circle8 sk-circle"></div>'+
		'<div class="sk-circle9 sk-circle"></div>'+
		'<div class="sk-circle10 sk-circle"></div>'+
		'<div class="sk-circle11 sk-circle"></div>'+
		'<div class="sk-circle12 sk-circle"></div>'+
		'</div>';

		con.html(html);

	}

$(function(){

	$(".submoduleremove").click(function(e){
		var t = $(this);
		var name = t.data("name");
		var parent = t.data("parent");

		var domParent = t.parent().parent();

		t.parent().remove();

		if(domParent.find(".submoduleremove").length == 0){
			domParent.remove();
		}

	    $.ajax({
	        url: "/submodule/"+parent+"/"+name,
	        type: 'DELETE',
	        success: function(result) {}
	    });

	});

    $("#createNewPageButton").click(function(e){
       $("#createNewPagePopupOverlay").show();
       e.preventDefault();
       return false;
    });
    $("#createNewPagePopupCancel").click(function(e){
       $("#createNewPagePopupOverlay").hide();
       e.preventDefault();
       return false;
    });


    $("#createNewPagePopupOverlay").click(function(e){
       if(e.target.id == "createNewPagePopupOverlay"){
            $(this).hide();
            e.preventDefault();
            return false;
       }
    });


    $(window).keydown(function(e){
       if(e.keyCode == 27){
         if($("#createNewPagePopupOverlay").is(":visible")){
             $("#createNewPagePopupOverlay").hide();
         }
       }
    });

    $(".columnarrows").first().addClass("first");
    $(".columnarrows").last().addClass("last");

    $(".columnup").click(function(){
        var t = $(this);
        var module = t.data("module");

         var url = "/"+module+"/moveRightColUp";
        $.post(url,{}, function(e){
        });

        var li = t.closest('.rightModule');
        var prev = $(li.prev().get(0));

        li.after(prev);

         var arrows = $(t.parent());
         var otherArrows = $(prev.find('.columnarrows'));

        if(otherArrows.hasClass("first")){
            otherArrows.removeClass("first");
            arrows.addClass("first");
        }

         if(arrows.hasClass("last")){
            arrows.removeClass("last");
            otherArrows.addClass("last");
        }
    });

    $(".columndown").click(function(){
        var t = $(this);
        var module = t.data("module");

        var url = "/"+module+"/moveRightColDown";
        $.post(url,{}, function(e){
        });


        var li = t.closest('.rightModule');
        var next = $(li.next().get(0));

        li.before(next);

         var arrows = $(t.parent());
         var otherArrows = $(next.find('.columnarrows'));

        if(otherArrows.hasClass("last")){
            otherArrows.removeClass("last");
            arrows.addClass("last");
        }

         if(arrows.hasClass("first")){
            arrows.removeClass("first");
            otherArrows.addClass("first");
        }
    });


    $(".moduleli").first().parent().addClass("first");
    $(".moduleli").last().parent().addClass("last");

    $(".moduleup").click(function(){
        var t = $(this);
        var module = t.data("module");

        var url = "/"+module+"/moveUp";
        $.post(url,{}, function(e){
        });

        var li = $(t.parent().parent().parent().get(0));
        var prev = $(li.prev().get(0));

        li.after(prev);

        if(prev.hasClass("first")){
            prev.removeClass("first");
            li.addClass("first");
        }

         if(li.hasClass("last")){
            li.removeClass("last");
            prev.addClass("last");
        }
    });

    $(".moduledown").click(function(){
        var t = $(this);
        var module = t.data("module");

        var url = "/"+module+"/moveDown";
        $.post(url,{}, function(e){
        });

         var li = $(t.parent().parent().parent().get(0));
        var next = $(li.next().get(0));
        li.before(next);

        if(next.hasClass("last")){
            next.removeClass("last");
            li.addClass("last");
        }

         if(li.hasClass("first")){
            li.removeClass("first");
            next.addClass("first");
        }
    });

	$(".modulevisiblecheckbox").change(
		function(){
		    var t = $(this);
		    var module = t.data("module");

		    var url = "/"+module+"/toggleVisibility";
		    $.post(url, {}, function(e){});
		}
	);

	$(".rightcolvisiblecheckbox").change(
		function(){
		    var url = "/rightcol/toggleVisibility";
		    $.post(url, {}, function(e){});
		}
	);

	$(".JSCheckboxListItem input:checkbox").change(function(){
		var t = $(this);
		var url = t.attr("data-url");
		if(url){
			$.post(url,{},function(){
			});
		}
	});

	$(".JSDropdownListItem select").change(function(){
		var t = $(this);
		var url = t.attr("data-url");
		var selected = t.find(":selected").val();

		if(url){
			$.post(url,{value:selected},function(){
			});
		}
	});

	$(".JSDeleteListItem a.delete").click(function(){
		var t = $(this);
		var url = t.attr("href");
		var tr = $(t.parent());
		while(!tr.is("tr")){
			tr = $(tr.parent());
		}


		$.ajax({
			url: url,
			type: 'DELETE',
			success: function(result) {
				tr.remove();
			}
		});

		t.hide();
		insertLoader(t.parent());
		return false;
	});





	$(".calendarDiv").each(function(){
		var con = $(this);
		var body = con.find(".calendarBody");
		var title = con.find(".calendarTitle");
		var next = con.find(".calendarRight");
		var prev = con.find(".calendarLeft");
		var date = new Date();
		var monthDiff = 0;
		var yearDiff = 0;
		function renderMonth(month, year){
			body.empty();

			var lastDateBefore = new Date(year,month , 0).getDate();
			var firstDay = new Date(year,month , 1);
			var lastDay = new Date(year,month + 1, 0);
			var firstDayWeekDay = (firstDay.getDay() +6) % 7;
			var lastDayWeekDay = (lastDay.getDay() +6) % 7;
			var firstWeek = firstDay.getWeek();

			if(firstDay.getDay() == 0){
				firstWeek--;
			}

			title.html(monthNames[month]+" "+year);

			var weeks = 5;
			if(firstDayWeekDay == 0 && lastDayWeekDay == 6){
				weeks = 4;
			}

			for(var i = 0;i<weeks;i++){
				var week = firstWeek+ i;
				body.append('<div class="calendarWeek calendarWeek'+week+'"><div></div></div>');
				var weekcon = body.find(".calendarWeek"+week).find("div");
				for(var j = 0;j<7;j++){
					var calendarMonth = month;
					var calendarYear = year;
					var extraClass = ""
					var content = (i*7)+j-firstDayWeekDay+1;
					if(i == 0 && j < firstDayWeekDay){
						extraClass = "oldDay";
						calendarMonth--;
						if(calendarMonth<0){
							calendarMonth =11;
							calendarYear--;
						}
						content = lastDateBefore-firstDayWeekDay+j+1;
					}else if(i == 4 && j > lastDayWeekDay){
						extraClass = "newDay";
						calendarMonth++;
						if(calendarMonth>11){
							calendarMonth =0;
							calendarYear++;
						}
						content = j-lastDayWeekDay;
					}
					weekcon.append('<div class="'+extraClass+' calendarDay calendarYear'+calendarYear+' calendarMonth'+calendarMonth+' calendarDay'+content+'"><table><td>'+content+'</td></table></div>');
				}
			}
			var s = ".calendarYear"+date.getFullYear()+".calendarMonth"+date.getMonth()+".calendarDay"+date.getDate();
			$(s).addClass("currentDay");

			if(window.brf){
				$.get("/"+window.brf+"/bookingsThisMonth?year="+year+"&month="+(month+1), function(d){
					function addDate(date, month, year, color){
						var daydiv = $(".calendarYear"+year+".calendarMonth"+month+".calendarDay"+date);
						//daydiv.append('<div class="bookingItem" style="background-color: #'+color+';"></div>');
					}

					function parseObjects(o){
						var color = o.bookingObjectColor;
						var start = new Date(Math.min(Date.parse(o.start), Date.parse(o.end)));
						var end = new Date(Math.max(Date.parse(o.start), Date.parse(o.end)));

						while(!start.isSameDateAs(end)){
							addDate(start.getDate(), start.getMonth(), start.getFullYear(), color);
							start = new Date(start.setDate(start.getDate() + 1));
						}
					}

					for(var i = 0;i<d.length;i++){
						parseObjects(d[i]);
					}

				});
			}
		}

		prev.click(function(){
			monthDiff--;
			if(monthDiff + date.getMonth() < 0){
				yearDiff--;
				monthDiff = 11 - date.getMonth();
			}

			renderBody();
		});

		next.click(function(){
			monthDiff++;
			if(monthDiff + date.getMonth() > 11){
				yearDiff++;
				monthDiff = -date.getMonth();
			}
			renderBody();
		});

		function renderBody(){
			renderMonth(date.getMonth()+monthDiff,date.getFullYear()+yearDiff);
		}
		renderBody();
	});


});
