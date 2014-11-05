<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="keywords" content="<?php echo esc_attr( timeliner_get_option( 'seo_keywords' ) ) ?>"/>
    <meta name="description" content="<?php echo esc_attr( timeliner_get_option( 'seo_description' ) ) ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

    <!-- Title -->
	<title><?php
	/*
	 * Print the <title> tag based on what is being viewed.
	 */
	global $page, $paged;

	wp_title( '|', true, 'right' );

	// Add the blog name.
	bloginfo( 'name' );

	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		echo " | $site_description";

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 )
		echo ' | ' . sprintf( __( 'Page %s', 'timeliner' ), max( $paged, $page ) );

	?></title>

    <!-- Favicon -->
	<?php 
		$favicon = timeliner_get_option( 'site_favicon' );
		if( !empty( $favicon ) ):
	?>
		<link rel="shortcut icon" type="image/x-icon" href="<?php echo esc_url( $favicon ); ?>">
	<?php
		endif;
	?>
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
	<![endif]-->
	
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<div class="spinner-wrap">
	<div class="leftside"></div>
	<div class="rightside"></div>
	<div class="spinner">
		<div class="rect1"></div>
		<div class="rect2"></div>
		<div class="rect3"></div>
		<div class="rect4"></div>
		<div class="rect5"></div>
	</div>	
</div>
<!-- ==================================================================================================================================
TOP BAR
======================================================================================================================================= -->
<section class="top-bar">
	<div class="container">
		<div class="row">
			<div class="col-md-9">
				<div id="navigation">
					<button class="navbar-toggle button-white menu" data-toggle="collapse" data-target=".navbar-collapse">
						<span class="sr-only"><?php _e( 'Toggle navigation', 'timeliner' ) ?></span>
						<i class="fa fa-bars fa-3x"></i>
					</button>
					<div class="navbar navbar-default" role="navigation">
						<div class="collapse navbar-collapse">
							<?php
							if ( ( $locations = get_nav_menu_locations() ) && isset( $locations[ 'top-navigation' ] ) ) {
								wp_nav_menu( array(
									'theme_location'  	=> 'top-navigation',
									'menu_class'        => 'nav navbar-nav',
									'container'			=> false,
									'echo'          	=> true,
									'items_wrap'        => '<ul class="%2$s">%3$s</ul>',
									'walker' 			=> new timeliner_walker
								) );
							}
							?>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-3">
<form role="search" method="get" class="searchform" action="<?php echo esc_url( site_url('/') ); ?>">
	<div class="timeliner-form">
		<input type="text" value="" name="s" class="form-control" placeholder="<?php esc_attr_e( 'Search...', 'timeliner' ); ?>">
		<a class="btn btn-default submit_form"><i class="fa fa-search"></i></a>
	</div>
</form>			
		</div>
</div>		
		<div class="row">
			<div class="col-sm-12">
			
			</div>
		</div>
	</div>
				<div class="blog-title">			
					<img src="wp-content/uploads/2014/10/NPlogo7.png"> 
					<h1>
						<?php 
							if ( is_category() ){
								echo __('Category: ', 'timeliner');
								single_cat_title();
							}
							else if( is_404() ){
								_e( '404 Page Not Found', 'timeliner' );
							}
							else if( is_tag() ){
								echo __('Search by tag: ', 'timeliner'). get_query_var('tag'); 
							}
							else if( is_author() ){
								echo __('Posts written by ', 'timeliner'). get_the_author_meta( 'display_name' ); 
							}
							else if( is_archive() ){
								echo __('Archive for ', 'timeliner'). single_month_title(' ',false); 
							}
							else if( is_search() ){ 
								echo __('Search results for: ', 'timeliner').' '. get_search_query();
							}
							else if( is_home() ){
								echo bloginfo( 'name' );
							}
							else{
								the_title();
							}
							?>						
					</h1>
					<small>
						<?php 
							if( is_home() ){ 
								echo $site_description; 
							} 
							else if( is_404() ){
								_e( 'Sorry but we could not find what you are looking for', 'timeliner' );
							}
						?>
					</small>
					<p>
					<smaller> 
						<?php
							if ( is_home() ){
							_e (' Scroll down to find out what we are doing', 'timeliner');
							}
						?>
					</smaller>
					<p>
					<img src='wp-content/uploads/2014/10/down-arrow-circle-md.png' >
				</div>		
</section>

