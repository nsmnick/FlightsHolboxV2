<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$preview_popup_image && !$hide_panel) {
    $heading = get_field('heading');
    $rows    = get_field('rows') ?: [];
    $note    = get_field('note');

    if (!$rows) {
        echo '<p style="padding:2rem;opacity:0.4;font-style:italic;">No price rows added yet — edit the block to add rows.</p>';
        return;
    }

    // Column labels default to the aircraft/route wording this card was
    // built for, but stay editable so the same block can carry any 4-column
    // price table (e.g. vehicle/transfer pricing) without a template change.
    $col_1 = get_field('column_1_label') ?: 'Aircraft';
    $col_2 = get_field('column_2_label') ?: 'Passengers';
    $col_3 = get_field('column_3_label') ?: 'Route';
    $col_4 = get_field('column_4_label') ?: 'Price';
?>

<section class="price-table-panel <?php echo $generic_block_settings_classes; ?>">
     <div class="container <?php echo esc_attr($generic_container_class); ?>">
        <div class="price-table-panel__card">
            <?php if ($heading) : ?>
                <h2 class="price-table-panel__heading"><?php echo esc_html($heading); ?></h2>
            <?php endif; ?>

            <div class="price-table-panel__table-wrap">
                <table class="price-table-panel__table">
                    <thead>
                        <tr>
                            <th class="price-table-panel__col--label"><?php echo esc_html($col_1); ?></th>
                            <th class="price-table-panel__col--center"><?php echo esc_html($col_2); ?></th>
                            <th class="price-table-panel__col--center"><?php echo esc_html($col_3); ?></th>
                            <th class="price-table-panel__col--price"><?php echo esc_html($col_4); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row) :
                            $aircraft   = $row['aircraft'] ?? '';
                            $passengers = $row['passengers'] ?? '';
                            $route      = $row['route'] ?? '';
                            $price      = $row['price'] ?? '';
                            $tax_note   = $row['tax_note'] ?? '';
                            $price_type = $row['price_type'] ?? '';
                        ?>
                            <tr>
                                <td class="price-table-panel__col--label" data-label="<?php echo esc_attr($col_1); ?>"><?php echo esc_html($aircraft); ?></td>
                                <td class="price-table-panel__col--center" data-label="<?php echo esc_attr($col_2); ?>"><?php echo esc_html($passengers); ?></td>
                                <td class="price-table-panel__col--center price-table-panel__route" data-label="<?php echo esc_attr($col_3); ?>"><?php echo esc_html($route); ?></td>
                                <td class="price-table-panel__col--price" data-label="<?php echo esc_attr($col_4); ?>">
                                    <?php if ($price) : ?>
                                        <strong class="price-table-panel__amount"><?php echo esc_html($price); ?></strong>
                                    <?php endif; ?>
                                    <?php if ($tax_note) : ?>
                                        <span class="price-table-panel__tax"><?php echo esc_html($tax_note); ?></span>
                                    <?php endif; ?>
                                    <?php if ($price_type) : ?>
                                        <span class="price-table-panel__type"><?php echo esc_html($price_type); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($note) : ?>
                <div class="price-table-panel__note">
                    <span class="price-table-panel__note-icon" aria-hidden="true">i</span>
                    <p><?php echo nl2br(esc_html($note)); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
}
?>
