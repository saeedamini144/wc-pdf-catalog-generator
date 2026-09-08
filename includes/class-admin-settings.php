<?php
namespace WC_PDF_Catalog;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin_Settings
 *
 * مدیریت صفحه تنظیمات پلاگین:
 * - ثبت optionها
 * - صفحه تنظیمات در منوی ادمین
 * - آپلود تصویر قالب PDF
 */
class Admin_Settings {

    /**
     * نام option که تنظیمات را نگه می‌دارد
     */
    const OPTION_KEY = 'wc_pdf_catalog_options';

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_menu' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    /**
     * اضافه کردن منو اصلی پلاگین
     */
    public function add_menu() {
        add_menu_page(
            __( 'PDF Catalog', 'wc-pdf-catalog' ),   // page title
            __( 'PDF Catalog', 'wc-pdf-catalog' ),   // menu title
            'manage_woocommerce',                    // capability
            'wc-pdf-catalog',                        // menu slug
            [ $this, 'render_page' ],                // callback
            'dashicons-media-document',              // icon
            56                                       // position
        );
    }

    /**
     * رجیستر تنظیمات با sanitize callback
     */
    public function register_settings() {
        register_setting( 'wc_pdf_catalog_group', self::OPTION_KEY, [
            'sanitize_callback' => [ $this, 'sanitize_options' ],
            'default' => [
                'pdf_template_id' => '',
                'show_price' => 'yes',
                'show_attributes' => 'yes',
            ],
        ] );
    }

    /**
     * sanitize و validate گزینه‌ها قبل از ذخیره
     *
     * @param array $input
     * @return array
     */
    public function sanitize_options( $input ) {
        $output = [];

        $output['pdf_template_id'] = isset( $input['pdf_template_id'] ) ? intval( $input['pdf_template_id'] ) : 0;
        $output['show_price'] = ( isset( $input['show_price'] ) && $input['show_price'] === 'no' ) ? 'no' : 'yes';
        $output['show_attributes'] = ( isset( $input['show_attributes'] ) && $input['show_attributes'] === 'no' ) ? 'no' : 'yes';

        // در آینده می‌توان گزینه‌های بیشتری اضافه کرد

        return $output;
    }

    /**
     * رندر صفحه تنظیمات (callback منو)
     */
    public function render_page() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( __( 'Unauthorized', 'wc-pdf-catalog' ) );
        }

        $opts = get_option( self::OPTION_KEY, [] );

        // مسیر template
        $template = WC_PDF_CATALOG_PLUGIN_DIR . 'templates/admin-settings-page.php';
        if ( file_exists( $template ) ) {
            include $template;
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'Settings template not found.', 'wc-pdf-catalog' ) . '</p></div>';
        }
    }
}
