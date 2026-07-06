<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$hide_panel && !$preview_popup_image) {
    $html = get_field('html');

    if (!$html) {
        return;
    }
?>

<section class="html-panel content animate fade-in <?php echo $generic_block_settings_classes; ?>">
    <div class="container <?php echo $generic_container_class; ?>">
        <?php echo $html; ?>
    </div>
</section>

<?php
}
?>
