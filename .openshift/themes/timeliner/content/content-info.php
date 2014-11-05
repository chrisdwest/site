<div class="post-info">
	<ul class="post-meta">
		<li>
			<span class="fa fa-comment"></span>
			<a href="<?php the_permalink(); ?>">
				<?php comments_number( __( '0', 'timeliner'), __( '1', 'timeliner'), __( '%', 'timeliner') ); ?>
			</a>
		</li>
		<li>
			<span class="fa fa-eye"></span>
			<a href="javascript:;"><?php echo timeliner_get_post_extra( 'views' );?></a>
		</li>			
		<li>
			<span class="fa fa-heart"></span>
			<a href="javascript:;"  class="post-like" data-post_id="<?php the_ID(); ?>">
				<?php _e( 'Like (', 'timeliner' ); ?><span class="like-count"><?php echo timeliner_get_post_extra( 'likes' ) ?></span><?php _e( ')', 'timeliner' ); ?>
			</a>
		</li>		
	</ul>
</div>