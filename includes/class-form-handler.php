<?php
namespace WC_PDF_Catalog;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * هندلر فرم اختصاصی در فرانت‌اند
 */
class Form_Handler {

    public function __construct() {
        // می‌توانیم شورت‌کد جدا برای فرم داشته باشیم، یا از شورت‌کد اصلی استفاده کنیم
        add_shortcode( 'wc_pdf_catalog_form', [ $this, 'render_form_shortcode' ] );
    }

    /**
     * خروجی شورت‌کد فرم
     */
    public function render_form_shortcode( $atts ) {
        $atts = shortcode_atts( [], $atts, 'wc_pdf_catalog_form' );

        ob_start();
        $template = WC_PDF_CATALOG_PLUGIN_DIR . 'templates/frontend/form.php';
        if ( file_exists( $template ) ) {
            // nonce برای فرم
            $nonce = wp_create_nonce( 'wc_pdf_catalog_form_nonce' );
            include $template;
        } else {
            echo esc_html__( 'Form template not found.', 'wc-pdf-catalog' );
        }
        return ob_get_clean();
    }
}
