<?php get_header(); ?>

<?php while (have_posts()) : the_post();
    $hero_image  = get_field('hero_image');
    $hero_url    = $hero_image ? wp_get_attachment_image_url($hero_image, 'full') : '';
    $introduction = get_field('introduction');
    $sections    = get_field('content_sections') ?: [];
?>

<article class="cpt-single cpt-single--airport">

    <div class="cpt-single__hero" <?php if ($hero_url) : ?>style="background-image: url('<?php echo esc_url($hero_url); ?>')"<?php endif; ?>>
        <div class="cpt-single__hero-overlay" aria-hidden="true"></div>
        <div class="cpt-single__hero-content container">
            <h1 class="cpt-single__title"><?php the_title(); ?></h1>
        </div>
    </div>

    <div class="cpt-single__body container">

        <?php if ($introduction) : ?>
            <div class="cpt-single__intro content">
                <?php echo wp_kses_post($introduction); ?>
            </div>
        <?php endif; ?>

        <?php foreach ($sections as $section) :
            $heading = $section['section_heading'] ?? '';
            $content = $section['section_content'] ?? '';
            if (!$heading && !$content) continue;
        ?>
            <div class="cpt-single__section">
                <?php if ($heading) : ?>
                    <h2 class="cpt-single__section-heading"><?php echo esc_html($heading); ?></h2>
                <?php endif; ?>
                <?php if ($content) : ?>
                    <div class="cpt-single__section-content content">
                        <?php echo wp_kses_post($content); ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <div class="cpt-single__back">
            <a href="<?php echo esc_url(get_post_type_archive_link('airports')); ?>" class="btn btn--navy">
                &larr; All Airports
            </a>
        </div>

    </div>

</article>

<?php endwhile; ?>

<?php get_footer(); ?>
