<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$preview_popup_image && !$hide_panel) {
    $items = get_field('trust_items') ?: [];

    if (!$items) {
        echo '<p style="padding:2rem;opacity:0.4;font-style:italic;">No trust items added yet — edit the block to add some.</p>';
        return;
    }
?>

<section class="trust-panel animate fade-up <?php echo $generic_block_settings_classes; ?>">
    <div class="trust-panel__container">
        <div class="trust-panel__grid">
            <?php foreach ($items as $item) :
                $icon     = $item['icon'] ?? null;
                $title    = $item['title'] ?? '';
                $subtitle = $item['subtitle'] ?? '';

                // The Icon field's Return Format may be set to either
                // "Image Array" or "Image ID" in the CMS — handle both so
                // rendering doesn't break depending on which was picked.
                $icon_url = '';
                if (is_array($icon)) {
                    $icon_url = $icon['sizes']['thumbnail'] ?? $icon['url'] ?? '';
                } elseif ($icon) {
                    $icon_url = wp_get_attachment_image_url($icon, 'thumbnail') ?: '';
                }
            ?>
                <div class="trust-panel__card">
                    <?php if ($icon_url) : ?>
                        <span class="trust-panel__icon" aria-hidden="true">
                            <img
                                src="<?php echo esc_url($icon_url); ?>"
                                alt=""
                                loading="lazy"
                                width="32"
                                height="32"
                            >
                        </span>
                    <?php endif; ?>

                    <?php if ($title) : ?>
                        <span class="trust-panel__title"><?php echo esc_html($title); ?></span>
                    <?php endif; ?>

                    <?php if ($subtitle) : ?>
                        <span class="trust-panel__subtitle"><?php echo esc_html($subtitle); ?></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php
}
?>
