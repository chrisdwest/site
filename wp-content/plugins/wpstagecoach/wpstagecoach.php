<?php
/*
Plugin Name: WP Stagecoach
Plugin URI: https://wpstagecoach.com/
Description: Wordpress staging sites made easy
Version: 0.3.0
Author: Jonathan Kay
Author URI: https://wpstagecoach.com/
*/
/*
Copyright 2014 Alchemy Computer Solutions, Inc.
*/

if ( ! defined('ABSPATH') ) {
	die('Please do not load this file directly.');
}

/* some plugin defines */
define('WPSTAGECOACH_PLUGIN_URL',	plugins_url().'/wpstagecoach/');
#define('WPSTAGECOACH_PLUGIN_URL',	plugin_dir_url(__FILE__));
#define('WPSTAGECOACH_TEMP_URL',		WPSTAGECOACH_PLUGIN_URL.'temp/'); // used get get around symlinks of dir
define('WPSTAGECOACH_REL_DIR',		str_replace(site_url(), '.', WPSTAGECOACH_PLUGIN_URL));
define('WPSTAGECOACH_REL_TEMP_DIR',	WPSTAGECOACH_REL_DIR.'temp/');
define('WPSTAGECOACH_TEMP_DIR',		dirname(__FILE__).'/'.'temp/');
define('WPSTAGECOACH_VERSION',		'0.3.0');
define('WPSTAGECOACH_CONDUCTOR',	'https://conductor-beta.wpstagecoach.com');
define('WPSTAGECOACH_ERRDIV',		'<div class="wpscerror">');
define('WPSTAGECOACH_WARNDIV',		'<div class="wpscwarn">');
define('WPSTAGECOACH_LARGE_FILE',	10485760);

set_site_transient( 'update_plugins', null );

/* What to do when the plugin is activated? */
register_activation_hook(__FILE__,'wpstagecoach_install');

/* What to do when the plugin is deactivated? */
register_deactivation_hook( __FILE__, 'wpstagecoach_remove' );

function wpstagecoach_install() {
	// twiddle my thumbs for now
}

function wpstagecoach_remove() {
	require_once 'includes/wpsc-functions.inc.php';
	$wpsc = get_option('wpstagecoach');
	if( $wpsc['delete_settings'] == true ){
		global $wpdb;
		wpsc_rm_rf( WPSTAGECOACH_TEMP_DIR );
		$wpdb->query("delete from ".$wpdb->prefix."options where option_name like 'wpstagecoach%';");
	}
}

function wpstagecoach_admin_init() {
	wp_register_script( 'wpsc-js', WPSTAGECOACH_PLUGIN_URL.'assets/wpstagecoach.js', array('jquery') );
	wp_register_style( 'wpsc-css', WPSTAGECOACH_PLUGIN_URL.'assets/wpstagecoach.css' );
}
add_action( 'admin_init', 'wpstagecoach_admin_init' );





function wpstagecoach_admin_menu() {
	$wpscmainpage = add_menu_page('WP Stagecoach', 'WP Stagecoach', 'manage_options','wpstagecoach', 'wpstagecoach_main', WPSTAGECOACH_PLUGIN_URL.'assets/wpsc-logo-16.png');

		// this is done so we don't unnecessarily do DB lookups when you aren't using our plugin!
		$wpsc = get_option('wpstagecoach');

		add_action('load-'.$wpscmainpage, 'wpstagecoach_admin_scripts');
		if( isset($wpsc['staging-site']) && !empty($wpsc['staging-site']) ) {
			$wpscimportpage = add_submenu_page('wpstagecoach', 'WP Stagecoach Import', 'Import Changes', 'manage_options', 'wpstagecoach_import', 'wpstagecoach_import');
			add_action('load-'.$wpscimportpage, 'wpstagecoach_admin_scripts');
		}
		$wpscsettingspage = add_submenu_page('wpstagecoach', 'WP Stagecoach Settings', 'Settings', 'manage_options', 'wpstagecoach_settings', 'wpstagecoach_settings');
		add_action('load-'.$wpscsettingspage, 'wpstagecoach_admin_scripts');
		if( isset($wpsc['advanced']) && $wpsc['advanced'] ==  true ) {
			$wpscdebugpage = add_submenu_page('wpstagecoach', 'WP Stagecoach Advanced', 'Advanced', 'manage_options', 'wpstagecoach_advanced', 'wpstagecoach_advanced');
			add_action('load-'.$wpscdebugpage, 'wpstagecoach_admin_scripts');
		}
		if( isset($wpsc['debug']) && $wpsc['debug'] ==  true ) {
			$wpscdebugpage = add_submenu_page('wpstagecoach', 'WP Stagecoach Debug', 'Debug', 'manage_options', 'wpstagecoach_debug', 'wpstagecoach_debug');
			add_action('load-'.$wpscdebugpage, 'wpstagecoach_admin_scripts');
		}
}
add_action('admin_menu', 'wpstagecoach_admin_menu');

function wpstagecoach_admin_scripts() {
	add_action('admin_enqueue_scripts', 'wpstagecoach_enqueue_js');
}

function wpstagecoach_enqueue_js() {
	wp_enqueue_script( 'wpsc-js' );
	wp_enqueue_style( 'wpsc-css' );
}






/**
* Add staging site notification to toolbar
*/
function wpstagecoach_staging_notice_menu() {
	global $wp_admin_bar;
	$wp_admin_bar->add_node(array(
	'id' => 'wpstagecoach',
	'title' => __('This is your Staging Site!'),
///////////////////     TODO    ///////////////////////
//           make line below point to the live site?
	'href' => 'https://wpstagecoach.com'
	));
}

if( $stage_site = get_option('wpstagecoach-stage-site') ){
	if( $_SERVER['SERVER_NAME'] == $stage_site )
		add_action('wp_before_admin_bar_render', 'wpstagecoach_staging_notice_menu');
}




function wpstagecoach_main() {
	 #    #    ##       #    #    #
	 ##  ##   #  #      #    ##   #
	 # ## #  #    #     #    # #  #
	 #    #  ######     #    #  # #
	 #    #  #    #     #    #   ##
	 #    #  #    #     #    #    #
	
	#####################################################################################
	################                  MAIN PAGE                 #########################
	#####################################################################################

	require_once 'includes/wpsc-functions.inc.php';


	if( empty($wpsc['username']) || empty($wpsc['apikey']) ){
		wp_redirect(get_admin_url().'admin.php?page=wpstagecoach_settings');
		return;
	}


	if( empty($_POST) || ( isset($display_main_form) && $display_main_form == true) ){

		wpsc_echo_header();
	}


	// check basic sanity, it will display error if it fails, if so we need to return.
	$wpsc_sanity=wpsc_sanity_check();
	if($wpsc_sanity == false)
		return;



	// display the create-a-staging-site form
	if( empty($_POST) || ( isset($display_main_form) && $display_main_form == true) ){




		wpsc_display_welcome($wpsc_sanity['auth']);

		wpsc_display_main_form($wpsc_sanity['auth'], $wpsc);

		if( isset($wpsc['db_backup_file']) ){
			echo 'The database backup WP Stagecoach made before importing changes is:<br/>'.$wpsc['db_backup_file'].'</br>'.PHP_EOL;
		}
	}




	// if we have $_POST data, we are doing stuff!  Otherwise, just display the create-a-staging-site form
	if( !empty($_POST) ){
		if( WPSC_DEBUG ){
			echo '$_POST (from main() function): <pre>';
			print_r($_POST);
			echo '</pre>';
		}

		if ( isset($_POST['wpsc-feedback']) ) {
			// we have feedback in _POST, so we need to submit it with this function
			wpsc_send_feedback();
		} elseif( isset($_POST['wpsc-create']) ){
			// updating settings in initial setup (or bad input from settings page)
			require_once 'includes/wpsc-create.inc.php';
		} elseif( isset($_POST['wpsc-settings-updated']) ) {
			// if we updated a setting that requires refreshing the screen (eg, debug), we want to just display the main form
			$display_main_form = true;
		} elseif( isset($_POST['wpsc-delete-site']) ){
			// delete the site!
			require_once 'includes/wpsc-delete.inc.php';
		}
	}








	if(WPSC_DEBUG)
		echo '<br/>this is the main() function saying bye!<br/>'.PHP_EOL;
}





function wpstagecoach_import() {
   ###
    #   ##    # ######    ####  ######    #####
    #    ##  ##  #    #  #    #  #    #   # # #
    #    # ## #  #    #  #    #  #    #     #
    #    #    #  #####   #    #  #####      #
    #    #    #  #       #    #  #   #      #
   ###  ##    ## #        ####  ##    ##   ###

	###################################################################################################
	################                  Staging Site Check menu                 #########################
	###################################################################################################
	require_once 'includes/wpsc-functions.inc.php';
	global $wpdb;


	if( empty($wpsc['username']) || empty($wpsc['apikey']) ){
		wpstagecoach_settings();
		return;
	}

	if( empty($_POST) )
		wpsc_echo_header();
	
	// check basic sanity, it will display error if it fails, if so we need to return.
	$wpsc_sanity=wpsc_sanity_check('import');
	if($wpsc_sanity == false)
		return;

	// now that we have sanity, we can make some defines
	define('WPSTAGECOACH_LIVE_SITE',	$wpsc_sanity['auth']['live-site']);
	define('WPSTAGECOACH_STAGE_SITE',	$wpsc_sanity['auth']['stage-site']);
	define('WPSTAGECOACH_SERVER',		$wpsc_sanity['auth']['server']);
	define('WPSTAGECOACH_LIVE_PATH',	$_SERVER['DOCUMENT_ROOT']);
	#define('WPSTAGECOACH_STAGE_PATH',	'/var/www/'.$_POST['wpsc-create-stageurl'].'/staging-site');
	define('WPSTAGECOACH_DOMAIN',		'.wpstagecoach.com');




	if( $wpsc_sanity['auth']['type'] != 'live' ){
		$msg ='Please go to your live site, <a href="http://'.$wpsc_sanity['auth']['live-site'].'" target="_blank">'.$wpsc_sanity['auth']['live-site'].'</a> to interact with WP Stagecoach on your site.<br/>'.PHP_EOL;
		wpsc_display_error($msg, false);
		return;
	}

	$changes = get_option('wpstagecoach_retrieved_changes');	
	######      We need to see if we have changes stored locally in the DB
	if ( is_array($changes) && sizeof($changes) > 0 ){
		$changes_stored = true;
	} else{
		$changes_stored = false;
	}
	unset($changes);


	######    Now we're going to do work--we need to display the check for changes button (and check for them), display changes if we got 'em, and apply them if told

	######    we have NOT received _POST info     ####
	if ( empty($_POST) ) {

		// if we have a stored step, we should prompt the user whether they want to continue or start over.
		if ( empty($_POST['wpsc-step']) && $step = get_option('wpstagecoach_importing') ){
			echo 'It looks like you have already started an import, and are currently on step '.$step.'.<br/>'.PHP_EOL;
			echo 'Press the "Continue" button below if you would like to resume from this step.<br/>'.PHP_EOL;

			echo '<form method="POST" id="wpsc-step-form">'.PHP_EOL;
			echo '  <input type="hidden" name="wpsc-step" value='.$step.' />'.PHP_EOL;
			echo '<input type="submit" name="wpsc-import-changes" value="Continue">'.PHP_EOL;
			echo '</form>';
			echo '<br/>';

			echo 'Alternatively, if you really know what you are doing, you can press "Start import over" to start the import process over.<br/>'.PHP_EOL;
			echo 'WP Stagecoach cannot reset your live site to how it was before the first import attempt--you are proceeding at your own risk.<br/>'.PHP_EOL;
			echo '<b>This may leave your site in an unknown state, and reimporting may cause major problems!</b><br/>'.PHP_EOL;
			echo '<form method="POST" name="wpsc-reset-import">'.PHP_EOL;
			echo '<input type="submit" name="wpsc-reset-import" value="Start import over">'.PHP_EOL;

			return;
		}

		$import_step = get_option('wpstagecoach_import_step');
		if( is_numeric($import_step) ){
			$_POST = array(
				'wpsc-change-apply' => true,
				'wpsc-step' => $import_step
			);
			
		} else{
			######    offer to (re)check for changes
			echo '<p><form method="post">';
			if( $changes_stored )
				$check='Recheck';
			else
				$check='Check';
			echo $check.' changes from staging site: ';
			echo '<input type="submit" name="wpsc-check-changes" value="'.$check.' for changes" />';
			echo '</form></p>';




			if( $changes_stored ) {
				######    if we already have changes stored, display those changes
				require_once 'includes/wpsc-import-display.inc.php';
			}

		}


	} else { // $_POST is not empty

		if( WPSC_DEBUG ){
			echo '$_POST (from import() function): <pre>';
			print_r($_POST);
			echo '</pre>';
		}

		// we are checking for changes
		if ( isset($_POST['wpsc-check-changes']) ) {
			$done = false; // this will be set to true within the included file when it is done.
			//  doing check (or re-check (if $changes_stored is set))
			require_once 'includes/wpsc-import-check.inc.php';

			if( $done ){
				require_once 'includes/wpsc-import-display.inc.php';
			}

		} elseif ( isset($_POST['wpsc-options']['stop']) ) {
			// we are stopping in the middle of something
			if( isset($_POST['wpsc-options']['cleanup-import']) ){
				delete_option('wpstagecoach_importing');
				delete_option('wpstagecoach_importing_db');
				delete_option('wpstagecoach_importing_files');

				echo '<p>Okay, we will stop the import process here.</p>'.PHP_EOL;
				require_once 'includes/wpsc-import-display.inc.php';
			}

		// we are going to import changes!
		} elseif ( $changes_stored && isset($_POST['wpsc-import-changes']) ) {

			include_once 'includes/wpsc-import.inc.php';


		// 	we are resetting the step counter, and going to let the user choose what files/DB they want to import.
		} elseif( $_POST['wpsc-reset-import'] ){
			echo '<h3>The import step counter has been reset.<br/>';
			echo 'WP Stagecoach cannot reset your live site to how it was before the first import attempt--you are proceeding at your own risk.</h3>'.PHP_EOL;
			delete_option('wpstagecoach_importing');
			delete_option('wpstagecoach_importing_db');
			delete_option('wpstagecoach_importing_files');
			require_once 'includes/wpsc-import-display.inc.php';

		}
	}


} # end of wpstagecoach_import function


function wpstagecoach_settings( $msg='', $auth_only=false, $display_header=true) {
	  ####   ######   #####   #####     #    #    #   ####    ####
	 #       #          #       #       #    ##   #  #    #  #
	  ####   #####      #       #       #    # #  #  #        ####
	      #  #          #       #       #    #  # #  #  ###       #
	 #    #  #          #       #       #    #   ##  #    #  #    #
	  ####   ######     #       #       #    #    #   ####    ####

	#########################################################################################
	################                  SETTINGS PAGE                 #########################
	#########################################################################################
	require_once 'includes/wpsc-functions.inc.php';





	if(WPSC_DEBUG)
		echo 'calling wpstagecoach_settings<br/>';

	if(WPSC_DEBUG && 0){
		$post_url = WPSTAGECOACH_CONDUCTOR.'/wpsc-auth.php';
		$post_args = array(
			'wpsc-user'	=> $wpsc['username'],
			'wpsc-key'	=> $wpsc['apikey'],
			'wpsc-ver'	=> WPSTAGECOACH_VERSION,
			'site'		=> preg_replace('#https?://#', '',rtrim(site_url(),'/') ),
		);

		echo '<pre>';
		print_r($post_args);
		echo '</pre>';	


		if( WPSC_DEBUG ){
			echo '<br/>'.$post_url.'?'.http_build_query($post_args);
			echo '<br/><br/>';
		}
	}

	if( WPSC_DEBUG ){
		echo '<pre>$_POST from settings(): ';
		print_r($_POST);
		echo '</pre>';
	}



	// optionally display the header (eg, if this is the only page being shown)
	if($display_header == true)
		wpsc_echo_header();
	// optionally display a message if there is one.
	if( !empty($msg) && $auth_only == false )
		echo $msg;


	if( isset( $wpsc['errormsg'] ) ){
		wpsc_display_error( $wpsc['errormsg'] );
		unset($wpsc['errormsg']);
		update_option('wpstagecoach', $wpsc);
	}


	// if we have _POST data, we are going to update the settings
	if( !empty($_POST['wpsc-settings']) ){

		if( empty($_POST['wpsc-username']) || empty($_POST['wpsc-apikey'])){
			$errmsg = 'The User name and API Key fields below may not be blank.';

			wpsc_display_error($errmsg);	
			#wpsc_display_error($errmsg,true);	
			#$display_header=false;
		} else {

			// We do this so if you enable or disable the debug menu, you see it immediately
			if( ( (!empty($_POST['wpsc-debug']) && !isset($wpsc['debug']) ) ||
				(empty($_POST['wpsc-debug']) && isset($wpsc['debug']) && $wpsc['debug'] ==  true ) ) ||

				( (!empty($_POST['wpsc-advanced']) && !isset($wpsc['advanced']) ) ||
				(empty($_POST['wpsc-advanced']) && isset($wpsc['advanced']) && $wpsc['advanced'] ==  true ) )
			) {
				echo 'updating...<br/>'.PHP_EOL;
				wp_redirect(get_admin_url().'admin.php?page=wpstagecoach_settings');
			}

			// have to handle checkboxes different from text info
			$wpstagecoach_settings = array(
				'wpsc-username' => 'username',
				'wpsc-apikey' => 'apikey',
			);
			$wpstagecoach_checkbox_settings = array(
				'wpsc-delete-settings' => 'delete_settings',
				'wpsc-debug' => 'debug',
				'wpsc-advanced' => 'advanced',
				'wpsc-slow' => 'slow',
			);
			foreach ($wpstagecoach_settings as $post_val => $opt_val) {
				if( isset($_POST[$post_val]) && !empty($_POST[$post_val]) )
					$wpsc[$opt_val] = $_POST[$post_val];
				elseif( isset($wpsc[$opt_val]) )
					unset($wpsc[$opt_val]);
			}
			foreach ($wpstagecoach_checkbox_settings as $post_val => $opt_val) {
				if( isset($_POST[$post_val]) && $_POST[$post_val] == 'on' )
					$wpsc[$opt_val] = true;
				elseif( isset($wpsc[$opt_val]) )
					unset($wpsc[$opt_val]);
			}



			delete_transient('wpstagecoach_sanity');

			if( update_option('wpstagecoach', $wpsc) !== false )
				$msg = 'Settings successfully updated!<br/>'.PHP_EOL;

		}
	}




	// maybe there is old setting infomation from an older version?
	if( empty($wpsc['username']) || empty($wpsc['apikey']) ){
		wpsc_check_db_upgrade();
		$wpsc = get_option('wpstagecoach');
	}

	// letting users know they have to enter their login info
	if( empty($wpsc['username']) || empty($wpsc['apikey']) ){
		$errmsg = 'You must enter your authentication information before you can use WP Stagecoach.<br/>'.PHP_EOL;
		$errmsg .= 'Your authentication information may be found on your <a href="https://wpstagecoach.com/your-account" target="_blank">account page on wpstagecoach.com</a>.'.PHP_EOL;
		wpsc_display_error($errmsg);
	}

	// making the submit button have different text
	if ( !empty($wpsc['username']) || !empty($wpsc['apikey']) ){
		$subtext = 'Update';
	} else{
		$subtext = 'Submit';
	}



	
	// if false (normal) we display the form with the data, otherwise, we only check & update authenication info (usually with _POST data, above)
	if( $auth_only != true ){  

		if( isset($wpsc['debug']) && $wpsc['debug'] ) //  if we have the debug option saved, we need to check it in the form below
			$wpsc_debug = 'checked';

		if( isset($wpsc['delete_settings']) && $wpsc['delete_settings'] ) //  if we have the delete_settings option saved, we need to check it in the form below
			$wpsc_delete_settings = 'checked';

		if( isset($wpsc['advanced']) && $wpsc['advanced'] ) //  if we have the delete_settings option saved, we need to check it in the form below
			$wpsc_advanced = 'checked';

		if( isset($wpsc['slow']) && $wpsc['slow'] ) //  if we have the delete_settings option saved, we need to check it in the form below
			$wpsc_slow = 'checked';

		echo '<form method="POST" id="wpsc-settings" >'.PHP_EOL.'<table class="form-table">'.PHP_EOL.'<tr><th valign="top">User name: </th>'.PHP_EOL;
		echo '<td valign="top"><input type="text" size="40" name="wpsc-username" value="'.(isset($wpsc['username']) ? $wpsc['username'] : '') .'" /><br /></td></tr>'.PHP_EOL;
		echo '<tr><th valign="top">API Key: </th>'.PHP_EOL;
		echo '<td valign="top"><input type="text" size="40" name="wpsc-apikey" value="'.(isset($wpsc['apikey']) ? $wpsc['apikey'] : '').'" /><br /></td></tr>'.PHP_EOL;

		echo '<tr><th valign="top">Delete plugin setting when you disable the plugin?<br/><small>(check if you want to remove all WP Stagecoach settings from your database)</small></th>'.PHP_EOL;
		echo '<td valign="top"><input type="checkbox" name="wpsc-delete-settings" '.(isset($wpsc_delete_settings) ? $wpsc_delete_settings : '').'/><br /></td></tr>'.PHP_EOL;

		echo '<tr><th valign="top">Enable Advanced Settings?</th>'.PHP_EOL;
		echo '<td valign="top"><input type="checkbox" name="wpsc-advanced" '.(isset($wpsc_advanced) ? $wpsc_advanced : '').' /><br /></td></tr>'.PHP_EOL;

		echo '<tr><th valign="top">Enable Debug mode?</th>'.PHP_EOL;
		echo '<td valign="top"><input type="checkbox" name="wpsc-debug" '.(isset($wpsc_debug) ? $wpsc_debug : '').' /><br /></td></tr>'.PHP_EOL;

		echo '<tr><th valign="top">Optimize WP Stagecoach for a slower server?</th>'.PHP_EOL;
		echo '<td valign="top"><input type="checkbox" name="wpsc-slow" '.(isset($wpsc_slow) ? $wpsc_slow : '').' /><br /></td></tr>'.PHP_EOL;

		echo '</table>'.PHP_EOL;
		echo '<input type="submit" name="wpsc-settings" value="'. $subtext .'" />'.PHP_EOL;
		echo '</form>'.PHP_EOL;

	} // end if $auth_only

	if( WPSC_DEBUG )
		echo '<br/>this is the wpstagecoach_settings() function saying bye!<br/>'.PHP_EOL;
}


function wpstagecoach_advanced() {
   ##    #####   #    #    ##    #    #   ####   ######  #####
  #  #   #    #  #    #   #  #   ##   #  #    #  #       #    #
 #    #  #    #  #    #  #    #  # #  #  #       #####   #    #
 ######  #    #  #    #  ######  #  # #  #       #       #    #
 #    #  #    #   #  #   #    #  #   ##  #    #  #       #    #
 #    #  #####     ##    #    #  #    #   ####   ######  #####


	require_once 'includes/wpsc-functions.inc.php';



	if( empty($_POST) ){

		echo '<form method="POST" id="wpsc-advanced" >'.PHP_EOL;

		echo 'Clear all retrieved changes from staging site? <input type="checkbox" name="wpsc-clear-retrieved-changes" /><br/>'.PHP_EOL;
		echo '<input type="submit" name="wpsc-settings" value="Submit" />'.PHP_EOL;
		echo '</form>'.PHP_EOL;



	} else {

		if( isset($_POST['wpsc-clear-retrieved-changes']) ){
			delete_option('wpstagecoach_retrieved_changes');
			delete_option('wpstagecoach_importing');
			delete_option('wpstagecoach_importing_files');
			delete_option('wpstagecoach_importing_db');
			echo 'The list of changes retrieved from your staging site has been removed.<br/>'.PHP_EOL;
		}
	}









}



function wpstagecoach_debug() {

	#####   ######  #####   #    #   ####
	#    #  #       #    #  #    #  #    #
	#    #  #####   #####   #    #  #
	#    #  #       #    #  #    #  #  ###
	#    #  #       #    #  #    #  #    #
	#####   ######  #####    ####    ####


	#################################################################################
	################                  DEBUG                 #########################
	#################################################################################
	require_once 'includes/wpsc-functions.inc.php';
	global $wpdb;



echo '<pre>';
echo 'option site_url: <b>';
print_r(get_option('siteurl'));
echo '</b>'.PHP_EOL;
echo 'DB     site_url: <b>';
print_r(array_shift($wpdb->get_row('select option_value from '.$wpdb->prefix.'options where option_name="siteurl"','ARRAY_N')) );
echo '</b>'.PHP_EOL;
echo '</pre>';

// sanity check
#if( rtrim(get_option('siteurl'),'/') != rtrim(array_shift($wpdb->get_row('select option_value from '.$wpdb->prefix.'options where option_name="siteurl"','ARRAY_N')),'/') ){
if( get_option('siteurl') != array_shift($wpdb->get_row('select option_value from '.$wpdb->prefix.'options where option_name="siteurl"','ARRAY_N')) ){
	echo <<< EOERR
<div class="error">
<h3>Your current Site URL is different from what is stored in the database.</h3>
This makes it very difficult for WP Stagecoach to reliably automatically change your database so the site will work on the staging server.<br/>
One common cause of this is that the <a href="http://codex.wordpress.org/Editing_wp-config.php#WordPress_address_.28URL.29" rel="nofollow">WP_SITEURL</a> 
and the <a href="http://codex.wordpress.org/Editing_wp-config.php#Blog_address_.28URL.29" rel="nofollow">WP_HOME</a> are hard-coded into the
<b>wp-config.php</b> file to easily (but preferably temporarily) change the URL of the site.<br/>
For example, you may see this in your wp-config.php file:</br>
EOERR;
	echo '<pre>define(\'WP_HOME\',\''.get_option('siteurl').'\');'.PHP_EOL;
	echo 'define(\'WP_SITEURL\',\''.get_option('siteurl').'\');</pre>';
	echo <<< EOERR
If you do see this, you might consider running a serialized search and replace over your database to permanently change the Site URL.<br/>
<a href="http://interconnectit.com/products/search-and-replace-for-wordpress-databases/" rel="nofollow">interconnect/it</a>
has a wonderful product on github called <a href="https://github.com/interconnectit/Search-Replace-DB" rel="nofollow">Search Replace DB</a>.<br/>
<br/>
Alternatively, you can contact WP Stagecoach for our premium support to identify and resolve this issue.<br/>
Unfortunately, WP Stagecoach cannot proceed further automatically. :-(
</div>
EOERR;
	die;
	}


}

function wpstagecoach_plugin_updater() {
	if( ! $wpsc_transient = get_transient('wpstagecoach_upgrade_check') ){
		$wpsc_transient = true;
		if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) 
			require_once( dirname( __FILE__ ) . '/includes/wpsc-plugin-updater.php' );

	#	$license_key = trim( get_option( 'edd_sample_license_key' ) );
		$license_key = 'ffb53eab95a14f2a4ae597787f892bb9';
		$updater = new EDD_SL_Plugin_Updater( 'https://wpstagecoach.com/', __FILE__, array( 
				'version' 	=> WPSTAGECOACH_VERSION,
				'license' 	=> $license_key,
				'item_name' => 'WP Stagecoach',
				'author' 	=> 'Jonathan Kay'
			)
		);
		set_transient('wpstagecoach_upgrade_check',$wpsc_transient, 3600); // 1 hour

	}
}
add_action( 'admin_init', 'wpstagecoach_plugin_updater' );
add_action( 'wp_loaded', 'wpstagecoach_refresh' );
function wpstagecoach_refresh(){
	if( $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] == preg_replace('#https?://#', '', get_admin_url().'admin.php?page=wpstagecoach') ){
		global $wpsc;
		if( empty($wpsc) )
			$wpsc = get_option('wpstagecoach');
		if( empty($wpsc['username']) || empty($wpsc['apikey']) ){
			wp_redirect(get_admin_url().'admin.php?page=wpstagecoach_settings');
			exit();
		}
	}
}
?>
