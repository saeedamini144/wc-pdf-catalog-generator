<?php
namespace WC_PDF_Catalog;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * PDF_Template_Manager
 *
 * Manage PDF template image (background/header) and related settings
 */
class PDF_Template_Manager {

    public function __construct() {
        // nothing for now
    }

    /**
     * Return attachment URL of template image stored in options
     *
     * @return string
     */
    public function get_template_image_url() {
        $id = Options_Helper::get_template_id();
        if ( $id ) {
            // از فایل اصلی آپلود شده استفاده می‌کنیم تا کیفیت تصویر پس‌زمینه در PDF افت نکند
            $url = wp_get_attachment_url( $id );
            return $url ? esc_url_raw( $url ) : '';
        }
        return '';
    }

    /**
     * آیا قالب تصویری برای پس‌زمینه صفحات PDF تنظیم شده است؟
     *
     * @return bool
     */
    public function has_template() {
        return '' !== $this->get_template_image_url();
    }

    /**
     * Dompdf از background روی <body> برای تمام صفحات پشتیبانی نمی‌کند مگر با
     * یک المان با position:fixed که در ابتدای بدنه سند تکرار می‌شود.
     * این متد یک تگ <div> برای استفاده به‌عنوان پس‌زمینه تمام‌صفحه (کاور از پیش طراحی‌شده) برمی‌گرداند
     * که محتوای کاتالوگ (محصولات) روی آن قرار می‌گیرد.
     *
     * @return string HTML
     */
    public function get_full_page_background_html() {
        $url = $this->get_template_image_url();
        if ( ! $url ) {
            return '';
        }
        return '<div class="wc-pdf-page-bg" style="background-image: url(\'' . esc_url( $url ) . '\');"></div>';
    }
}
