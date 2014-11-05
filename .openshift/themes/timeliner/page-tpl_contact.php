<?php
/*
	Template Name: Page Contact
*/
get_header();
the_post();
?>

<section class="main-listing">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
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
						
						<div class="row">
							<div class="col-md-6">
						
								<form id="comment-form" class="comment-form contact-form">								
									<div class="form-group has-feedback">
										<input type="text" class="form-control name" id="name" name="name" placeholder="<?php esc_attr_e( 'Your name', 'timeliner' ) ?>" />
									</div>
									<div class="form-group has-feedback">
										<input type="text" class="form-control email" id="email" name="email" placeholder="<?php esc_attr_e( 'Your email', 'timeliner' ) ?>" />
									</div>
									<div class="form-group has-feedback">
										<input type="text" class="form-control subject" id="subject" name="subject" placeholder="<?php esc_attr_e( 'Message subject', 'timeliner' ) ?>" />
									</div>
									<div class="form-group has-feedback">
										<textarea rows="10" cols="100" class="form-control message" id="message" name="message" placeholder="<?php esc_attr_e( 'Your message', 'timeliner' ) ?>"></textarea>															
									</div>
									<p class="form-submit">
										<a href="javascript:;" class="send-contact btn"><?php _e( 'Send Message', 'timeliner' ) ?> </a>
									</p>
									<div class="send_result"></div>
								</form>	
								
							</div>						
							<div class="col-md-6">
								<div class="post-content">
									<?php the_content(); ?>							
								</div>
								<div class="clearfix"></div>
							</div>
						</div>
					</div>					
				</div>
			</div>
		</div>
	</div>
</section>
<?php get_footer(); ?>