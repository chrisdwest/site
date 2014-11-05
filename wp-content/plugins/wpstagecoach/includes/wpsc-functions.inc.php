<?php

if ( ! defined('ABSPATH') ) {
	die('Please do not load this file directly.');
}



global $wpsc;
if( empty($wpsc) ){
	$wpsc = get_option('wpstagecoach');
}



if( isset($wpsc['debug']) && $wpsc['debug'] ){
	define('WPSC_DEBUG', true);
} else {
	define('WPSC_DEBUG', false);
}




function wpsc_encode($array){
	return base64_decode(json_encode($array));
}
function wpsc_decode($string){
	return json_decode(base64_decode($string),true);
}


function wpsc_check_auth($display_output = true){
	/*******************************************************************************
	*                          function check_auth()                               *
	*     checks the __currently stored__ username & api against the WPSC.com DB   *
	*     returns:                                                                 *
	*		auth_response[] array with                                             *
	*			result     == OK/BAD                                               *
	*			info       == (optional) text info about what happened             *
	*			name       == name of user                                         *
	*			site       == type the currently querying site is: live/stage/null *
	*			live-site  == URL of live site                                     *
	*			stage-site == URL of staging site                                  *
	*******************************************************************************/

	global $wpsc;
	if( empty($wpsc) ){
		if( !$wpsc = get_option('wpstagecoach') )
			$error = true;
		echo 'setting $wpsc in check_auth<br/>';
	}

	if( empty($wpsc['username']) || empty($wpsc['apikey']) ){
		$errmsg = 'You must provide a username and an API key.';
		$wpsc['errormsg'] = $errmsg;
		update_option('wpstagecoach', $wpsc);

		wp_redirect(get_admin_url().'admin.php?page=wpstagecoach_settings');
	
	}
	if(0){
		if( $display_output == true )
			wpstagecoach_settings();
		else
			wpstagecoach_settings('', false, false);
		return false;
	}


 #	if( !isset($error) ){

		$post_url = WPSTAGECOACH_CONDUCTOR.'/wpsc-auth.php';
		$post_args = array(
			'wpsc-user'	=> $wpsc['username'],
			'wpsc-key'	=> $wpsc['apikey'],
			'wpsc-ver'	=> WPSTAGECOACH_VERSION,
			'site'		=> preg_replace('#https?://#', '',rtrim(site_url(),'/') ),
		);



		if( WPSC_DEBUG ){
			echo '<br/>'.$post_url.'?'.http_build_query($post_args);
			echo '<br/><br/>';
		}

		$post_result = wp_remote_post($post_url,  array('timeout' => 120, 'body' => $post_args) );


		if( !$auth_info = wpsc_check_post_info('auth', $post_url, $post_args, $post_result, true )) // we got a negative response from the server...
			return false;

		if( $auth_info['result'] != 'OK' && $display_output == true){

			$errmsg = 'The user name and password did not match.  We received the following error: '.print_r($auth_info['info'],true);
			$wpsc['errormsg'] = $errmsg;
			update_option('wpstagecoach', $wpsc);

			wp_redirect(get_admin_url().'admin.php?page=wpstagecoach_settings');

			return false;
		}


		if( isset($auth_info['alert']) && !empty($auth_info['alert']) ){
			echo 'Message from WP Stagecoach:<br/>'.PHP_EOL;
			echo '<div class="wpscwarn">'.$auth_info['alert'].'</div>'.PHP_EOL;
		}

		return $auth_info;
}




function wpsc_display_welcome($auth_info){
	/*********************************************************************************
	*	displays the welcome message
	*	requires
	*		$auth_info (response from wpstagecoach.com auth) for full name
	*	return value:
	*		N/A
	*********************************************************************************/

	echo '<p>Welcome, '.$auth_info['name'].'!</p>';
}

function wpsc_display_main_form($auth_info, $wpsc){
	/*********************************************************************************
	*	displays the main page form, depending on the status of the site
	*		(eg, where there is a staging site created, and whether this is the staging or live site)
	*	requires
	*		$auth_info (response from wpstagecoach.com auth) for staging site info
	*	return value:
	*		N/A
	*********************************************************************************/



	switch ($auth_info['type']) {
		case 'null':

			wpsc_display_create_form('wpstagecoach.com', 'This site currently does not have a staging site set up.', $wpsc);

			return true;
			break;		
		case 'live':
		
			$site_info = explode('.', $auth_info['live-site']);
			echo '<p class="info">This is your <b>live</b> site, which has the corresponding staging site: <a href="http://'.$auth_info['stage-site'].'" target="_blank">'.$auth_info['stage-site'].'</a></p>'.PHP_EOL;
			echo '<p><form method="post">'.PHP_EOL;
			echo 'Delete this staging site: '.PHP_EOL;
			echo '<input type="hidden" name="wpsc-delete-site-name" value="'.$site_info[0].'"/><br />'.PHP_EOL;
			echo '<input type="submit" name="wpsc-delete-site" value="Delete" />'.PHP_EOL;
			echo '</form></p>'.PHP_EOL;
			wpsc_display_sftp_login($auth_info['stage-site'], $auth_info['live-site']);




			return true;
			break;		
		case 'stage':
			echo "<p class='info'>This is your <b>staging</b> site, which has the corresponding live site: <a href=\"http://".$auth_info['live-site']."\">".$auth_info['live-site']."</a></p>\n";
			wpsc_display_sftp_login($auth_info['stage-site'], $auth_info['live-site']);
			return true;
			break;

		default:
			$msg = WPSTAGECOACH_ERRDIV.'<b>We got an unrecognized response back from the server about the status of this site.</b><br/>'.PHP_EOL;
			$msg .= 'We received the following response:<br/>'.PHP_EOL;
			if( is_string($auth_info['type']) )
				$msg .= 'type: '.$auth_info['type'];
			else
				$msg .= '<pre>$auth_info: '.print_r($auth_info, true).'</pre>'.PHP_EOL;
			$msg .= '</div>'.PHP_EOL;
			echo $msg;
			return false;
			break;

	} // end of $auth_info switch statement

}


function wpsc_display_create_form($domain, $message, $wpsc, $again=''){
	/*********************************************************************************
	*	displays the form to create a new staging site
	*	requires
	*		$domain -- domain name for staging site
	*		$message -- optional message to display above the form
	*		$again -- put in the text ' again' if you want to append to the button name
	*	return value:
	*		none
	*********************************************************************************/
	if( strpos($_SERVER['SERVER_NAME'], '/' ) ){  // if we're in a subdir, we want to use that as the staging site name
		$new_site_name_array = array_pop( explode('/',$_SERVER['SERVER_NAME']) );
	} else {
		$new_site_name_array = explode('.',$_SERVER['SERVER_NAME']);
		$new_site_name = array_shift($new_site_name_array);
		if( $new_site_name == 'www' )
			$new_site_name = array_shift($new_site_name_array);
	}


	if( stripos( site_url(), 'https' ) !== false || stripos( get_option('siteurl'), 'https' ) !== false || isset($_SERVER['HTTPS']) ){
		echo WPSTAGECOACH_ERRDIV.'<br/>It looks like your site is using an encrypted (https) connection--WP Stagecoach\'s support';
		echo ' for encrypted websites is still in testing; please let us know if you run into problems with your site!<br/>&nbsp;</div>'.PHP_EOL;
	}

	echo '<p>'.$message.'<br/>'.PHP_EOL;
	echo '<form method="post" action="'.admin_url('admin.php?page=wpstagecoach').'">';
    echo 'Create a staging site: ';
	echo '<input type="hidden" name="wpsc-create-liveurl" value="'.preg_replace('#https?://#', '', site_url() ).'" />'.PHP_EOL;
	echo '<input type="text" size="16" maxlength="16" style="text-align: right" name="wpsc-create-stageurl" value="'.substr($new_site_name,0,16).'" />.'.$domain.'<br />'.PHP_EOL;

	echo 'Disable caching plugins on staging site: <input type="checkbox" name="wpsc-options[disable-caching-plugins]" /><br/>'.PHP_EOL;
	echo '<small>(Please note, if you select this and make changes to the active plugins on your staging site, you will disable the caching plugin on your live site when you import your changes.)</small>'.PHP_EOL;
	echo '<br/>'.PHP_EOL;


	echo '<a class="toggle">Password protect the staging site</a><br/>'.PHP_EOL;
	echo '<div class="more" style="display: none">'.PHP_EOL;
	echo 'Checking this box and entering the items below will make a prompt for your staging site.<br/>'.PHP_EOL;
	echo 'Require a User and Password to view the staging site: <input type="checkbox" name="wpsc-options[password-protect]" /><br/>'.PHP_EOL;
	echo 'User Name: <input type="text" name="wpsc-options[password-protect-user]" /><br/>'.PHP_EOL;
	echo 'Password: <input type="text" name="wpsc-options[password-protect-password]" /><br/>'.PHP_EOL;
	echo '</div>'.PHP_EOL;


	// don't hotlink images
	if( strpos($new_site_name, 'localhost') !== false || (!empty($wpsc['debug']) && $wpsc['debug'] == true) ){
		if( strpos($new_site_name, 'localhost') !== false )
			echo 'It looks like you\'re developing on a local machine, please note if you don\'t select the checkbox below, your images will not be visable to the internet.<br/>'.PHP_EOL;
		echo 'Don\'t hotlink images from staging site to the live site: <input type="checkbox" name="wpsc-options[no-hotlink]" /><br/>'.PHP_EOL;
		echo '<small>(Please note, this may make the staging site creation take a very long time to complete.)</small>'.PHP_EOL;
		echo '<br/>'.PHP_EOL;
	}

	echo '<br/>'.PHP_EOL;
	echo '<input type="submit" name="wpsc-create" value="Ride the Stagecoach'.$again.'!" />';
	echo '</form></p>';
}


function wpsc_echo_header(){
	echo '<div class="wpsc-header">'.PHP_EOL;
	echo '<a href="http://wpstagecoach.com" target="_blank"><img src="'.WPSTAGECOACH_PLUGIN_URL.'assets/wpsc-ticket-sm.png" align="left" /></a>'.PHP_EOL;
	echo '<h2>WP Stagecoach</h2>'.PHP_EOL;
	echo 'v '.WPSTAGECOACH_VERSION.'<br/>'.PHP_EOL;
	echo '</div>'.PHP_EOL;
}


function wpsc_check_post_info($action, $url, $post_args, $post_result, $display_output=true){
	/*********************************************************************************
	*	displays an error if we get a null response from WPSC.com
	*	requires
	*		$action (description of what we tried to do)
	*		$url (where we tried to go)
	*		$post_args -- the post_args that were passed to wp_remote_post
	*		$post_result -- from WordPress's wp_remote_post
	*	return value:
	*		decoded response of body from remote script
	*		false if we got a negative response
	*********************************************************************************/
	global $wpsc;
	$error = false;
	if( empty($wpsc) ){
		$wpsc = get_option('wpstagecoach');
		if(WPSC_DEBUG)
			echo 'setting $wpsc in wpsc_check_post_info(), action: '.$action.'<br/>';
	}


	if( is_wp_error( $post_result ) ) {
		$error_message = $post_result->get_error_message();
		$msg = 'We had a website connection error with url: '.$url.' <br/>'.PHP_EOL;
		$msg .= '<b>'.$error_message.'</b><br/>'.PHP_EOL;
		$error = true;
	}
	if( is_array($post_result) ){
		$response = json_decode(base64_decode(rtrim(strip_tags($post_result['body']))), true);


		if( !is_array($response) ){
			$msg  = '<b>Error!  $post_result did not decode!</b><br/>'.PHP_EOL;
			$msg .= 'Please contact WP Stagecoach support with the following information:<br/><br/>'.PHP_EOL;
			$msg .= 'The Full $post_result[\'body\'] is as follows: <br/><hr/>'.PHP_EOL;
			$temp = strip_tags($post_result['body'] );
			if( !empty( $temp ) )
				$msg .= '<b>'.print_r($post_result['body'],true).'</b><br/><hr/>'.PHP_EOL;
			else
				$msg .= '<b>$post_result[\'body\'] is empty!</b><br/><hr/>'.PHP_EOL;

			$error = true;
		
		} elseif( $post_result['response']['message'] != 'OK' ){
			$msg = 'We got a bad HTTP response from the server url: '.$url.'<br/>'.PHP_EOL;
			$msg .= 'Error code: '.$post_result['response']['code'].'</b><br/>'.PHP_EOL;
			$error = true;
		
		} elseif( empty($response) && $post_result['body'] == 'Go away.'  ){
			$msg = 'Somehow the plugin didn\'t supply enough information to WP Stagecoach to complete the following: '.$action.'<br/>'.PHP_EOL;
			$error = true;
		

		} elseif( $response['result'] != 'OK' ){ // $response['info'] must be a string

			if( is_string($response['info']) && $response['info'] == 'no valid user/key combo' ){
				$msg = 'There was an error authenticating your Username and API Key against what is stored at wpstagecoach.com<br/>'.PHP_EOL;
				$msg .= 'Please check your authentication information and try again.<br/>'.PHP_EOL;
				$msg .= 'Your authentication information may be found on your <a href="https://wpstagecoach.com/your-account" target="_blank">account page on wpstagecoach.com</a>.'.PHP_EOL;

				$wpsc['errormsg'] = $msg;
				update_option('wpstagecoach', $wpsc);
				wp_redirect(get_admin_url().'admin.php?page=wpstagecoach_settings');
				return; 
				#wpstagecoach_settings($msg);
			} elseif( is_string($response['info']) && stripos( $response['info'], 'license has expired') ) {
				$msg = '<b>'.print_r($response['info'],true).'</b><br/>'.PHP_EOL;
	 			#$msg .= 'You can renew your license on your <a href="https://wpstagecoach.com/your-account" target="_blank">account page on wpstagecoach.com</a>.</div>'.PHP_EOL;
				wpsc_display_error($msg, false);

			} elseif( is_string($response['info']) && stripos( $response['info'], 'Could not find username') ) {
				#$msg = WPSTAGECOACH_ERRDIV;
				$msg .= '<b>We could not find your Username at wpstagecoach.com</b><br/>'.PHP_EOL;
				$msg .= 'Please check your authentication information and try again.<br/>'.PHP_EOL;
				$msg .= 'Your authentication information may be found on your <a href="https://wpstagecoach.com/your-account" target="_blank">account page on wpstagecoach.com</a>.'.PHP_EOL;
				#$msg .= '</div>'.PHP_EOL;
				$wpsc['errormsg'] = $msg;
				update_option('wpstagecoach', $wpsc);
				wp_redirect(get_admin_url().'admin.php?page=wpstagecoach_settings');
				return; 
				#wpstagecoach_settings($msg, false, false);

			} elseif( is_string($response['info']) && stripos( $response['info'], 'Your username and API Key do not match') ) {
				#$msg = WPSTAGECOACH_ERRDIV;
				$msg .= '<b>There was an error authenticating your Username and API Key against what is stored at wpstagecoach.com</b><br/>'.PHP_EOL;
				$msg .= 'Please check your authentication information and try again.<br/>'.PHP_EOL;
				$msg .= 'Your authentication information may be found on your <a href="https://wpstagecoach.com/your-account" target="_blank">account page on wpstagecoach.com</a>.'.PHP_EOL;
				#$msg .= '</div>'.PHP_EOL;
				$wpsc['errormsg'] = $msg;
				update_option('wpstagecoach', $wpsc);
				wp_redirect(get_admin_url().'admin.php?page=wpstagecoach_settings');
				return; 

				#wpstagecoach_settings($msg, false, false);

			} elseif( is_string($response['info']) && stripos( $response['info'], 'staging site with this name already exists.') ) {
				$msg  = '<b>Error: A staging site with this name already exists.</b><br/>'.PHP_EOL;
				$msg .= 'Please choose a different staging site name.'.PHP_EOL;
				wpsc_display_create_form('wpstagecoach.com', wpsc_display_error($msg), $wpsc);
			} elseif( $response['result'] == 'OTHER' ) {
				// OTHER -- we will just pass this back to the application & let it deal with it!
				return $response;

			} else {


				$msg = 'We received a negative response from the WP Stagecoach server at: '.$url.'<br/>';
				if( is_array($response) && isset($response['info']) && !empty($response['info']) )
					$msg .= 'Details: <b><pre>'.print_r($response['info'],true).'</pre></b><br/><br/>'.PHP_EOL;
				else
					$msg .= 'Details: <b><pre>'.print_r($response,true).'</pre></b><br/><br/>'.PHP_EOL;
				$response['result'] = 'BAD';
				$response['info'] = $msg;
				if($display_output == true){
					wpsc_display_error($msg);
				}
				return $response;
			}
			return false;

		}
	} else {  // $post_result is NOT an array
		$msg = 'we got a corrupted response from the url: '.$url.' <br/><b>'.print_r($post_result).'</b><br/>'.PHP_EOL;
		$error = true;
	}

	if( $error ){
		if( $display_output == true ){
			echo WPSTAGECOACH_ERRDIV;
			echo $msg;
			echo 'General Information below:<hr/>';
			wpsc_display_post_error($url, $post_args, ' ');
			echo '</div>'.PHP_EOL;
			return false;		
		} else {
			$response['result'] = 'BAD';
			$response['info'] = $msg;
			return $response;
		}
	} else {
		return $response;
	}	
} // end wpsc_check_post_info()

function wpsc_display_post_error($url, $post_args, $msg=''){
	/*********************************************************************************
	*	displays the actual error if we get a bad response from WPSC.com
	*	requires
	*		$url (where we tried to go)
	*		$post_args
	*		$_POST (superglobal)
	*	return value:
	*		none--just outputs error
	*********************************************************************************/

	if( empty($msg) ){
		echo 'Please contact WP Stagecoach with the following information which was sent:<br/>'.PHP_EOL;
 #		echo '(please remove any references to the api key before posting in the forums)<br/>'.PHP_EOL;
	}
	echo '<pre>';
 #	echo date('Y-M-d H:i:s O (e)').PHP_EOL;
	echo 'Date: '.date('Y-M-d H:i:s ', current_time('timestamp',0)).get_option('gmt_offset').':00 ('.get_option('timezone_string').')'.'<br/>'.PHP_EOL;
	echo 'URI: '.$_SERVER['REQUEST_URI'].'<br/>'.PHP_EOL;
	if( !empty($_POST) ){
		echo '$_POST: ';
		$post = $_POST;
		if( isset($post['wpsc-key']) )
			$post['wpsc-key'] = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
		print_r($_POST);
	} 
	echo PHP_EOL;
	echo 'destination URL: '.$url.'<br/>'.PHP_EOL;
	echo '$post_args: ';
	if( isset($post_args['wpsc-key']) )
		$post_args['wpsc-key'] = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
	print_r($post_args);
	echo '</pre>';
} // end wpsc_display_post_error()


function wpsc_display_error( $msg, $display_header=false){
	/*********************************************************************************
	*	displays an error with headers, etc
	*	requires
	*		$msg
	*	return value:
	*		none--just outputs error
	*********************************************************************************/
	if($display_header === true)
		wpsc_echo_header();
	echo WPSTAGECOACH_ERRDIV.$msg.'</div>'.PHP_EOL;
} // end wpsc_display_error()



function wpsc_sanity_check( $type='' ){
	if( ! $wpsc_sanity = get_transient('wpstagecoach_sanity') ){
		if(WPSC_DEBUG)
			echo 'running sanity check!<br/>';
		$wpsc_sanity = array();


		// check that php-curl is installed!!!!
		if( !function_exists('curl_exec') ){
			$errmsg  = 'Uh oh--it looks like your webhost does not have the "Curl" PHP extension.  Currently, WP Stagecoach requires this function to work.<br/>'.PHP_EOL;
			$errmsg .= 'You can ask your webhost if they can install or enable the curl extension (specifically, WP Stagecoach requires curl_exec).'.PHP_EOL;
			wpsc_display_error( $errmsg, true);
			return false;
		}

		//	check multisite
		if( defined(MULTISITE) ){
			wpsc_display_error('Sorry, currently WP Stagecoach does not work on WordPress MultiSite.', true);
			return false;
		}

		// ensure directory for tar/sql files is there
		if( !is_dir( WPSTAGECOACH_TEMP_DIR ) || !is_writable( WPSTAGECOACH_TEMP_DIR ) ){
			if( !is_dir( WPSTAGECOACH_TEMP_DIR ) )
				if( !@mkdir( WPSTAGECOACH_TEMP_DIR ) ){
					wpsc_display_error('We could not create the "/temp" directory which WP Stagecoach requires to work--please check the permissions of the plugin directory: '.dirname(WPSTAGECOACH_TEMP_DIR).'<br/>');
					return false;
				}
			if( !is_writable( WPSTAGECOACH_TEMP_DIR ) ){
				wpsc_display_error('We could not write to the "/temp" directory which WP Stagecoach requires to work--please check the permissions of the temp directory: '.WPSTAGECOACH_TEMP_DIR.'<br/>');
				return false;
			}
		} else
			$wpsc_sanity['temp_dir'] = true;


		//	check free disk space
		$wpsc_sanity['disk_space'] = @disk_free_space('.'); 
		if( !wpsc_display_disk_space_warning( $wpsc_sanity['disk_space'] ) )
			return false;


		//	check writable dirs
		if( $type == 'import' )
			$wpsc_sanity['write'] = wpsc_check_write_permissions(true);
		else
			$wpsc_sanity['write'] = wpsc_check_write_permissions();

		//	check if license is valid
		if( !$wpsc_sanity['auth'] = wpsc_check_auth(false) ){
			// probably need to do a switch() here over $wpsc_sanity['auth'] to check for bad results.

			if( $wpsc_sanity['auth'] !== false ){
				$errmsg = WPSTAGECOACH_ERRDIV.'There was an error authenticating your Username and API Key against what is stored at wpstagecoach.com<br/>'.PHP_EOL;
				$errmsg .= 'Please check your authentication information and try again.<br/>'.PHP_EOL;
				$errmsg .= 'Your authentication information may be found on your <a href="https://wpstagecoach.com/your-account" target="_blank">account page on wpstagecoach.com</a>.'.PHP_EOL;


				$wpsc['errormsg'] = $errmsg;
				update_option('wpstagecoach', $wpsc);
				wp_redirect(get_admin_url().'admin.php?page=wpstagecoach_settings');
				return; 

			}
			return false;
		}


		// warn if jetpack is installed
		$active_plugins = get_option('active_plugins');
		if( in_array('jetpack/jetpack.php', $active_plugins) ){
			$warnmsg = WPSTAGECOACH_WARNDIV.'Notice: You have the jetpack plugin enabled on your site--this plugin will not function as expected on your staging site, and may cause issues.</div>'.PHP_EOL;
			echo $warnmsg;
		}

		//	check if "get_option(siteurl) == siteurl"
		global $wpdb;
		if( rtrim( preg_replace('/http[s]:\/\//', '', get_option('siteurl') ),'/') != rtrim( preg_replace('/http[s]:\/\//', '', array_shift($wpdb->get_row('select option_value from '.$wpdb->prefix.'options where option_name="siteurl"','ARRAY_N')) ),'/') ){
			$errmsg  = '<h3>Your current Site URL is different from what is stored in the database.</h3>'.PHP_EOL;
			$errmsg .= 'This makes it very difficult for WP Stagecoach to reliably automatically change your database so the site will work on the staging server.<br/>'.PHP_EOL;
			$errmsg .= 'One common cause of this is that the <a href="http://codex.wordpress.org/Editing_wp-config.php#WordPress_address_.28URL.29" rel="nofollow">WP_SITEURL</a> '.PHP_EOL;
			$errmsg .= 'and the <a href="http://codex.wordpress.org/Editing_wp-config.php#Blog_address_.28URL.29" rel="nofollow">WP_HOME</a> are hard-coded into the'.PHP_EOL;
			$errmsg .= '<b>wp-config.php</b> file to easily (but preferably temporarily) change the URL of the site.<br/>'.PHP_EOL;
			$errmsg .= 'For example, you may see this in your wp-config.php file:</br>'.PHP_EOL;


			$errmsg .= '<pre>define(\'WP_HOME\',\''.get_option('siteurl').'\');'.PHP_EOL;
			$errmsg .= 'define(\'WP_SITEURL\',\''.get_option('siteurl').'\');</pre>'.PHP_EOL;

			$errmsg .= 'If you do see this, you might consider running a serialized search and replace over your database to permanently change the Site URL.<br/>'.PHP_EOL;
			$errmsg .= '<a href="http://interconnectit.com/products/search-and-replace-for-wordpress-databases/" rel="nofollow">interconnect/it</a>'.PHP_EOL;
			$errmsg .= 'has a wonderful product on github called <a href="https://github.com/interconnectit/Search-Replace-DB" rel="nofollow">Search Replace DB</a>.<br/>'.PHP_EOL;
			$errmsg .= '<br/>'.PHP_EOL;
			$errmsg .= 'Alternatively, you can contact WP Stagecoach for our premium support to identify and resolve this issue.<br/>'.PHP_EOL;
			$errmsg .= '<br/>'.PHP_EOL;
			$errmsg .= 'Unfortunately, WP Stagecoach cannot proceed further automatically. :-(<br/>'.PHP_EOL;
			$errmsg .= '<br/>'.PHP_EOL;

			$errmsg .= 'for debugging, here are the conflicting results:<br/>'.PHP_EOL;
			$errmsg .= '<b>WordPress get_option()</b>: '.rtrim(get_option('siteurl'),'/').'<br/>'.PHP_EOL;
			$errmsg .= '<b>Querying the database</b>: '.rtrim(array_shift($wpdb->get_row('select option_value from '.$wpdb->prefix.'options where option_name="siteurl"','ARRAY_N')),'/').'<br/>'.PHP_EOL;
			#$errmsg .= '</div>'.PHP_EOL; // end of WPSC_ERROR div

			#$wpsc_sanity['siteurl'] = false;
			wpsc_display_error($errmsg ,true);
			return false;
		}



		set_transient('wpstagecoach_sanity',$wpsc_sanity, 3600); // 1 hour
	} else {
		if(WPSC_DEBUG)
			echo 'we already have sanity!<br/>';

		// now going to display any warnings we may have.
		if( $wpsc_sanity['write'] == false ){
			if( $type == 'import' )
				wpsc_check_write_permissions(true);  // $crit == true so we know to bail because we can't go further with filesystem in its current state
			else
				wpsc_check_write_permissions();
		}
	}

	return $wpsc_sanity;
} // end wpsc_sanity_check()


function wpsc_rm_rf($dir){
	$files = array_diff(scandir($dir), array('.','..'));
	foreach ($files as $file) {
		if( is_dir( $dir.'/'.$file ) ) 
			wpsc_rm_rf( $dir.'/'.$file );
		else
			unlink( $dir.'/'.$file );
	}
	rmdir($dir);
} // end wpsc_rm_rf()

function wpsc_check_db_upgrade(){
	/*********************************************************************************
	*	Changes old version of WP Stagecoach settings to new version
	*	requires
	*		none
	*	return value:
	*		none--just changes 
	*********************************************************************************/


	$old_wpsc_settings = array(
		'wpsc_user_name' => 'username',
		'wpsc_api_key' => 'apikey',
	);
	$old_wpsc_import = array(
		'wpsc_change_db_list' => 'wpstagecoach_change_db_list',
		'wpsc_change_new_list' => 'wpstagecoach_change_new_list',
		'wpsc_change_modified_list' => 'wpstagecoach_change_modified_list',
		'wpsc_change_deleted_list' => 'wpstagecoach_change_deleted_list',
		'wpsc_working' => 'wpstagecoach_working',
		'wpsc_working_db_list' => 'wpstagecoach_working_db_list',
		'wpsc_working_new_list' => 'wpstagecoach_working_new_list',
		'wpsc_working_modified_list' => 'wpstagecoach_working_modified_list',
		'wpsc_working_deleted_list' => 'wpstagecoach_working_deleted_list',
	);


	$wpsc = array();
	$old_user = get_option('wpstagecoach_username');
	$old_apikey = get_option('wpstagecoach_apikey');
	if( !empty($old_user) || !empty($old_apikey)){
		if(isset($old_user))
			$wpsc['username'] = $old_user;
		if(isset($old_apikey))
			$wpsc['apikey'] = $old_apikey;
		
		$updated = true;
		update_option('wpstagecoach', $wpsc);
		if(isset($old_user))
			#delete_option('wpsc_user_name');
			delete_option('wpstagecoach_username');
		if(isset($old_apikey))
			#delete_option('wpsc_api_key');
			delete_option('wpstagecoach_apikey');
	}
	
	foreach ($old_wpsc_import as $oldname => $newname) {
		if($temp = get_option( $oldname ) ){
			$updated = true;
			update_option($newname, $temp);
			delete_option($oldname);
		}
	}
	if( isset($updated) ){
		echo '<p>Your WP Stagecoach plugin settings are from an older version of the plugin.<br/>'.PHP_EOL.'We have migrated them, but please look over them and make sure everything looks correct.</p>'.PHP_EOL;
	}
} // end wpsc_check_db_upgrade()

/*****************************************************************************************/
function wpsc_check_write_permissions( $crit=false ){
	/*********************************************************************************
	*	checks that we can write to most of the important places in the system
	*	if we can't, displays a warning
	*	return value:
	*		true / false  depending on success of writes
	*********************************************************************************/
	chdir('../');
	$rootdir=getcwd();
	$testdirs = array(
		WPSTAGECOACH_TEMP_DIR,
		$rootdir,
		$rootdir.'/wp-content/',
		$rootdir.'/wp-admin/',
		$rootdir.'/wp-includes/',
		);
	foreach ($testdirs as $dir) {
		if( ! is_writable( $dir ) ){
			// I have seen occasions when PHP reports a dir as unwritable, but it really is, so I want to double-check.
			if( !@mkdir($dir.'/wpsc-test') ) {
				wpsc_display_write_check_error($dir, $crit);
				chdir('wp-admin'); // put ourselves back in the wp-admin dir, so the rest of the scripts can expect to start there.
				return false;
			} else {
				rmdir($dir.'/wpsc-test');
			}
		}
	}
	chdir('wp-admin'); // put ourselves back in the wp-admin dir, so the rest of the scripts can expect to start there.
	return true;
} // end wpsc_check_write_permissions()


function wpsc_display_write_check_error($dir, $crit=false){
	/*********************************************************************************
	*	Displays the details of why we can't write to a particular directory
	*	requires:
	*		dir -- the dir we can't write to
	*		crit -- if the error is currently critical (eg, when we are about to import stuff!!)
	*	return value:
	*		N/A -- just displays error
	*********************************************************************************/
	$dir_info = stat($dir);
	$dir_perm = fileperms($dir);
	$dir_perm_text = explode('|',wpsc_get_dir_perm_texts_text($dir_perm));


	// I have seen occasions when PHP reports a dir as unwritable, but it really is, so I want to triple-check.
	# getmyuid actually returns the id of the owner of the .php file when running under mod_php, so this doesn't accurately work.
	#	if( ((int)$dir_info[4] == (int)getmyuid()) && ($dir_perm & 0x0080) ){ 
	#		return;
	#	}

	// try to get ID of user running script...
	if( function_exists('posix_getuid'))
		$id = posix_getuid();
	if( !isset($id) && function_exists('shell_exec'))
		$id = rtrim(shell_exec('id -u'));
	if( !isset($id) )
		$id = getmyuid();

	if($crit)
		$errmsg = '<b>WARNING: <br/>';
	else
		$errmsg = '<b>Don\'t panic just yet, but ';
	$errmsg .= 'the file permissions for your Wordpress install do not appear to be fully writeable by the web-server.</b><br/>'.PHP_EOL;
	if( !$crit ){
		$errmsg .= '<a class="toggle">Show/Hide</a>'.PHP_EOL;
		$errmsg .= '<div class="more" style="display: none">'.PHP_EOL;
	}
	$errmsg .= 'WP Stagecoach requires full write access to be able to update your live site.'."<br/>".PHP_EOL;
	if( $crit )
		$errmsg .= '<b>You may not be able to re-import file changes back to your live site!</b>'."<br/>".PHP_EOL;
	else
		$errmsg .= 'We will go ahead and make your staging site, but <b>you may not be able to re-import file changes back to your live site!</b>'."<br/>".PHP_EOL;
	$errmsg .= 'Please contact your webhost for support in enabling the web-server to write to your WordPress install.<br/>'.PHP_EOL;
	$errmsg .= '<br/>'.PHP_EOL;
	$errmsg .= 'More information:<br/>'.PHP_EOL;
	$errmsg .= 'The directory <b>'.$dir.'</b> is not writable by this script (which is running as UID '.$id.').<br/>'.PHP_EOL;
	$errmsg .= 'The directory has the following permissions:<br/>'.PHP_EOL;
	$errmsg .= 'Owner UID: '. $dir_info[4] .' with permissions: '. $dir_perm_text[0] .'<br/>';
	$errmsg .= 'Group GID: '. $dir_info[5] .' with permissions: '. $dir_perm_text[1] .'<br/>';
	$errmsg .= '"World" permissions: '. $dir_perm_text[2] .'<br/>';
	$errmsg .= '(potential problems are in bold)<br/>'.PHP_EOL;
	if( $dir_info[4] != getmyuid() ){
		$errmsg .= '<br/>'.PHP_EOL;
		$errmsg .= 'Typically, the UID the PHP is running as ('.getmyuid().') should be the same as the directory UID ('.$dir_info[4].').<br/>'.PHP_EOL;
		$errmsg .= 'Because this PHP script is running as a different user than the owner of the directory, it probably does not have permission to write to the directory.<br/>'.PHP_EOL;
		$errmsg .= 'You will need to ask your hosting provider what you can do to remedy this situation (you can provide them with this text).<br/>'.PHP_EOL;
	}
	if( !strpos($dir_perm_text[0], ', write, ') ){
		$errmsg .= '<br/>'.PHP_EOL;
		$errmsg .= 'Typically the Owner should have permissions of "Read, Write, Execute", your is "'.$dir_perm_text[0].'".<br/>'.PHP_EOL;
		$errmsg .= 'You will need to add owner write permissions for this directory--please contact your hosting provider for help if you need help.<br/>'.PHP_EOL;
	}
	if( !$crit )
		$errmsg .= '</div>'.PHP_EOL; // end of hiding div
	
	if($crit){ // if it's a critical failure, we need to stop
		wpsc_display_error($errmsg);
		return false;
	} else { // else, we can just put up a warning.
		if( !isset($_POST['wpsc-create']) ){
			echo WPSTAGECOACH_WARNDIV.$errmsg.'</div>'.PHP_EOL;
	
		}
		
	}

} // end wpsc_display_write_check_error()



function wpsc_get_dir_perm_texts_text($perms){
	/*
	* taken from php.net/manual/en/function.fileperms.php
	* returns the human readable permissions (eg. drwxr-x-r-x) of a file
	*/

	// Owner
	$info = (($perms & 0x0100) ? 'Read' : '<b>NO read</b>');
	$info .= ', ';
	$info .= (($perms & 0x0080) ? 'Write' : '<b>NO write</b>');
	$info .= ', ';
	$info .= (($perms & 0x0040) ?
	            (($perms & 0x0800) ? '<b>Setuid</b>|' : 'Execute|' ) :
	            (($perms & 0x0800) ? '<b>Setuid (without execute)</b>|' : '<b>NO execute</b>|'));

	// Group
	$info .= (($perms & 0x0020) ? 'Read' : '<b>NO read</b>');
	$info .= ', ';
	$info .= (($perms & 0x0010) ? 'Write' : 'NO write');
	$info .= ', ';
	$info .= (($perms & 0x0008) ?
	            (($perms & 0x0400) ? '<b>Setgid</b>|' : 'Execute|' ) :
	            (($perms & 0x0400) ? '<b>Setgid (without execute)</b>|' : '<b>NO execute</b>|'));

	// World
	$info .= (($perms & 0x0004) ? 'Read' : 'NO read');
	$info .= ', ';
	$info .= (($perms & 0x0002) ? 'Write' : 'NO write');
	$info .= ', ';
	$info .= (($perms & 0x0001) ?
	            (($perms & 0x0200) ? '<b>Sticky</b>|' : 'Execute|' ) :
	            (($perms & 0x0200) ? '<b>Sticky (without execute)</b>|' : '<b>NO execute</b>|'));

	return $info;
} // end wpsc_get_dir_perm_texts_text()
/*****************************************************************************************/

function wpsc_display_disk_space_warning($df){
	/*	displays a warning if there is < 300MB of free disk space  */
	if( $df < (300*1024^2) ){
		$df = wpsc_get_disk_space($df);  // returns array with [0] => disk amount [1] => suffix (eg 4tb)
		$errmsg  = '<b>Warning: You appear to only have '.(int)$df[0].$df[1].' of free disk space</b>--this may not be enough to safely run WP Stagecoach<br/>'.PHP_EOL;
		$errmsg .= 'Please clear up some space before you attempt to run WP Stagecoach or you may run into problems.'.PHP_EOL;
		wpsc_display_error($errmsg);
		return false;
	} else {
		return true;
	}
} // end wpsc_display_disk_space_warning()

/* checks the available disk space & return an array with
[0] number of 
[1] suffix (eg, kb, mb, tb)
*/
function wpsc_get_disk_space( $df ){
	if( !$df ){
		// PHP has a bug with disk_free_space() for large fileststems on a 32-bit machine
		return array(4,'tb');
	}
	if( ($df/1099511627776) > 1 ){
		$suff = 'tb';
		$df = ($df/1099511627776);
	} elseif( ($df/1073741824) > 1 ){
		$suff = 'gb';
		$df = ($df/1073741824);

	} elseif( ($df/1048576) > 1 ){
		$suff = 'mb';
		$df = ($df/1048576);

	}
	return array($df,$suff);	

} // end wpsc_get_disk_space()


function wpsc_recursive_unserialize_replace( $live_site, $stage_site, $live_path, $stage_path, $data = '', $serialised = false ) {
	/*********************************************************************************
	*	unserailizes any serialized arrays, changes any mention of the live site URL/path to the staging site URL/path
	*	requires
	*		live_site -- the live site URL
	*		stage_site -- the staging site's URL
	*		live_path -- the live site directory path
	*		stage_path -- the staging site's directory path
	*		data  -- the data to be unserialized
	*		serialized  -- set to true if we just unserilized a string so we know to return the changed data serialized.
	*	return value:
	*		the fully serialized data
	*********************************************************************************/

	if ( is_string( $data ) && ( $unserialized = @unserialize( $data ) ) !== false ) {
		$data = wpsc_recursive_unserialize_replace( $live_site, $stage_site, $live_path, $stage_path, $unserialized, true );
	} elseif ( is_array( $data ) ) {
		$_tmp = array( );
		foreach ( $data as $key => $value ) {
			$_tmp[ $key ] = wpsc_recursive_unserialize_replace( $live_site, $stage_site, $live_path, $stage_path, $value, false );
		}
		$data = $_tmp;
		unset( $_tmp );
	} elseif ( is_object( $data )  ) {
		$obj_vars = get_object_vars( $data );
		foreach ( $obj_vars as $key => $value ) {
			$data->$key = wpsc_recursive_unserialize_replace( $live_site, $stage_site, $live_path, $stage_path, $value, false );
		}
	} else {
		if ( is_string( $data ) ){


				$data = str_replace( $live_path, $stage_path, $data );	// PATH

// removed to make work with PHP v5.2 
//				$data = preg_replace_callback('#(https?://)?'.$live_site.'#i',
//					function($match) use ($stage_site){return empty($match[1]) ? $stage_site :'http://'.$stage_site;},
//					$data
//				); 
				$data = preg_replace('#https://'.$live_site.'#', 'http://'.$stage_site, $data);
				$data = str_replace( $live_site, $stage_site, $data );

		}
	}
	if ( $serialised )
		return serialize( $data );
	return $data;
} // end wpsc_recursive_unserialize_replace()



function wpsc_build_tar_list_rec( $dir, $list ){
	/*********************************************************************************
	*	Scan the filesystem and make a list of all files on it.
	*	requires
	*		dir -- current directory we are scanning in
	*		list -- the list we are building up
	*	return value:
	*		list -- the full list we have found so far
	*********************************************************************************/
	global $special_dirs;
	global $BINARY_FILE_LIST;
	$dirlist = scandir( $dir );
	unset( $dirlist[ array_search('.', $dirlist) ]);
	unset( $dirlist[ array_search('..', $dirlist) ]);
	foreach( $dirlist as $entry ){
		if ( $dir.'/'.$entry != rtrim(WPSTAGECOACH_REL_TEMP_DIR,'/') ) {

			if( empty($BINARY_FILE_LIST) ){

					if( is_dir( $dir.'/'.$entry ) &&
						!is_link( $dir.'/'.$entry ) && // don't want to actually copy the contents of symlinked dirs
						!is_file( $dir.'/'.$entry.'/wp-config.php' )  // don't need to archive other sub-WP directories
					){
						$list = wpsc_build_tar_list_rec( $dir.'/'.$entry, $list );
					} elseif ( is_file( $dir.'/'.$entry ) ||
						(is_link( $dir.'/'.$entry ) && !is_dir( $dir.'/'.$entry )) &&
						!($dir == 'cache' && strpos($entry, 'timthumb') ) // don't want to copy timthumb caches
					){
						$fsize = filesize($dir.'/'.$entry);
						if( $fsize > WPSTAGECOACH_LARGE_FILE ){ // 10MB
							$list['largefiles'][] = $dir.'/'.$entry;
						} else{
							$list[] = $dir.'/'.$entry;
						}
						$list['totalsize'] += $fsize;
					} elseif( is_file( $dir.'/'.$entry.'/wp-config.php' ) ) {
						echo 'I am not backing up the WordPress install in "'.$dir.'/'.$entry.'"<br/>'.PHP_EOL;
					} elseif( is_dir( $dir.'/'.$entry ) &&
						is_link( $dir.'/'.$entry )
					) {
						echo 'I am not backing up this symlink to a dir: '.$dir.'/'.$entry.'<br/>'.PHP_EOL;
					} else {
						echo 'I have no clue what this is: '.$dir.'/'.$entry.'<br/>'.PHP_EOL;
					}				

			} else {
				if( strpos($dir, 'wp-includes') || !preg_match('/'.$BINARY_FILE_LIST.'/', $entry) ){
					if( isset($special_dirs[$dir][$entry]) && $special_dirs[$dir][$entry] == true){
					} elseif( is_dir( $dir.'/'.$entry ) &&
						!is_link( $dir.'/'.$entry ) && // don't want to actually copy the contents of symlinked dirs
						!is_file( $dir.'/'.$entry.'/wp-config.php' ) &&  // don't need to archive other sub-WP directories
						$dir.'/'.$entry != './wp-content/uploads' // not copying uploads for now
					){
						$list = wpsc_build_tar_list_rec( $dir.'/'.$entry, $list );
					} elseif ( is_file( $dir.'/'.$entry ) ||
						(is_link( $dir.'/'.$entry ) && !is_dir( $dir.'/'.$entry )) &&
						!($dir == 'cache' && strpos($entry, 'timthumb') ) // don't want to copy timthumb caches
					){
						$fsize = filesize($dir.'/'.$entry);
						if( $fsize > 10485760 ){ // 10MB
							$list['largefiles'][] = $dir.'/'.$entry;
						} else{
							$list[] = $dir.'/'.$entry;
						}
						$list['totalsize'] += $fsize;
					} elseif( $dir.'/'.$entry == './wp-content/uploads' ) {
						echo 'I am not backing up '.$dir.'/'.$entry.'<br/>'.PHP_EOL;
					} elseif( is_file( $dir.'/'.$entry.'/wp-config.php' ) ) {
						echo 'I am not backing up the WordPress install in "'.$dir.'/'.$entry.'"<br/>'.PHP_EOL;
					} elseif( is_dir( $dir.'/'.$entry ) &&
						is_link( $dir.'/'.$entry )
					) {
						echo 'I am not backing up this symlink to a dir: '.$dir.'/'.$entry.'<br/>'.PHP_EOL;
					} else {
						echo 'I have no clue what this is: '.$dir.'/'.$entry.'<br/>'.PHP_EOL;
					}
				}

			} // end of if empty(BINARY_FILE_LIST)
		}
	}
	return $list;
} // end of wpsc_build_tar_list_rec()





function wpsc_append_special_dirs_to_list( $list ){
	/*********************************************************************************
	*	Adds a list of all files from directories which require all files to be transferred to staging site
	*	requires
	*		$list -- list of files to create tar file from
	*	return value:
	*		$list -- extended list of files to create tar file from
	*********************************************************************************/
	global $special_dirs;
	$themedir = './wp-content/themes/';
	$themes = glob($themedir.'*', GLOB_ONLYDIR);


	// need to check for other dirs in wpstagecoach_debug array!!

	foreach ($themes as $theme) {
		if( is_file($themedir.$theme.'/screenshot.png'))
			$list[] = $themedir.$theme.'/screenshot.png';
	}



	foreach ($special_dirs as $basedir => $array) {
		foreach ($array as $dir => $value) {
			if( file_exists($basedir.'/'.$dir) )
				$list = wpsc_build_tar_list_rec_no_exclusions($basedir.'/'.$dir, $list);
		}
	}


	return $list;
} // end wpsc_append_special_dirs_to_list()

function wpsc_build_tar_list_rec_no_exclusions( $dir, $list ){
	/*********************************************************************************
	*	Does stuff
	*	requires
	*		none
	*	return value:
	*		none
	*********************************************************************************/

	$dirlist = scandir( $dir );
	unset( $dirlist[ array_search('.', $dirlist) ]);
	unset( $dirlist[ array_search('..', $dirlist) ]);
	foreach( $dirlist as $entry ){
		switch (filetype( $dir.'/'.$entry )) {
		 	case 'file':
		 		$list[] = $dir.'/'.$entry;
		 		break;
		 	case 'dir':
		 		$list = wpsc_build_tar_list_rec_no_exclusions( $dir.'/'.$entry, $list );
		 		break;
		 	case 'link':
		 		if( !is_dir( $dir.'/'.$entry ) ) // still don't want to back up symlink dirs
		 			$list[] = $dir.'/'.$entry;
		 		break;
		 	default:
		 		echo "<p>I have no clue what this is: ".$entry."</p>\n";
		 		break;
		}
	}
	return $list;
} // end wpsc_build_tar_list_rec_no_exclusions()




function wpsc_display_feedback_form($location, $site_info, $message='', $step=false ){
	/*********************************************************************************
	*	requires:
	*		$location:	create / import
	*			(displays different wording as well as different form creation.)
	*		$site_info:	array which contains:
	*			wpsc-stage -- full staging site name 
	*			wpsc-live -- live site name
	*			wpsc-user
	*			wpsc-key
	*	return value:
	*		string containing the feedback form
	*********************************************************************************/



	$output = '<div class="wpsc-feedback">';
	$output .= '<div class="wpsc-feedback-message">';
	$output .= '<h3>WP Stagecoach User Feedback</h3>';

	if( strstr($location, 'create')  ){
		if ( empty($message) )
			$output .= 'Did the staging site creation work?'.PHP_EOL;
	} elseif( strstr($location, 'import')  ){
		$goto = 'action="'.admin_url('admin.php?page=wpstagecoach').'"';
		if ( empty($message) )
			$output .= 'Did the import work correctly?'.PHP_EOL;
	}
	if( ! empty($message) ) {
		$output .= $message.PHP_EOL;
	}
	$output .= '</div>';	
	$output .= '<form method="POST" id="wpsc-happiness-form" '.(isset($goto)? $goto:'').'>'.PHP_EOL;
	if( strstr($location, 'error') !== false ){
		if( empty($message) )
			$output .= '  Can you please describe the problem you ran into?<br/>'.PHP_EOL;
	} else {
		$output .= '  <input type="radio" name="worked" value="yes" />Yes, it was a smooth ride!<br/>'.PHP_EOL;
		$output .= '  <input type="radio" name="worked" value="no" />No, I ran into problems.<br/>'.PHP_EOL;
		$output .= '  Comments?<br/>'.PHP_EOL;
	}
	$output .= '  <textarea cols=60 rows=3 name="comments" />';
	if( $location == 'error' )
		$output .= 'debug info: The SQL file is '.(int)(filesize(WPSTAGECOACH_DB_FILE)/1048576).'mb in size, and the tar file is '.(int)(filesize(WPSTAGECOACH_TAR_FILE)/1048576).'mb in size.';
	$output .= '</textarea><br/>'.PHP_EOL;

	$output .= '  Can we contact you for follow-up information if necessary?<br/>'.PHP_EOL;
	$output .= '  <input type="radio" name="contact" value="yes" />Yes<br/>'.PHP_EOL;
	$output .= '  <input type="radio" name="contact" value="no" />No<br/>'.PHP_EOL;
	$output .= '  <input type="submit" name="wpsc-feedback" value="Submit feedback" />'.PHP_EOL;
	$post_fields = array(
		'wpsc-type' => $location,
		'wpsc-stage-site' => $site_info['wpsc-stage'],
		'wpsc-live-site' => $site_info['wpsc-live'],
		'wpsc-user' => $site_info['wpsc-user'],
		'wpsc-key' => $site_info['wpsc-key'],
		'wpsc-dest' => $site_info['wpsc-dest'],
		'wpsc-wpver' => get_bloginfo('version'),
		'wpsc-serverinfo' => base64_encode(json_encode( $_SERVER )),
		'wpsc-phpinfo' => base64_encode(json_encode( ini_get_all() )),
		'wpsc-plugins' => base64_encode(json_encode(get_option('active_plugins'))),
		'wpsc-ver' => WPSTAGECOACH_VERSION,
	);
	if( function_exists('phpversion') )
		$post_fields['wpsc-phpver'] = phpversion();
	if( stripos($_SERVER['SERVER_SOFTWARE'], 'apache') !== false  && function_exists('apache_get_version'))
		$post_fields['wpsc-apache-ver'] = apache_get_version();

	foreach ($post_fields as $key => $value) {
		$output .= '  <input type="hidden" name="'.$key.'" value="'.$value.'"/>'.PHP_EOL;
	}
	$output .= '</form></div>'.PHP_EOL;

	return $output;
}

function wpsc_send_feedback(){
	/*********************************************************************************
	*	requires:
	*		N/A -- $_POST has everything we need
	*	return value:
	*		string containing the feedback form
	*********************************************************************************/
	// include import.log
	if( is_file(WPSTAGECOACH_TEMP_DIR.'import.log')){
		$_POST['importlog'] = file_get_contents(WPSTAGECOACH_TEMP_DIR.'import.log');
	}
	$post_url = WPSTAGECOACH_CONDUCTOR.'/wpsc-feedback.php';
	$post_result = wp_remote_post($post_url, array('timeout' => 120, 'body' => $_POST) );
	$result = wpsc_check_post_info('feedback', $post_url, $_POST, $post_result) ; // check response from the server

	if( $result['result'] != 'OK' ){
		wpsc_display_error( print_r($result['info'],true) );
		return false;
	} else {
		echo '<div class="wpsc-thankyou">Thank you for your feedback--it is invaluable to us!</div>';
	}


}




function wpsc_display_sftp_login($stage_site, $live_site){
	/*********************************************************************************
	*	displays the SFTP login information, including the iframe that comes from https://wpstagecoach.com
	*	requires
	*		stage_site
	*		live_site
	*	return value:
	*		none
	*********************************************************************************/
	global $wpsc;

	$url = WPSTAGECOACH_CONDUCTOR.'/wpsc-user-pass.php?wpsc-user='.$wpsc['username'].'&wpsc-key='.$wpsc['apikey'].'&wpsc-ver='.WPSTAGECOACH_VERSION.'&wpsc-live-site='.$live_site;
	$msg  = 'Your <b>SFTP/FTP login</b> information for the staging site is:<br />'.PHP_EOL;
	$msg .= '<iframe style="margin-top:-9px; margin-bottom:-9px;" height=81px src="'.$url.'">'.PHP_EOL;
	$msg .= 'Please log into your WPStagecoach.com account for your login details.';
	$msg .= '</iframe>'.PHP_EOL;
	$msg .= '<br/>Your WordPress credentials are the same as on your live site.<br/>'.PHP_EOL;
	$msg .= '<br/>';

	echo $msg;


}
?>