(function($){
    'use strict';

    // client-side validation (final validation always happens on the server too)
    // returns { errors: [ 'message', ... ], fields: [ 'first_name', ... ] }
    function validateFormData(data) {
        var errors = [];
        var fields = [];

        if (!data.first_name || data.first_name.trim() === '') {
            errors.push('First name is required.');
            fields.push('first_name');
        }
        if (!data.last_name || data.last_name.trim() === '') {
            errors.push('Last name is required.');
            fields.push('last_name');
        }
        if (!data.email || data.email.trim() === '') {
            errors.push('Email is required.');
            fields.push('email');
        } else {
            var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!re.test(data.email)) {
                errors.push('Please enter a valid email address.');
                fields.push('email');
            }
        }
        if (!data.phone || data.phone.trim() === '') {
            errors.push('Phone number is required.');
            fields.push('phone');
        }

        return { errors: errors, fields: fields };
    }

    // build the lead form HTML that must be completed before the PDF is generated
    function buildInlineForm(nonce, category) {
        var html = '';
        html += '<form class="wc-pdf-catalog-form" method="post" action="#" data-nonce="' + nonce + '" data-category="' + (category || '') + '">';
        html += '<div class="wc-pdf-form-errors"></div>';
        html += '<div class="wc-pdf-fields">';
        html += '<div class="wc-pdf-field"><label>First Name <span class="wc-pdf-required">*</span></label><input type="text" name="first_name" required></div>';
        html += '<div class="wc-pdf-field"><label>Last Name <span class="wc-pdf-required">*</span></label><input type="text" name="last_name" required></div>';
        html += '<div class="wc-pdf-field"><label>Email <span class="wc-pdf-required">*</span></label><input type="email" name="email" required></div>';
        html += '<div class="wc-pdf-field"><label>Phone Number <span class="wc-pdf-required">*</span></label><input type="tel" name="phone" required></div>';
        html += '<div class="wc-pdf-field wc-pdf-field-full"><label>Company Name</label><input type="text" name="company"></div>';
        html += '<div class="wc-pdf-field"><label>Country</label><input type="text" name="country"></div>';
        html += '<div class="wc-pdf-field"><label>Telegram ID</label><input type="text" name="telegram_id"></div>';
        html += '<div class="wc-pdf-field wc-pdf-field-full"><label>WhatsApp</label><input type="text" name="whatsapp"></div>';
        html += '</div>';
        html += '<div class="wc-pdf-actions"><button type="submit" class="wc-pdf-submit">Submit and Download Catalog</button></div>';
        html += '</form>';
        return html;
    }

    // build the success box (message + download button + share links) once the PDF file is ready
    function buildDownloadBlock(url) {
        var encodedUrl = encodeURIComponent(url);
        var html = '<div class="wc-pdf-success-box">';
        html += '<p class="wc-pdf-success-message">Your catalog is ready.</p>';
        html += '<div class="wc-pdf-download-block">';
        html += '<a href="' + url + '" target="_blank" rel="noopener" class="button button-primary wc-pdf-download-link">Download PDF Catalog</a>';
        html += '</div>';
        html += '<div class="wc-pdf-share-links">';
        html += '<span class="wc-pdf-share-label">Share:</span>';
        html += '<a href="https://wa.me/?text=' + encodedUrl + '" target="_blank" rel="noopener" class="wc-pdf-share whatsapp">WhatsApp</a>';
        html += '<a href="https://t.me/share/url?url=' + encodedUrl + '" target="_blank" rel="noopener" class="wc-pdf-share telegram">Telegram</a>';
        html += '<a href="mailto:?subject=' + encodeURIComponent('Product Catalog') + '&body=' + encodedUrl + '" class="wc-pdf-share email">Email</a>';
        html += '<button type="button" class="wc-pdf-share copy-link" data-url="' + url + '">Copy Link</button>';
        html += '</div>';
        html += '</div>';
        return html;
    }

    /* ---------------------------------------------------------------------
     * Lead form popup (modal): opens the form in a floating overlay with a
     * smooth fade + scale animation.
     * ------------------------------------------------------------------- */

    var $modalOverlay = null;

    function getModal() {
        if ( $modalOverlay && $modalOverlay.length ) {
            return $modalOverlay;
        }

        var html = '' +
            '<div class="wc-pdf-modal-overlay" aria-hidden="true">' +
                '<div class="wc-pdf-modal" role="dialog" aria-modal="true">' +
                    '<button type="button" class="wc-pdf-modal-close" aria-label="Close">&times;</button>' +
                    '<div class="wc-pdf-modal-body"></div>' +
                '</div>' +
            '</div>';

        $modalOverlay = $(html).appendTo('body');
        return $modalOverlay;
    }

    function openModal(contentHtml) {
        var $overlay = getModal();
        $overlay.find('.wc-pdf-modal-body').html(contentHtml);
        $('body').addClass('wc-pdf-modal-open');
        $overlay.attr('aria-hidden', 'false');

        // the "is-open" class must be added on the next render frame for the CSS transition to trigger
        requestAnimationFrame(function(){
            requestAnimationFrame(function(){
                $overlay.addClass('is-open');
            });
        });
    }

    function closeModal() {
        if ( ! $modalOverlay ) {
            return;
        }
        $modalOverlay.removeClass('is-open').attr('aria-hidden', 'true');
        $('body').removeClass('wc-pdf-modal-open');
    }

    // close on backdrop click or close button
    $(document).on('click', '.wc-pdf-modal-overlay', function(e){
        if ( e.target === this ) {
            closeModal();
        }
    });
    $(document).on('click', '.wc-pdf-modal-close', function(e){
        e.preventDefault();
        closeModal();
    });

    // close on Escape key
    $(document).on('keydown', function(e){
        if ( e.key === 'Escape' && $modalOverlay && $modalOverlay.hasClass('is-open') ) {
            closeModal();
        }
    });

    // shortcode button click: the lead form always opens as a popup
    $(document).on('click', '.wc-pdf-catalog-btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var category = $btn.data('category') || '';
        var nonce = $btn.data('nonce') || WCPDFCatalog.nonce_form;

        var formHtml = buildInlineForm( nonce, category );
        openModal( formHtml );
    });

    // show a boxed error list at the top of the form and highlight the offending fields
    function showFormErrors($form, messages, fieldNames) {
        $form.find('.wc-pdf-field-error').removeClass('wc-pdf-field-error');

        var $box = $form.find('.wc-pdf-form-errors');
        if ( messages && messages.length ) {
            $box.html('<ul><li>' + messages.join('</li><li>') + '</li></ul>').show();
            $.each(fieldNames || [], function(i, name){
                $form.find('[name="' + name + '"]').addClass('wc-pdf-field-error');
            });
        } else {
            $box.hide().empty();
        }
    }

    // replace the whole form with the success box once the PDF is ready
    function showSuccess($form, url) {
        var $container = $form.closest('.wc-pdf-modal-body');
        if ( ! $container.length ) {
            $container = $form.parent();
        }
        $container.html( buildDownloadBlock(url) );
    }

    // form submit: validate first, then save the request and generate the PDF on the server
    $(document).on('submit', '.wc-pdf-catalog-form', function(e){
        e.preventDefault();
        var $form = $(this);
        var nonce = $form.data('nonce') || WCPDFCatalog.nonce_form;
        var category = $form.data('category') || '';

        var formData = {
            first_name: $form.find('[name="first_name"]').val(),
            last_name: $form.find('[name="last_name"]').val(),
            email: $form.find('[name="email"]').val(),
            country: $form.find('[name="country"]').val(),
            company: $form.find('[name="company"]').val(),
            phone: $form.find('[name="phone"]').val(),
            telegram_id: $form.find('[name="telegram_id"]').val(),
            whatsapp: $form.find('[name="whatsapp"]').val()
        };

        var validation = validateFormData(formData);
        if ( validation.errors.length ) {
            showFormErrors($form, validation.errors, validation.fields);
            return;
        }
        showFormErrors($form, [], []);

        var $submitBtn = $form.find('.wc-pdf-submit');
        var originalLabel = $submitBtn.text();
        $submitBtn.prop('disabled', true).text('Preparing your PDF...');

        var data = {
            action: 'wc_pdf_catalog_submit_form',
            nonce: nonce,
            category: category,
            first_name: formData.first_name,
            last_name: formData.last_name,
            email: formData.email,
            country: formData.country,
            company: formData.company,
            phone: formData.phone,
            telegram_id: formData.telegram_id,
            whatsapp: formData.whatsapp
        };

        $.post( WCPDFCatalog.ajax_url, data )
            .done(function(res){
                if ( res && res.status && res.download_url ) {
                    showSuccess($form, res.download_url);
                } else {
                    var messages = [ 'There was a problem submitting the form.' ];
                    var fields = [];
                    if ( res && res.errors ) {
                        messages = [];
                        $.each(res.errors, function(field, text){
                            messages.push(text);
                            if ( field !== 'general' ) {
                                fields.push(field);
                            }
                        });
                    } else if ( res && res.message ) {
                        messages = [ res.message ];
                    }
                    showFormErrors($form, messages, fields);
                    $submitBtn.prop('disabled', false).text(originalLabel);
                }
            })
            .fail(function(xhr){
                var messages = [ 'Could not connect to the server.' ];
                if (xhr && xhr.responseJSON && xhr.responseJSON.errors) {
                    messages = [];
                    $.each(xhr.responseJSON.errors, function(field, text){
                        messages.push(text);
                    });
                }
                showFormErrors($form, messages, []);
                $submitBtn.prop('disabled', false).text(originalLabel);
            });
    });

    // copy the download link to the clipboard
    $(document).on('click', '.wc-pdf-share.copy-link', function(e){
        e.preventDefault();
        var $btn = $(this);
        var url = $btn.data('url');

        function done() {
            var original = $btn.data('original-text') || $btn.text();
            $btn.data('original-text', original);
            $btn.text('Copied!');
            setTimeout(function(){ $btn.text(original); }, 1500);
        }

        if ( navigator.clipboard && navigator.clipboard.writeText ) {
            navigator.clipboard.writeText(url).then(done);
        } else {
            var $tmp = $('<input>').val(url).appendTo('body').select();
            document.execCommand('copy');
            $tmp.remove();
            done();
        }
    });

})(jQuery);
