<?php
namespace WC_PDF_Catalog;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // جلوگیری از دسترسی مستقیم به فایل
}

/**
 * Plugin_Loader
 *
 * این کلاس مسئول بارگذاری فایل‌های مورد نیاز پلاگین، رجیستر کردن ماژول‌ها،
 * و راه‌اندازی هوک‌های عمومی است. طراحی شده تا ماژولار و قابل توسعه باشد.
 */
class Plugin_Loader {

    /**
     * مسیر دایرکتوری includes
     * @var string
     */
    protected $includes_dir;

    public function __construct() {
        $this->includes_dir = WC_PDF_CATALOG_PLUGIN_DIR . 'includes/';
    }

    /**
     * نقطه ورود برای راه‌اندازی پلاگین
     */
    public function init() {
        // لود فایل‌های کمکی (helpers)
        $this->load_helpers();

        // لود کلاس‌های اصلی پلاگین
        $this->load_includes();

        // رجیستر اسکریپت‌ها و استایل‌های عمومی
        $this->register_assets();

        // نمونه‌سازی و راه‌اندازی ماژول‌ها
        $this->init_modules();

        // رجیستر REST routes یا AJAX های عمومی در صورت نیاز
        $this->register_common_hooks();
    }

    /**
     * لود فایل‌های کمکی (helpers)
     */
    protected function load_helpers() {
        $helpers = [
            'helpers/class-options-helper.php',
            'helpers/class-assets-loader.php',
            'helpers/functions-utils.php',
        ];

        foreach ( $helpers as $file ) {
            $path = $this->includes_dir . $file;
            if ( file_exists( $path ) ) {
                require_once $path;
            } else {
                error_log( "[WC PDF Catalog] Missing helper file: {$path}" );
            }
        }
    }

    /**
     * لود فایل‌های اصلی پلاگین (ماژول‌ها)
     */
    protected function load_includes() {
        // ترتیب لود مهم است: کلاس DB باید قبل از ماژول‌هایی که از آن استفاده می‌کنند لود شود
        $files = [
            'class-db-manager.php',            // مدیریت جدول دیتابیس (باید اول باشد)
            'class-woocommerce-data.php',      // گرفتن محصولات ووکامرس
            'class-pdf-template-manager.php',  // مدیریت قالب PDF (تصویر پس‌زمینه)
            'class-pdf-generator.php',         // تولید PDF با Dompdf/mPDF
            'class-download-controller.php',   // endpointها و cleanup
            'class-form-validator.php',        // اعتبارسنجی فرم
            'class-form-handler.php',          // رندر فرم در فرانت‌اند (شورت‌کد)
            'class-form-ajax.php',             // هندل AJAX فرم
            'class-shortcode-handler.php',     // شورت‌کد دکمه/آرشیو
            'class-admin-settings.php',        // صفحه تنظیمات پلاگین
            'class-admin-requests-list.php',   // صفحه لیست درخواست‌ها در ادمین
            // در آینده می‌توان ماژول‌های دیگر را اینجا اضافه کرد
        ];

        foreach ( $files as $file ) {
            $path = $this->includes_dir . $file;
            if ( file_exists( $path ) ) {
                require_once $path;
            } else {
                // لاگ خطا برای دیباگ؛ اجرای پلاگین ادامه می‌یابد تا سایت از کار نیفتد
                error_log( "[WC PDF Catalog] Missing include file: {$path}" );
            }
        }
    }

    /**
     * رجیستر و enqueue اسکریپت‌ها و استایل‌های عمومی از طریق Assets_Loader
     * Assets_Loader کلاس استاتیک است که در helpers/class-assets-loader.php تعریف شده.
     */
    protected function register_assets() {
        if ( class_exists( '\\WC_PDF_Catalog\\Assets_Loader' ) ) {
            // Assets_Loader::init() خودش هوک‌های لازم را اضافه می‌کند
            Assets_Loader::init();
        }
    }

    /**
     * نمونه‌سازی ماژول‌ها و رجیستر هوک‌های مربوطه
     */
    protected function init_modules() {
        // DB Manager: کلاس استاتیک است؛ نیازی به نمونه‌سازی ندارد اما اطمینان می‌دهیم موجود است
        if ( ! class_exists( '\\WC_PDF_Catalog\\DB_Manager' ) ) {
            error_log( '[WC PDF Catalog] DB_Manager class not found.' );
        }

        // WooCommerce data helper
        if ( class_exists( '\\WC_PDF_Catalog\\WooCommerce_Data' ) ) {
            // نمونه‌سازی اختیاری برای آماده‌سازی (در صورت نیاز)
            $this->wc_data = new WooCommerce_Data();
        }

        // PDF template manager
        if ( class_exists( '\\WC_PDF_Catalog\\PDF_Template_Manager' ) ) {
            $this->pdf_template = new PDF_Template_Manager();
        }

        // PDF generator
        if ( class_exists( '\\WC_PDF_Catalog\\PDF_Generator' ) ) {
            $this->pdf_generator = new PDF_Generator();
        }

        // Download controller (REST routes / cleanup)
        if ( class_exists( '\\WC_PDF_Catalog\\Download_Controller' ) ) {
            $this->download_controller = new Download_Controller();
        }

        // Form validator (static utility)
        if ( ! class_exists( '\\WC_PDF_Catalog\\Form_Validator' ) ) {
            error_log( '[WC PDF Catalog] Form_Validator class not found.' );
        }

        // Form handler (شورت‌کد فرم)
        if ( class_exists( '\\WC_PDF_Catalog\\Form_Handler' ) ) {
            $this->form_handler = new Form_Handler();
        }

        // Form AJAX (ثبت و تولید PDF)
        if ( class_exists( '\\WC_PDF_Catalog\\Form_Ajax' ) ) {
            $this->form_ajax = new Form_Ajax();
        }

        // Shortcode handler (دکمه/آرشیو)
        if ( class_exists( '\\WC_PDF_Catalog\\Shortcode_Handler' ) ) {
            $this->shortcode_handler = new Shortcode_Handler();
        }

        // Admin settings page
        if ( class_exists( '\\WC_PDF_Catalog\\Admin_Settings' ) ) {
            $this->admin_settings = new Admin_Settings();
        }

        // Admin requests list (لیست ارسال‌ها)
        if ( class_exists( '\\WC_PDF_Catalog\\Admin_Requests_List' ) ) {
            $this->admin_requests = new Admin_Requests_List();
        }
    }

    /**
     * رجیستر هوک‌های عمومی (REST, AJAX, i18n و غیره)
     */
    protected function register_common_hooks() {
        // بارگذاری متن‌های ترجمه (در صورت وجود فایل‌های .mo)
        add_action( 'init', [ $this, 'load_textdomain' ] );

        // در صورت نیاز می‌توانیم REST routeهای عمومی را اینجا اضافه کنیم
        // (بعضی ماژول‌ها خودشان routeها را رجیستر می‌کنند)
    }

    /**
     * بارگذاری textdomain برای ترجمه‌ها
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'wc-pdf-catalog', false, dirname( plugin_basename( WC_PDF_CATALOG_PLUGIN_FILE ) ) . '/languages/' );
    }
}
