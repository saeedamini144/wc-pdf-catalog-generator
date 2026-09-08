<?php
namespace WC_PDF_Catalog;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * وقتی شورت‌کد بدون آتریبیوت category در یک صفحه آرشیو دسته‌بندی محصول ووکامرس
 * (shop، product_cat یا برگه‌ای که با category خاصی فیلتر شده) قرار می‌گیرد،
 * این تابع اسلاگ دسته فعلی را برمی‌گرداند تا کاتالوگ فقط شامل همان دسته باشد.
 *
 * @return string اسلاگ دسته یا رشته خالی
 */
function wc_pdf_catalog_detect_current_category() {
    if ( ! is_admin() && function_exists( 'is_product_category' ) && is_product_category() ) {
        $term = get_queried_object();
        if ( $term instanceof \WP_Term ) {
            return $term->slug;
        }
    }
    return '';
}

/**
 * قالب‌بندی یکسان تاریخ/زمان برای نمایش در ادمین بر اساس تنظیمات سایت
 *
 * @param string $mysql_datetime
 * @return string
 */
function wc_pdf_catalog_format_datetime( $mysql_datetime ) {
    if ( empty( $mysql_datetime ) || $mysql_datetime === '0000-00-00 00:00:00' ) {
        return '—';
    }
    $timestamp = strtotime( $mysql_datetime );
    if ( ! $timestamp ) {
        return esc_html( $mysql_datetime );
    }
    return date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp );
}
