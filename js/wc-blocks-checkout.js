/**
 * WooCommerce Blocks Checkout Tracking
 * Handles GA4 events for the new WooCommerce Blocks-based checkout
 */

( function () {
	'use strict';

	// Check if this is a Blocks checkout
	function isBlocksCheckout () {
		return document.querySelector( '.wc-block-checkout' ) !== null;
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

		// Use a flag to prevent duplicate tracking
		if ( window.tggr_begin_checkout_tracked ) {
			return;
		}

		// Check if checkout block is present
		if ( isBlocksCheckout() ) {
			// Get cart data from localized script
			if ( window.tggr_checkout_data.cart_data ) {
				const eventData = Object.assign( {}, window.tggr_checkout_data.cart_data );
				
				// Clean up - begin_checkout should not have shipping_tier or payment_type
				if ( eventData.ecommerce.shipping_tier ) {
					delete eventData.ecommerce.shipping_tier;
				}
				if ( eventData.ecommerce.payment_type ) {
					delete eventData.ecommerce.payment_type;
				}
				
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

		// Listen for shipping method changes
		const checkoutForm = document.querySelector( '.wc-block-checkout' );

		if ( !checkoutForm ) {
			return;
		}

		// Track last shipping method to avoid duplicate events
		let lastTrackedShippingMethod = null;

		const trackShipping = function ( triggerSource ) {
			if ( !window.tggr_checkout_data.cart_data ) {
				return;
			}

			// Get selected shipping method - be very specific
			const shippingSection = checkoutForm.querySelector( '.wc-block-components-totals-shipping' );
			if ( !shippingSection ) {
				return;
			}

			// Check for both input[type="radio"] and elements with role="radio"
			let selectedShipping = shippingSection.querySelector( 'input[type="radio"]:checked' );
			let shippingText = '';

			if ( selectedShipping ) {
				// Regular radio input
				const shippingLabel = selectedShipping.closest( 'label' );
				if ( shippingLabel ) {
					shippingText = shippingLabel.textContent.trim();
				}
			} else {
				// Check for role="radio" with aria-checked="true"
				const selectedRole = shippingSection.querySelector( '[role="radio"][aria-checked="true"]' );
				if ( selectedRole ) {
					shippingText = selectedRole.textContent.trim();
					selectedShipping = selectedRole; // Mark as found
				}
			}

			// Only track if a shipping method is actually selected
			if ( !selectedShipping || !shippingText ) {
				return;
			}

			// Don't track if this is the same shipping method we just tracked
			if ( shippingText === lastTrackedShippingMethod ) {
				return;
			}

			const eventData = Object.assign( {}, window.tggr_checkout_data.cart_data );
			eventData.event = 'add_shipping_info';

			// Clean up - remove payment_type if it exists
			if ( eventData.ecommerce.payment_type ) {
				delete eventData.ecommerce.payment_type;
			}

			eventData.ecommerce.shipping_tier = shippingText;

			pushToDataLayer( eventData );
			lastTrackedShippingMethod = shippingText;
		};

		// Watch for shipping option selection - support both input and role="radio"
		const shippingInputs = checkoutForm.querySelectorAll( '.wc-block-components-totals-shipping input[type="radio"]' );
		const shippingRoles = checkoutForm.querySelectorAll( '.wc-block-components-totals-shipping [role="radio"]' );
		const shippingSection = checkoutForm.querySelector( '.wc-block-components-totals-shipping' );

		// Add event listeners to regular inputs
		shippingInputs.forEach( function ( option ) {
			option.addEventListener( 'change', function() {
				trackShipping( 'input-change' );
			} );
		} );

		// Add event listeners to role="radio" elements
		shippingRoles.forEach( function ( option ) {
			option.addEventListener( 'click', function() {
				// Small delay to let aria-checked update
				setTimeout( function() {
					trackShipping( 'role-click' );
				}, 100 );
			} );
		} );

		// Use event delegation for dynamically loaded shipping options
		if ( shippingSection ) {
			shippingSection.addEventListener( 'change', function( e ) {
				if ( e.target && e.target.type === 'radio' ) {
					setTimeout( function() {
						trackShipping( 'delegated-change' );
					}, 50 );
				}
			} );

			// Also listen for clicks on the shipping section
			shippingSection.addEventListener( 'click', function( e ) {
				setTimeout( function() {
					trackShipping( 'section-click' );
				}, 150 );
			} );
		}

		// Check immediately if there's already a selected option (default)
		setTimeout( function() {
			trackShipping( 'initial-check' );
		}, 500 );

		// Mark listeners as attached (not tracking completed)
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

		const checkoutForm = document.querySelector( '.wc-block-checkout' );

		if ( !checkoutForm ) {
			return;
		}

		// Track last payment method to avoid duplicate events
		let lastTrackedPaymentMethod = null;

		const trackPayment = function ( triggerSource ) {
			if ( !window.tggr_checkout_data.cart_data ) {
				return;
			}

			// Get selected payment method - support both input and role="radio"
			let selectedPayment = checkoutForm.querySelector( 'input[name="radio-control-wc-payment-method-options"]:checked' );
			let paymentValue = '';

			if ( selectedPayment ) {
				// Regular radio input
				paymentValue = selectedPayment.value;
			} else {
				// Check for role="radio" with aria-checked="true" in payment methods
				const paymentSection = checkoutForm.querySelector( '.wc-block-components-payment-method-options' );
				if ( paymentSection ) {
					const selectedRole = paymentSection.querySelector( '[role="radio"][aria-checked="true"]' );
					if ( selectedRole ) {
						// Try to get value from data attribute or id
						paymentValue = selectedRole.getAttribute( 'data-value' ) || 
						               selectedRole.getAttribute( 'id' ) || 
						               selectedRole.textContent.trim();
						selectedPayment = selectedRole; // Mark as found
					}
				}
			}

			// Only track if a payment method is actually selected
			if ( !selectedPayment || !paymentValue ) {
				return;
			}

			// Don't track if this is the same payment method we just tracked
			if ( paymentValue === lastTrackedPaymentMethod ) {
				return;
			}

			const eventData = Object.assign( {}, window.tggr_checkout_data.cart_data );
			eventData.event = 'add_payment_info';

			// Clean up - remove shipping_tier if it exists
			if ( eventData.ecommerce.shipping_tier ) {
				delete eventData.ecommerce.shipping_tier;
			}

			eventData.ecommerce.payment_type = paymentValue;

			pushToDataLayer( eventData );
			lastTrackedPaymentMethod = paymentValue;
		};

		// Watch for payment method selection - support both input and role="radio"
		const paymentInputs = checkoutForm.querySelectorAll( 'input[name="radio-control-wc-payment-method-options"]' );
		const paymentSection = checkoutForm.querySelector( '.wc-block-components-payment-method-options' );
		const paymentRoles = paymentSection ? paymentSection.querySelectorAll( '[role="radio"]' ) : [];

		// Add event listeners to regular inputs
		paymentInputs.forEach( function ( option ) {
			option.addEventListener( 'change', function() {
				trackPayment( 'input-change' );
			} );
		} );

		// Add event listeners to role="radio" elements
		paymentRoles.forEach( function ( option ) {
			option.addEventListener( 'click', function() {
				// Small delay to let aria-checked update
				setTimeout( function() {
					trackPayment( 'role-click' );
				}, 100 );
			} );
		} );

		// Use event delegation for dynamically loaded payment options
		checkoutForm.addEventListener( 'change', function( e ) {
			if ( e.target && e.target.name === 'radio-control-wc-payment-method-options' ) {
				setTimeout( function() {
					trackPayment( 'delegated-change' );
				}, 50 );
			}
		} );

		// Also listen for clicks on the payment section
		if ( paymentSection ) {
			paymentSection.addEventListener( 'click', function( e ) {
				setTimeout( function() {
					trackPayment( 'section-click' );
				}, 150 );
			} );
		}

		// Check immediately if there's already a selected option (default)
		setTimeout( function() {
			trackPayment( 'initial-check' );
		}, 500 );

		// Mark listeners as attached (not tracking completed)
		window.tggr_payment_listeners_attached = true;
	}

	// Initialize tracking when DOM is ready
	function init () {
		if ( !isBlocksCheckout() ) {
			return; // Not a Blocks checkout, skip
		}

		// Track begin_checkout immediately
		trackBeginCheckoutBlocks();

		// Setup observers for shipping and payment
		// Use MutationObserver to handle dynamically loaded content
		const observer = new MutationObserver( function ( mutations ) {
			trackShippingInfoBlocks();
			trackPaymentInfoBlocks();
		} );

		const checkoutElement = document.querySelector( '.wc-block-checkout' );

		if ( checkoutElement ) {
			observer.observe( checkoutElement, {
				childList: true,
				subtree: true,
			} );

			// Try multiple times with different delays
			// (shipping/payment options might load after address is entered)
			setTimeout( function () {
				trackShippingInfoBlocks();
				trackPaymentInfoBlocks();
			}, 1000 );

			setTimeout( function () {
				trackShippingInfoBlocks();
				trackPaymentInfoBlocks();
			}, 2500 );

			setTimeout( function () {
				trackShippingInfoBlocks();
				trackPaymentInfoBlocks();
			}, 5000 );
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
