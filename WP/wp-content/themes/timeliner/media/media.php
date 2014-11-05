<div class="embed-responsive embed-responsive-16by9">
	<?php 
	$post_meta = get_post_custom();
	$iframe_standard = timeliner_get_smeta( 'iframe_standard', $post_meta, '' );
	if( !empty( $iframe_standard ) ){
		?>
		<iframe src="<?php echo esc_url( $iframe_standard ) ?>" class="embed-responsive-item"></iframe>
		<?php
	}
	else{
		the_post_thumbnail( 'full', array( 'class' => 'embed-responsive-item' ) );
	}
	?>
</div>