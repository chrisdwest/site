<?php
	/**********************************************************************
	***********************************************************************
	TIMELINER COMMENTS
	**********************************************************************/	
	if (!empty($_SERVER['SCRIPT_FILENAME']) && 'comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
		die ( 'Please do not load this page directly. Thanks!' );
	if ( post_password_required() ) {
		return;
	}
?>
<?php if ( comments_open() ) :?>
	<?php if( get_comments_number() > 0 ): ?>
		<div class="white-block">
			<div class="post-inner">			
				<!-- title -->
				<div class="widget-title-wrap">
					<h6 class="widget-title">
						<?php comments_number( __( 'No Comments', 'timeliner' ), __( '1 Comment', 'timeliner' ), __( '% Comments', 'timeliner' ) ); ?>
					</h6>
				</div>
				<!--.title -->
			
				<!-- comments -->
				<div class="comment-content comments">
					<?php if( have_comments() ): ?>
						<?php wp_list_comments( array(
							'type' => 'comment',
							'callback' => 'timeliner_comments',
							'end-callback' => 'timeliner_end_comments',
							'style' => 'div'
						)); ?>
					<?php endif; ?>
				</div>
				<!-- .comments -->
			
				<!-- comments pagination -->
				<?php
					$comment_links = paginate_comments_links( 
						array(
							'echo' => false,
							'type' => 'array',
							'prev_next' => false,
							'separator' => ' ',
						) 
					);
					if( !empty( $comment_links ) ):
				?>
					<div class="comments-pagination-wrap">
						<div class="pagination">
							<?php _e( 'Comment page: ', 'timeliner');  echo timeliner_format_pagination( $comment_links ); ?>
						</div>
					</div>
				<?php endif; ?>
				<!-- .comments pagination -->
			</div>	
		</div>
	<?php endif; ?>
	<div class="white-block">
		<div class="post-inner">	
			<!-- leave comment form -->
			<!-- title -->
			<div class="widget-title-wrap">
				<h6 class="widget-title">
					<?php _e( 'Leave Comment', 'timeliner' ); ?>
				</h6>
			</div>
			<!--.title -->
			<div id="contact_form">
				<?php
					$comments_args = array(
						'id_form'		=> 'comment-form',
						'label_submit'	=>	__( 'Send Comment', 'timeliner' ),
						'title_reply'	=>	'',
						'fields'		=>	apply_filters( 'comment_form_default_fields', array(
												'author' => '<div class="form-group has-feedback">
																<input type="text" class="form-control" id="name" name="author" placeholder="'.__('Name','timeliner').'">
															</div>',
												'email'	 => '<div class="form-group has-feedback">
																<input type="email" class="form-control" id="email" name="email" placeholder="'.__('Email','timeliner').'">
															</div>'
											)),
						'comment_field'	=>	'<div class="form-group has-feedback">
												<textarea rows="10" cols="100" class="form-control" id="message" name="comment" placeholder="'.__('Comment','timeliner').'"></textarea>															
											</div>',
						'cancel_reply_link' => __( 'or cancel reply', 'timeliner' ),
						'comment_notes_after' => '<p class="form-allowed-tags">' . sprintf( __( 'You may use these <abbr title="HyperText Markup Language">HTML</abbr> tags and attributes: %s' ), ' <code>' . allowed_tags() . '</code>' ) . '</p>',
						'comment_notes_before' => ''
					);
					comment_form( $comments_args );	
				?>
			</div>
			<!-- content -->
			<!-- .leave comment form -->
		</div>
	</div>

<?php endif; ?>