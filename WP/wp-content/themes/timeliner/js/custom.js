jQuery(document).ready(function($){
	"use strict";
	var loaded = false;
	
	function hide_loader(){
		if( !loaded ){
			$('body').css('overflow','visible');
			$(".leftside").stop().animate({width:'0px'}, 1000 );
			$(".rightside").stop().animate({width:'0px'},1000 );
			$('.spinner').stop().animate({top: '-1000px'}, 1000);
			$('.spinner-wrap').fadeOut(1000, function(){$(this).remove()});	
			loaded = true;
		}
	}
	
	$(window).load( function(){
		hide_loader();
	});	
	
	setTimeout(function(){ hide_loader() }, 10000);

	var $timeline_block = $('.cd-timeline-block');

	//hide timeline blocks which are outside the viewport
	$timeline_block.each(function(){
		if( $(this).offset().top > $(window).scrollTop()+$(window).height() * 0.75 ) {
			$(this).find('.cd-timeline-img, .cd-timeline-content').addClass('is-hidden');
		}
	});

	//on scolling, show/animate timeline blocks when enter the viewport
	$(window).on('scroll', function(){		
		if( $(window).scrollTop() > 200 ){
			$('.to_top').fadeIn();
		}
		else{
			$('.to_top').fadeOut();
		}
		$('.cd-timeline-block').each(function(){
			if( $(this).offset().top <= $(window).scrollTop()+$(window).height()*0.75 && $(this).find('.cd-timeline-img').hasClass('is-hidden') ) {
				$(this).find('.cd-timeline-img').removeClass('is-hidden').addClass('bounce-in');
				$(this).find('.cd-timeline-content').animate({
					opacity: 1,
					top: '0px'
				}, 600);
			}
		});
	});
	
	$('.to_top').click(function(e){
		e.preventDefault();
		$("html, body").stop().animate(
			{
				scrollTop: 0
			}, 
			{
				duration: 1200
			}
		);		
	});	
	
	/* RESPONSIVE SLIDES FOR THE GALLERY POST TYPE */
	$('.gallery-slider').responsiveSlides({
		speed: 800,
		auto: false,
		pager: false,
		nav: true,
		prevText: '<i class="fa fa-chevron-left"></i>',
		nextText: '<i class="fa fa-chevron-right"></i>',
	});
	
	/* NAVIGATION */
	function handle_navigation(){
		if ($(window).width() >= 767) {
			$('ul.nav li.dropdown, ul.nav li.dropdown-submenu').hover(function () {
				$(this).addClass('open').find(' > .dropdown-menu').stop(true, true).hide().slideDown(200);
			}, function () {
				$(this).removeClass('open').find(' > .dropdown-menu').stop(true, true).show().slideUp(200);
	
			});
		}
		else{
			$('ul.nav li.dropdown, ul.nav li.dropdown-submenu').unbind('mouseenter mouseleave');
		}
	}
	handle_navigation();
	
	$(window).resize(function(){
		setTimeout(function(){
			handle_navigation();
		}, 200);
	});		

	
	/* PAGINATION */
	$('.load-more').click(function(e){
		e.preventDefault();
		var $this = $(this);
		var $parent = $this.parents( '.load-more-block' );
		var next_link = $this.data('next_link');
		$this.find('i').attr( 'class', 'fa fa-spin fa-spinner' );
		var last_class = $('.cd-timeline-block:not(.load-more-block):last').attr('class');
		var counter = 2;		
		if( last_class.indexOf( 'even' ) !== -1 ){
			counter = 1;
		}
		var last_year = $('.year-block:last h2').text();
		$.ajax({
			url: next_link,
			success: function( response ){
				$(response).find( '.cd-timeline-block:not(.load-more-block)' ).each(function(){
					var $$this = $(this);
					if( $$this.hasClass('year-block') ){
						if( $$this.find('h2').text() != last_year ){
							last_year = $$this.find('h2').text();
							$parent.before( '<div class="cd-timeline-block year-block">' + $(this).html() + '</div>' );
						}
					}
					else{
						$$this.find('.cd-timeline-img, .cd-timeline-content').addClass('is-hidden');
						$parent.before( '<div class="cd-timeline-block '+( counter == 2 ? 'even' : '' )+' is-hidden">' + $$this.html() + '</div>' );
						counter == 2 ? counter = 1 : counter++;
					}
				});
				
				var $link = $(response).find( '.load-more' ).attr( 'data-next_link' );
				
				if( $link != "" ){
					$this.data( 'next_link', $link );
				}
				else{
					$parent.remove();
				}
			},
			complete: function(){
				$this.find('i').attr( 'class', 'fa fa-angle-double-down' );
			}
		})
	});
	
	//GALERY SLIDER
	$('.post-slider').responsiveSlides({
		speed: 800,
		auto: false,
		pager: false,
		nav: true,
		prevText: '<i class="fa fa-chevron-left"></i>',
		nextText: '<i class="fa fa-chevron-right"></i>',
	});	

	/* SUBMIT FORMS */
	$('.submit_form').click(function(){
		$(this).parents('form').submit();
	});
	
	
	/* SUBSCRIBE */
	$('.subscribe').click( function(e){
		e.preventDefault();
		var $this = $(this);
		var $parent = $this.parents('.subscribe-form');		
		
		$.ajax({
			url: ajaxurl,
			method: "POST",
			data: {
				action: 'subscribe',
				email: $parent.find( '.email' ).val()
			},
			dataType: "JSON",
			success: function( response ){
				if( !response.error ){
					$parent.find('.sub_result').html( '<div class="alert alert-success" role="alert"><span class="fa fa-check-circle"></span> '+response.success+'</div>' );
				}
				else{
					$parent.find( '.sub_result' ).html( '<div class="alert alert-danger" role="alert"><span class="fa fa-times-circle"></span> '+response.error+'</div>' );
				}
			}
		})
	} );
	
	
	/* handle likes*/
	$('.post-like').click(function(e){
		e.preventDefault();
		var $this = $(this);
		var post_id = $this.data('post_id');
		
		$.ajax({
			url: ajaxurl,
			method: "POST",
			dataType: "JSON",
			data: {
				action: 'likes',
				post_id: post_id
			},
			success: function( response ){
				if( !response.error ){
					$this.find('.like-count').text( response.count );
				}
				else{
					alert( response.error );
				}
			}
		});
	});	
		
	/* contact script */
	$('.send-contact').click(function(e){
		e.preventDefault();
		
		$.ajax({
			url: ajaxurl,
			method: "POST",
			data: {
				action: 'contact',
				name: $('.name').val(),
				email: $('.email').val(),
				subject: $('.subject').val(),
				message: $('.message').val()
			},
			dataType: "JSON",
			success: function( response ){
				if( !response.error ){
					$('.send_result').html( '<div class="alert alert-success" role="alert"><span class="fa fa-check-circle"></span> '+response.success+'</div>' );
				}
				else{
					$('.send_result').html( '<div class="alert alert-danger" role="alert"><span class="fa fa-times-circle"></span> '+response.error+'</div>' );
				}
			}
		})
	});
	
	/* MAGNIFIC POPUP FOR THE GALLERY */
	$('.gallery').each(function(){
		var $this = $(this);
		$this.magnificPopup({
			type:'image',
			delegate: 'a',
			gallery:{enabled:true},
		});
	});

});