(function ($) {
    ("use strict");
    
    $('.upload-image-button').on('click', function(e) {
        e.preventDefault();
    
        const button = $(this),
              group_item = button.closest('p'),
              target = group_item.find('input[type="text"]'),
              preview = group_item.next('p').find('img');
    
        var image = wp.media({ 
            title: 'Upload Image',
            multiple: false
        }).open()
        .on('select', function(e){
            var uploaded_image = image.state().get('selection').first();
            var image_url = uploaded_image.toJSON().url;
                target.val(image_url);
                preview.attr('src', image_url).show();
                button.nextAll('.remove-image-button').show();
        });
    });
    
    $('.remove-image-button').on('click', function(e) {
        e.preventDefault();
    
        const button = $(this),
            group_item = button.closest('p'),
            target = group_item.find('input[type="text"]'),
            preview = group_item.next('p').find('img');
    
            target.val('');
            preview.hide();
            button.hide();
    
        return false;
    });
    
    })(jQuery); /*End document ready*/
      