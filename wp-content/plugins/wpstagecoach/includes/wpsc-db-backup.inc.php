<?php

if ( ! defined('ABSPATH') ) {
	die('Please do not load this file directly.');
}




set_time_limit(0);
ini_set("max_execution_time", "0");
ini_set('memory_limit', '-1');

echo 'Creating database backup.<br/>';
echo str_pad('',4096)."\n";
ob_flush();
flush();

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

				echo '<p>Because WordPress has used the UTF-8 character set since version 2.2, you may wish to just create the backup of all tables with the UTF-8 character set--however, this may cause some character encoding problems if you need to restore your database from this backup.</p>'.PHP_EOL;

			 	echo '<form method="POST" action="admin.php?page=wpstagecoach">'.PHP_EOL;
				echo '<input type="submit" value="Stop importing so you can go investigate your database situation.">'.PHP_EOL;
				echo '</form>'.PHP_EOL;

			 	echo '<form method="POST" id="wpsc-step-form">'.PHP_EOL;
				$wpscpost_fields['wpsc-force-utf8'] = true;

				echo '<input type="submit" value="Go ahead and create a database backup with UTF-8 encoding">'.PHP_EOL;
			}
			
		}
		unset($res);
		unset($tables);
		unset($table);
		unset($pos);
		define('DB_CHARSET', $charset);
		return;

	}
}




// proceed with dump


//SELECT table_schema "Data Base Name", sum( data_length + index_length ) / 1024 / 1024 "Data Base Size in MB" FROM information_schema.TABLES where table_schema='wp_waxon.com'  GROUP BY table_schema;
global $wpdb;

$DB_HOST = explode(':', DB_HOST);

if(isset($DB_HOST[1]) ){
	$db = mysqli_connect($DB_HOST[0], DB_USER, DB_PASSWORD, DB_NAME, $DB_HOST[1]); // these are defined in wp-config.php
} else {
	$db = mysqli_connect($DB_HOST[0], DB_USER, DB_PASSWORD, DB_NAME); // these are defined in wp-config.php
}

if( mysqli_errno($db) ){
	wpsc_display_error( 'couldn\'t connect to database "'.DB_NAME.'" on host "'.DB_HOST.'".  This should never happen. Error: '.mysqli_connect_error() );
	return;
}

if (!mysqli_set_charset($db, DB_CHARSET)) {
	$msg = 'Error loading character set "'.DB_CHARSET.'": '.mysqli_error($db).'<br/>'.PHP_EOL;
	$msg .= 'Please find out what character set your database is using and then contact WP Stagecoach support.'.PHP_EOL;
	wpsc_display_error($msg);
	return;
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
while( $row = mysqli_fetch_row($res) )
	$tables[$row[0]] = $row[1];
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
	$msg = '<b>Error: I couldn\'t write to the file <i>'.basename(WPSTAGECOACH_DB_FILE).'</i> in the directory <i>'.WPSTAGECOACH_TEMP_DIR.'</i>.<br/>'.PHP_EOL;
	$msg .= 'Check your site\'s permissions.</b>'.PHP_EOL;
	wpsc_display_error($msg);
	return;	
}

// create a special view variable to add on to the end
$view_dump = '';

// go through tables, row by row
foreach ($tables as $table => $table_type) {
	if( $table_type == 'VIEW' ){


		
		$res = mysqli_query($db, 'show create table '.$table.';' );
		$view_create = mysqli_fetch_row($res);

		$view_dump .= '--'.PHP_EOL.'-- Final view structure for view `'.$view_create[0].'`'.PHP_EOL.'--'.PHP_EOL.PHP_EOL;

		$view_dump .= '/*!50001 DROP TABLE IF EXISTS `'.$view_create[0].'`*/;'.PHP_EOL;
		$view_dump .= '/*!50001 DROP VIEW IF EXISTS `'.$view_create[0].'`*/;'.PHP_EOL;
		$view_dump .= '/*!50001 SET @saved_cs_client          = @@character_set_client */;'.PHP_EOL;
		$view_dump .= '/*!50001 SET @saved_cs_results         = @@character_set_results */;'.PHP_EOL;
		$view_dump .= '/*!50001 SET @saved_col_connection     = @@collation_connection */;'.PHP_EOL;

		$temp_view_dump = strstr($view_create[1], ' VIEW `', true);
		/*!50001 CREATE ALGORITHM=eg.UNDEFINED */
		$view_dump .= '/*!50001 '.strstr($temp_view_dump, ' DEFINER=', true).' */'.PHP_EOL;
		/*!50013 DEFINER=`eg.root`@`eg.localhost` SQL SECURITY DEFINER */
		$view_dump .= '/*!50001'.strstr($temp_view_dump, ' DEFINER=').' */'.PHP_EOL;
		/*!50001 VIEW `view` AS select query */;
		$view_dump .= '/*!50001'.strstr($view_create[1], ' VIEW `').' */;'.PHP_EOL;

		$view_dump .= '/*!50001 SET character_set_client      = @saved_cs_client */;'.PHP_EOL;
		$view_dump .= '/*!50001 SET character_set_results     = @saved_cs_results */;'.PHP_EOL;
		$view_dump .= '/*!50001 SET collation_connection      = @saved_col_connection */;'.PHP_EOL;
		$view_dump .= PHP_EOL;				
		
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
					$table_dump .= mysqli_real_escape_string( $db, $element );

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
#return;	
}	// end $tables foreach


// append views at the end of the database dump
if( !empty($view_dump) ){
	fwrite($db_fh, $view_dump);

}



mysqli_close($db);
gzclose($db_fh);



$db_size = (int)filesize(WPSTAGECOACH_DB_FILE)/1048576;
if( (int)$db_size < 1 ){
	$db_size = (int)filesize(WPSTAGECOACH_DB_FILE)/1024;
	$units = 'KB';
} else {
	$units = 'MB';
}
echo 'Finished creating compressed database bacup, it is '.(int)$db_size.$units.' in size.<br/>';

echo str_pad('',4096)."\n";
ob_flush();
flush();






?>
