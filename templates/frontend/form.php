<?php
// Variable: $nonce from Form_Handler

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wc-pdf-catalog-standalone">
    <form class="wc-pdf-catalog-form" method="post" action="#" data-nonce="<?php echo esc_attr( $nonce ); ?>">
        <div class="wc-pdf-form-errors"></div>

        <div class="wc-pdf-fields">
            <div class="wc-pdf-field">
                <label><?php esc_html_e( 'First Name', 'wc-pdf-catalog' ); ?> <span class="wc-pdf-required">*</span></label>
                <input type="text" name="first_name" required>
            </div>

            <div class="wc-pdf-field">
                <label><?php esc_html_e( 'Last Name', 'wc-pdf-catalog' ); ?> <span class="wc-pdf-required">*</span></label>
                <input type="text" name="last_name" required>
            </div>

            <div class="wc-pdf-field">
                <label><?php esc_html_e( 'Email', 'wc-pdf-catalog' ); ?> <span class="wc-pdf-required">*</span></label>
                <input type="email" name="email" required>
            </div>

            <div class="wc-pdf-field">
                <label><?php esc_html_e( 'Phone Number', 'wc-pdf-catalog' ); ?> <span class="wc-pdf-required">*</span></label>
                <input type="tel" name="phone" required>
            </div>

            <div class="wc-pdf-field wc-pdf-field-full">
                <label><?php esc_html_e( 'Company Name', 'wc-pdf-catalog' ); ?></label>
                <input type="text" name="company">
            </div>

            <div class="wc-pdf-field">
                <label><?php esc_html_e( 'Country', 'wc-pdf-catalog' ); ?></label>
                <input type="text" name="country">
            </div>

            <div class="wc-pdf-field">
                <label><?php esc_html_e( 'Telegram ID', 'wc-pdf-catalog' ); ?></label>
                <input type="text" name="telegram_id">
            </div>

            <div class="wc-pdf-field wc-pdf-field-full">
                <label><?php esc_html_e( 'WhatsApp', 'wc-pdf-catalog' ); ?></label>
                <input type="text" name="whatsapp">
            </div>
        </div>

        <div class="wc-pdf-actions">
            <button type="submit" class="wc-pdf-submit">
                <?php esc_html_e( 'Submit and Download Catalog', 'wc-pdf-catalog' ); ?>
            </button>
        </div>
    </form>
</div>
