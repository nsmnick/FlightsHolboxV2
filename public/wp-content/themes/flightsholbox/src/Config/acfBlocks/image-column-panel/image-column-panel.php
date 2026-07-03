<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$is_preview && !$hide_panel && !$preview_popup_image) {
    $images = get_field('images');
    $border_colour = get_field('border_colour') ?: 'gold';

    if (!$images) {
        return;
    }

    $images = array_values(array_filter($images, fn($row) => !empty($row['image'])));
    $column_count = count($images);

    if (!$column_count) {
        return;
    }
?>

<section class="image-column-panel <?php echo $generic_block_settings_classes; ?>">
    <div class="container <?php echo $generic_container_class; ?>">
        <div class="image-column-panel__grid image-column-panel__grid--cols-<?php echo $column_count; ?>">
            <?php foreach ($images as $row) :
                $heading      = $row['main_heading'] ?? '';
                $meta_heading = $row['meta_heading']  ?? '';
                $paragraph    = $row['paragraph']     ?? '';
                $has_text     = $heading || $meta_heading || $paragraph;
            ?>
                <div class="image-column-panel__item<?php echo $has_text ? ' has-text' : ''; ?>"
                     <?php if ($has_text) : ?>role="button" tabindex="0" aria-expanded="false"<?php endif; ?>>
                    <span class="image-column-panel__watercolor bgc-<?php echo esc_attr($border_colour); ?>" aria-hidden="true"></span>
                    <span class="image-column-panel__photo">
                        <?php echo Theme\Utils::get_image_html($row['image'], $column_count); ?>
                    </span>
                    <?php if ($has_text) : ?>
                        <span class="image-column-panel__badge" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" fill="currentColor" width="13" height="13"><path d="M0 55.2V426c0 12.2 9.9 22 22 22c6.3 0 12.4-2.7 16.6-7.5L121.2 346l58.1 116.3c7.9 15.8 27.1 22.2 42.9 14.3s22.2-27.1 14.3-42.9L179.8 320H297.9c12.2 0 22.1-9.9 22.1-22.1c0-6.3-2.7-12.3-7.4-16.5L38.6 37.9C34.3 34.1 28.9 32 23.2 32C10.4 32 0 42.4 0 55.2z"/></svg>
                        </span>
                        <div class="image-column-panel__text-overlay">
                            <?php if ($heading) : ?>
                                <h3 class="image-column-panel__overlay-heading"><?php echo esc_html($heading); ?></h3>
                            <?php endif; ?>
                            <?php if ($meta_heading) : ?>
                                <p class="image-column-panel__overlay-meta"><?php echo esc_html($meta_heading); ?></p>
                            <?php endif; ?>
                            <?php if ($paragraph) : ?>
                                <p class="image-column-panel__overlay-paragraph"><?php echo nl2br(esc_html($paragraph)); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php
}
?>
