<?php
$post_meta = get_post_custom();
$link = timeliner_get_smeta( 'link', $post_meta, '' );
if( !empty( $link ) ){
	?>
	<a href="<?php esc_url( $link ); ?>">
		<h2 class="break-word"><?php echo $link ?></h2>
	</a>
	<?php
}
?>