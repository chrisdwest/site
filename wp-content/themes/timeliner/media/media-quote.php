<?php
$post_meta = get_post_custom();
$blockquote = timeliner_get_smeta( 'blockquote', $post_meta, '' );
$cite = timeliner_get_smeta( 'cite', $post_meta, '' );
?>
<?php if( !empty( $blockquote ) ): ?>
	<blockquote>
		<h2><?php echo $blockquote ?></h2>
	</blockquote>
<?php endif; ?>
<?php if( !empty( $cite ) ): ?>
	<cite class="pull-right">
		<?php echo $cite; ?>
	</cite>
	<div class="clearfix"></div>
<?php endif; ?>