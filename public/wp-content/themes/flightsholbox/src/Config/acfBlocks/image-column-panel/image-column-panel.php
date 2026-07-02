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
    <div class="container">
        <div class="image-column-panel__grid image-column-panel__grid--cols-<?php echo $column_count; ?>">
            <?php foreach ($images as $row) : ?>
                <div class="image-column-panel__item">
                    <span class="image-column-panel__watercolor bgc-<?php echo esc_attr($border_colour); ?>" aria-hidden="true"></span>
                    <span class="image-column-panel__photo">
                        <?php echo Theme\Utils::get_image_html($row['image'], $column_count); ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php
}
?>
