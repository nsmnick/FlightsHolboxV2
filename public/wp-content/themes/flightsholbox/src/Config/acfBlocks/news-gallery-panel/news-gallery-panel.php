<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$preview_popup_image && !$hide_panel) {
    $heading = get_field('heading');
    $intro   = get_field('intro');

    // Support linking straight to a filtered view, e.g. a "See all Transfers
    // news" link elsewhere on the site pointing at ?cat=transfers.
    $category_id = 0;
    if (isset($_GET['cat'])) {
        $category = get_category_by_slug(sanitize_title(wp_unslash($_GET['cat'])));
        if ($category) {
            $category_id = $category->term_id;
        }
    }

    $config_data = [
        'defaultCategoryID' => $category_id,
    ];
?>

<section class="news-gallery-panel animate fade-up <?php echo $generic_block_settings_classes; ?>">
    <div class="container <?php echo $generic_container_class; ?>">

        <?php if ($heading) : ?>
            <h2 class="news-gallery-panel__heading"><?php echo esc_html($heading); ?></h2>
        <?php endif; ?>

        <?php if ($intro) : ?>
            <div class="news-gallery-panel__intro"><?php echo wp_kses_post($intro); ?></div>
        <?php endif; ?>

        <div id="vue-app" data-app="posts-gallery" data-config="<?php echo esc_attr(wp_json_encode($config_data)); ?>"></div>

    </div>
</section>

<?php
}
?>
