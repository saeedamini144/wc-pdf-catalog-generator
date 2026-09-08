<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// حذف optionها
delete_option( 'wc_pdf_catalog_options' );

// پاک کردن رویداد کران‌ زمان‌بندی‌شده پاکسازی فایل‌ها
$timestamp = wp_next_scheduled( 'wc_pdf_catalog_cleanup_event' );
if ( $timestamp ) {
    wp_unschedule_event( $timestamp, 'wc_pdf_catalog_cleanup_event' );
}

// حذف جدول و فایل‌های تولید شده
global $wpdb;
$table = $wpdb->prefix . 'wc_pdf_catalog_requests';
$wpdb->query( "DROP TABLE IF EXISTS {$table}" );

// حذف فایل‌های تولید شده در uploads/wc-pdf-catalog
$upload_dir = wp_upload_dir();
$dir = trailingslashit( $upload_dir['basedir'] ) . 'wc-pdf-catalog/';
if ( is_dir( $dir ) ) {
    $files = glob( $dir . '*.pdf' );
    if ( $files ) {
        foreach ( $files as $file ) {
            @unlink( $file );
        }
    }
    @rmdir( $dir );
}
