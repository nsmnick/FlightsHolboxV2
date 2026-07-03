<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$is_preview && !$hide_panel && !$preview_popup_image) {
    $slides  = get_field('hero_slides');
    $heading = get_field('hero_heading');
    $intro   = get_field('hero_intro');
    $logo    = get_field('hero_logo');
?>

<section class="hero-panel">
    <div class="hero-slider__slides-wrapper swiper">
        <div class="hero-slider__slides swiper-wrapper">
            <?php if ($slides) : ?>
                <?php foreach ($slides as $slide) : ?>
                    <div
                        class="hero-slider__slide swiper-slide"
                        style="background-image: url('<?php echo esc_url(wp_get_attachment_image_url($slide['slide_image'], 'full')); ?>')"
                        role="img"
                        aria-label="<?php echo esc_attr(get_post_meta($slide['slide_image'], '_wp_attachment_image_alt', true)); ?>"
                    ></div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="hero-panel__overlay" aria-hidden="true"></div>

    <div class="hero-panel__content">
        <?php if ($logo) : ?>
            <a href="<?php echo esc_url(site_url()); ?>" class="hero-panel__logo-link">
                <img
                    class="hero-panel__logo"
                    src="<?php echo esc_url(wp_get_attachment_image_url($logo, 'full')); ?>"
                    alt="<?php echo esc_attr(get_post_meta($logo, '_wp_attachment_image_alt', true) ?: get_bloginfo('name')); ?>"
                >
            </a>
        <?php endif; ?>

        <?php if ($heading) : ?>
            <h1 class="hero-panel__heading"><?php echo esc_html($heading); ?></h1>
        <?php endif; ?>

        <?php if ($intro) : ?>
            <p class="hero-panel__intro"><?php echo esc_html($intro); ?></p>
        <?php endif; ?>
    </div>
</section>

<?php
}
?>
