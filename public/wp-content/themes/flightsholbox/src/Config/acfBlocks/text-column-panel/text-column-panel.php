<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$preview_popup_image && !$hide_panel) {
    $heading = get_field('heading');
    $columns = get_field('columns') ?: [];

    $columns = array_values(array_filter($columns, function ($col) {
        return !empty($col['column_content']);
    }));

    if (!$columns) {
        echo '<p style="padding:2rem;opacity:0.4;font-style:italic;">No columns added yet — edit the block to add text columns.</p>';
        return;
    }

    $count = min(count($columns), 3);
?>

<section class="text-column-panel <?php echo $generic_block_settings_classes; ?>">
    <div class="container">

        <?php if ($heading) : ?>
            <h2 class="text-column-panel__heading"><?php echo esc_html($heading); ?></h2>
        <?php endif; ?>

        <div class="text-column-panel__columns text-column-panel__columns--<?php echo $count; ?>">
            <?php foreach ($columns as $col) : ?>
                <div class="text-column-panel__col"><?php echo wp_kses_post($col['column_content']); ?></div>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<?php
}
?>
