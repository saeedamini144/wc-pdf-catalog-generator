(function($){
    'use strict';

    // helper: نمایش پیام خطا/موفقیت
    function showResponse($container, html, isError) {
        $container.removeClass('wc-pdf-error wc-pdf-success');
        if (isError) $container.addClass('wc-pdf-error'); else $container.addClass('wc-pdf-success');
        $container.html(html);
    }

    // client-side validation ساده (اعتبارسنجی نهایی همیشه در سرور هم انجام می‌شود)
    function validateFormData(data) {
        var errors = {};
        if (!data.first_name || data.first_name.trim() === '') {
            errors.first_name = 'نام الزامی است';
        }
        if (!data.last_name || data.last_name.trim() === '') {
            errors.last_name = 'نام خانوادگی الزامی است';
        }
        if (!data.email || data.email.trim() === '') {
            errors.email = 'ایمیل الزامی است';
        } else {
            var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!re.test(data.email)) {
                errors.email = 'ایمیل معتبر نیست';
            }
        }
        if (!data.phone || data.phone.trim() === '') {
            errors.phone = 'شماره تلفن الزامی است';
        }
        return errors;
    }

    // ساخت HTML فرم لید که پیش از تولید PDF باید تکمیل شود
    function buildInlineForm(nonce, category) {
        var html = '';
        html += '<form class="wc-pdf-catalog-form" method="post" action="#" data-nonce="' + nonce + '" data-category="' + (category || '') + '">';
        html += '<div><label>نام *</label><input type="text" name="first_name" required></div>';
        html += '<div><label>نام خانوادگی *</label><input type="text" name="last_name" required></div>';
        html += '<div><label>ایمیل *</label><input type="email" name="email" required></div>';
        html += '<div><label>کشور</label><input type="text" name="country"></div>';
        html += '<div><label>شرکت</label><input type="text" name="company"></div>';
        html += '<div><label>تلفن *</label><input type="text" name="phone" required></div>';
        html += '<div><label>تلگرام</label><input type="text" name="telegram_id"></div>';
        html += '<div><label>واتس‌اپ</label><input type="text" name="whatsapp"></div>';
        html += '<div><button type="submit" class="wc-pdf-submit">ارسال و دریافت کاتالوگ PDF</button></div>';
        html += '<div class="wc-pdf-response" style="margin-top:8px;"></div>';
        html += '</form>';
        return html;
    }

    // ساخت بلاک دانلود + اشتراک‌گذاری بعد از آماده شدن فایل PDF
    function buildDownloadBlock(url) {
        var encodedUrl = encodeURIComponent(url);
        var html = '<div class="wc-pdf-download-block">';
        html += '<a href="' + url + '" target="_blank" rel="noopener" class="button button-primary wc-pdf-download-link">دانلود PDF کاتالوگ</a>';
        html += '<div class="wc-pdf-share-links">';
        html += '<span class="wc-pdf-share-label">اشتراک‌گذاری:</span>';
        html += '<a href="https://wa.me/?text=' + encodedUrl + '" target="_blank" rel="noopener" class="wc-pdf-share whatsapp">واتس‌اپ</a>';
        html += '<a href="https://t.me/share/url?url=' + encodedUrl + '" target="_blank" rel="noopener" class="wc-pdf-share telegram">تلگرام</a>';
        html += '<button type="button" class="wc-pdf-share copy-link" data-url="' + url + '">کپی لینک</button>';
        html += '</div>';
        html += '</div>';
        return html;
    }

    // کلیک روی دکمه شورت‌کد: همیشه ابتدا فرم لید نمایش داده می‌شود
    $(document).on('click', '.wc-pdf-catalog-btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var $wrapper = $btn.closest('.wc-pdf-catalog-wrapper');
        var $container = $wrapper.find('.wc-pdf-catalog-container');
        var category = $btn.data('category') || '';
        var nonce = $btn.data('nonce') || WCPDFCatalog.nonce_form;

        // اگر فرم قبلاً ساخته شده، فقط نمایشش بده
        if ( $container.find('.wc-pdf-catalog-form').length ) {
            $container.find('.wc-pdf-catalog-form').show();
            return;
        }

        var formHtml = buildInlineForm( nonce, category );
        $container.html( formHtml );
    });

    // submit فرم: ابتدا اعتبارسنجی، سپس ثبت درخواست و تولید PDF در سرور
    $(document).on('submit', '.wc-pdf-catalog-form', function(e){
        e.preventDefault();
        var $form = $(this);
        var $resp = $form.find('.wc-pdf-response');
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

        var errors = validateFormData(formData);
        if ( Object.keys(errors).length ) {
            var html = '';
            for (var k in errors) {
                if (errors.hasOwnProperty(k)) {
                    html += '<div>' + errors[k] + '</div>';
                }
            }
            showResponse($resp, html, true);
            return;
        }

        $form.find('.wc-pdf-submit').prop('disabled', true).text('در حال آماده‌سازی فایل PDF...');
        $resp.removeClass('wc-pdf-error wc-pdf-success').text('');

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
                    showResponse($resp, buildDownloadBlock(res.download_url), false);
                    $form.find('.wc-pdf-actions, .wc-pdf-field').hide();
                } else {
                    var msg = 'خطا در ارسال فرم.';
                    if ( res && res.errors ) {
                        msg = '';
                        $.each(res.errors, function(field, text){
                            msg += '<div>' + text + '</div>';
                        });
                    } else if ( res && res.message ) {
                        msg = res.message;
                    }
                    showResponse($resp, msg, true);
                }
            })
            .fail(function(xhr){
                var msg = 'خطا در ارتباط با سرور.';
                if (xhr && xhr.responseJSON && xhr.responseJSON.errors) {
                    msg = '';
                    $.each(xhr.responseJSON.errors, function(field, text){
                        msg += '<div>' + text + '</div>';
                    });
                }
                showResponse($resp, msg, true);
            })
            .always(function(){
                $form.find('.wc-pdf-submit').prop('disabled', false).text('ارسال و دریافت کاتالوگ PDF');
            });
    });

    // کپی لینک دانلود در کلیپ‌بورد
    $(document).on('click', '.wc-pdf-share.copy-link', function(e){
        e.preventDefault();
        var $btn = $(this);
        var url = $btn.data('url');

        function done() {
            var original = $btn.data('original-text') || $btn.text();
            $btn.data('original-text', original);
            $btn.text('کپی شد!');
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
