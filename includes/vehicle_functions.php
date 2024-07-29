<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if ( ! function_exists( 'stm_ajaxurl' ) ) {
	/**
	 * Ajax admin Url declaration.
	 */
	function stm_ajaxurl() {
		$my_locale = explode( '_', get_locale() );

		?>
		<script type="text/javascript">
			var stm_lang_code = '<?php echo esc_html( $my_locale[0] ); ?>';
			<?php if ( class_exists( 'SitePress' ) ) : ?>
			stm_lang_code = '<?php echo esc_js( ICL_LANGUAGE_CODE ); ?>';
			<?php endif; ?>
			var ajaxurl = '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>';
			var stm_site_blog_id = "<?php echo get_current_blog_id(); ?>";
			var stm_added_to_compare_text = "<?php esc_html_e( 'Added to compare', 'stm_vehicles_listing' ); ?>";
			var stm_removed_from_compare_text = "<?php esc_html_e( 'was removed from compare', 'stm_vehicles_listing' ); ?>";

			<?php if ( true === apply_filters( 'stm_is_boats', false ) ) : ?>
			var stm_already_added_to_compare_text = "<?php esc_html_e( 'You have already added 3 boats', 'stm_vehicles_listing' ); ?>";
			<?php else : ?>
			var stm_already_added_to_compare_text = "<?php esc_html_e( 'You have already added 3 cars', 'stm_vehicles_listing' ); ?>";
			<?php endif; ?>
		</script>
		<?php
	}

	add_action( 'wp_head', 'stm_ajaxurl' );
}

if ( ! function_exists( 'stm_vehicle_plugin_admin_create_nonce' ) ) {
	function stm_vehicle_plugin_admin_create_nonce() {

		$save_single_option   = wp_create_nonce( 'stm_listings_save_single_option_row' );
		$detele_single_option = wp_create_nonce( 'stm_listings_delete_single_option_row' );
		$save_option          = wp_create_nonce( 'stm_listings_save_option_order' );
		$add_option           = wp_create_nonce( 'stm_listings_add_new_option' );
		?>
		<script>
			var saveSingleOpt = '<?php echo esc_js( $save_single_option ); ?>';
			var deleteSingleOpt = '<?php echo esc_js( $detele_single_option ); ?>';
			var saveOpt = '<?php echo esc_js( $save_option ); ?>';
			var addOpt = '<?php echo esc_js( $add_option ); ?>';
		</script>
		<?php

	}

	add_action( 'admin_head', 'stm_vehicle_plugin_admin_create_nonce' );
}

if ( ! function_exists( 'stm_set_html_content_type_mail' ) ) {
	function stm_set_html_content_type_mail() {
		return 'text/html';
	}
}

if ( ! function_exists( 'stm_get_listing_archive_link' ) ) {
	/**
	 * Get inventory URL.
	 */
	function stm_get_listing_archive_link( $filters = array() ) {
		$listing_link = stm_listings_user_defined_filter_page();

		if ( ! $listing_link ) {

			$options = get_option( 'stm_post_types_options' );

			$default_type = array(
				'listings' => array(
					'title'        => __( 'Listings', 'stm_vehicles_listing' ),
					'plural_title' => __( 'Listings', 'stm_vehicles_listing' ),
					'rewrite'      => 'listings',
				),
			);

			$stm_vehicle_options = wp_parse_args( $options, $default_type );

			$listing_link = site_url() . '/' . $stm_vehicle_options['listings']['rewrite'] . '/';
		} else {
			$listing_link = get_permalink( $listing_link );
		}

		$qs = array();
		foreach ( $filters as $key => $val ) {
			$info = apply_filters( 'stm_vl_get_all_by_slug', array(), preg_replace( '/^(min_|max_)/', '', $key ) );
			$val  = ( is_array( $val ) ) ? implode( ',', $val ) : $val;

			$numeric = apply_filters( 'stm_me_get_nuxy_mod', false, 'friendly_url' ) ? apply_filters( 'get_value_from_listing_category', false, $key, 'numeric' ) : true;

			if ( ! $numeric ) {
				$listing_link .= $val . '/';
			} else {
				$qs[] = $key . ( ! empty( $info['listing_rows_numbers'] ) ? '[]=' : '=' ) . $val;
			}
		}

		if ( count( $qs ) ) {
			$listing_link .= ( strpos( $listing_link, '?' ) ? '&' : '?' ) . join( '&', $qs );
		}

		return $listing_link;
	}
}

if ( ! function_exists( 'stm_filter_listing_link' ) ) {
	function stm_filter_listing_link( $listing_link = '', $filters = array() ) {
		return stm_get_listing_archive_link( $filters );
	}

	add_filter( 'stm_filter_listing_link', 'stm_filter_listing_link', 10, 2 );
}

if ( ! function_exists( 'stm_listings_parent_list' ) ) {
	function stm_listings_parent_list( $html = false ) {
		$response = $html ? '' : array();
		$filter   = apply_filters( 'stm_listings_filter_func', null, true );

		if ( empty( $filter ) || ! isset( $filter['filters'] ) || ! is_array( $filter['filters'] ) ) {
			return $response;
		}

		$slugs = wp_filter_object_list( $filter['filters'], array( 'listing_taxonomy_parent' => true ), 'AND', 'listing_taxonomy_parent' );
		if ( $slugs ) :
			if ( $html ) :
				$slugs = implode( ',', $slugs );
				ob_start();
				?>
				<span
						id="stm_parent_slug_list"
						style="display: none;"
						data-value="<?php echo esc_attr( $slugs ); ?>">
				</span>
				<?php
				$response = ob_get_clean();
			else :
				$response = $slugs;
			endif;
		endif;

		return $response;
	}
}

add_filter( 'stm_listings_parent_list', 'stm_listings_parent_list' );

if ( ! function_exists( 'stm_listings_parent_list_response' ) ) {
	function stm_listings_parent_list_response() {
		echo wp_kses_post( apply_filters( 'stm_listings_parent_list', true ) );
	}
}

add_action( 'stm_listings_filter_before', 'stm_listings_parent_list_response' );

if ( ! function_exists( 'stm_listings_add_filter_nonce' ) ) {
	function stm_listings_filter_nonce( $display = true, $action = 'stm_security_nonce', $name = 'security', $referer = false ) {
		$nonce_field = wp_nonce_field( $action, $name, $referer, false );

		if ( $display ) {
			echo wp_kses_post( $nonce_field );
		}

		return $nonce_field;
	}
}

add_filter( 'stm_listings_filter_nonce', 'stm_listings_filter_nonce', 10, 4 );

if ( ! function_exists( 'stm_listings_filter_nonce_response' ) ) {
	function stm_listings_filter_nonce_response() {
		echo wp_kses_post( apply_filters( 'stm_listings_filter_nonce', false ) );
	}
}

add_action( 'stm_listings_filter_before', 'stm_listings_filter_nonce_response', 15 );

if ( ! function_exists( 'stm_listing_price_view' ) ) {
	/**
	 * Price delimeter
	 */
	function stm_listing_price_view( $response, $price = '' ) {
		if ( ! empty( $price ) || 0 === $price ) {
			$price_label          = stm_get_price_currency();
			$price_label_position = apply_filters( 'stm_me_get_nuxy_mod', 'left', 'price_currency_position' );
			$price_delimeter      = apply_filters( 'stm_me_get_nuxy_mod', ' ', 'price_delimeter' );

			if ( strpos( $price, '<' ) !== false || strpos( $price, '>' ) !== false ) {
				$price_convert = number_format( apply_filters( 'get_conver_price', filter_var( $price, FILTER_SANITIZE_NUMBER_INT ) ), 0, '', $price_delimeter );
			} elseif ( strpos( $price, '-' ) !== false ) {
				$price_array   = explode( '-', $price );
				$price_l       = ( ! empty( $price_array[0] ) ) ? number_format( apply_filters( 'get_conver_price', $price_array[0] ), 0, '', $price_delimeter ) : '';
				$price_r       = ( ! empty( $price_array[1] ) ) ? number_format( apply_filters( 'get_conver_price', $price_array[1] ), 0, '', $price_delimeter ) : '';
				$price_convert = ( ! empty( $price_l ) && ! empty( $price_r ) ) ? $price_l . '-' . $price_r : ( ( ! empty( $price_l ) ) ? $price_l : $price_r );
			} else {
				$price_convert = number_format( apply_filters( 'get_conver_price', floatval( $price ) ), 0, '', $price_delimeter );
			}

			if ( 'left' === $price_label_position ) {

				$response = $price_label . $price_convert;

				if ( strpos( $price, '<' ) !== false ) {
					$response = '&lt; ' . $price_label . $price_convert;
				} elseif ( strpos( $price, '>' ) !== false ) {
					$response = '&gt; ' . $price_label . $price_convert;
				}
			} else {
				$response = $price_convert . $price_label;

				if ( strpos( $price, '<' ) !== false ) {
					$response = '&lt; ' . $price_convert . $price_label;
				} elseif ( strpos( $price, '>' ) !== false ) {
					$response = '&gt; ' . $price_convert . $price_label;
				}
			}

			return $response;
		}
	}

	add_filter( 'stm_filter_price_view', 'stm_listing_price_view', 10, 2 );
}

if ( ! function_exists( 'stm_get_price_currency' ) ) {
	/**
	 * Get price currency
	 */
	function stm_get_price_currency() {
		$currency = apply_filters( 'stm_me_get_nuxy_mod', '$', 'price_currency' );
		if ( isset( $_COOKIE['stm_current_currency'] ) ) {
			$cookie   = explode( '-', $_COOKIE['stm_current_currency'] );
			$currency = $cookie[0];
		}

		return $currency;
	}
}

if ( ! function_exists( 'stm_add_admin_body_class' ) ) {
	/**
	 * Add class
	 */
	function stm_add_admin_body_class( $classes ) {
		$name = 'not_motors';
		if ( stm_check_motors() ) {
			$name = '';
		}

		return apply_filters( 'stm_listings_admin_body_class', "$classes stm-template-" . $name );
	}

	add_filter( 'admin_body_class', 'stm_add_admin_body_class' );
}


if ( ! function_exists( 'stm_get_post_limits' ) ) {
	/**
	 * Get user adds and media limitations
	 *
	 * @param $user_id
	 *
	 * @return mixed|void
	 */
	function stm_get_post_limits( $default, $user_id = false, $post_status = '' ) {
		$listing_type = stm_listings_multi_type( true );

		$user_id = intval( $user_id );

		$restrictions = array(
			'premoderation' => apply_filters( 'stm_me_get_nuxy_mod', false, 'user_premoderation' ),
			'posts_allowed' => intval( apply_filters( 'stm_me_get_nuxy_mod', 3, 'user_post_limit' ) ),
			'posts'         => intval( apply_filters( 'stm_me_get_nuxy_mod', 3, 'user_post_limit' ) ),
			'images'        => intval( apply_filters( 'stm_me_get_nuxy_mod', 5, 'user_post_images_limit' ) ),
			'role'          => 'user',
		);

		if ( ! empty( $user_id ) ) {

			$dealer = apply_filters( 'stm_get_user_role', false, $user_id );

			if ( $dealer ) {
				$restrictions['posts_allowed'] = intval( apply_filters( 'stm_me_get_nuxy_mod', 50, 'dealer_post_limit' ) );
				$restrictions['premoderation'] = apply_filters( 'stm_me_get_nuxy_mod', false, 'dealer_premoderation' );
				$restrictions['images']        = intval( apply_filters( 'stm_me_get_nuxy_mod', 10, 'dealer_post_images_limit' ) );
				$restrictions['role']          = 'dealer';
			}

			if ( apply_filters( 'stm_pricing_enabled', false ) ) {
				$current_quota = stm_user_active_subscriptions( false, $user_id );
				if ( ! empty( $current_quota['multi_plans_images_limit'] ) ) {
					$current_quota['multi_plans_images_limit']['free'] = array( 'limit' => $restrictions['images'] );
					$restrictions['multi_plans_images_limit']          = $current_quota['multi_plans_images_limit'];
				}

				if ( ! empty( $current_quota['post_limit'] ) && ! empty( $current_quota['image_limit'] ) ) {
					$restrictions['posts_allowed'] = intval( $current_quota['post_limit'] );
					$restrictions['images']        = intval( $current_quota['image_limit'] );
				}
			}

			$restrictions = apply_filters( 'stm_user_restrictions', $restrictions, $user_id );

			$query = new WP_Query(
				array(
					'post_type'      => $listing_type,
					'post_status'    => ( ! empty( $post_status ) ) ? 'publish' : array(
						'publish',
						'pending',
						'draft',
					),
					'posts_per_page' => 1,
					'meta_query'     => array(
						'relation' => 'AND',
						array(
							'key'     => 'stm_car_user',
							'value'   => $user_id,
							'compare' => '=',
						),
						array(
							'key'     => 'pay_per_listing',
							'compare' => 'NOT EXISTS',
							'value'   => '',
						),
					),
				)
			);

			/*IF is admin, set all */
			if ( user_can( $user_id, 'manage_options' ) ) {
				$restrictions['premoderation'] = false;
				$restrictions['posts_allowed'] = '9999';
				$restrictions['images']        = '9999';
				$restrictions['role']          = 'user';
			}

			$restrictions['posts'] = max( 0, intval( $restrictions['posts_allowed'] ) - intval( $query->found_posts ) );
		}

		return $restrictions;
	}

	add_filter( 'stm_get_post_limits', 'stm_get_post_limits', 10, 3 );
}

if ( ! function_exists( 'stm_get_user_custom_fields' ) ) {
	/**
	 * Get user additional information
	 *
	 * @param $user_id
	 *
	 * @return mixed|void
	 */
	function stm_get_user_custom_fields( $user_id ) {
		$response = array();

		if ( empty( $user_id ) ) {
			$user_current = wp_get_current_user();
			$user_id      = $user_current->ID;
		}

		$user_phone     = get_the_author_meta( 'stm_phone', $user_id );
		$has_whatsapp   = get_the_author_meta( 'stm_whatsapp_number', $user_id );
		$user_mail      = get_the_author_meta( 'email', $user_id );
		$user_show_mail = get_the_author_meta( 'stm_show_email', $user_id );
		$user_name      = get_the_author_meta( 'first_name', $user_id );
		$user_last_name = get_the_author_meta( 'last_name', $user_id );
		$user_image     = get_the_author_meta( 'stm_user_avatar', $user_id );
		$socials        = array( 'facebook', 'twitter', 'linkedin', 'youtube' );
		$user_socials   = array();

		foreach ( $socials as $social ) {
			$user_soc = get_the_author_meta( 'stm_user_' . $social, $user_id );
			if ( ! empty( $user_soc ) ) {
				$user_socials[ $social ] = $user_soc;
			}
		}

		$response['user_id']             = $user_id;
		$response['phone']               = $user_phone;
		$response['stm_whatsapp_number'] = $has_whatsapp;
		$response['image']               = $user_image;
		$response['name']                = $user_name;
		$response['last_name']           = $user_last_name;
		$response['socials']             = $user_socials;
		$response['email']               = $user_mail;
		$response['show_mail']           = $user_show_mail;

		/*Dealer fields*/
		$logo                = get_the_author_meta( 'stm_dealer_logo', $user_id );
		$dealer_image        = get_the_author_meta( 'stm_dealer_image', $user_id );
		$license             = get_the_author_meta( 'stm_company_license', $user_id );
		$website             = get_the_author_meta( 'stm_website_url', $user_id );
		$location            = get_the_author_meta( 'stm_dealer_location', $user_id );
		$location_lat        = get_the_author_meta( 'stm_dealer_location_lat', $user_id );
		$location_lng        = get_the_author_meta( 'stm_dealer_location_lng', $user_id );
		$stm_company_name    = get_the_author_meta( 'stm_company_name', $user_id );
		$stm_company_license = get_the_author_meta( 'stm_company_license', $user_id );
		$stm_message_to_user = get_the_author_meta( 'stm_message_to_user', $user_id );
		$stm_sales_hours     = get_the_author_meta( 'stm_sales_hours', $user_id );
		$stm_seller_notes    = get_the_author_meta( 'stm_seller_notes', $user_id );
		$stm_payment_status  = get_the_author_meta( 'stm_payment_status', $user_id );

		$response['logo']                = $logo;
		$response['dealer_image']        = $dealer_image;
		$response['license']             = $license;
		$response['website']             = $website;
		$response['location']            = $location;
		$response['location_lat']        = $location_lat;
		$response['location_lng']        = $location_lng;
		$response['stm_company_name']    = $stm_company_name;
		$response['stm_company_license'] = $stm_company_license;
		$response['stm_message_to_user'] = $stm_message_to_user;
		$response['stm_sales_hours']     = $stm_sales_hours;
		$response['stm_seller_notes']    = $stm_seller_notes;
		$response['stm_payment_status']  = $stm_payment_status;

		return apply_filters( 'stm_filter_user_fields', $response );

	}
}

if ( ! function_exists( 'stm_display_user_name' ) ) {
	/**
	 * User display name
	 *
	 * @param $user_id
	 * @param string $user_login
	 * @param string $f_name
	 * @param string $l_name
	 */
	function stm_display_user_name( $user_id, $user_login = '', $f_name = '', $l_name = '' ) {
		$user = get_userdata( $user_id );

		if ( empty( $user_login ) ) {
			$login = ( ! empty( $user ) ) ? $user->get( 'user_login' ) : '';
		} else {
			$login = $user_login;
		}
		if ( empty( $f_name ) ) {
			$first_name = get_the_author_meta( 'first_name', $user_id );
		} else {
			$first_name = $f_name;
		}

		if ( empty( $l_name ) ) {
			$last_name = get_the_author_meta( 'last_name', $user_id );
		} else {
			$last_name = $l_name;
		}

		$display_name = $login;

		if ( ! empty( $first_name ) ) {
			$display_name = $first_name;
		}

		if ( ! empty( $first_name ) && ! empty( $last_name ) ) {
			$display_name .= ' ' . $last_name;
		}

		if ( empty( $first_name ) && ! empty( $last_name ) ) {
			$display_name = $last_name;
		}

		echo wp_kses_post( apply_filters( 'stm_filter_display_user_name', $display_name, $user_id, $user_login, $f_name, $l_name ) );
	}
}

if ( ! function_exists( 'stm_custom_login' ) ) {
	// login from header dropdown or add listing page bottom
	function stm_custom_login() {
		$errors = array();

		if ( empty( $_POST['stm_user_login'] ) ) {
			$errors['stm_user_login'] = true;
		} else {
			$username = sanitize_text_field( $_POST['stm_user_login'] );
		}

		if ( empty( $_POST['stm_user_password'] ) ) {
			$errors['stm_user_password'] = true;
		} else {
			$password = $_POST['stm_user_password'];
		}

		if ( isset( $_POST['redirect_path'] ) ) {
			$redirect_path = $_POST['redirect_path'];
		}

		$remember = false;

		if ( ! empty( $_POST['stm_remember_me'] ) && 'on' === $_POST['stm_remember_me'] ) {
			$remember = true;
		}

		if ( ! empty( $_POST['redirect'] ) && 'disable' === $_POST['redirect'] ) {
			$redirect = false;
		} else {
			$redirect = true;
		}

		// authenticate user
		$errors = apply_filters( 'stm_user_login', $errors, $username );

		if ( empty( $errors ) ) {
			if ( filter_var( $username, FILTER_VALIDATE_EMAIL ) ) {
				$user = get_user_by( 'email', $username );
			} else {
				$user = get_user_by( 'login', $username );
			}

			if ( $user ) {
				$username = $user->data->user_login;
			}

			$creds                  = array();
			$creds['user_login']    = $username;
			$creds['user_password'] = $password;
			$creds['remember']      = $remember;
			$secure_cookie          = is_ssl() ? true : false;

			$user = wp_signon( $creds, $secure_cookie );

			if ( is_wp_error( $user ) ) {
				$response['message'] = esc_html__( 'Wrong Username or Password', 'stm_vehicles_listing' );
			} else {

				// enable other functions to discover current user before next page reload
				wp_set_current_user( $user->ID );

				if ( $redirect ) {
					$response['message'] = esc_html__( 'Successfully logged in. Redirecting...', 'stm_vehicles_listing' );

					$wpml_url = ( ! empty( $redirect_path ) ) ? $redirect_path : get_author_posts_url( $user->ID );

					if ( class_exists( 'SitePress' ) && isset( $_POST['current_lang'] ) ) {
						$wpml_url = apply_filters( 'wpml_permalink', $wpml_url, $_POST['current_lang'], true );
					}

					$response['redirect_url'] = $wpml_url;
				} else {
					ob_start();
					stm_add_a_car_user_info( '', '', '', $user->ID );
					$restricted            = false;
					$restrictions          = apply_filters(
						'stm_get_post_limits',
						array(
							'premoderation' => true,
							'posts_allowed' => 0,
							'posts'         => 0,
							'images'        => 0,
							'role'          => 'user',
						),
						$user->ID
					);
					$response['user_html'] = ob_get_clean();

					// if logged in from add car page, fetch user's available plans
					$logged_in_from_add_car = false;

					if ( isset( $_POST['fetch_plans'] ) && 'true' === $_POST['fetch_plans'] ) {
						$logged_in_from_add_car = true;
					}

					if ( apply_filters( 'stm_me_get_nuxy_mod', false, 'enable_plans' ) && apply_filters( 'stm_is_multiple_plans', false ) && $logged_in_from_add_car ) {

						$multi               = new MultiplePlan();
						$multi::$currentUser = $user->ID;
						$multi->buildPlansMeta();
						$multi->disableExpiredListings();
						$plans = $multi::getPlans();

						if ( ! empty( $plans['total_quota'] ) ) {
							$restrictions['posts'] = intval( $plans['total_quota'] );
						}

						$response['plans_select']  = '<div class="user-plans-list" >';
						$response['plans_select'] .= '<select name="selectedPlan">';
						$response['plans_select'] .= '<option value="">' . esc_html__( 'Select Plan', 'stm_vehicles_listing' ) . '</option>';

						foreach ( $plans['plans'] as $plan ) {
							$disabled = '';
							if ( $plan['used_quota'] === $plan['total_quota'] ) {
								$disabled = 'disabled';
							}

							$response['plans_select'] .= '<option value="' . esc_attr( $plan['plan_id'] ) . '" ' . esc_attr( $disabled ) . '>';
							$response['plans_select'] .= sprintf( ( '%s %s / %s' ), $plan['label'], $plan['used_quota'], $plan['total_quota'] );
							$response['plans_select'] .= '</option>';
						}

						$response['plans_select'] .= '</select>';
						$response['plans_select'] .= '</div>';
					}

					if ( $restrictions['posts'] < 1 ) {
						$restricted = true;
					}

					$response['restricted'] = $restricted;
				}
			}
		} else {
			$response['message'] = esc_html__( 'Please fill required fields', 'stm_vehicles_listing' );
		}

		$response['errors'] = $errors;

		wp_send_json( apply_filters( 'stm_filter_custom_login', $response ) );
	}

	add_action( 'wp_ajax_stm_custom_login', 'stm_custom_login' );
	add_action( 'wp_ajax_nopriv_stm_custom_login', 'stm_custom_login' );
}

if ( ! function_exists( 'stm_custom_register' ) ) {
	// registration from header dropdown or add listing page bottom
	function stm_custom_register() {
		$response = array();
		$errors   = array();

		if ( empty( $_POST['stm_nickname'] ) ) {
			$errors['stm_nickname'] = true;
		} else {
			$user_login = sanitize_text_field( $_POST['stm_nickname'] );
		}

		if ( empty( $_POST['stm_user_first_name'] ) ) {
			$user_name = '';
		} else {
			$user_name = sanitize_text_field( $_POST['stm_user_first_name'] );
		}

		if ( empty( $_POST['stm_user_last_name'] ) ) {
			$user_lastname = '';
		} else {
			$user_lastname = sanitize_text_field( $_POST['stm_user_last_name'] );
		}

		$recaptcha_enabled    = apply_filters( 'stm_me_get_nuxy_mod', 0, 'enable_recaptcha' );
		$recaptcha_secret_key = apply_filters( 'stm_me_get_nuxy_mod', '', 'recaptcha_secret_key' );

		if ( $recaptcha_enabled && isset( $_POST['g-recaptcha-response'] ) && ! stm_motors_check_recaptcha( $recaptcha_secret_key, $_POST['g-recaptcha-response'] ) ) {
			$errors['captcha']   = true;
			$response['message'] = esc_html__( 'Please, enter captcha', 'stm_vehicles_listing' );
		}

		if ( empty( $_POST['stm_user_phone'] ) ) {
			$user_phone = '';
		} elseif ( empty( $_POST['stm_user_phone'] ) ) {
			$errors['stm_user_phone'] = true;
		} else {
			$user_phone = sanitize_text_field( $_POST['stm_user_phone'] );
		}

		if ( ! is_email( $_POST['stm_user_mail'] ) ) {
			$errors['stm_user_mail'] = true;
		} else {
			$user_mail = sanitize_email( $_POST['stm_user_mail'] );
		}

		if ( empty( $_POST['stm_user_password'] ) ) {
			$errors['stm_user_password'] = true;
		} else {
			$user_pass = $_POST['stm_user_password'];
		}

		if ( ! empty( $_POST['redirect'] ) && 'disable' === $_POST['redirect'] ) {
			$redirect = false;
		} else {
			$redirect = true;
		}

		$demo = stm_is_site_demo_mode();
		if ( $demo ) {
			$errors['demo'] = true;
		}

		if ( empty( $errors ) ) {
			$user_data = array(
				'user_login' => $user_login,
				'user_pass'  => $user_pass,
				'first_name' => $user_name,
				'last_name'  => $user_lastname,
				'user_email' => $user_mail,
			);

			$user_id = wp_insert_user( $user_data );

			if ( ! is_wp_error( $user_id ) && apply_filters( 'stm_me_get_nuxy_mod', false, 'enable_email_confirmation' ) ) {

				$user_data['user_phone'] = $user_phone;

				stm_handle_premoderation( $user_id, $user_data );

				$response['message'] = esc_html__( 'Please confirm your email', 'stm_vehicles_listing' );

				wp_send_json( $response );
			}

			if ( ! is_wp_error( $user_id ) ) {
				update_user_meta( $user_id, 'stm_phone', $user_phone );
				update_user_meta( $user_id, 'stm_show_email', 'on' );

				// number has whatsapp
				if ( ! empty( $_POST['stm_whatsapp_number'] ) && 'on' === $_POST['stm_whatsapp_number'] ) {
					update_user_meta( $user_id, 'stm_whatsapp_number', 'on' );
				} else {
					update_user_meta( $user_id, 'stm_whatsapp_number', '' );
				}

				// When using caching plugins user sessions are not working properly
				// deleting user meta cache should solve this issue
				wp_cache_delete( $user_id, 'user_meta' );

				wp_set_current_user( $user_id, $user_login );

				wp_set_auth_cookie( $user_id );

				do_action( 'wp_login', $user_login, new WP_User( $user_id ) );

				if ( $redirect ) {
					$response['message']      = esc_html__( 'Congratulations! You have been successfully registered. Redirecting to your account profile page.', 'stm_vehicles_listing' );
					$response['redirect_url'] = get_author_posts_url( $user_id );
				} else {
					ob_start();
					stm_add_a_car_user_info( $user_login, $user_name, $user_lastname, $user_id );
					$restricted   = false;
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

					$response['user_html'] = ob_get_clean();

					if ( apply_filters( 'stm_me_get_nuxy_mod', false, 'enable_plans' ) && apply_filters( 'stm_is_multiple_plans', false ) ) {

						$multi               = new MultiplePlan();
						$multi::$currentUser = $user->ID;
						$multi->buildPlansMeta();
						$multi->disableExpiredListings();
						$plans = $multi::getPlans();

						if ( ! empty( $plans['total_quota'] ) ) {
							$restrictions['posts'] = intval( $plans['total_quota'] );
						}

						$response['plans_select']  = '<div class="user-plans-list" >';
						$response['plans_select'] .= '<select name="selectedPlan">';
						$response['plans_select'] .= '<option value="">' . esc_html__( 'Select Plan', 'stm_vehicles_listing' ) . '</option>';

						foreach ( $plans['plans'] as $plan ) {
							$disabled = '';
							if ( $plan['used_quota'] === $plan['total_quota'] ) {
								$disabled = 'disabled';
							}

							$response['plans_select'] .= '<option value="' . esc_attr( $plan['plan_id'] ) . '" ' . esc_attr( $disabled ) . '>';
							$response['plans_select'] .= sprintf( ( '%s %s / %s' ), $plan['label'], $plan['used_quota'], $plan['total_quota'] );
							$response['plans_select'] .= '</option>';
						}

						$response['plans_select'] .= '</select>';
						$response['plans_select'] .= '</div>';
					}

					if ( $restrictions['posts'] < 1 ) {
						$restricted = true;
					}

					$response['restricted'] = $restricted;

				}

				// AUTH
				do_action( 'stm_register_new_user', $user_id );

				if ( (int) get_option( 'users_can_register' ) ) {
					$response['message'] = esc_html__( 'Congratulations! You have been successfully registered. Please, activate your account', 'stm_vehicles_listing' );
				} else {
					wp_set_current_user( $user_id, $user_login );

					wp_set_auth_cookie( $user_id );

					do_action( 'wp_login', $user_login, new WP_User( $user_id ) );

					if ( $redirect ) {
						$response['message']      = esc_html__( 'Congratulations! You have been successfully registered. Redirecting to your account profile page.', 'stm_vehicles_listing' );
						$response['redirect_url'] = get_author_posts_url( $user_id );
					} else {
						ob_start();
						stm_add_a_car_user_info( $user_login, $user_name, $user_lastname, $user_id );
						$restricted   = false;
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

						if ( $restrictions['posts'] < 1 && apply_filters( 'stm_me_get_nuxy_mod', false, 'dealer_pay_per_listing' ) ) {
							$restricted = true;
						}

						$response['restricted'] = $restricted;
						$response['user_html']  = ob_get_clean();
					}
				}

				add_filter( 'wp_mail_content_type', 'stm_set_html_content_type_mail' );

				/*Mail admin*/
				$to      = get_bloginfo( 'admin_email' );
				$subject = stm_generate_subject_view( 'new_user', array( 'user_login' => $user_login ) );
				$body    = stm_generate_template_view( 'new_user', array( 'user_login' => $user_login ) );

				wp_mail( $to, $subject, $body );

				/*Mail user*/
				$email_subject = stm_generate_subject_view( 'welcome', array( 'user_login' => $user_login ) );
				$email_body    = stm_generate_template_view( 'welcome', array( 'user_login' => $user_login ) );
				wp_mail( $user_mail, $email_subject, $email_body );

				remove_filter( 'wp_mail_content_type', 'stm_set_html_content_type_mail' );

			} else {
				$response['message'] = $user_id->get_error_message();
				$response['user']    = $user_id;
			}
		} else {

			if ( $demo ) {
				$response['message'] = esc_html__( 'Site is on demo mode', 'stm_vehicles_listing' );
			} else {
				$response['message'] = esc_html__( 'Please fill required fields', 'stm_vehicles_listing' );
			}
		}

		$response['errors'] = $errors;

		wp_send_json( apply_filters( 'stm_filter_custom_register', $response ) );
	}

	add_action( 'wp_ajax_stm_custom_register', 'stm_custom_register' );
	add_action( 'wp_ajax_nopriv_stm_custom_register', 'stm_custom_register' );
}

if ( ! function_exists( 'stm_handle_premoderation' ) ) {
	function stm_handle_premoderation( $user_id, $data ) {
		$token = bin2hex( openssl_random_pseudo_bytes( 16 ) );

		/*Setting link for 3 days*/
		set_transient( $token, $data, 3 * 24 * 60 * 60 );

		/*Delete User first and save his data to transient*/
		require_once ABSPATH . 'wp-admin/includes/ms.php';

		wp_delete_user( $user_id );
		wpmu_delete_user( $user_id );

		$login_page = apply_filters( 'stm_me_get_nuxy_mod', 1718, 'login_page' );

		$reset_url = get_the_permalink( $login_page ) . '?user_token=' . $token;
		add_filter( 'wp_mail_content_type', 'stm_set_html_content_type_mail' );

		/*Mail user*/
		add_filter( 'wp_mail_content_type', 'stm_set_html_content_type_mail' );

		$email_subject = stm_generate_subject_view( 'user_email_confirmation', array( 'site_name' => get_option( 'blogname' ) ) );
		$email_body    = stm_generate_template_view(
			'user_email_confirmation',
			array(
				'user_login'        => $data['user_login'],
				'confirmation_link' => $reset_url,
				'site_name'         => get_option( 'blogname' ),
			)
		);

		wp_mail( $data['user_email'], $email_subject, $email_body );
		remove_filter( 'wp_mail_content_type', 'stm_set_html_content_type_mail' );
	}
}

if ( ! function_exists( 'stm_verify_user_by_token' ) ) {
	function stm_verify_user_by_token() {
		$token = sanitize_text_field( $_GET['user_token'] );

		$data = get_transient( $token );

		if ( ! empty( $data ) ) {
			$user_data = $data;

			$user_login = $user_data['user_login'];
			$user_phone = $user_data['user_phone'];

			unset( $user_data['user_phone'] );

			$user_id = wp_insert_user( $user_data );

			if ( ! is_wp_error( $user_id ) ) {
				$redirect_url = get_author_posts_url( $user_id );

				update_user_meta( $user_id, 'stm_phone', $user_phone );
				update_user_meta( $user_id, 'stm_show_email', 'on' );

				wp_cache_delete( $user_id, 'user_meta' );

				wp_set_current_user( $user_id, $data['user_login'] );
				wp_set_auth_cookie( $user_id );
				do_action( 'wp_login', $user_login, new WP_User( $user_id ) );

				add_filter( 'wp_mail_content_type', 'stm_set_html_content_type_mail' );

				/*Mail admin*/
				$to      = get_bloginfo( 'admin_email' );
				$subject = stm_generate_subject_view( 'new_user', array( 'user_login' => $user_login ) );
				$body    = stm_generate_template_view( 'new_user', array( 'user_login' => $user_login ) );

				wp_mail( $to, $subject, $body );

				remove_filter( 'wp_mail_content_type', 'stm_set_html_content_type_mail' );

				wp_safe_redirect( $redirect_url );
			}
		}
	}

	add_action(
		'template_redirect',
		function () {
			if ( ! empty( $_GET['user_token'] ) ) {
				stm_verify_user_by_token();
			}
		}
	);
}

if ( ! function_exists( 'stm_add_a_car_user_info' ) ) {
	/**
	 * Add car user info
	 *
	 * @param string $user_login
	 * @param string $f_name
	 * @param string $l_name
	 * @param string $user_id
	 */
	function stm_add_a_car_user_info( $user_login = '', $f_name = '', $l_name = '', $user_id = '' ) {
		$user = stm_get_user_custom_fields( $user_id );
		if ( ! is_wp_error( $user ) ) {

			$user_id = $user['user_id'];
			$dealer  = apply_filters( 'stm_get_user_role', false, $user['user_id'] );

			do_action( 'stm_car_user_info_before_action' );
			?>

			<div class="stm-add-a-car-user">
				<div class="stm-add-a-car-user-wrapper">

					<?php
					if ( $dealer ) :
						$ratings = stm_get_dealer_marks( $user_id );
						?>

						<div class="left-info left-dealer-info">
							<div class="stm-dealer-image-custom-view">
								<?php if ( ! empty( $user['logo'] ) ) : ?>
									<img src="<?php echo esc_url( $user['logo'] ); ?>"/>
								<?php else : ?>
									<img src="<?php stm_get_dealer_logo_placeholder(); ?>"/>
								<?php endif; ?>
							</div>
							<h4><?php stm_display_user_name( $user['user_id'], $user_login, $f_name, $l_name ); ?></h4>

							<?php if ( ! empty( $ratings['average'] ) ) : ?>
								<div class="stm-star-rating">
									<div class="inner">
										<div class="stm-star-rating-upper" style="width:<?php echo esc_attr( $ratings['average_width'] ); ?>"></div>
										<div class="stm-star-rating-lower"></div>
									</div>
									<div class="heading-font"><?php echo wp_kses_post( $ratings['average'] ); ?></div>
								</div>
							<?php endif; ?>

						</div>

					<?php else : ?>

						<div class="left-info">
							<div class="avatar">
								<?php if ( ! empty( $user['image'] ) ) : ?>
									<img src="<?php echo esc_url( $user['image'] ); ?>"/>
								<?php else : ?>
									<i class="stm-service-icon-user"></i>
								<?php endif; ?>
							</div>
							<div class="user-info">
								<h4><?php stm_display_user_name( $user['user_id'], $user_login, $f_name, $l_name ); ?></h4>
								<div class="stm-label"><?php esc_html_e( 'Private Seller', 'stm_vehicles_listing' ); ?></div>
							</div>
						</div>

					<?php endif; ?>

					<div class="right-info">

						<a target="_blank" href="<?php echo esc_url( add_query_arg( array( 'view-myself' => 1 ), get_author_posts_url( $user_id ) ) ); ?>">
							<i class="fas fa-external-link-alt"></i><?php esc_html_e( 'Show my Public Profile', 'stm_vehicles_listing' ); ?>
						</a>

						<div class="stm_logout">
							<a href="#"><?php esc_html_e( 'Log out', 'stm_vehicles_listing' ); ?></a>
							<?php esc_html_e( 'to choose a different account', 'stm_vehicles_listing' ); ?>
						</div>

					</div>

				</div>
			</div>

			<?php

		}

		do_action( 'stm_car_user_info_after_action' );
	}
}

if ( ! function_exists( 'stm_logout_user' ) ) {
	/**
	 * Logout
	 */
	function stm_logout_user() {
		$response = array();
		wp_logout();
		$response['exit'] = true;
		wp_send_json( $response );
	}

	add_action( 'wp_ajax_stm_logout_user', 'stm_logout_user' );
	add_action( 'wp_ajax_nopriv_stm_logout_user', 'stm_logout_user' );
}

if ( ! function_exists( 'stm_is_site_demo_mode' ) ) {
	/**
	 * Site demo mode
	 *
	 * @return string
	 */
	function stm_is_site_demo_mode() {

		$site_demo_mode = apply_filters( 'stm_me_get_nuxy_mod', false, 'site_demo_mode' );

		return apply_filters( 'stm_site_demo_mode', $site_demo_mode );
	}
}

if ( ! function_exists( 'stm_ajax_add_a_car' ) ) {
	/**
	 * Add a car
	 *
	 * @return bool
	 */
	function stm_ajax_add_a_car() {
		$response     = array();
		$first_step   = array();
		$second_step  = array();
		$car_features = array();
		$videos       = array();
		$notes        = esc_html__( 'N/A', 'stm_vehicles_listing' );
		$registered   = '';
		$vin          = '';
		$history      = array(
			'label' => '',
			'link'  => '',
		);
		$location     = array(
			'label'   => '',
			'lat'     => '',
			'lng'     => '',
			'address' => '',
		);

		if ( ! is_user_logged_in() ) {
			$response['message'] = esc_html__( 'Please, log in', 'stm_vehicles_listing' );

			return false;
		} else {
			$user         = stm_get_user_custom_fields( '' );
			$restrictions = apply_filters(
				'stm_get_post_limits',
				array(
					'premoderation' => true,
					'posts_allowed' => 0,
					'posts'         => 0,
					'images'        => 0,
					'role'          => 'user',
				),
				$user['user_id']
			);
		}

		$response['message'] = '';
		$error               = false;

		$demo = stm_is_site_demo_mode();
		if ( $demo ) {
			$error               = true;
			$response['message'] = esc_html__( 'Site is on demo mode', 'stm_vehicles_listing' );
		}

		$update = false;
		if ( ! empty( $_POST['stm_current_car_id'] ) ) {
			$post_id  = intval( $_POST['stm_current_car_id'] );
			$car_user = get_post_meta( $post_id, 'stm_car_user', true );
			$update   = true;

			/*Check if current user edits his car*/
			if ( intval( $car_user ) !== intval( $user['user_id'] ) ) {
				return false;
			}
		}

		/*Get first step*/
		if ( ! empty( $_POST['stm_f_s'] ) ) {
			foreach ( $_POST['stm_f_s'] as $post_key => $post_value ) {
				if ( ! empty( $_POST['stm_f_s'][ $post_key ] ) ) {
					$original_post_key                                  = str_replace( '_pre_', '-', $post_key );
					$first_step[ sanitize_title( $original_post_key ) ] = sanitize_title( $_POST['stm_f_s'][ $post_key ] );
				} else {
					$response['message'] = esc_html__( 'Enter required fields', 'stm_vehicles_listing' );
					$error               = true;
				}
			}
		}

		if ( empty( $first_step ) ) {
			$response['message'] = esc_html__( 'Enter required fields', 'stm_vehicles_listing' );
			$error               = true;
		}

		/*Get if no available posts*/
		if ( $restrictions['posts'] < 1 && false === $update ) {
			$response['message'] = esc_html__( 'You do not have available posts', 'stm_vehicles_listing' );
			$error               = true;
		}

		/*Getting second step*/
		foreach ( $_POST as $second_step_key => $second_step_value ) {
			if ( strpos( $second_step_key, 'stm_s_s_' ) !== false ) {
				if ( ! empty( $_POST[ $second_step_key ] ) ) {
					$original_key                                   = str_replace( 'stm_s_s_', '', $second_step_key );
					$second_step[ sanitize_title( $original_key ) ] = sanitize_text_field( $_POST[ $second_step_key ] );
				}
			}
		}

		/*Getting car features*/
		if ( ! empty( $_POST['stm_car_features_labels'] ) ) {
			foreach ( $_POST['stm_car_features_labels'] as $car_feature ) {
				$car_features[] = sanitize_text_field( $car_feature );
			}
		}

		/*Videos*/
		if ( ! empty( $_POST['stm_video'] ) ) {
			foreach ( $_POST['stm_video'] as $video ) {
				$videos[] = esc_url( $video );
			}
		}

		/*Note*/
		if ( ! empty( $_POST['stm_seller_notes'] ) ) {
			$notes = sanitize_textarea_field( $_POST['stm_seller_notes'] );
		}

		/*Registration date*/
		if ( ! empty( $_POST['stm_registered'] ) ) {
			$registered = sanitize_text_field( $_POST['stm_registered'] );
		}

		/*Vin*/
		if ( ! empty( $_POST['stm_vin'] ) ) {
			$vin = sanitize_text_field( $_POST['stm_vin'] );
		}

		/*History*/
		if ( ! empty( $_POST['stm_history_label'] ) ) {
			$history['label'] = sanitize_text_field( $_POST['stm_history_label'] );
		}

		if ( ! empty( $_POST['stm_history_link'] ) ) {
			$history['link'] = esc_url( $_POST['stm_history_link'] );
		}

		/*Location*/
		if ( ! empty( $_POST['stm_location_text'] ) ) {
			$location['label'] = sanitize_text_field( $_POST['stm_location_text'] );
		}

		if ( ! empty( $_POST['stm_lat'] ) ) {
			$location['lat'] = sanitize_text_field( $_POST['stm_lat'] );
		}

		if ( ! empty( $_POST['stm_lng'] ) ) {
			$location['lng'] = sanitize_text_field( $_POST['stm_lng'] );
		}

		if ( ! empty( $_POST['stm_location_address'] ) ) {
			$location['address'] = sanitize_text_field( $_POST['stm_location_address'] );
		}

		if ( empty( $_POST['stm_car_price'] ) ) {
			$error               = true;
			$response['message'] = esc_html__( 'Please add item price', 'stm_vehicles_listing' );
		} else {
			$price = stm_convert_to_normal_price( abs( intval( $_POST['stm_car_price'] ) ) );
		}

		if ( ! empty( $_POST['car_price_form_label'] ) ) {
			$location['car_price_form_label'] = sanitize_text_field( $_POST['car_price_form_label'] );
		}

		if ( ! empty( $_POST['stm_car_sale_price'] ) ) {
			$location['stm_car_sale_price'] = stm_convert_to_normal_price( abs( sanitize_text_field( $_POST['stm_car_sale_price'] ) ) );
		}

		$generic_title = '';
		if ( ! empty( $_POST['stm_car_main_title'] ) ) {
			$generic_title = sanitize_text_field( $_POST['stm_car_main_title'] );
		} else {
			$error               = true;
			$response['message'] = esc_html__( 'Enter title', 'stm_vehicles_listing' );
		}

		/*Generating post*/
		if ( ! $error ) {
			if ( $restrictions['premoderation'] ) {
				$status = 'pending';
			} else {
				$status = 'publish';
			}

			$post_data = array(
				'post_type'   => 'listings',
				'post_title'  => '',
				'post_status' => $status,
			);

			if ( ! empty( $generic_title ) ) {
				$post_data['post_title'] = $generic_title;
			}

			if ( ! $update ) {
				$post_id = wp_insert_post( $post_data, true );
			}

			if ( ! is_wp_error( $post_id ) ) {

				if ( $update ) {
					$post_data_update = array(
						'ID'          => $post_id,
						'post_status' => $status,
					);

					if ( ! empty( $generic_title ) ) {
						$post_data_update['post_title'] = $generic_title;
					}

					wp_update_post( $post_data_update );
				}

				update_post_meta( $post_id, 'stock_number', $post_id );
				update_post_meta( $post_id, 'stm_car_user', $user['user_id'] );
				update_post_meta( $post_id, 'listing_seller_note', $notes );

				/*Set categories*/
				foreach ( $first_step as $tax => $term ) {
					$tax_info = apply_filters( 'stm_vl_get_all_by_slug', array(), $tax );
					if ( ! empty( $tax_info['numeric'] ) && $tax_info['numeric'] ) {
						update_post_meta( $post_id, $tax, abs( sanitize_title( $term ) ) );
					} else {
						wp_delete_object_term_relationships( $post_id, $tax );
						wp_add_object_terms( $post_id, $term, $tax, true );
						update_post_meta( $post_id, $tax, sanitize_title( $term ) );
					}
				}

				if ( ! empty( $second_step ) ) {
					/*Set categories*/
					foreach ( $second_step as $tax => $term ) {
						if ( ! empty( $tax ) && ! empty( $term ) ) {
							$tax_info = apply_filters( 'stm_vl_get_all_by_slug', array(), $tax );
							if ( ! empty( $tax_info['numeric'] ) && $tax_info['numeric'] ) {
								update_post_meta( $post_id, $tax, sanitize_text_field( $term ) );
							} else {
								wp_delete_object_term_relationships( $post_id, $tax );
								wp_add_object_terms( $post_id, $term, $tax, true );
								update_post_meta( $post_id, $tax, sanitize_text_field( $term ) );
							}
						}
					}
				}

				if ( ! empty( $videos ) ) {
					update_post_meta( $post_id, 'gallery_video', $videos[0] );

					if ( count( $videos ) > 1 ) {
						array_shift( $videos );
						update_post_meta( $post_id, 'gallery_videos', array_filter( array_unique( $videos ) ) );
					}
				}

				if ( ! empty( $vin ) ) {
					update_post_meta( $post_id, 'vin_number', $vin );
				}

				if ( ! empty( $registered ) ) {
					update_post_meta( $post_id, 'registration_date', $registered );
				}

				if ( ! empty( $history['label'] ) ) {
					update_post_meta( $post_id, 'history', $history['label'] );
				}

				if ( ! empty( $history['link'] ) ) {
					update_post_meta( $post_id, 'history_link', $history['link'] );
				}

				if ( ! empty( $location['label'] ) ) {
					update_post_meta( $post_id, 'stm_car_location', $location['label'] );
				}

				if ( ! empty( $location['lat'] ) ) {
					update_post_meta( $post_id, 'stm_lat_car_admin', $location['lat'] );
				}

				if ( ! empty( $location['lng'] ) ) {
					update_post_meta( $post_id, 'stm_lng_car_admin', $location['lng'] );
				}

				if ( ! empty( $location['address'] ) ) {
					update_post_meta( $post_id, 'stm_location_address', $location['address'] );
				}

				if ( ! empty( $car_features ) ) {
					update_post_meta( $post_id, 'additional_features', implode( ',', $car_features ) );
				}

				update_post_meta( $post_id, 'price', $price );
				update_post_meta( $post_id, 'stm_genuine_price', $price );

				if ( ! empty( $location['car_price_form_label'] ) ) {
					update_post_meta( $post_id, 'car_price_form_label', $location['car_price_form_label'] );
				}

				if ( isset( $location['stm_car_sale_price'] ) && ! empty( $location['stm_car_sale_price'] ) ) {
					update_post_meta( $post_id, 'sale_price', $location['stm_car_sale_price'] );
					update_post_meta( $post_id, 'stm_genuine_price', $location['stm_car_sale_price'] );
				} else {
					update_post_meta( $post_id, 'sale_price', '' );
				}

				update_post_meta( $post_id, 'title', 'hide' );
				update_post_meta( $post_id, 'breadcrumbs', 'show' );
				update_post_meta( $post_id, 'car_mark_as_sold', '' );

				$response['post_id'] = $post_id;
				if ( ( $update ) ) {
					$response['message'] = esc_html__( 'Listing Updated, uploading photos', 'stm_vehicles_listing' );
				} else {
					$response['message'] = esc_html__( 'Listing Added, uploading photos', 'stm_vehicles_listing' );
				}
			} else {
				$response['message'] = $post_id->get_error_message();
			}
		}

		$response = wp_json_encode( $response );
		echo wp_kses_post( apply_filters( 'stm_filter_add_a_car', $response ) );
		exit;
	}

	add_action( 'wp_ajax_stm_ajax_add_a_car', 'stm_ajax_add_a_car' );
	add_action( 'wp_ajax_nopriv_stm_ajax_add_a_car', 'stm_ajax_add_a_car' );
}

if ( ! function_exists( 'stm_convert_to_normal_price' ) ) {
	function stm_convert_to_normal_price( $price ) {
		if ( isset( $_COOKIE['stm_current_currency'] ) ) {
			$default_currency = get_option( 'price_currency', '$' );
			$cookie           = explode( '-', $_COOKIE['stm_current_currency'] );

			if ( $cookie[0] !== $default_currency ) {
				return $price / $cookie[1];
			}
		}

		return $price;
	}
}

if ( ! function_exists( 'stm_ajax_add_a_car_images' ) ) {
	function stm_ajax_add_a_car_images() {
		check_ajax_referer( 'stm_security_nonce', 'security' );

		if ( stm_is_site_demo_mode() ) {
			wp_send_json( array( 'message' => esc_html__( 'Site is on demo mode', 'stm_vehicles_listing' ) ) );
			exit;
		}

		$post_id         = ( isset( $_POST['post_id'] ) ) ? intval( $_POST['post_id'] ) : '';
		$user_id         = get_current_user_id();
		$attachments_ids = ( isset( $_POST['attachments'] ) && ! empty( $attachments_ids ) ) ? array_map( 'sanitize_text_field', array_values( explode( ',', $_POST['attachments'] ) ) ) : array();

		if ( ! empty( $post_id ) ) {
			if ( intval( get_post_meta( $post_id, 'stm_car_user', true ) ) !== intval( $user_id ) ) {
				/*User tries to add info to another car*/
				wp_send_json( array( 'message' => esc_html__( 'You are trying to add car to another car user, or your session has expired, please sign in first', 'stm_vehicles_listing' ) ) );
				exit;
			}
		}

		$error    = true;
		$response = array(
			'message'    => esc_html__( 'Some error occurred, try again later', 'stm_vehicles_listing' ),
			'post'       => $post_id,
			'attachment' => false,
		);

		if ( ! empty( $_FILES ) ) {
			$limits = apply_filters(
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

			$_thumbnail_id = 0;

			if ( ! empty( $post_id ) ) {
				$_thumbnail_id = get_post_thumbnail_id( $post_id );

				if ( empty( $attachments_ids ) ) {
					$attachments_ids = get_post_meta( $post_id, 'gallery', true );
				}
			}

			if ( empty( $attachments_ids ) || ! is_array( $attachments_ids ) ) {
				$attachments_ids = array();
			}

			if ( ! empty( $_thumbnail_id ) ) {
				array_unshift( $attachments_ids, $_thumbnail_id );
			}

			$attachments_ids = array_values( $attachments_ids );
			$max_file_size   = apply_filters( 'stm_listing_media_upload_size', 1024 * 4000 ); /*4mb is highest media upload here*/
			$max_uploads     = intval( $limits['images'] );
			$files           = $_FILES['files'];

			if ( count( $attachments_ids ) > $max_uploads ) {
				$response['message'] = sprintf(
				/* translators: %d: images limit */
					esc_html__( 'Sorry, you can upload only %d images per add', 'stm_vehicles_listing' ),
					$max_uploads
				);
			} else {
				// Check if user is trying to upload more than the allowed number of images for the current post
				$file_index      = 0;
				$name            = $files['name'][ $file_index ];
				$file_error      = $files['error'][ $file_index ];
				$type            = $files['type'][ $file_index ];
				$tmp_name        = $files['tmp_name'][ $file_index ];
				$size            = $files['size'][ $file_index ];
				$check_file_type = wp_check_filetype( $name );

				if ( ! $check_file_type['ext'] ) {
					$response['message'] = esc_html__( 'Sorry, you are trying to upload the wrong image format', 'stm_vehicles_listing' ) . ': ' . $name;
				} elseif ( UPLOAD_ERR_OK !== $file_error ) {
					$response['message'] = $file_error . ': ' . $name;
				} else {
					// Check if the file being uploaded is in the allowed file types
					$check_image = getimagesize( $tmp_name );
					if ( $size > $max_file_size ) {
						$response['message'] = esc_html__( 'Sorry, image is too large', 'stm_vehicles_listing' ) . ': ' . $name;
					} elseif ( empty( $check_image ) ) {
						$response['message'] = esc_html__( 'Sorry, image has invalid format', 'stm_vehicles_listing' ) . ': ' . $name;
					} else {
						require_once ABSPATH . 'wp-admin/includes/image.php';

						$file     = array(
							'tmp_name' => $tmp_name,
							'error'    => $file_error,
							'type'     => $type,
							'size'     => $size,
							'name'     => $name,
						);
						$uploaded = wp_handle_upload( $file, array( 'action' => 'stm_ajax_add_a_car_images' ) );

						if ( $uploaded || empty( $uploaded['error'] ) ) {
							$filetype = wp_check_filetype( basename( $uploaded['file'] ), null );

							// Insert attachment to the database
							$attach_id = wp_insert_attachment(
								array(
									'guid'           => $uploaded['url'],
									'post_mime_type' => $filetype['type'],
									'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $uploaded['file'] ) ),
									'post_content'   => '',
									'post_status'    => 'inherit',
								),
								$uploaded['file'],
								$post_id,
								true
							);

							if ( is_wp_error( $attach_id ) ) {
								$response['message'] = $attach_id->get_error_message();
							} else {
								// Generate meta data
								wp_update_attachment_metadata( $attach_id, wp_generate_attachment_metadata( $attach_id, $uploaded['file'] ) );

								update_post_meta( $attach_id, 'stm_attachment_cron_timestamp', ( time() + HOUR_IN_SECONDS ) );

								$response['attachment'] = array(
									'id'      => $attach_id,
									'url'     => wp_get_attachment_image_url( $attach_id, 'stm-img-796-466' ),
									'message' => esc_html__( 'Successfully image upload', 'stm_vehicles_listing' ),
								);
							}
						} else {
							$response['message'] = $uploaded['error'];
						}
					}
				}
			}
		}

		wp_send_json( $response );
		exit;
	}

	add_action( 'wp_ajax_stm_ajax_add_a_car_images', 'stm_ajax_add_a_car_images' );
	add_action( 'wp_ajax_nopriv_stm_ajax_add_a_car_images', 'stm_ajax_add_a_car_images' );
}

if ( ! function_exists( 'stm_listing_images_cron_event_start' ) ) {
	function stm_listing_images_cron_event_start() {
		$hook = 'stm_add_a_car_images_schedule';

		if ( ! wp_next_scheduled( $hook, array( 'stm_attachment_cron_timestamp' ) ) ) {
			wp_schedule_event( time(), 'hourly', $hook, array( 'stm_attachment_cron_timestamp' ) );
		}
	}
}

add_action( 'plugins_loaded', 'stm_listing_images_cron_event_start' );

if ( ! function_exists( 'stm_add_a_car_images_schedule' ) ) {
	function stm_add_a_car_images_schedule( $meta_key ) {
		if ( ! $meta_key ) {
			return;
		}

		$attachments = new WP_Query(
			array(
				'post_type'              => 'attachment',
				'post_status'            => 'inherit',
				'meta_query'             => array(
					array(
						'key'     => $meta_key,
						'compare' => '<=',
						'type'    => 'NUMERIC',
						'value'   => time(),
					),
				),
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_meta_cache' => true,
				'update_post_term_cache' => true,
				'cache_results'          => false,
			)
		);

		if ( $attachments->posts ) {
			foreach ( $attachments->posts as $attach_id ) {
				wp_delete_attachment( $attach_id, true );
			}
		}
	}
}

add_action( 'stm_add_a_car_images_schedule', 'stm_add_a_car_images_schedule' );

if ( ! function_exists( 'stm_ajax_add_a_car_media' ) ) {
	/**
	 * Car media
	 */
	function stm_ajax_add_a_car_media() {
		if ( stm_is_site_demo_mode() ) {
			wp_send_json( array( 'message' => esc_html__( 'Site is on demo mode', 'stm_vehicles_listing' ) ) );
			exit;
		}

		$redirect_type = ( isset( $_POST['redirect_type'] ) ) ? $_POST['redirect_type'] : '';
		$post_id       = intval( $_POST['post_id'] );
		if ( ! $post_id ) {
			/*No id passed from first ajax Call?*/
			wp_send_json( array( 'message' => esc_html__( 'Some error occurred, try again later', 'stm_vehicles_listing' ) ) );
			exit;
		}

		$user_id  = get_current_user_id();
		$updating = ! empty( $_POST['stm_edit'] ) && 'update' === $_POST['stm_edit'];

		if ( intval( get_post_meta( $post_id, 'stm_car_user', true ) ) !== intval( $user_id ) ) {
			/*User tries to add info to another car*/
			wp_send_json( array( 'message' => esc_html__( 'You are trying to add car to another car user, or your session has expired, please sign in first', 'stm_vehicles_listing' ) ) );
			exit;
		}

		$attachments_ids = array();
		foreach ( $_POST as $get_media_keys => $get_media_values ) {
			if ( strpos( $get_media_keys, 'media_position_' ) !== false ) {
				$attachments_ids[ str_replace( 'media_position_', '', $get_media_keys ) ] = intval( $get_media_values );
			}
		}

		$response = array(
			'message' => '',
			'post'    => $post_id,
			'errors'  => array(),
		);

		$current_attachments = get_posts(
			array(
				'fields'                 => 'ids',
				'post_type'              => 'attachment',
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'no_found_rows'          => true,
				'posts_per_page'         => -1,
				'post_parent'            => $post_id,
			)
		);
		$_thumbnail_id       = get_post_thumbnail_id( $post_id );
		if ( $_thumbnail_id ) {
			$current_attachments = array_unique( (array) array_unshift( $current_attachments, $_thumbnail_id ), SORT_NUMERIC );
		}

		if ( ! empty( $current_attachments ) ) {
			$delete_attachments = array_diff( $current_attachments, $attachments_ids );

			if ( ! empty( $delete_attachments ) ) {
				foreach ( $delete_attachments as $_attachment_id ) {
					stm_delete_media( $_attachment_id );
				}
			}
		}

		if ( ! empty( $attachments_ids ) ) {
			$attachments_ids = array_unique( $attachments_ids );
			ksort( $attachments_ids );

			$new_attachments = array_diff( $attachments_ids, $current_attachments );
			foreach ( $new_attachments as $_attachment_id ) {
				delete_post_meta( $_attachment_id, 'stm_attachment_cron_timestamp' );
			}
		} else {
			stm_delete_media( $_thumbnail_id );
		}

		set_post_thumbnail( $post_id, reset( $attachments_ids ) );
		update_post_meta( $post_id, 'gallery', $attachments_ids );

		do_action( 'stm_after_listing_gallery_saved', $post_id, $attachments_ids );

		$post_status = get_post_status( $post_id );

		if ( $updating ) {
			if ( 'publish' === $post_status ) {
				$response['message'] = esc_html__( 'Car updated, redirecting to your listing', 'stm_vehicles_listing' );
			} else {
				$response['message'] = esc_html__( 'Car updated, redirecting to your account profile', 'stm_vehicles_listing' );
			}

			$to = get_bloginfo( 'admin_email' );

			$args = array(
				'user_id' => $user_id,
				'car_id'  => $post_id,
			);

			$subject = stm_generate_subject_view( 'update_a_car', $args );
			$body    = stm_generate_template_view( 'update_a_car', $args );

			if ( 'edit-ppl' === $redirect_type ) {
				$args    = array(
					'user_id'       => $user_id,
					'car_id'        => $post_id,
					'revision_link' => getRevisionLink( $post_id ),
				);
				$subject = stm_generate_subject_view( 'update_a_car_ppl', $args );
				$body    = stm_generate_template_view( 'update_a_car_ppl', $args );
			}
		} else {
			if ( 'publish' === $post_status ) {
				$response['message'] = esc_html__( 'Listing added, redirecting to your listing', 'stm_vehicles_listing' );
			} else {
				$response['message'] = esc_html__( 'Car updated, redirecting to your account profile', 'stm_vehicles_listing' );
			}

			$to      = get_bloginfo( 'admin_email' );
			$args    = array(
				'user_id' => $user_id,
				'car_id'  => $post_id,
			);
			$subject = stm_generate_subject_view( 'add_a_car', $args );
			$body    = stm_generate_template_view( 'add_a_car', $args );
		}

		add_filter( 'wp_mail_content_type', 'stm_set_html_content_type_mail' );
		if ( apply_filters( 'stm_listings_notify_updated', true ) ) {
			wp_mail( $to, $subject, apply_filters( 'stm_listing_saved_email_body', $body, $post_id, $updating ) );

			if ( apply_filters( 'stm_me_get_nuxy_mod', false, 'send_email_to_user' ) && ! $updating && 'pending' === get_post_status( $post_id ) ) {
				$email = get_userdata( $user_id );
				$to    = $email->user_email;

				$args = array(
					'car_id'    => $post_id,
					'car_title' => get_the_title( $post_id ),
				);

				$subject = stm_generate_subject_view( 'user_listing_wait', $args );
				$body    = stm_generate_template_view( 'user_listing_wait', $args );

				wp_mail( $to, $subject, $body );
			}
		}
		remove_filter( 'wp_mail_content_type', 'stm_set_html_content_type_mail' );

		$response['success'] = true;

		$checkout_url = '';

		// multilisting
		$current_listing_type  = get_post_type( $post_id );
		$dealer_ppl            = apply_filters( 'stm_me_get_nuxy_mod', false, 'dealer_pay_per_listing' );
		$pay_per_listing_price = apply_filters( 'stm_me_get_nuxy_mod', 0, 'pay_per_listing_price' );

		if ( stm_is_multilisting() ) {
			if ( apply_filters( 'stm_listings_post_type', 'listings' ) !== $current_listing_type ) {

				$ml = new STMMultiListing();

				if ( $ml->stm_get_listing_type_settings( 'inventory_custom_settings', $current_listing_type ) === true ) {

					$custom_dealer_ppl = $ml->stm_get_listing_type_settings( 'dealer_pay_per_listing', $current_listing_type );
					$custom_ppl_price  = $ml->stm_get_listing_type_settings( 'pay_per_listing_price', $current_listing_type );

					if ( $custom_dealer_ppl ) {
						$dealer_ppl = $custom_dealer_ppl;
					}

					if ( ! empty( $custom_ppl_price ) ) {
						$pay_per_listing_price = $custom_ppl_price;
					}
				}
			}
		}

		if ( class_exists( 'WooCommerce' ) && $dealer_ppl && ! $updating && ! empty( $redirect_type ) && 'pay' === $redirect_type ) {

			update_post_meta( $post_id, '_price', $pay_per_listing_price );
			update_post_meta( $post_id, 'pay_per_listing', 'pay' );

			$checkout_url = esc_url( add_query_arg( 'add-to-cart', $post_id, wc_get_checkout_url() ) );
		}

		if ( 'publish' === $post_status ) {
			$redirect_link = get_the_permalink( $post_id );
		} else {
			$redirect_link = get_author_posts_url( $user_id );
		}

		$response['url'] = ( ! empty( $redirect_type ) && 'pay' === $redirect_type ) ? $checkout_url : esc_url( $redirect_link );
		if ( ! empty( $redirect_type ) && 'pay' === $redirect_type && ! $updating ) {
			$response['message'] = esc_html__( 'Listing added, redirecting to checkout', 'stm_vehicles_listing' );
		}

		wp_send_json( apply_filters( 'stm_filter_add_car_media', $response ) );
		exit;
	}

	add_action( 'wp_ajax_stm_ajax_add_a_car_media', 'stm_ajax_add_a_car_media' );
	add_action( 'wp_ajax_nopriv_stm_ajax_add_a_car_media', 'stm_ajax_add_a_car_media' );
}

if ( ! function_exists( 'stm_media_random_affix' ) ) {
	/**
	 * Helper function for media to generate random name
	 *
	 * @param int $length
	 *
	 * @return string
	 */
	function stm_media_random_affix( $length = 5 ) {

		$string     = '';
		$characters = '23456789ABCDEFHJKLMNPRTVWXYZabcdefghijklmnopqrstuvwxyz';

		for ( $p = 0; $p < $length; $p ++ ) {
			$string .= $characters[ wp_rand( 0, strlen( $characters ) - 1 ) ];
		}

		return $string;

	}
}

if ( ! function_exists( 'stm_delete_media' ) ) {
	/**
	 * Delete media
	 *
	 * @param $media_id
	 */
	function stm_delete_media( $media_id ) {
		$current_user = wp_get_current_user();
		$media_id     = intval( $media_id );
		if ( ! empty( $current_user->ID ) ) {
			$current_user_id = $current_user->ID;

			$args = array(
				'author'      => intval( $current_user_id ),
				'post_status' => 'any',
				'post__in'    => array( $media_id ),
				'post_type'   => 'attachment',
			);

			$query = new WP_Query( $args );

			if ( 1 === $query->found_posts ) {
				wp_delete_attachment( $media_id, true );
			}
		}
	}
}

if ( ! function_exists( 'stm_user_listings_query' ) ) {
	/**
	 * Get User cars
	 *
	 * @param $user_id
	 * @param string $status
	 * @param int $per_page
	 * @param bool $popular
	 * @param int $offset
	 * @param bool $data_desc
	 *
	 * @return WP_Query
	 */
	function stm_user_listings_query( $user_id, $status = 'publish', $per_page = - 1, $popular = false, $offset = 0, $data_desc = false, $get_all = false ) {
		$ppl = ( $get_all ) ? array() : array(
			'key'     => 'pay_per_listing',
			'compare' => 'NOT EXISTS',
			'value'   => '',
		);

		$args = array(
			'post_type'      => 'listings',
			'post_status'    => $status,
			'posts_per_page' => $per_page,
			'offset'         => $offset,
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => 'stm_car_user',
					'value'   => $user_id,
					'compare' => '=',
				),
				$ppl,
			),
		);

		if ( $popular ) {
			$args['order']   = 'ASC';
			$args['orderby'] = 'stm_car_views';
		}

		$query = new WP_Query( $args );
		wp_reset_postdata();

		return $query;

	}
}


if ( ! function_exists( 'stm_user_pay_per_listings_query' ) ) {
	/**
	 * Get User cars
	 *
	 * @param $user_id
	 * @param string $status
	 * @param int $per_page
	 * @param bool $popular
	 * @param int $offset
	 * @param bool $data_desc
	 *
	 * @return WP_Query
	 */
	function stm_user_pay_per_listings_query( $user_id, $status = 'publish', $per_page = - 1, $popular = false, $offset = 0, $data_desc = false ) {
		$post_type = stm_listings_multi_type( true );

		$args = array(
			'post_type'      => $post_type,
			'post_status'    => $status,
			'posts_per_page' => $per_page,
			'offset'         => $offset,
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => 'stm_car_user',
					'value'   => $user_id,
					'compare' => '=',
				),
				array(
					'key'     => 'pay_per_listing',
					'compare' => '=',
					'value'   => 'pay',
				),
			),
		);

		if ( $popular ) {
			$args['order']   = 'ASC';
			$args['orderby'] = 'stm_car_views';
		}

		$query = new WP_Query( $args );
		wp_reset_postdata();

		return $query;

	}
}

if ( ! function_exists( 'stm_get_author_link' ) ) {
	/**
	 * Get author link
	 *
	 * @param string $id
	 *
	 * @return mixed
	 */
	function stm_get_author_link( $id = 'register' ) {

		if ( 'register' === $id ) {
			$login_page = apply_filters( 'stm_me_get_nuxy_mod', 1718, 'login_page' );
			if ( class_exists( 'SitePress' ) ) {
				$id = stm_vehicles_wpml_binding( $login_page, 'page' );
				if ( is_page( $id ) ) {
					$login_page = $id;
				}
			}

			$link = get_permalink( $login_page );
		} else {
			if ( empty( $id ) || 'myself-view' === $id ) {
				$link = '';

				if ( is_user_logged_in() ) {
					$user = wp_get_current_user();
					if ( ! is_wp_error( $user ) ) {
						$link = get_author_posts_url( $user->data->ID );
						if ( 'myself-view' === $id ) {
							$link = add_query_arg( array( 'view-myself' => 1 ), $link );
						}
					}
				}
			} else {
				$link = get_author_posts_url( $id );
			}
		}

		return apply_filters( 'stm_filter_author_link', $link, $id );
	}
}

if ( ! function_exists( 'stm_get_add_page_url' ) ) {
	/**
	 * Get add a car page url
	 *
	 * @param string $edit
	 * @param string $post_id
	 *
	 * @return mixed
	 */
	function stm_get_add_page_url( $edit = '', $post_id = '' ) {
		if ( get_post_type( $post_id ) === apply_filters( 'stm_listings_post_type', 'listings' ) ) {
			$page_id = apply_filters( 'stm_me_get_nuxy_mod', 1755, 'user_add_car_page' );
		} else {
			// this is a multilisting type
			if ( stm_is_multilisting() ) {
				$listings = STMMultiListing::stm_get_listings();
				if ( ! empty( $listings ) ) {
					foreach ( $listings as $key => $listing ) {
						if ( get_post_type( $post_id ) === $listing['slug'] ) {
							$page_id = $listing['add_page'];
						}
					}
				}
			}
		}

		$page_link = '';

		if ( ! empty( $page_id ) ) {
			if ( class_exists( 'SitePress' ) ) {
				$id = stm_vehicles_wpml_binding( $page_id, 'page' );
				if ( is_page( $id ) ) {
					$page_id = $id;
				}
			}

			$page_link = get_permalink( $page_id );
		}

		if ( 'edit' === $edit && ! empty( $post_id ) ) {
			$return_value = esc_url(
				add_query_arg(
					array(
						'edit_car' => '1',
						'item_id'  => intval( $post_id ),
					),
					$page_link
				)
			);
		} else {
			$return_value = esc_url( $page_link );
		}

		return apply_filters( 'stm_filter_add_car_page_url', $return_value );
	}
}

if ( ! function_exists( 'stm_edit_delete_user_car' ) ) {
	/**
	 * Delete car, added by user
	 */
	function stm_edit_delete_user_car() {
		$demo = stm_is_site_demo_mode();
		if ( ! $demo ) {

			if ( isset( $_GET['stm_make_featured'] ) && ! empty( $_GET['stm_make_featured'] ) && is_numeric( $_GET['stm_make_featured'] ) ) {
				/*
				 * status level:
				 *  in_cart - user make featured
				 *  processing - checkout complete
				 *  complete - admin approved
				 *
				*/

				$featured_payment_enabled = apply_filters( 'stm_me_get_nuxy_mod', false, 'dealer_payments_for_featured_listing' );

				$featured_listing_price = apply_filters( 'stm_me_get_nuxy_mod', 0, 'featured_listing_price' );

				// multilisting compatibility
				if ( stm_is_multilisting() ) {

					$post_type = get_post_type( $_GET['stm_make_featured'] );

					if ( apply_filters( 'stm_listings_post_type', 'listings' ) !== $post_type ) {

						$ml = new STMMultiListing();

						if ( $ml->stm_get_listing_type_settings( 'inventory_custom_settings', $post_type ) === true ) {

							$custom_dealer_ppl = $ml->stm_get_listing_type_settings( 'dealer_payments_for_featured_listing', $post_type );
							if ( ! empty( $custom_dealer_ppl ) ) {
								$featured_payment_enabled = $custom_dealer_ppl;
							}

							$custom_price = $ml->stm_get_listing_type_settings( 'featured_listing_price', $post_type );
							if ( ! empty( $custom_price ) ) {
								$featured_listing_price = $custom_price;
							}
						}
					}
				}

				if ( class_exists( 'WooCommerce' ) && $featured_payment_enabled ) {

					update_post_meta( $_GET['stm_make_featured'], '_price', $featured_listing_price );
					update_post_meta( $_GET['stm_make_featured'], 'car_make_featured_status', 'in_cart' );

					$checkout_url = wc_get_checkout_url() . '?add-to-cart=' . $_GET['stm_make_featured'] . '&make_featured=yes';

					wp_safe_redirect( $checkout_url );
					die();
				}
			}

			if ( isset( $_GET['stm_unmark_as_sold_car'] ) || isset( $_GET['stm_mark_as_sold_car'] ) ) {
				$post_id = ( isset( $_GET['stm_unmark_as_sold_car'] ) ) ? $_GET['stm_unmark_as_sold_car'] : $_GET['stm_mark_as_sold_car'];
				update_post_meta( $post_id, 'car_mark_as_sold', isset( $_GET['stm_mark_as_sold_car'] ) ? 'on' : '' );
			}

			if ( ! empty( $_GET['stm_disable_user_car'] ) ) {
				$car = intval( $_GET['stm_disable_user_car'] );

				$author = get_post_meta( $car, 'stm_car_user', true );
				$user   = wp_get_current_user();

				if ( intval( $author ) === intval( $user->ID ) ) {
					$status = get_post_status( $car );
					if ( 'publish' === $status ) {
						$disabled_car = array(
							'ID'          => $car,
							'post_status' => 'draft',
						);

						if ( class_exists( 'MultiplePlan' ) ) {
							MultiplePlan::updateListingStatus( $car, 'draft' );
						}

						wp_update_post( $disabled_car );
					}
				}
			}

			if ( ! empty( $_GET['stm_enable_user_car'] ) ) {
				$car = intval( $_GET['stm_enable_user_car'] );

				$author = get_post_meta( $car, 'stm_car_user', true );
				$user   = wp_get_current_user();

				if ( intval( $author ) === intval( $user->ID ) ) {
					$status = get_post_status( $car );
					if ( 'draft' === $status ) {
						$disabled_car = array(
							'ID'          => $car,
							'post_status' => 'publish',
						);

						$can_update = true;

						$user_limits = apply_filters(
							'stm_get_post_limits',
							array(
								'premoderation' => true,
								'posts_allowed' => 0,
								'posts'         => 0,
								'images'        => 0,
								'role'          => 'user',
							),
							$user->ID,
							'edit_delete'
						);

						if ( apply_filters( 'stm_pricing_enabled', false ) ) {

							$user_limits = apply_filters(
								'stm_get_post_limits',
								array(
									'premoderation' => true,
									'posts_allowed' => 0,
									'posts'         => 0,
									'images'        => 0,
									'role'          => 'user',
								),
								$user->ID,
								'edit_delete'
							);
							if ( apply_filters( 'stm_me_get_nuxy_mod', false, 'enable_plans' ) && apply_filters( 'stm_is_multiple_plans', false ) ) {
								$multi           = new MultiplePlan();
								$plan_slot_exist = $multi->existSlotByPlanId( $car, $user->ID );
								if ( ! $plan_slot_exist ) {
									$can_update = false;
								}
							}

							if ( ! $user_limits['posts'] ) {
								$can_update = false;
							}
						}

						if ( 'pay' === get_post_meta( $car, 'pay_per_listing', true ) ) {
							$can_update = true;
						}

						if ( $can_update ) {
							if ( class_exists( 'MultiplePlan' ) ) {
								MultiplePlan::updateListingStatus( $car, 'active' );
							}
							wp_update_post( $disabled_car );
						} else {
							add_action( 'wp_enqueue_scripts', 'stm_user_out_of_limit' );
							function stm_user_out_of_limit() {
								$field_limit  = 'jQuery(document).ready(function(){';
								$field_limit .= 'jQuery(".stm-no-available-adds-overlay, .stm-no-available-adds").removeClass("hidden");';
								$field_limit .= 'jQuery(".stm-no-available-adds-overlay").click(function(){';
								$field_limit .= 'jQuery(".stm-no-available-adds-overlay, .stm-no-available-adds").addClass("hidden")';
								$field_limit .= '});';
								$field_limit .= '});';
								wp_add_inline_script( 'stm-theme-scripts', $field_limit );
							}
						}
					}
				}
			}

			if ( ! empty( $_GET['stm_move_trash_car'] ) ) {
				$car = intval( $_GET['stm_move_trash_car'] );

				$author = get_post_meta( $car, 'stm_car_user', true );
				$user   = wp_get_current_user();

				if ( intval( $author ) === intval( $user->ID ) ) {
					do_action( 'remove_car_from_all', $car );
					if ( 'draft' === get_post_status( $car ) || 'pending' === get_post_status( $car ) ) {
						if ( class_exists( 'MultiplePlan' ) ) {
							MultiplePlan::updateListingStatus( $car, 'trash' );
						}

						wp_trash_post( $car, false );
					}
				}
			}
		}
	}

	add_action( 'wp', 'stm_edit_delete_user_car' );
}

add_action( 'remove_car_from_all', 'remove_car_from_favourites', 10, 1 );
function remove_car_from_favourites( $listing_id ) {
	$users = get_users();

	foreach ( $users as $k => $user ) {
		$u_meta = get_user_meta( $user->ID, 'stm_user_favourites', true );
		if ( ! empty( $u_meta ) ) {
			$fav = explode( ',', $u_meta );
			if ( array_search( $listing_id, $fav, true ) !== false ) {
				unset( $fav[ array_search( $listing_id, $fav, true ) ] );
				update_user_meta( $user->ID, 'stm_user_favourites', implode( ',', $fav ) );
			}
		}
	}
}

add_action( 'admin_footer', 'my_admin_add_js' );
function my_admin_add_js() {
	if ( is_post_type_archive( 'listings' ) || ( ! empty( $_GET['action'] ) && 'edit' === $_GET['action'] && ! empty( $_GET['post'] ) && 'listings' === get_post_type( $_GET['post'] ) ) ) {
		echo '<script type="text/javascript">
		jQuery(".submitdelete").on("click", function(e) {
			var del=confirm( "' . esc_html__( 'Do you want to delete Listing images permanently?', 'stm_vehicles_listing' ) . '" );
			if (del==true){
				var date = new Date(new Date().getTime() + 10 * 1000);
				document.cookie = "deleteListingAttach=delete; path=/; expires=" + date.toUTCString();
			}
		});
	</script>';
	}
}

// Resize image

if ( has_filter( 'wp_get_attachment_image_src', 'stm_get_thumbnail_filter' ) === false ) {
	add_filter( 'wp_get_attachment_image_src', 'stm_get_thumbnail_filter', 100, 4 );
	function stm_get_thumbnail_filter( $image, $attachment_id, $size = 'thumbnail', $icon = false ) {
		$file       = wp_check_filetype( get_attached_file( $attachment_id ) );
		$image_exts = array( 'jpg', 'jpeg', 'jpe', 'png', 'webp' );

		if ( ! in_array( $file['ext'], $image_exts, true ) ) {
			return $image;
		}

		return stm_get_thumbnail( $attachment_id, $size, $icon );
	}
}

function stm_get_thumbnail( $attachment_id, $size = 'thumbnail', $icon = false ) {
	$intermediate = image_get_intermediate_size( $attachment_id, $size );
	$upload_dir   = wp_upload_dir();

	if ( ! $intermediate || ! file_exists( $upload_dir['basedir'] . '/' . $intermediate['path'] ) ) {
		$file = get_attached_file( $attachment_id );

		if ( empty( $file ) || ! file_exists( $file ) ) {
			return false;
		}

		$imagesize = getimagesize( $file );

		if ( is_array( $size ) ) {
			$sizes = array(
				'width'  => $size[0],
				'height' => $size[1],
			);
		} else {
			$_wp_additional_image_sizes = wp_get_additional_image_sizes();
			$sizes                      = array();
			foreach ( get_intermediate_image_sizes() as $s ) {
				$sizes[ $s ] = array(
					'width'  => '',
					'height' => '',
					'crop'   => false,
				);
				if ( isset( $_wp_additional_image_sizes[ $s ]['width'] ) ) {
					// For theme-added sizes
					$sizes[ $s ]['width'] = intval( $_wp_additional_image_sizes[ $s ]['width'] );
				} else {
					// For default sizes set in options
					$sizes[ $s ]['width'] = get_option( "{$s}_size_w" );
				}

				if ( isset( $_wp_additional_image_sizes[ $s ]['height'] ) ) {
					// For theme-added sizes
					$sizes[ $s ]['height'] = intval( $_wp_additional_image_sizes[ $s ]['height'] );
				} else {
					// For default sizes set in options
					$sizes[ $s ]['height'] = get_option( "{$s}_size_h" );
				}

				if ( isset( $_wp_additional_image_sizes[ $s ]['crop'] ) ) {
					// For theme-added sizes
					$sizes[ $s ]['crop'] = $_wp_additional_image_sizes[ $s ]['crop'];
				} else {
					// For default sizes set in options
					$sizes[ $s ]['crop'] = get_option( "{$s}_crop" );
				}
			}

			if ( ! is_array( $size ) && ! isset( $sizes[ $size ] ) && $imagesize ) {
				$sizes['width']  = $imagesize[0];
				$sizes['height'] = $imagesize[1];
			} else {
				$sizes = ( ! empty( $sizes[ $size ] ) ) ? $sizes[ $size ] : $sizes['large'];
			}
		}

		if ( ! empty( $imagesize[0] ) && $sizes['width'] >= $imagesize[0] ) {
			$sizes['width'] = null;
		}

		if ( ! empty( $imagesize[1] ) && $sizes['height'] >= $imagesize[1] ) {
			$sizes['height'] = null;
		}

		$editor = wp_get_image_editor( $file );
		if ( ! is_wp_error( $editor ) ) {
			$resize                     = $editor->multi_resize( array( $sizes ) );
			$wp_get_attachment_metadata = wp_get_attachment_metadata( $attachment_id );

			if ( isset( $resize[0] ) && is_array( $size ) && isset( $wp_get_attachment_metadata['sizes'] ) ) {
				foreach ( $wp_get_attachment_metadata['sizes'] as $key => $val ) {
					if ( array_search( $resize[0]['file'], $val, true ) ) {
						$size = $key;
					}
				}
			}

			if ( is_array( $size ) ) {
				$size = $size[0] . 'x' . $size[0];
			}

			if ( ! $wp_get_attachment_metadata ) {
				$wp_get_attachment_metadata                   = array();
				$wp_get_attachment_metadata['width']          = $imagesize[0];
				$wp_get_attachment_metadata['height']         = $imagesize[1];
				$wp_get_attachment_metadata['file']           = _wp_relative_upload_path( $file );
				$wp_get_attachment_metadata['sizes'][ $size ] = ( ! empty( $resize ) ) ? $resize[0] : null;
			} else {
				if ( isset( $resize[0] ) ) {
					$wp_get_attachment_metadata['sizes'][ $size ] = $resize[0];
				}
			}

			wp_update_attachment_metadata( $attachment_id, $wp_get_attachment_metadata );
		}
	}

	$image = image_downsize( $attachment_id, $size );

	return apply_filters( 'get_thumbnail', $image, $attachment_id, $size, $icon );
}

function getRevisionLink( $post_parent ) {

	$posts = new WP_Query(
		array(
			'post_status'   => 'inherit',
			'post_type'     => 'revision',
			'post_parent'   => $post_parent,
			'post_per_page' => 1,
			'orderby'       => 'ID',
			'order'         => 'DESC',
		)
	);

	$post_id = $posts->post->ID;
	wp_reset_postdata();

	return get_admin_url() . 'revision.php?revision=' . $post_id;
}

// Select sorting options
if ( ! function_exists( 'get_stm_select_sorting_options' ) ) {
	function get_stm_select_sorting_options() {

		$sort_args = array();

		if ( function_exists( 'stm_get_sort_options_array' ) ) {
			$sort_args = stm_get_sort_options_array();
		}

		if ( true === apply_filters( 'stm_is_boats', false ) ) {
			unset( $sort_args['mileage_low'] );
			unset( $sort_args['mileage_high'] );
		}

		if ( stm_sort_distance_nearby() ) {
			$sort_args = array_merge( array( 'distance_nearby' => esc_html__( 'Distance : nearby', 'stm_vehicles_listing' ) ), $sort_args );
		}

		return apply_filters( 'stm_select_sorting_options', $sort_args );
	}
}

if ( ! function_exists( 'get_stm_select_sorting_options_for_select2' ) ) {
	function get_stm_select_sorting_options_for_select2() {
		$data = array();
		foreach ( get_stm_select_sorting_options() as $key => $value ) {
			$data[] = array(
				'id'   => $key,
				'text' => $value,
			);
		}

		return $data;
	}
}

if ( ! function_exists( 'sort_distance_nearby' ) ) {
	function sort_distance_nearby() {

		$ca_location = apply_filters( 'stm_listings_input', null, 'ca_location' );
		$stm_lat     = apply_filters( 'stm_listings_input', null, 'stm_lat' );
		$stm_lng     = apply_filters( 'stm_listings_input', null, 'stm_lng' );

		if ( $ca_location && $stm_lat && $stm_lng ) {
			return true;
		}

		return false;
	}
}

if ( ! function_exists( 'stm_motors_wpml_binding' ) ) {
	function stm_vehicles_wpml_binding( $id, $type ) {
		return apply_filters( 'wpml_object_id', $id, $type );
	}
}

// inventory and archive default listing sort option
add_filter( 'stm_get_default_sort_option', 'stm_get_default_sort_option' );
function stm_get_default_sort_option() {
	$display_multilisting_sorts = false;

	if ( stm_is_multilisting() ) {
		$current_slug = STMMultiListing::stm_get_current_listing_slug();
		if ( ! empty( $current_slug ) ) {
			$display_multilisting_sorts = true;
		}
	}

	if ( $display_multilisting_sorts ) {
		$ml               = new STMMultiListing();
		$selected_options = apply_filters( 'stm_prefix_given_sort_options', $ml->stm_get_listing_type_settings( 'multilisting_sort_options', $current_slug ) );
		$selected_sort    = $ml->stm_get_listing_type_settings( 'multilisting_default_sort_by', $current_slug );
	} else {
		$sort_options     = apply_filters( 'stm_me_get_nuxy_mod', array(), 'sort_options' );
		$selected_options = apply_filters( 'stm_prefix_given_sort_options', $sort_options );
		$selected_sort    = apply_filters( 'stm_me_get_nuxy_mod', 'date_high', 'default_sort_by' );
	}

	if ( strpos( $selected_sort, 'date_' ) !== false || in_array( $selected_sort, $selected_options, true ) ) {
		return $selected_sort;
	}

	// newest listing first by default
	return 'date_high';
}

// get prefixed values for sorts checking
add_filter( 'stm_prefix_given_sort_options', 'stm_prefix_given_sort_options' );
function stm_prefix_given_sort_options( $array ) {
	$prefixed = array( 'date_high', 'date_low' );

	if ( ! empty( $array ) ) {
		foreach ( $array as $slug ) {
			$prefixed[] = $slug . '_high';
			$prefixed[] = $slug . '_low';
		}
	}

	return $prefixed;
}

//this function is used to check if the value is empty except zero
add_filter( 'is_empty_value', 'is_empty_value' );
function is_empty_value( $value ) {
	$value = floatval( $value );
	if ( 0 === $value ) {
		return false;
	}
}
