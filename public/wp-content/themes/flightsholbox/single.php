<?php get_header(); ?>

<?php while (have_posts()) : the_post();
    $hero_url = get_the_post_thumbnail_url(get_the_ID(), 'full');

    $categories       = get_the_category();
    $primary_category = $categories[0] ?? null;

    // Falls back to the homepage if no static "Posts page" is set under
    // Settings > Reading — there's no dedicated news archive template yet,
    // just the News Gallery Panel block wherever it's placed.
    $posts_page_id = get_option('page_for_posts');
    $news_link     = $posts_page_id ? get_permalink($posts_page_id) : home_url('/');
?>

<article class="cpt-single cpt-single--post">

    <div class="cpt-single__hero" <?php if ($hero_url) : ?>style="background-image: url('<?php echo esc_url($hero_url); ?>')"<?php endif; ?>>
        <div class="cpt-single__hero-overlay" aria-hidden="true"></div>
        <div class="cpt-single__hero-content container">
            <p class="cpt-single__eyebrow">
                <a href="<?php echo esc_url($news_link); ?>">Latest News</a>
                <?php if ($primary_category) : ?>
                    <span aria-hidden="true">/</span>
                    <a href="<?php echo esc_url(get_category_link($primary_category)); ?>"><?php echo esc_html($primary_category->name); ?></a>
                <?php endif; ?>
            </p>
            <h1 class="cpt-single__title"><?php the_title(); ?></h1>
        </div>
    </div>

    <div class="cpt-single__intro-section">
        <div class="container cpt-single__intro-container">
            <div class="cpt-single__intro content">
                <?php the_content(); ?>
            </div>
        </div>
    </div>

    <div class="cpt-single__footer">
        <div class="container">
            <a href="<?php echo esc_url($news_link); ?>" class="button">
                &larr; All Latest News
            </a>
        </div>
    </div>

</article>

<?php endwhile; ?>

<?php get_footer(); ?>
