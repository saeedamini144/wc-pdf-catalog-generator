<?php
/**
 * Plugin Name: WooCommerce PDF Catalog Generator
 * Plugin URI:  saaed.amini@gmail.com
 * Description: Generate WooCommerce product PDF catalogs with a custom lead form and admin listing.
 * Version:     1.1.0
 * Author:      Saeed Amini
 * Author URI:  saaed.amini@gmail.com
 * Text Domain: wc-pdf-catalog
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // جلوگیری از دسترسی مستقیم
}

/**
 * تعریف کانستنت‌ها
 */
define( 'WC_PDF_CATALOG_VERSION', '1.1.0' );
define( 'WC_PDF_CATALOG_PLUGIN_FILE', __FILE__ );
define( 'WC_PDF_CATALOG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WC_PDF_CATALOG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * لود Composer autoload (برای Dompdf و PSR-4)
 */
$autoload = WC_PDF_CATALOG_PLUGIN_DIR . 'vendor/autoload.php';
$dependencies_error = '';
if ( file_exists( $autoload ) ) {
    try {
        require_once $autoload;
    } catch ( \Throwable $exception ) {
        $dependencies_error = $exception->getMessage();
        error_log( '[WC PDF Catalog] Composer dependencies could not be loaded: ' . $dependencies_error );
    }
} else {
    $dependencies_error = 'Composer autoload not found. Run composer install.';
    error_log( '[WC PDF Catalog] ' . $dependencies_error );
}

if ( $dependencies_error ) {
    add_action( 'admin_notices', function() use ( $dependencies_error ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        printf(
            '<div class="notice notice-error"><p><strong>%s</strong> %s</p></div>',
            esc_html__( 'WooCommerce PDF Catalog Generator is inactive:', 'wc-pdf-catalog' ),
            esc_html( $dependencies_error )
        );
    } );
}

/**
 * لود Loader اصلی پلاگین
 */
require_once WC_PDF_CATALOG_PLUGIN_DIR . 'includes/class-plugin-loader.php';
require_once WC_PDF_CATALOG_PLUGIN_DIR . 'includes/class-db-manager.php';
require_once WC_PDF_CATALOG_PLUGIN_DIR . 'includes/class-download-controller.php';

/**
 * بررسی فعال بودن ووکامرس؛ بدون آن پلاگین کاربردی ندارد
 */
add_action( 'admin_notices', function() {
    if ( ! current_user_can( 'activate_plugins' ) ) {
        return;
    }
    if ( ! class_exists( 'WooCommerce' ) ) {
        printf(
            '<div class="notice notice-error"><p><strong>%s</strong> %s</p></div>',
            esc_html__( 'WooCommerce PDF Catalog Generator requires WooCommerce:', 'wc-pdf-catalog' ),
            esc_html__( 'please install and activate WooCommerce.', 'wc-pdf-catalog' )
        );
    }
} );

/**
 * راه‌اندازی پلاگین بعد از لود شدن سایر پلاگین‌ها
 */
add_action( 'plugins_loaded', function() {
    $loader = new \WC_PDF_Catalog\Plugin_Loader();
    $loader->init();
} );

/**
 * اکتیویشن: ساخت جدول دیتابیس، تنظیمات اولیه و زمان‌بندی پاکسازی خودکار فایل‌ها
 */
register_activation_hook( __FILE__, function() {
    // ساخت جدول دیتابیس فرم
    \WC_PDF_Catalog\DB_Manager::create_table();

    // تنظیمات اولیه پلاگین
    if ( ! get_option( 'wc_pdf_catalog_options' ) ) {
        add_option( 'wc_pdf_catalog_options', [
            'pdf_template_id'   => '',
            'show_price'        => 'yes',
            'show_attributes'   => 'yes',
        ] );
    }

    // زمان‌بندی رویداد روزانه برای پاکسازی فایل‌های PDF قدیمی
    \WC_PDF_Catalog\Download_Controller::schedule_cleanup();
} );

/**
 * دی‌اکتیویشن: پاک کردن رویداد کران زمان‌بندی‌شده
 */
register_deactivation_hook( __FILE__, function() {
    \WC_PDF_Catalog\Download_Controller::unschedule_cleanup();
} );
