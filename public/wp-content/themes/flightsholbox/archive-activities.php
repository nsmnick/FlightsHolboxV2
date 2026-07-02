<?php get_header(); ?>

<section class="cpt-archive">
    <div class="cpt-archive__hero">
        <div class="cpt-archive__hero-overlay" aria-hidden="true"></div>
        <div class="cpt-archive__hero-content container">
            <h1 class="cpt-archive__title">Activities</h1>
        </div>
    </div>

    <div class="container">
        <div class="cpt-archive__grid">
            <?php while (have_posts()) : the_post();
                $hero_image = get_field('hero_image');
                $thumb_url  = $hero_image
                    ? wp_get_attachment_image_url($hero_image, 'medium_large')
                    : get_the_post_thumbnail_url(null, 'medium_large');
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
