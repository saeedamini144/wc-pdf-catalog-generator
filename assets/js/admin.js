jQuery(document).ready(function($){
    var frame;
    $('#wc-pdf-template-upload').on('click', function(e){
        e.preventDefault();
        if ( frame ) { frame.open(); return; }
        frame = wp.media({
            title: WCPDFAdmin.media_title || 'Select Image',
            button: { text: 'Select' },
            multiple: false
        });
        frame.on('select', function(){
            var attachment = frame.state().get('selection').first().toJSON();
            $('#wc_pdf_template_id').val(attachment.id);
            var url = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
            $('#wc-pdf-template-preview').html('<img src="'+url+'" style="max-width:300px;height:auto;">');
        });
        frame.open();
    });

    $('#wc-pdf-template-remove').on('click', function(e){
        e.preventDefault();
        $('#wc_pdf_template_id').val('');
        $('#wc-pdf-template-preview').html('');
    });
});
