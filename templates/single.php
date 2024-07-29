<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
get_header(); ?>

	<div class="stm_single_car_wrapper">
		<div class="stm_single_car_row">

			<div class="stm_single_car_side">
				<div class="stm-single-car-side">

					<!--User info-->
					<?php do_action( 'stm_listings_load_template', 'single-car/car-user', array( 'post_id' => get_the_ID() ) ); ?>

					<!--Prices-->
					<?php do_action( 'stm_listings_load_template', 'single-car/car-price' ); ?>

					<!--Data-->
					<?php do_action( 'stm_listings_load_template', 'single-car/car-data' ); ?>

					<!--MPG-->
					<?php do_action( 'stm_listings_load_template', 'single-car/car-mpg' ); ?>

				</div>
			</div>
			<div class="stm_single_car_content">
				<div class="stm-single-car-content">
					<h2 class="title"><?php the_title(); ?></h2>

					<!--Actions-->
					<?php do_action( 'stm_listings_load_template', 'single-car/car-actions' ); ?>

					<!--Gallery-->
					<?php do_action( 'stm_listings_load_template', 'single-car/car-gallery' ); ?>

					<?php
					if ( have_posts() ) :
						while ( have_posts() ) :
							the_post();
							the_content();
							endwhile;
						endif;
					?>
				</div>
			</div>
		</div>
	</div>
<?php
get_footer();
