<?php
namespace WC_PDF_Catalog;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin_Help
 *
 * صفحه راهنما در پنل ادمین: توضیح عملکرد پلاگین، شورت‌کدها و روند کار برای بازدیدکننده
 */
class Admin_Help {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
    }

    public function add_admin_menu() {
        add_submenu_page(
            'wc-pdf-catalog',
            __( 'Help & Shortcodes', 'wc-pdf-catalog' ),
            __( 'Help & Shortcodes', 'wc-pdf-catalog' ),
            'manage_woocommerce',
            'wc-pdf-catalog-help',
            [ $this, 'render_page' ]
        );
    }

    public function render_page() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( __( 'Unauthorized', 'wc-pdf-catalog' ) );
        }

        $template = WC_PDF_CATALOG_PLUGIN_DIR . 'templates/admin-help-page.php';
        if ( file_exists( $template ) ) {
            include $template;
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'Help template not found.', 'wc-pdf-catalog' ) . '</p></div>';
        }
    }
}
