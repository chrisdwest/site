<div class="embed-responsive embed-responsive-16by9">
	<?php
	$post_meta = get_post_custom();
	$video_url = timeliner_get_smeta( 'video', $post_meta, '' );
	$video_type = timeliner_get_smeta( 'video_type', $post_meta, '' );
	if( !empty( $video_url ) ){
		if( $video_type == 'self'){
			?>
			<video controls class="embed-responsive-item">
				<source src="<?php echo esc_url( $video_url ); ?>" type="video/ogg">
				<?php _e( 'Your browser does not support the video tag.', 'timeliner' ) ?>;
			</video>
			<?php
		}
		else{
			?>
			<iframe src="<?php echo esc_url( $video_url ); ?>" class="embed-responsive-item"></iframe>
			<?php
		}
	}
	else{
		the_post_thumbnail( 'full', array( 'class' => 'embed-responsive-item' ) );
	}
	?>
</div>