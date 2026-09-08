<?php
namespace WC_PDF_Catalog;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin_Requests_List
 *
 * صفحه ادمین برای نمایش لیست ارسال‌های فرم کاتالوگ با پیجینیشن.
 */
class Admin_Requests_List {

    /**
     * تعداد رکورد در هر صفحه
     */
    const PER_PAGE = 50;

    public function __construct() {
        // اضافه کردن منو زیر منوی پلاگین (یا صفحه جدا)
        add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );

        // پردازش عملیات حذف (POST) با nonce
        add_action( 'admin_post_wc_pdf_catalog_delete_request', [ $this, 'handle_delete_request' ] );
    }

    /**
     * اضافه کردن صفحه منو در ادمین
     */
    public function add_admin_menu() {
        add_submenu_page(
            'wc-pdf-catalog',                          // parent slug (از Admin_Settings استفاده می‌کند)
            __( 'Requests', 'wc-pdf-catalog' ),        // page title
            __( 'Requests', 'wc-pdf-catalog' ),        // menu title
            'manage_woocommerce',                      // capability
            'wc-pdf-catalog-requests',                 // menu slug
            [ $this, 'render_admin_page' ]             // callback
        );
    }

    /**
     * رندر صفحه ادمین
     */
    public function render_admin_page() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( __( 'Unauthorized', 'wc-pdf-catalog' ) );
        }

        // شماره صفحه از GET
        $paged = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
        $per_page = self::PER_PAGE;

        // گرفتن داده‌ها از DB_Manager
        $requests = DB_Manager::get_requests( $paged, $per_page );
        $total    = DB_Manager::get_requests_count();

        // محاسبه صفحات
        $total_pages = (int) ceil( $total / $per_page );

        // nonce برای عملیات حذف
        $delete_nonce = wp_create_nonce( 'wc_pdf_catalog_delete_request' );

        // مسیر صفحه فعلی برای لینک‌ها
        $base_url = admin_url( 'admin.php?page=wc-pdf-catalog-requests' );

        // بارگذاری template
        $template = WC_PDF_CATALOG_PLUGIN_DIR . 'templates/admin-requests-list.php';
        if ( file_exists( $template ) ) {
            include $template;
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'Requests template not found.', 'wc-pdf-catalog' ) . '</p></div>';
        }
    }

    /**
     * هندل حذف یک رکورد (admin_post)
     * درخواست باید POST باشد و دارای capability و nonce معتبر
     */
    public function handle_delete_request() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( __( 'Unauthorized', 'wc-pdf-catalog' ) );
        }

        // بررسی nonce
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'wc_pdf_catalog_delete_request' ) ) {
            wp_die( __( 'Security check failed.', 'wc-pdf-catalog' ) );
        }

        $id = isset( $_POST['request_id'] ) ? absint( $_POST['request_id'] ) : 0;
        if ( $id <= 0 ) {
            wp_safe_redirect( admin_url( 'admin.php?page=wc-pdf-catalog-requests&deleted=0' ) );
            exit;
        }

        global $wpdb;
        $table = DB_Manager::get_table_name();

        // حذف امن با prepare
        $deleted = $wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE id = %d", $id ) );

        if ( $deleted === false ) {
            error_log( '[WC PDF Catalog] Failed to delete request: ' . $wpdb->last_error );
            wp_safe_redirect( admin_url( 'admin.php?page=wc-pdf-catalog-requests&deleted=0' ) );
            exit;
        }

        wp_safe_redirect( admin_url( 'admin.php?page=wc-pdf-catalog-requests&deleted=1' ) );
        exit;
    }
}
