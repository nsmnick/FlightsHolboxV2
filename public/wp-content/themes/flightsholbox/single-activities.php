<?php get_header(); ?>

<?php while (have_posts()) : the_post();
    $hero_image   = get_field('hero_image');
    $hero_url     = is_array($hero_image) ? ($hero_image['url'] ?? '') : ($hero_image ? wp_get_attachment_image_url($hero_image, 'full') : '');
    $introduction = get_field('introduction');
    $sections     = get_field('content_sections') ?: [];
?>

<article class="cpt-single cpt-single--activity">

    <div class="cpt-single__hero" <?php if ($hero_url) : ?>style="background-image: url('<?php echo esc_url($hero_url); ?>')"<?php endif; ?>>
        <div class="cpt-single__hero-overlay" aria-hidden="true"></div>
        <div class="cpt-single__hero-content container">
            <p class="cpt-single__eyebrow">
                <a href="<?php echo esc_url(get_post_type_archive_link('activities')); ?>">Activities</a>
                <span aria-hidden="true">/</span>
                <?php the_title(); ?>
            </p>
            <h1 class="cpt-single__title"><?php the_title(); ?></h1>
        </div>
    </div>

    <?php if ($introduction) : ?>
    <div class="cpt-single__intro-section">
        <div class="container cpt-single__intro-container">
            <div class="cpt-single__intro content">
                <?php echo wp_kses_post($introduction); ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($sections)) : ?>
    <div class="cpt-single__body">
        <div class="container cpt-single__body-container">
            <?php foreach ($sections as $i => $section) :
                $heading = $section['section_heading'] ?? '';
                $content = $section['section_content'] ?? '';
                if (!$heading && !$content) continue;
            ?>
                <div class="cpt-single__section">
                    <div class="cpt-single__section-aside">
                        <span class="cpt-single__section-number"><?php echo str_pad($i + 1, 2, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    <div class="cpt-single__section-main">
                        <?php if ($heading) : ?>
                            <h2 class="cpt-single__section-heading"><?php echo esc_html($heading); ?></h2>
                        <?php endif; ?>
                        <?php if ($content) : ?>
                            <div class="cpt-single__section-content content">
                                <?php echo wp_kses_post($content); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="cpt-single__footer">
        <div class="container">
            <a href="<?php echo esc_url(get_post_type_archive_link('activities')); ?>" class="button">
                &larr; All Activities
            </a>
        </div>
    </div>

</article>

<?php endwhile; ?>

<?php get_footer(); ?>
