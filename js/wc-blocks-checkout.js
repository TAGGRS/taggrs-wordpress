/**
 * WooCommerce Blocks Checkout Tracking
 * Handles GA4 events for the new WooCommerce Blocks-based checkout
 */

( function () {
	'use strict';

	// Check if this is a Blocks checkout
	function isBlocksCheckout () {
		return document.querySelector( '.wc-block-checkout, .wc-block-checkout__form' ) !== null;
	}

	// Helper to push to dataLayer
	function pushToDataLayer ( data ) {
		window.dataLayer = window.dataLayer || [];
		window.dataLayer.push( data );
	}

	// Track begin_checkout for Blocks
	function trackBeginCheckoutBlocks () {
		if ( !window.tggr_checkout_data || !window.tggr_checkout_data.begin_checkout_enabled ) {
			return;
		}

		if ( window.tggr_begin_checkout_tracked ) {
			return;
		}

		if ( isBlocksCheckout() ) {
			if ( window.tggr_checkout_data.cart_data ) {
				const eventData = JSON.parse( JSON.stringify( window.tggr_checkout_data.cart_data ) );

				delete eventData.ecommerce.shipping_tier;
				delete eventData.ecommerce.payment_type;

				pushToDataLayer( { ecommerce: null } );
				pushToDataLayer( eventData );
				window.tggr_begin_checkout_tracked = true;
			}
		}
	}

	// Track add_shipping_info for Blocks
	function trackShippingInfoBlocks () {
		if ( !window.tggr_checkout_data || !window.tggr_checkout_data.add_shipping_info_enabled ) {
			return;
		}

		if ( window.tggr_shipping_listeners_attached ) {
			return;
		}

		const checkoutForm = document.querySelector( '.wc-block-checkout, .wc-block-checkout__form' );

		if ( !checkoutForm ) {
			return;
		}

		let lastTrackedShippingMethod = null;

		const trackShipping = function () {
			if ( !window.tggr_checkout_data.cart_data ) {
				return;
			}

			// Try rate-selection panels first, then fall back to totals row
			let shippingSection = checkoutForm.querySelector( '.wc-block-components-shipping-rates-control' );

			if ( !shippingSection ) {
				shippingSection = checkoutForm.querySelector( '.wc-block-checkout__shipping-method-container' );
			}
			if ( !shippingSection ) {
				shippingSection = checkoutForm.querySelector( '#shipping-method' );
			}
			if ( !shippingSection ) {
				shippingSection = checkoutForm.querySelector( '.wc-block-components-totals-shipping' );
			}
			if ( !shippingSection ) {
				return;
			}

			let selectedShipping = shippingSection.querySelector( 'input[type="radio"]:checked' );
			let shippingText = '';

			if ( selectedShipping ) {
				const shippingLabel = selectedShipping.closest( 'label' );

				if ( shippingLabel ) {
					shippingText = shippingLabel.textContent.trim();
				}
			} else {
				const selectedRole = shippingSection.querySelector( '[role="radio"][aria-checked="true"]' );

				if ( selectedRole ) {
					const titleElement = selectedRole.querySelector( '.wc-block-checkout__shipping-method-option-title' );

					shippingText = titleElement
						? titleElement.textContent.trim()
						: selectedRole.textContent.trim();
					selectedShipping = selectedRole;
				}
			}

			if ( !selectedShipping || !shippingText ) {
				return;
			}

			if ( shippingText === lastTrackedShippingMethod ) {
				return;
			}

			const eventData = JSON.parse( JSON.stringify( window.tggr_checkout_data.cart_data ) );

			eventData.event = 'add_shipping_info';

			delete eventData.ecommerce.payment_type;
			eventData.ecommerce.shipping_tier = shippingText;

			pushToDataLayer( { ecommerce: null } );
			pushToDataLayer( eventData );
			lastTrackedShippingMethod = shippingText;
		};

		// Find the shipping section for direct listener attachment — rate-selection panels first
		let shippingSection = checkoutForm.querySelector( '.wc-block-components-shipping-rates-control' );

		if ( !shippingSection ) {
			shippingSection = checkoutForm.querySelector( '.wc-block-checkout__shipping-method-container' );
		}
		if ( !shippingSection ) {
			shippingSection = checkoutForm.querySelector( '#shipping-method' );
		}
		if ( !shippingSection ) {
			shippingSection = checkoutForm.querySelector( '.wc-block-components-totals-shipping' );
		}

		if ( !shippingSection ) {
			return; // Section not in DOM yet — MutationObserver will retry
		}

		// Listen for regular radio input changes
		shippingSection.querySelectorAll( 'input[type="radio"]' ).forEach( function ( option ) {
			option.addEventListener( 'change', trackShipping );
		} );

		// Use event delegation on checkoutForm (stable ancestor) for role="radio" clicks.
		// This survives React re-renders that may replace inner elements.
		checkoutForm.addEventListener( 'click', function ( e ) {
			if ( e.target.closest( '.wc-block-checkout__shipping-method-option, .wc-block-checkout__shipping-method-container' ) ) {
				setTimeout( trackShipping, 200 );
			}
		} );

		// Also delegate input[type="radio"] changes at form level for dynamically loaded options
		checkoutForm.addEventListener( 'change', function ( e ) {
			if ( e.target && e.target.type === 'radio' && e.target.closest( '.wc-block-checkout__shipping-option, .wc-block-components-shipping-rates-control' ) ) {
				setTimeout( trackShipping, 50 );
			}
		} );

		// Initial state checks — multiple retries to survive React hydration timing
		[ 500, 1500, 3000 ].forEach( function ( delay ) {
			setTimeout( trackShipping, delay );
		} );

		window.tggr_shipping_listeners_attached = true;
	}

	// Track add_payment_info for Blocks
	function trackPaymentInfoBlocks () {
		if ( !window.tggr_checkout_data || !window.tggr_checkout_data.add_payment_info_enabled ) {
			return;
		}

		if ( window.tggr_payment_listeners_attached ) {
			return;
		}

		const checkoutForm = document.querySelector( '.wc-block-checkout, .wc-block-checkout__form' );

		if ( !checkoutForm ) {
			return;
		}

		let lastTrackedPaymentMethod = null;

		const trackPayment = function () {
			if ( !window.tggr_checkout_data.cart_data ) {
				return;
			}

			let selectedPayment = checkoutForm.querySelector( 'input[name="radio-control-wc-payment-method-options"]:checked' );
			let paymentValue = '';

			if ( selectedPayment ) {
				paymentValue = selectedPayment.value;
			} else {
				const paymentSection = checkoutForm.querySelector( '.wc-block-components-payment-method-options' );

				if ( paymentSection ) {
					const selectedRole = paymentSection.querySelector( '[role="radio"][aria-checked="true"]' );

					if ( selectedRole ) {
						paymentValue = selectedRole.getAttribute( 'data-value' ) ||
						               selectedRole.getAttribute( 'id' ) ||
						               selectedRole.textContent.trim();
						selectedPayment = selectedRole;
					}
				}
			}

			if ( !selectedPayment || !paymentValue ) {
				return;
			}

			if ( paymentValue === lastTrackedPaymentMethod ) {
				return;
			}

			const eventData = JSON.parse( JSON.stringify( window.tggr_checkout_data.cart_data ) );

			eventData.event = 'add_payment_info';

			delete eventData.ecommerce.shipping_tier;
			eventData.ecommerce.payment_type = paymentValue;

			pushToDataLayer( { ecommerce: null } );
			pushToDataLayer( eventData );
			lastTrackedPaymentMethod = paymentValue;
		};

		const paymentInputs = checkoutForm.querySelectorAll( 'input[name="radio-control-wc-payment-method-options"]' );
		const paymentSection = checkoutForm.querySelector( '.wc-block-components-payment-method-options' );
		const paymentRoles = paymentSection ? paymentSection.querySelectorAll( '[role="radio"]' ) : [];

		if ( !paymentSection && paymentInputs.length === 0 ) {
			return; // Section not in DOM yet — MutationObserver will retry
		}

		paymentInputs.forEach( function ( option ) {
			option.addEventListener( 'change', trackPayment );
		} );

		paymentRoles.forEach( function ( option ) {
			option.addEventListener( 'click', function () {
				setTimeout( trackPayment, 100 );
			} );
		} );

		// Event delegation for payment method changes
		checkoutForm.addEventListener( 'change', function ( e ) {
			if ( e.target && e.target.name === 'radio-control-wc-payment-method-options' ) {
				setTimeout( trackPayment, 50 );
			}
		} );

		if ( paymentSection ) {
			paymentSection.addEventListener( 'click', function () {
				setTimeout( trackPayment, 150 );
			} );
		}

		setTimeout( trackPayment, 500 );

		window.tggr_payment_listeners_attached = true;
	}

	// Initialize tracking when DOM is ready
	function init () {
		if ( !isBlocksCheckout() ) {
			return; // Not a Blocks checkout, skip
		}

		trackBeginCheckoutBlocks();

		// Use MutationObserver to handle dynamically loaded content
		const observer = new MutationObserver( function () {
			trackShippingInfoBlocks();
			trackPaymentInfoBlocks();
		} );

		const checkoutElement = document.querySelector( '.wc-block-checkout, .wc-block-checkout__form' );

		if ( checkoutElement ) {
			observer.observe( checkoutElement, {
				childList: true,
				subtree: true,
			} );

			// Fallback attempts for shipping/payment options that may load after address entry
			[ 1000, 2500, 5000 ].forEach( function ( delay ) {
				setTimeout( function () {
					trackShippingInfoBlocks();
					trackPaymentInfoBlocks();
				}, delay );
			} );
		}
	}

	// Wait for DOM and WooCommerce Blocks to be ready
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}

	// Also try on window load as fallback
	window.addEventListener( 'load', function () {
		setTimeout( init, 500 );
	} );
} )();
