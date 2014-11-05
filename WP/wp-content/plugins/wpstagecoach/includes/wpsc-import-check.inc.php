<?php

if ( ! defined('ABSPATH') ) {
	die('Please do not load this file directly.');
}


set_time_limit(0);
ini_set("max_execution_time", "0");
ini_set('memory_limit', '-1');


/*******************************************************************************************
*                                                                                          *
*                             Beginning of checking step form                              *
*                                                                                          *
*******************************************************************************************/
$wpscpost_fields = array(
	'wpsc-check-changes' => $_POST['wpsc-check-changes'],
);
if( !empty($_POST['wpsc-options']) )
	$wpscpost_wpsc_options = $_POST['wpsc-options'];


if( !empty($_POST['wpsc-use-file']) ) // if we are going to use the file, we want to skip ahead to step 2 directly!
	$_POST['wpsc-step'] = 2;

$USER=array_shift(explode('.',WPSTAGECOACH_STAGE_SITE));


$normal=true;

################################################################################################################################

		   #
		  ##
		 # #
		   #
		   #
		   #
		 #####

################################################################################################################################

if( !isset($_POST['wpsc-step']) || empty($_POST['wpsc-step']) ){
	echo 'Step 1 -- talking to the conductor and having the file containing all changes stored.<br/><br/>';

	$post_url = WPSTAGECOACH_CONDUCTOR.'/wpsc-make-changes-file.php';
	$post_args = array(
		'wpsc-user'			=> $wpsc['username'],
		'wpsc-key'			=> $wpsc['apikey'],
		'wpsc-ver'			=> WPSTAGECOACH_VERSION,
		'wpsc-live-site'	=> WPSTAGECOACH_LIVE_SITE,
		'wpsc-stage-site'	=> WPSTAGECOACH_STAGE_SITE,
		'wpsc-live-path'	=> rtrim(ABSPATH, '/'),
		'wpsc-dest'			=> WPSTAGECOACH_SERVER,
	);
	if( !empty($_POST['wpsc-options']) )
		$post_args['wpsc-options'] = $_POST['wpsc-options'];



	if(WPSC_DEBUG)
		echo $post_url.'?'.http_build_query($post_args);

	$post_result = wp_remote_post($post_url, array('timeout' => 300, 'body' => $post_args) );
	$result = wpsc_check_post_info('make-changes-file', $post_url, $post_args, $post_result) ; // check response from the server


	if($result['result'] == 'OK'){
		if(WPSC_DEBUG){
			echo '<pre>';
			print_r($result);
			echo '</pre>';
		}

		if(isset( $result['info'] ) &&  $result['info'] == 'EMPTY'){
			echo 'There are no new changes on your staging site.<br/>'.PHP_EOL;


			return;
		} else {
			echo 'There are changes found on your staging site.<br/>'.PHP_EOL;
			echo 'We will go download them in the next step, hang on!<br/>'.PHP_EOL;
			$nextstep = 2;
		}

	} elseif($result['result'] == 'OTHER') {
		$normal = false;
		$msg = "There was a complication checking for changes on your staging site ".WPSTAGECOACH_STAGE_SITE.".<br/>\n";
		$msg .= print_r($result['info'], true);
		echo '<div class="wpscwarn">'.$msg.'</div>'.PHP_EOL;



		echo '<form method="POST" id="wpsc-step-form">'.PHP_EOL;
		echo '<input type="submit" name="wpsc-options[use-file]" value="Use File">'.PHP_EOL;	
		echo '<input type="submit" name="wpsc-options[check-anew]" value="Check again">'.PHP_EOL;


	} else {
		$normal = false;
		$msg = "There was a problem checking for changes on your staging site ".WPSTAGECOACH_STAGE_SITE.".<br/>\n";
		$msg .= "Please contact WP Stagecoach support with this error information:<pre>";
		$msg .= print_r($result['info'], true);
		wpsc_display_error($msg, false);



		echo 'It looks like we ran into a problem while checking for changes--would you like to try again?<br/>'.PHP_EOL;
		echo '<form method="POST" id="wpsc-step-form">'.PHP_EOL;
		echo '<input type="submit" name="wpsc-options[retry]" value="Yes">'.PHP_EOL;	
		echo '<input type="submit" name="wpsc-options[stop]" value="No">'.PHP_EOL;

	}







} // end of step 1


################################################################################################################################

         #####
        #     #
              #
         #####
        #
        #
        #######

################################################################################################################################

if( isset($_POST['wpsc-step']) && $_POST['wpsc-step'] == 2 ){
	echo 'Step '.$_POST['wpsc-step'].' -- Downloading changes file.<br/><br/>';


	$post_url = 'https://'.WPSTAGECOACH_SERVER.'/wpsc-app-download-changes-file.php';
	$post_args = array(
		'wpsc-user'			=> $wpsc['username'],
		'wpsc-key'			=> $wpsc['apikey'],
		'wpsc-ver'			=> WPSTAGECOACH_VERSION,
		'wpsc-live-site'	=> WPSTAGECOACH_LIVE_SITE,
		'wpsc-stage-site'	=> WPSTAGECOACH_STAGE_SITE,
		'wpsc-live-path'	=> rtrim(ABSPATH, '/'),
		'wpsc-dest'			=> WPSTAGECOACH_SERVER,
		'wpsc-file'			=> $USER.'-changes',
	);
	if( !empty($_POST['wpsc-options']) )
		$post_args['wpsc-options'] = $_POST['wpsc-options'];





	foreach (array('', '.md5') as $ext) {
		$post_args['wpsc-file'] .= $ext;
		$dest_file = WPSTAGECOACH_TEMP_DIR.$post_args['wpsc-file'];

		
		echo 'Downloading the '.$ext.' file currently...';
		echo str_pad('',4096)."\n";
		ob_flush();
		flush();

		if( !$changes_file = fopen ($dest_file, 'w') ){
			wpsc_display_error('Error: We were not able to open the '.$ext.' file "'.$dest_file.'" for writing!'.PHP_EOL);
			return;
		}
		$ch=curl_init($post_url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_args));
		curl_setopt($ch, CURLOPT_TIMEOUT, 600);
		curl_setopt($ch, CURLOPT_FILE, $changes_file); // write curl response to file
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		$result = curl_exec($ch); // get curl response
		if( curl_errno($ch) ){
			wpsc_display_error('Error: We received an error from curl while downloading the '.$ext.' file: '.curl_error($ch).PHP_EOL);
			return;
		}
		curl_close($ch);
		fclose($changes_file);

		if( !$result ){ // bad result
			wpsc_display_error('Error: We were not able to download the '.$ext.' file!'.PHP_EOL);
			return;
		}

		if( !is_file($dest_file) ){
			wpsc_display_error('Error: We could not find the file the '.$ext.' file "'.$dest_file.'" we <b>just</b> wrote!<br/>Might something be wrong with your webhost?'.PHP_EOL);
			return;
		}

		sleep(.5); // give the slower webhosts a chance to flush their writes out!
		if($ext == '')  // might as well get the checksum while we're here!
			$md5sum = md5_file($dest_file);

		echo 'done!<br/>'.PHP_EOL;
		echo str_pad('',4096)."\n";
		ob_flush();
		flush();
	} // end foreach over the file & .md5 file


	// make sure the file has the correct .md5 sum -- compare it to the current dest_file
	if( $md5sum != file_get_contents($dest_file) ){
		$normal=false;
		$nextstep = 2;

		$msg = 'Error: the changes file we just downloaded appears to have gotten corrupted (the checksums do not match)!<br/>'.PHP_EOL;
		$msg .= 'Would you like to try to download it again?<br/>'.PHP_EOL;
		wpsc_display_error($msg);


		echo '<form method="POST" id="wpsc-step-form">'.PHP_EOL;
		echo '<input type="submit" name="wpsc-options[retry]" value="Yes">'.PHP_EOL;	
		echo '<input type="submit" name="wpsc-options[stop]" value="No">'.PHP_EOL;

	} else {
		echo 'The changes file appears to have downloaded without a problem!<br/>'.PHP_EOL;
		$nextstep = 3;





















	}

} // end of step 2



################################################################################################################################

         #####
        #     #
              #
         #####
              #
        #     #
         #####

################################################################################################################################
if( isset($_POST['wpsc-step']) && $_POST['wpsc-step'] == 3 ){
	echo 'Step '.$_POST['wpsc-step'].' -- opening file and recording the changes.<br/><br/>';


	$dest_file = WPSTAGECOACH_TEMP_DIR.$USER.'-changes';

	// get the contents of the file and make sure they are sane.
	$new_changes = json_decode(file_get_contents($dest_file), true);

	if( WPSC_DEBUG ){
		echo '<a class="toggle">Show raw list of changes</a><br/>'.PHP_EOL;
		echo '<div class="more" style="display: none">'.PHP_EOL;
		echo '<pre>';
		print_r($new_changes);
		echo '</pre>';	
		echo '</div>'.PHP_EOL;
	}




	if( is_array($new_changes) && ( (isset($new_changes['db']) && is_array($new_changes['db'])) || (isset($new_changes['file']) && is_array($new_changes['file'])) ) ){
		echo 'The changes file looks okay!<br/>'.PHP_EOL;
	} else {
		$msg = 'It looks like the changes file we downloaded is not valid. Please contact WP Stagecoach support with the following information:<br/>'.PHP_EOL;
		$msg .= '<pre>'.print_r($wpsc_sanity, true).'</pre>';
		wpsc_display_error($msg);
		return false;
	}

	$current_changes = get_option('wpstagecoach_retrieved_changes');
	delete_option('wpstagecoach_retrieved_changes');


	if( empty($current_changes) ){
		$res = add_option('wpstagecoach_retrieved_changes', $new_changes, '', 'no');
	} else {  // we have to merge alllllllllllllllllllllll the entries
		// foreach over db & files
			// foreach over db_tables & file_mods
				// foreach over 
		$res = add_option('wpstagecoach_retrieved_changes', array_merge_recursive($current_changes, $new_changes), '', 'no');
	}


	if( !$res ){
		$msg  = 'We were not able to store your changes in the database--please contact WP Stagecoach support with the following information:<br/>'.PHP_EOL;
		$msg .= '<pre>'.print_r($wpsc_sanity, true).'</pre>';
		wpsc_display_error($msg);
		return;
	} else { // everything is okay!

		echo 'We were able to store you changes into the database!<br/>'.PHP_EOL;
		unlink($dest_file); // the changes file
		unlink($dest_file.'.md5'); // the .md5 file



// go delete/archive the files from wpstagecoach!




		// go tell the staging server we got it, and it can delete the old file
		$post_url = WPSTAGECOACH_CONDUCTOR.'/wpsc-delete-changes-file.php';
		$post_args = array(
			'wpsc-user'			=> $wpsc['username'],
			'wpsc-key'			=> $wpsc['apikey'],
			'wpsc-ver'			=> WPSTAGECOACH_VERSION,
			'wpsc-live-site'	=> WPSTAGECOACH_LIVE_SITE,
			'wpsc-stage-site'	=> WPSTAGECOACH_STAGE_SITE,
			'wpsc-live-path'	=> rtrim(ABSPATH, '/'),
			'wpsc-dest'			=> WPSTAGECOACH_SERVER,
			'wpsc-file'			=> $USER.'-changes',
		);
		if( !empty($_POST['wpsc-options']) )
			$post_args['wpsc-options'] = $_POST['wpsc-options'];

		$post_result = wp_remote_post($post_url, array('timeout' => 300, 'body' => $post_args) );
		$result = wpsc_check_post_info('delete-changes-file', $post_url, $post_args, $post_result) ; // check response from the server



		if($result['result'] == 'OK'){
			if(WPSC_DEBUG){
				echo '<pre>';
				print_r($result);
				echo '</pre>';
			}

			echo 'We succesfully deleted your changes files from the server.<br/>'.PHP_EOL;



		} else {
			$normal = false;
			$msg  = 'There was a problem cleaning up the changes on your staging site '.WPSTAGECOACH_STAGE_SITE.'.<br/>'.PHP_EOL;
			$msg .= 'Please contact WP Stagecoach support with this error information:';
			$msg .= '<pre>'.print_r($result['info'], true).'</pre>';
			#wpsc_display_error($msg, false);
			echo '<div class="wpscwarn">'.$msg.'</div>'.PHP_EOL;


		}
		$done = true;
	}

} // end of step 3



/*******************************************************************************************
*                                                                                          *
*                              End of checking step form                                   *
*                                                                                          *
*******************************************************************************************/

if( 1 ){
	echo str_pad('',4096)."\n";
	ob_flush();
	flush();
	sleep(1);
}

if( !isset($nextstep)){
	$nextstep =0;
	$normal = false;
}

$wpscpost_fields['wpsc-step'] = $nextstep;

if( $normal === true )
	echo '<form style="display: hidden"  method="POST" id="wpsc-step-form">'.PHP_EOL;

foreach ($wpscpost_fields as $key => $value)
	echo '  <input type="hidden" name="'.$key.'" value="'.$value.'"/>'.PHP_EOL;

if( !empty($wpscpost_wpsc_options) )
	foreach ($wpscpost_wpsc_options as $key => $value)
		echo '  <input type="hidden" name="wpsc-options['.$key.']" value="'.$value.'"/>'.PHP_EOL;

if( $normal === true ){
	echo '</form>'.PHP_EOL;
	echo '<script>'.PHP_EOL;
	echo 'document.forms["wpsc-step-form"].submit();'.PHP_EOL;
	echo '</script>'.PHP_EOL;
} else {
	echo '</form>'.PHP_EOL;
}




?>
