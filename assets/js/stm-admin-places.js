(function($) {
	"use strict";

	let selectors = {
		location: '#stm_car_location',
		address: '#stm_location_address',
		lat: '#stm_lat_car_admin',
		lng: '#stm_lng_car_admin',
	};

	function autocompleteConfig() {
		return {types: ['geocode']};
	}

	function initGoogleScripts() {
		let input        = document.querySelector( selectors.location ),
			autocomplete = new google.maps.places.Autocomplete( input, autocompleteConfig() );

		google.maps.event.addDomListener(
			autocomplete,
			'place_changed',
			function () {
				let place = autocomplete.getPlace(), {lat, lng} = '';

				if ( typeof(place.geometry) !== 'undefined' ) {
					lat = place.geometry.location.lat();
					lng = place.geometry.location.lng();
				}

				let addressComponents = filterAddressComponents( place );

				$( selectors.lat ).val( lat );
				$( selectors.lng ).val( lng );
				$( selectors.address ).val( addressComponents );
			}
		);

		//If user just entered some text, without getting prediction, geocode it
		google.maps.event.addDomListener(
			input,
			'keydown',
			function(e) {
				let keyCode = e.keyCode || e.charCode,
					place   = autocomplete.getPlace();

				if ( ( keyCode === 8 || keyCode === 46 ) && ! $( selectors.lat + ', ' + selectors.lng + ', ' + selectors.location ).val() ) {
					$( selectors.address ).val( '' );

					return false;
				}

				if (typeof(place) === 'undefined') {
					geocoderByInput();
				} else {
					if (typeof(place.geometry) === 'undefined' || place.name !== $( selectors.location ).val()) {
						geocoderByInput();
					}
				}

				if ( keyCode === 13 ) {
					e.preventDefault();
				}
			}
		);
	}

	function filterAddressComponents(place) {
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

				items = JSON.stringify( Object.assign( {}, items ) );
			}
		}

		return items.length ? items : '';
	}

	function reverseGeocoder(lat, lng) {
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

						addressComponents = filterAddressComponents( place );
					} else {
						window.alert( 'No results found' );
					}
				} else {
					window.alert( 'Geocoder failed due to: ' + status );
				}

				$( selectors.address ).val( addressComponents );
			}
		);
	}

	function geocoderByInput() {
		var address_search = $( selectors.location ).val(),
			geocoder       = new google.maps.Geocoder();

		geocoder.geocode(
			{'address': address_search},
			function (results, status) {
				let {addressComponents, lat, lng} = '';

				if (status === google.maps.GeocoderStatus.OK && results.length) {
					let place = results.shift();

					if ( typeof(place.geometry) !== "undefined" ) {
						lat = place.geometry.location.lat();
						lng = place.geometry.location.lng();
					}

					addressComponents = filterAddressComponents( place );
				}

				$( selectors.lat ).val( lat );
				$( selectors.lng ).val( lng );
				$( selectors.address ).val( addressComponents );
			}
		);
	}

	function initAsync() {
		google.maps.event.addDomListener( window, 'load', initGoogleScripts );
	}

	$( document ).ready(
		function () {

			initAsync();

			let selectors_lat_lng = $( selectors.lat + ', ' + selectors.lng );

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
								reverseGeocoder( lat, $this.val() );
							}
						} else if ( lat.length && lng.length && lat.attr( 'id' ) === $this.attr( 'id' ) ) {
							lng = lng.val();

							if ( $this.val().length > 0 && lng.length > 0 ) {
								reverseGeocoder( $this.val(), lng );
							}
						}
					}
				);
			}

		}
	);

})( jQuery );
