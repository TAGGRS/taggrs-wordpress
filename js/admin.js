jQuery( document ).ready( function ( $ ) {
	$( '.nav-tab' ).click( function ( e ) {
		e.preventDefault();
		$( '.nav-tab' ).removeClass( 'nav-tab-active' );
		$( this ).addClass( 'nav-tab-active' );
		let tab = $( this ).attr( 'href' ).split( '&tab=' )[ 1 ];

		if ( tab == 'gtm' ) {
			// Show GTM settings and hide events
		} else if ( tab == 'events' ) {
			// Show events and hide GTM settings
		}
	} );
} );

document.addEventListener( 'DOMContentLoaded', function () {
	const urlToggleField = document.getElementById( 'tggr_url_toggle' );
	const containerIdField = document.getElementById(
		'enhanced_tracking_container_id',
	);
	const enhancedTrackingSection = document.getElementById(
		'enhanced_tracking_section',
	);

	if ( !urlToggleField || !containerIdField || !enhancedTrackingSection ) {
		return;
	}

	function toggleEnhancedTrackingSection () {
		const isEnabled = urlToggleField.value.trim() !== '';

		containerIdField.disabled = !isEnabled;
		enhancedTrackingSection.style.opacity = isEnabled ? '1' : '0.7';

		if ( !isEnabled ) {
			containerIdField.value = '';
		}
	}

	toggleEnhancedTrackingSection();
	urlToggleField.addEventListener( 'input', toggleEnhancedTrackingSection );
} );
