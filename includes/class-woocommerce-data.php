<?php
namespace WC_PDF_Catalog;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WooCommerce_Data
 *
 * Helper class to fetch WooCommerce products and normalize product data
 */
class WooCommerce_Data {

    public function __construct() {
        // nothing to init for now
    }

    /**
     * Get products using WP_Query args (safe wrapper)
     *
     * @param array $args WP_Query args for products
     * @return \WC_Product[] array of WC_Product objects
     */
    public function get_products( $args = [] ) {
        if ( ! class_exists( 'WC_Product' ) ) {
            return [];
        }

        $defaults = [
            'post_type'      => 'product',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ];

        $query_args = wp_parse_args( $args, $defaults );

        // Ensure we only query allowed fields
        $query = new \WP_Query( $query_args );

        $products = [];
        if ( $query->have_posts() ) {
            foreach ( $query->posts as $post ) {
                $product = wc_get_product( $post->ID );
                if ( $product ) {
                    $products[] = $product;
                }
            }
        }
        wp_reset_postdata();
        return $products;
    }

    /**
     * Normalize product into an array suitable for PDF template
     *
     * @param \WC_Product $product
     * @return array
     */
    public function get_product_data_array( \WC_Product $product ) {
        // Use safe getters and sanitize outputs for HTML rendering
        $image_id = $product->get_image_id();
        $image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : '';

        $price_html = $this->strip_screen_reader_price_text( $product->get_price_html() );
        $regular = $product->get_regular_price();
        $sale = $product->get_sale_price();

        // Attributes
        $attributes = $this->get_product_attributes( $product );

        return [
            'id'            => (int) $product->get_id(),
            'title'         => wp_kses_post( $product->get_name() ),
            'price_html'    => $price_html ? $price_html : '',
            'regular_price' => $regular ? wc_price( $regular ) : '',
            'sale_price'    => $sale ? wc_price( $sale ) : '',
            'sku'           => $product->get_sku(),
            'permalink'     => esc_url( $product->get_permalink() ),
            'image'         => esc_url( $image_url ),
            'attributes'    => $attributes,
        ];
    }

    /**
     * WooCommerce's get_price_html() adds visually-hidden
     * "Original price was: X. Current price is: Y." text (via
     * .screen-reader-text spans) for accessibility. That CSS class is
     * normally hidden by the theme, but the PDF renderer doesn't load
     * theme styles, so the text renders visibly and duplicates the price.
     * Strip it out before it reaches the PDF template.
     *
     * @param string $price_html
     * @return string
     */
    protected function strip_screen_reader_price_text( $price_html ) {
        if ( ! $price_html ) {
            return $price_html;
        }
        return preg_replace( '#<span[^>]*class="[^"]*screen-reader-text[^"]*"[^>]*>.*?</span>#si', '', $price_html );
    }

    /**
     * Extract product attributes as simple arrays
     *
     * @param \WC_Product $product
     * @return array
     */
    protected function get_product_attributes( \WC_Product $product ) {
        $attrs = [];
        $raw = $product->get_attributes();

        foreach ( $raw as $key => $attr ) {
            // WC_Product_Attribute or taxonomy-based attribute
            if ( is_a( $attr, 'WC_Product_Attribute' ) ) {
                $name = $attr->get_name();
                $options = $attr->get_options();
                $attrs[] = [
                    'name'    => wc_attribute_label( $name ),
                    'options' => array_map( 'sanitize_text_field', $options ),
                ];
            } else {
                // fallback: try to get label
                $label = wc_attribute_label( $key );
                $value = (string) $attr;
                $attrs[] = [
                    'name'    => $label,
                    'options' => [ sanitize_text_field( $value ) ],
                ];
            }
        }

        return $attrs;
    }
}
