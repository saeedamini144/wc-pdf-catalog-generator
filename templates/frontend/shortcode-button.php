<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<?php // با کلیک روی دکمه، فرم لید (نام/ایمیل/تلفن...) به‌صورت پاپ‌آپ با افکت نرم باز می‌شود و تنها پس از تکمیل موفق آن، فایل PDF ساخته می‌شود. ?>
<div class="wc-pdf-catalog-wrapper">
    <button type="button"
            class="wc-pdf-catalog-btn"
            data-category="<?php echo esc_attr( $category ); ?>"
            data-nonce="<?php echo esc_attr( $nonce ); ?>">
        <?php echo esc_html( $label ); ?>
    </button>
</div>
