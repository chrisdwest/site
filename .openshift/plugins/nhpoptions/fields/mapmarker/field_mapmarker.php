<?php
class NHP_Options_mapmarker extends NHP_Options	
	{	
	/**
	 * Field Constructor.
	 *
	 * Required - must call the parent constructor, then assign field and value to vars, and obviously call the render field function
	 *
	 * @since NHP_Options 1.0
	 */ 
	function __construct($field = array() , $value = '', $parent = '')
		{
		parent::__construct($parent->sections, $parent->args, $parent->extra_tabs);
		$this->field = $field;
		$this->value = $value;
		} //function
	/**
	 * Field Render Function.
	 *
	 * Takes the vars and outputs the HTML for the field in the settings
	 *
	 * @since NHP_Options 1.0
	 */
	function render()
		{
		$markers = json_decode( $this->value );
		echo '<input type="hidden" id="' . $this->field['id'] . '" name="' . $this->args['opt_name'] . '[' . $this->field['id'] . ']" value=\' '.$this->value.' \'/>';
		echo '<ul id="marker_container_'.$this->field['id'].'">';
		$counter = 0;
		
		foreach( (array)$markers as $marker ){
			echo '<li class="ui-state-default">
					<table>
						<tr>
							<td>
								Marker longitude:<input type="text" name="longitude_'.$this->field['id'].'" value="'.$marker->longitude.'" /><br />
								Marker latitude:<input type="text" name="latitude_'.$this->field['id'].'" value="'.$marker->latitude.'" /><br />
								Marker baloon text:<input type="text" name="baloon_'.$this->field['id'].'" value="'.$marker->baloon_text.'" /><br />
								Marker icon:<br />
								<img src="'.$marker->icon.'" /><br />
								<a href="javascript:;" id="nhp-marker-image-remove_'.$this->field['id'].'_'.$counter.'" class=button-secondary">Remove Image</a>
								<br>
								<a href="javascript:void(0);" id="nhp-remove-marker_'.$this->field['id'].'_'.$counter.'" class="button-secondary" rel-id="'.$this->field['id'].'">Remove Marker</a>
							</td>
						</tr>
					</table>
				</li>';
				
			$counter++;
		}
		echo '</ul>';
		echo ' <a href="javascript:;" class="nhp-marker-repeat button-secondary" rel-id="' . $this->field['id'] . '">' . __('Add New Map Marker', 'nhp-opts') . '</a>';
		echo (isset($this->field['desc']) && !empty($this->field['desc'])) ? '<br/><br/><span class="description">' . $this->field['desc'] . '</span>' : '';
			
		} //function
	/**
	 * Enqueue Function.
	 *
	 * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
	 *
	 * @since NHP_Options 1.0
	 */
	function enqueue()
		{
		wp_enqueue_script('nhp-map-jquery', NHP_OPTIONS_URL . 'fields/mapmarker/jquery-json.js');	
		wp_enqueue_script('nhp-opts-marker-repeat-js', NHP_OPTIONS_URL . 'fields/mapmarker/field_mapmarker.js');	
		} //function
	} //class

?>