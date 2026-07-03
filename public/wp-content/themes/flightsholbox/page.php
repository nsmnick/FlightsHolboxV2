<?php get_header(); ?>

<?php while (have_posts()) : the_post(); ?>

    <?php if (has_blocks()) : ?>

        <?php the_content(); ?>

    <?php else : ?>

        <div class="page-content">
            <div class="page-content__header">
                <div class="container">
                    <h1 class="page-content__title"><?php the_title(); ?></h1>
                </div>
            </div>
            <div class="page-content__body">
                <div class="container">
                    <div class="page-content__prose">
                        <?php the_content(); ?>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>

<?php endwhile; ?>

<?php get_footer(); ?>
