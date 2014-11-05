<?php
/*=============================
	DEFAULT SINGLE
=============================*/
get_header();
the_post();

$post_pages = wp_link_pages( 
	array(
		'before' => '',
		'after' => '',
		'link_before'      => '<span>',
		'link_after'       => '</span>',
		'next_or_number'   => 'number',
		'nextpagelink'     => __( '&raquo;', 'timeliner' ),
		'previouspagelink' => __( '&laquo;', 'timeliner' ),			
		'separator'        => ' ',
		'echo'			   => 0
	) 
);

timeliner_increase_views();
?>
<section class="main-listing">
	<div class="container">
		<div class="row">
			<div class="col-md-8"> 
				<div class="white-block">
					<?php $post_format = get_post_format(); ?>
						<?php 
							if( timeliner_has_media() ){
								if( $post_format != 'link' && $post_format != 'quote' ){
									get_template_part( 'media/media', get_post_format() );
								}
								else if( $post_format == 'link' ){
									$image = wp_get_attachment_image_src( get_post_thumbnail_id(), 'post-thumbnail' );
									?>
									<div class="content-bg">
										<div class="bg-image" style="<?php echo has_post_thumbnail() ? 'background-image: url( '.esc_url( $image[0] ).' ); background-size: cover;' : ''; ?>">
											<div class="overlay">
												<?php get_template_part( 'media/media', 'link' ); ?>
											</div>
										</div>
									</div>
									<?php
								}
								else if( $post_format == 'quote' ){
									$image = wp_get_attachment_image_src( get_post_thumbnail_id(), 'post-thumbnail' );
									?>
									<div class="content-bg">
										<div class="bg-image" style="<?php echo has_post_thumbnail() ? 'background-image: url( '.esc_url( $image[0] ).' ); background-size: cover;' : ''; ?>">
											<div class="overlay">
												<?php get_template_part( 'media/media', 'quote' ); ?>
											</div>
										</div>
									</div>
									<?php
								}
							}
						?>
					<div class="post-inner">
						
						<div class="post-header">
							<h1 class="post-title"><?php the_title() ?></h1>
						<!--
							<ul class="list-unstyled post-meta">
								
								<li>
									<a href="javascript:;" class="post-like" data-post_id="<?php the_ID(); ?>"><i class="fa fa-heart"></i> <span class="like-count"><?php echo timeliner_get_post_extra( 'likes' );?></span></a>
								</li>
								<li>
									<i class="fa fa-eye"></i> <?php echo timeliner_get_post_extra( 'views' );?>
								</li>
								<li>
									<i class="fa fa-calendar"></i> <?php the_time( 'F j, Y' ) ?>
								</li>
								<li>
									<a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>"><i class="fa fa-user"></i> <?php echo get_the_author_meta('display_name'); ?></a>
								</li>
								<li>
									<i class="fa fa-pencil"></i> <?php echo timeliner_the_category(); ?>
								</li>
							</ul>
						-->
						</div>
						
						<div class="post-content">
							<?php the_content(); ?>							
						</div>
						<?php 
						$tags = timeliner_the_tags();
						if( !empty( $tags ) ):
						?>
							<div class="post-tags">
								<?php _e( '<i class="fa fa-tags"></i> Post tags: ', 'timeliner' ); echo $tags; ?>
							</div>
						<?php
						endif;
						?>
						<!--
						<hr />
						
						<div class="post-share">
							<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo rawurlencode( get_permalink() ); ?>" class="share facebook" target="_blank" title="<?php esc_attr_e( 'Share on Facebook', 'timeliner' ); ?>"><i class="fa fa-facebook"></i></a>
							<a href="http://twitter.com/intent/tweet?source=<?php echo esc_url( bloginfo( 'name' ) ); ?>&amp;text=<?php echo rawurlencode( get_the_excerpt() ); ?>&amp;url=<?php echo rawurlencode( get_permalink() ); ?>" class="share twitter" target="_blank" title="<?php esc_attr_e( 'Share on Twitter', 'timeliner' ); ?>"><i class="fa fa-twitter"></i></a>
							<a href="https://plus.google.com/share?url=<?php echo rawurlencode( get_permalink() ); ?>" class="share google" target="_blank" title="<?php esc_attr_e( 'Share on Google+', 'timeliner' ); ?>"><i class="fa fa-google"></i></a>
							<a href="http://www.linkedin.com/shareArticle?mini=true&amp;url=<?php echo rawurlencode( get_permalink() ); ?>&amp;title=<?php echo rawurlencode( get_the_title() ); ?>&amp;summary=<?php echo rawurlencode( get_the_excerpt() ); ?>&amp;source=<?php echo esc_url( bloginfo( 'name' ) ); ?>" class="share linkedin" target="_blank" title="<?php esc_attr_e( 'Share on LinkedIn', 'timeliner' ); ?>"><i class="fa fa-linkedin"></i></a>
							<a href="http://www.tumblr.com/share/link?url=<?php echo rawurlencode( get_permalink() ); ?>&amp;name=<?php echo rawurlencode( get_the_title() ); ?>&amp;description=<?php echo rawurlencode( get_the_excerpt() ); ?>" class="share tumblr" target="_blank" title="<?php esc_attr_e( 'Share on Tumblr', 'timeliner' ); ?>"><i class="fa fa-tumblr"></i></a>
						</div>
						-->
					</div>
				</div>
				<!--
				<?php if( !empty( $post_pages ) ): ?>
				<div class="white-block">
					<div class="post-inner">						
						<div class="pagination">
							<?php _e( 'Post pages: ', 'timeliner'); echo timeliner_link_pages( $post_pages ); ?>
						</div>
					</div>
				</div>
				<?php endif; ?>
				
				<?php
				$prev_post = get_previous_post();
				$next_post = get_next_post();
				if( !empty( $prev_post ) || !empty( $next_post ) ):
				?>
					<div class="next-prev">
						<div class="row">
							<div class="col-md-6 left-text">
								<?php
								if( !empty( $prev_post ) ){
									$prev_query = new WP_Query( array( 
										'post_type' => 'post',
										'post__in' => array( $prev_post->ID ),
										'posts_per_page' => 1,
										'ignore_sticky_posts' => true
									) );
									if( $prev_query->have_posts() ){
										while( $prev_query->have_posts() ){
											$prev_query->the_post();
											?>
											<a href="<?php the_permalink() ?>" class="btn">
												&laquo; <?php the_title() ?>
											</a>
											<?php
										}
									}
									wp_reset_query();
								}
								?>
							</div>
							<div class="col-md-6 right-text">
								<?php
								if( !empty( $next_post ) ){
									$next_query = new WP_Query( array( 
										'post_type' => 'post',
										'post__in' => array( $next_post->ID ),
										'posts_per_page' => 1,
										'ignore_sticky_posts' => true
									) );
									while( $next_query->have_posts() ){
										$next_query->the_post();
										?>
										<a href="<?php the_permalink() ?>" class="btn">
											<?php the_title() ?> &raquo;
										</a>
										<?php
									}
									wp_reset_query();
								}
								?>
							</div>
						</div>
					</div>
				<?php endif; ?>
				-->
				<!--
				<div class="white-block">
					<div class="post-inner">
						<div class="widget-title-wrap">
							<h6 class="widget-title">
								<?php _e( 'About ', 'timeliner' ); echo get_the_author_meta( 'display_name' );  ?>
							</h6>
						</div>
						
						<div class="author-info">
							<div class="row">
								<div class="col-md-2">
									<?php
									$avatar_url = timeliner_get_avatar_url( get_avatar( get_the_author_meta('ID'), 150 ) );
									if( !empty( $avatar_url ) ):
									?>
										<img src="<?php echo esc_url( $avatar_url ) ?>" class="img-responsive" alt="author"/>
									<?php
									endif;
									?>
								</div>
								<div class="col-md-10">
									<p><?php echo get_the_author_meta( 'description' ); ?></p>
								</div>
							</div>
						</div>
						
					</div>
				</div>
				-->	
				<?php comments_template( '', true ) ?>

			</div>
			<div class="col-md-4">
				<?php get_sidebar(); ?>
			</div>
		</div>
	</div>
</section>
<?php get_footer(); ?>