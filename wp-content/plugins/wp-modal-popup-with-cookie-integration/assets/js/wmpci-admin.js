// Admin js
jQuery( document ).ready(function($) {
    
    if( Wmpci_Admin.new_ui == 1 ){
    	
    	$('.wmpci-color-box').wpColorPicker();

    } else{

    	var inputcolor = jQuery('.wmpci-color-box-farbtastic').prev('input').val();
		jQuery('.wmpci-color-box-farbtastic').prev('input').css('background-color',inputcolor);
		jQuery('.wmpci-color-box-farbtastic').click(function(e) {
			colorPicker = jQuery(this).next('div');
			input = jQuery(this).prev('input');
			jQuery.farbtastic(jQuery(colorPicker), function(a) { jQuery(input).val(a).css('background', a); });
			colorPicker.show();
			e.preventDefault();
			jQuery(document).mousedown( function() { jQuery(colorPicker).hide(); });
		});
    }
});