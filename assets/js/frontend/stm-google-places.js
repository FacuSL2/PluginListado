(function($) {
	"use strict";

	let Places    = STMListings.Places = {},
		selectors = {
			location: 'input[name=\'stm_location_text\']',
			address: 'input:hidden[name=\'stm_location_address\']',
			lat: 'input[name=\'stm_lat\']',
			lng: 'input[name=\'stm_lng\']',
			filter: '#ca_location_listing_filter',
			add_car: '#stm-add-car-location',
			become_dealer: '#stm_google_user_location_entry',
			resetFieldFilter: '.stm-location-reset-field'
	};

	Places.historyComponents = [];

	Places.autocompleteConfig = function () {
		return {types: ['geocode']};
	};

	Places.addGoogleAutocomplete = function (location_id) {
		let input        = document.querySelector( '#' + location_id ),
			autocomplete = new google.maps.places.Autocomplete( input, Places.autocompleteConfig() );

		//Place changed hook
		google.maps.event.addDomListener(
			autocomplete,
			'place_changed',
			function () {
				let place         = autocomplete.getPlace(), {lat, lng} = '',
					location_unit = $( '#' + location_id ).closest( '.stm-location-search-unit' );

				if ( typeof(place.geometry) !== 'undefined' ) {
					lat = place.geometry.location.lat();
					lng = place.geometry.location.lng();
				}

				let addressComponents = Places.filterAddressComponents( place );

				location_unit.find( selectors.address ).val( addressComponents );

				location_unit.find( selectors.lat ).val( lat );
				location_unit.find( selectors.lng ).val( lng );

				if ( ! ['ca_location_listing_filter', 'stm-add-car-location'].includes( $( input ).attr( 'id' ) ) ) {
					location_unit.closest( 'form' ).find( 'select:not(.hide)' ).trigger( 'change' );
				}

				let difference = Places.difference( place );

				if (
					location_unit.find( selectors.lat ).val().length &&
					location_unit.find( selectors.lng ).val().length &&
					'ca_location_listing_filter' === location_id &&
					difference
				) {
					location_unit.find( selectors.lng ).trigger( 'change' );
				}
			}
		);

		//If user just entered some text, without getting prediction, geocode it
		google.maps.event.addDomListener(
			input,
			'keydown',
			function(e) {
				let keyCode = e.keyCode || e.charCode;

				if ( keyCode === 13 ) {
					e.preventDefault();
				}
			}
		);
	};

	Places.geocoderByInput = function (location_id) {
		var address_search = $( '#' + location_id ).val(),
			geocoder       = new google.maps.Geocoder();

		geocoder.geocode(
			{'address': address_search},
			function (results, status) {
				let location_input                = $( '#' + location_id ),
					{addressComponents, lat, lng} = '',
					location_unit                 = location_input.closest( '.stm-location-search-unit' ),
					place;

				if (status === google.maps.GeocoderStatus.OK && results.length) {
					place = results.shift();

					if ( typeof(place.geometry) !== "undefined" ) {
						lat = place.geometry.location.lat();
						lng = place.geometry.location.lng();
					}

					addressComponents = Places.filterAddressComponents( place );
				}

				location_unit.find( selectors.lat ).val( lat );
				location_unit.find( selectors.lng ).val( lng );
				location_unit.find( selectors.address ).val( addressComponents );

				let lat_lng_value = parseFloat( $( selectors.lat + ', ' + selectors.lng ).val() );

				if (
					lat_lng_value &&
					'ca_location_listing_filter' === location_id &&
					Places.difference( place )
				) {
					location_unit.find( selectors.lng ).trigger( 'change' );
				}
			}
		);
	};

	Places.difference = function ( place ) {
		let difference = [],
			components = Places.filterAddressComponents( place, false );

		if ( ! Places.historyComponents.length ) {
			Places.historyComponents = components;

			return true;
		}

		if ( components.length && Places.historyComponents.length ) {
			difference = components.filter(
				function (element) {
					let findItemHistory = Places.historyComponents.find(
						function (item) {
							return item.key === element.key && item.value !== element.value;
						}
					);

					return findItemHistory !== undefined;
				}
			);
		}

		Places.historyComponents = components;

		return ( difference.length > 0 );
	};

	Places.filterAddressComponents = function (place, output_json = true) {
		let items = [];

		if ( place && typeof(place.address_components) !== "undefined" ) {
			let address = place.address_components.length > 0 ? place.address_components : false;

			if ( address && Array.isArray( address ) ) {
				address.forEach(
					function (item) {
						let types = ['country', 'locality', 'sublocality_level_1', 'administrative_area_level_1', 'route'];

						let findType = item.types.find(
							function ( type ) {
								return types.includes( type );
							}
						);

						if ( findType !== undefined ) {
							let uniqItem = {
								key: findType,
								value: item.long_name
							};

							items.push( uniqItem );
						}
					}
				);

				if ( output_json ) {
					items = JSON.stringify( Object.assign( {}, items ) );
				}
			}
		}

		if ( ! output_json ) {
			return items;
		}

		let value = items.length ? items : ''

		$.cookie( 'stm_location_address', value, { path: '/' } );

		return value;
	};

	Places.initializePlacesSearch = function () {
		$( '.stm_listing_search_location' ).each(
			function () {
				let location_id = $( this ).attr( 'id' );

				if ( typeof(location_id) != 'undefined' ) {
					Places.addGoogleAutocomplete( location_id );
				}
			}
		);

		if ( $( selectors.filter ).length > 0 ) {
			Places.addGoogleAutocomplete( $( selectors.filter ).attr( 'id' ) );
		}

		if ( $( selectors.add_car ).length > 0 ) {
			Places.addGoogleAutocomplete( $( selectors.add_car ).attr( 'id' ) );
		}

		if ( $( selectors.become_dealer ).length > 0 ) {
			Places.addGoogleAutocomplete( $( selectors.become_dealer ).attr( 'id' ) );
		}
	};

	Places.initAsync = function () {
		google.maps.event.addDomListener( window, 'load', function () { Places.initializePlacesSearch() } );
	};

	Places.reverseGeocoder = function (lat, lng) {
		lat = parseFloat( lat );
		lng = parseFloat( lng );

		if ( ! lat && ! lng ) {
			return;
		}

		let geocoder = new google.maps.Geocoder,
			latLng   = {lat: lat, lng: lng};

		geocoder.geocode(
			{'location': latLng},
			function(results, status) {
				let addressComponents = '';

				if (status === 'OK' && results.length) {
					let place = results.shift();

					if ( place ) {
						if ( typeof(place.formatted_address) !== "undefined" ) {
							$( selectors.location ).val( place.formatted_address.trim() );
						}

						addressComponents = Places.filterAddressComponents( place );
					} else {
						window.alert( 'No results found' );
					}
				} else {
					window.alert( 'Geocoder failed due to: ' + status );
				}

				$( selectors.address ).val( addressComponents );
			}
		);
	};

	$( document ).ready(
		function(){

			Places.initAsync();

			let locationFilter    = $( selectors.filter ),
				selectors_lat_lng = $( selectors.lat + ', ' + selectors.lng );

			if ( locationFilter.length ) {
				let cur_val = locationFilter.val();

				if ( ! cur_val.length ) {
					locationFilter.addClass( 'empty' );
					$.removeCookie( 'stm_location_address', { path: '/' } );
				} else {
					locationFilter.removeClass( 'empty' );

					let lat_lng_value = parseFloat( selectors_lat_lng.val() );

					if ( ! lat_lng_value ) {
						Places.geocoderByInput( locationFilter.attr( 'id' ) );
					}
				}

				locationFilter.on(
					'keyup',
					function(){
						let $this = $( this );

						if ( ! $this.val().length ) {
							if ( cur_val === $this.val() ) {
								return;
							}

							Places.historyComponents = [];

							$( selectors.lat + ', ' + selectors.lng + ', ' + selectors.address ).val( '' );
							$this.closest( 'form' ).trigger( 'submit' );

							$this.addClass( 'empty' );
							$( '.filter-search_radius' ).hide();
						} else {
							$this.removeClass( 'empty' );

							if ( typeof stm_slide_filter === "function" && $( '.stm-search_radius-range' ).slider( "instance" ) === undefined ) {
								stm_slide_filter();
							}

							$( '.filter-search_radius' ).show();
						}

						cur_val = $this.val();
					}
				);
			}

			let resetField = $( selectors.resetFieldFilter );

			if ( resetField.length ) {
				resetField.on(
					'click',
					function () {
						locationFilter.val( '' ).trigger( 'keyup' );

						if ( typeof STMListings !== "undefined" && typeof STMListings.stm_disable_rest_filters !== "undefined" ) {
							STMListings.stm_disable_rest_filters( $( this ), 'listings-binding' );
						}
					}
				);
			}

			if ( selectors_lat_lng.length ) {
				selectors_lat_lng.on(
					"keyup input",
					function(e) {
						let $this   = $( this ),
							lng     = $( selectors.lng ),
							lat     = $( selectors.lat ),
							keyCode = e.keyCode || e.charCode;

						if ( ( keyCode === 8 || keyCode === 46 ) && ! $( selectors.lng + ', ' + selectors.lng + ', ' + selectors.location ).val() ) {
							$( selectors.address ).val( '' );

							return false;
						}

						if ( lng.length && lat.length && lng.attr( 'id' ) === $this.attr( 'id' ) ) {
							lat = lat.val();

							if ( $this.val().length > 0 && lat.length > 0 ) {
								Places.reverseGeocoder( lat, $this.val() );
							}
						} else if ( lat.length && lng.length && lat.attr( 'id' ) === $this.attr( 'id' ) ) {
							lng = lng.val();

							if ( $this.val().length > 0 && lng.length > 0 ) {
								Places.reverseGeocoder( $this.val(), lng );
							}
						}
					}
				);

				$( selectors.lng ).on(
					'change',
					function () {
						$( this ).closest( 'form' ).trigger( 'submit' );
					}
				);
			}
		}
	)

})( jQuery );
