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
     * Build WP_Query args (tax_query/meta_query) that reproduce the
     * attribute, price and rating filters currently applied on a shop or
     * category archive page, so the generated catalog only contains the
     * products the visitor is actually seeing after filtering.
     *
     * @param string $category             Optional product_cat slug (from the shortcode or the current archive).
     * @param string $filters_query_string Raw query string (no leading "?") captured from the shop/category page URL,
     *                                     e.g. "filter_color=red&query_type_color=or&min_price=10&max_price=90&rating_filter=4,5".
     * @return array WP_Query args.
     */
    public function build_filtered_query_args( $category = '', $filters_query_string = '' ) {
        $tax_query = [];

        if ( $category ) {
            $tax_query[] = [
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => $category,
            ];
        }

        $parsed = [];
        if ( $filters_query_string ) {
            wp_parse_str( $filters_query_string, $parsed );
        }

        // Attribute filters (WooCommerce's own "filter_{attribute}" / "query_type_{attribute}" convention,
        // used by both the classic Filter by Attribute widget and the block-based filter widgets).
        if ( ! empty( $parsed ) && class_exists( '\\WC_Query' ) ) {
            $saved_get = $_GET; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $_GET      = $parsed; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash

            $chosen_attributes = \WC_Query::get_layered_nav_chosen_attributes();

            $_GET = $saved_get; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

            foreach ( $chosen_attributes as $taxonomy => $data ) {
                if ( empty( $data['terms'] ) || ! taxonomy_exists( $taxonomy ) ) {
                    continue;
                }
                $tax_query[] = [
                    'taxonomy'         => $taxonomy,
                    'field'            => 'slug',
                    'terms'            => $data['terms'],
                    'operator'         => 'and' === $data['query_type'] ? 'AND' : 'IN',
                    'include_children' => false,
                ];
            }
        }

        // Rating filter (?rating_filter=4,5), same term convention WooCommerce itself uses.
        if ( ! empty( $parsed['rating_filter'] ) && function_exists( 'wc_get_product_visibility_term_ids' ) ) {
            $product_visibility_terms = wc_get_product_visibility_term_ids();
            $rating_filter            = array_filter( array_map( 'absint', explode( ',', (string) $parsed['rating_filter'] ) ) );
            $rating_terms             = [];
            for ( $i = 1; $i <= 5; $i++ ) {
                if ( in_array( $i, $rating_filter, true ) && isset( $product_visibility_terms[ 'rated-' . $i ] ) ) {
                    $rating_terms[] = $product_visibility_terms[ 'rated-' . $i ];
                }
            }
            if ( $rating_terms ) {
                $tax_query[] = [
                    'taxonomy' => 'product_visibility',
                    'field'    => 'term_taxonomy_id',
                    'terms'    => $rating_terms,
                    'operator' => 'IN',
                ];
            }
        }

        $args = [];
        if ( count( $tax_query ) > 1 ) {
            $tax_query['relation'] = 'AND';
        }
        if ( ! empty( $tax_query ) ) {
            $args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
        }

        // Price filter (?min_price=10&max_price=90).
        if ( isset( $parsed['min_price'] ) || isset( $parsed['max_price'] ) ) {
            $min = isset( $parsed['min_price'] ) ? (float) $parsed['min_price'] : null;
            $max = isset( $parsed['max_price'] ) ? (float) $parsed['max_price'] : null;

            if ( null !== $min && null !== $max ) {
                $price_meta = [ 'key' => '_price', 'value' => [ $min, $max ], 'type' => 'DECIMAL', 'compare' => 'BETWEEN' ];
            } elseif ( null !== $min ) {
                $price_meta = [ 'key' => '_price', 'value' => $min, 'type' => 'DECIMAL', 'compare' => '>=' ];
            } else {
                $price_meta = [ 'key' => '_price', 'value' => $max, 'type' => 'DECIMAL', 'compare' => '<=' ];
            }

            $args['meta_query'] = [ $price_meta ]; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
        }

        return $args;
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
