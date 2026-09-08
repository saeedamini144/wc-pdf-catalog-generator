<?php
namespace WC_PDF_Catalog;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Shortcode_Handler
 *
 * رجیستر شورت‌کد [wc_pdf_catalog] که یک دکمه تولید کاتالوگ نمایش می‌دهد.
 * ویژگی‌ها:
 * - پشتیبانی از attribute های: category (slug) و label
 * - تولید nonce امن برای هر دکمه
 * - خروجی قالب در templates/frontend/shortcode-button.php
 */
class Shortcode_Handler {

    public function __construct() {
        // رجیستر شورت‌کد
        add_shortcode( 'wc_pdf_catalog', [ $this, 'render_shortcode' ] );

        // اگر نیاز به REST route یا AJAX خاصی باشد، می‌توان اینجا اضافه کرد
    }

    /**
     * خروجی شورت‌کد
     *
     * @param array $atts
     * @return string HTML
     */
    public function render_shortcode( $atts ) {
        $atts = shortcode_atts( [
            'category' => '', // slug دسته محصول (اختیاری - در صورت خالی بودن و قرارگیری در آرشیو دسته، به‌صورت خودکار تشخیص داده می‌شود)
            'label'    => __( 'Generate PDF Catalog', 'wc-pdf-catalog' ),
        ], $atts, 'wc_pdf_catalog' );

        // تولید nonceهای مورد نیاز فرم (تولید کاتالوگ همیشه پس از تکمیل فرم انجام می‌شود)
        $nonce = wp_create_nonce( 'wc_pdf_catalog_form_nonce' );

        // آماده‌سازی متغیرها برای template
        $label = sanitize_text_field( $atts['label'] );
        $category = sanitize_title( $atts['category'] );

        // اگر دسته صراحتاً مشخص نشده، در صفحه آرشیو دسته محصول به‌صورت خودکار تشخیص بده
        if ( '' === $category ) {
            $category = wc_pdf_catalog_detect_current_category();
        }

        ob_start();
        $template = WC_PDF_CATALOG_PLUGIN_DIR . 'templates/frontend/shortcode-button.php';
        if ( file_exists( $template ) ) {
            include $template;
        } else {
            // fallback ساده
            ?>
            <div class="wc-pdf-catalog-wrapper">
                <button type="button" class="wc-pdf-catalog-btn" data-category="<?php echo esc_attr( $category ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>">
                    <?php echo esc_html( $label ); ?>
                </button>
                <div class="wc-pdf-catalog-container"></div>
            </div>
            <?php
        }
        return ob_get_clean();
    }
}

// نمونه‌سازی در Loader انجام می‌شود
