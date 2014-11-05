<?php
/*
	Template Name: Left Sidebar
*/
get_header();
the_post();
?>

<section class="main-listing">
	<div class="container">
		<div class="row">
			<div class="col-md-4">
				<?php get_sidebar( 'left' ) ?>
			</div>		
			<div class="col-md-8">
				<div class="white-block">
					<?php if( has_post_thumbnail() ): ?>
						<div class="embed-responsive embed-responsive-16by9">
							<?php  the_post_thumbnail( 'full', array( 'class' => 'embed-responsive-item' ) ); ?>
						</div>
					<?php endif; ?>
					<div class="post-inner">
						<div class="post-header">
							<h1 class="post-title"><?php the_title() ?></h1>
						</div>
						
						<div class="post-content">
							<?php the_content(); ?>							
						</div>
						
					</div>			
				</div>
				
				<?php comments_template( '', true ) ?>
				
			</div>
		</div>
	</div>
</section>
<?php get_footer(); ?>