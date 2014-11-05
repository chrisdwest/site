<?php

class Custom_Widget_Posts extends WP_Widget {

	function __construct() {
		parent::__construct('custom_posts', __('Recent, popular, by views posts','timeliner'), array('description' =>__('Display recent, popular, by views posts','timeliner') ));
	}

	function widget($args, $instance) {
		extract($args);
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'text' => '' ) );
		
		$random = timeliner_random_string();
		
		$title = esc_attr( $instance['title'] );
		$text = esc_attr( $instance['text'] );		

		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( 'Posts', 'timeliner' );
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );
		$number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 5;
		if ( ! $number )
			$number = 5;
		$show = isset( $instance['show'] ) ? $instance['show'] : array( 'recent' );
		
		 $navigation = '<ul class="nav nav-tabs" role="tablist">';

		if( in_array( 'recent', $show ) ){
			$navigation .= '<li '.( $show[0] == 'recent' ? 'class="active"' : '' ).'><a href="#recent_'.$random.'" role="tab" data-toggle="tab" class="btn button">'.__( 'Recent', 'timeliner' ).'</a></li>';
		}
		if( in_array( 'popular', $show ) ){
			$navigation .= '<li '.( $show[0] == 'popular' ? 'class="active"' : '' ).'><a href="#popular_'.$random.'" role="tab" data-toggle="tab" class="btn button">'.__( 'Popular', 'timeliner' ).'</a></li>';
		}
		if( in_array( 'views', $show ) ){
			$navigation .= '<li '.( $show[0] == 'views' ? 'class="active"' : '' ).'><a href="#views_'.$random.'" role="tab" data-toggle="tab" class="btn button">'.__( 'Views', 'timeliner' ).'</a></li>';
		}
		$navigation .= '</ul>';
		?>
		<?php echo $before_widget; ?>
		<?php 
		if ( $title ) echo $before_title . $title . $after_title; 
		echo $navigation;
		?>
		
		<div class="tab-content absolute">
			<!-- show recent posts -->
			<?php 
			if( in_array( 'recent', $show ) ): 
			?>
			<div class="tab-pane fade <?php echo $show[0] == 'recent' ? 'in active' : ''; ?>" id="recent_<?php echo $random ?>">
				<?php
				$r = new WP_Query( apply_filters( 'widget_posts_args', array(
					'posts_per_page'      => $number,
					'no_found_rows'       => true,
					'post_status'         => 'publish',
					'ignore_sticky_posts' => true
				) ) );

				if ($r->have_posts()):
				?>
				<ul class="list-unstyled">
				<?php 
				while ( $r->have_posts() ) : 
					$r->the_post();
					include( locate_template( 'includes/widget-loop.php' ) );
				endwhile; ?>
				</ul>
			</div>
			<?php 
				endif; 
				// Reset the global $the_post as this query will have stomped on it
				wp_reset_postdata();
			endif;			
			?>	
			<!-- .show recent posts -->
			
			<!-- show popular posts -->
			<?php 
			if( in_array( 'popular', $show ) ): 
			?>
			<div class="tab-pane fade <?php echo $show[0] == 'popular' ? 'in active' : ''; ?>" id="popular_<?php echo $random ?>">
				<?php
				$r = new WP_Query( array(
					'posts_per_page'      => $number,
					'post_status'         => 'publish',
					'ignore_sticky_posts' => true,
					'orderby'			  => 'meta_value_num',
					'meta_key'			  => 'likes',
					'order'				  => 'DESC',
					'meta_query'		  => array(
						'key' => 'likes',
						'value'    => 'EXISTS'
					)
				));

				if ($r->have_posts()):
				?>
				<ul class="list-unstyled">
				<?php 
				while ( $r->have_posts() ) : 
					$r->the_post();
					include( locate_template( 'includes/widget-loop.php' ) );
				endwhile; ?>
				</ul>
				<?php endif; ?>
			</div>
			<?php 				
				// Reset the global $the_post as this query will have stomped on it
				wp_reset_postdata();
			endif;			
			?>	
			<!-- .show popular posts -->	
			
<!-- show posts by views -->
			<?php 
			if( in_array( 'views', $show ) ): 
			?>
			<div class="tab-pane fade <?php echo $show[0] == 'views' ? 'in active' : ''; ?>" id="views_<?php echo $random ?>">
				<?php
				$r = new WP_Query( array(
					'posts_per_page'      => $number,
					'post_status'         => 'publish',
					'ignore_sticky_posts' => true,
					'orderby'			  => 'meta_value_num',
					'meta_key'			  => 'views',
					'order'				  => 'DESC',
					'meta_query'		  => array(
						'key' => 'views',
						'value'    => 'EXISTS'
					)
				));

				if ($r->have_posts()):
				?>
				<ul class="list-unstyled">
				<?php 
				while ( $r->have_posts() ) : 
					$r->the_post();
					include( locate_template( 'includes/widget-loop.php' ) );
				endwhile; ?>
				</ul>
				<?php endif; ?>
			</div>
			<?php 				
				// Reset the global $the_post as this query will have stomped on it
				wp_reset_postdata();
			endif;			
			?>	
			<!-- .show posts by views-->	
		</div>
		
		<?php echo $after_widget; ?>
<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number'] = (int) $new_instance['number'];
		var_dump( $new_instance['title'] );
		$instance['show'] = $new_instance['show'];

		return $instance;
	}

	function form( $instance ) {
		$title     = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$number    = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
		$show	   = isset( $instance['show'] ) ? $instance['show'] : array( 'recent' );
?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'timeliner' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:', 'timeliner' ); ?></label>
		<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>

		<p><label for="<?php echo $this->get_field_id( 'show' ); ?>"><?php _e( 'Select tabs to display:', 'timeliner' ); ?></label>
		<select id="<?php echo $this->get_field_id( 'show' ); ?>" name="<?php echo $this->get_field_name( 'show' ); ?>[]" class="widefat" multiple>
			<option value="recent" <?php echo in_array( 'recent', $show ) ? 'selected="selected"' : '' ?>><?php _e( 'Recent Posts', 'timeliner' ) ?></option>
			<option value="popular" <?php echo in_array( 'popular', $show ) ? 'selected="selected"' : '' ?>><?php _e( 'Popular Posts', 'timeliner' ) ?></option>
			<option value="views" <?php echo in_array( 'views', $show ) ? 'selected="selected"' : '' ?>><?php _e( 'Posts By Views', 'timeliner' ) ?></option>
		</select>
<?php
	}
}


class Custom_Widget_Recent_Comments extends WP_Widget {

	function __construct() {
		parent::__construct('timeliner_recent_comments', __('Timeliner Recent Comments','timeliner'), array('description' =>__('Display recent comments','timeliner') ));
	}

	function widget( $args, $instance ) {
		global $comments, $comment;
		extract( $args );
		$output = '';
		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( 'Recent Comments', 'timeliner' );
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 5;
		if ( ! $number )
			$number = 5;
		$comments = get_comments( apply_filters( 'widget_comments_args', array(
			'number'      => $number,
			'status'      => 'approve',
			'post_status' => 'publish'
		) ) );

		$output .= $before_widget;
		if ( $title )
			$output .= $before_title . $title . $after_title;

		$output .= '<ul class="list-unstyled no-top-padding">';
		if ( $comments ) {

			foreach ( (array) $comments as $comment) {
				$comment_text = get_comment_text( $comment->comment_ID );
				if( strlen( $comment_text ) > 40 ){
					$comment_text = substr( $comment_text, 0, 40 );
					$comment_text = substr( $comment_text, 0, strripos( $comment_text, " "  ) );
					$comment_text .= "...";
				}
				$url  = timeliner_get_avatar_url( get_avatar( $comment, 60 ) );
				
				$output .=  '<li>
								<div class="widget-image-thumb">
									<img src="'.$url.'" class="img-responsive" width="60" height="60" alt=""/>
								</div>
								
								<div class="widget-text">
									'.get_comment_author_link().'								
									<small><a href="' . esc_url( get_comment_link($comment->comment_ID) ) . '" class="widget-small-link">' .$comment_text. '</a></small>
								</div>
								<div class="clearfix"></div>
							</li>';
			}
		}
		$output .= '</ul>';
		$output .= $after_widget;

		echo $output;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number'] = absint( $new_instance['number'] );
		return $instance;
	}

	function form( $instance ) {
		$title  = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'timeliner' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of comments to show:', 'timeliner' ); ?></label>
		<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>
<?php
	}
}
class Custom_Top_Authors extends WP_Widget{
	function __construct() {
		parent::__construct('widget_top_author', __('Top Author','timeliner'), array('description' =>__('Adds list of top authors.','timeliner') ));
	}

	function widget($args, $instance) {
		global $wpdb;
		/** This filter is documented in wp-includes/default-widgets.php */
		$instance['title'] = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		$instance['count'] = apply_filters( 'widget_title', empty( $instance['count'] ) ? '5' : $instance['count'], $instance, $this->id_base );

		echo $args['before_widget'];
		
		$authors = $wpdb->get_results( "SELECT users.ID, COUNT( posts.ID ) AS post_count FROM {$wpdb->users} AS users RIGHT JOIN {$wpdb->posts} AS posts ON posts.post_author = users.ID WHERE posts.post_type='post' AND posts.post_status='publish' GROUP BY users.ID ORDER BY post_count DESC LIMIT {$instance['count']}" );
		
		if ( !empty($instance['title']) ){
			echo $args['before_title'] . $instance['title'] . $args['after_title'];
		}
		if( !empty( $authors ) ){
			echo '<ul class="list-unstyled no-top-padding">';
			foreach( $authors as $author ){
				$url = timeliner_get_avatar_url( get_avatar( $author->ID, 60 ) );
				echo    '<li class="top-authors">
							<div class="widget-image-thumb">
								<img src="'.$url.'" class="img-responsive" width="60" height="60" alt=""/>
							</div>
							
							<div class="widget-text">
								<a href="'.get_author_posts_url( $author->ID ).'">
									'.get_the_author_meta( 'display_name', $author->ID ).'
								</a>
								<small>'.__( 'Wrote ', 'timeliner' ).' '.$author->post_count.' '.__( 'posts', 'timeliner' ).'</small>
							</div>
							<div class="clearfix"></div>
						</li>';				
			}
			echo '</ul>';
		}
		echo $args['after_widget'];
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( stripslashes($new_instance['title']) );
		$instance['count'] = strip_tags( stripslashes($new_instance['count']) );
		return $instance;
	}

	function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		$count = isset( $instance['count'] ) ? $instance['count'] : '';
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'timeliner') ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Count:', 'timeliner') ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" value="<?php echo $count; ?>" />
		</p>		
		<?php
	}
}

class Custom_Social extends WP_Widget{
	function __construct() {
		parent::__construct('widget_social', __('Social Follow','timeliner'), array('description' =>__('Adds list of the social icons.','timeliner') ));
	}

	function widget($args, $instance) {
		/** This filter is documented in wp-includes/default-widgets.php */
		$instance['title'] = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		$facebook = !empty( $instance['facebook'] ) ? '<a href="'.esc_url( $instance['facebook'] ).'" target="_blank" class="btn"><span class="fa fa-facebook"></span></a>' : '';
		$twitter = !empty( $instance['twitter'] ) ? '<a href="'.esc_url( $instance['twitter'] ).'" target="_blank" class="btn"><span class="fa fa-twitter"></span></a>' : '';
		$google = !empty( $instance['google'] ) ? '<a href="'.esc_url( $instance['google'] ).'" target="_blank" class="btn"><span class="fa fa-google"></span></a>' : '';
		$linkedin = !empty( $instance['linkedin'] ) ? '<a href="'.esc_url( $instance['linkedin'] ).'" target="_blank" class="btn"><span class="fa fa-linkedin"></span></a>' : '';
		$pinterest = !empty( $instance['pinterest'] ) ? '<a href="'.esc_url( $instance['pinterest'] ).'" target="_blank" class="btn"><span class="fa fa-pinterest"></span></a>' : '';
		$youtube = !empty( $instance['youtube'] ) ? '<a href="'.esc_url( $instance['youtube'] ).'" target="_blank" class="btn"><span class="fa fa-youtube"></span></a>' : '';
		$flickr = !empty( $instance['flickr'] ) ? '<a href="'.esc_url( $instance['flickr'] ).'" target="_blank" class="btn"><span class="fa fa-flickr"></span></a>' : '';
		$behance = !empty( $instance['behance'] ) ? '<a href="'.esc_url( $instance['behance'] ).'" target="_blank" class="btn"><span class="fa fa-behance"></span></a>' : '';

		echo $args['before_widget'];
		
		if ( !empty($instance['title']) ){
			echo $args['before_title'] . $instance['title'] . $args['after_title'];
		}
		echo '<div class="widget-social">';
			echo $facebook.$twitter.$google.$linkedin.$pinterest.$youtube.$flickr.$behance;
		echo '</div>';
		echo $args['after_widget'];
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( stripslashes($new_instance['title']) );
		$instance['facebook'] = strip_tags( stripslashes($new_instance['facebook']) );
		$instance['twitter'] = strip_tags( stripslashes($new_instance['twitter']) );
		$instance['google'] = strip_tags( stripslashes($new_instance['google']) );
		$instance['linkedin'] = strip_tags( stripslashes($new_instance['linkedin']) );
		$instance['pinterest'] = strip_tags( stripslashes($new_instance['pinterest']) );
		$instance['youtube'] = strip_tags( stripslashes($new_instance['youtube']) );
		$instance['flickr'] = strip_tags( stripslashes($new_instance['flickr']) );
		$instance['behance'] = strip_tags( stripslashes($new_instance['behance']) );
		return $instance;
	}

	function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		$facebook = isset( $instance['facebook'] ) ? $instance['facebook'] : '';
		$twitter = isset( $instance['twitter'] ) ? $instance['twitter'] : '';
		$google = isset( $instance['google'] ) ? $instance['google'] : '';
		$linkedin = isset( $instance['linkedin'] ) ? $instance['linkedin'] : '';
		$pinterest = isset( $instance['pinterest'] ) ? $instance['pinterest'] : '';
		$youtube = isset( $instance['youtube'] ) ? $instance['youtube'] : '';
		$flickr = isset( $instance['flickr'] ) ? $instance['flickr'] : '';
		$behance = isset( $instance['behance'] ) ? $instance['behance'] : '';
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'timeliner') ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('facebook'); ?>"><?php _e('Facebook:', 'timeliner') ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('facebook'); ?>" name="<?php echo $this->get_field_name('facebook'); ?>" value="<?php echo $facebook; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('twitter'); ?>"><?php _e('Twitter:', 'timeliner') ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('twitter'); ?>" name="<?php echo $this->get_field_name('twitter'); ?>" value="<?php echo $twitter; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('google'); ?>"><?php _e('Google +:', 'timeliner') ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('google'); ?>" name="<?php echo $this->get_field_name('google'); ?>" value="<?php echo $google; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('linkedin'); ?>"><?php _e('Linkedin:', 'timeliner') ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('linkedin'); ?>" name="<?php echo $this->get_field_name('linkedin'); ?>" value="<?php echo $linkedin; ?>" />
		</p>			
		<p>
			<label for="<?php echo $this->get_field_id('youtube'); ?>"><?php _e('YouTube:', 'timeliner') ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('youtube'); ?>" name="<?php echo $this->get_field_name('youtube'); ?>" value="<?php echo $youtube; ?>" />
		</p>		
		<p>
			<label for="<?php echo $this->get_field_id('pinterest'); ?>"><?php _e('Pinterest:', 'timeliner') ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('pinterest'); ?>" name="<?php echo $this->get_field_name('pinterest'); ?>" value="<?php echo $pinterest; ?>" />
		</p>		
		<p>
			<label for="<?php echo $this->get_field_id('flickr'); ?>"><?php _e('Flickr:', 'timeliner') ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('flickr'); ?>" name="<?php echo $this->get_field_name('flickr'); ?>" value="<?php echo $flickr; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('behance'); ?>"><?php _e('Behance:', 'timeliner') ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('behance'); ?>" name="<?php echo $this->get_field_name('behance'); ?>" value="<?php echo $behance; ?>" />
		</p>			
		<?php
	}
}

class Custom_Subscribe extends WP_Widget{
	function __construct() {
		parent::__construct('widget_subscribe', __('Subscribe','timeliner'), array('description' =>__('Adds subscribe form in the sidebar.','timeliner') ));
	}

	function widget($args, $instance) {
		/** This filter is documented in wp-includes/default-widgets.php */
		$instance['title'] = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

		echo $args['before_widget'];
		
		if ( !empty($instance['title']) ){
			echo $args['before_title'] . $instance['title'] . $args['after_title'];
		}
		echo '<div class="subscribe-form">
				<div class="timeliner-form">
					<input type="text" class="form-control email" placeholder="'.esc_attr__( 'Input email here...', 'timeliner' ).'">
					<a href="javascript:;" class="btn btn-default subscribe"><i class="fa fa-rss"></i></a>
				</div>
				<div class="sub_result"></div>
			  </div>';
		echo $args['after_widget'];
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( stripslashes($new_instance['title']) );
		return $instance;
	}

	function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'timeliner') ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title; ?>" />
		</p>	
		<?php
	}
}

class Custom_Text extends WP_Widget{
	function __construct() {
		parent::__construct('widget_text', __('Shortcode Text','timeliner'), array('description' =>__('Text widget which can render shortcode.','timeliner') ));
	}

	function widget($args, $instance) {
		/** This filter is documented in wp-includes/default-widgets.php */
		$instance['title'] = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		$instance['text'] = $instance['text'];

		echo $args['before_widget'];
		
		if ( !empty($instance['title']) ){
			echo $args['before_title'] . $instance['title'] . $args['after_title'];
		}
		echo do_shortcode( $instance['text'] );
		echo $args['after_widget'];
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( stripslashes($new_instance['title']) );
		$instance['text'] = $new_instance['text'];
		return $instance;
	}

	function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		$text = isset( $instance['text'] ) ? $instance['text'] : '';
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'timeliner') ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('text'); ?>"><?php _e('Text:', 'timeliner') ?></label>
			<textarea type="text" class="widefat" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>" ><?php echo $text; ?></textarea>
		</p>
		<?php
	}
}

function custom_widgets_init() {
	if ( !is_blog_installed() ){
		return;
	}	
	/* register new ones */
	register_widget('Custom_Widget_Posts');
	register_widget('Custom_Widget_Recent_Comments');
	register_widget('Custom_Top_Authors');
	register_widget('Custom_Social');
	register_widget('Custom_Subscribe');
	register_widget('Custom_Text');
}

add_action('widgets_init', 'custom_widgets_init', 1);
?>