<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$preview_popup_image && !$hide_panel) {
    $heading        = get_field('heading');
    $phone          = get_field('phone');
    $whatsapp       = get_field('whatsapp');
    $email          = get_field('email');
    $address        = get_field('address');
    $maps_link      = get_field('maps_link');
    $booking_notice = get_field('booking_notice');
    $social_links   = get_field('social_links') ?: [];

    if (!$phone && !$whatsapp && !$email && !$address) {
        echo '<p style="padding:2rem;opacity:0.4;font-style:italic;">No contact details added yet — edit the block to add them.</p>';
        return;
    }
?>

<section class="contact-details-panel animate fade-up <?php echo $generic_block_settings_classes; ?>">
    <div class="container">

        <?php if ($heading) : ?>
            <h2 class="contact-details-panel__heading"><?php echo esc_html($heading); ?></h2>
        <?php endif; ?>

        <div class="contact-details-panel__methods">
            <?php if ($phone) : ?>
                <p>
                    <label>Mobile</label>
                    <a href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $phone)); ?>"><?php echo esc_html($phone); ?></a>
                </p>
            <?php endif; ?>

            <?php if ($whatsapp) : ?>
                <p>
                    <label>WhatsApp</label>
                    <a href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $whatsapp)); ?>"><?php echo esc_html($whatsapp); ?></a>
                </p>
            <?php endif; ?>

            <?php if ($email) : ?>
                <p>
                    <label>E-mail</label>
                    <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
                </p>
            <?php endif; ?>
        </div>

        <?php if ($address) : ?>
            <p class="contact-details-panel__address">
                <label>Office Address</label>
                <span><?php echo nl2br(esc_html($address)); ?></span>
                <?php if ($maps_link && !empty($maps_link['url'])) : ?>
                    <a class="contact-details-panel__maps-link" target="_blank" rel="noopener" href="<?php echo esc_url($maps_link['url']); ?>">
                        <?php echo esc_html($maps_link['title'] ?: 'View on Google Maps'); ?>
                    </a>
                <?php endif; ?>
            </p>
        <?php endif; ?>

        <?php if ($booking_notice) : ?>
            <p class="contact-details-panel__notice"><?php echo esc_html($booking_notice); ?></p>
        <?php endif; ?>

        <?php
        // Each repeater row can carry any combination of these four platform URLs.
        $platform_labels = [
            'facebook' => 'Facebook',
            'instagram' => 'Instagram',
            'trip_advisor' => 'TripAdvisor',
            'whatsapp' => 'WhatsApp',
        ];
        $social_urls = [];
        foreach ($social_links as $row) {
            foreach ($platform_labels as $platform => $label) {
                if (!empty($row[$platform])) {
                    $social_urls[$platform] = $row[$platform];
                }
            }
        }
        ?>
        <?php if ($social_urls) : ?>
            <div class="contact-details-panel__socials">
                <p class="contact-details-panel__socials-heading">Follow us on social media</p>
                <div class="social-channels social-channels--row">
                    <?php foreach ($social_urls as $platform => $url) :
                        $icon_class = $platform === 'trip_advisor' ? 'tripadvisor' : $platform;
                    ?>
                        <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr($platform_labels[$platform]); ?>">
                            <span class="social-icon social-icon--<?php echo esc_attr($icon_class); ?>"></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php
}
?>
