<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$is_preview && !$hide_panel && !$preview_popup_image) {
    // Looked up by a random per-entry token (set in functions.php via the
    // gform_confirmation_1 filter), never by the entry's own sequential ID
    // — an ID in the URL would let anyone view another customer's booking
    // just by incrementing/decrementing it.
    $token   = isset($_GET['token']) ? sanitize_text_field($_GET['token']) : '';
    $entries = $token ? GFAPI::get_entries(
        1,
        [
            'status'        => 'active',
            'field_filters' => [
                ['key' => 'confirmation_token', 'value' => $token],
            ],
        ],
        [],
        ['page_size' => 1]
    ) : [];

    $entry = (!is_wp_error($entries) && !empty($entries)) ? $entries[0] : null;
    $valid = (bool) $entry;

    // Same row list (and order) as the two booking notification emails —
    // see fh_gf_booking_entry_rows() in functions.php — so the page and
    // the emails a customer already received never show different info.
    $rows = $valid ? fh_gf_booking_entry_rows($entry) : [];
?>

<section class="booking-summary-panel <?php echo $generic_block_settings_classes; ?>">
    <div class="container <?php echo $generic_container_class; ?>">

        <?php if ($valid) : ?>

            <div class="booking-summary-panel__header">
                <h1 class="booking-summary-panel__title">Booking Request Received</h1>
            </div>

            <div class="booking-summary-block booking-summary-panel__summary">
                <?php foreach ($rows as $i => [$label, $value]) :
                    $is_last = $i === count($rows) - 1;
                ?>
                    <div class="booking-summary-block__row<?php echo $is_last ? ' booking-summary-block__row--total' : ''; ?>">
                        <strong><?php echo esc_html($label); ?>:</strong>
                        <?php echo $value; ?>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php else : ?>

            <div class="booking-summary-panel__missing">
                <p>
                    We couldn't find that booking. If you've just submitted a request, please check your
                    email for confirmation, or <a href="<?php echo esc_url(site_url('/flights')); ?>">start a new search</a>.
                </p>
            </div>

        <?php endif; ?>

    </div>
</section>

<?php
}
?>
