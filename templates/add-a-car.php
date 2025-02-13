<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$car_edit = false;

if ( ! empty( $_GET['edit_car'] ) && $_GET['edit_car'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$car_edit = true;
}

$restricted = false;

$user_id = '';
if ( is_user_logged_in() ) {
	$user    = wp_get_current_user();
	$user_id = $user->ID;
}

$restrictions = apply_filters(
	'stm_get_post_limits',
	array(
		'premoderation' => true,
		'posts_allowed' => 0,
		'posts'         => 0,
		'images'        => 0,
		'role'          => 'user',
	),
	$user_id
);

if ( $restrictions['posts'] < 1 ) {
	$restricted = true;
}

$vars = array();

?>

<?php if ( $restricted && ! $car_edit ) : ?>
	<div class="stm-no-available-adds-overlay"></div>
	<div class="stm-no-available-adds">
		<h3><?php esc_html_e( 'Slots available', 'stm_vehicles_listing' ); ?>: <span>0</span></h3>
		<p><?php esc_html_e( 'You ended the limit of free classified ads.', 'stm_vehicles_listing' ); ?></p>
	</div>
<?php endif; ?>

<!--CAR ADD-->
<?php if ( $car_edit ) : ?>
	<?php
	if ( ! is_user_logged_in() ) {
		echo '<h4>' . esc_html__( 'Please login.', 'stm_vehicles_listing' ) . '</h4>';

		return false;
	}

	if ( ! empty( $_GET['item_id'] ) ) {//phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$item_id = intval( $_GET['item_id'] ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$car_user = get_post_meta( $item_id, 'stm_car_user', true );

		if ( intval( $user_id ) !== intval( $car_user ) ) {
			echo '<h4>' . esc_html__( 'You are not the owner of this car.', 'stm_vehicles_listing' ) . '</h4>';

			return false;
		}
	} else {
		echo '<h4>' . esc_html__( 'No car to edit.', 'stm_vehicles_listing' ) . '</h4>';

		return false;
	}

	$vars = array( 'id' => $item_id );
endif;
?>
<div class="stm_add_car_form stm_add_car_form_<?php echo esc_attr( $car_edit ); ?>">

	<form method="POST" action="" enctype="multipart/form-data" id="stm_sell_a_car_form">

		<?php if ( $car_edit ) : ?>
			<input type="hidden" value="<?php echo intval( $item_id ); ?>" name="stm_current_car_id"/>
		<?php endif; ?>

		<?php do_action( 'stm_listings_load_template', 'add_car/title', $vars ); ?>

		<?php do_action( 'stm_listings_load_template', 'add_car/step_1', $vars ); ?>

		<?php do_action( 'stm_listings_load_template', 'add_car/step_2', $vars ); ?>

		<?php do_action( 'stm_listings_load_template', 'add_car/step_3', $vars ); ?>

		<?php do_action( 'stm_listings_load_template', 'add_car/step_4', $vars ); ?>

		<?php do_action( 'stm_listings_load_template', 'add_car/step_5', $vars ); ?>

		<?php do_action( 'stm_listings_load_template', 'add_car/step_6', $vars ); ?>

	</form>

	<?php do_action( 'stm_listings_load_template', 'add_car/check_user' ); ?>

</div>
