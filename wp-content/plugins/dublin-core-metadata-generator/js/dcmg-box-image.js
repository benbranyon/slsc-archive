var $ = jQuery.noConflict();
$(document).ready(function($) {
      var meta_image_frame;
      $('#dcmg-image-button').live('click', function(e){
            e.preventDefault();
            if( meta_image_frame ){
                meta_image_frame.open();
                return;
            }
            meta_image_frame = wp.media.frames.file_frame = wp.media({
                title: 'Dublin Core Metatags Generator Image',
                library: { type: 'image'},
                  multiple: false
            });
            meta_image_frame.on('select', function(){
                var media_attachment = meta_image_frame.state().get('selection').first().toJSON();
                  var url = '';
                $('#dcmg-img').val(media_attachment.url);
            });
            meta_image_frame.open();
      });
	    $("#remove_default_image").live('click', function() {
			$(this).parent().remove();
			$('#imgtop').removeAttr('src');
			$('#dcmg-img').removeAttr('value');
			$('#img-dcmg').hide();
			$('#remove_default_image').hide();
		});
});