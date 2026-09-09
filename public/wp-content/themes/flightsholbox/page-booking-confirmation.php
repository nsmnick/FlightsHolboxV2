<?php get_header(); ?>

<?php
$entry_id = isset($_GET['entry_id']) ? (int) $_GET['entry_id'] : 0;
$entry    = $entry_id ? GFAPI::get_entry($entry_id) : null;

$valid = $entry
    && !is_wp_error($entry)
    && (int) $entry['form_id'] === 1
    && $entry['status'] !== 'trash';

$is_round_trip = $valid && ($entry['44'] ?? '') === 'Round Trip';
?>

<section class="booking-confirmation-page">
    <div class="container booking-confirmation-page__container">

        <?php if ($valid) : ?>

            <div class="booking-confirmation-page__header">
                <h1 class="booking-confirmation-page__title">Booking Request Received</h1>
                <p class="booking-confirmation-page__ref">
                    Booking Reference: <strong>#<?php echo esc_html($entry['id']); ?></strong>
                </p>
            </div>

            <div class="booking-summary-block booking-confirmation-page__summary">
                <div class="booking-summary-block__row">
                    <strong>Route:</strong>
                    <?php echo esc_html($entry['18'] ?? ''); ?> &rarr; <?php echo esc_html($entry['19'] ?? ''); ?>
                </div>

                <div class="booking-summary-block__row">
                    <strong>Transfer Date:</strong>
                    <?php echo esc_html($entry['1'] ?? ''); ?> at <?php echo esc_html($entry['3'] ?? ''); ?>
                </div>

                <?php if ($is_round_trip) : ?>
                    <div class="booking-summary-block__row">
                        <strong>Return Date:</strong>
                        <?php echo esc_html($entry['41'] ?? ''); ?> at <?php echo esc_html($entry['42'] ?? ''); ?>
                    </div>
                <?php endif; ?>

                <div class="booking-summary-block__row">
                    <strong>Passenger Names:</strong>
                    <?php echo nl2br(esc_html($entry['2'] ?? '')); ?>
                </div>

                <div class="booking-summary-block__row">
                    <strong>Number of Passengers:</strong>
                    <?php echo esc_html($entry['34'] ?? ''); ?>
                </div>

                <div class="booking-summary-block__row">
                    <strong>Email:</strong>
                    <?php echo esc_html($entry['6'] ?? ''); ?>
                </div>

                <div class="booking-summary-block__row booking-summary-block__row--total">
                    <strong>Cost:</strong>
                    $<?php echo esc_html(number_format((float) ($entry['22'] ?? 0), 2)); ?> excl. tax
                    &middot;
                    $<?php echo esc_html(number_format((float) ($entry['38'] ?? 0), 2)); ?> incl. tax
                </div>
            </div>

        <?php else : ?>

            <div class="booking-confirmation-page__missing">
                <p>
                    We couldn't find that booking. If you've just submitted a request, please check your
                    email for confirmation, or <a href="<?php echo esc_url(site_url('/flights')); ?>">start a new search</a>.
                </p>
            </div>

        <?php endif; ?>

    </div>
</section>

<?php while (have_posts()) : the_post(); ?>
    <?php the_content(); ?>
<?php endwhile; ?>

<?php get_footer(); ?>
