<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wc-pdf-catalog-wrapper">
    <button type="button"
            class="wc-pdf-catalog-btn"
            data-category="<?php echo esc_attr( $category ); ?>"
            data-nonce="<?php echo esc_attr( $nonce ); ?>">
        <?php echo esc_html( $label ); ?>
    </button>

    <?php // با کلیک روی دکمه، فرم لید بالا (نام/ایمیل/تلفن...) به‌صورت پاپ‌آپ نمایش داده می‌شود و تنها پس از تکمیل موفق آن، فایل PDF ساخته می‌شود. ?>
    <div class="wc-pdf-catalog-container" style="margin-top:12px;"></div>
</div>
