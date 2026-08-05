<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$preview_popup_image && !$hide_panel) {
    $text_content     = get_field('text_content');
    $image            = get_field('image');
    $use_slider       = get_field('use_slider');
    $slider_images    = $use_slider ? (get_field('slider_images') ?: []) : [];
    $image_position   = get_field('image_position') ?: 'right';
    $more_info_label  = get_field('more_info_label');
    $more_info_content = get_field('more_info_content');

    // A second button is optional — if it's left empty in the CMS, only
    // the first one renders and nothing about the layout changes.
    $buttons = array_filter(array_map(function ($button) {
        $link = $button['button_link'] ?? null;
        if (!$button || !$link || empty($link['url'])) {
            return null;
        }

        $colour = $button['button_colour'] ?? 'white';
        return [
            'link'  => $link,
            'class' => 'button' . ($colour !== 'gold' ? ' button--' . $colour : ''),
        ];
    }, [get_field('button'), get_field('button_2')]));

    if (!$text_content && !$image && !$slider_images) {
        return;
    }

    $columns_class = 'text-and-image-panel__columns';
    if ($image_position === 'left') {
        $columns_class .= ' text-and-image-panel__columns--image-left';
    }
?>

<section class="text-and-image-panel <?php echo $generic_block_settings_classes; ?>">
     <div class="container <?php echo esc_attr($generic_container_class); ?>">
        <div class="<?php echo esc_attr($columns_class); ?>">

            <div class="text-and-image-panel__text">
                <?php if ($text_content) : ?>
                    <div class="wysiwyg-container"><?php echo wp_kses_post($text_content); ?></div>
                <?php endif; ?>

                <?php if ($buttons) : ?>
                    <div class="text-and-image-panel__buttons">
                        <?php foreach ($buttons as $btn) : ?>
                            <a
                                class="<?php echo esc_attr($btn['class']); ?> text-and-image-panel__button"
                                href="<?php echo esc_url($btn['link']['url']); ?>"
                                <?php if (!empty($btn['link']['target'])) : ?>target="<?php echo esc_attr($btn['link']['target']); ?>" rel="noopener noreferrer"<?php endif; ?>
                            >
                                <?php echo esc_html($btn['link']['title'] ?: 'Find out more'); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($more_info_content) : ?>
                    <details class="text-and-image-panel__more-info">
                        <summary class="text-and-image-panel__more-info-toggle">
                            <?php echo esc_html($more_info_label ?: 'More Information'); ?>
                        </summary>
                        <div class="text-and-image-panel__more-info-content">
                            <?php echo wp_kses_post($more_info_content); ?>
                        </div>
                    </details>
                <?php endif; ?>
            </div>

            <?php if ($slider_images) : ?>
                <div class="text-and-image-panel__image">
                    <div class="text-and-image-slider__slides-wrapper swiper">
                        <div class="text-and-image-slider__slides swiper-wrapper">
                            <?php foreach ($slider_images as $slide_image) : ?>
                                <div class="text-and-image-slider__slide swiper-slide">
                                    <?php echo wp_get_attachment_image($slide_image, 'full'); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php if (count($slider_images) > 1) : ?>
                        <span class="text-and-image-slider__counter" aria-hidden="true">
                            <span class="text-and-image-slider__counter-current">1</span>
                            /
                            <span class="text-and-image-slider__counter-total"><?php echo count($slider_images); ?></span>
                        </span>

                        <button type="button" class="text-and-image-slider__arrow text-and-image-slider__arrow--prev" aria-label="View previous image">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                        </button>
                        <button type="button" class="text-and-image-slider__arrow text-and-image-slider__arrow--next" aria-label="View next image">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                        </button>

                        <div class="text-and-image-slider__pagination" aria-label="Choose an image"></div>
                    <?php endif; ?>
                </div>
            <?php elseif ($image) : ?>
                <div class="text-and-image-panel__image">
                    <?php echo wp_get_attachment_image($image, 'full'); ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
</section>

<?php
}
?>
