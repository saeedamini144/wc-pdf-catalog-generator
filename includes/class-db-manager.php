<?php
namespace WC_PDF_Catalog;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * مدیریت جدول دیتابیس برای ذخیره درخواست‌های کاتالوگ
 */
class DB_Manager {

    /**
     * گرفتن نام جدول با توجه به prefix وردپرس
     */
    public static function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . 'wc_pdf_catalog_requests';
    }

    /**
     * ساخت جدول در هنگام فعال‌سازی پلاگین
     */
    public static function create_table() {
        global $wpdb;

        $table_name = self::get_table_name();
        $charset_collate = $wpdb->get_charset_collate();

        // ساختار جدول:
        // - id: کلید اصلی
        // - created_at: زمان ثبت فرم
        // - downloaded_at: زمان دانلود PDF (nullable)
        // - فیلدهای فرم: نام، نام خانوادگی، ایمیل، کشور، شرکت، تلفن، تلگرام، واتس‌اپ
        // - ip_address: آی‌پی کاربر
        // - user_agent: مرورگر کاربر
        $sql = "CREATE TABLE {$table_name} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            created_at DATETIME NOT NULL,
            downloaded_at DATETIME NULL,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            email VARCHAR(190) NOT NULL,
            country VARCHAR(100) NULL,
            company VARCHAR(190) NULL,
            phone VARCHAR(50) NOT NULL,
            telegram_id VARCHAR(100) NULL,
            whatsapp VARCHAR(100) NULL,
            ip_address VARCHAR(45) NULL,
            user_agent TEXT NULL,
            PRIMARY KEY  (id),
            KEY email (email),
            KEY created_at (created_at)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    /**
     * درج یک رکورد جدید (ارسال فرم)
     *
     * @param array $data آرایه شامل فیلدهای فرم
     * @return int|false ID رکورد درج‌شده یا false در صورت خطا
     */
    public static function insert_request( array $data ) {
        global $wpdb;

        $table = self::get_table_name();

        // زمان فعلی
        $now = current_time( 'mysql' );

        // آماده‌سازی داده‌ها (همه باید قبلاً sanitize شده باشند)
        $insert_data = [
            'created_at'   => $now,
            'downloaded_at'=> null,
            'first_name'   => $data['first_name'] ?? '',
            'last_name'    => $data['last_name'] ?? '',
            'email'        => $data['email'] ?? '',
            'country'      => $data['country'] ?? '',
            'company'      => $data['company'] ?? '',
            'phone'        => $data['phone'] ?? '',
            'telegram_id'  => $data['telegram_id'] ?? '',
            'whatsapp'     => $data['whatsapp'] ?? '',
            'ip_address'   => $data['ip_address'] ?? '',
            'user_agent'   => $data['user_agent'] ?? '',
        ];

        // فرمت داده‌ها برای wpdb
        $formats = [
            '%s', // created_at
            '%s', // downloaded_at (nullable، ولی dbDelta اجازه می‌دهد)
            '%s', // first_name
            '%s', // last_name
            '%s', // email
            '%s', // country
            '%s', // company
            '%s', // phone
            '%s', // telegram_id
            '%s', // whatsapp
            '%s', // ip_address
            '%s', // user_agent
        ];

        $result = $wpdb->insert( $table, $insert_data, $formats );

        if ( $result === false ) {
            // در صورت خطا، لاگ می‌کنیم
            error_log( '[WC PDF Catalog] Failed to insert request: ' . $wpdb->last_error );
            return false;
        }

        return (int) $wpdb->insert_id;
    }

    /**
     * علامت‌گذاری رکورد به عنوان دانلود شده (ثبت زمان دانلود)
     *
     * @param int $id ID رکورد
     * @return bool
     */
    public static function mark_downloaded( $id ) {
        global $wpdb;

        $table = self::get_table_name();
        $id = (int) $id;
        if ( $id <= 0 ) {
            return false;
        }

        $now = current_time( 'mysql' );

        $result = $wpdb->update(
            $table,
            [ 'downloaded_at' => $now ],
            [ 'id' => $id ],
            [ '%s' ],
            [ '%d' ]
        );

        if ( $result === false ) {
            error_log( '[WC PDF Catalog] Failed to mark downloaded: ' . $wpdb->last_error );
            return false;
        }

        return true;
    }

    /**
     * گرفتن لیست درخواست‌ها برای نمایش در پنل ادمین با پیجینیشن
     *
     * @param int $paged شماره صفحه (۱، ۲، ...)
     * @param int $per_page تعداد رکورد در هر صفحه
     * @return array آرایه‌ای از آبجکت‌ها
     */
    public static function get_requests( $paged = 1, $per_page = 50 ) {
        global $wpdb;

        $table = self::get_table_name();

        $paged = max( 1, (int) $paged );
        $per_page = max( 1, (int) $per_page );

        $offset = ( $paged - 1 ) * $per_page;

        // استفاده از prepare برای جلوگیری از SQL Injection
        $sql = $wpdb->prepare(
            "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $per_page,
            $offset
        );

        $results = $wpdb->get_results( $sql );

        if ( $results === null ) {
            error_log( '[WC PDF Catalog] Failed to get requests: ' . $wpdb->last_error );
            return [];
        }

        return $results;
    }

    /**
     * گرفتن تعداد کل رکوردها برای پیجینیشن
     *
     * @return int
     */
    public static function get_requests_count() {
        global $wpdb;

        $table = self::get_table_name();

        $sql = "SELECT COUNT(*) FROM {$table}";
        $count = (int) $wpdb->get_var( $sql );

        if ( $count < 0 ) {
            $count = 0;
        }

        return $count;
    }
}
