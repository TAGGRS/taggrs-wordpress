document.addEventListener( 'DOMContentLoaded', function () {
	const urlToggleField = document.getElementById( 'tggr_url_toggle' );
	const enhancedTrackingCheckbox = document.getElementById( 'tggr_tggr_enhanced_tracking_v2' );
	const containerIdField = document.getElementById( 'tggr_enhanced_tracking_v2_container_id' );
	const enhancedTrackingSection = document.getElementById( 'tggr_enhanced_tracking_v2_section' );
	const submitButton = document.querySelector( '#tggr-options-form input[type="submit"], #tggr-options-form button[type="submit"]' ); // Specifieke selectie van de submitknop binnen het formulier

	let errorMessage = document.createElement( 'p' );

	errorMessage.style.color = 'red';
	errorMessage.style.fontSize = '12px';
	errorMessage.style.marginTop = '5px';
	errorMessage.textContent = 'To enable Enhanced Tracking v2, it is required to supply a TAGGRS Container Identifier.';
	errorMessage.style.display = 'none';
	containerIdField.insertAdjacentElement( 'afterend', errorMessage );

	function toggleEnhancedTrackingSection () {
		const urlToggleValue = urlToggleField.value;
		const isEnabled = urlToggleValue !== '';

		enhancedTrackingCheckbox.disabled = !isEnabled;
		containerIdField.disabled = !isEnabled;
		enhancedTrackingSection.style.opacity = isEnabled ? '1' : '0.7';

		if ( !isEnabled ) {
			enhancedTrackingCheckbox.checked = false;
			containerIdField.value = '';
			errorMessage.style.display = 'none';
		}

		validateForm();
	}

	function validateForm () {
		if ( enhancedTrackingCheckbox.checked && containerIdField.value.trim() === '' ) {
			errorMessage.style.display = 'block';
			submitButton.disabled = true;
		} else {
			errorMessage.style.display = 'none';
			submitButton.disabled = false;
		}
	}

	if ( urlToggleField && enhancedTrackingCheckbox && containerIdField && enhancedTrackingSection && submitButton ) {
		toggleEnhancedTrackingSection();
		urlToggleField.addEventListener( 'input', toggleEnhancedTrackingSection );
		enhancedTrackingCheckbox.addEventListener( 'change', validateForm );
		containerIdField.addEventListener( 'input', validateForm );
	}
} );
