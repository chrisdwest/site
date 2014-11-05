<li>
	<div class="widget-image-thumb">
		<a href="<?php the_permalink(); ?>">
			<?php
			if( has_post_thumbnail() ){
				the_post_thumbnail();
			}else{
				$post_format = get_post_format();
				?>
				<div class="fake-thumb-wrap">
					<div class="post-format post-format-<?php echo !empty( $post_format ) ? $post_format : 'standard'; ?>"></div>
				</div>
				<?php
			}							
			?>
		</a>
	</div>
	<div class="widget-text">
		<a href="<?php the_permalink(); ?>"><?php get_the_title() ? the_title() : the_ID(); ?></a>
		<small><?php comments_number( __( '0 comments', 'timeliner'), __( '1 comment', 'timeliner'), __( '% comments', 'timeliner') ); ?></small>
	</div>
	<div class="clearfix"></div>
</li>