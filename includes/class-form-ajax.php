<?php
namespace WC_PDF_Catalog;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Form_Ajax
 *
 * هندل ارسال فرم از طریق AJAX: validate -> insert DB -> generate PDF -> return URL
 */
class Form_Ajax {

    public function __construct() {
        add_action( 'wp_ajax_wc_pdf_catalog_submit_form', [ $this, 'handle' ] );
        add_action( 'wp_ajax_nopriv_wc_pdf_catalog_submit_form', [ $this, 'handle' ] );
    }

    public function handle() {
        // nonce
        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
        if ( ! wp_verify_nonce( $nonce, 'wc_pdf_catalog_form_nonce' ) ) {
            wp_send_json( [ 'status' => false, 'errors' => [ 'general' => __( 'Security check failed.', 'wc-pdf-catalog' ) ] ], 403 );
        }

        if ( ! class_exists( 'WooCommerce' ) ) {
            wp_send_json( [ 'status' => false, 'errors' => [ 'general' => __( 'WooCommerce is not active.', 'wc-pdf-catalog' ) ] ], 500 );
        }

        if ( ! class_exists( '\\Dompdf\\Dompdf' ) ) {
            wp_send_json( [ 'status' => false, 'errors' => [ 'general' => __( 'PDF engine is not available. Please run composer install.', 'wc-pdf-catalog' ) ] ], 500 );
        }

        // جمع‌آوری داده‌ها (wp_unslash برای جلوگیری از escaping دوگانه)
        $data = [
            'first_name'  => isset( $_POST['first_name'] ) ? wp_unslash( $_POST['first_name'] ) : '',
            'last_name'   => isset( $_POST['last_name'] ) ? wp_unslash( $_POST['last_name'] ) : '',
            'email'       => isset( $_POST['email'] ) ? wp_unslash( $_POST['email'] ) : '',
            'country'     => isset( $_POST['country'] ) ? wp_unslash( $_POST['country'] ) : '',
            'company'     => isset( $_POST['company'] ) ? wp_unslash( $_POST['company'] ) : '',
            'phone'       => isset( $_POST['phone'] ) ? wp_unslash( $_POST['phone'] ) : '',
            'telegram_id' => isset( $_POST['telegram_id'] ) ? wp_unslash( $_POST['telegram_id'] ) : '',
            'whatsapp'    => isset( $_POST['whatsapp'] ) ? wp_unslash( $_POST['whatsapp'] ) : '',
        ];

        // اعتبارسنجی و پاک‌سازی
        $result = Form_Validator::validate( $data );
        if ( ! $result['valid'] ) {
            wp_send_json( [ 'status' => false, 'errors' => $result['errors'] ], 422 );
        }
        $clean = $result['clean'];

        // ذخیره در DB
        $request_id = DB_Manager::insert_request( $clean );
        if ( ! $request_id ) {
            wp_send_json( [ 'status' => false, 'errors' => [ 'general' => __( 'Failed to save request.', 'wc-pdf-catalog' ) ] ], 500 );
        }

        // category اختیاری (اگر فرم یا شورت‌کد آن را ارسال کند) - به‌صورت اسلاگ تاکسونومی پاک‌سازی می‌شود
        $category = isset( $_POST['category'] ) ? sanitize_title( wp_unslash( $_POST['category'] ) ) : '';

        // گرفتن محصولات
        $wc = new WooCommerce_Data();
        $args = [];
        if ( $category ) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'product_cat',
                    'field'    => 'slug',
                    'terms'    => $category,
                ],
            ];
        }
        $products = $wc->get_products( $args );

        if ( empty( $products ) ) {
            wp_send_json( [ 'status' => false, 'errors' => [ 'general' => __( 'No products found for catalog.', 'wc-pdf-catalog' ) ] ], 404 );
        }

        // تولید PDF
        $pdf = new PDF_Generator();
        $pdf_result = $pdf->generate( $products );

        if ( empty( $pdf_result['status'] ) ) {
            wp_send_json( [ 'status' => false, 'errors' => [ 'general' => __( 'Failed to generate PDF.', 'wc-pdf-catalog' ) ] ], 500 );
        }

        // علامت‌گذاری دانلود (ثبت زمان آماده شدن/دانلود)
        DB_Manager::mark_downloaded( $request_id );

        // پاسخ موفق
        wp_send_json( [
            'status' => true,
            'download_url' => esc_url_raw( $pdf_result['url'] ),
            'message' => __( 'Catalog is ready. Click to download.', 'wc-pdf-catalog' ),
        ], 200 );
    }
}
