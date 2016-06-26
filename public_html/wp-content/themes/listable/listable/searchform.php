<form class="search-form" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" role="search">
	<?php if ( get_post_type() === 'job_listing' ) {
		echo '<input type="hidden" name="post_type" value="job_listing" />';
	} ?>
	<input class="search-field" type="text" name="s" id="s" placeholder="<?php esc_html_e( '
O que você está procurando?', 'listable' ); ?>" autocomplete="off" value="<?php the_search_query(); ?>"/>
	<button class="search-submit" name="enviar" id="searchsubmit"></button>
</form>