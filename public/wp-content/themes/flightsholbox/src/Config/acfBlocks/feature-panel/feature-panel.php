<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$preview_popup_image && !$hide_panel) {
    $features = get_field('features') ?: [];

    if (!$features) {
        echo '<p style="padding:2rem;opacity:0.4;font-style:italic;">No features added yet — edit the block to add features.</p>';
        return;
    }

    $count = min(count($features), 3);
    $count_class = ['', 'one', 'two', 'three'][$count];
?>

<section class="feature-panel feature-panel--<?php echo $count_class; ?> <?php echo $generic_block_settings_classes; ?>">
    <?php foreach (array_slice($features, 0, 3) as $feature) :
        $image_id = $feature['feature_image'] ?? null;
        $heading  = $feature['feature_heading'] ?? '';
        $link     = $feature['button']['link'] ?? null;
        $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'large') : '';
    ?>
        <div
            class="feature-panel__item"
            <?php if ($image_url) : ?>style="background-image: url('<?php echo esc_url($image_url); ?>')"<?php endif; ?>
        >
            <div class="feature-panel__overlay" aria-hidden="true"></div>
            <div class="feature-panel__content">
                <?php if ($heading) : ?>
                    <h3 class="feature-panel__heading"><?php echo esc_html($heading); ?></h3>
                <?php endif; ?>
                <?php if ($link && !empty($link['url'])) : ?>
                    <a
                        class="feature-panel__btn btn btn--primary"
                        href="<?php echo esc_url($link['url']); ?>"
                        <?php if (!empty($link['target'])) : ?>target="<?php echo esc_attr($link['target']); ?>" rel="noopener noreferrer"<?php endif; ?>
                    >
                        <?php echo esc_html($link['title'] ?: 'Find out more'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</section>

<?php
}
?>
