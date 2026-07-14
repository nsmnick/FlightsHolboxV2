<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$preview_popup_image && !$hide_panel) {
    $heading      = get_field('table_heading');
    $intro        = get_field('table_intro');
    $column_1     = get_field('column_1_label');
    $column_1_sub = get_field('column_1_sublabel');
    $column_2     = get_field('column_2_label');
    $column_2_sub = get_field('column_2_sublabel');
    $rows         = get_field('rows') ?: [];

    if (!$rows) {
        echo '<p style="padding:2rem;opacity:0.4;font-style:italic;">No table rows added yet — edit the block to add rows.</p>';
        return;
    }

    // Every Table Panel gets a stable anchor id — whatever's set in the block's
    // own HTML anchor field, or failing that a slug from its heading — so a
    // Hyperlink Panel elsewhere on the page can always jump straight to it.
    $table_anchor = Theme\Utils::get_table_panel_anchor($heading ?: '', $block['anchor'] ?? '', $block['id'] ?? '');
?>

<section id="<?php echo esc_attr($table_anchor); ?>" class="table-panel animate fade-up <?php echo $generic_block_settings_classes; ?>">
    <div class="container <?php echo $generic_container_class; ?>">

        <?php if ($heading) : ?>
            <h2 class="table-panel__heading"><?php echo esc_html($heading); ?></h2>
        <?php endif; ?>

        <?php if ($intro) : ?>
            <div class="table-panel__intro"><?php echo wp_kses_post($intro); ?></div>
        <?php endif; ?>

        <div class="table-panel__wrap">
            <table class="table-panel__table">
                <thead>
                    <tr>
                        <th class="table-panel__row-head"></th>
                        <th>
                            <?php echo esc_html($column_1); ?>
                            <?php if ($column_1_sub) : ?>
                                <span class="table-panel__col-sub"><?php echo esc_html($column_1_sub); ?></span>
                            <?php endif; ?>
                        </th>
                        <th>
                            <?php echo esc_html($column_2); ?>
                            <?php if ($column_2_sub) : ?>
                                <span class="table-panel__col-sub"><?php echo esc_html($column_2_sub); ?></span>
                            <?php endif; ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row) :
                        $row_label  = $row['row_label'] ?? '';
                        $value_1    = $row['column_1_value'] ?? '';
                        $value_2    = $row['column_2_value'] ?? '';
                        $plane_info = $row['row_plane_info'] ?? '';
                    ?>
                        <tr>
                            <td class="table-panel__row-label">
                                <?php echo esc_html($row_label); ?>
                                <?php if ($plane_info) : ?>
                                    <details class="table-panel__place-info">
                                        <summary class="table-panel__place-info-toggle">Plane Info</summary>
                                        <div class="table-panel__place-info-content"><?php echo nl2br(esc_html($plane_info)); ?></div>
                                    </details>
                                <?php endif; ?>
                            </td>
                            <td class="table-panel__value"><?php echo esc_html($value_1); ?></td>
                            <td class="table-panel__value"><?php echo esc_html($value_2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</section>

<?php
}
?>
