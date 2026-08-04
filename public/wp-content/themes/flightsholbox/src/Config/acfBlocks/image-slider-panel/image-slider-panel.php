<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$preview_popup_image && !$hide_panel) {
    $heading = get_field('slider_heading');
    $intro   = get_field('slider_intro');
    $images  = get_field('slider_images') ?: [];

    if (!$images) {
        echo '<p style="padding:2rem;opacity:0.4;font-style:italic;">No images added yet — edit the block to add slider images.</p>';
        return;
    }

    // Duplicate images until there are at least 6, for a clean Swiper loop —
    // same convention as the Airport Slider Panel.
    $slides = $images;
    if (count($slides) < 6) {
        while (count($slides) < 6) {
            $slides = array_merge($slides, $images);
        }
    }
?>

<section class="image-slider-panel <?php echo $generic_block_settings_classes; ?>">
     <div class="container <?php echo esc_attr($generic_container_class); ?>">
        <?php if ($heading || $intro) : ?>
            <div class="image-slider-panel__intro">
                <?php if ($heading) : ?>
                    <h2 class="image-slider-panel__heading"><?php echo esc_html($heading); ?></h2>
                <?php endif; ?>
                <?php if ($intro) : ?>
                    <div class="image-slider-panel__subheading"><?php echo wp_kses_post($intro); ?></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="image-slider-panel__track">
        <div class="image-slider-panel-swiper">
            <div class="swiper-wrapper image-slider-panel__wrapper">
                <?php $slide_count = count($slides); ?>
                <?php foreach ($slides as $i => $slide_image) :
                    // Swiper's loop clones the last slide to sit in front of
                    // slide 0 so it can display as "previous" from the very
                    // first paint — see the Airport Slider Panel for the
                    // same fix. Rotate the reveal order by one slot to
                    // match: previous, then current, then next.
                    $reveal_rank = ($i + 1) % $slide_count;
                ?>
                    <div class="swiper-slide image-slider-panel__slide animate slide-left" style="animation-delay: <?php echo esc_attr($reveal_rank * 0.08); ?>s;">
                        <div class="image-slider-panel__card">
                            <?php echo wp_get_attachment_image($slide_image, 'full'); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="image-slider-panel__nav">
            <button class="image-slider-panel__btn image-slider-panel__btn--prev" aria-label="Previous slide">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><polyline points="15 18 9 12 15 6"/></svg>
            </button>
            <button class="image-slider-panel__btn image-slider-panel__btn--next" aria-label="Next slide">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
        </div>
    </div>
</section>

<?php
}
?>
