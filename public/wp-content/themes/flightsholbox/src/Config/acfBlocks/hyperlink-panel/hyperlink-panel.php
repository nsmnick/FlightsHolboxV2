<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$preview_popup_image && !$hide_panel) {
    $heading = get_field('heading');

    $post_id      = get_the_ID();
    $table_blocks = $post_id
        ? Theme\Utils::find_blocks_by_name(parse_blocks(get_post_field('post_content', $post_id)), 'acf/table-panel')
        : [];

    $links = [];
    foreach ($table_blocks as $table_block) {
        $table_heading = $table_block['attrs']['data']['table_heading'] ?? '';
        if (!$table_heading) {
            continue;
        }

        $links[] = [
            'label'  => $table_heading,
            'anchor' => Theme\Utils::get_table_panel_anchor(
                $table_heading,
                $table_block['attrs']['anchor'] ?? '',
                $table_block['attrs']['id'] ?? ''
            ),
        ];
    }

    if (!$links) {
        echo '<p style="padding:2rem;opacity:0.4;font-style:italic;">No Table Panels with a heading were found on this page yet — add one, then this block will list it automatically.</p>';
        return;
    }
?>

<section class="hyperlink-panel animate fade-up <?php echo $generic_block_settings_classes; ?>">
    <div class="container <?php echo $generic_container_class; ?>">

        <?php if ($heading) : ?>
            <h2 class="hyperlink-panel__heading"><?php echo esc_html($heading); ?></h2>
        <?php endif; ?>

        <ul class="hyperlink-panel__list">
            <?php foreach ($links as $link) : ?>
                <li class="hyperlink-panel__item">
                    <a class="hyperlink-panel__link" href="#<?php echo esc_attr($link['anchor']); ?>">
                        <?php echo esc_html($link['label']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

    </div>
</section>

<?php
}
?>
