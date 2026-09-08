<?php
/**
 * templates/admin-requests-list.php
 *
 * متغیرهای مورد استفاده:
 * - $requests (آرایه اشیاء)
 * - $total (تعداد کل رکوردها)
 * - $paged (شماره صفحه فعلی)
 * - $per_page (تعداد در هر صفحه)
 * - $total_pages (تعداد کل صفحات)
 * - $base_url (آدرس پایه صفحه)
 * - $delete_nonce (nonce برای حذف)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap">
    <h1><?php esc_html_e( 'Catalog Requests', 'wc-pdf-catalog' ); ?></h1>

    <?php if ( isset( $_GET['deleted'] ) ) : ?>
        <?php if ( intval( $_GET['deleted'] ) === 1 ) : ?>
            <div class="notice notice-success"><p><?php esc_html_e( 'Request deleted.', 'wc-pdf-catalog' ); ?></p></div>
        <?php else : ?>
            <div class="notice notice-error"><p><?php esc_html_e( 'Failed to delete request.', 'wc-pdf-catalog' ); ?></p></div>
        <?php endif; ?>
    <?php endif; ?>

    <p><?php printf( esc_html__( 'Showing page %d of %d — total %d requests', 'wc-pdf-catalog' ), $paged, max(1, $total_pages), $total ); ?></p>

    <table class="widefat fixed striped">
        <thead>
            <tr>
                <th><?php esc_html_e( 'ID', 'wc-pdf-catalog' ); ?></th>
                <th><?php esc_html_e( 'Name', 'wc-pdf-catalog' ); ?></th>
                <th><?php esc_html_e( 'Email', 'wc-pdf-catalog' ); ?></th>
                <th><?php esc_html_e( 'Phone', 'wc-pdf-catalog' ); ?></th>
                <th><?php esc_html_e( 'Company', 'wc-pdf-catalog' ); ?></th>
                <th><?php esc_html_e( 'Country', 'wc-pdf-catalog' ); ?></th>
                <th><?php esc_html_e( 'Telegram', 'wc-pdf-catalog' ); ?></th>
                <th><?php esc_html_e( 'WhatsApp', 'wc-pdf-catalog' ); ?></th>
                <th><?php esc_html_e( 'Created At', 'wc-pdf-catalog' ); ?></th>
                <th><?php esc_html_e( 'Downloaded At', 'wc-pdf-catalog' ); ?></th>
                <th><?php esc_html_e( 'IP', 'wc-pdf-catalog' ); ?></th>
                <th><?php esc_html_e( 'Actions', 'wc-pdf-catalog' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $requests ) ) : ?>
                <tr>
                    <td colspan="12"><?php esc_html_e( 'No requests found.', 'wc-pdf-catalog' ); ?></td>
                </tr>
            <?php else : ?>
                <?php foreach ( $requests as $r ) : ?>
                    <tr>
                        <td><?php echo esc_html( $r->id ); ?></td>
                        <td><?php echo esc_html( $r->first_name . ' ' . $r->last_name ); ?></td>
                        <td><?php echo esc_html( $r->email ); ?></td>
                        <td><?php echo esc_html( $r->phone ); ?></td>
                        <td><?php echo esc_html( $r->company ); ?></td>
                        <td><?php echo esc_html( $r->country ); ?></td>
                        <td><?php echo esc_html( $r->telegram_id ); ?></td>
                        <td><?php echo esc_html( $r->whatsapp ); ?></td>
                        <td><?php echo esc_html( $r->created_at ); ?></td>
                        <td><?php echo esc_html( $r->downloaded_at ); ?></td>
                        <td><?php echo esc_html( $r->ip_address ); ?></td>
                        <td>
                            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
                                <?php wp_nonce_field( 'wc_pdf_catalog_delete_request' ); ?>
                                <input type="hidden" name="action" value="wc_pdf_catalog_delete_request">
                                <input type="hidden" name="request_id" value="<?php echo esc_attr( $r->id ); ?>">
                                <button type="submit" class="button button-link-delete" onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to delete this request?', 'wc-pdf-catalog' ) ); ?>');">
                                    <?php esc_html_e( 'Delete', 'wc-pdf-catalog' ); ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="tablenav">
        <div class="tablenav-pages">
            <?php
            // ساخت لینک‌های پیجینیشن امن
            $page_links = paginate_links( [
                'base'      => add_query_arg( 'paged', '%#%', $base_url ),
                'format'    => '',
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
                'total'     => $total_pages,
                'current'   => $paged,
                'type'      => 'array',
            ] );

            if ( is_array( $page_links ) ) {
                echo '<span class="pagination-links">';
                foreach ( $page_links as $link ) {
                    echo wp_kses_post( $link );
                }
                echo '</span>';
            }
            ?>
        </div>
    </div>
</div>
