<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$preview_popup_image && !$hide_panel) {
    $description  = get_field('description');
    $logo         = get_field('logo');
    $button_field = get_field('button_link');
    $link         = $button_field['button_link'] ?? null;
    $colour       = $button_field['button_colour'] ?? 'white';
    $btn_class    = 'button' . ($colour !== 'gold' ? ' button--' . $colour : '');
    $platform     = get_field('platform') ?: 'tripadvisor';

    // Heading and button text are fixed per platform rather than editable —
    // keeps this consistent everywhere the block is used instead of relying
    // on whatever text an editor happens to type in. The badge SVG + label
    // is the fallback shown when no custom "Logo" image is uploaded (that
    // field still works the same for any platform).
    $platforms = [
        'tripadvisor' => [
            'label'   => 'TripAdvisor',
            'heading' => 'See Our Reviews On TripAdvisor',
            'button'  => 'See Reviews',
            'badge'   => '<svg width="34" height="34" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="7" cy="12" r="5.5" stroke="currentColor" stroke-width="2"/><circle cx="17" cy="12" r="5.5" stroke="currentColor" stroke-width="2"/><circle cx="7" cy="12" r="2" fill="currentColor"/><circle cx="17" cy="12" r="2" fill="currentColor"/></svg>',
        ],
        'facebook' => [
            'label'   => 'Facebook',
            'heading' => 'Visit Our Facebook Page',
            'button'  => 'View Facebook',
            'badge'   => '<svg width="34" height="34" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M13.5 21v-7h2.3l.35-2.7h-2.65V9.5c0-.78.22-1.32 1.34-1.32h1.43V5.77C15.98 5.7 15.17 5.65 14.23 5.65c-1.9 0-3.2 1.16-3.2 3.29V11.3H8.7V14h2.33v7h2.47z" fill="currentColor"/></svg>',
        ],
        'instagram' => [
            'label'   => 'Instagram',
            'heading' => 'Visit Our Instagram Page',
            'button'  => 'View Instagram',
            'badge'   => '<svg width="34" height="34" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="3" width="18" height="18" rx="5" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="4.2" stroke="currentColor" stroke-width="2"/><circle cx="17.4" cy="6.6" r="1.2" fill="currentColor"/></svg>',
        ],
    ];

    $config = $platforms[$platform] ?? $platforms['tripadvisor'];
?>

<section class="social-panel social-panel--<?php echo esc_attr($platform); ?> animate fade-up <?php echo $generic_block_settings_classes; ?>">
    <div class="container <?php echo $generic_container_class; ?>">
        <div class="social-panel__inner">

            <?php if ($logo) : ?>
                <img
                    class="social-panel__logo"
                    src="<?php echo esc_url(wp_get_attachment_image_url($logo, 'medium')); ?>"
                    alt="<?php echo esc_attr(get_post_meta($logo, '_wp_attachment_image_alt', true) ?: $config['label']); ?>"
                >
            <?php else : ?>
                <div class="social-panel__badge" aria-hidden="true">
                    <?php echo $config['badge']; ?>
                    <span><?php echo esc_html($config['label']); ?></span>
                </div>
            <?php endif; ?>

            <h2 class="social-panel__heading"><?php echo esc_html($config['heading']); ?></h2>

            <?php if ($description) : ?>
                <p class="social-panel__description"><?php echo esc_html($description); ?></p>
            <?php endif; ?>

            <?php if ($link && !empty($link['url'])) : ?>
                <a
                    class="<?php echo esc_attr($btn_class); ?>"
                    href="<?php echo esc_url($link['url']); ?>"
                    <?php if (!empty($link['target'])) : ?>target="<?php echo esc_attr($link['target']); ?>" rel="noopener noreferrer"<?php endif; ?>
                >
                    <?php echo esc_html($config['button']); ?>
                </a>
            <?php endif; ?>

        </div>
    </div>
</section>

<?php
}
?>
