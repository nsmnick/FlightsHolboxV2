<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$preview_popup_image && !$hide_panel) {
    $heading = get_field('heading');
    $review_limit = (int) (get_field('review_display_count') ?: 5);
    $review_limit = max(1, min(5, $review_limit));

    $data = \Theme\Config\GoogleReviews::get_data();

    $rating_value = $data['rating'] ?? 0;
    $review_count = $data['review_count'] ?? 0;
    $google_url = $data['google_url'] ?? '';
    $service_name = $data['name'] ?? get_bloginfo('name');

    $reviews = array_values(array_filter(array_slice($data['reviews'] ?? [], 0, $review_limit), function ($review) {
        return !empty($review['author_name']) && !empty($review['text']);
    }));
?>

<section class="testimonial-panel <?php echo $generic_block_settings_classes; ?>">
    <div class="container">

        <?php if ($heading) : ?>
            <h2 class="testimonial-panel__title"><?php echo esc_html($heading); ?></h2>
        <?php endif; ?>

        <?php if ($rating_value && $review_count) : ?>
            <div class="testimonial-panel__summary">
                <span class="stars" style="--rating: <?php echo esc_attr(min(100, max(0, ($rating_value / 5) * 100))); ?>%" aria-hidden="true"></span>
                <span class="testimonial-panel__summary-text">
                    <?php echo esc_html(number_format_i18n($rating_value, 1)); ?> out of 5 &mdash;
                    based on <?php echo esc_html($review_count); ?> Google reviews
                </span>
                <?php if ($google_url) : ?>
                    <a class="testimonial-panel__summary-link" href="<?php echo esc_url($google_url); ?>" target="_blank" rel="noopener">
                        Read all our reviews on Google
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($reviews) : ?>
            <div class="testimonial-panel__grid">
                <?php foreach ($reviews as $review) :
                    $name = $review['author_name'];
                    $text = $review['text'];
                    $rating = (int) ($review['rating'] ?? 5);
                    $photo = $review['profile_photo_url'] ?? '';
                ?>
                    <blockquote class="testimonial-card">
                        <span class="stars stars--small" style="--rating: <?php echo esc_attr(($rating / 5) * 100); ?>%" aria-hidden="true"></span>
                        <p class="testimonial-card__text">&ldquo;<?php echo esc_html($text); ?>&rdquo;</p>
                        <footer class="testimonial-card__footer">
                            <?php if ($photo) : ?>
                                <img class="testimonial-card__avatar-img" src="<?php echo esc_url($photo); ?>" alt="" width="36" height="36" loading="lazy" referrerpolicy="no-referrer">
                            <?php else : ?>
                                <span class="testimonial-card__avatar" aria-hidden="true"><?php echo esc_html(mb_substr($name, 0, 1)); ?></span>
                            <?php endif; ?>
                            <span class="testimonial-card__name"><?php echo esc_html($name); ?></span>
                        </footer>
                    </blockquote>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <p style="opacity:0.4; font-style:italic;">No Google reviews synced yet — check the Google Place ID and API key are set, or wait for the next automatic refresh.</p>
        <?php endif; ?>

    </div>
</section>

<?php
    // Review / AggregateRating structured data — sourced entirely from the
    // live Google Places response, never fabricated or hand-edited.
    if ($rating_value && $review_count) {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => $service_name,
            'provider' => [
                '@type' => 'Organization',
                '@id' => home_url('/') . '#organization',
                'name' => get_bloginfo('name'),
            ],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => (float) $rating_value,
                'reviewCount' => (int) $review_count,
                'bestRating' => 5,
                'worstRating' => 1,
            ],
        ];

        if ($reviews) {
            $schema['review'] = array_map(function ($review) {
                return [
                    '@type' => 'Review',
                    'author' => [
                        '@type' => 'Person',
                        'name' => $review['author_name'],
                    ],
                    'reviewRating' => [
                        '@type' => 'Rating',
                        'ratingValue' => (int) ($review['rating'] ?? 5),
                        'bestRating' => 5,
                        'worstRating' => 1,
                    ],
                    'reviewBody' => $review['text'],
                    'datePublished' => gmdate('Y-m-d', $review['time'] ?? time()),
                ];
            }, $reviews);
        }
?>
        <script type="application/ld+json"><?php echo wp_json_encode($schema); ?></script>
<?php
    }
}
?>
