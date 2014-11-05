<?php
/*=============================
	DEFAULT BLOG LISTING PAGE
=============================*/ 
get_header();
global $wp_query;
$args = array_merge( $wp_query->query_vars, array( 'post_type' => 'post' ) );
$main_query = new WP_Query( $args );

$cur_page = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1; //get curent page
$page_links_total =  $main_query->max_num_pages;
$page_links = paginate_links( 
	array(
		'base' => add_query_arg( 'paged', '%#%' ),
		'prev_next' => true,
		'end_size' => 2,
		'mid_size' => 2,
		'total' => $page_links_total,
		'current' => $cur_page,	
		'prev_next' => false,
		'type' => 'array'
	)
);	
$pagination = timeliner_format_pagination( $page_links );
?>
<section class="main-listing">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<?php 
				if( $main_query->have_posts() ){
					$year = '';
					$counter = 1;
					?>
					<section id="cd-timeline" class="cd-container">
					<?php
					while( $main_query->have_posts() ){
						$main_query->the_post(); 
						if( $year != get_the_time('Y') ){
						$year = get_the_time('Y');
						?>
						<div class="cd-timeline-block year-block">
							<div class="cd-timeline-year">
								<h2><?php echo $year; ?></h2>
							</div> <!-- cd-timeline-img -->
						</div> <!-- cd-timeline-block -->
						<?php
						}
						$post_format = get_post_format();		
						?>
						
						<div id="post-<?php the_ID(); ?>" <?php post_class( 'cd-timeline-block '.($counter == 2 ? 'even' : '').'' ); ?>>
							<div class="cd-timeline-img">
								<h2><?php the_time('d'); ?></h2>
								<p><?php the_time('M'); ?></p>
							</div> <!-- cd-timeline-img -->
							<?php if( $post_format !== 'link' && $post_format !== 'quote' ): ?>
								<div class="cd-timeline-content <?php echo is_sticky() ? 'sticky-post' : '' ?>">
									<div class="cd-content clearfix">
										<?php if( timeliner_has_media() ){
											get_template_part( 'media/media', get_post_format() );
										}?> 
										<div class="content-padding">
											<a href="<?php the_permalink() ?>" class="post-title"><h2><?php the_title(); ?></h2></a>
											<!-- 
											<ul class="list-unstyled post-meta">
											
												<li>
													<a href="javascript:;" class="post-like" data-post_id="<?php the_ID(); ?>"><i class="fa fa-heart"></i> <span class="like-count"><?php echo timeliner_get_post_extra( 'likes' );?></span></a>
												</li>
												<li>
													<i class="fa fa-eye"></i> <?php echo timeliner_get_post_extra( 'views' );?>
												</li>
												<li>
													<a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>"><i class="fa fa-user"></i> <?php echo get_the_author_meta('display_name'); ?></a>
												</li>
												<li>
													<i class="fa fa-pencil"></i> <?php echo timeliner_the_category(); ?>
												</li>
											</ul>
											-->
											<div class="post-content">
												<?php the_excerpt(); ?>
											</div>
											<a href="<?php the_permalink() ?>" class="btn btn-default cd-read-more"><?php _e( 'Read more', 'timeliner' ) ?></a>
											<div class="clearfix"></div>
											<div class="cd-author">
												<?php
												$url = timeliner_get_avatar_url( get_avatar( get_the_author_meta( 'ID' ), 150 ) );
												if( !empty( $url ) ):
												?>
													<img src="<?php echo esc_url( $url ); ?>" class="media-object img-responsive" alt="" />
												<?php
												endif;
												?>
											</div>
										</div>
									</div>
								</div> <!-- cd-timeline-content -->
							<?php elseif( $post_format == 'link' ):?>
								<?php $image = wp_get_attachment_image_src( get_post_thumbnail_id(), 'post-thumbnail' ); ?>
								<div class="cd-timeline-content content-bg <?php echo is_sticky() ? 'sticky-post' : '' ?>">
									<div class="bg-image" style="<?php echo has_post_thumbnail() ? 'background-image: url( '.esc_url( $image[0] ).' ); background-size: cover;' : ''; ?>">
										<div class="overlay">
											<?php if( timeliner_has_media() ): ?>
											<ul class="list-unstyled post-meta">
												<li>
													<a href="javascript:;" class="post-like" data-post_id="<?php the_ID(); ?>"><i class="fa fa-heart"></i> <span class="like-count"><?php echo timeliner_get_post_extra( 'likes' );?></span></a>
												</li>
												<li>
													<i class="fa fa-eye"></i> <?php echo timeliner_get_post_extra( 'views' );?>
												</li>
												<li>
													<a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>"><i class="fa fa-user"></i> <?php echo get_the_author_meta('display_name'); ?></a>
												</li>
												<li>
													<i class="fa fa-pencil"></i> <?php echo timeliner_the_category(); ?>
												</li>
											</ul>											
											<?php
												get_template_part( 'media/media', 'link' );
											endif;
											?>
										</div>
									</div>
									<div class="cd-content">
										<div class="cd-author">
											<?php
											$url = timeliner_get_avatar_url( get_avatar( get_the_author_meta( 'ID' ), 150 ) );
											if( !empty( $url ) ):
											?>
												<img src="<?php echo esc_url( $url ); ?>" class="media-object img-responsive" alt="" />
											<?php
											endif;
											?>
										</div>
									</div>
								</div>
							<?php elseif( $post_format == 'quote' ): ?>
								<?php $image = wp_get_attachment_image_src( get_post_thumbnail_id(), 'post-thumbnail' ); ?>
								<div class="cd-timeline-content content-bg <?php echo is_sticky() ? 'sticky-post' : '' ?>">
									<div class="bg-image" style="<?php echo has_post_thumbnail() ? 'background-image: url( '.esc_url( $image[0] ).' ); background-size: cover;' : ''; ?>">
										<div class="overlay">
											<?php if( timeliner_has_media() ): ?>
											<ul class="list-unstyled post-meta">
												<li>
													<a href="javascript:;" class="post-like" data-post_id="<?php the_ID(); ?>"><i class="fa fa-heart"></i> <span class="like-count"><?php echo timeliner_get_post_extra( 'likes' );?></span></a>
												</li>
												<li>
													<i class="fa fa-eye"></i> <?php echo timeliner_get_post_extra( 'views' );?>
												</li>
												<li>
													<a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>"><i class="fa fa-user"></i> <?php echo get_the_author_meta('display_name'); ?></a>
												</li>
												<li>
													<i class="fa fa-pencil"></i> <?php echo timeliner_the_category(); ?>
												</li>
											</ul>
											<?php 
												get_template_part( 'media/media', 'quote' );
											endif;
											?>
										</div>
									</div>
									<div class="cd-content">
										<div class="cd-author">
											<?php
											$url = timeliner_get_avatar_url( get_avatar( get_the_author_meta( 'ID' ), 150 ) );
											if( !empty( $url ) ):
											?>
												<img src="<?php echo esc_url( $url ); ?>" class="media-object img-responsive" alt="" />
											<?php
											endif;
											?>
										</div>
										<div class="clearfix"></div>
									</div>
								</div>
							<?php endif; ?>
						</div> <!-- cd-timeline-block -->
						<?php
						$counter == 2 ? $counter = 1 : $counter++;
					}
					if( !empty( $pagination ) ){
						$next = get_next_posts_link();
						$temp = explode( "href=\"", $next );
						$temp2 = explode( "\"", $temp[1] );
						?> 
						<div class="cd-timeline-block load-more-block">
							<div class="cd-timeline-year">
								<h2><a href="javascript:;" class="load-more" data-next_link="<?php echo $temp2[0] ?>"><i class="fa fa-angle-double-down"></i></a></h2>
							</div> <!-- cd-timeline-img -->
						</div> <!-- cd-timeline-block -->
						<?php
					}
					?>
					</section>
					<?php
				}
				else{
					?>
					<div class="row">
						<div class="col-md-12">
							<div class="white-block">
								<div class="post-inner">
									<!-- title -->
									<div class="widget-title-wrap">
										<h6 class="widget-title">
											<?php _e( 'Nothing Found', 'timeliner' ) ?>
										</h6>
									</div>
									<!--.title -->
									<p><?php _e( 'Sorry but we could not find anything which resembles you search criteria. Please try again using form bellow.', 'timeliner' ) ?></p>
									<?php get_search_form(); ?>
								</div>
							</div>
						</div>
					</div>
					<?php
				}
				?>
			</div>
		</div>
	</div>
</section>
<!-- .pagination -->
<?php wp_reset_query(); ?>
<?php get_footer(); ?>