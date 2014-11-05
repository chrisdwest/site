<?php

	/**********************************************************************
	***********************************************************************
	TIMELINER FUNCTIONS
	**********************************************************************/


require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'class-tgm-plugin-activation.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'widgets.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'fonts.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'gallery.php';


add_action( 'tgmpa_register', 'timeliner_requred_plugins' );

function timeliner_requred_plugins(){
	$plugins = array(
		array(
				'name'                 => 'NHP Options',
				'slug'                 => 'nhpoptions',
				'source'               => get_stylesheet_directory() . '/lib/plugins/nhpoptions.zip',
				'required'             => true,
				'version'              => '',
				'force_activation'     => false,
				'force_deactivation'   => false,
				'external_url'         => '',
		),		
		array(
				'name'                 => 'Smeta',
				'slug'                 => 'smeta',
				'source'               => get_stylesheet_directory() . '/lib/plugins/smeta.zip',
				'required'             => true,
				'version'              => '',
				'force_activation'     => false,
				'force_deactivation'   => false,
				'external_url'         => '',
		),
		array(
				'name'                 => 'User Avatar',
				'slug'                 => 'wp-user-avatar',
				'source'               => get_stylesheet_directory() . '/lib/plugins/wp-user-avatar.zip',
				'required'             => true,
				'version'              => '',
				'force_activation'     => false,
				'force_deactivation'   => false,
				'external_url'         => '',
		),
	);

	/**
	 * Array of configuration settings. Amend each line as needed.
	 * If you want the default strings to be available under your own theme domain,
	 * leave the strings uncommented.
	 * Some of the strings are added into a sprintf, so see the comments at the
	 * end of each line for what each argument will be.
	 */
	$config = array(
			'domain'           => 'timeliner',
			'default_path'     => '',
			'parent_menu_slug' => 'themes.php',
			'parent_url_slug'  => 'themes.php',
			'menu'             => 'install-required-plugins',
			'has_notices'      => true,
			'is_automatic'     => false,
			'message'          => '',
			'strings'          => array(
				'page_title'                      => __( 'Install Required Plugins', 'timeliner' ),
				'menu_title'                      => __( 'Install Plugins', 'timeliner' ),
				'installing'                      => __( 'Installing Plugin: %s', 'timeliner' ),
				'oops'                            => __( 'Something went wrong with the plugin API.', 'timeliner' ),
				'notice_can_install_required'     => _n_noop( 'This theme requires the following plugin: %1$s.', 'This theme requires the following plugins: %1$s.' ),
				'notice_can_install_recommended'  => _n_noop( 'This theme recommends the following plugin: %1$s.', 'This theme recommends the following plugins: %1$s.' ),
				'notice_cannot_install'           => _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.' ),
				'notice_can_activate_required'    => _n_noop( 'The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.' ),
				'notice_can_activate_recommended' => _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.' ),
				'notice_cannot_activate'          => _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.' ),
				'notice_ask_to_update'            => _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.' ),
				'notice_cannot_update'            => _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.' ),
				'install_link'                    => _n_noop( 'Begin installing plugin', 'Begin installing plugins' ),
				'activate_link'                   => _n_noop( 'Activate installed plugin', 'Activate installed plugins' ),
				'return'                          => __( 'Return to Required Plugins Installer', 'timeliner' ),
				'plugin_activated'                => __( 'Plugin activated successfully.', 'timeliner' ),
				'complete'                        => __( 'All plugins installed and activated successfully. %s', 'timeliner' ),
				'nag_type'                        => 'updated'
			)
	);

	tgmpa( $plugins, $config );
}

if (!isset($content_width))
	{
	$content_width = 1920;
	}

function timeliner_options(){
	global $timeliner_opts;
	$args = array();
	$sections = array();
	$tabs = array();
	$args['dev_mode'] = false;
	$args['opt_name'] = 'timeliner';
	$args['menu_title'] = __('Timeliner Options', 'timeliner');
	$args['page_title'] = __('Timeliner Settings', 'timeliner');
	$args['page_slug'] = 'timeliner_theme_options';
	
	
	/**********************************************************************
	***********************************************************************
	OVERALL
	**********************************************************************/
	$sections[] = array(
		'title' => __('Overall', 'timeliner') ,
		'icon' => NHP_OPTIONS_URL . 'img/glyphicons/glyphicons_119_adjust.png',
		'desc' => __('This is basic section where you can set up main settings for your website.', 'timeliner'),
		'fields' => array(
			//Favicon
			array(
				'id' => 'site_favicon',
				'type' => 'upload',
				'title' => __('Site Favicon', 'timeliner') ,
				'desc' => __('Please upload favicon here in PNG or JPG format. <small>(18px 18px maximum size recommended)</small>)', 'timeliner')
			),	
			//Footer Copyrights
			array(
				'id' => 'footer_copyrights',
				'type' => 'text',
				'title' => __('Footer Copyrights', 'timeliner') ,
				'desc' => __('Input footer copyrights.', 'timeliner'),
			),
		)
	);
	/**********************************************************************
	***********************************************************************
	SEO
	**********************************************************************/
	
	$sections[] = array(
		'title' => __('SEO', 'timeliner') ,
		'icon' => NHP_OPTIONS_URL . 'img/glyphicons/glyphicons_079_signal.png',
		'desc' => __('This is important part for search engines.', 'timeliner'),
		'fields' => array(	
			// Keywords
			array(
				'id' => 'seo_keywords',
				'type' => 'text',
				'title' => __('Keywords', 'timeliner') ,
				'desc' => __('<br />Type here website keywords separated by comma. <small>(eg. lorem, ipsum, adiscipit)</small>.', 'timeliner')
			) ,
			
			// Description
			array(
				'id' => 'seo_description',
				'type' => 'textarea',
				'title' => __('Description', 'timeliner') ,
				'desc' => __('<br />Type here website description.', 'timeliner')
			) ,
		)
	);
	
	/**********************************************************************
	***********************************************************************
	SUBSCRIPTION
	**********************************************************************/
	
	$sections[] = array(
		'title' => __('Subscription', 'timeliner') ,
		'icon' => NHP_OPTIONS_URL . 'img/glyphicons/glyphicons_073_signal.png',
		'desc' => __('Set up subscription API key and list ID.', 'timeliner'),
		'fields' => array(
			// Mail Chimp API
			array(
				'id' => 'mail_chimp_api',
				'type' => 'text',
				'title' => __('API Key', 'timeliner') ,
				'desc' => __('<br />Type your mail chimp api key.', 'timeliner')
			) ,	
			// Mail Chimp List ID
			array(
				'id' => 'mail_chimp_list_id',
				'type' => 'text',
				'title' => __('List ID', 'timeliner') ,
				'desc' => __('<br />Type here ID of the list on which users will subscribe.', 'timeliner')
			) ,
		)
	);
	
	/***********************************************************************
	Appearance
	**********************************************************************/
	$sections[] = array(
		'title' => __('Appearance', 'timeliner') ,
		'icon' => NHP_OPTIONS_URL . 'img/glyphicons/glyphicons_030_pencil.png',
		'desc' => __('Set up the looks.', 'timeliner'),
		'fields' => array(
			//Hedaer BG Image
			array(
				'id' => 'header_bg_image',
				'type' => 'upload',
				'title' => __('Header Background Image', 'timeliner'),
				'desc' => __('Upload image for the header background.', 'timeliner'),
			),
			array(
				'id' => 'header_bg_color',
				'type' => 'color',
				'title' => __('Header Background Color', 'timeliner'),
				'desc' => __('Select color for the header bakground.', 'timeliner'),
				'std' => '#666666'
			),			
			/*--------------------------TITLE FONT-------------------------*/
			//Title font
			array(
				'id' => 'title_font',
				'type' => 'select',
				'title' => __('Title Font', 'timeliner'),
				'desc' => __('Select title font.', 'timeliner'),
				'options' => timeliner_all_google_fonts(),
				'std' => 'Roboto+Slab'
			),
			/*-------------------------TEXT FONT----------------------------*/
			//Text font
			array(
				'id' => 'text_font',
				'type' => 'select',
				'title' => __('Text Font', 'timeliner'),
				'desc' => __('Select font for the regular text.', 'timeliner'),
				'options' => timeliner_all_google_fonts(),
				'std' => 'Roboto+Slab'
			),
			/* -------------------MAIN BODY------------------------- */
			//Body Background Image
			array(
				'id' => 'body_bg_image',
				'type' => 'upload',
				'title' => __('Body Background Image', 'timeliner'),
				'desc' => __('Select image for the body.', 'timeliner'),
			),
			//Body Background Color
			array(
				'id' => 'body_bg_color',
				'type' => 'color',
				'title' => __('Body Background Color', 'timeliner'),
				'desc' => __('Select color for the body.', 'timeliner'),
				'std' => '#555555'
			),
			/* -------------------MAIN COLOR------------------------- */
			//Main Color
			array(
				'id' => 'main_color',
				'type' => 'color',
				'title' => __('Main Color', 'timeliner'),
				'desc' => __('Select main color for the site.', 'timeliner'),
				'std' => '#E74C3C'
			),
			//Main Color Button Font
			array(
				'id' => 'maincolor_btn_font_clr',
				'type' => 'color',
				'title' => __('Main Color Button Font', 'timeliner'),
				'desc' => __('Select button font color for the buttons with the main color.', 'timeliner'),
				'std' => '#FFFFFF'
			),	
		)
	);	
	
	/**********************************************************************
	***********************************************************************
	CONTACT PAGE SETTINGS
	**********************************************************************/
	
	$sections[] = array(
		'title' => __('Contact Page', 'timeliner') ,
		'icon' => NHP_OPTIONS_URL . 'img/glyphicons/glyphicons_151_edit.png',
		'desc' => __('Contact page settings.', 'timeliner'),
		'fields' => array(
			array(
				'id' => 'contact_form_email',
				'type' => 'text',
				'title' => __('Contact Email', 'timeliner') ,
				'desc' => __('<br />Input email where the messages should arive.', 'timeliner'),
			),
		)
	);
	
	$timeliner_opts = new NHP_Options($sections, $args, $tabs);
	}
if (class_exists('NHP_Options')){
	add_action('init', 'timeliner_options', 10);
}
/* do shortcodes in the excerpt */
add_filter('the_excerpt', 'do_shortcode');
	
/* include custom made widgets */
function timeliner_widgets_init(){

	register_sidebar(array(
		'name' => __('Blog Sidebar', 'timeliner') ,
		'id' => 'blog',
		'before_widget' => '<div class="widget white-block %2$s" >',
		'after_widget' => '</div>',
		'before_title' => '<div class="widget-title-wrap"><h6 class="widget-title">',
		'after_title' => '</h6></div>',
		'description' => __('Appears on the right side of the blog single page.', 'timeliner')
	));	

	register_sidebar(array(
		'name' => __('Page Left Sidebar', 'timeliner') ,
		'id' => 'left',
		'before_widget' => '<div class="widget white-block %2$s" >',
		'after_widget' => '</div>',
		'before_title' => '<div class="widget-title-wrap"><h6 class="widget-title">',
		'after_title' => '</h6></div>',
		'description' => __('Appears on the left side of the page.', 'timeliner')
	));	
	
	register_sidebar(array(
		'name' => __('Page Right Sidebar', 'timeliner') ,
		'id' => 'right',
		'before_widget' => '<div class="widget white-block %2$s" >',
		'after_widget' => '</div>',
		'before_title' => '<div class="widget-title-wrap"><h6 class="widget-title">',
		'after_title' => '</h6></div>',
		'description' => __('Appears on the right side of the page.', 'timeliner')
	));			
	register_sidebar( array(
'name' => 'Footer Sidebar 1',
'id' => 'footer-sidebar-1',
'description' => 'Appears in the footer area',
'before_widget' => '<aside id="%1$s" class="widget %2$s">',
'after_widget' => '</aside>',
'before_title' => '<h3 class="widget-title">',
'after_title' => '</h3>',
) );
register_sidebar( array(
'name' => 'Footer Sidebar 2',
'id' => 'footer-sidebar-2',
'description' => 'Appears in the footer area',
'before_widget' => '<aside id="%1$s" class="widget %2$s">',
'after_widget' => '</aside>',
'before_title' => '<h3 class="widget-title">',
'after_title' => '</h3>',
) );
register_sidebar( array(
'name' => 'Footer Sidebar 3',
'id' => 'footer-sidebar-3',
'description' => 'Appears in the footer area',
'before_widget' => '<aside id="%1$s" class="widget %2$s">',
'after_widget' => '</aside>',
'before_title' => '<h3 class="widget-title">',
'after_title' => '</h3>',
) );
}

add_action('widgets_init', 'timeliner_widgets_init');

/* total_defaults */
function timeliner_defaults( $id ){	
	$defaults = array(
		'site_favicon' => '',
		'footer_copyrights' => '',
		'seo_keywords' => '',
		'seo_description' => '',
		'mail_chimp_api' => '',
		'mail_chimp_list_id' => '',
		'header_bg_image' => '',
		'header_bg_color' => '#666666',
		'title_font' => 'Roboto+Slab',
		'text_font' => 'Roboto+Slab',
		'body_bg_image' => '',
		'body_bg_color' => '#555555',
		'main_color' => '#E74C3C',
		'maincolor_btn_font_clr' => '#FFFFFF',
		'contact_form_email' => ''
	);
	
	if( isset( $defaults[$id] ) ){
		return $defaults[$id];
	}
	else{
		
		return '';
	}
}

/* get option from theme options */
function timeliner_get_option($id){
	global $timeliner_opts;
	if( isset( $timeliner_opts ) ){
		$value = $timeliner_opts->get($id);
		if( isset( $value ) ){
			return $value;
		}
		else{
			return '';
		}
	}
	else{
		return timeliner_defaults( $id );
	}
}

	/* setup neccessary theme support, add image sizes */
function timeliner_setup(){
	load_theme_textdomain('timeliner', get_template_directory() . '/languages');
	add_theme_support('automatic-feed-links');
	add_theme_support('html5', array(
		'comment-form',
		'comment-list'
	));
	register_nav_menu('top-navigation', __('Top Navigation', 'timeliner'));
	
	add_theme_support('post-thumbnails',array( 'post', 'page', 'testimonial', 'lawyer' ));
	add_theme_support('post-formats',array( 'gallery', 'link', 'image', 'quote', 'status', 'video', 'audio' ));
	
	set_post_thumbnail_size(800, 600, true);
	if (function_exists('add_image_size')){
		add_image_size( 'widget-thumbnail', 200, 150, true );
	}

	add_theme_support('custom-header');
	add_theme_support('custom-background');
	add_editor_style();
}
add_action('after_setup_theme', 'timeliner_setup');


/* setup neccessary styles and scripts */
function timeliner_scripts_styles(){
	wp_enqueue_style( 'timeliner-bootstrap', get_template_directory_uri() . '/css/bootstrap.min.css' );
	wp_enqueue_style( 'timeliner-awesome', get_template_directory_uri() . '/css/font-awesome.min.css' );
	wp_enqueue_style( 'timeliner-magnific-css', get_template_directory_uri() . '/css/magnific-popup.css' );

	/*load selecte fonts*/
	wp_enqueue_style('timeliner-title-font', 'http://fonts.googleapis.com/css?family='.timeliner_get_option( 'title_font' ).':400,300,700');
	wp_enqueue_style('timeliner-text-font', 'http://fonts.googleapis.com/css?family='.timeliner_get_option( 'text_font' ).':400,300,700');
	
	/* load style.css */
	wp_enqueue_style('timeliner-style', get_stylesheet_uri() , array('dashicons'));
	wp_enqueue_style('dynamic-layout', admin_url('admin-ajax.php').'?action=dynamic_css', array());	
	
	if (is_singular() && comments_open() && get_option('thread_comments')){
		wp_enqueue_script('comment-reply');
	}

	wp_enqueue_script('jquery');	
	/* bootstrap */
	wp_enqueue_script('timeliner-bootstrap', get_template_directory_uri() . '/js/bootstrap.min.js', false, false, true);
	/* responsiveslides */
	wp_enqueue_script( 'timeliner-responsiveslides',  get_template_directory_uri() . '/js/responsiveslides.min.js', false, false, true);
	wp_enqueue_script( 'timeliner-modernizr',  get_template_directory_uri() . '/js/modernizr.js', false, false, true);

	/* custom */
	wp_enqueue_script('timeliner-magnific', get_template_directory_uri() . '/js/jquery.magnific-popup.min.js', false, false, true);
	wp_enqueue_script('timeliner-custom', get_template_directory_uri() . '/js/custom.js', false, false, true);

}
add_action('wp_enqueue_scripts', 'timeliner_scripts_styles');

function timeliner_admin_scripts_styles(){
	wp_enqueue_script('timeliner-admin-custom', get_template_directory_uri() . '/js/admin_custom.js', false, false, true);
}
add_action('admin_enqueue_scripts', 'timeliner_admin_scripts_styles');

/* add main css dynamically so it can support changing collors */
function dynaminc_css() {
  require(get_template_directory().'/css/main-color.css.php');
  exit;
}
add_action('wp_ajax_dynamic_css', 'dynaminc_css');
add_action('wp_ajax_nopriv_dynamic_css', 'dynaminc_css');

/* add admin-ajax */
function timeliner_custom_head(){
	echo '<script type="text/javascript">var ajaxurl = \'' . admin_url('admin-ajax.php') . '\';</script>';
}
add_action('wp_head', 'timeliner_custom_head');

function timeliner_smeta_images( $meta_key, $post_id, $default ){
	if(class_exists('SM_Frontend')){
		global $sm;
		return $result = $sm->sm_get_meta($meta_key, $post_id);
	}
	else{		
		return $default;
	}
}

/* check if smeta plugin is installed */
function timeliner_get_smeta( $meta_key, $post_data = '', $default ){
	if( !empty( $post_data[$meta_key] ) ){
		return $post_data[$meta_key][0];
	}
	else{
		return $default;
	}
}	
/* add custom meta fields using smeta to post types. */
function timeliner_custom_meta(){

	$post_meta_standard = array(
		array(
			'id' => 'iframe_standard',
			'name' => __( 'Input url to be embeded', 'timeliner' ),
			'type' => 'text',
		),
	);
	
	$meta_boxes[] = array(
		'title' => __( 'Standard Post Information', 'timeliner' ),
		'pages' => 'post',
		'fields' => $post_meta_standard,
	);	
	
	$post_meta_gallery = array(
		array(
			'id' => 'gallery_images',
			'name' => __( 'Add images for the gallery', 'timeliner' ),
			'type' => 'image',
			'repeatable' => 1
		)
	);

	$meta_boxes[] = array(
		'title' => __( 'Gallery Post Information', 'timeliner' ),
		'pages' => 'post',
		'fields' => $post_meta_gallery,
	);	
	
	
	$post_meta_audio = array(
		array(
			'id' => 'iframe_audio',
			'name' => __( 'Input URL for the audio', 'timeliner' ),
			'type' => 'text',
		),
		
		array(
			'id' => 'audio_type',
			'name' => __( 'Select type of the audio', 'timeliner' ),
			'type' => 'select',
			'options' => array(
				'embed' => __( 'Embed', 'timeliner' ),
				'direct' => __( 'Direct Link', 'timeliner' )
			)
		),
	);
	
	$meta_boxes[] = array(
		'title' => __( 'Audio Post Information', 'timeliner' ),
		'pages' => 'post',
		'fields' => $post_meta_audio,
	);
	
	$post_meta_video = array(
		array(
			'id' => 'video',
			'name' => __( 'Input video URL', 'timeliner' ),
			'type' => 'text',
		),
		array(
			'id' => 'video_type',
			'name' => __( 'Select video type', 'timeliner' ),
			'type' => 'select',
			'options' => array(
				'self' => __( 'Self Hosted', 'timeliner' ),
				'remote' => __( 'Embed', 'timeliner' ),
			)
		),
	);
	
	$meta_boxes[] = array(
		'title' => __( 'Video Post Information', 'timeliner' ),
		'pages' => 'post',
		'fields' => $post_meta_video,
	);
	
	$post_meta_quote = array(
		array(
			'id' => 'blockquote',
			'name' => __( 'Input the quotation', 'timeliner' ),
			'type' => 'textarea',
		),
		array(
			'id' => 'cite',
			'name' => __( 'Input the quoted person\'s name', 'timeliner' ),
			'type' => 'text',
		),
	);
	
	$meta_boxes[] = array(
		'title' => __( 'Quote Post Information', 'timeliner' ),
		'pages' => 'post',
		'fields' => $post_meta_quote,
	);	

	$post_meta_link = array(
		array(
			'id' => 'link',
			'name' => __( 'Input link', 'timeliner' ),
			'type' => 'text',
		),
	);
	
	$meta_boxes[] = array(
		'title' => __( 'Link Post Information', 'timeliner' ),
		'pages' => 'post',
		'fields' => $post_meta_link,
	);
	
	return $meta_boxes;
}

add_filter('sm_meta_boxes', 'timeliner_custom_meta');


/* get data of the attached image */
function timeliner_get_attachment( $attachment_id, $size ){
	$attachment = get_post( $attachment_id );
	if( !empty( $attachment ) ){
	$att_data_thumb = wp_get_attachment_image_src( $attachment_id, $size );
		return array(
			'alt' => get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
			'caption' => $attachment->post_excerpt,
			'description' => $attachment->post_content,
			'href' => $attachment->guid,
			'src' => $att_data_thumb[0],
			'title' => $attachment->post_title
		);
	}
	else{
		return array(
			'alt' => '',
			'caption' => '',
			'description' => '',
			'href' => '',
			'src' => '',
			'title' => '',
		);
	}
}

class timeliner_walker extends Walker_Nav_Menu {
  
	/**
	* @see Walker::start_lvl()
	* @since 3.0.0
	*
	* @param string $output Passed by reference. Used to append additional content.
	* @param int $depth Depth of page. Used for padding.
	*/
	public function start_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat( "\t", $depth );
		$output .= "\n$indent<ul role=\"menu\" class=\" dropdown-menu\">\n";
	}

	/**
	* @see Walker::start_el()
	* @since 3.0.0
	*
	* @param string $output Passed by reference. Used to append additional content.
	* @param object $item Menu item data object.
	* @param int $depth Depth of menu item. Used for padding.
	* @param int $current_page Menu item ID.
	* @param object $args
	*/
	public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		/**
		* Dividers, Headers or Disabled
		* =============================
		* Determine whether the item is a Divider, Header, Disabled or regular
		* menu item. To prevent errors we use the strcasecmp() function to so a
		* comparison that is not case sensitive. The strcasecmp() function returns
		* a 0 if the strings are equal.
		*/
		if ( strcasecmp( $item->attr_title, 'divider' ) == 0 && $depth === 1 ) {
			$output .= $indent . '<li role="presentation" class="divider">';
		} 
		else if ( strcasecmp( $item->title, 'divider') == 0 && $depth === 1 ) {
			$output .= $indent . '<li role="presentation" class="divider">';
		} 
		else if ( strcasecmp( $item->attr_title, 'dropdown-header') == 0 && $depth === 1 ) {
			$output .= $indent . '<li role="presentation" class="dropdown-header">' . esc_attr( $item->title );
		} 
		else if ( strcasecmp($item->attr_title, 'disabled' ) == 0 ) {
			$output .= $indent . '<li role="presentation" class="disabled"><a href="#">' . esc_attr( $item->title ) . '</a>';
		} 
		else {
			$class_names = $value = '';
			$classes = empty( $item->classes ) ? array() : (array) $item->classes;
			$classes[] = 'menu-item-' . $item->ID;
			$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );
			
			if ( $args->has_children ){
				$class_names .= ' dropdown';
			}
			
			$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';
			$id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args );
			$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

			$output .= $indent . '<li' . $id . $value . $class_names .'>';

			$atts = array();
			$atts['title'] = ! empty( $item->title )	? $item->title	: '';
			$atts['target'] = ! empty( $item->target )	? $item->target	: '';
			$atts['rel'] = ! empty( $item->xfn )	? $item->xfn	: '';

			// If item has_children add atts to a.
			$atts['href'] = ! empty( $item->url ) ? $item->url : '';
			if ( $args->has_children ) {
				$atts['data-toggle']	= 'dropdown';
				$atts['class']	= 'dropdown-toggle';
				$atts['data-hover']	= 'dropdown';
				$atts['aria-haspopup']	= 'true';
			} 

			$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args );

			$attributes = '';
			foreach ( $atts as $attr => $value ) {
				if ( ! empty( $value ) ) {
					$value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
					$attributes .= ' ' . $attr . '="' . $value . '"';
				}
			}

			$item_output = $args->before;

			/*
			* Glyphicons
			* ===========
			* Since the the menu item is NOT a Divider or Header we check the see
			* if there is a value in the attr_title property. If the attr_title
			* property is NOT null we apply it as the class name for the glyphicon.
			*/
			$subcaret = '';
			if( $depth > 0 && strpos( $class_names, 'current' ) !== false ){
				$subcaret = '<span class="fa fa-caret-right fa-2x current-caret"></span>';
			}			
			
			if ( ! empty( $item->attr_title ) ){
				$item_output .= '<a'. $attributes .'><span class="glyphicon ' . esc_attr( $item->attr_title ) . '"></span>&nbsp;';
			}
			else{
				$item_output .= '<a'. $attributes .'>'.$subcaret;
			}

			$item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
			if( $args->has_children && 0 === $depth ){
				$item_output .= ' <i class="fa fa-angle-down"></i>';
			}
			$item_output .= '</a>';
			$item_output .= $args->after;
			
			$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
		}
	}

	/**
	* Traverse elements to create list from elements.
	*
	* Display one element if the element doesn't have any children otherwise,
	* display the element and its children. Will only traverse up to the max
	* depth and no ignore elements under that depth.
	*
	* This method shouldn't be called directly, use the walk() method instead.
	*
	* @see Walker::start_el()
	* @since 2.5.0
	*
	* @param object $element Data object
	* @param array $children_elements List of elements to continue traversing.
	* @param int $max_depth Max depth to traverse.
	* @param int $depth Depth of current element.
	* @param array $args
	* @param string $output Passed by reference. Used to append additional content.
	* @return null Null on failure with no changes to parameters.
	*/
	public function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ) {
		if ( ! $element )
			return;

		$id_field = $this->db_fields['id'];

		// Display this element.
		if ( is_object( $args[0] ) ){
		   $args[0]->has_children = ! empty( $children_elements[ $element->$id_field ] );
		}

		parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
	}

	/**
	* Menu Fallback
	* =============
	* If this function is assigned to the wp_nav_menu's fallback_cb variable
	* and a manu has not been assigned to the theme location in the WordPress
	* menu manager the function with display nothing to a non-logged in user,
	* and will add a link to the WordPress menu manager if logged in as an admin.
	*
	* @param array $args passed from the wp_nav_menu function.
	*
	*/
	public static function fallback( $args ) {
		if ( current_user_can( 'manage_options' ) ) {

			extract( $args );

			$fb_output = null;

			if ( $container ) {
				$fb_output = '<' . $container;

				if ( $container_id ){
					$fb_output .= ' id="' . $container_id . '"';
				}

				if ( $container_class ){
					$fb_output .= ' class="' . $container_class . '"';
				}

				$fb_output .= '>';
			}

			$fb_output .= '<ul';

			if ( $menu_id ){
				$fb_output .= ' id="' . $menu_id . '"';
			}

			if ( $menu_class ){
				$fb_output .= ' class="' . $menu_class . '"';
			}

			$fb_output .= '>';
			$fb_output .= '<li><a href="' . admin_url( 'nav-menus.php' ) . '">Add a menu</a></li>';
			$fb_output .= '</ul>';

			if ( $container ){
				$fb_output .= '</' . $container . '>';
			}

			echo $fb_output;
		}
	}
}

/*generate random password*/
function timeliner_random_string( $length = 10 ) {
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$random = '';
	for ($i = 0; $i < $length; $i++) {
		$random .= $characters[rand(0, strlen($characters) - 1)];
	}
	return $random;
}

/* format wp_link_pages so it has the right css applied to it */
function timeliner_link_pages( $post_pages ){
	/* format pages that are not current ones */
	$post_pages = str_replace( '<a', '<a class="btn btn-default "', $post_pages );
	$post_pages = str_replace( '</span></a>', '</a>', $post_pages );
	$post_pages = str_replace( '><span>', '>', $post_pages );
	
	/* format current page */
	$post_pages = str_replace( '<span>', '<a href="javascript:;" class="btn btn-default active">', $post_pages );
	$post_pages = str_replace( '</span>', '</a>', $post_pages );
	
	return $post_pages;
	
}
/* create tags list */
function timeliner_the_tags(){
	$tags = get_the_tags();
	$list = array();
	if( !empty( $tags ) ){
		foreach( $tags as $tag ){
			$list[] = '<a href="'.esc_url( get_tag_link( $tag->term_id ) ).'" class="btn">'.$tag->name.'</a>';
		}
	}
	
	return join( " ", $list );
}

function timeliner_cloud_sizes($args) {
	$args['smallest'] = 14;
	$args['largest'] = 14;
	$args['unit'] = 'px';
	return $args; 
}
add_filter('widget_tag_cloud_args','timeliner_cloud_sizes');

function timeliner_the_category(){
	$list = '';
	$categories = get_the_category();
	if( !empty( $categories ) ){
		foreach( $categories as $category ){
			$list .= '<a href="'.esc_url( get_category_link( $category->term_id ) ).'">'.$category->name.'</a> ';
		}
	}
	
	return $list;
}

/* check if the blog has any media */
function timeliner_has_media(){
	$post_format = get_post_format();
	switch( $post_format ){
		case 'aside' : 
			return has_post_thumbnail() ? true : false; break;
			
		case 'audio' :
			$post_meta = get_post_custom();
			$iframe_audio = timeliner_get_smeta( 'iframe_audio', $post_meta, '' );
			if( !empty( $iframe_audio ) ){
				return true;
			}
			else if( has_post_thumbnail() ){
				return true;
			}
			else{
				return false;
			}
			break;
			
		case 'chat' : 
			return has_post_thumbnail() ? true : false; break;
		
		case 'gallery' :
			$post_meta = get_post_custom();
			$gallery_images = timeliner_smeta_images( 'gallery_images', get_the_ID(), array() );		
			if( !empty( $gallery_images ) ){
				return true;
			}
			else if( has_post_thumbnail() ){
				return true;
			}			
			else{
				return false;
			}
			break;
			
		case 'image':
			return has_post_thumbnail() ? true : false; break;
			
		case 'link' :
			$post_meta = get_post_custom();
			$link = timeliner_get_smeta( 'link', $post_meta, '' );
			if( !empty( $link ) ){
				return true;
			}
			else{
				return false;
			}
			break;
			
		case 'quote' :
			$post_meta = get_post_custom();
			$blockquote = timeliner_get_smeta( 'blockquote', $post_meta, '' );
			$cite = timeliner_get_smeta( 'cite', $post_meta, '' );
			if( !empty( $blockquote ) || !empty( $cite ) ){
				return true;
			}
			else if( has_post_thumbnail() ){
				return true;
			}
			else{
				return false;
			}
			break;
		
		case 'status' :
			return has_post_thumbnail() ? true : false; break;
	
		case 'video' :
			$post_meta = get_post_custom();
			$video_url = timeliner_get_smeta( 'video', $post_meta, '' );
			if( !empty( $video_url ) ){
				return true;
			}
			else if( has_post_thumbnail() ){
				return true;
			}
			else{
				return false;
			}
			break;
			
		default: 
			$post_meta = get_post_custom();
			$iframe_standard = timeliner_get_smeta( 'iframe_standard', $post_meta, '' );		
			if( !empty( $iframe_standard ) ){
				return true;
			}
			else if( has_post_thumbnail() ){
				return true;
			}
			else{
				return false;
			}
			break;
	}	
}

/* format pagination so it has correct style applied to it */
function timeliner_format_pagination( $page_links ){
	$list = '';
	if( !empty( $page_links ) ){
		foreach( $page_links as $page_link ){
			$page_link = str_replace( "<span class='page-numbers current'>", '<a href="javascript:;" class="active">', $page_link );
			$page_link = str_replace( '</span>', '</a>', $page_link );
			$page_link = str_replace( array( 'class="', "class='" ), array( 'class="btn btn-default ', "class='btn btn-default " ), $page_link );
			$list .= $page_link." ";
		}
	}
	
	return $list;
}


/*======================CONTACT FUNCTIONS==============*/
function timeliner_send_contact(){
	session_start();
	$errors = array();
	$name = esc_sql( $_POST['name'] );	
	$email = esc_sql( $_POST['email'] );
	$subject = esc_sql( $_POST['subject'] );
	$message = esc_sql( $_POST['message'] );
	if( empty( $name ) || empty( $subject ) || empty( $email ) || empty( $message ) ){
		$response = array(
			'error' => __( 'All fields are required.', 'timeliner' ),
		);
	}
	else if( !filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
		$response = array(
			'error' => __( 'E-mail address is not valid.', 'timeliner' ),
		);	
	}
	else{
		$email_to = timeliner_get_option( 'contact_form_email' );
		$message = "
			".__( 'Name: ', 'timeliner' )." {$name} \n			
			".__( 'Email: ', 'timeliner' )." {$email} \n
			".__( 'Message: ', 'timeliner' )."\n {$message} \n
		";
		
		$info = @wp_mail( $email_to, $subject, $message );
		$new_captcha = timeliner_captcha();
		if( $info ){
			$response = array(
				'success' => __( 'Your message was successfully submitted.', 'timeliner' ),
			);
		}
		else{
			$response = array(
				'success' => __( 'Unexpected error while attempting to send e-mail.', 'timeliner' ),
			);
		}
		
	}
	
	echo json_encode( $response );
	die();	
}
add_action('wp_ajax_contact', 'timeliner_send_contact');
add_action('wp_ajax_nopriv_contact', 'timeliner_send_contact');

/* =======================================================SUBSCRIPTION FUNCTIONS */
function timeliner_send_subscription( $email = '' ){
	$email = !empty( $email ) ? $email : $_POST["email"];
	$response = array();	
	if( filter_var( $email, FILTER_VALIDATE_EMAIL ) ){
		require_once( locate_template( 'includes/mailchimp.php' ) );
		$chimp_api = timeliner_get_option("mail_chimp_api");
		$chimp_list_id = timeliner_get_option("mail_chimp_list_id");
		if( !empty( $chimp_api ) && !empty( $chimp_list_id ) ){
			$mc = new MailChimp( $chimp_api );
			$result = $mc->call('lists/subscribe', array(
				'id'                => $chimp_list_id,
				'email'             => array( 'email' => $email )
			));
			
			if( $result === false) {
				$response['error'] = __( 'There was an error contacting the API, please try again.', 'timeliner' );
			}
			else if( isset($result['status']) && $result['status'] == 'error' ){
				$response['error'] = json_encode($result);
			}
			else{
				$response['success'] = __( 'You have successuffly subscribed to the newsletter.', 'timeliner' );
			}
			
		}
		else{
			$response['error'] = __( 'API data are not yet set.', 'timeliner' );
		}
	}
	else{
		$response['error'] = __( 'Email is empty or invalid.', 'timeliner' );
	}
	
	echo json_encode( $response );
	die();
}
add_action('wp_ajax_subscribe', 'timeliner_send_subscription');
add_action('wp_ajax_nopriv_subscribe', 'timeliner_send_subscription');

function timeliner_hex2rgb( $hex ){
	$hex = str_replace("#", "", $hex);

	$r = hexdec(substr($hex,0,2));
	$g = hexdec(substr($hex,2,2));
	$b = hexdec(substr($hex,4,2));
	return $r.", ".$g.", ".$b; 
}

function timeliner_get_avatar_url($get_avatar){
    preg_match("/src='(.*?)'/i", $get_avatar, $matches);
	if( empty( $matches[1] ) ){
		preg_match("/src=\"(.*?)\"/i", $get_avatar, $matches);
	}
    return $matches[1];
}

function timeliner_comments( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	$add_below = ''; 
	?>
	<!-- comment -->
	<div class="row comment-row <?php echo $comment->comment_parent != '0' ? 'comment-margin-left' : ''; ?> ">
		<!-- comment media -->
		<div class="col-md-2">
			<?php 
			$avatar = timeliner_get_avatar_url( get_avatar( $comment, 150 ) );
			if( !empty( $avatar ) ): ?>
				<img src="<?php echo esc_url( $avatar ); ?>" class="img-responsive" title="" alt="">
			<?php endif; ?>
		</div><!-- .comment media -->
		<!-- comment content -->
		<div class="col-md-10">
			<h6 class="comment-name">
				<?php comment_author(); ?><br/>
				<small><?php comment_time( 'F j, Y '.__('@','timeliner').' H:i' ); ?> </small>
			</h6>
			<?php 
			if ($comment->comment_approved != '0'){
			?>
				<p><?php echo get_comment_text(); ?></p>
			<?php 
			}
			else{ ?>
				<p><?php _e('Your comment is awaiting moderation.', 'timeliner'); ?></p>				
			<?php
			}
			?>
			<?php 
			comment_reply_link( 
				array_merge( 
					$args, 
					array( 
						'reply_text' => '<i class="fa fa-share"></i> <small>'.__( 'Reply', 'timeliner' ).'</small>', 
						'add_below' => $add_below, 
						'depth' => $depth, 
						'max_depth' => $args['max_depth'] 
					) 
				) 
			); ?>
			<div class="clearfix"></div>
		</div><!-- .comment content -->		
	</div><!-- .comment -->
	<?php  
}

function timeliner_end_comments(){
	return "";
}

function timeliner_embed_html( $html ) {
    return '<div class="video-container">' . $html . '</div>';
}
add_filter( 'embed_oembed_html', 'timeliner_embed_html', 10, 3 );
add_filter( 'video_embed_html', 'timeliner_embed_html' ); // Jetpack

/* add new column to the posts listing in the admin area*/
function timeliner_set_extra_columns( $columns ){
	$columns = array_slice($columns, 0, count($columns) - 1, true) + array("views" => __( 'Views', 'popularity' )) + array_slice($columns, count($columns) - 1, count($columns) - 1, true) ;	
	$columns = array_slice($columns, 0, count($columns) - 1, true) + array("likes" => __( 'Likes', 'popularity' )) + array_slice($columns, count($columns) - 1, count($columns) - 1, true) ;	
	return $columns;
}
add_filter( 'manage_edit-post_columns', 'timeliner_set_extra_columns' );

function bogismo_extra_columns( $column, $post_id ){
	switch ( $column ) {
		case 'views' :
			$views = get_post_meta( $post_id, 'views' );
			if( !empty( $views ) ){
				echo array_shift( $views );
			}
			else{
				echo '0';
			}
			break;
		case 'likes' :
			$likes = get_post_meta( $post_id, 'likes' );
			if( !empty( $likes ) ){
				echo array_shift( $likes );
			}
			else{
				echo '0';
			}
			break;			
	}
}
add_action( 'manage_post_posts_custom_column' , 'bogismo_extra_columns' , 10, 2 );

function timeliner_sorting_by_extra( $columns ){
	$custom = array(
		'views'	=> 'views',
		'likes'	=> 'likes',
	);
	return wp_parse_args($custom, $columns);
}
add_filter( 'manage_edit-post_sortable_columns', 'timeliner_sorting_by_extra' );

function timeliner_sort_by_extra( $query ){
	if( ! is_admin() ){
		return;	
	}

	$orderby = $query->get( 'orderby');
	if( $orderby == 'views' ){
		$query->set( 'meta_key', $orderby );
		$query->set( 'orderby', 'meta_value_num' );
	}
	else if( $orderby == 'likes'  ){
		$query->set( 'meta_key', $orderby );
		$query->set( 'orderby', 'meta_value_num' );		
	}
}
add_action( 'pre_get_posts', 'timeliner_sort_by_extra' );

/* get post views */
function timeliner_get_post_extra( $meta_key, $post_id = '' ){
	if( empty( $post_id ) ){
		$post_id = get_the_ID();
	}
	
	$extra_count = get_post_meta( $post_id, $meta_key );
	if( !empty( $extra_count ) ){
		return $extra_count[0];
	}
	else{
		return 0;
	}
}

function timeliner_password_form() {
	global $post;
	$label = 'pwbox-'.( empty( $post->ID ) ? rand() : $post->ID );
	$form = '<form class="protected-post-form" action="' . site_url() . '/wp-login.php?action=postpass" method="post">
				' . __( "This post is password protected. To view it please enter your password below:", "timeliner" ) . '
				<label for="' . $label . '">' . __( "Password:", "timeliner" ) . ' </label><div class="timeliner-form"><input name="post_password" class="form-control" id="' . $label . '" type="password" /><a class="btn btn-default submit_form"><i class="fa fa-sign-in"></i></a></div>
			</form>
	';
	return $form;
}
add_filter( 'the_password_form', 'timeliner_password_form' );

/* record post views */
function timeliner_count_post_extra( $meta_key = '', $post_id = '' ){
	$can_increment = true;
	$echo = false;
	/* if it is ajax it means that it is likes */
	if( empty( $meta_key ) ){
		global $wpdb;
		$post_id = $_POST['post_id'];
		$meta_key = 'likes';
		$ip_address = $_SERVER['REMOTE_ADDR'];
		$post_meta = get_post_meta( $post_id, 'ip_likes' );
		$query = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}postmeta AS postmeta WHERE meta_value='{$ip_address}' AND post_id='{$post_id}'" );
		if( !empty( $query ) ){			
			$can_increment = false;
		}
		else{
			$echo = true;
			update_post_meta( $post_id, 'ip_likes', $ip_address );
		}
	}
	else if( empty( $post_id ) ){
		$post_id = get_the_ID();
	}
	if( $can_increment == true ){		
		$extra_count = get_post_meta( $post_id, $meta_key );
		if( !empty( $extra_count ) ){
			$extra_count = $extra_count[0] + 1;
		}
		else{
			$extra_count = 1;
		}
	
		update_post_meta( $post_id, $meta_key, $extra_count );
		
		if( $echo ){
			echo json_encode(array(
				"count" => $extra_count
			));
			die();
		}
		else{
			return $extra_count;
		}
	}
	else{
		echo json_encode(array(
			"error" => __( 'You have already liked this post', 'timeliner' ),
		));
		die();
	}
}
add_action('wp_ajax_likes', 'timeliner_count_post_extra');
add_action('wp_ajax_nopriv_likes', 'timeliner_count_post_extra');

function timeliner_increase_views(){
	$post_meta = get_post_meta( get_the_ID(), 'views' );
	$count = 1;
	if( !empty( $post_meta ) ){
		$count = $post_meta[0] + 1;
	}
	
	update_post_meta( get_the_ID(), 'views', $count );
}

?>

