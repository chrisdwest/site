<?php header('Content-type: text/css'); 
	/* HEADER */
	$header_bg_image = timeliner_get_option( 'header_bg_image' );
	$header_bg_color = timeliner_get_option( 'header_bg_color' );//#666666
	
	/* MAINCOLOR */
	$main_color = timeliner_get_option( 'main_color' ); //#E74C3C
	$maincolor_btn_font_clr = timeliner_get_option( 'maincolor_btn_font_clr' ); //#FFFFFF
	
	/* BODY BACKGROUND */
	$body_bg_image = timeliner_get_option( 'body_bg_image' ); 
	$body_bg_color = timeliner_get_option( 'body_bg_color' );//#555555
	
	/* TITLE FONT */
	$title_font = timeliner_get_option( 'title_font' );//Roboto Slab
	
	/* TEXT FONT */
	$text_font = timeliner_get_option( 'text_font' );//Roboto Slab

?>

.top-bar{
	background-color: <?php echo $header_bg_color ?>;
	background-image: url(<?php echo $header_bg_image ?>);
}

.main-listing{
	background-color: <?php echo $body_bg_color ?>;
	background-image: url(<?php echo $body_bg_image ?>);
}

body{
	font-family: "<?php echo str_replace( "+", " ", $text_font ); ?>", sans-serif;
}

h1, h2, h3, h4, h5, h6{
	font-family: "<?php echo str_replace( "+", " ", $title_font ); ?>", sans-serif;
}

a, a:hover, a:focus, a:active, a:visited,
.post-meta a:hover,
.post-title:hover h2,
.nav.navbar-nav ul li.open > a,
.nav.navbar-nav ul li.open > a:hover,
.nav.navbar-nav ul li.open > a:focus,
.nav.navbar-nav ul li.open > a:active,
.nav.navbar-nav ul li.current > a,
.navbar-nav ul li.current-menu-parent > a, .navbar-nav ul li.current-menu-ancestor > a, .navbar-nav ul li.current-menu-item  > a,
.nav.navbar-nav ul li a:hover,
.navbar-toggle,
.fake-thumb-holder .post-format,
.comment-reply-link:hover,
.widget-small-link:hover,
.widget_custom_posts .nav-tabs li a:hover, .widget_custom_posts .nav-tabs li a:active, .widget_custom_posts .nav-tabs li a:focus{
	color: <?php echo $main_color; ?>;
}
@media only screen and (max-width: 768px) {
	.navbar-default .navbar-nav .open .dropdown-menu > li > a:hover, 
	.navbar-default .navbar-nav .open .dropdown-menu > li > a:focus,
	.navbar-default .navbar-nav .open .dropdown-menu > li > a:active
	.navbar-default .navbar-nav .open .dropdown-menu > li.current > a,
	.navbar-default .navbar-nav .open .dropdown-menu > li.current-menu-ancestor > a,
	.navbar-default .navbar-nav .open .dropdown-menu > li.current-menu-item > a,
	.navbar-default .navbar-nav .open .dropdown-menu > li.current-menu-parent > a{
		color: <?php echo $main_color; ?>;
	}	
}

blockquote,
.cd-timeline-content.sticky-post,
#navigation .nav.navbar-nav > li.open > a,
#navigation .nav.navbar-nav > li > a:hover,
#navigation .nav.navbar-nav > li > a:focus ,
#navigation .nav.navbar-nav > li > a:active,
#navigation .nav.navbar-nav > li.current > a,
#navigation .navbar-nav > li.current-menu-parent > a, #navigation  .navbar-nav > li.current-menu-ancestor > a, #navigation  .navbar-nav > li.current-menu-item  > a,
.widget-title,
.widget_custom_posts .nav-tabs li a:hover, .widget_custom_posts .nav-tabs li a:active, .widget_custom_posts .nav-tabs li a:focus{
	border-color: <?php echo $main_color; ?>;
}

table th,
.tagcloud a, .btn, a.btn,
.cd-timeline-img,
.rslides_nav,
.form-submit #submit,
.leftside,
.rightside,
.widget_custom_posts .nav-tabs li a:hover, .widget_custom_posts .nav-tabs li a:active, .widget_custom_posts .nav-tabs li a:focus{
	background: <?php echo $main_color; ?>;
}

table th,
.tagcloud a, .btn, a.btn,
.cd-timeline-img,
.rslides_nav,
.form-submit #submit,
.leftside,
.rightside,
.cd-timeline-img h2,
.cd-timeline-content .cd-read-more,
.overlay,
.content-bg blockquote h2, .content-bg a h2,
.overlay .post-meta, .overlay .post-meta a,
.rslides_nav .fa,
table th a, table th a:hover, table th a:focus, table th a:active,
.widget_custom_posts .nav-tabs li a:hover, .widget_custom_posts .nav-tabs li a:active, .widget_custom_posts .nav-tabs li a:focus,
.gallery-overlay{
	color: <?php echo $maincolor_btn_font_clr ?>;
}

.gallery-overlay,
.overlay{
	background: rgba( <?php echo timeliner_hex2rgb( $main_color ); ?>, 0.8  );
}

.spinner > div{
	background-color: <?php echo $maincolor_btn_font_clr ?>;
}

