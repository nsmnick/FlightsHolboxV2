<?php get_header(); ?>

<?php while (have_posts()) : the_post();
    // Featured Image is the primary source now — falls back to the old
    // "Hero Image" ACF field only for posts that were set up before this
    // switch and don't have a featured image assigned yet.
    $hero_url = get_the_post_thumbnail_url(get_the_ID(), 'full');

    if (!$hero_url) {
        $hero_image = get_field('hero_image');
        $hero_url   = is_array($hero_image) ? ($hero_image['url'] ?? '') : ($hero_image ? wp_get_attachment_image_url($hero_image, 'full') : '');
    }
    $introduction = get_field('introduction');
    $sections     = get_field('content_sections') ?: [];
?>

<article class="cpt-single cpt-single--airport">

    <div class="cpt-single__hero" <?php if ($hero_url) : ?>style="background-image: url('<?php echo esc_url($hero_url); ?>')"<?php endif; ?>>
        <div class="cpt-single__hero-overlay" aria-hidden="true"></div>
        <canvas class="hero-panel__wave" aria-hidden="true"></canvas>
        <div class="cpt-single__hero-content container">
            <p class="cpt-single__eyebrow">
                <a href="<?php echo esc_url(get_post_type_archive_link('airports')); ?>">Airports</a>
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

    <?php if (get_the_content()) : ?>
        <div class="cpt-single__blocks">
            <?php the_content(); ?>
        </div>
    <?php endif; ?>

    <div class="cpt-single__footer">
        <div class="container">
            <a href="<?php echo esc_url(get_post_type_archive_link('airports')); ?>" class="button">
                &larr; All Airports
            </a>
        </div>
    </div>

</article>

<?php endwhile; ?>

<?php get_footer(); ?>
