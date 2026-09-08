<?php
namespace WC_PDF_Catalog;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Assets_Loader {

    public static function init() {
        add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_frontend' ] );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_admin' ] );
    }

    public static function enqueue_frontend() {
        // استایل فرم
        $css = WC_PDF_CATALOG_PLUGIN_URL . 'assets/css/form.css';
        if ( file_exists( WC_PDF_CATALOG_PLUGIN_DIR . 'assets/css/form.css' ) ) {
            wp_enqueue_style( 'wc-pdf-catalog-form', $css, [], WC_PDF_CATALOG_VERSION );
        }

        // اسکریپت یکپارچه: wc-pdf-catalog.js
        $js = WC_PDF_CATALOG_PLUGIN_URL . 'assets/js/wc-pdf-catalog.js';
        if ( file_exists( WC_PDF_CATALOG_PLUGIN_DIR . 'assets/js/wc-pdf-catalog.js' ) ) {
            wp_enqueue_script( 'wc-pdf-catalog', $js, [ 'jquery' ], WC_PDF_CATALOG_VERSION, true );

            // localize: یک آبجکت واحد برای همهٔ عملیات
            wp_localize_script( 'wc-pdf-catalog', 'WCPDFCatalog', [
                'ajax_url'   => esc_url_raw( admin_url( 'admin-ajax.php' ) ),
                'nonce_form' => wp_create_nonce( 'wc_pdf_catalog_form_nonce' ),
            ] );
        }
    }

    public static function enqueue_admin( $hook ) {
        $allowed_hooks = [
            'toplevel_page_wc-pdf-catalog',
            'wc-pdf-catalog_page_wc-pdf-catalog-requests',
        ];
        if ( ! in_array( $hook, $allowed_hooks, true ) ) {
            return;
        }

        $admin_css = WC_PDF_CATALOG_PLUGIN_URL . 'assets/css/admin.css';
        if ( file_exists( WC_PDF_CATALOG_PLUGIN_DIR . 'assets/css/admin.css' ) ) {
            wp_enqueue_style( 'wc-pdf-catalog-admin', $admin_css, [], WC_PDF_CATALOG_VERSION );
        }

        // انتخابگر رسانه (wp.media) فقط در صفحه تنظیمات لازم است، نه در صفحه لیست درخواست‌ها
        if ( 'toplevel_page_wc-pdf-catalog' === $hook ) {
            wp_enqueue_media();

            $admin_js = WC_PDF_CATALOG_PLUGIN_URL . 'assets/js/admin.js';
            if ( file_exists( WC_PDF_CATALOG_PLUGIN_DIR . 'assets/js/admin.js' ) ) {
                wp_enqueue_script( 'wc-pdf-catalog-admin', $admin_js, [ 'jquery' ], WC_PDF_CATALOG_VERSION, true );
                wp_localize_script( 'wc-pdf-catalog-admin', 'WCPDFAdmin', [
                    'media_title' => __( 'Select PDF template image', 'wc-pdf-catalog' ),
                    'nonce'       => wp_create_nonce( 'wc_pdf_catalog_admin_nonce' ),
                ] );
            }
        }
    }
}
