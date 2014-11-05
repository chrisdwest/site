<?php

if ( ! defined('ABSPATH') ) {
	die('Please do not load this file directly.');
}


set_time_limit(0);
ini_set("max_execution_time", "0");
ini_set('memory_limit', '-1');



$changes = get_option('wpstagecoach_retrieved_changes');

if( $changes == false ){
	$msg = 'Somehow we were unable to retrieve the changes from the WordPress database...<br/>'.PHP_EOL;
	$msg .= 'Please try again, and if it doesn\'t work, then please check that your database is not corrupt, and if so, you may contact WP Stagecoach support.<br/>'.PHP_EOL;
	wpsc_display_error($msg);
	return false;
}

// pull out file changes and put them into a string.
if( isset($changes['file']) && !empty($changes['file']) ){

	$file_output = '<p><input type="checkbox" name="files" id="checkFiles" class="overview"> Import all file changes</p>';
	$file_output .= '<p>or <a href="#" onclick="toggle_visibility(\'files\');">select specific file changes to import</a></p>';

	$file_output .= '<div id="files" style="display: none;">';
	$file_output .= '<fieldset id="wpsc_files">';


	foreach ( $changes['file'] as $action_type => $file_list){
		if( !empty( $file_list ) ){		
			$display_file_output=1;
			$file_output .= '<h4>'.$action_type.' files</h4>'."\n";
			$file_output .= '<fieldset id="wpsc_'.$action_type.'_files">';
			$file_output .= make_select_all_links("",'wpsc_'.$action_type.'_files');
			foreach ($file_list as $file) {
				$file_output .= '<input type="checkbox" class="file overview" name="wpsc-'.$action_type.'[]" value="'.base64_encode($file).'"> '.$file."<br/>\n";
			}
		}
		$file_output .= '</fieldset>'; // end wpsc_'.$action_type.'_files fieldset
	} ##  end of action_type foreach loop
	unset($changes['file']);
	$file_output .= '</fieldset>';   //  end of wpsc_files fieldset
	$file_output .= '</fieldset>';   //  end of all fieldset
	$file_output .= '<fieldset id="wpsc_all">';
} // end file changes -- printout below



// pull out database changes and put them into a string.
if( isset($changes['db']) && !empty($changes['db']) ){
	$display_db_output = 1;

	$db_output = '<p><input type="checkbox" name="database" id="checkTables" class="overview"> Import all database changes</p>'.PHP_EOL;
	$db_output .= '<p>or <a href="#" onclick="toggle_visibility(\'tables\');">select specific database changes to import</a></p>'.PHP_EOL;


	$db_output .= '<div id="tables" style="display: none;">';
	$db_output .= '<fieldset id="wpsc_db">';
	$db_output .= '<p class="info"><b>WARNING!  Selecting some database changes but not others can make a huge mess of your site.  Only do this if you know what you are doing.</b></p>';

	$db_output .= 'The following tables have had changes made in them:<br/>';
	foreach ($changes['db'] as $table_name => $table) {


		$db_output .= '<h4>table: '.$table_name.'</h4>';

		$db_output .= make_select_all_links("","wpsc_db_$table_name");
		$db_output .= '<fieldset id="wpsc_db_'.$table_name.'">';
		foreach ($table as $row) {




			$db_output .= '<input type="checkbox" class="table overview" name="wpsc-db[]" value="'.base64_encode($row).'">';
			switch ( esc_html(substr($row, 0, 6)) ){
				case 'INSERT':
					switch($table_name){
						case 'usermeta':
							$db_output .= "insert: ";
							$output = preg_split('/.[)(]/i', $row);
							$column = preg_split('/,/i', $output[1]);
							$value = preg_split('/,/i', $output[3]);
							$db_output .= "'".trim($value[1], " \t\n\r\0\x0B'`" )."'=>'";
							$db_output .= trim($value[2], " \t\n\r\0\x0B'`" )."', for user_id ";
							$db_output .= trim($value[0], " \t\n\r\0\x0B'`" )."\n";
						break;
						case 'posts':
							$db_output .= "new post: '";
							$output = preg_split('/.[)(]/i', $row);
							$column = preg_split('/,/i', $output[1]);
							$value = preg_split('/,/i', $output[3]);
							$db_output .= trim($value[5], " \t\n\r\0\x0B'`" )."', by user_id:";
							$db_output .= trim($value[0], " \t\n\r\0\x0B'`" )."'\n";

						break;						
						case 'postmeta':
							$db_output .= "insert: ";
							$output = preg_split('/.[)(]/i', $row);
							$column = preg_split('/,/i', $output[1]);
							$value = preg_split('/,/i', $output[3]);
							$db_output .= "'".trim($value[1], " \t\n\r\0\x0B'`" )."'=>'";
							$db_output .= trim($value[2], " \t\n\r\0\x0B'`" )."', for user_id ";
							$db_output .= trim($value[0], " \t\n\r\0\x0B'`" )."\n";
						break;
						default:
							$db_output .= "insert: ";
							$output = preg_split('/.[)(]/i', $row);
							$column = preg_split('/,/i', $output[1]);
							$value = preg_split('/,/i', $output[3]);
							$db_output .= trim($column[0], " \t\n\r\0\x0B'`" )."\" = \"";
							$db_output .= trim($value[0], " \t\n\r\0\x0B'`" )."\"";
						break;
					}
					$db_output .= "<br/>\n";
					break;
				case 'UPDATE':
					$db_output .= "modify: \"";
					switch($table_name){
						case 'posts':
							$output = preg_split('/SET/i', $row);
							$output = preg_split('/\', `/', (string)$output[1]);
							if( sizeof($output) > 2 ){
								$db_output .= trim($output[4], " \t\n\r\0\x0B'`" ).'", ';
								$db_output .= '"'.trim(substr($output[2],0,100), " \t\n\r\0\x0B'`" ).'...", ';
								$output = preg_split('/WHERE/i', $row);
								$output = preg_split('/=/', (string)$output[1]);
								$db_output .= " where: \"";
								$db_output .= trim($output[0], " \t\n\r\0\x0B'`" )."\" = \"";
								$db_output .= trim($output[1], " \t\n\r\0\x0B'`" );
								$db_output .= "\"<br/>\n";
							} else {
								$output = preg_split('/SET/i', $row);
								$db_output .= $output[1];
								$db_output .= "\"<br/>\n";
							}


						break;
						default:
							$output = preg_split('/SET/i', $row);
							$output = preg_split('/=/', (string)$output[1]);
							$db_output .= trim($output[0], " \t\n\r\0\x0B'`" )."\" ";
							if(isset($output[1]) && isset($output[2])){
								$output = preg_split('/WHERE/i', $output[1].'='.$output[2]);
								$output = preg_split('/=/', (string)$output[1]);
								$db_output .= " where: \"";
								$db_output .= trim($output[0], " \t\n\r\0\x0B'`" )."\" = \"";
								$db_output .= trim($output[1], " \t\n\r\0\x0B'`" );
							} 
							$db_output .= "\"<br/>\n";

						break;
					}
					break;
				case 'DELETE':
					$db_output .= "delete: where \"";
					$output = preg_split('/WHERE/i', $row);
					$output = preg_split('/=/', (string)$output[1]);

					$db_output .= trim($output[0], " \t\n\r\0\x0B'`" )."\" = \"";
					$db_output .= trim($output[1], " \t\n\r\0\x0B'`" )."\"";
					$db_output .= "<br/>\n";
					break;
			}




		} # end of $row foreach  loop
		$db_output .= '</fieldset>';  # end of table fieldset
	} # end of $table foreach loop
	$db_output .= '</fieldset>';  # end of database fieldset
	$db_output .= '</div><!-- #tables -->'.PHP_EOL;


} // end database changes -- printout below




//  print out all the strings created above
if( isset($display_file_output) || isset($display_db_output) ){
	echo '<h2>Select the changes you want to import from the Staging Site</h2>';
	echo '<p><form id="wpsc" method="post">';
	echo '<fieldset id="wpsc_all">';

	echo '<p><input type="checkbox" name="all" id="checkAll"> Import all changes</p>';

	if( isset($display_file_output) ){
		echo $file_output;
	}

	if( isset($display_db_output) ){
		echo $db_output;
	}



	echo '</fieldset>';  # end of wpsc_all fieldset

	echo '<div id="message" class="wpscerror">';
	echo '<p><b>Please back up your site before you import changes from your staging site!</b></p></div>';
	echo '<p>';
	echo '<input type="submit" name="wpsc-import-changes" value="Import Changes" />';
	echo '</form></p>';

}




function make_select_all_links($message,$item){
return <<<EOF
$message
<a rel="$item" href="#select_all">Select All</a>
<a rel="$item" href="#select_none">Select None</a>
<a rel="$item" href="#invert_selection">Invert Selection</a><br/>
EOF;
} ?>

<script type="text/javascript">
<!--
    function toggle_visibility(id) {
       var e = document.getElementById(id);
       if(e.style.display == 'block')
          e.style.display = 'none';
       else
          e.style.display = 'block';
    }

//-->

</script>
<?php ?>
