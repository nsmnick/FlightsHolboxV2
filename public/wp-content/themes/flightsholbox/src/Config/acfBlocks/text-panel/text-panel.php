<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$hide_panel && !$preview_popup_image) {
    $content   = get_field('content');
    $alignment = get_field('text_alignment') ?: 'left';

    if (!$content) {
        return;
    }
?>

<section class="text-panel content text-panel--<?php echo esc_attr($alignment); ?> animate fade-in <?php echo $generic_block_settings_classes; ?>">
    <div class="container <?php echo $generic_container_class; ?>">
        <div class="wysiwyg-container">
            <?php echo $content; ?>
        </div>
    </div>
</section>

<?php
}
?>
