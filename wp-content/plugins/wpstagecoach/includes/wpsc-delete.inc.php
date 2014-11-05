<?php

if ( ! defined('ABSPATH') ) {
	die('Please do not load this file directly.');
}


define('WPSTAGECOACH_DOMAIN',		'.wpstagecoach.com');




if( !isset($wpsc_sanity['auth']) || empty($wpsc_sanity['auth']['live-site']) || empty($wpsc_sanity['auth']['stage-site']) ){
	$msg = 'You tried to delete your staging site, but it looks like this live site doesn\'t currently have a staging site set up.<br/>'.PHP_EOL;
	$msg .= 'You shouldn\'t have been able to get here--please contact WP Stagecoach with details on exactly what you clicked<br/>'.PHP_EOL;
	wpsc_display_error($msg, false);
	wpsc_display_create_form('wpstagecoach.com', 'Would you like to create a new staging site?', $wpsc, ' again');
	return;
}

define('WPSTAGECOACH_LIVE_SITE',	$wpsc_sanity['auth']['live-site']);
define('WPSTAGECOACH_STAGE_SITE',	$wpsc_sanity['auth']['stage-site']);
define('WPSTAGECOACH_SERVER',		$wpsc_sanity['auth']['server']);


$post_url = WPSTAGECOACH_CONDUCTOR.'/wpsc-delete-site.php';
$post_args = array(
	'wpsc-user'			=> $wpsc['username'],
	'wpsc-key'			=> $wpsc['apikey'],
	'wpsc-ver'			=> WPSTAGECOACH_VERSION,
	'wpsc-live-site'	=> WPSTAGECOACH_LIVE_SITE,
	'wpsc-stage-site'	=> WPSTAGECOACH_STAGE_SITE,
	'wpsc-dest'			=> WPSTAGECOACH_SERVER,
);

if( WPSC_DEBUG ){
	echo '<br/>'.$post_url.'?'.http_build_query($post_args);
	echo '<br/><br/>';
}

$post_result = wp_remote_post($post_url, array('timeout' => 300, 'body' => $post_args) );
$result = wpsc_check_post_info('create-site', $post_url, $post_args, $post_result) ; // check response from the server

if($result['result'] == 'OK'){


	// get rid of the old db backup file being listed
	if( isset($wpsc['db_backup_file'])){
		echo 'Please remember you have a database dump file in '.$wpsc['db_backup_file'].'.<br/>'.PHP_EOL;
		echo 'You may wish to delete it.<br/>'.PHP_EOL;
		unset($wpsc['db_backup_file']);
		update_option('wpstagecoach', $wpsc);
	} 




	delete_option('wpstagecoach_retrieved_changes');
	delete_option('wpstagecoach_old_retrieved_changes');
	delete_option('wpstagecoach_importing');
	delete_option('wpstagecoach_importing_db');
	delete_option('wpstagecoach_importing_files');

	// delete the wpstagecoach option for the staging site
	if( isset($wpsc['staging-site']) ){

		unset($wpsc['staging-site']);
		if( !update_option('wpstagecoach', $wpsc) ){
			$msg = 'Somehow, we could not update the WordPress option for wpstagecoach (settings $wpsc).  This shouldn\'t happen.<br/>'.PHP_EOL;
			$msg .= 'You might consider checking your database\'s consistency.<br/>'.PHP_EOL;
			$msg .= 'Unfortunately debugging this further is beyond the scope of this plugin.<br/>'.PHP_EOL;
			wpsc_display_error($msg, false);
		}
	}

	// clean up log & db files
	$temp_files = scandir(WPSTAGECOACH_TEMP_DIR);
	foreach ($temp_files as $file) {
		if( (strpos($file, 'mport.log') || strpos($file, '.gz') ) && !($file == '.' || $file == '..') ){
			unlink( WPSTAGECOACH_TEMP_DIR.$file);
		}
	}


	// do some clean up
	delete_transient('wpstagecoach_sanity');

	echo "Your staging site ".WPSTAGECOACH_STAGE_SITE." has been successfully deleted.<br/>\n";
	echo "Thank you for using WP Stagecoach!<br/>".PHP_EOL;


	wpsc_display_create_form('wpstagecoach.com', 'If you would like, you can re-create your staging site.', $wpsc, ' again');

} else {
	$msg = "There was a problem deleting your staging site ".WPSTAGECOACH_STAGE_SITE.".<br/>\n";
	$msg .= "Please contact WP Stagecoach support with the above error information:<pre>";
	wpsc_display_error($msg, false);
}


?>