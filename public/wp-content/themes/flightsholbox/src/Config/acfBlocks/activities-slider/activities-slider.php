<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$is_preview && !$hide_panel && !$preview_popup_image) {
    $slider_heading = get_field('slider_heading');
    $slider_intro   = get_field('slider_intro');
    $slider_items   = get_field('slider_items') ?: [];

    // Drop rows where no post has been selected yet, so an in-progress
    // repeater row never renders as a blank slide.
    $slider_items = array_values(array_filter($slider_items, function ($slide) {
        return !empty($slide['slide_post']);
    }));

    if (!$slider_items) {
        return;
    }

    // Duplicate slides until we have at least 7 for a clean Swiper loop
    $slides = $slider_items;
    if (count($slides) < 7) {
        while (count($slides) < 7) {
            $slides = array_merge($slides, $slider_items);
        }
    }
?>

<section class="activities-slider <?php echo $generic_block_settings_classes; ?>">

    <?php if ($slider_heading || $slider_intro) : ?>
    <div class="activities-slider__intro">
        <?php if ($slider_heading) : ?>
            <h2 class="activities-slider__heading"><?php echo esc_html($slider_heading); ?></h2>
        <?php endif; ?>
        <?php if ($slider_intro) : ?>
            <p class="activities-slider__subheading"><?php echo esc_html($slider_intro); ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="activities-slider__track">
        <div class="activities-swiper swiper">
            <div class="swiper-wrapper activities-slider__wrapper">
                <?php foreach ($slides as $slide) :
                    $post_obj = $slide['slide_post'] ?? null;
                    $post_id  = $post_obj ? $post_obj->ID : 0;

                    $hero_image = $post_id ? get_field('hero_image', $post_id) : null;
                    if (is_array($hero_image)) {
                        $img_id = $hero_image['ID'] ?? $hero_image['id'] ?? 0;
                    } else {
                        $img_id = (int) $hero_image;
                    }

                    $heading   = $slide['slide_heading'] ?? '';
                    $desc      = $slide['slide_description'] ?? '';
                    $link      = $slide['button']['button_link'] ?? null;
                    $colour    = $slide['button']['button_colour'] ?? 'gold';
                    $btn_class = 'button' . ($colour !== 'gold' ? ' button--' . $colour : '');
                ?>
                    <div class="swiper-slide activities-slider__slide">
                        <div class="activities-slider__card">
                            <?php if ($img_id) : ?>
                                <div class="activities-slider__card-image">
                                    <?php echo wp_get_attachment_image($img_id, 'medium_large', false, ['loading' => 'lazy']); ?>
                                </div>
                            <?php endif; ?>
                            <div class="activities-slider__card-body">
                                <?php if ($heading) : ?>
                                    <p class="activities-slider__card-heading"><?php echo esc_html($heading); ?></p>
                                <?php endif; ?>
                                <?php if ($post_id) : ?>
                                    <h3 class="activities-slider__card-name"><?php echo esc_html(get_the_title($post_id)); ?></h3>
                                <?php endif; ?>
                                <?php if ($desc) : ?>
                                    <p class="activities-slider__card-desc"><?php echo nl2br(esc_html($desc)); ?></p>
                                <?php endif; ?>
                                <?php if ($link && !empty($link['url'])) : ?>
                                    <a
                                        class="<?php echo esc_attr($btn_class); ?> activities-slider__card-btn"
                                        href="<?php echo esc_url($link['url']); ?>"
                                        <?php if (!empty($link['target'])) : ?>target="<?php echo esc_attr($link['target']); ?>" rel="noopener noreferrer"<?php endif; ?>
                                    >
                                        <?php echo esc_html($link['title'] ?: 'Find out more'); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="activities-slider__nav">
            <button class="activities-slider__btn activities-slider__btn--prev" aria-label="Previous slide">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><polyline points="15 18 9 12 15 6"/></svg>
            </button>
            <button class="activities-slider__btn activities-slider__btn--next" aria-label="Next slide">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
        </div>
    </div>

</section>

<?php
}
?>
