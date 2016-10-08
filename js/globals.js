$(function() {

	var xhr   = null,
		$key  = null,
		$type = null,
		$file = 'fetch';
		
	// Backwards compatibility for placeholder
	$(':input[placeholder]').simplePlaceholder();
	// Shadowbox
	$inner = $('#sb-title');
	Shadowbox.init({
		continuous: true,
		displayCounter: false,
		onOpen: function(cur) {
			$('#sb-title').hide();
		},
		onChange: function(cur) {
			$('#sb-title').hide();
		},
		onFinish: function(cur) {
			if(cur.title.length > 0)
				$('#sb-title').fadeIn(200);
		}
	});
	
	// Prevent default for hash (#) links
	$('a[href=#]').click(function(e) { e.preventDefault(); });
	
	$.fn.scrollT = function(offset, time, callback) {
		var complete = false;
		$('html,body').animate({ scrollTop: offset }, time,
		function() {
			if(!complete){ complete = true;
				if(typeof callback == 'function')
	                callback();
            }
		});
	}
	
	$('.con .results').on('click', 'a', function(e) {
		var $this = $(this),
			$res  = $this.parent().parent();
			$href = $this.attr('href').match(/[^\/]+$/g);
		
		if($href) {
			$('input[name=search]').val($this.text())
			.next().val($href[0]);
			$res.hide();
		}
		e.preventDefault();
	});
	
	$('.table').on('click', '.modify a', function(e) {
		var $this = $(this),
			$tbl  = $this.parents('.table'),
			$sure = $tbl.find('.sure'),
			$dex  = $this.index();
			
		$sure.eq($dex).slideDown(200);
		e.preventDefault();
	});
	
	$('.table').on('click', 'a[href=#close]', function(e) {
		var $this = $(this);
		
		$this.parent().slideUp(200);
		e.preventDefault();
	});
	
	$('.table').on('click', 'a[href=#change]', function(e) {
		var $this = $(this),
			$par  = $this.parent(),
			$id   = $par.siblings('.thb').text().substr(1);
			
		if($par.siblings('.name').find('a').size() == 1) {		
			window.location = $('.nav .a').attr('href').replace(/\/$/g, '') + '/' + $id;
		} else $par.html("Du kan inte v\u00e4xla till en den h\u00e4r anv\u00e4ndaren!");
		e.preventDefault();
	});
	
	$('.table').on('click', 'a[href=#remove]', function(e) {
		var $this = $(this),
			$tbl  = $this.parents('.table'),
			$id   = $tbl.find('.id').text().substr(1);
		
		$file = 'modify';
			
		$this.backend($id, 'remove',
		function($this, response) {
			if(response.length)
				$this.parent().html(response);
			else
				$tbl.slideUp(400, function() { $(this).remove(); });
		});
		e.preventDefault();
	});
	
	$('.teammates a').bind('click', function(e) {
		var $this = $(this),
			$href = $this.attr('href'),
			$div  = $($href),
			$vis  = $('.team .text:visible');
		
		$('.teammates a').text('L\u00e4s mer');
		$vis.get(0) !== $div.get(0) && $this.text('D\u00f6lj');
		
		if($vis.length) {
			$vis.slideUp(400, function() {
				if($vis.get(0) !== $div.get(0))
					$div.slideDown(400);
			});
		} else $div.slideDown(400);
		
		e.preventDefault();
	});
	
	$('.list a[href=#]').bind('click', function(e) {
		var $this = $(this),
			$li   = $this.parents('li'),
			$dex  = $li.index(),
			$div  = $this.hasClass('board') ? $('#messages .message') : $('.con .info');
			
		$div = $div.size() > 0 ? $div : $('.con h2');
		$.fn.scrollT($div.eq($dex).offset().top, 600);		
		e.preventDefault();
	});
	
	$('.list a[href=#toggle]').bind('click', function(e) {
		var $this = $(this).parent(),
			$sib  = $this.siblings('p');
		
		$this.parent().toggleClass('down');
		$sib.slideToggle(200);
		e.preventDefault();
	});
	
	$('h2 span, h3 span').bind('click', function() {
		var $div  = $('.con .info');
			
		$div = $div.size() > 0 ? $div : $('.con h2');
		$.fn.scrollT($div.eq(0).offset().top, 600);
	});
	
	$('a[href=#login], a[href=#password]').bind('click', function(e) {
		var $this = $(this),
			$href = $this.attr('href'),
			$par  = $this.parents('.con');
			
		$par.fadeOut(400, function() {
			$($href).fadeIn(400);
		});
		e.preventDefault();
	});
	
	// Remove post
	$(document).on('click', '.remove:not(.admin, .person)', function(e) {
		var $this = $(this),
			$par  = $this.parent(),
			$size = $('.i-wrap').size() - 1,
			$add  = $('.addnew'),
			$fade = $size === 0 || $size === $par.index();
		
		$fade && $add.fadeTo(200, 0);
		$par.slideUp(800, function() {
			$par.remove();
			if($('.i-wrap').size() == 0)
				$('.addnew').trigger('click');
			else $fade && $add.fadeTo(200, 1);
		});
		e.preventDefault();
	});
	
	// Add new post
	$('.addnew').not('.admin, .blue, .person, .reg, .pub').bind('click', function(e) {
		var $this = $(this),
			$wrap = $('.i-wrap'),
			$size = $wrap.size(),
			$last = $wrap.eq($size - 1).attr('data-rel');
			
		$this.addClass('load')
		.fadeTo(0, 0);
		
		$this.backend( {
			cnt: $size,
			last: $last,
			dep: $this.attr('data-rel')
		}, 'addnew', function($this, response) {
			setTimeout(function() {
				$this.removeClass('load')
				.fadeTo(400, 1);
			}, 600);
			
			$(response).hide()
			.appendTo($this.parents('.con').find('form'))
			.slideDown(600);
		});
	});
	
	$('.addnew.reg').bind('click', function(e) {
		var $this = $(this),
			$prev = $this.prev(),
			data = {
				email: $prev.val()
			},
			board = $prev.attr('name') == 'board-email',
			member = $prev.attr('name') == 'member-email';
		
		if(board) data.type = 'board';
		
		$this.addClass('load')
		.fadeTo(0, 0);
		
		$file = 'board';
		$this.backend(data, 'reg', function($this, response) {
			setTimeout(function() {
				$this.removeClass('load')
				.fadeTo(400, 1);
			}, 600);
			
			if(response == 'success') {
				$this.prev().attr('style', '').val('');
				setTimeout(function() {
									console.log('yo');
					$html = $("<div class='notice success radius'></div>").html('Din e-postadress \u00e4r registrerad!');
					$this.after($html);
					
					$html.delay(3000).fadeTo(400, 0, function() {
						$(this).slideUp(function() { $(this).remove(); });
					});
				}, 600);
			}
			else if(response == 'error') $this.prev().css('border-color', '#982b2b');
			
			if(board || member) {
				$this.siblings('.mess').append($("<span class='e-list'>"+data.email+" <img src='"+ c_http + "/gfx/e-remove.png' alt='x' /></span>"));
			}
		});
	});
	
	$('.mess').on('click', '.e-list img', function(e) {
		var $this = $(this).parent(),
			$type = $this.parent().data('type');
		
		$file = 'board';
		$this.backend({
			email: $.trim($this.text()),
			type: $type
		}, 'e-remove', function($this, response) {
			$this.fadeOut(function() { $this.remove(); });
		});
	});
	
	$('.submit.pub, .submit.mail').bind('click', function(e) {
		var $this = $(this),
			$form = $this.closest('form'),
			$title = $('input[name=title]'),
			$mess = $('textarea[name=message]');
		
		$this.addClass('load')
		.fadeTo(0, 0);
		
		$file = 'board';
		$this.backend({
			form: $form.serialize()
		}, 'post', function($this, response) {
			setTimeout(function() {
				$this.removeClass('load')
				.fadeTo(400, 1);
			}, 600);
			
			if(response == 'title')
				$title.css('border-color', '#982b2b');
			else if(response == 'message') {
				$mess.css('border-color', '#982b2b');
				$title.css('border-color', '');
			}
			else if(response != 'error') {
				var msg = /^sent!/.test(response) ? 'E-postmeddelandet har nu skickats!' : 'Meddelandet har publiserats!';
				response = response.replace(/^sent!/, '');
				setTimeout(function() {
					$html = $("<div class='notice success radius'></div>").html(msg);
					$this.after($html);
					
					$html.delay(3000).fadeTo(400, 0, function() {
						$(this).slideUp(function() { $(this).remove(); });
					});
				}, 600);
				
				$title.add($mess).attr('style', '').val('');
				$form.find('input[type=checkbox]').prop('checked', false)
				.end().find('input[type=checkbox]:eq(0)').prop('checked', true);
				$('#messages').prepend(response);
			}
		});
	});
	
	$(document).on('click', '.remove.message', function(e) {
		var $this = $(this);
		
		$file = 'board';
		$this.backend({
			post: $this.parent().data('id')
		}, 'remove', function($this, response) {
		});
	});
	// Remove person
	$(document).on('click', '.remove.person', function(e) {
		var $this = $(this),
			$prev = $this.prev(),
			$size = $('.people').size() - 1,
			$add  = $('.addnew'),
			$fade = $size === 0;
			
		$this.parent()
		.fadeTo(400, 0, function() {
			$fade && $add.fadeTo(200, 0);
			$(this).slideUp(400, function() {
				if($size === 0)
					$('.addnew').trigger('click');
				
				$fade && $add.delay(400).fadeTo(200, 1);
				$(this).remove();
			});
		});
		e.preventDefault();
	});
	
	// Add new post
	$('.addnew.person').bind('click', function(e) {
		var $this = $(this),
			$prev = $this.prev(),
			$ppl  = $('.people'),
			$size = $('.people').size();
			
		$this.addClass('load');
		
		$this.backend( {
			last: $ppl.last().attr('data-rel')
		}, 'person', function($this, response) {
			setTimeout(function() {
				$this.removeClass('load');
			}, 600);
			
			$(response).css({
				display: 'none',
				opacity: 0
			})
			.insertBefore($this)
			.slideDown(400, function() { $(this).fadeTo(400, 1); });
		});
	});
	
	// Remove post
	$(document).on('click', '.remove.admin', function(e) {
		var $this = $(this),
			$par  = $this.parent(),
			$size = $par.parent().find('.i-wrap').size(),
			$add  = $par.siblings('.add.admin'),
			$fade = $size === 0 || $size == $par.index();
			
		$fade && $add.fadeTo(200, 0);
		$par.slideUp(800, function() {
			var $pp = $par.parent();
			$par.remove();
			if($pp.find('.i-wrap').size() == 0)
				$pp.find('.addnew.admin').trigger('click', 600);
			else $fade && $add.fadeTo(400, 1);
		});
		e.preventDefault();
	});
	
	// Add new post
	$('.addnew.admin').bind('click', function(e, delay) {
		var $this = $(this),
			$wrap = $this.parent().parent().find('.i-wrap'),
			$size = $wrap.size(),
			$last = $wrap.eq($size - 1).attr('data-rel'),
			delay = delay || 0;
			
		$this.addClass('load');
		
		$this.backend( {
			cnt: $size,
			last: $last,
			dep: $this.attr('data-rel')
		}, 'addnew-admin', function($this, response) {
			setTimeout(function() {
				$this.removeClass('load');
			}, 600);
			
			$('.add.admin').delay(delay).fadeTo(400, 1);
			$(response).hide()
			.insertBefore($this.parent())
			.slideDown(600);
		});
	});
	
	// Add info item
	$('.addnew.blue').bind('click', function(e) {
		var $this = $(this),
			$prev = $this.prev(),
			$name = $prev.attr('name'),
			$block = false;
			
		switch($name) {
			case 'additional' :
				$sel = $this.parent().find('p');
				$no = $sel.length ? $sel.last().find('input').attr('name').split('_')[1] : -1;
			break;
			case 'common' :
			case 'repair' :
			case 'fees' :
				if($name == 'common' || $name == 'fees') {
					$sel = $this.parent().find('ul:last li:last-child');
					$no  = $sel.find('label').attr('for').substr(1);
				}
				if($name == 'repair') {
					$sel = $this.prevAll('p:eq(1)');
					$no  = $sel.find('input').attr('name').split('_')[1];
				}
			break;
		}
				
		var $obj = {
			val: $prev.val(),
			number : $no
		}
		
		if($prev.val().length == 0) $block = true;
			
		if($block) return;
		$this.addClass('load');
		
		$this.backend( $obj, $name, function($this, response) {
			setTimeout(function() {
				$this.removeClass('load');
			}, 600);
			
			switch($name) {
				case 'additional' :
					$(response).insertBefore($prev);
					$prev.val('');
				break;
				case 'common' :
				case 'repair' :
				case 'fees' :
					$(response).insertAfter($sel);
					$prev.val('');
				break;
			}
		});
	});
	
	var $success = $('.success').size() == 1,
		$admin   = $('.wrap.admin').size() == 1;
	$keyup = false;
	$(document).on('keyup', 'input, textarea', function() {
		if(!$keyup)
			$keyup = true;
	});
	
	if(!$success) $('.navigate').hide();
	
	$(window).bind('beforeunload', function(e) {
		if(!$success && $admin && $keyup)
			return "\u00c4r du s\u00e4ker p\u00e5 att du vill l\u00e4mna sidan? Eventuella \u00e4ndringar kommer inte att bli sparade.";
	});
	
	$('.page-prev, .page-next').bind('click', function(e) {
		var $this = $(this),
			$link = $('.nav.admin .a'),
			$dex  = $link.parent().index();
		
		$dir = $this.hasClass('page-prev') ? 'prev' : 'next';
		$dex = $dir == 'prev' ? $dex - 1 : $dex + 1;
		
		//!$success || 
		if(($dex < 0 && $dir == 'prev') || ($dex > $('.nav.admin li').size() - 1 && $dir == 'next') ) return;
		var $href = $('.nav.admin li').eq($dex).find('a').attr('href');
		
		window.location.href = $href;
		e.preventDefault();
	});
	
	$('.submit.save.admin').bind('click', function(e) {
		$('.submit.end.save').trigger('click');
	});
	
	// Save stuff
	$('.save').bind('click', function(e) {
		var $this = $(this),
			$href = $this.attr('href').substr(1);
		
		if($href == 'preview') {
			$data = $this.siblings('form').serializeArray();
			var win = window.open();
			
			$file = 'preview';
			$this.backend($data, 'preview', function($this, response) {
				$('p.preview').fadeIn(400);
				win.target = '_blank';
				win.location = response;
				win.focus();
			});
			e.preventDefault();
		} else {
			$keyup = false;
			$this.siblings('form').submit();
		}
	});
	
	$('form input').bind('keyup', function(e) {
		var $this = $(this),
			$code = e.charCode ? e.charCode :
					(e.keyCode ? e.keyCode : 0);
		
		if($this.parents('form').hasClass('disable'))
			return;
			
		if($code == 13) { $keyup = false;
			$this.parents('form').submit();
		}
	});
	
	$('.con > .submit, .submit.mail').not('.save').bind('click', function() {
		var $this = $(this);
		$this.siblings('form').submit();
	});
	
	$('#name').bind('keyup blur', function() {
		var $this = $(this),
			$val  = $this.val(),
			$perm = $('#perma');
			
		if($val.length == 0) $perm.val('');
		
		$perm.backend({ name: $val }, 'perma',
		function($this, response) {
			$this.val(response);
		});
	});
		
	$('.number.i .bb a').bind('click', function(e) {
		var $this = $(this).parent().parent();
		$.fn.scrollT($this.offset().top - 10, 800,
		function() {
			$this.next('.upload').slideDown();
		});
	});
	
	// BBcode
	$(document).on('click', '.bb a', function(e) {
		var $this = $(this),
			$code = $this.attr('data-rel'),
			$txt  = $this.parent().siblings('textarea').attr('id');
		
		if($this.parents('.number.i').size() == 1) return;
		if($txt === undefined)
			$txt = $this.parent().parent().siblings('textarea').attr('id');
			
		if($this.parents('.number.i').size() == 1) return;
		$this.blur();
		addTag($txt, $code);
		e.preventDefault();
	});
	
	// Search
	$('input[name=search]').bind('keyup', function() {
		var $this = $(this),
			$key  = $this.val(),
			$sib  = $this.siblings('.results');
		$type = 'search';
		
		$this.backend($key, $type, function($this, response) {
			$sib.show().html(response);
			if(response.length === 0) { $sib.hide(); }
		});
	});
	
	// Dropdown
	$(document).on('click', 'div.dropdown', function() {
		var $this = $(this);
		$this.toggleClass('open');
		$this.next().slideToggle(300);
	});
	$(document).on('click', '.dropdown li', function() {
		var $this = $(this),
			$dex  = $this.index(),
			$div  = $this.parent().prev(),
			$inp  = $this.parent().next(),
			$sib  = $this.siblings();
			
		$sib.removeClass('a');
		$this.addClass('a');
		if($dex > 0) {
			$div.text($this.text());
			$text = $dex == 1 ? '' : $this.text();
			$inp.val($text);
			$div.trigger('click');
		}
		// Egen rubrik
		else {
			$this.parent().parent().fadeOut(400, function() {
				$(this).next().fadeIn(200)
				.attr('name', $inp.attr('name'));
				$inp.removeAttr('name');
			});
		}
	});

	$(document).on('change', 'input[type=file]', function() {
		var $this = $(this);
		$this.upload();
	});
	
	$(document).on('click', '.attached .delete', function() {
		var $this = $(this),
			$sib  = $this.siblings('a').attr('href');
			
		$sib = $sib.match(/[^\/]+\.[\w]{3,4}$/g, '');
		if($sib !== null) {
			$sib = $sib[0];
			$file = 'modify';
			
			$this.backend($sib, 'delete',
			function($this, response) {
				if(response == 1)
					$this.parent().slideUp(400, function() { $(this).remove(); });
			});
		}
	});
	$(document).on('click', '.attached .edit', function() {
		var $this = $(this),
			$sib  = $this.siblings('a');
			
		$href = $sib.attr('href').match(/[^\/]+\.[\w]{3,4}$/g, '');
		if($href !== null && $this.parent().find('.check').size() == 0) {
			$href = $href[0];
			$inp = $("<input />").
			attr({
				 type: 'text',
				 name: generate(5),
				 value: $sib.text()
				 })
			.bind('keyup', function(e, triggered) {
				var $this = $(this),
					$code = e.charCode ? e.charCode :
							(e.keyCode ? e.keyCode : 0);
							
				if($this.val().length > 0 && (triggered || (e.type == 'keyup' && $code == 13) )) {
					
					$file = 'modify';
					$this.backend( { key: $this.val(), file: $href }, 'edit',
					function($this, response) {
						$this.add($this.siblings('img')).fadeTo(200, 0, function() {
							$(this).slideUp(400, function() {
								$(this).remove();
							});
						});
						$sib.text(response);
					});
				}
			}).insertAfter($sib);
			$("<img alt='' class='check' />")
			.attr('src', c_http + "/gfx/check.png")
			.bind('click', function() { $(this).siblings('input').trigger('keyup', true); })
			.insertAfter($inp);
		}
	});
	
	$(document).on('click', '.modify .delete', function(e) {
		var $this = $(this).parent(),
			$href  = $this.siblings('.thb').find('a')
					.attr('href').match(/[^\/]+\.[\w]{3,4}$/g, '')[0];
		
			$file = 'modify';
			
			$this.backend($href, 'delete',
			function($this, response) {
				if(response == 1)
					$this.parent().slideUp(400, function() { $(this).remove(); });
			});
		e.preventDefault();
	});
	$(document).on('click', '.modify .edit', function(e) {
		var $this = $(this).parent(),
			$sib = $this.siblings('.name'),
			$name = $sib.text();
			
		if($name == '-')
			$name = '';
		
		if($this.parent().find('.check').size() == 0) {
			$href = $this.siblings('.thb').find('a')
			.attr('href').match(/[^\/]+\.[\w]{3,4}$/g, '')[0];
			
			$inp = $("<input />").
			attr({
				 type: 'text',
				 name: generate(5),
				 value: $name
				 })
			.bind('keyup', function(e, triggered) {
				var $this = $(this),
					$code = e.charCode ? e.charCode :
							(e.keyCode ? e.keyCode : 0);
							
				if(triggered || (e.type == 'keyup' && $code == 13) ) {
					
					$file = 'modify';
					$this.backend( { key: $this.val(), file: $href }, 'edit',
					function($this, response) {
						$this.add($this.siblings('img')).fadeTo(200, 0, function() {
							$(this).slideUp(400, function() {
								$(this).remove();
							});
						});
						$sib.text(response);
					});
				}
			}).insertAfter($this);
			$("<img alt='' class='check' />")
			.attr('src', c_http +  "/gfx/check.png")
			.bind('click', function() { $(this).siblings('input').trigger('keyup', true); })
			.insertAfter($inp);
		}
		e.preventDefault();
	});
	
	$.fn.upload = function() {
		var $this = $(this),
			$id   = $this.attr('id'),
			$prev = $this.prev();
		
		// if match i_ in data-page, require title to be filled in
		if($this.attr('data-page').match(/^\d+_/g) && $prev.val().length == 0) {
			$prev.css("border-color", '#EE5C5C');
			$this.val('');
			return;
		}
		
		$this.add($this.prev()).hide();
		$this.siblings('.fError').remove();
		
		var $load = $("<div class='loading' id='l_" + $id + "'></div>"),
			$err  = $("<div class='fError' id='e_" + $id + "'></div>");
			
		$load.html("<img src='" + c_http + "/gfx/ajax.gif" + "' class='ajax' alt='Laddar...' />").insertAfter($this);
		
		$.ajaxFileUpload({
			url: c_http +  "/ajax/upload.php",
			secureuri: false,
			fileElementId: $id,
			dataType: 'json',
			data: {
				image: $this.attr('name'),
				name: $prev.val(),
				page: $this.attr('data-page')
			},
			success: function (data, status) {
				var $this = $('#' + $id);
				if(typeof(data.error) != 'undefined') {
					if(data.error != '') {
						// Reset input
						$this.val('').show();
						$this.prev().show();
						$load.remove();
						// Insert error
						$err.html(data.error).insertAfter($this);
					} else {
						$load.remove();
						
						$new = $this.parent().clone().insertAfter($this.parent());
						
						// Reset file
						$new.find('input[type=file]')
						.attr({
							  id: generate(5),
							  name: generate(5)
						})
						.val('').show();
						// Reset name
						$new.find('input[type=text]').
						attr({
							  id: generate(5),
							  name: generate(5)
						})
						.css("border-color", '#C6DADE')
						.val('').show();
						
						// Treat image or document
						if($.inArray(data.msg, ['pdf', 'xls', 'doc', 'ppt']) !== -1) {
							var $att = $this.parent().prev();
							$att.find('p').remove()
							.end().append(data.img);
						} else {
							$(data.msg).insertAfter($('.table:first-child'));
						}
						$this.parent().remove();
					}
				} else alert(data);
			},
			error: function (data, status, e) {
				if(console) {
					console.log(data);
					console.log(status);
					console.log(e);
				}
			}
		});
	}
	
	// AJAX
	$.fn.backend = function($key, $type, $callback) {
		
		// Abort current call
		if(xhr != null) xhr.abort();
		var $this = $(this);
		$key = typeof $key == 'object'
			? JSON.stringify($key) : $key;
		
		xhr = $.ajax({
			type: "post",
			url: c_http + "/ajax/" + $file + ".php",
			data: 'key=' + encodeURIComponent($key) + '&type=' + $type,
			cache: false,
			success: function(response) {
				
				if(typeof($callback) == "function")
					$callback($this, response);
				
				$file = 'fetch';
				$key  = null;
				$type = null;
			}
		});
	}

});

if(typeof address !== 'undefined') {
	function initialize() {
		var geo = new google.maps.Geocoder;
		geo.geocode( {'address': address},
			function(results, status) {
				if(status == google.maps.GeocoderStatus.OK) {
					$ll = results[0].geometry.location;
					var d = 0;
					for(val in $ll) {
						$ll[d] = $ll[val];
						if(d == 1) break;
					d++;
					}
					$latlng = new google.maps.LatLng($ll[0], $ll[1]);
					var mapOptions = {
						center: $latlng,
						zoom: 15,
						mapTypeId: google.maps.MapTypeId.ROADMAP
					};
					var map = new google.maps.Map(document.getElementById("google"), mapOptions);
					var marker = new google.maps.Marker({
						position: $latlng,
						map: map,
						title: address
					});
	
				} else {
					$('#google').hide();
					//$('#google').html('Adressen kunde inte hittas. '+address).height('auto');
					console && console.log('address not found: ' + status);
				}
			});
	}
	google.maps.event.addDomListener(window, 'load', initialize);
}

/**
 * @author      :  Canberk BOLAT <canberk.bolat at gmail.com>
 * @additional author: http://stackoverflow.com/users/10840/daniel
 * @revised author: Peter Ljunggren
**/

function addTag(ele, tag) {
	var obj = document.getElementById(ele);
	var scrollPos = obj.scrollTop;
	var strPos = 0;
	var br = ((obj.selectionStart || obj.selectionStart == '0')
			   ? "ff" : (document.selection ? "ie" : false ) );
	
	if (br == "ie") {
		obj.focus();
		var bookmark = document.selection.createRange().getBookmark();
        var sel = obj.createTextRange();
        var bfr = sel.duplicate();
        sel.moveToBookmark(bookmark);
        bfr.setEndPoint("EndToStart", sel);
        strPos = bfr.text.length;
        strEnd = strPos + sel.text.length;
	}
	else if (br == "ff") {
		strPos = obj.selectionStart;
		strEnd = obj.selectionEnd;
	}
	
	var front = (obj.value).substring(0, strPos);
	var mid   = (obj.value).substring(strPos, strEnd);
	var back = (obj.value).substring(strPos + mid.length, obj.value.length);
	
	// Reset tags
	tagOpen = tagClose = '';
	var tags = {
		bold: 'b',
		italic: 'i',
		underline: 'u',
		headline: 'h',
		list: 'li'
	};
	
    switch(tag) {
        
        case "bold":
        case "italic":
        case "underline":
        case "headline":
		case 'list':
            tagOpen = "[" + tags[tag] + "]";
            tagClose = "[/" + tags[tag] + "]";
        break;

        case "url":
            url = prompt("Skriv in adressen nedan:", "");
            if (url.length == 0) {
                break;
            }

            tagOpen = "[url=" + url + "]";
            tagClose = "[/url]";
        break;
		
		case 'attach':
			var $this = $(obj).next('.attached');
			$.fn.scrollT($this.offset().top - 10, 800,
			function() {
				$this.next('.upload').slideDown();
			});
		break;
	}
	
	obj.value = front + tagOpen + mid + tagClose + back;
	strPos = strPos + (tagOpen + mid + tagClose).length;
	
	if(mid.length == 0)
		strPos -= tagClose.length;
		
	if (br == "ie") {
		obj.focus();
		var range = document.selection.createRange();
		strEnd = back.length;
		if(mid.length == 0) {
			range.moveStart ('character', -obj.value.length);
			strEnd -= tagClose.length;
		}
		range.moveStart('character', strPos);
		range.moveEnd('character', -strEnd);
        range.select();
	}
	else if (br == "ff") {
		obj.selectionStart = strPos;
		obj.selectionEnd = strPos;
		obj.focus();
	}
	obj.scrollTop = scrollPos;
}

function generate(num) {
    var str = '';
    var chars = "AaBbCcDdEeFfGgHhiJjKkLMmNnOoPpQqRrSsTtUuVvWwXxYyZz0123456789";

    for( var i=0; i < num; i++ )
        str += chars.charAt(Math.floor(Math.random() * chars.length));

    return str;
}