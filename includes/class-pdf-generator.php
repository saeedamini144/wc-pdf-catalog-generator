<?php
namespace WC_PDF_Catalog;

use Dompdf\Dompdf;
use Dompdf\Options;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * PDF_Generator
 *
 * Render HTML template and convert to PDF using Dompdf.
 * - Accepts array of product arrays or WC_Product objects
 * - Saves PDF to uploads/wc-pdf-catalog/
 */
class PDF_Generator {

    protected $template_manager;
    protected $wc_data;

    public function __construct() {
        $this->template_manager = new PDF_Template_Manager();
        $this->wc_data = new WooCommerce_Data();
    }

    /**
     * Generate PDF from products
     *
     * @param array $products array of WC_Product or normalized arrays
     * @param array $args optional settings (paper, orientation, show_price, show_attributes)
     * @return array ['status'=>bool, 'path'=>string, 'url'=>string, 'message'=>string]
     */
    public function generate( $products, $args = [] ) {
        if ( ! class_exists( '\\Dompdf\\Dompdf' ) ) {
            return [
                'status'  => false,
                'message' => __( 'PDF engine (Dompdf) is not available. Please run composer install.', 'wc-pdf-catalog' ),
            ];
        }

        // Normalize products to arrays
        $product_arrays = [];
        foreach ( $products as $p ) {
            if ( is_a( $p, '\\WC_Product' ) ) {
                $product_arrays[] = $this->wc_data->get_product_data_array( $p );
            } elseif ( is_array( $p ) ) {
                $product_arrays[] = $p;
            }
        }

        // Render HTML
        $html = $this->render_html( $product_arrays, $args );

        // Dompdf options
        $options = new Options();
        $options->set( 'isRemoteEnabled', true ); // allow remote images
        $options->set( 'isHtml5ParserEnabled', true );
        // default font; for better Persian support, add a Persian font to Dompdf font directory and set here
        $options->set( 'defaultFont', 'DejaVu Sans' );

        try {
            $dompdf = new Dompdf( $options );
            $dompdf->loadHtml( $html );
            $paper = isset( $args['paper'] ) ? $args['paper'] : 'A4';
            $orientation = isset( $args['orientation'] ) ? $args['orientation'] : 'portrait';
            $dompdf->setPaper( $paper, $orientation );
            $dompdf->render();

            // Save to uploads
            $upload_dir = wp_upload_dir();
            $dir = trailingslashit( $upload_dir['basedir'] ) . 'wc-pdf-catalog/';
            if ( ! file_exists( $dir ) ) {
                wp_mkdir_p( $dir );
            }
            // جلوگیری از directory listing در پوشه آپلود کاتالوگ‌ها
            $index_file = $dir . 'index.php';
            if ( ! file_exists( $index_file ) ) {
                file_put_contents( $index_file, "<?php\n// Silence is golden.\n" );
            }

            $filename = 'catalog-' . time() . '-' . wp_generate_password( 6, false, false ) . '.pdf';
            $filepath = $dir . $filename;
            $output = $dompdf->output();
            file_put_contents( $filepath, $output );

            $fileurl = trailingslashit( $upload_dir['baseurl'] ) . 'wc-pdf-catalog/' . $filename;

            return [
                'status' => true,
                'path'   => $filepath,
                'url'    => $fileurl,
            ];
        } catch ( \Exception $e ) {
            error_log( '[WC PDF Catalog] PDF generation error: ' . $e->getMessage() );
            return [
                'status'  => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Render HTML using template file
     *
     * @param array $products
     * @param array $args
     * @return string HTML
     */
    protected function render_html( $products, $args = [] ) {
        $template = WC_PDF_CATALOG_PLUGIN_DIR . 'templates/pdf/default-layout.php';
        ob_start();
        if ( file_exists( $template ) ) {
            // expose variables to template
            $products_for_template = $products;
            $pdf_bg_html = $this->template_manager->get_full_page_background_html();
            $show_price = isset( $args['show_price'] ) ? $args['show_price'] : ( Options_Helper::is_enabled( 'show_price' ) ? 'yes' : 'no' );
            $show_attributes = isset( $args['show_attributes'] ) ? $args['show_attributes'] : ( Options_Helper::is_enabled( 'show_attributes' ) ? 'yes' : 'no' );

            include $template;
        } else {
            echo '<h1>PDF template not found</h1>';
        }
        $html = ob_get_clean();
        return $html;
    }
}
