<?php

if ( ! defined('ABSPATH') ) {
	die('Please do not load this file directly.');
}

###                                                                                         ###
###       this file applies changes that are stored locally (from the staging site)         ###
###                                                                                         ###


if( isset($wpsc['debug']) ){
	define('LOG', true);
} else {
	define('LOG', false);
}


set_time_limit(0);
ini_set("max_execution_time", "0");
ini_set('memory_limit', '-1');




echo '<br/><hr/>'.PHP_EOL;
echo 'WP Stagecoach is importing your changes';
echo '<hr/><br/>'.PHP_EOL;



$USER=array_shift(explode('.',WPSTAGECOACH_STAGE_SITE));



// check to see if the site is in a subdir (or multiple subdirs) on the live site
$subdir_array =  explode('/', WPSTAGECOACH_LIVE_SITE) ;
array_shift($subdir_array);
if( !empty($subdir_array) ){
	$subdir = implode('/', $subdir_array);
	unset($subdir_array);
}



//	must check if our temp dir is writable, otherwise, bad!
if( !is_writable(WPSTAGECOACH_TEMP_DIR) ){
	$msg = '<b>The "temp/" directory in the WP Stagecoach plugin is not writable--this is a major problem!</b><br/>'.PHP_EOL;
	$msg .= 'You need to fix this before we can continue. The full path is: '.WPSTAGECOACH_TEMP_DIR.''.PHP_EOL;
	die;
}









// do logging if enabled
if(LOG){
	$logname = WPSTAGECOACH_TEMP_DIR.'import.log';

	if( !isset($_POST['wpsc-step']) || $_POST['wpsc-step'] == 1 ){
		if( is_file($logname)){
			rename($logname, WPSTAGECOACH_TEMP_DIR.'import.log-'.date('Y-m-d_H:i', filemtime($logname) ) );
		}
		$flog = fopen($logname, 'a');
		fwrite($flog, '--------------------------------------------------------------------------------'.PHP_EOL);
		fwrite($flog, "starting import on ".date('Y-m-d_H:i').PHP_EOL);
		fwrite($flog, '--------------------------------------------------------------------------------');
	} else {
		$flog = fopen($logname, 'a');
	}

	$posttemp = array();
	foreach ($_POST as $key => $post) {
		if( is_array($post) )
			$posttemp[$key] = 'array, size: '.sizeof($post);
		elseif( is_string($post) && strlen($post) > 50)
			$posttemp[$key] = 'string, len: '.strlen($post);
		else
			$posttemp[$key] = $post;
	}
	fwrite($flog, PHP_EOL.'step '.$_POST['wpsc-step'].PHP_EOL.'$_POST:'.print_r($posttemp,true).PHP_EOL);

}

if( !isset($_POST['wpsc-step']) )
	$_POST['wpsc-step'] = 1;




// set the current step stored in the database
delete_option('wpstagecoach_importing');
add_option('wpstagecoach_importing', $_POST['wpsc-step'], '', 'no');




/*******************************************************************************************
*                                                                                          *
*                               Beginning of stepping form                                 *
*                                                                                          *
*******************************************************************************************/


$wpscpost_fields = array(
	'wpsc-import-changes' => $_POST['wpsc-import-changes'],
);
if( !empty($_POST['wpsc-options']) )
	$wpscpost_wpsc_options = $_POST['wpsc-options'];

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
if( isset($_POST['wpsc-step']) && $_POST['wpsc-step'] == 1 ){
	echo 'Step 1 -- loop through changes -- store choices and make sure everything is writable!<br/>';

	// set some arrays we'll be using later
	$wpsc_writable_file_test_array = array();	// list of all the files we need to see if are writable

	// we must go up from wp-admin to crawl the filesystem
	chdir('../');




	// decode the select files & DB from $_POST & store it in $stored_array
	foreach ( array('new','modified','deleted','db') as $action_type){
		if( isset($_POST['wpsc-'.$action_type]) && is_array( $_POST['wpsc-'.$action_type] ) ){
			// de-base64 and put files we are currently working on into database
			$temp_array = array();
			foreach ($_POST['wpsc-'.$action_type] as $encfile){
				$temp = base64_decode($encfile);
				if( !empty($temp) ) {
					$temp_array[] = $temp;
				} else { // we really shouldn't get an empty string when we base64 decode
					wpsc_display_error('warning: this selected post entry failed to decode: '.$encfile);
				}
			}
			unset($_POST['wpsc-'.$action_type]);
			$store_array[$action_type] = $temp_array;

		}
	}

	
	// we are picking up where we left off, and we should get the selected files & DB entries back from the database
	if( $_POST['wpsc-import-changes'] == 'Continue' ){
		$store_array = get_option('wpstagecoach_importing_files');
		$store_array['db'] = get_option('wpstagecoach_importing_db');
	}



	// check for writablity of files
	foreach ( array('new','modified','deleted') as $action_type){
		if( isset($store_array[$action_type]) && is_array( $store_array[$action_type] ) ){


				//	get a list of dirs that New files will be put in to make sure they are writable
			if( $action_type == 'modified' || $action_type == 'new' ){
				foreach ($store_array[$action_type] as $file){
					$tempdir = dirname($file);

					// need to check the parent dir of any dirs that don't exist, but not those dirs!
					while( !is_dir($tempdir) ){
						$tempdir = dirname($tempdir);
					}
					$wpsc_writable_file_test_array[] = $tempdir;
				}

				//	condense the list of dirs so we don't recheck repeats
				if( is_array($wpsc_writable_file_test_array) )
					$wpsc_writable_file_test_array= array_unique($wpsc_writable_file_test_array);
			}


			//	get a list of Modified or Deleted files that we need to be sure we can write to 
			if( $action_type == 'modified' || $action_type == 'deleted' ){
				if( is_array( $store_array[$action_type] )){
					$wpsc_writable_file_test_array = array_merge($store_array[$action_type], $wpsc_writable_file_test_array);
				}
			}

		}
	}

	// store the selected changes in the database:
	if( isset($store_array['db']) ){
		delete_option('wpstagecoach_importing_db');
		add_option('wpstagecoach_importing_db', $store_array['db'], '', 'no' );
	}
	if( isset($store_array['new']) || isset($store_array['modified']) || isset($store_array['deleted']) ){
		delete_option('wpstagecoach_importing_files');
		add_option('wpstagecoach_importing_files', $store_array, '', 'no' );
	}

	if(WPSC_DEBUG){
		echo 'store_array:<pre>';
		print_r($store_array);
		echo '</pre>';
	}

	if ( empty($wpsc_writable_file_test_array) && empty($store_array['db']) ){
		if( LOG  ) fwrite($flog, 'no files/db selected. deleting "working" option.'.PHP_EOL);
		delete_option('wpstagecoach_working');
		echo '<p>You didn\'t appear to select any files or database entries to import!</p>';
		echo '<form method="POST" id="wpsc-no-items-checked-form">';
		echo '  <input type="submit" value="Go back and select some file or database entries to import">';
		echo '</form>';
	} elseif ( !empty($store_array['db']) && empty($wpsc_writable_file_test_array)){
		// DB only
		if( LOG  ) fwrite($flog, 'no files--only db selected.'.PHP_EOL);
		echo '<p>No files selected, just database entries, moving on.</p>';
		$nextstep = 5;
	} else { // We have at least files, so onto step 2.
		if( LOG  ) fwrite($flog, 'at least files selected.'.PHP_EOL);
		$nextstep = 2;
		// don't need to worry about DB stuff until later.
		unset($store_array['db']);



		// check to make sure files which are supposed to be there are, and those that shouldn't aren't.
		foreach (array('new', 'modified', 'deleted') as $action_type) {
			if(is_array($store_array[$action_type])){
				foreach ($store_array[$action_type] as $file) {
					if( $action_type == 'new' && is_file($file) ){
						$file_problem[] = 'The file <b>"'.$file.'"</b>, should <b>not</b> exist, but it does.';
						if( LOG  ) fwrite($flog, $file.' should NOT exist, but it does'.PHP_EOL);
					} elseif( $action_type == 'modified' && !is_file($file) ){
						$file_problem[] = 'The file <b>"'.$file.'</b>", <b>should</b> exist, but it does not.';
						if( LOG  ) fwrite($flog, $file.' SHOULD exist, but it does not'.PHP_EOL);
					} elseif( $action_type == 'deleted' && !is_file($file) ){
						$file_problem[] = 'The file <b>"'.$file.'</b>", <b>should</b> exist, but it does not <small>(it was going to be deleted anyway, but it is odd)</small>.';
						if( LOG  ) fwrite($flog, $file.' SHOULD exist, but it does not'.PHP_EOL);
					}
				}
			}
		}



		// go over the list of files & dirs to make sure they are writable
		foreach ($wpsc_writable_file_test_array as $file) {
			if( is_file($file) && !is_writable($file) )
				$unwritable_array[] = $file;
		}



		// writablility checks
		//	if we have files or dirs that are unwritable, give error and prompt before moving on.
		if( (isset($unwritable_array) && is_array($unwritable_array)) || ( isset($file_problem) && is_array($file_problem)) ){
			$msg = '';
			if( isset($unwritable_array) && is_array($unwritable_array) ){

				if( LOG  ) fwrite($flog, 'some files or dirs are unwritable:'.print_r($unwritable_array,true).PHP_EOL);
				$msg .= 'The following files or directories you are trying to import are not writable by your webserver:<br/>'.PHP_EOL;
				$msg .= '<ul>'.PHP_EOL;
				foreach ($unwritable_array as $file) {
					$msg .= '<li style="margin-left: 2em">./';
					if ( $file == '.' )
						$msg .= ' (the root directory of your website)';
					else
						$msg .= $file;
					$msg .= PHP_EOL;
				}
				$msg .= '</ul>'.PHP_EOL;
				$msg .= 'WP Stagecoach cannot import changes to these files.<br/>'.PHP_EOL;
				$msg .= 'To let WP Stagecoach work, you will need to make these files writable by your web server.'.PHP_EOL;
				$msg .= '<a href="https://wpstagecoach.com/file-permissions" target="_blank">More information about file permissions</a><br/>'.PHP_EOL;
				$msg .= 'You may safely leave this page here and come back when you are done and reload this page (you will probably have to press yes to resubmit the data).<br/>'.PHP_EOL;
			}

			if( isset($file_problem) && is_array($file_problem) ){
				if( isset($unwritable_array) && is_array($unwritable_array) )
					$msg .= '<br/>';
				if( LOG  ) fwrite($flog, 'some files are missing or unexpectedly present:'.print_r($file_problem,true).PHP_EOL);
				$msg .= 'The following files are in a state different from when the staging site was created:<br/>'.PHP_EOL;

				$msg .= '<ul>'.PHP_EOL;
				foreach ($file_problem as $file) {
					$msg .= '<li style="margin-left: 2em">';
					$msg .= $file;
					$msg .= PHP_EOL;
				}
				$msg .= '</ul>'.PHP_EOL;
				$msg .= 'Because of this, importing may yield unexpected results, and you may not be able to totally restore from the import.<br/>'.PHP_EOL;

			}

			$msg .= '<h3>Do you want to continue with the import process, knowing that it may not go correctly?</h3>'.PHP_EOL;
			$msg .= 'Make sure you have backed up your site!<br/>'.PHP_EOL;


			$form = '<form method="POST" id="wpsc-unwritable-form">'.PHP_EOL;

			//  YES
			$post_fields = array(
				'wpsc-step' => $nextstep,
				'wpsc-import-changes' => true,
			);
			foreach ($post_fields as $key => $value) {
				$form .= '  <input type="hidden" name="'.$key.'" value="'.$value.'"/>'.PHP_EOL;
			}
			$form .= '  <input type="submit" name="wpsc-unwritable-files" value="Yes, charge ahead!">'.PHP_EOL;
			$form .= '</form>'.PHP_EOL;
			//  NO
			$form .= '<form method="POST" id="wpsc-unwritable-form">';
			$form .= '  <input type="submit" value="No, go back and choose different files.">';
			$form .= '</form>';
			wpsc_display_error($msg.$form);

			return;
		}

	}  //  end of having post data! :-)


}  // end of STEP 1


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
	echo 'Step '.$_POST['wpsc-step'].' -- going to talk the conductor and have new tar file created.<br/>';
	$nextstep = 3;	
	echo str_pad('',4096).PHP_EOL;
	ob_flush();
	flush();


	// make the list of all the new/modified files we need to go download
	if( !$allfiles = get_option('wpstagecoach_importing_files') ){
		wpsc_display_error('Uh oh!<br/>There was an error retrieving the selected file entries from the WordPress database; please re-select your changes to import and try again.'.PHP_EOL);
		require_once 'wpsc-import-display.inc.php';
		return;
	}
	$tar_file_list = array();
	foreach (array('new','modified') as $action_type) {
		if( is_array($allfiles[$action_type]) ){
			$tar_file_list = array_merge( $tar_file_list, $allfiles[$action_type] );
		}
	}



	if( LOG  ) fwrite($flog, 'list of new/mod files we need to download: '.print_r($tar_file_list,true).PHP_EOL);

	//	check if given host name is taken.
	$post_url = WPSTAGECOACH_CONDUCTOR.'/wpsc-make-tar-file.php';
	$post_args = array(
		'wpsc-user'			=> $wpsc['username'],
		'wpsc-key'			=> $wpsc['apikey'],
		'wpsc-ver'			=> WPSTAGECOACH_VERSION,
		'wpsc-live-site'	=> WPSTAGECOACH_LIVE_SITE,
		'wpsc-stage-site'	=> WPSTAGECOACH_STAGE_SITE,
		'wpsc-dest'			=> WPSTAGECOACH_SERVER,
		'wpsc-file-list'	=> base64_encode(json_encode( $tar_file_list )),		
	);




	$post_result = wp_remote_post($post_url, array('timeout' => 300, 'body' => $post_args) );
	$result = wpsc_check_post_info('check_if_site_exists', $post_url, $post_args, $post_result) ; // check response from the server



	if(isset($result['result']) && $result['result'] == 'OK'){


		echo 'Great, we were able to successfully make the tar file.<br/>'.PHP_EOL;
		if( LOG  ) fwrite($flog, 'successfully talked to webserver & had tar file created.'.PHP_EOL);



	} else { // we got a bad result--it will output the reason above

		return false;
	}
	
}  // end of STEP 2



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
	echo 'Step '.$_POST['wpsc-step'].' -- download tar file from app.WPSC.com.<br/>';
	$nextstep = 4;

	$post_url = 'https://'.WPSTAGECOACH_SERVER.'/wpsc-app-download-tar-file.php';
	$post_args = array(
		'wpsc-user'			=> $wpsc['username'],
		'wpsc-key'			=> $wpsc['apikey'],
		'wpsc-ver'			=> WPSTAGECOACH_VERSION,
		'wpsc-live-site'	=> WPSTAGECOACH_LIVE_SITE,
		'wpsc-stage-site'	=> WPSTAGECOACH_STAGE_SITE,
		'wpsc-live-path'	=> rtrim(ABSPATH, '/'),
		'wpsc-dest'			=> WPSTAGECOACH_SERVER,
		'wpsc-file'			=> $USER,
	);

	foreach (array('.tar.gz', '.md5') as $ext) {
		$post_args['wpsc-file'] .= $ext;
		$dest_file = WPSTAGECOACH_TEMP_DIR.$post_args['wpsc-file'];

		
		echo 'Downloading the file '.$post_args['wpsc-file'].'  currently...';
		echo str_pad('',4096).PHP_EOL;
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
		if($ext == '.tar.gz')  // might as well get the checksum while we're here!
			$md5sum = md5_file($dest_file);

		echo 'done!<br/>'.PHP_EOL;
		echo str_pad('',4096).PHP_EOL;
		ob_flush();
		flush();
	} // end foreach over the file & .md5 file



	// make sure the file has the correct .md5 sum -- compare it to the current dest_file (the md5 file)
	if( $md5sum != trim(file_get_contents($dest_file)) ){
		$normal=false;
		$nextstep = 3;

		$msg = 'Error: the changes file we just downloaded appears to have gotten corrupted (the checksums do not match)!<br/>'.PHP_EOL;
		$msg .= 'Would you like to try to download it again?<br/>'.PHP_EOL;
		wpsc_display_error($msg);

		echo '<form method="POST" id="wpsc-step-form">'.PHP_EOL;
		echo '  <input type="hidden" name="wpsc-import-changes" value="Yes"/>'.PHP_EOL;
		echo '<input type="submit" name="wpsc-options[retry]" value="Yes">'.PHP_EOL;
		echo '</form>';
		echo '<form method="POST" name="wpsc-import-cleanup" action="admin.php?page=wpstagecoach_import">'.PHP_EOL;
		echo '  <input type="hidden" name="wpsc-options[cleanup-import]" value="Yes"/>'.PHP_EOL;
		echo '<input type="submit" name="wpsc-options[stop]" value="No">'.PHP_EOL;
 
	} else {
		echo '<br/>The changes file appears to have downloaded without a problem!<br/>'.PHP_EOL;
		$nextstep = 4;
	}


}  // end of STEP 3



################################################################################################################################
		#
		#    #
		#    #
		#    #
		#######
		     #
		     #
################################################################################################################################
if( isset($_POST['wpsc-step']) && $_POST['wpsc-step'] == 4 ){
	echo 'Step '.$_POST['wpsc-step'].' -- untar tar file into "temp" dir.<br/>'.PHP_EOL;
	$nextstep = 5;

	chdir(WPSTAGECOACH_TEMP_DIR); // need to do everything in the temp dir!
	require_once('Tar.php');
	$TAR_FILE = WPSTAGECOACH_TEMP_DIR.'/'.$USER.'.tar.gz';

	// get an array of all the files we have from the tar file
	if( file_exists( $TAR_FILE ) ){ 
		$tar = new Archive_Tar( $TAR_FILE );
		$files_from_tar_array = $tar->listContent();
		if( !is_array($files_from_tar_array) ){
			$msg = 'Error: we didn\'t get a valid list of files from the tar file--please check the file: '.$TAR_FILE.PHP_EOL;
			wpsc_display_error($msg);
			if( LOG  ) fwrite($flog, $msg.PHP_EOL);
			return;
		} else {
			echo 'opened tar.<br/>';
			if( LOG  ) fwrite($flog, 'successfully opened tar file.'.PHP_EOL);
			foreach ($files_from_tar_array as $file) {
				$files_from_tar[] = $file['filename'];
			}
		}
	} else{
		wpsc_display_error('Error: we don\'t see the tar file: '.$TAR_FILE.', I swear it was here a second ago!');
		if( LOG  ) fwrite($flog, 'Error: can\'t find tar file?'.PHP_EOL);
		return;
	}


	// make the list of all the new/modified files that should be in the tar file-- need to compare to the tar file's actaul contents
	if( !$allfiles = get_option('wpstagecoach_importing_files') ){
		wpsc_display_error('Uh oh!<br/>There was an error retrieving the selected file entries from the WordPress database; please re-select your changes to import and try again.'.PHP_EOL);
		require_once 'wpsc-import-display.inc.php';
		return;
	}
	$tar_file_list = array();
	foreach (array('new','modified') as $action_type) {
		if( is_array($allfiles[$action_type]) ){
			$tar_file_list = array_merge( $tar_file_list, $allfiles[$action_type] );
		}
	}




	// compare them so we know we have everything we need.
	$diff_array = array_diff( $files_from_tar, $tar_file_list);
	if( !empty($diff_array) ){
		wpsc_display_error('Error: the tar file we downloaded does not have all the files we requested--please try again, or contact WP Stagecoach support if it happens again.').PHP_EOL;
		if( LOG  ) fwrite($flog, 'Error: $diff_array was not empty--we are missing files we want from the tar file.'.PHP_EOL);
		return;
	} else{ 
		echo 'The tar file has all the files we asked for, yay!<br/>'.PHP_EOL;
		if( LOG  ) fwrite($flog, 'all the files we want are in the tar file.'.PHP_EOL);

		if ( $tar->extract('extract/') ){
			echo 'Files were extracted successfully!<br/>';
			if( LOG  ) fwrite($flog, 'Files were extracted successfully.'.PHP_EOL);
		} else {
			$msg = 'We ran into a problem while extracting the files from the tar to '.WPSTAGECOACH_TEMP_DIR.'/extract/<br/>'.PHP_EOL;
			$msg .= 'Please check the permissions on '.WPSTAGECOACH_TEMP_DIR.'/extract/.<br/>'.PHP_EOL;
			wpsc_display_error($msg);
			if( LOG  ) fwrite($flog, $msg.PHP_EOL);
			return;
		}

	}



	if( !$alldb = get_option('wpstagecoach_importing_db') ){
		echo '<p>No database entries selected, just doing file changes, moving on.</p>';
		if( LOG  ) fwrite($flog, 'no database entries select, skipping DB steps.'.PHP_EOL);
		$nextstep = 6;
	} else {
		echo '<p>All the files have been extracted into the temp directory, going to go backup the database next.</p>'.PHP_EOL;
	}


}  // end of STEP 4


################################################################################################################################
		#######
		#
		#
		######
		      #
		#     #
		 #####
################################################################################################################################
if( isset($_POST['wpsc-step']) && $_POST['wpsc-step'] == 5 ){
	echo 'Step '.$_POST['wpsc-step'].' -- backup DB!<br/>'.PHP_EOL;
	$nextstep = 6;

	
	define('WPSTAGECOACH_DB_FILE', WPSTAGECOACH_TEMP_DIR . WPSTAGECOACH_LIVE_SITE .'-backup_'.date('Y-M-d_H:i').'.sql.gz');
	require_once('wpsc-db-backup.inc.php');


	if( ! is_file(WPSTAGECOACH_DB_FILE) && filesize(WPSTAGECOACH_DB_FILE) > 1 ){
		$msg = 'Error: we couldn\'t create the database backup file--please try again, or contact WP Stagecoach support if it happens again.'.PHP_EOL;
		wpsc_display_error($msg);
		if( LOG  ) fwrite($flog, 'Error: tar file not saved successfully.'.PHP_EOL);
		die;
	}

	if( LOG  ) fwrite($flog, 'backed up DB.'.PHP_EOL);

	echo '<div class="wpscinfo"><h4>We have successfully backed up your database in its current state to the file:<br/>'.WPSTAGECOACH_DB_FILE.'</h4>'.PHP_EOL;
	echo 'Your backup file has been noted in the WP Stagecoach dashboard, should it need it!</div>'.PHP_EOL;

	echo str_pad('',4096).PHP_EOL;
	ob_flush();
	flush();


	$wpsc['db_backup_file'] = WPSTAGECOACH_DB_FILE;
	update_option('wpstagecoach', $wpsc);



	echo 'continuing in ';
	for ($i=3; $i > 0; $i--) { 
		echo $i.' ';
		echo str_pad('',4096).PHP_EOL;
		ob_flush();
		flush();
		sleep(1);
	}
	

}  // end of STEP 5




################################################################################################################################
		 #####
		#     #
		#
		######
		#     #
		#     #
		 #####
################################################################################################################################
if( isset($_POST['wpsc-step']) && $_POST['wpsc-step'] == 6 ){
	echo 'Step '.$_POST['wpsc-step'].' -- Work, Work, Work.<br/>Loop through file lists, move old files away and move new files into place.<br/>';
	$nextstep = 7;


	echo str_pad('',4096).PHP_EOL;
	ob_flush();
	flush();



	if( !$alldb = get_option('wpstagecoach_importing_db') ){ // no need to go do DB stuff.
		$do_db = false;
		echo '<p>No database entries selected, just doing file changes.</p>';
		if( LOG  ) fwrite($flog, 'no database entries selected, skipping DB steps.'.PHP_EOL);
	} else {
		if( is_array($alldb) ){
			$do_db = true;
			echo ' Do DB changes.<br/>'.PHP_EOL;
			if( LOG  ) fwrite($flog, 'Doing DB changes too'.PHP_EOL);
		} else {
			$do_db = false;
			wpsc_display_error('Error: the stored database changes entry is bad!'.PHP_EOL);
			if( LOG  ) fwrite($flog, 'Error: stored database entry is bad!'.PHP_EOL);
			$normal = false;
		}
	
	}

	$allfiles = get_option('wpstagecoach_importing_files');
	if( !$allfiles && !$do_db ){
		wpsc_display_error('Uh oh!<br/>There was an error retrieving the selected file entries from the WordPress database; please re-select your changes to import and try again.'.PHP_EOL);
		require_once 'wpsc-import-display.inc.php';
		return;
	}


	// move the new files into place
	chdir('../');  // need to get into the root directory of the site
	$errmsg = '';  // need to have an error message ready to append to.
	if( LOG  ) fwrite($flog, 'start work on file changes'.PHP_EOL);
	foreach ( array('new','modified','deleted') as $action_type){

		if( is_array( $allfiles[$action_type] ) && $normal == true  ){
			#$file_array = json_decode( base64_decode($allfiles[$action_type]), true) ;

			if( LOG  ) fwrite($flog, 'working on '.$action_type.PHP_EOL);
			foreach ($allfiles[$action_type] as $file) {

				echo 'working on '.$file.' -- it is '.$action_type.'<br/>'.PHP_EOL;
				echo str_pad('',4096).PHP_EOL;
				ob_flush();
				flush();

				if( ($action_type == 'new' || $action_type == 'modified') && !is_file(WPSTAGECOACH_TEMP_DIR.'extract/'.$file) ){
					$errmsg = 'Whoa--the file "'.WPSTAGECOACH_TEMP_DIR.'extract/'.$file.'", which we just extracted, does not appear to be there.<br/>'.PHP_EOL;
					$errmsg .= 'Something very strange is going on in the filesystem!<br/>Did you perhaps manually refresh a step?<br/>'.PHP_EOL;
					$errmsg .= 'WP Stagecoach will not continue as it will lead to unexpected results.<br/>'.PHP_EOL;
					$errmsg .= 'Please contact WP Stagecoach support and tell them exactly what happened, and what you did before it happened.<br/>'.PHP_EOL;
					wpsc_display_error($errmsg);
					return;
				}



				if( $action_type == 'modified' || $action_type == 'deleted' ){
					$moved = '';
					// move away old file
					if( is_file($file) ){
						if( !rename( $file, $file.'.wpsc_temp' ) ){
							echo 'Error: could not rename '.$action_type.' file "'.$file.'" to WPSC temp file name "'.$file.'.wpsc_temp".<br/>Please check permissions on this directory: '.dirname($file).'!<br/>'.PHP_EOL;
							$moved = false;
							$normal = false;
							// the file that should be there isn't, we need to make a record of it.
							if( LOG  ) fwrite($flog, 'Failed moving '.$action_type.' file '.$file.' to include .wpsc_temp name.'.PHP_EOL);
						} else {
							$moved=true;
						}
					} elseif( !is_file($file) ) {
						$errmsg .= 'The file "'.$file.'" does not appear to exist, <b>but it should!</b> ';
						if( $action_type == 'modified' ){
							$errmsg .= 'We are going to try to move the new file from the staging site here anyway.<br/>'.PHP_EOL;
							 #rename( WPSTAGECOACH_TEMP_DIR.'extract/'.$file, $file );
						} else {
							$errmsg .= 'We were just going to delete it anyway, but it is odd that it disappeared.<br/>'.PHP_EOL;
						}
					}

					if($action_type == 'modified' && $moved !== false ) {
						$moved = true;
					}
				}


				if( ($action_type == 'new' || ($action_type == 'modified' && $moved == true )) && !is_file($file) ){
					unset($moved);
					// make parent directory if it doesn't exist.
					$dir = dirname($file);
					while( !is_dir( $dir ) ){
						$dir_arr[] = $dir;
						$dir = dirname($dir);
					}
					if( isset($dir_arr) && is_array($dir_arr) ){
						sort($dir_arr);
						foreach ($dir_arr as $dir) {
							mkdir( $dir );
						}
						unset($dir_arr);
						unset($dir);
					}
					// move new file
					if( $normal == true && !rename( WPSTAGECOACH_TEMP_DIR.'extract/'.$file, $file )){
						echo 'Error: could not move the '.$action_type.' file from "./wp-content/plugins/wpstagecoach/temp/extract/'.$file.'" to "./'.$file.'".<br/>Please check permissions on this directory: "'.dirname($file).'"<br/>'.PHP_EOL;
						$moved = false;
						$normal = false;
						if( LOG  ) fwrite($flog, 'Failed moving '.$action_type.' file '.$file.' into '.dirname($file).'.'.PHP_EOL);
					}
					if(!$normal){
						break;
					}
				} elseif( is_file($file) ){
					// the file that should be there isn't, we need to make a record of it.
					$errmsg .= 'The file '.$file.' <b>should not exist</b>, but it does.  We are going to try to rename it to "'.$file.'.wpsc-temp" and put the new file from the staging site in its place.<br/>'.PHP_EOL;
					rename( $file, $file.'.wpsc_temp' );
					rename( WPSTAGECOACH_TEMP_DIR.'extract/'.$file, $file );
					// todo -- maybe change file from 'new' to 'modified' action_type?
				}
			}

			if(!$normal)
				break;
		} else{
			if( LOG  ) fwrite($flog, $action_type.' is empty'.PHP_EOL);
		}
	}


	if( !empty($errmsg) ){
		$msg = 'There were some problems with the state of some files compared to what they should be.<br/>'.PHP_EOL;
		$msg .= 'we have done our best to figure out what needed to happen, but just in case, here is what we found:<br/><br/>'.PHP_EOL;
		wpsc_display_error($msg.$errmsg);
		echo str_pad('',4096).PHP_EOL;
		ob_flush();
		flush();
		$normal = false;

		echo '<form method="POST" id="wpsc-files-in-abnormal-state-form">'.PHP_EOL;

		//  YES
		$post_fields = array(
			'wpsc-step' => $nextstep,
			'wpsc-import-changes' => true,
		);
		foreach ($post_fields as $key => $value) {
			echo '  <input type="hidden" name="'.$key.'" value="'.$value.'"/>'.PHP_EOL;
		}
		echo '  <input type="submit" name="wpsc-files-in-abnormal-state" value="Continue">'.PHP_EOL;
		echo '</form>'.PHP_EOL;


	}


	// now we apply all the items from the database.
	if( $do_db && $normal == true ){
		foreach ($alldb as $row) {
			$wpdb->query($row);
		}
	}



}  // end of STEP 6


################################################################################################################################
		#######
		#    #
		    #
		   #
		  #
		  #
		  #
################################################################################################################################
if( isset($_POST['wpsc-step']) && $_POST['wpsc-step'] == 7 ){
	echo 'Step '.$_POST['wpsc-step'].' -- confirm and cleanup!<br/>';

	echo '<p>Everything has been imported--please go check your site now and make sure everything is as you expected<br/>'.PHP_EOL;
	echo '<a target="_blank" href="'.get_site_url().'">Check your site</a></p>'.PHP_EOL;

	echo '<p>If everything looks right and you want to finalize everything, press the "Clean Up" button below and WP Stagecoach will clean up after itself<small>(we have left some temporary files ending with .wpsc_temp in your install--Clean Up will remove all those)</small>.</p>'.PHP_EOL;

	//  YES
	$post_fields = array(
		'wpsc-import-changes' => true,
	);
	foreach ( array('new','modified','deleted','db') as $action_type){
		if( !empty( $_POST['wpsc-'.$action_type] ) ){
			$post_fields['wpsc-'.$action_type] = $_POST['wpsc-'.$action_type];
		}
	}

	echo '<form method="POST" id="wpsc-unwritable-form">'.PHP_EOL;
	foreach ($post_fields as $key => $value)
		echo '  <input type="hidden" name="'.$key.'" value="'.$value.'"/>'.PHP_EOL;
	echo '  <input type="hidden" name="wpsc-step" value="8">'.PHP_EOL;
	echo '  <input type="submit" name="wpsc-everythings-peachy-delete" value="Clean Up and Delete staging site">'.PHP_EOL;
	echo '  <input type="submit" name="wpsc-everythings-peachy" value="Clean Up">'.PHP_EOL;
	echo '</form>'.PHP_EOL;


	echo '<p>If everything is not perfect, and you want to change all your files back to the way they were before, click "Revert Files" below.</p>'.PHP_EOL;
	echo '<p><small>(there is a database dump file "'.WPSTAGECOACH_TEMP_DIR.'wpsc-db-backup.sql.gz", however, this version cannot restore it yet)</small></p>'.PHP_EOL;

	//  NO
	echo '<form method="POST" id="wpsc-unwritable-form">';
	foreach ($post_fields as $key => $value)
		echo '  <input type="hidden" name="'.$key.'" value="'.$value.'"/>'.PHP_EOL;
	echo '  <input type="hidden" name="wpsc-step" value="9">'.PHP_EOL;
	echo '  <input type="submit" name="wpsc-everythings-in-a-handbasket" value="Revert Files">'.PHP_EOL;
	echo '</form>';
	echo '</div>';
}  // end of STEP 7

################################################################################################################################
		 #####
		#     #
		#     #
		 #####
		#     #
		#     #
		 #####
################################################################################################################################
if( isset($_POST['wpsc-step']) && $_POST['wpsc-step'] == 8 ){
	echo 'Step '.$_POST['wpsc-step'].' -- everything\'s good.<br/>';
	if( LOG  ) fwrite($flog, 'cleaning up after ourselves!'.PHP_EOL);



	echo '<p>We have cleaned up after ourselves!</p>'.PHP_EOL;
	echo '<p>bye!</p>'.PHP_EOL;


	chdir('../');

	foreach ( array('new','modified','deleted','db') as $action_type){
		if( isset( $_POST['wpsc-'.$action_type] ) ){
			// de bas64/json these
			$_POST['wpsc-'.$action_type] = json_decode(base64_decode($_POST['wpsc-'.$action_type]),true);

			if( is_array( $_POST['wpsc-'.$action_type] ) ){
				// delete our working option entries now that we're done.
				delete_option('wpstagecoach_working_'.$action_type.'_file_list');

				// find any entries from DB that we didn't handle & re-store those, otherwise, delete the option
				if( $action_type == 'db' ){
					foreach (get_option('wpstagecoach_change_'.$action_type.'_list') as $table => $tab_records) {
						$result = array_diff( $tab_records, $_POST['wpsc-'.$action_type] );
						if( ! empty($result) ){
							$remaining_entries[$table] = $result;
						}
					}
				} else{
					$remaining_entries = array_diff( get_option('wpstagecoach_change_'.$action_type.'_list'), $_POST['wpsc-'.$action_type] );

				}
				if( empty($remaining_entries) ) {
					delete_option('wpstagecoach_change_'.$action_type.'_list');
				} else {
					update_option('wpstagecoach_change_'.$action_type.'_list', $remaining_entries);
				}
				unset($remaining_entries);


				// go delete the backup files
				if( $action_type == 'modified' || $action_type == 'deleted' ){
					foreach ($_POST['wpsc-'.$action_type] as $file) {
						unlink($file.'.wpsc_temp');
					}
				}

			}

		}
	}

	// clean up old tar file
	if( file_exists(WPSTAGECOACH_TEMP_DIR .$USER.'.tar.gz') )
		unlink(WPSTAGECOACH_TEMP_DIR .$USER.'.tar.gz');

	// clean up old DB file
	if( file_exists(WPSTAGECOACH_TEMP_DIR .'wpsc-db-backup.sql.gz') )
		unlink(WPSTAGECOACH_TEMP_DIR .'wpsc-db-backup.sql.gz');

	// clean up old extraction directories
	if( is_dir(WPSTAGECOACH_TEMP_DIR .'extract') )
		wpsc_rm_rf(WPSTAGECOACH_TEMP_DIR.'extract');

	// we're no longer working
	delete_option('wpstagecoach_working');



	if( isset($_POST['wpsc-everythings-peachy-delete']) ){
		require_once('wpsc-plgn-delete-stage.inc.php');
	}

	echo display_feedback_form('import',
		array(
			'wpsc-stage' => $wpsc_sanity['auth']['stage-site'],
			'wpsc-live'  => $wpsc_sanity['auth']['live-site'],
			'wpsc-user'  => $wpsc['username'],
			'wpsc-key'   => $wpsc['apikey'],
			'wpsc-dest'  => $wpsc_sanity['auth']['server'],
			),
		'You have cleaned up after the import--did everything work properly?' );

}  // end of STEP 8

################################################################################################################################
		 #####
		#     #
		#     #
		 ######
		      #
		#     #
		 #####
################################################################################################################################
if( isset($_POST['wpsc-step']) && $_POST['wpsc-step'] == 9 ){
	echo 'Step '.$_POST['wpsc-step'].' -- okay, something not right happened--we are reverting files...<br/>';
	if( LOG  ) fwrite($flog, 'Reverting files--that\'s too bad...'.PHP_EOL);

	echo '<p>Restoring the original files.</p>'.PHP_EOL;

	if( get_option('wpstagecoach_importing_db') ){
		echo '<p>If you need, you may restore the database from the DB dump file "'.$wpsc['db_backup_file'].'"</p>'.PHP_EOL;
	}


	chdir('../');




	if( !$allfiles = get_option('wpstagecoach_importing_files') ){
		wpsc_display_error('Uh oh!<br/>There was an error retrieving the selected file entries from the WordPress database; please re-select your changes to import and try again.'.PHP_EOL);
		require_once 'wpsc-import-display.inc.php';
		return;
	}

	foreach ( array('new','modified','deleted','db') as $action_type){
		if( isset( $allfiles[$action_type] ) ){

			if( is_array( $allfiles[$action_type] ) ){
				// delete our working option entries now that we're done.
				delete_option('wpstagecoach_working_'.$action_type.'_file_list');


				// go restore the backup files
				if( $action_type == 'new' || $action_type == 'modified' ){
					foreach ($allfiles[$action_type] as $file) {
						unlink($file);
						if( sizeof(scandir(dirname($file))) == 2 ){
							rmdir(dirname($file));
							$file = dirname($file);
							while( sizeof(scandir(dirname($file))) == 2 ){
								rmdir(dirname($file));
								$file = dirname($file);
							}
						}
					}

				}
				if( $action_type == 'modified' || $action_type == 'deleted' ){
					foreach ($allfiles[$action_type] as $file) {
						rename($file.'.wpsc_temp', $file);
					}
				}



			}

		}
	}	


	// clean up changes tar file
	if( file_exists(WPSTAGECOACH_TEMP_DIR .$USER.'.tar.gz') )
		unlink(WPSTAGECOACH_TEMP_DIR .$USER.'.tar.gz');
	if( file_exists(WPSTAGECOACH_TEMP_DIR .$USER.'.tar.gz.md5') )
		unlink(WPSTAGECOACH_TEMP_DIR .$USER.'.tar.gz.md5');



	// clean up old extraction directories
	if( is_dir(WPSTAGECOACH_TEMP_DIR .'extract') )
		wpsc_rm_rf(WPSTAGECOACH_TEMP_DIR.'extract');

	// we're no longer importing
	delete_option('wpstagecoach_importing');
	delete_option('wpstagecoach_importing_db');
	delete_option('wpstagecoach_importing_files');




	echo wpsc_display_feedback_form('import_revert',
		array(
			'wpsc-stage' => $wpsc_sanity['auth']['stage-site'],
			'wpsc-live'  => $wpsc_sanity['auth']['live-site'],
			'wpsc-user'  => $wpsc['username'],
			'wpsc-key'   => $wpsc['apikey'],
			'wpsc-dest'  => $wpsc_sanity['auth']['server'],
			),
		'Sorry to see you reverted back your file changes--did the import work correctly?' );



}  // end of STEP 9




/*******************************************************************************************
*                                                                                          *
*                              End of importing step form                                  *
*                                                                                          *
*******************************************************************************************/

if( 1 ){
	echo str_pad('',4096).PHP_EOL;
	ob_flush();
	flush();
	sleep(1);
}

if( !isset($nextstep)){
	$nextstep = 0;
	$normal = false;
}

$wpscpost_fields['wpsc-step'] = $nextstep;

if( $normal == true )
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
} else { // not normal, and the step will have created its own form.
	echo '</form>'.PHP_EOL;
}


if( LOG  ){
	fclose($flog);
}

?>