<?php
/**
 * Base class for handling controls.  Controls are the form fields for the manager.  Each
 * control should be tied to a section.
 *
 * @package    ButterBean
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2015-2016, Justin Tadlock
 * @link       https://github.com/justintadlock/butterbean
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/**
 * Base control class.
 *
 * @since  1.0.0
 * @access public
 */
class ButterBean_Control {

	/**
	 * Stores the manager object.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    object
	 */
	public $manager;

	/**
	 * Name/ID of the control.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $name = '';

	/**
	 * Label for the control.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $label = '';

	/**
	 * Description for the control.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $description = '';

	/**
	 * ID of the section the control is for.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $section = '';

	/**
	 * The setting key for the specific setting the control is tied to.
	 * Controls can have multiple settings attached to them.  The default
	 * setting is `default`.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $setting = 'default';

	/**
	 * Array of settings if the control has multiple settings.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    array
	 */
	public $settings = array();

	/**
	 * The type of control.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $type = 'text';

	/**
	 * Form field attributes.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    array
	 */
	public $attr = '';

	/**
	 * Choices for fields with multiple choices.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    array
	 */
	public $choices = array();

	/**
	 * Priority (order) the control should be output.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    int
	 */
	public $priority = 10;

	/**
	 * The number of instances created.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    int
	 */
	protected static $instance_count = 0;

	/**
	 * The instance of the current control.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    int
	 */
	public $instance_number;

	/**
	 * A callback function for deciding if a control is active.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    callable
	 */
	public $active_callback = '';

	/**
	 * A user role capability required to show the control.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string|array
	 */
	public $capability = '';

	/**
	 * A feature that the current post type must support to show the control.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $post_type_supports = '';

	/**
	 * A feature that the current theme must support to show the control.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string|array
	 */
	public $theme_supports = '';

	/**
	 * Stores the JSON data for the control.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    array()
	 */
	public $json = array();

	/**
	 * Creates a new control object.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  object  $manager
	 * @param  string  $name
	 * @param  array   $args
	 * @return void
	 */

	/**
	 * Preview image of the control.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $preview = '';

	/**
	 * Preview image of the control.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $preview_url = STM_LISTINGS_IMAGES;

	public function __construct( $manager, $name, $args = array() ) {

		foreach ( array_keys( get_object_vars( $this ) ) as $key ) {

			if ( isset( $args[ $key ] ) ) {
				$this->$key = $args[ $key ];
			}
		}

		$this->manager = $manager;
		$this->name    = $name;

		if ( empty( $args['settings'] ) || ! is_array( $args['settings'] ) ) {
			$this->settings['default'] = $name;
		}

		// Increment the instance count and set the instance number.
		++self::$instance_count;
		$this->instance_number = self::$instance_count;

		// Set the active callback function if not set.
		if ( ! $this->active_callback ) {
			$this->active_callback = array( $this, 'active_callback' );
		}

	}

	/**
	 * Enqueue scripts/styles for the control.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function enqueue() {}

	/**
	 * Get the value for the setting.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  string $setting
	 * @return mixed
	 */
	public function get_value( $setting = 'default' ) {

		$setting = $this->get_setting( $setting );

		return $setting ? $setting->get_value() : '';
	}

	/**
	 * Returns the setting object associated with this control. If no setting is
	 * found, `false` is returned.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  string $setting
	 * @return object|bool
	 */
	public function get_setting( $setting = 'default' ) {

		return $this->manager->get_setting( $this->settings[ $setting ] );
	}

	/**
	 * Gets the attributes for the control.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return array
	 */
	public function get_attr() {

		$defaults = array();

		if ( isset( $this->settings[ $this->setting ] ) ) {
			$defaults['name'] = $this->get_field_name( $this->setting );
		}

		return wp_parse_args( $this->attr, $defaults );
	}

	/**
	 * Returns the HTML field name for the control.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  string $setting
	 * @return array
	 */
	public function get_field_name( $setting = 'default' ) {

		return "butterbean_{$this->manager->name}_setting_{$this->settings[ $setting ]}";
	}

	/**
	 * Returns the json array.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return array
	 */
	public function get_json() {
		$this->to_json();

		return $this->json;
	}

	/**
	 * Adds custom data to the json array. This data is passed to the Underscore template.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function to_json() {

		$this->json['manager']     = $this->manager->name;
		$this->json['section']     = $this->section;
		$this->json['setting']     = $this->setting;
		$this->json['settings']    = $this->settings;
		$this->json['name']        = $this->name;
		$this->json['label']       = $this->label;
		$this->json['preview']     = $this->preview;
		$this->json['preview_url'] = $this->preview_url;
		$this->json['type']        = $this->type;
		$this->json['description'] = $this->description;
		$this->json['choices']     = $this->choices;
		$this->json['active']      = $this->is_active();

		$this->json['value']      = isset( $this->settings[ $this->setting ] ) ? $this->get_value( $this->setting ) : '';
		$this->json['field_name'] = isset( $this->settings[ $this->setting ] ) ? $this->get_field_name( $this->setting ) : '';

		$this->json['attr'] = '';

		foreach ( $this->get_attr() as $attr => $value ) {
			$this->json['attr'] .= sprintf( '%s="%s" ', esc_html( $attr ), esc_attr( $value ) );
		}

		if ( get_the_ID() !== apply_filters( 'stm_get_wpml_product_parent_id', get_the_ID() ) && get_post_status() !== 'auto-draft' ) {
			if ( 'cars_qty' === $this->name ) {
				$this->json['label'] .= ' (This field are not editable.)';
				$this->json['attr']  .= sprintf( '%s="%s" ', 'disabled', 'disabled' );
				$carsQty              = get_post_meta( apply_filters( 'stm_get_wpml_product_parent_id', 'product', get_the_ID() ), 'cars_qty', true );
				$this->json['value']  = $carsQty;
				$this->json['attr']  .= sprintf( '%s="%s" ', 'disabled', 'disabled' );
			}

			if ( 'cars_info' !== $this->name && 'address' !== $this->json['name'] ) {
				$this->json['value'] = get_post_meta( apply_filters( 'stm_get_wpml_product_parent_id', get_the_ID() ), $this->name, true );
			}

			if ( 'stm_rental_office' === $this->name ) {
				$this->json['label'] .= ' (This field are not editable.)';
				$this->json['attr']  .= sprintf( '%s="%s" ', 'disabled', 'disabled' );
			}

			if ( 'multiselect' === $this->json['type'] && 'stm_rental_office' !== $this->json['name'] ) {
				$this->json['value'] = get_post_meta( apply_filters( 'stm_motors_wpml_binding', get_the_ID(), 'product' ), $this->name, true );
			}

			if ( ! apply_filters( 'stm_get_wpml_product_parent_id', get_the_ID() ) ) {
				$this->json['value'] = isset( $this->settings[ $this->setting ] ) ? $this->get_value( $this->setting ) : '';
			}

			update_post_meta( get_the_ID(), $this->name, $this->json['value'] );
		}
	}

	/**
	 * Returns whether the control is active.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return bool
	 */
	public function is_active() {

		$is_active = call_user_func( $this->active_callback, $this );

		return apply_filters( 'butterbean_is_control_active', $is_active, $this );
	}

	/**
	 * Default active callback.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return bool
	 */
	public function active_callback() {
		return true;
	}

	/**
	 * Checks if the control should be allowed at all.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return bool
	 */
	public function check_capabilities() {

		if ( $this->capability && ! call_user_func_array( 'current_user_can', (array) $this->capability ) ) {
			return false;
		}

		if ( $this->post_type_supports && ! call_user_func_array( 'post_type_supports', array( get_post_type( $this->manager->post_id ), $this->post_type_supports ) ) ) {
			return false;
		}

		if ( $this->theme_supports && ! call_user_func_array( 'theme_supports', (array) $this->theme_supports ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Prints Underscore.js template.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function print_template() { ?>

		<script type="text/html" id="tmpl-butterbean-control-<?php echo esc_attr( $this->type ); ?>">
			<?php $this->get_template(); ?>
		</script>
		<?php
	}

	/**
	 * Gets the Underscore.js template.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function get_template() {
		butterbean_get_control_template( $this->type );
	}
}
