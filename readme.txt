=== WooCommerce PDF Catalog Generator ===
Contributors: saeedamini
Tags: woocommerce, pdf, catalog, lead-generation, product-catalog
Requires at least: 5.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Generate PDF catalogs from WooCommerce products (with image, price and attributes), gated behind a lead-capture form, with an admin requests list.

== Description ==

WooCommerce PDF Catalog Generator adds a "Generate PDF Catalog" button (via shortcode) that customers can place on the shop page or on any product category archive. When a visitor clicks the button:

1. A lead form (name, email, phone, company, country, Telegram, WhatsApp) is shown and must be completed.
2. Once submitted and validated, the plugin fetches the relevant WooCommerce products (the current category if the shortcode is on a category archive, or all products otherwise) — including main image, price and attributes.
3. Those products are rendered into a PDF using the admin-configured template image as a full-page background (a company letterhead/cover you upload once in Settings), with the product listing placed on top of it.
4. The visitor receives a download link plus quick share buttons (WhatsApp, Telegram, copy link).

Every submission is stored and visible to admins under **PDF Catalog → Requests**, with pagination and delete support.

= Features =

* `[wc_pdf_catalog]` shortcode — shows a button that opens the lead form; auto-detects the current product category when placed on a category archive page.
* `[wc_pdf_catalog_form]` shortcode — the lead form on its own (generates a catalog of all products when used standalone).
* PDF generation via Dompdf, with RTL/Persian support (DejaVu Sans).
* Admin-uploadable full-page PDF background/template image.
* Toggle price and attributes visibility from Settings.
* Admin requests list with pagination and secure delete.
* Automatic daily cleanup of PDF files older than 7 days (plus a manual cleanup REST endpoint for admins).
* The PDF is only ever generated after the lead form is validated server-side — there is no way to bypass the form.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`.
2. Run `composer install` inside the plugin folder if the `vendor/` directory is not already present (required for Dompdf).
3. Activate the plugin through the "Plugins" screen in WordPress.
4. Go to **PDF Catalog → Settings** to upload your PDF template image and configure price/attribute visibility.
5. Add the `[wc_pdf_catalog]` shortcode to the shop page, a category archive, or any page/widget.

== Frequently Asked Questions ==

= Does this work without WooCommerce? =
No, WooCommerce must be installed and active.

= Can I generate a catalog for a specific category only? =
Yes — pass `category="your-category-slug"` to the shortcode, or simply place the shortcode on that category's archive page and it will be detected automatically.

= Can visitors skip the lead form? =
No. Generating a PDF always requires a successfully validated form submission; this is enforced on the server, not just in the browser.

== Changelog ==

= 1.1.0 =
* Removed the "generate without form" bypass — the lead form is now always required before a PDF is generated, per the intended flow.
* PDF template image is now used as a true full-page background on every page, with product content rendered on top of it (previously it was only a small header strip).
* Shortcode now auto-detects the current product category when placed on a category archive page.
* Added share buttons (WhatsApp, Telegram, copy link) alongside the download link.
* Added automatic daily cleanup of old generated PDF files (previously only a manual, unused REST endpoint).
* Added guards for missing WooCommerce/Dompdf so the site doesn't fatal-error if dependencies are missing.
* Removed dead/duplicate JS and empty template files; implemented the previously-empty helper classes.
* Various sanitization and directory-protection hardening.

= 1.0.0 =
* Initial release.
