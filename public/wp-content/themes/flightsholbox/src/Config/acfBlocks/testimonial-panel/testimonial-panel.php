<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$preview_popup_image && !$hide_panel) {
    $embed_code = get_field('embed_code');

    if (!$embed_code) {
        echo '<p style="padding:2rem;opacity:0.4;font-style:italic;">No embed code added yet — edit the block to paste your widget code.</p>';
        return;
    }
?>

<section class="testimonial-panel <?php echo $generic_block_settings_classes; ?>">
    <div class="container <?php echo $generic_container_class; ?>">
        <?php echo do_shortcode($embed_code); ?>
    </div>
</section>

<?php
}
?>
