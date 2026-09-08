<?php
namespace WC_PDF_Catalog;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Options_Helper
 *
 * دسترسی متمرکز به تنظیمات پلاگین برای جلوگیری از تکرار get_option در کلاس‌های مختلف
 */
class Options_Helper {

    const OPTION_KEY = 'wc_pdf_catalog_options';

    protected static $defaults = [
        'pdf_template_id' => 0,
        'show_price'      => 'yes',
        'show_attributes' => 'yes',
    ];

    /**
     * تمام تنظیمات پلاگین را به همراه مقادیر پیش‌فرض برمی‌گرداند
     *
     * @return array
     */
    public static function get_all() {
        $opts = get_option( self::OPTION_KEY, [] );
        if ( ! is_array( $opts ) ) {
            $opts = [];
        }
        return wp_parse_args( $opts, self::$defaults );
    }

    /**
     * یک تنظیم مشخص را برمی‌گرداند
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public static function get( $key, $default = null ) {
        $opts = self::get_all();
        if ( array_key_exists( $key, $opts ) ) {
            return $opts[ $key ];
        }
        return $default;
    }

    /**
     * آیا یک گزینه yes/no فعال است؟
     *
     * @param string $key
     * @return bool
     */
    public static function is_enabled( $key ) {
        return self::get( $key, 'yes' ) === 'yes';
    }

    /**
     * شناسه attachment قالب PDF
     *
     * @return int
     */
    public static function get_template_id() {
        return (int) self::get( 'pdf_template_id', 0 );
    }
}
