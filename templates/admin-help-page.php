<?php
namespace WC_PDF_Catalog;

/**
 * templates/admin-help-page.php
 * Plugin usage documentation shown in the admin dashboard.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap wc-pdf-catalog-help" style="max-width:900px;">
    <h1><?php esc_html_e( 'PDF Catalog Generator — Help', 'wc-pdf-catalog' ); ?></h1>

    <div class="card" style="max-width:100%;padding:16px 20px;margin-top:16px;">
        <h2><?php esc_html_e( 'What does this plugin do?', 'wc-pdf-catalog' ); ?></h2>
        <p>
            <?php esc_html_e( 'This plugin adds a "Generate PDF Catalog" button that you can place on the shop page, on a specific product category archive, or on any other page. When a visitor clicks the button:', 'wc-pdf-catalog' ); ?>
        </p>
        <ol>
            <li><?php esc_html_e( '1. A short lead form (first/last name, email, required phone number, plus a few optional fields) opens in a popup.', 'wc-pdf-catalog' ); ?></li>
            <li><?php esc_html_e( '2. The form is validated and saved to the database on the server. No PDF is ever generated without a successful form submission.', 'wc-pdf-catalog' ); ?></li>
            <li><?php esc_html_e( '3. WooCommerce products (main image, price and attributes) are collected for the selected category.', 'wc-pdf-catalog' ); ?></li>
            <li><?php esc_html_e( '4. A PDF file is generated using the template image you uploaded in Settings as a full-page background.', 'wc-pdf-catalog' ); ?></li>
            <li><?php esc_html_e( '5. The visitor is shown a download link for the PDF, along with share buttons (WhatsApp, Telegram, copy link).', 'wc-pdf-catalog' ); ?></li>
        </ol>
    </div>

    <div class="card" style="max-width:100%;padding:16px 20px;margin-top:16px;">
        <h2><?php esc_html_e( 'Main shortcode: the "Generate Catalog" button', 'wc-pdf-catalog' ); ?></h2>
        <p><code>[wc_pdf_catalog]</code></p>
        <p><?php esc_html_e( 'Place this shortcode on any page, post, or text/shortcode widget (or a Shortcode block in the block editor). If you place it on a WooCommerce product category archive page, the catalog will automatically be limited to that category — no extra configuration needed.', 'wc-pdf-catalog' ); ?></p>

        <table class="widefat striped" style="margin-top:10px;">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Parameter', 'wc-pdf-catalog' ); ?></th>
                    <th><?php esc_html_e( 'Required?', 'wc-pdf-catalog' ); ?></th>
                    <th><?php esc_html_e( 'Description', 'wc-pdf-catalog' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>category</code></td>
                    <td><?php esc_html_e( 'No', 'wc-pdf-catalog' ); ?></td>
                    <td><?php esc_html_e( 'The slug of a WooCommerce product category. If left empty and the shortcode is placed on a category archive page, that category is detected automatically; otherwise all published products are included in the catalog.', 'wc-pdf-catalog' ); ?></td>
                </tr>
                <tr>
                    <td><code>label</code></td>
                    <td><?php esc_html_e( 'No', 'wc-pdf-catalog' ); ?></td>
                    <td><?php esc_html_e( 'The text shown on the button (default: "Generate PDF Catalog").', 'wc-pdf-catalog' ); ?></td>
                </tr>
            </tbody>
        </table>

        <p style="margin-top:12px;"><strong><?php esc_html_e( 'Examples:', 'wc-pdf-catalog' ); ?></strong></p>
        <ul style="list-style:disc;padding-left:20px;">
            <li><code>[wc_pdf_catalog]</code> — <?php esc_html_e( 'catalog of all products (or of the current category, if placed on a category archive)', 'wc-pdf-catalog' ); ?></li>
            <li><code>[wc_pdf_catalog category="lighting"]</code> — <?php esc_html_e( 'catalog limited to the category with slug "lighting"', 'wc-pdf-catalog' ); ?></li>
            <li><code>[wc_pdf_catalog label="Download Product Catalog"]</code> — <?php esc_html_e( 'custom button text', 'wc-pdf-catalog' ); ?></li>
        </ul>
    </div>

    <div class="card" style="max-width:100%;padding:16px 20px;margin-top:16px;">
        <h2><?php esc_html_e( 'Standalone form shortcode', 'wc-pdf-catalog' ); ?></h2>
        <p><code>[wc_pdf_catalog_form]</code></p>
        <p><?php esc_html_e( 'Use this shortcode if you want to show only the lead form (no button) on a page such as "Request a Catalog". This mode generates a catalog of all products.', 'wc-pdf-catalog' ); ?></p>
    </div>

    <div class="card" style="max-width:100%;padding:16px 20px;margin-top:16px;">
        <h2><?php esc_html_e( 'Plugin settings', 'wc-pdf-catalog' ); ?></h2>
        <p>
            <?php
            printf(
                /* translators: %s: link to settings page */
                esc_html__( 'From the %s screen you can configure the following:', 'wc-pdf-catalog' ),
                '<a href="' . esc_url( admin_url( 'admin.php?page=wc-pdf-catalog' ) ) . '">' . esc_html__( 'PDF Catalog → Settings', 'wc-pdf-catalog' ) . '</a>'
            );
            ?>
        </p>
        <ul style="list-style:disc;padding-left:20px;">
            <li><?php esc_html_e( 'PDF template image: used as a full-page background repeated on every page of the PDF, with product content placed on top of it. Recommended size: A4 portrait, roughly 1240×1754px.', 'wc-pdf-catalog' ); ?></li>
            <li><?php esc_html_e( 'Show price: toggle whether product prices appear in the PDF.', 'wc-pdf-catalog' ); ?></li>
            <li><?php esc_html_e( 'Show attributes: toggle whether product attributes (e.g. color, size) appear in the PDF.', 'wc-pdf-catalog' ); ?></li>
        </ul>
    </div>

    <div class="card" style="max-width:100%;padding:16px 20px;margin-top:16px;">
        <h2><?php esc_html_e( 'Requests list', 'wc-pdf-catalog' ); ?></h2>
        <p>
            <?php
            printf(
                /* translators: %s: link to requests page */
                esc_html__( 'Every time the form is submitted successfully, the submitted details (name, email, phone, company, etc.) are stored and can be viewed or deleted under %s.', 'wc-pdf-catalog' ),
                '<a href="' . esc_url( admin_url( 'admin.php?page=wc-pdf-catalog-requests' ) ) . '">' . esc_html__( 'PDF Catalog → Requests', 'wc-pdf-catalog' ) . '</a>'
            );
            ?>
        </p>
    </div>

    <div class="card" style="max-width:100%;padding:16px 20px;margin-top:16px;">
        <h2><?php esc_html_e( 'Technical notes', 'wc-pdf-catalog' ); ?></h2>
        <ul style="list-style:disc;padding-left:20px;">
            <li><?php esc_html_e( 'WooCommerce must be installed and active for this plugin to work.', 'wc-pdf-catalog' ); ?></li>
            <li><?php esc_html_e( 'Generated PDF files are stored under wp-content/uploads/wc-pdf-catalog/ and files older than 7 days are deleted automatically once a day.', 'wc-pdf-catalog' ); ?></li>
            <li><?php esc_html_e( 'A PDF is only ever generated after the lead form has been successfully validated — there is no way to bypass the form.', 'wc-pdf-catalog' ); ?></li>
        </ul>
    </div>
</div>
