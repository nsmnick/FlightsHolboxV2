<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$preview_popup_image && !$hide_panel) {
    $heading  = get_field('heading');
    $intro    = get_field('intro');
    $col_one  = get_field('column_one');
    $col_two  = get_field('column_two');
    $pullout  = get_field('pullout_summary');

    if (!$col_one && !$col_two) {
        echo '<p style="padding:2rem;opacity:0.4;font-style:italic;">No column content added yet — edit the block to add content.</p>';
        return;
    }
?>

<section class="two-column-panel <?php echo $generic_block_settings_classes; ?>">
    <div class="container">

        <?php if ($heading) : ?>
            <h2 class="two-column-panel__heading"><?php echo esc_html($heading); ?></h2>
        <?php endif; ?>

        <?php if ($intro) : ?>
            <div class="two-column-panel__intro"><?php echo wp_kses_post($intro); ?></div>
        <?php endif; ?>

        <div class="two-column-panel__columns">
            <?php if ($col_one) : ?>
                <div class="two-column-panel__col"><?php echo wp_kses_post($col_one); ?></div>
            <?php endif; ?>
            <?php if ($col_two) : ?>
                <div class="two-column-panel__col"><?php echo wp_kses_post($col_two); ?></div>
            <?php endif; ?>
        </div>

        <?php if ($pullout) : ?>
            <p class="two-column-panel__pullout"><?php echo esc_html($pullout); ?></p>
        <?php endif; ?>

    </div>
</section>

<?php
}
?>
