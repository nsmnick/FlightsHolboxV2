<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$is_preview && !$hide_panel && !$preview_popup_image) {
    $items = get_field('trust_items') ?: [];

    if (!$items) {
        echo '<p style="padding:2rem;opacity:0.4;font-style:italic;">No trust items added yet — edit the block to add some.</p>';
        return;
    }
?>

    <section class="trust-panel <?php echo $generic_block_settings_classes; ?>">
        <div class="trust-panel__container animate fade-up">
            <div class="trust-panel__grid">
                <?php foreach ($items as $item) :
                    $icon     = $item['icon'] ?? null;
                    $title    = $item['title'] ?? '';
                    $subtitle = $item['subtitle'] ?? '';
                ?>
                    <div class="trust-panel__card">
                        <?php if ($icon) : ?>
                            <span class="trust-panel__icon" aria-hidden="true">
                                <?php echo wp_get_attachment_image($icon, 'full'); ?>
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