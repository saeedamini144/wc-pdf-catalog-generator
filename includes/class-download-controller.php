<?php
namespace WC_PDF_Catalog;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Download_Controller
 *
 * مدیریت پاکسازی فایل‌های PDF قدیمی تولیدشده در uploads/wc-pdf-catalog
 *
 * توجه: تولید PDF همیشه باید پس از تکمیل فرم (Form_Ajax) انجام شود؛
 * این کلاس عمداً هیچ مسیر AJAX/REST برای تولید مستقیم PDF بدون فرم ارائه نمی‌دهد.
 */
class Download_Controller {

    /**
     * فایل‌های قدیمی‌تر از این مدت (به ثانیه) در پاکسازی حذف می‌شوند
     */
    const MAX_FILE_AGE = 7 * DAY_IN_SECONDS;

    const CRON_HOOK = 'wc_pdf_catalog_cleanup_event';

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
        add_action( self::CRON_HOOK, [ $this, 'cleanup_files' ] );
    }

    public function register_routes() {
        register_rest_route( 'wc-pdf-catalog/v1', '/cleanup', [
            'methods'  => 'POST',
            'callback' => [ $this, 'cleanup_files' ],
            'permission_callback' => function() {
                return current_user_can( 'manage_woocommerce' );
            },
        ] );
    }

    /**
     * پاکسازی فایل‌های PDF قدیمی‌تر از MAX_FILE_AGE
     * هم از طریق REST (توسط ادمین) و هم به‌صورت خودکار روزانه (wp-cron) فراخوانی می‌شود
     *
     * @return \WP_REST_Response|array
     */
    public function cleanup_files() {
        $upload_dir = wp_upload_dir();
        $dir = trailingslashit( $upload_dir['basedir'] ) . 'wc-pdf-catalog/';

        $response = [ 'status' => true, 'deleted' => 0 ];

        if ( is_dir( $dir ) ) {
            $files = glob( $dir . '*.pdf' );
            $now = time();
            foreach ( (array) $files as $file ) {
                if ( is_file( $file ) && ( $now - filemtime( $file ) ) > self::MAX_FILE_AGE ) {
                    if ( @unlink( $file ) ) {
                        $response['deleted']++;
                    }
                }
            }
        }

        if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
            return new \WP_REST_Response( $response, 200 );
        }

        return $response;
    }

    /**
     * فعال‌سازی cron روزانه پاکسازی (فراخوانی از activation hook)
     */
    public static function schedule_cleanup() {
        if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
            wp_schedule_event( time() + DAY_IN_SECONDS, 'daily', self::CRON_HOOK );
        }
    }

    /**
     * غیرفعال‌سازی cron (فراخوانی از deactivation hook)
     */
    public static function unschedule_cleanup() {
        $timestamp = wp_next_scheduled( self::CRON_HOOK );
        if ( $timestamp ) {
            wp_unschedule_event( $timestamp, self::CRON_HOOK );
        }
    }
}
