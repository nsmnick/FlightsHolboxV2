<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$hide_panel && !$preview_popup_image) {
    $content   = get_field('content');
    $alignment = get_field('text_alignment') ?: 'left';

    if (!$content) {
        return;
    }

    // The card (background/rounded corners) is deliberately kept separate
    // from the section's own tp-/bp- spacing classes below — those control
    // the gap between this panel and its neighbours on the page, while the
    // card classes size/decorate only the boxed content area inside.
    $top_padding    = $generic_block_settings['top_padding'] ?? 'default';
    $bottom_padding = $generic_block_settings['bottom_padding'] ?? 'default';
    $decoration     = $generic_block_settings['panel_decoration'] ?? 'none';
    $bg_colour      = $generic_block_settings['background_colour'] ?? '';
    $heading_colour = $generic_block_settings['heading_colour'] ?? '';

    $section_classes = trim(
        ($top_padding !== 'default' ? ' tp-' . $top_padding : '') .
        ($bottom_padding !== 'default' ? ' bp-' . $bottom_padding : '')
    );

    $card_classes = trim(
        ($decoration !== 'none' ? ' ' . $decoration : '') .
        ($bg_colour ? ' bgc-' . $bg_colour : '') .
        ($heading_colour ? ' htc-' . $heading_colour : '')
    );
?>

<section class="text-panel content text-panel--<?php echo esc_attr($alignment); ?> <?php echo esc_attr($section_classes); ?>">
    <div class="container animate fade-up <?php echo esc_attr($generic_container_class); ?>">
        <div class="text-panel__card <?php echo esc_attr($card_classes); ?>">
            <div class="wysiwyg-container">
                <?php echo $content; ?>
            </div>
        </div>
    </div>
</section>

<?php
}
?>
