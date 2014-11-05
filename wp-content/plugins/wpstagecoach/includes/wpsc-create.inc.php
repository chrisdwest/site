<?php

if ( ! defined('ABSPATH') ) {
	die('Please do not load this file directly.');
}


define('WPSTAGECOACH_DOMAIN',		'.wpstagecoach.com');
define('WPSTAGECOACH_LIVE_SITE',	$_POST['wpsc-create-liveurl']);
define('WPSTAGECOACH_STAGE_SITE',	$_POST['wpsc-create-stageurl'].WPSTAGECOACH_DOMAIN);
define('WPSTAGECOACH_LIVE_PATH',	$_SERVER['DOCUMENT_ROOT']);
define('WPSTAGECOACH_STAGE_PATH',	'/var/www/'.$_POST['wpsc-create-stageurl'].'/staging-site');
define('WPSTAGECOACH_DB_FILE',		WPSTAGECOACH_TEMP_DIR . WPSTAGECOACH_STAGE_SITE .'.sql.gz');
define('WPSTAGECOACH_TAR_FILE',		WPSTAGECOACH_TEMP_DIR . WPSTAGECOACH_STAGE_SITE .'.tar.gz');
define('WPSTAGECOACH_TAR_NOGZ_FILE', WPSTAGECOACH_TEMP_DIR . WPSTAGECOACH_STAGE_SITE .'.tar');
global $BINARY_FILE_LIST;
if( isset($_POST['wpsc-options']['no-hotlink']) && $_POST['wpsc-options']['no-hotlink'] == true ){	
	$BINARY_FILE_LIST = '';
} else {
	$BINARY_FILE_LIST = '\.jpg$|\.jpeg$|\.png$|\.gif$|\.svg$|\.bmp$|\.tif$|\.tiff$|\.pct$|\.pdf$|\.git$|\.mp3$|\.mp4$|\.m4a$|\.aac$|\.aif$|\.mov$|\.qt$|\.mpg$|\.mpeg$|\.wmv$|\.mkv$|\.avi$|\.mpa$|\.ra$|\.rm$|\.swf$|\.avi$|\.mpg$|\.mpeg$|\.flv$|\.swf$|\.gz$|\.sql$|\.tar$|\.log$|\.db$|\.123$|\.zip$|\.rar$|\.iso$|\.vcd$|\.toast$|\.bin$|\.hqx$|\.sit$|\.bak$|\.old$|\.psd$|\.psp$|\.ps$|\.ai$|\.rtf$|\.wps$|\.wpd$|\.dll$|\.exe$|\.wks$|\.msg$|\.mdb$|\.xls$|\.doc$|\.ppt$|\.xlsx$|\.docx$|\.pptx$|\.core$';
}


set_time_limit(0);
ini_set("max_execution_time", "0");
ini_set('memory_limit', '-1');




/*******************************************************************************************
*                                                                                          *
*                               Beginning of stepping form                                 *
*                                                                                          *
*******************************************************************************************/
$wpscpost_fields = array(
	'wpsc-create-liveurl' => $_POST['wpsc-create-liveurl'],
	'wpsc-create-stageurl' => $_POST['wpsc-create-stageurl'],
	'wpsc-create' => $_POST['wpsc-create'],
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

if( !isset($_POST['wpsc-step']) ){
	echo 'Step 1 -- Setting up and doing sanity checks.<br/>';

	//	sanity checks
	if( !ctype_alnum( $_POST['wpsc-create-stageurl'] ) ){
		echo "Sorry, you may only use alphanumeric characters in the subdomain name.";
		wpsc_display_create_form();
		return;
	}

	if( isset($_POST['wpsc-options']['password-protect']) && !empty($_POST['wpsc-options']['password-protect']) ){
		if( empty($_POST['wpsc-options']['password-protect-user']) || empty($_POST['wpsc-options']['password-protect-password']) ){
			$msg = 'You must specify a user and a password if you want to password protect your staging site.';
			wpsc_display_create_form('wpstagecoach.com', wpsc_display_error($msg), $wpsc);
			return;
		}
	}


	//	check if given host name is taken.
	$post_url = WPSTAGECOACH_CONDUCTOR.'/wpsc-sanity-check.php';
	$post_args = array(
		'wpsc-user'			=> $wpsc['username'],
		'wpsc-key'			=> $wpsc['apikey'],
		'wpsc-ver'			=> WPSTAGECOACH_VERSION,
		'wpsc-live-site'	=> WPSTAGECOACH_LIVE_SITE,
		'wpsc-stage-site'	=> WPSTAGECOACH_STAGE_SITE,
	);
	$post_result = wp_remote_post($post_url, array('timeout' => 120, 'body' => $post_args) );
	$result = wpsc_check_post_info('check_if_site_exists', $post_url, $post_args, $post_result) ; // check response from the server


	if( $result['result'] != 'OK' ){
		wpsc_display_error( print_r($result['info'],true) );
		return false;
	} else {
		if( is_string($result['info']['dest']) && strlen($result['info']['dest']) < 42 ){
			$wpscpost_wpsc_options['dest'] = $result['info']['dest'];
			echo '<br/>'.PHP_EOL;
			echo 'Okay, staging site name is good, and we know where to install it.<br/>'.PHP_EOL;
		} else {
			$errmsg  = 'We did not get a valid staging server from the conductor--please contact WP Stagecoach support with the following information:<br>'.PHP_EOL;
			$errmsg .= print_r($result['info'],true);
			wpsc_display_error( $errmsg );
			return false;
		}
	}	

	echo str_pad('',4096)."\n";
	ob_flush();
	flush();



	###   if old files are still around, delete them!
	if(file_exists(WPSTAGECOACH_DB_FILE)) unlink(WPSTAGECOACH_DB_FILE);
	if(file_exists(WPSTAGECOACH_TAR_FILE)) unlink(WPSTAGECOACH_TAR_FILE);

	global $wpdb;


	$db_size_query = 'SELECT sum( data_length + index_length ) / 1024 FROM information_schema.TABLES where table_schema="'.DB_NAME.'";';
	// $res = mysqli_query($db, $db_size_query);
	$db_size = array_shift($wpdb->get_row($db_size_query, ARRAY_N));
	// mysqli_free_result($res);

	if( $db_size / 1024 < 1)
		echo 'Database size: '.(int)$db_size.'KB<br/>';
	else
		echo 'Database size: '.(int)($db_size/1024).'MB<br/>';
	

	echo str_pad('',4096)."\n";
	ob_flush();
	flush();


	$nextstep = 2;

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
	echo 'Step '.$_POST['wpsc-step'].' -- creating database file.<br/>';

	if( isset($_POST['wpsc-force-utf8']) && $_POST['wpsc-force-utf8'] == true ){
		define('DB_CHARSET', 'utf8');
	} else {

		if( !defined('DB_CHARSET') ){
			$tables = $wpdb->get_results('SHOW tables', ARRAY_N);

			foreach ($tables as $table) {
				$res = $wpdb->get_results('SHOW CREATE TABLE '.$table[0], ARRAY_N);

				$res = $res[0][1];

				$pos = strpos($res, 'CHARSET');
				$substr = ltrim(substr($res, ($pos+7) )); //ltrim to remove any potential spaces
				$substr = ltrim(substr($substr, 1 )); //remove =
				if( !isset($charset) ){
					$charset = $substr;
				} elseif( $substr != $charset ) {
					$normal=false;

					echo '<div class="wpscerror">Your Database Character Set (DB_CHARSET) is not defined in your <b>wp-config.php</b> file as is the standard for WordPress.<br/>'.PHP_EOL;
					echo 'Unfortunately, WP Stagecoach was not able to determine the character set used in your database (we found conflicting results between tables).<br/>'.PHP_EOL;
					echo 'You will need to find what character set your database uses and set it in your wp-config.php file.<br/>'.PHP_EOL;
					echo 'Please visit this page for more information: <a href="http://codex.wordpress.org/Editing_wp-config.php#Database_character_set">http://codex.wordpress.org/Editing_wp-config.php#Database_character_set</a><br/>'.PHP_EOL;
					echo 'Here is a list of each table\'s character set from its creation:<br/>'.PHP_EOL;
					foreach ($tables as $table) {
						$res = $wpdb->get_results('SHOW CREATE TABLE '.$table[0], ARRAY_N);
						$res = $res[0][1];
						$pos = strpos($res, 'CHARSET');
						$substr = substr($res, $pos ); 
						echo 'Table: '.$table[0].' '.$substr.'<br/>'.PHP_EOL;
					} 
					echo '<br/>If you can not determine what to do, you may contact WP Stagecoach\'s premium support, and they may be able to help.</div>'.PHP_EOL;

					echo '<p>Because WordPress has used the UTF-8 character set since version 2.2, you may wish to just create all tables on the staging site with the UTF-8 character set--this may cause some character encoding problems if you import your database changes back from the staging site.</p>'.PHP_EOL;

				 	echo '<form method="POST" action="admin.php?page=wpstagecoach">'.PHP_EOL;
					echo '<input type="submit" value="Don\'t create a staging site right now; you can go investigate your database situation.">'.PHP_EOL;
					echo '</form>'.PHP_EOL;

				 	echo '<form method="POST" id="wpsc-step-form">'.PHP_EOL;
					$wpscpost_fields['wpsc-force-utf8'] = true;
					$nextstep = 2;

					echo '<input type="submit" value="Go ahead and create a staging with with UTF-8 encoding">'.PHP_EOL;

				}
				
			}
			unset($res);
			unset($tables);
			unset($table);
			unset($pos);
			define('DB_CHARSET', $charset);

		}
	}



//SELECT table_schema "Data Base Name", sum( data_length + index_length ) / 1024 / 1024 "Data Base Size in MB" FROM information_schema.TABLES where table_schema='wp_waxon.com'  GROUP BY table_schema;
	if( $normal === true ){
		global $wpdb;

		$DB_HOST = explode(':', DB_HOST);

		if(isset($DB_HOST[1]) ){
			$db = mysqli_connect($DB_HOST[0], DB_USER, DB_PASSWORD, DB_NAME, $DB_HOST[1]); // these are defined in wp-config.php
		} else
			$db = mysqli_connect($DB_HOST[0], DB_USER, DB_PASSWORD, DB_NAME); // these are defined in wp-config.php

		if( mysqli_errno($db) )
			die( 'couldn\'t connect to database "'.DB_NAME.'" on host "'.DB_HOST.'".  This should never happen. Error: '.mysqli_connect_error() );

		if (!mysqli_set_charset($db, DB_CHARSET)) {
			echo '<div class="wpscerror">Error loading character set "'.DB_CHARSET.'": '.mysqli_error($db).'<br/>'.PHP_EOL;
			echo 'Please find out what character set your database is using and then contact WP Stagecoach support.</div>'.PHP_EOL;
			die;
		}


		$db_size_query = 'SELECT sum( data_length + index_length ) / 1024 FROM information_schema.TABLES where table_schema="'.DB_NAME.'";';
		$res = mysqli_query($db, $db_size_query);
		$db_size = array_shift(mysqli_fetch_row($res));
		mysqli_free_result($res);

		if( $db_size / 1024 < 1)
			echo 'Database size: '.(int)$db_size.'KB<br/>';
		else
			echo 'Database size: '.(int)($db_size/1024).'MB<br/>';



		echo str_pad('',4096)."\n";
		ob_flush();
		flush();





		// get array of tables
		$res = mysqli_query($db, 'SHOW FULL TABLES;');
		while( $row = mysqli_fetch_row($res) ){
			$tables[$row[0]] = $row[1];
		}
		mysqli_free_result($res);


		//	create new gzip'd dump file

		$db_fh = gzopen(WPSTAGECOACH_DB_FILE,'w');

		$db_header = '-- Dump of '. DB_NAME.PHP_EOL;
		$db_header .= '-- Server version '. mysqli_get_server_info($db).PHP_EOL.PHP_EOL;
		$db_header .= '/*!40101 SET NAMES '.DB_CHARSET.' */;'.PHP_EOL;
		$db_header .= '/*!40101 SET character_set_client = '.DB_CHARSET.' */;'.PHP_EOL;
		$db_header .= PHP_EOL;


		$ret = fwrite( $db_fh, $db_header );
		if( !$ret ){ // unsuccessful write (0 bytes written)
			die('<div class="wpscerror"><b>Error: I couldn\'t write to the file <i>'.basename(WPSTAGECOACH_DB_FILE).'</i> in the directory <i>'.WPSTAGECOACH_TEMP_DIR.'</i>.<br/>Check your site\'s permissions.</b></div>');
		}

		// go through tables, row by row
		foreach ($tables as $table => $table_type) {
			if( $table_type == 'VIEW' ){
				//  currently we just skip view tables entirely.
				continue;
			}


			//	get number of rows in table
			$query = 'select count(*) from '.$table.';';
			

			$res = mysqli_query($db, $query);
			$result = mysqli_fetch_row($res);
			if( !is_array($result) ){
				wpsc_display_error('Error: $result is not an array!<br/>'.$query.'</br><pre>'.print_r($result,true).'</pre>');
				return false;
			}
			$num_rows = array_shift( $result );
			mysqli_free_result($res);

			echo 'dumping table '.$table.', with '.$num_rows.' rows.<br/>';
			echo str_pad('',4096)."\n";
			ob_flush();
			flush();

			// add table structure + size
			$table_dump = '--'.PHP_EOL.'-- Table structure for table `'.$table.'`, size '.$num_rows.' rows'.PHP_EOL.'--'.PHP_EOL.PHP_EOL;
			// add table dropping
			$table_dump .= 'DROP TABLE IF EXISTS `'.$table.'`;'.PHP_EOL;
			// add table creation
			$query = 'show create table '.$table.';';
			$res = mysqli_query($db, $query);
			$result = mysqli_fetch_row($res);
			if( !is_array($result) ){
				wpsc_display_error('Error: $result is not an array!<br/>'.$query.'</br><pre>'.print_r($result,true).'</pre>');
				return false;
			}
			$table_dump .= array_pop( $result ).';'.PHP_EOL;
			if( strpos($table_dump, 'ENGINE=MyISAM') )
				$ismyisam = true;

			mysqli_free_result($res);
			$table_dump .= PHP_EOL.'--'.PHP_EOL.'-- Dumping data for table `'.$table.'`'.PHP_EOL.'--'.PHP_EOL.PHP_EOL;
			$table_dump .= 'LOCK TABLES `'.$table.'` WRITE;'.PHP_EOL;
			if( isset($ismyisam) )
				$table_dump .= '/*!40000 ALTER TABLE `'.$table.'` DISABLE KEYS */;'.PHP_EOL;



			// get each row
			$query = 'select * from '.$table.';';
			$res = mysqli_query($db, $query);

			$num_of_iterations=5;

			if( $num_rows > 0 ){
				$i=0;

				while ( $i < $num_rows ) {

					$table_dump .= 'INSERT INTO '.$table.' VALUES ';
					$j=0;
					while ( $j < $num_of_iterations && $i < $num_rows) { 

						$row = mysqli_fetch_row($res);
						if( !is_array($row) ){
							wpsc_display_error('Error: $row is not an array!<br/>'.$query.'</br><pre>'.print_r($row,true).'</pre>');
							return false;
						}
						$table_dump .= '(';

						foreach ($row as $element) {

							$table_dump .= '\'';
							if( !is_numeric($element) )
								if( $table != $wpdb->prefix.'users' )
									$element = wpsc_recursive_unserialize_replace(WPSTAGECOACH_LIVE_SITE,WPSTAGECOACH_STAGE_SITE, WPSTAGECOACH_LIVE_PATH,WPSTAGECOACH_STAGE_PATH, $element);

							$element = mysqli_real_escape_string( $db, $element );
							$table_dump .= $element;

							$table_dump .= '\',';

						}
						$table_dump[ strlen($table_dump)-1 ] = ')';  // replace last ',' w/ ';'
						$table_dump .= ',';
						$i++;
						$j++;
					} // end for $j < $num_of_iterations



					$table_dump[ strlen($table_dump)-1 ] = ';';  // replace last ',' w/ ';'
					$table_dump .= PHP_EOL;

				} // end while $i < $num_rows
			} // end if num_rows > 0
		

			if( isset($ismyisam) )
				$table_dump .= '/*!40000 ALTER TABLE `'.$table.'` ENABLE KEYS */;'.PHP_EOL;

			$table_dump .= 'UNLOCK TABLES;'.PHP_EOL;
			mysqli_free_result($res);


			
			$table_dump .= PHP_EOL;
			fwrite($db_fh, $table_dump);
		}	// end $tables foreach


		mysqli_close($db);
		gzclose($db_fh);



		$db_size = (int)filesize(WPSTAGECOACH_DB_FILE)/1048576;
		if( (int)$db_size < 1 ){
			$db_size = (int)filesize(WPSTAGECOACH_DB_FILE)/1024;
			$units = 'KB';
		} else {
			$units = 'MB';
		}
		echo '<br/>Finished creating compressed database file, it is '.(int)$db_size.$units.' in size.<br/>';


		$nextstep = 3;


	}  // done with "normal" loop.



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

	echo 'Step '.$_POST['wpsc-step'].' -- finding all files.<br/>';
	/***************************************************************************************
	 *                               find all files by hand                                *
	 ***************************************************************************************/
	chdir( '../' );




	//	Get list of directories that have special needs!
	//		(right now this means just copying all files within these dirs)	
	$post_url = WPSTAGECOACH_CONDUCTOR.'/wpsc-special-dirs.php';
	$post_args = array(
		'wpsc-user'			=> $wpsc['username'],
		'wpsc-key'			=> $wpsc['apikey'],
		'wpsc-ver'			=> WPSTAGECOACH_VERSION,
		'wpsc-live-site'	=> WPSTAGECOACH_LIVE_SITE,
		'wpsc-stage-site'	=> WPSTAGECOACH_STAGE_SITE,
	);
	$post_result = wp_remote_post($post_url, array('timeout' => 120, 'body' => $post_args) );
	$result = wpsc_check_post_info('special_dirs', $post_url, $post_args, $post_result) ; // we got a bad response from the server...


	if( $result['result'] != 'OK' ){
		wpsc_display_error($result['info']);
		die;
	} else {
		echo '<br/>'.PHP_EOL;
		echo 'Okay, we have the list of special needs directories.<br/>'.PHP_EOL;
	}	

	echo str_pad('',4096)."\n";
	ob_flush();
	flush();



	global $special_dirs; // we're in a function already, so we need to declare it as a global
	$special_dirs = $result['info'];

	if( empty($special_dirs) ){
		$errmsg  = '<b>Error</b>: We got a corrupted list of directories with special needs from the WP Stagecoach website<br/>';
		$errmsg .= 'Please refresh this page (and confirm to resend information) to try again.<br/>';
		$errmsg .= 'If this problem persists, please contact <a href="mailto:support@wpstagecoach.com">WP Stagecoach support</a>';
		wpsc_display_error($errmsg);
		return false;
	}




	// need to add other special handling dirs..
	// active theme
	$temparr = explode('/', str_replace( getcwd(), '.', get_template_directory() ) );
	$last = array_pop($temparr);
	$special_dirs[implode('/', $temparr)][$last] = true;
	// and child
	if( get_template_directory() != get_stylesheet_directory() ){
		$temparr = explode('/', str_replace( getcwd(), '.', get_stylesheet_directory() ) );
		$last = array_pop($temparr);
		$special_dirs[implode('/', $temparr)][$last] = true;
	}





	echo '<p class="wpsc-info-from-crawling-fs">'.PHP_EOL;
	$list_of_files = array();
	$list_of_files['totalsize'] = 0;
	$list_of_files = wpsc_build_tar_list_rec( '.', $list_of_files );
	$list_of_files = wpsc_append_special_dirs_to_list( $list_of_files );
	echo '</p>'.PHP_EOL;
	
	echo str_pad('',4096)."\n";
	ob_flush();
	flush();


	$totalsize = $list_of_files['totalsize'];
	unset($list_of_files['totalsize']);

	if( isset($list_of_files['largefiles']) && is_array($list_of_files['largefiles']) ){
		$normal=false;
		$nextstep = 4;
		$largefiles = $list_of_files['largefiles'];
		unset($list_of_files['largefiles']);

		$errmsg  = 'These files are much larger than the files usually needed to create a staging site.<br/>'.PHP_EOL;
		$errmsg .= 'If you think these files are necessary for your staging site creation, please <b>check each file</b> to add it to the staging site.'.PHP_EOL;
		wpsc_display_error($errmsg);
	

		echo '<form method="POST" id="wpsc-step-form">'.PHP_EOL;
		foreach ($largefiles as $file) {
			echo '<input type="checkbox" name="wpsc-largefiles[]" value="'.base64_encode($file).'">'.$file.' (size: '.(int)(filesize($file)/1048576).'mb)<br/>'.PHP_EOL;
		}
		echo '<input type="submit" name="wpsc-step-form" value="Proceed">'.PHP_EOL;	

	} else {
		$nextstep = 5;
		if( ! $df = @disk_free_space(WPSTAGECOACH_TEMP_DIR) )
			$df = 4294967296;
		if( $totalsize > $df ){
			$errmsg  = 'There is not enough free space to create the tar file we need to create your staging site from.'.PHP_EOL;
			$errmsg .= 'Please clear up at least '.(int)( ($totalsize/1048576) - ($df/1048576) ).'mb and try again.'.PHP_EOL;
			wpsc_display_error($errmsg);
			return;
		}

		echo "<p>Okay; we've found all the files, next we'll make them into a single file.</p>";

	}
	echo str_pad('',4096)."\n";
	ob_flush();
	flush();

	if( !in_array('./wp-config.php', $list_of_files) ){ // we didn't find the wp-config.php file in the list of files.
		// check if it is in the parent directory of the WP home
		if( is_file('../wp-config.php') ){
			$list_of_files[] = '../wp-config.php';
			echo '<p>We are including the "wp-config.php" file from the above directory.</p>'.PHP_EOL;
			echo str_pad('',4096)."\n";
			ob_flush();
			flush();
		} else {
			$errmsg  = '<b>Problem</b>: We could not find your config file <b>wp-config.php</b><br/>'.PHP_EOL;
			$errmsg .= 'How are you running WordPress?<br/>'.PHP_EOL;
			$errmsg .= 'Unfortunately, we cannot proceed further.'.PHP_EOL;
			wpsc_display_error($errmsg);
			return false;	
		}
	}

	$temp_file = tempnam(WPSTAGECOACH_TEMP_DIR, 'wpsc_file_list_');	
	if( file_put_contents($temp_file, implode(PHP_EOL, $list_of_files) ) === false ){
		$errmsg  = '<b>Problem</b>: We could not write to the file <b>'.$temp_file.'</b><br/>'.PHP_EOL;
		$errmsg .= 'Please check the permissions on the '.dirname($temp_file).' directory.<br/>'.PHP_EOL;
		$errmsg .= 'Unfortunately, we cannot proceed further.'.PHP_EOL;
		wpsc_display_error($errmsg);
		return false;	
	}

	if( WPSC_DEBUG ){
		echo '<pre>list of files: ';
		print_r($temp_file);
		echo '</pre>';
	}
	echo str_pad('',4096)."\n";
	ob_flush();
	flush();

	$wpscpost_fields['wpsc-temp-file-name'] = $temp_file;
	$wpscpost_fields['wpsc-step'] = $nextstep;
	if( $nextstep == 5 ){
		echo 'Going to step '.$nextstep.' next.<br/>';
	} else
		$wpscpost_fields['wpsc-totalsize'] = $totalsize;
	if( $nextstep == 5 ){
		
	} else{
		$normal = false;
	}


	chdir( 'wp-admin' );

} // end of step 3



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

	echo 'Step '.$_POST['wpsc-step'].' -- adding large files to the list of files.<br/>';
	$step = $_POST['wpsc-step'];
	$totalsize = $_POST['wpsc-totalsize'];
	chdir('../'); // we start in wp-admin/

	if( !empty($_POST['wpsc-largefiles']) ){
		$totalsize = 0;
		$fh_temp = fopen($_POST['wpsc-temp-file-name'], 'a');
		foreach ($_POST['wpsc-largefiles'] as $file) {
			$file = base64_decode($file);
			$totalsize += filesize($file);

			fwrite($fh_temp, PHP_EOL.$file);

		}
		fclose($fh_temp);

		$totalsize += $_POST['wpsc-totalsize'];

	}

	if( ! $df = @disk_free_space('.') )
		$df = 4294967296;
	if( $totalsize > $df ){
		echo '<div class="wpscerror">There is not enough free space to create the tar file we need to create your staging site from.'.PHP_EOL;
		echo 'Please clear up at least '.(int)( ($totalsize/1048576) - ($df/1048576) ).'mb and try again.</div>'.PHP_EOL;
		return;
	}




	$nextstep = 5;
	echo 'Going to step '.$nextstep.' next.<br/>';
	$wpscpost_fields['wpsc-temp-file-name'] = $_POST['wpsc-temp-file-name'];
	$wpscpost_fields['wpsc-step'] = $nextstep;

} // end of step 4


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

	echo 'Step '.$_POST['wpsc-step'].' -- creating tar file.<br/>';
	$step = $_POST['wpsc-step'];
	/***************************************************************************************
	 *                               build tar file by hand                                *
	 ***************************************************************************************/

	require_once(dirname(__FILE__).'/Tar.php');
	chdir( '../' );


	$temp_file = $_POST['wpsc-temp-file-name'];
	$list_of_files = explode(PHP_EOL, file_get_contents($_POST['wpsc-temp-file-name']) );

	if( isset($wpsc['slow']) ){
		if(WPSC_DEBUG)
			echo 'We are using a small split size for the tar file to help make the tar file more reilably on slower servers.<br/>'.PHP_EOL;
		$split_size = 200;
	} else {
		$split_size = 1000;
	}

	if( empty( $_POST['wpsc-tar-done'] ) && empty($_POST['wpsc-tar-worked']) ){ // first time in this step--need to create the tar file and start splitting up the file list

		$tot_num_files = sizeof($list_of_files);
		$num_files = $tot_num_files;
		if( $num_files > $split_size ){
			// split off $split_size (1200) files and re-store the remain files in the $temp_file
			$chunk_of_files=array_splice($list_of_files,0,$split_size);
			file_put_contents($temp_file, implode(PHP_EOL, $list_of_files) );

		} else {
			$chunk_of_files = $list_of_files;
			$tar_done = true;
		}

		echo '<p>We are 0% done building the initial tar file.</p>';

		// show the list of files being worked on	
		echo '<a class="toggle">Show files being worked on</a><br/>'.PHP_EOL;
		echo '<div class="more" style="display: none">'.PHP_EOL;
		echo implode('<br/>'.PHP_EOL, $chunk_of_files);
		echo '</div>'.PHP_EOL;

		echo str_pad('',4096)."\n";
		ob_flush();
		flush();

		$tar = new Archive_Tar(WPSTAGECOACH_TAR_FILE);
		if( !$tar->create( $chunk_of_files ) ){
			echo '<p>Error: we couldn\'t create the initial file we need to make your staging site.</p>';
			$tar_worked = false;
			$nextstep = 6; // bailing b/c we couldn't create tar file
		} else {
			$tar_worked = true;
			$nextstep = 5;
			$files_done = $split_size;
		}

		echo str_pad('',4096)."\n";
		ob_flush();
		flush();

	} elseif( !isset($_POST['wpsc-tar-done']) || $_POST['wpsc-tar-done'] != true ) { // we're coming back around--we need to append to the tar file
		$nextstep = 5;
		$files_done = $_POST['wpsc-done-files'];
		$tot_num_files = $_POST['wpsc-tot-files'];



		$num_files = sizeof($list_of_files);
		if( $num_files > $split_size ){
			// split off $split_size (1000) files and re-store the remain files in the $temp_file
			$chunk_of_files=array_splice($list_of_files,0,$split_size);
			file_put_contents($temp_file, implode(PHP_EOL, $list_of_files) );
		} else {
			$chunk_of_files = $list_of_files;
			$tar_done = true;
		}

		echo '<p>We are '.(int)(($split_size / $tot_num_files ) * 100 ).'% done building the initial tar file.</p>';

		// show the list of files being worked on	
		echo '<a class="toggle">Show files being worked on</a><br/>'.PHP_EOL;
		echo '<div class="more" style="display: none">'.PHP_EOL;
		echo implode('<br/>'.PHP_EOL, $chunk_of_files);
		echo '</div>'.PHP_EOL;

		echo str_pad('',4096)."\n";
		ob_flush();
		flush();



		require_once('Tar.php');
		$tar = new Archive_Tar(WPSTAGECOACH_TAR_FILE);

		if( !$tar->add( $chunk_of_files ) ){
			echo '<p>Error: we couldn\'t append to the initial file we need to make your staging site.</p>';
			$tar_worked = false;
			$nextstep = 6; // bailing b/c we couldn't create tar file
		} else {
			$tar_worked = true;
			$nextstep = 5;
			$files_done += $split_size;
		}

		echo str_pad('',4096)."\n";
		ob_flush();
		flush();		
			
	} elseif( $_POST['wpsc-tar-done'] == true ) {  //  tar file is done! yay!
		$nextstep = 6;
		$tar_worked = $_POST['wpsc-tar-worked'];
		unlink($_POST['wpsc-temp-file-name']);

		####    checking that tar file is good
		if( $tar_worked == false || !is_file(WPSTAGECOACH_TAR_FILE) || filesize(WPSTAGECOACH_TAR_FILE) < 102400 ) {  // that's 100kB
			echo '<p>We were not able to create the initial file we need to make your staging site; please contact WP Stagecoach support.</p>';
			$tar_worked = false;
			die;
		} else {
			echo '<p>Great, we are finished creating the initial file we need to make your staging site!</p>';
			$tar_worked = true;
			flush();
		}
	}
	echo str_pad('',4096)."\n";
	ob_flush();
	flush();



	echo 'Going ';
	if( $step == $nextstep ){
		$wpscpost_fields['wpsc-temp-file-name'] = $temp_file;
		echo 'back ';
	}
	echo 'to step '.$nextstep.' next.<br/>';
	$wpscpost_fields['wpsc-tar-worked'] = $tar_worked;
	if( isset($tar_done) && $tar_done )				$wpscpost_fields['wpsc-tar-done'] = $tar_done;
	if( isset($tot_num_files) && $tot_num_files )	$wpscpost_fields['wpsc-tot-files'] = $tot_num_files;
	if( isset($files_done) && $files_done )			$wpscpost_fields['wpsc-done-files'] = $files_done;


} // end of step 5




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
	// have to figure out if we are uploading both files, or doing it over 2 steps.



	if( isset($_POST['wpsc-stop']) && !empty($_POST['wpsc-stop']) ){ // we are giving up instead of trying again and going on.
		$msg  = 'Sorry to see that you were not able to create a site! :-(<br/>'.PHP_EOL;
		$msg .= 'Will you please provide feedback below with any error messages or additional information that may be helpful to try and determine the cause?<br/>'.PHP_EOL;
		echo wpsc_display_feedback_form('create-error',
		array(
			'wpsc-stage' => $_POST['wpsc-create-stageurl'].'.wpstagecoach.com',
			'wpsc-live' => $_POST['wpsc-create-liveurl'],
			'wpsc-user' => $wpsc['username'],
			'wpsc-key' => $wpsc['apikey'],
			'wpsc-dest' => $_POST['wpsc-options']['dest'],
			),
		$msg
		);
		return;
	} else { // normal!
		echo 'Step '.$_POST['wpsc-step'].' -- uploading your ';

	}




	if( empty($_POST['wpsc-upload-repeat']) ){
		if( (filesize(WPSTAGECOACH_DB_FILE) + filesize(WPSTAGECOACH_TAR_FILE)) > 52428800 ){ // 50MB
			echo 'tar file'; // complete our message to user...
			$file_type='tar';
			$wpscpost_fields['wpsc-upload-repeat'] = true;
			$nextstep = 6;
		} else { // files are small enough to hopefully upload both in one step.
			echo 'files'; // complete our message to user...
			$nextstep = 7;
		}
	} else { // we are repeating step 6 to upload sql file.
		echo 'sql file'; // complete our message to user...
		$file_type='sql';
		$nextstep = 7;
	}

	echo ' to the WP Stagecoach server.<br/>';
	echo str_pad('',4096)."\n";
	ob_flush();
	flush();




	if( isset($_POST['wpsc-tar-worked']) && $_POST['wpsc-tar-worked'] != true && !isset($_POST['wpsc-upload-repeat']) ) {
		$errmsg  = 'Uh oh.  We have run into a problem: we were not able to build the necessary files to create your staging site.<br/>'.PHP_EOL;
		$errmsg .= 'Please submit the feedback form below to send information to WP Stagecoach support for help!<br/>'.PHP_EOL;
		wpsc_display_error($errmsg);

		echo wpsc_display_feedback_form('create-error',
		array(
			'wpsc-stage' => $_POST['wpsc-create-stageurl'].'.wpstagecoach.com',
			'wpsc-live' => $_POST['wpsc-create-liveurl'],
			'wpsc-user' => $wpsc['username'],
			'wpsc-key' => $wpsc['apikey'],
			'wpsc-dest' => $_POST['wpsc-options']['dest'],
			)
		);
		return false;
	}




	if( isset($file_type) ){
		$upload_array = array(0 => $file_type);
	} else {
		$upload_array = array('tar', 'sql');
	}

	foreach ($upload_array as $ftype) {
		// upload each file to the server

		$post_url = 'https://'.$_POST['wpsc-options']['dest'].'/wpsc-app-upload.php';
		$post_args = array(
			'wpsc-user'			=> $wpsc['username'],
			'wpsc-key'			=> $wpsc['apikey'],
			'wpsc-type'			=> $ftype,
			'wpsc-ver'			=> WPSTAGECOACH_VERSION,
			'wpsc-live-site'	=> WPSTAGECOACH_LIVE_SITE,
			'wpsc-stage-site'	=> WPSTAGECOACH_STAGE_SITE,
		);
		if($ftype == 'sql'){
			$post_args['file'] = '@'.WPSTAGECOACH_DB_FILE;
			$fsize = filesize(WPSTAGECOACH_DB_FILE);
		}
		else {
			$post_args['file'] = '@'.WPSTAGECOACH_TAR_FILE;
			$fsize = filesize(WPSTAGECOACH_TAR_FILE);
		}

		if($fsize > 1048576)
			$fsize = number_format($fsize/1048576).'MB';
		else
			$fsize = number_format($fsize/1024).'KB';
		echo 'Uploading your '.$ftype.' file (size: '.$fsize.') to the staging server now... '.PHP_EOL;
		echo str_pad('',4096)."\n";
		ob_flush();
		flush();

		$ch=curl_init();
		curl_setopt($ch,CURLOPT_URL, $post_url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_args);
		curl_setopt($ch, CURLOPT_TIMEOUT, 600);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);  // RETURN THE CONTENTS OF THE CALL
		$post_result['body'] = curl_exec($ch);

		if( !curl_errno($ch) )
			$post_result['response']['message'] = 'OK';
		else{
			$post_result['response']['message'] = 'BAD';
			$post_result['response']['code'] = curl_error($ch);
		}
		curl_close($ch);


		$result = wpsc_check_post_info('upload_'.$ftype.'_file', $post_url, $post_args, $post_result, false) ; // decoding response from the server  -- setting $display_output = false so we can display error below


		if($result['result'] == 'OK'){
			echo 'done!<br/>'.PHP_EOL;
		} else {
			$error = true;
			break;
		}


		// we're going to cheat to get the "import Changes" menu option up a step early.
		$wpsc['staging-site'] = true;	
		update_option('wpstagecoach', $wpsc);



		echo str_pad('',4096)."\n";
		ob_flush();
		flush();
	}

















	// if we run into problems, we can ask to retry it...
	if( isset($error) && $error == true ){
		$normal=false;
		$nextstep = 6;
		$errmsg  = 'We ran into a problem with the communication to the staging server.<br/>'.PHP_EOL;
		if( isset($result['info']) && !empty($result['info']) ){
			$errmsg .= 'We got the following information back from the server: <br/><br/>'.PHP_EOL;
			if( is_array($result) && isset($result['info']) )
				$errmsg .= '<b>'.print_r($result['info'],true).'</b><br/>'.PHP_EOL;
			else
				$errmsg .= '<b><pre>'.print_r($result,true).'</pre></b><br/>'.PHP_EOL;
		}
		$errmsg .= 'Would you like to try again?'.PHP_EOL;
		wpsc_display_error($errmsg);




		echo '<form method="POST" id="wpsc-step-form">';

		echo '  <input type="hidden" name="wpsc-retry" value="Yes"/>'.PHP_EOL;
		echo '<input type="submit" name="wpsc-retry-upload" value="Yes">'.PHP_EOL;	
		echo '<input type="submit" name="wpsc-stop" value="No">'.PHP_EOL;

		$db_size = (int)(filesize(WPSTAGECOACH_DB_FILE)/1024);
		if( $db_size / 1024 < 1)
			$db_size = (int)$db_size.'KB';
		else
			$db_size = (int)($db_size/1024).'MB';
		echo '<br/>Please note, the SQL file is '.$db_size.' in size, and the tar file is '.(int)(filesize(WPSTAGECOACH_TAR_FILE)/1048576).'MB in size.<br/>'.PHP_EOL;
		if( (filesize(WPSTAGECOACH_DB_FILE)/1048576) > 50 )
			echo 'The SQL file is abnormally large--this may be the cause of the failure.<br/>'.PHP_EOL;
		if( (filesize(WPSTAGECOACH_TAR_FILE)/1048576) > 100 )
			echo 'The tar file is abnormally large--this may be the cause of the failure.<br/>'.PHP_EOL;
	
	} elseif( isset($_POST['wpsc-cleanup']) && $_POST['wpsc-cleanup'] != 'No' ) { // things are great!

		/***************************************************************************************
		 *                                    delete old stuff                                 *
		 ***************************************************************************************/
		if(file_exists(WPSTAGECOACH_DB_FILE)) unlink(WPSTAGECOACH_DB_FILE);
		if(file_exists(WPSTAGECOACH_TAR_FILE)) unlink(WPSTAGECOACH_TAR_FILE);




		// we're going to cheat to get the "import Changes" menu option up a step early.
		$wpsc['staging-site'] = true;	
		update_option('wpstagecoach', $wpsc);


	}




} // end of step 6






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

	if( !isset($_POST['wpsc-cleanup']) ){ // we are on our first run
		echo 'Step '.$_POST['wpsc-step'].' -- creating your staging site!<br/>';


		//	go talk to the conductor and create a staging site
		$post_url = WPSTAGECOACH_CONDUCTOR.'/wpsc-create-site.php';
		$post_args = array(
			'wpsc-user'			=> $wpsc['username'],
			'wpsc-key'			=> $wpsc['apikey'],
			'wpsc-ver'			=> WPSTAGECOACH_VERSION,
			'wpsc-live-site'	=> WPSTAGECOACH_LIVE_SITE,
			'wpsc-stage-site'	=> WPSTAGECOACH_STAGE_SITE,
			'wpsc-dest'			=> $_POST['wpsc-options']['dest'],
		);

		foreach ($wpscpost_wpsc_options as $key => $value){
			$post_args['wpsc-options'][$key] = $_POST['wpsc-options'][$key];
		}



		$post_result = wp_remote_post($post_url, array('timeout' => 300, 'body' => $post_args) );
		$result = wpsc_check_post_info('create-site', $post_url, $post_args, $post_result) ; // check response from the server

		if( WPSC_DEBUG ){
			echo '<br/>'.$post_url.'?'.http_build_query($post_args);
			echo '<br/><br/>';
		}


		if( $result['result'] != 'OK' ){
			#wpsc_display_error( print_r($result['info'],true) );
			$error=true;
		} else { // success!


			delete_transient('wpstagecoach_sanity');


			echo '<p class="info">Here is your new staging site: <a target="_BLANK" href="http://'.WPSTAGECOACH_STAGE_SITE.'">http://'.WPSTAGECOACH_STAGE_SITE.'</a></p>';
			wpsc_display_sftp_login( $_POST['wpsc-create-stageurl'].'.wpstagecoach.com', $_POST['wpsc-create-liveurl'] );
			echo wpsc_display_feedback_form('create',
			array(
				'wpsc-stage' => $_POST['wpsc-create-stageurl'].'.wpstagecoach.com',
				'wpsc-live' => $_POST['wpsc-create-liveurl'],
				'wpsc-user' => $wpsc['username'],
				'wpsc-key' => $wpsc['apikey'],
				'wpsc-dest' => $_POST['wpsc-options']['dest'],
				)
			);
			if( is_file(WPSTAGECOACH_DB_FILE) )
				unlink(WPSTAGECOACH_DB_FILE);
			if( is_file(WPSTAGECOACH_TAR_FILE) )
				unlink(WPSTAGECOACH_TAR_FILE);

			// update the wpstagecoach options so we see the import menu
			$wpsc['staging-site'] = $_POST['wpsc-create-stageurl'].WPSTAGECOACH_DOMAIN;
			if( !update_option('wpstagecoach', $wpsc) ){
				$msg = 'Could not update the WordPress option for wpstagecoach.  This shouldn\'t happen.<br/>'.PHP_EOL;
				$msg .= 'You might consider checking your database\'s consistency.<br/>'.PHP_EOL;
				$msg .= 'Unfortunately debugging this further is beyond the scope of this plugin.<br/>'.PHP_EOL;
				wpsc_display_error($msg, false);
			}
		}  // end of $result['result'] OK/BAD

	} else { //  $_POST['wpsc-cleanup']  is set, and we have to figure out what to do with it

		if ( $_POST['wpsc-cleanup'] == 'No' ) {
			// something went wrong and we are stopping and displaying a feedback form

			$errmsg  = 'Sorry!  We were not able to complete the creation your staging site.<br/>'.PHP_EOL;
			$errmsg .= 'Please give us as much information as you can in the feedback form below, and it will be sent to WP Stagecoach support for help!<br/>'.PHP_EOL;
			wpsc_display_error($errmsg);

			// clean up our temporary staging site link	
			unset($wpsc['staging-site']);
			update_option('wpstagecoach', $wpsc);

			echo wpsc_display_feedback_form('error',
			array(
				'wpsc-stage' => $_POST['wpsc-create-stageurl'].'.wpstagecoach.com',
				'wpsc-live' => $_POST['wpsc-create-liveurl'],
				'wpsc-user' => $wpsc['username'],
				'wpsc-key' => $wpsc['apikey'],
				'wpsc-dest' => $_POST['wpsc-options']['dest'],
				), ''
			);
			return false;
		} elseif( $_POST['wpsc-cleanup'] == 'Yes' ) { // things are great!

			/***************************************************************************************
			 *                                    delete old stuff                                 *
			 ***************************************************************************************/
			if(file_exists(WPSTAGECOACH_DB_FILE)) unlink(WPSTAGECOACH_DB_FILE);
			if(file_exists(WPSTAGECOACH_TAR_FILE)) unlink(WPSTAGECOACH_TAR_FILE);

			// update the wpstagecoach options so we see the import menu
			$wpsc['staging-site'] = $_POST['wpsc-create-stageurl'].WPSTAGECOACH_DOMAIN;
			if( !update_option('wpstagecoach', $wpsc) ){
				$msg = 'Could not update the WordPress option for wpstagecoach.  This shouldn\'t happen.<br/>'.PHP_EOL;
				$msg .= 'You might consider checking your database\'s consistency.<br/>'.PHP_EOL;
				$msg .= 'Unfortunately debugging this further is beyond the scope of this plugin.<br/>'.PHP_EOL;
				wpsc_display_error($msg, false);
			}


			echo wpsc_display_feedback_form('create',
			array(
				'wpsc-stage' => $_POST['wpsc-create-stageurl'].'.wpstagecoach.com',
				'wpsc-live' => $_POST['wpsc-create-liveurl'],
				'wpsc-user' => $wpsc['username'],
				'wpsc-key' => $wpsc['apikey'],
				'wpsc-dest' => $_POST['wpsc-options']['dest'],
				)
			);

		} elseif( !empty($_POST['wpsc-cleanup']) ){  //  weird error -- "wpsc-cleanup" is neither Yes nor No...
			$errmsg  = 'The "$_POST[\'wpsc-cleanup\']" variable is set and contains a value that wasn\'t recognized.<br/>'.PHP_EOL;
			$errmsg .= 'Please report this problem to <a href="mailto:support@wpstagecoach.com">WP Stagecoach support</a> and include the following information:<br/>'.PHP_EOL;
			$errmsg .= '<pre>'.print_r($_POST, true).'</pre>'.PHP_EOL;
			wpsc_display_error($errmsg);
			// clean up our temporary staging site link	
			unset($wpsc['staging-site']);
			update_option('wpstagecoach', $wpsc);

			return false;	
		}
	} // end of $_POST['wpsc-cleanup'] being set










	if( isset($error) && $error == true ){
		$normal=false;
		$nextstep = 7;
		$errmsg  = 'We ran into a problem with the communication to the staging server.'.PHP_EOL;
		if( isset($result['info']) && !empty($result['info']) ){
			$errmsg .= 'We got the following information back from the server: <br/><br/>';
			if( is_array($result) && isset($result['info']) )
				$errmsg .= '<b>'.print_r($result['info'],true).'</b><br/>'.PHP_EOL;
			else
				$errmsg .= '<b><pre>'.print_r($result,true).'</pre></b><br/>'.PHP_EOL;
		}
		$errmsg .= 'Would you like to try again?'.PHP_EOL;
		echo '<br/><br/>';
		wpsc_display_error($errmsg);

		echo '<form method="POST" id="wpsc-step-form">';

		echo '<input type="submit" name="wpsc-retry-upload" value="Yes">'.PHP_EOL;	
		echo '<input type="submit" name="wpsc-cleanup" value="No">'.PHP_EOL;	

		echo '<br/>Please note, the SQL file is '.(int)(filesize(WPSTAGECOACH_DB_FILE)/1048576).'mb in size, and the tar file is '.(int)(filesize(WPSTAGECOACH_TAR_FILE)/1048576).'mb in size.<br/>'.PHP_EOL;
		if( (filesize(WPSTAGECOACH_DB_FILE)/1048576) > 50 )
			echo 'The SQL file is abnormally large--this may be the cause of the failure.<br/>'.PHP_EOL;
		if( (filesize(WPSTAGECOACH_TAR_FILE)/1048576) > 300 )
			echo 'The tar file is abnormally large--this may be the cause of the failure.<br/>'.PHP_EOL;
	
	} elseif( isset($_POST['wpsc-cleanup']) && $_POST['wpsc-cleanup'] != 'No' ) { // things are great!

		/***************************************************************************************
		 *                                    delete old stuff                                 *
		 ***************************************************************************************/
		if(file_exists(WPSTAGECOACH_DB_FILE)) unlink(WPSTAGECOACH_DB_FILE);
		if(file_exists(WPSTAGECOACH_TAR_FILE)) unlink(WPSTAGECOACH_TAR_FILE);



		echo wpsc_display_feedback_form('create',
		array(
			'wpsc-stage' => $_POST['wpsc-create-stageurl'].'.wpstagecoach.com',
			'wpsc-live' => $_POST['wpsc-create-liveurl'],
			'wpsc-user' => $wpsc_user_name,
			'wpsc-key' => $wpsc_api_key,
			'wpsc-dest' => $_POST['wpsc-options']['dest'],
			)
		);


	}
	if( !isset($error) )
		return;
} // end of step 7








/*******************************************************************************************
*                                                                                          *
*                                End of stepping form                                      *
*                                                                                          *
*******************************************************************************************/

if( 1 ){
	echo str_pad('',4096)."\n";
	ob_flush();
	flush();
	sleep(1);
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







/////////////////////////////////////
//    feedback form for repeat problems!
/////////////////////////////////////
if(isset($error) && $error == true && isset($_POST['wpsc-retry'])){
	$msg  = 'It looks like you\'ve run into some problems while trying to create your site.<br/>'.PHP_EOL;
	$msg .= 'Sorry to hear that!  Will you please provide feedback with any error messages below?<br/>'.PHP_EOL;
	echo wpsc_display_feedback_form('create',
	array(
		'wpsc-stage' => $_POST['wpsc-create-stageurl'].'.wpstagecoach.com',
		'wpsc-live' => $_POST['wpsc-create-liveurl'],
		'wpsc-user' => $wpsc['username'],
		'wpsc-key' => $wpsc['apikey'],
		'wpsc-dest' => $_POST['wpsc-options']['dest'],
		),
	$msg
	);
}

?>
