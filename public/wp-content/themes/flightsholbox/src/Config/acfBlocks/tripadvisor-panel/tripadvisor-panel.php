<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$preview_popup_image && !$hide_panel) {
    $heading      = get_field('heading');
    $description  = get_field('description');
    $logo         = get_field('logo');
    $button_field = get_field('button_link');
    $link         = $button_field['button_link'] ?? null;
    $colour       = $button_field['button_colour'] ?? 'white';
    $btn_class    = 'button' . ($colour !== 'gold' ? ' button--' . $colour : '');

    if (!$heading) {
        echo '<p style="padding:2rem;opacity:0.4;font-style:italic;">No heading added yet — edit the block to set it up.</p>';
        return;
    }
?>

<section class="tripadvisor-panel animate fade-up <?php echo $generic_block_settings_classes; ?>">
    <div class="container <?php echo $generic_container_class; ?>">
        <div class="tripadvisor-panel__inner">

            <?php if ($logo) : ?>
                <img
                    class="tripadvisor-panel__logo"
                    src="<?php echo esc_url(wp_get_attachment_image_url($logo, 'medium')); ?>"
                    alt="<?php echo esc_attr(get_post_meta($logo, '_wp_attachment_image_alt', true) ?: 'TripAdvisor'); ?>"
                >
            <?php else : ?>
                <div class="tripadvisor-panel__badge" aria-hidden="true">
                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="7" cy="12" r="5.5" stroke="currentColor" stroke-width="2"/>
                        <circle cx="17" cy="12" r="5.5" stroke="currentColor" stroke-width="2"/>
                        <circle cx="7" cy="12" r="2" fill="currentColor"/>
                        <circle cx="17" cy="12" r="2" fill="currentColor"/>
                    </svg>
                    <span>TripAdvisor</span>
                </div>
            <?php endif; ?>

            <h2 class="tripadvisor-panel__heading"><?php echo esc_html($heading); ?></h2>

            <?php if ($description) : ?>
                <p class="tripadvisor-panel__description"><?php echo esc_html($description); ?></p>
            <?php endif; ?>

            <?php if ($link && !empty($link['url'])) : ?>
                <a
                    class="<?php echo esc_attr($btn_class); ?>"
                    href="<?php echo esc_url($link['url']); ?>"
                    <?php if (!empty($link['target'])) : ?>target="<?php echo esc_attr($link['target']); ?>" rel="noopener noreferrer"<?php endif; ?>
                >
                    <?php echo esc_html($link['title'] ?: 'View TripAdvisor'); ?>
                </a>
            <?php endif; ?>

        </div>
    </div>
</section>

<?php
}
?>
