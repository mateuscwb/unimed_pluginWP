document.addEventListener( 'wpcf7mailsent', function( event ) {
	var configuredId = (typeof ABGSOFT_AUTOPARK_SETTINGS !== 'undefined' && ABGSOFT_AUTOPARK_SETTINGS.cf7_form_id)
		? String(ABGSOFT_AUTOPARK_SETTINGS.cf7_form_id)
		: '1703';
	if ( configuredId == String(event.detail.contactFormId) ) {
		//var inputs = event.detail.inputs;
		var qrcode = event.detail.apiResponse.qrcode;
		var image = "data:image/png;base64,";
		image = image + qrcode;
		jQuery("#qrcodeImg").attr('src', image);
		jQuery("#qrcodeDownload").attr('href', image);
		jQuery('#qrcodeModal').modal();
	}	
}, false );


jQuery(document).ready(function(){
	jQuery.modal.defaults = {
	  clickClose: false,       // Allows the user to close the modal by clicking the overlay
	  closeText: 'Fechar',     // Text content for the close <a> tag.
	  showClose: true,        // Shows a (X) icon/link in the top-right corner
	  fadeDuration: 500,     // Number of milliseconds the fade transition takes (null means no transition)
	  fadeDelay: 1          // Point during the overlay's fade-in that the modal begins to fade in (.5 = 50%, 1.5 = 150%, etc.)
	};
});