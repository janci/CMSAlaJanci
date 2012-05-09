jQuery(function($){
	$('.photo-upload').each(function(){
                var gallery_id = $(this).attr('data-gallery_id');
		var uploader = new qq.FileUploader({
			element: this,
			action: 'upload-photos/'+gallery_id,
			debug: true
		});
	});
});
