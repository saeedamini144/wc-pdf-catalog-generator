<?php
namespace WC_PDF_Catalog;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * اعتبارسنجی و پاک‌سازی داده‌های فرم کاتالوگ
 */
class Form_Validator {

    /**
     * اعتبارسنجی داده‌ها
     *
     * @param array $data داده خام از $_POST یا JSON
     * @return array [ 'valid' => bool, 'clean' => array, 'errors' => array ]
     */
    public static function validate( array $data ) {
        $errors = [];
        $clean  = [];

        // نام
        $clean['first_name'] = isset( $data['first_name'] ) ? sanitize_text_field( $data['first_name'] ) : '';
        if ( $clean['first_name'] === '' ) {
            $errors['first_name'] = __( 'First name is required.', 'wc-pdf-catalog' );
        }

        // نام خانوادگی
        $clean['last_name'] = isset( $data['last_name'] ) ? sanitize_text_field( $data['last_name'] ) : '';
        if ( $clean['last_name'] === '' ) {
            $errors['last_name'] = __( 'Last name is required.', 'wc-pdf-catalog' );
        }

        // ایمیل
        $clean['email'] = isset( $data['email'] ) ? sanitize_email( $data['email'] ) : '';
        if ( $clean['email'] === '' || ! is_email( $clean['email'] ) ) {
            $errors['email'] = __( 'Valid email is required.', 'wc-pdf-catalog' );
        }

        // کشور (اختیاری)
        $clean['country'] = isset( $data['country'] ) ? sanitize_text_field( $data['country'] ) : '';

        // نام شرکت (اختیاری)
        $clean['company'] = isset( $data['company'] ) ? sanitize_text_field( $data['company'] ) : '';

        // شماره تلفن (اجباری)
        $clean['phone'] = isset( $data['phone'] ) ? sanitize_text_field( $data['phone'] ) : '';
        if ( $clean['phone'] === '' ) {
            $errors['phone'] = __( 'Phone number is required.', 'wc-pdf-catalog' );
        }

        // آیدی تلگرام (اختیاری)
        $clean['telegram_id'] = isset( $data['telegram_id'] ) ? sanitize_text_field( $data['telegram_id'] ) : '';

        // واتس‌اپ (اختیاری)
        $clean['whatsapp'] = isset( $data['whatsapp'] ) ? sanitize_text_field( $data['whatsapp'] ) : '';

        // IP و User Agent برای لاگ (از سرور می‌گیریم، نه از کاربر)
        $clean['ip_address'] = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : '';
        $clean['user_agent'] = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_textarea_field( $_SERVER['HTTP_USER_AGENT'] ) : '';

        return [
            'valid'  => empty( $errors ),
            'clean'  => $clean,
            'errors' => $errors,
        ];
    }
}
