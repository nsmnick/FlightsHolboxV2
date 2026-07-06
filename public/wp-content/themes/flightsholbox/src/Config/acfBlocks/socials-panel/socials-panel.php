<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$preview_popup_image && !$hide_panel) {
    $heading = get_field('heading');
    $socials = get_field('socials') ?: [];

    $socials = array_values(array_filter($socials, function ($row) {
        return !empty($row['platform']) && !empty($row['url']);
    }));

    if (!$socials) {
        echo '<p style="padding:2rem;opacity:0.4;font-style:italic;">No social links added yet — edit the block to add them.</p>';
        return;
    }

    $platform_labels = [
        'facebook' => 'Facebook',
        'instagram' => 'Instagram',
        'whatsapp' => 'WhatsApp',
        'email' => 'Email',
        'tripadvisor' => 'TripAdvisor',
        'x' => 'X',
    ];
?>

<section class="socials-panel animate fade-in <?php echo $generic_block_settings_classes; ?>">
    <div class="container">

        <?php if ($heading) : ?>
            <h2 class="socials-panel__heading"><?php echo esc_html($heading); ?></h2>
        <?php endif; ?>

        <div class="social-channels social-channels--row socials-panel__row">
            <?php foreach ($socials as $row) :
                $platform = $row['platform'];
                $value    = trim($row['url']);
                $label    = $platform_labels[$platform] ?? ucfirst($platform);

                // WhatsApp needs a phone number (→ wa.me link) and Email needs
                // an address (→ mailto:) — everything else is a plain URL.
                if ($platform === 'whatsapp') {
                    $href = 'https://wa.me/' . preg_replace('/[^0-9]/', '', $value);
                } elseif ($platform === 'email') {
                    $href = 'mailto:' . $value;
                } else {
                    $href = $value;
                }
            ?>
                <a
                    href="<?php echo esc_url($href); ?>"
                    aria-label="<?php echo esc_attr($label); ?>"
                    <?php if ($platform !== 'email') : ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>
                >
                    <span class="social-icon social-icon--<?php echo esc_attr($platform); ?>"></span>
                </a>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<?php
}
?>
