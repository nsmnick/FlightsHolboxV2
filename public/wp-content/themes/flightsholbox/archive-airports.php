<?php get_header();

$archive_hero_image = get_field('airports_archive_hero_image', 'option');
$archive_hero_url    = is_array($archive_hero_image)
    ? ($archive_hero_image['url'] ?? '')
    : ($archive_hero_image ? wp_get_attachment_image_url($archive_hero_image, 'full') : '');
?>

<section class="cpt-archive">
    <div class="cpt-archive__hero" <?php if ($archive_hero_url) : ?>style="background-image: url('<?php echo esc_url($archive_hero_url); ?>')"<?php endif; ?>>
        <div class="cpt-archive__hero-overlay" aria-hidden="true"></div>
        <canvas class="hero-panel__wave" aria-hidden="true"></canvas>
        <div class="cpt-archive__hero-content container">
            <h1 class="cpt-archive__title">Airports</h1>
        </div>
    </div>

    <div class="container">
        <div class="cpt-archive__grid">
            <?php while (have_posts()) : the_post();
                // Featured Image is the primary source now — falls back to
                // the old "Hero Image" ACF field only for posts that were
                // set up before this switch and don't have a featured image
                // assigned yet.
                $thumb_url = get_the_post_thumbnail_url(null, 'medium_large');

                if (!$thumb_url) {
                    $hero_image = get_field('hero_image');
                    $thumb_url  = !empty($hero_image['sizes']['medium_large'])
                        ? $hero_image['sizes']['medium_large']
                        : ($hero_image['url'] ?? '');
                }
            ?>
                <a href="<?php the_permalink(); ?>" class="cpt-card">
                    <div class="cpt-card__image" <?php if ($thumb_url) : ?>style="background-image: url('<?php echo esc_url($thumb_url); ?>')"<?php endif; ?>></div>
                    <div class="cpt-card__body">
                        <h2 class="cpt-card__title"><?php the_title(); ?></h2>
                        <span class="cpt-card__link">Find out more &rarr;</span>
                    </div>
                </a>
            <?php endwhile; ?>
        </div>

        <?php the_posts_pagination(); ?>
    </div>
</section>

<?php get_footer(); ?>
