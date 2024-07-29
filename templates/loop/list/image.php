<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="image">
	<!--Compare-->
	<?php do_action( 'stm_listings_load_template', 'loop/list/compare' ); ?>

	<!--Video-->
	<?php do_action( 'stm_listings_load_template', 'loop/list/video' ); ?>

	<a href="<?php the_permalink(); ?>" class="rmv_txt_drctn">
		<div class="image-inner">
			<!--Badge-->
			<?php do_action( 'stm_listings_load_template', 'loop/list/badge' ); ?>
			<?php
			if ( has_post_thumbnail() ) :
				the_post_thumbnail( 'medium', array( 'src' => 'img-responsive' ) );
			endif;
			?>
		</div>
	</a>
</div>
