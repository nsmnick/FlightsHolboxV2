<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$is_preview && !$hide_panel && !$preview_popup_image) {
    $content = get_field('content');
?>

    <section class="content content__example-text-panel <?php echo $generic_block_settings_classes ?>">
        <div class="container">
            <div class="wysiwyg-container">
                <?php echo $content; ?>
            </div>
        </div>
    </section>

<?php
}
?>
