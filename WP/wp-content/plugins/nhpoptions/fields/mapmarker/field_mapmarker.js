jQuery(document).ready(function ($) {
    var header_clicked = false;
	
	$(document).on( "click", ".nhp-marker-repeat", function(){
		var field_id = $(this).attr('rel-id');
		createNewBox( field_id, $("#marker_container_"+field_id+" li").length );
	});	
	
	/* remove marker image */
	$(document).on( 'click', 'a[id^="nhp-marker-image-remove_"]', function () {
		var element = $(this);
		var id = element.attr( "id" ).split( "_" )[2];
		var field_id = element.attr( "id" ).split( "_" )[1];
		
		var image = $(this).prev().prev();
		image.fadeOut(200,function(){
			image.remove();
			save_data( field_id );
			element.text( "Add Image" );
			element.attr( "id", "nhp-marker-image-add_"+field_id+"_"+id );
		});
	});	
	
	/* upload marker image */
	$(document).on( 'click', 'a[id^="nhp-marker-image-add_"]', function(){
		header_clicked = true;
		object = $(this);
		tb_show('', 'media-upload.php?type=image&amp;post_id=0&amp;TB_iframe=true');
		return false;			
	});
	
	$(document).on( 'click', 'a[id^="nhp-remove-marker_"]', function(){
		var element = $(this).parents(".ui-state-default");
		var field_id = $(this).attr( "rel-id" );
		element.fadeOut(200,function(){
			element.remove();
			save_data( field_id );
		});
	});
	
	
	// Store original function
	window.original_send_to_editor = window.send_to_editor;
	window.send_to_editor = function (html) {
		if(header_clicked) {
			imgurl = $('img', html).attr('src');
			
			var field_id = object.attr( "id" ).split( "_" )[1];
			var id = object.attr( "id" ).split( "_" )[2];
			
			object.before('<img src="'+imgurl+'" />');
			object.text( "Remove Image" );
			object.attr( "id", "nhp-marker-image-remove_"+field_id+"_"+id );
			object.before("<br>");
			
			save_data( field_id );
			
			tb_remove();
			header_clicked = false;
		} else {
			window.original_send_to_editor(html);
		}
	}	
	
	/* save data on input change */
	$(document).on( 'change', 'input', function(){
		var field_id = $(this).attr("name").split( "_" )[1];
		save_data( field_id );
	});	

	function save_data( field_id ){
		var data = [];
		$("#marker_container_"+field_id+" li").each(function(){
			data.push({
				longitude: $(this).find('input[name="longitude_'+field_id+'"]').val() || "",
				latitude: $(this).find('input[name="latitude_'+field_id+'"]').val() || "",
				baloon_text: $(this).find('input[name="baloon_'+field_id+'"]').val() || "",
				icon: $(this).find('img').attr("src") || ""
			})
		});
		
		$("#"+field_id).val( JSON.stringify( data ) );
	}
	
	function createNewBox( field_id, newRowNum ){		
		$("#marker_container_"+field_id).append(
			'<li class="ui-state-default">'+
				'<table>'+
					'<tr>' +
						'<td>' +
							'Marker longitude:<input type="text" name="longitude_'+field_id+'" /><br />'+
							'Marker latitude:<input type="text" name="latitude_'+field_id+'" /><br />'+
							'Marker baloon text:<input type="text" name="baloon_'+field_id+'" /><br />'+
							'Marker icon:<br />'+
							'<a href="javascript:;" id="nhp-marker-image-add_'+field_id+'_'+newRowNum+'" class=button-secondary">Add Image</a>'+
							'<br>' +
							'<a href="javascript:void(0);" id="nhp-remove-marker_'+field_id+'_'+newRowNum+'" class="button-secondary" rel-id="'+field_id+'">Remove Marker</a>' +
						'</td>' +
					'</tr>'+
				'</table>'+
			'</li>'		
		);
	}
});