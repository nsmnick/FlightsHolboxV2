<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$preview_popup_image && !$hide_panel) {
    $heading = get_field('heading');
    $intro   = get_field('introduction');

    // Each row's "button" sub-field is a clone of the shared "Component -
    // Link" field group (button_link + button_colour) — same clone used by
    // feature-panel and text-and-image-panel, so button colours/behaviour
    // stay consistent across the site. Rows without a link filled in yet
    // (still being edited) are dropped rather than rendered empty.
    $buttons = array_filter(array_map(function ($row) {
        $button = $row['button'] ?? null;
        $link   = $button['button_link'] ?? null;
        if (!$link || empty($link['url'])) {
            return null;
        }

        $colour = $button['button_colour'] ?? 'gold';
        return [
            'link'  => $link,
            'class' => 'button' . ($colour !== 'gold' ? ' button--' . $colour : ''),
        ];
    }, get_field('buttons') ?: []));

    if (!$buttons) {
        echo '<p style="padding:2rem;opacity:0.4;font-style:italic;">No buttons added yet — edit the block to add up to three.</p>';
        return;
    }

    $buttons = array_slice($buttons, 0, 3);
    $count_modifier = [1 => '', 2 => ' button-panel__row--two', 3 => ' button-panel__row--three'][count($buttons)] ?? '';
?>

<section class="button-panel content <?php echo $generic_block_settings_classes; ?>">
     <div class="container <?php echo esc_attr($generic_container_class); ?>">
        <?php if ($heading) : ?>
            <h2 class="button-panel__heading"><?php echo esc_html($heading); ?></h2>
        <?php endif; ?>

        <?php if ($intro) : ?>
            <div class="button-panel__intro"><?php echo wp_kses_post($intro); ?></div>
        <?php endif; ?>

        <div class="button-panel__row<?php echo esc_attr($count_modifier); ?>">
            <?php foreach ($buttons as $btn) : ?>
                <a
                    class="<?php echo esc_attr($btn['class']); ?> button-panel__button"
                    href="<?php echo esc_url($btn['link']['url']); ?>"
                    <?php if (!empty($btn['link']['target'])) : ?>target="<?php echo esc_attr($btn['link']['target']); ?>" rel="noopener noreferrer"<?php endif; ?>
                >
                    <?php echo esc_html($btn['link']['title'] ?: 'Find out more'); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php
}
?>
