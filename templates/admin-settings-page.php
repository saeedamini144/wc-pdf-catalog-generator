<?php
namespace WC_PDF_Catalog;

/**
 * templates/admin-settings-page.php
 *
 * متغیرها:
 * - $opts : آرایه تنظیمات از get_option
 *
 * این قالب فرم تنظیمات را رندر می‌کند.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$opts = wp_parse_args( $opts ?? [], [
    'pdf_template_id' => 0,
    'show_price' => 'yes',
    'show_attributes' => 'yes',
] );

$template_id = intval( $opts['pdf_template_id'] );
$template_url = $template_id ? wp_get_attachment_image_url( $template_id, 'medium' ) : '';
?>
<div class="wrap">
    <h1><?php esc_html_e( 'WooCommerce PDF Catalog Settings', 'wc-pdf-catalog' ); ?></h1>

    <form method="post" action="options.php">
        <?php
        settings_fields( 'wc_pdf_catalog_group' );
        do_settings_sections( 'wc_pdf_catalog_group' );
        ?>

        <table class="form-table">
            <tr>
                <th scope="row"><label for="wc_pdf_template"><?php esc_html_e( 'PDF Template Image', 'wc-pdf-catalog' ); ?></label></th>
                <td>
                    <input type="hidden" id="wc_pdf_template_id" name="<?php echo esc_attr( Admin_Settings::OPTION_KEY ); ?>[pdf_template_id]" value="<?php echo esc_attr( $template_id ); ?>">
                    <div id="wc-pdf-template-preview" style="margin-bottom:8px;">
                        <?php if ( $template_url ) : ?>
                            <img src="<?php echo esc_url( $template_url ); ?>" style="max-width:300px;height:auto;">
                        <?php endif; ?>
                    </div>
                    <button type="button" class="button" id="wc-pdf-template-upload"><?php esc_html_e( 'Select Image', 'wc-pdf-catalog' ); ?></button>
                    <button type="button" class="button" id="wc-pdf-template-remove"><?php esc_html_e( 'Remove', 'wc-pdf-catalog' ); ?></button>
                    <p class="description"><?php esc_html_e( 'This image is used as a full-page background on every page of the generated PDF catalog, and the product content (image, title, price, attributes) is placed on top of it. Recommended size: A4 portrait, e.g. 1240x1754px (210x297mm at 150dpi).', 'wc-pdf-catalog' ); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php esc_html_e( 'Show Price', 'wc-pdf-catalog' ); ?></th>
                <td>
                    <select name="<?php echo esc_attr( Admin_Settings::OPTION_KEY ); ?>[show_price]">
                        <option value="yes" <?php selected( $opts['show_price'], 'yes' ); ?>><?php esc_html_e( 'Yes', 'wc-pdf-catalog' ); ?></option>
                        <option value="no" <?php selected( $opts['show_price'], 'no' ); ?>><?php esc_html_e( 'No', 'wc-pdf-catalog' ); ?></option>
                    </select>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php esc_html_e( 'Show Attributes', 'wc-pdf-catalog' ); ?></th>
                <td>
                    <select name="<?php echo esc_attr( Admin_Settings::OPTION_KEY ); ?>[show_attributes]">
                        <option value="yes" <?php selected( $opts['show_attributes'], 'yes' ); ?>><?php esc_html_e( 'Yes', 'wc-pdf-catalog' ); ?></option>
                        <option value="no" <?php selected( $opts['show_attributes'], 'no' ); ?>><?php esc_html_e( 'No', 'wc-pdf-catalog' ); ?></option>
                    </select>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>
</div>
<?php // منطق انتخاب تصویر (wp.media) در assets/js/admin.js پیاده‌سازی شده و از طریق Assets_Loader::enqueue_admin() لود می‌شود ?>
