<?php get_header(); ?>

<?php
$from_id   = isset($_GET['locations_from'])   ? (int) $_GET['locations_from']   : 0;
$to_id     = isset($_GET['locations_to'])     ? (int) $_GET['locations_to']     : 0;
$people_id = isset($_GET['number_of_people']) ? (int) $_GET['number_of_people'] : 0;

$from_term   = ($from_id   > 0) ? get_term($from_id,   'locations_from')   : null;
$to_term     = ($to_id     > 0) ? get_term($to_id,     'locations_to')     : null;
$people_term = ($people_id > 0) ? get_term($people_id, 'number_of_people') : null;

$searched = $from_id || $to_id || $people_id;

$booking_price_id  = isset($_GET['price_id'])  ? (int) $_GET['price_id']  : 0;
$booking_trip_type = (isset($_GET['trip_type']) && in_array($_GET['trip_type'], ['one_way', 'round_trip']))
    ? $_GET['trip_type'] : '';

$tax_query = ['relation' => 'AND'];
if ($from_id)   $tax_query[] = ['taxonomy' => 'locations_from',   'field' => 'term_id', 'terms' => $from_id];
if ($to_id)     $tax_query[] = ['taxonomy' => 'locations_to',     'field' => 'term_id', 'terms' => $to_id];
if ($people_id) $tax_query[] = ['taxonomy' => 'number_of_people', 'field' => 'term_id', 'terms' => $people_id];

$results = [];
if ($searched) {
    $q = new WP_Query([
        'post_type'      => 'prices',
        'posts_per_page' => -1,
        'tax_query'      => $tax_query,
    ]);
    $results = $q->posts;
    wp_reset_postdata();
}

function fh_format_price(float $base, float $tax_rate): array {
    $inc = $base * (1 + $tax_rate / 100);
    return [
        'ex'  => '$' . number_format($base, 2),
        'inc' => '$' . number_format($inc, 2),
    ];
}

function fh_book_url(int $price_id, string $trip_type, int $from_id, int $to_id, int $people_id): string {
    return esc_url(site_url('/flights') . '?' . http_build_query(array_filter([
        'locations_from'   => $from_id   ?: null,
        'locations_to'     => $to_id     ?: null,
        'number_of_people' => $people_id ?: null,
        'price_id'         => $price_id,
        'trip_type'        => $trip_type,
    ])) . '#booking-form');
}
?>

<div class="prices-page">

    <div class="prices-page__hero">
        <div class="prices-page__hero-overlay" aria-hidden="true"></div>
        <div class="container prices-page__hero-content">
            <h1 class="prices-page__title">Flight Prices</h1>
            <p class="prices-page__subtitle">Search for your route below</p>
        </div>
    </div>

    <div class="prices-page__search-bar">
        <div class="container">
            <form role="search" method="GET" class="search-form search-form--inline" action="<?php echo esc_url(site_url('/flights')); ?>">
                <div class="search-form__group search-form__group--from">
                    <label class="search-form__label" for="locations_from">FROM</label>
                    <?php echo fh_get_categories_dropdown('locations_from', [], 'Any', $from_id); ?>
                </div>
                <div class="search-form__group search-form__group--to">
                    <label class="search-form__label" for="locations_to">TO</label>
                    <?php echo fh_get_categories_dropdown('locations_to', [], 'Any', $to_id); ?>
                </div>
                <div class="search-form__group search-form__group--people">
                    <label class="search-form__label" for="number_of_people">PEOPLE</label>
                    <?php echo fh_get_categories_dropdown('number_of_people', [], 'Any', $people_id); ?>
                </div>
                <div class="search-form__group search-form__group--button">
                    <button class="search-form__button" type="submit">SEARCH</button>
                </div>
            </form>
        </div>
    </div>

    <div class="prices-page__body">
        <div class="container prices-page__container">

            <?php if ($searched) : ?>

                <?php
                $parts = [];
                if ($from_term   && !is_wp_error($from_term))   $parts[] = 'From: <strong>' . esc_html($from_term->name)   . '</strong>';
                if ($to_term     && !is_wp_error($to_term))     $parts[] = 'To: <strong>'   . esc_html($to_term->name)     . '</strong>';
                if ($people_term && !is_wp_error($people_term)) $parts[] = '<strong>'        . esc_html($people_term->name) . '</strong>';
                ?>
                <?php if ($parts) : ?>
                    <p class="prices-page__summary"><?php echo implode(' &nbsp;&middot;&nbsp; ', $parts); ?></p>
                <?php endif; ?>

                <?php if (!empty($results)) : ?>
                    <div class="price-results">
                        <?php foreach ($results as $price_post) :
                            $one_way_base  = (float) get_field('price_one_way',    $price_post->ID);
                            $rt_base       = (float) get_field('price_round_trip', $price_post->ID);
                            $tax_rate      = (float) (get_field('federal_tax_rate', $price_post->ID) ?: 16);
                            $price_note    = get_field('price_note', $price_post->ID);

                            $one_way = $one_way_base ? fh_format_price($one_way_base, $tax_rate) : null;
                            $rt      = $rt_base      ? fh_format_price($rt_base,      $tax_rate) : null;

                            $post_from   = get_the_terms($price_post->ID, 'locations_from');
                            $post_to     = get_the_terms($price_post->ID, 'locations_to');
                            $post_people = get_the_terms($price_post->ID, 'number_of_people');
                            $route_from  = (!empty($post_from)   && !is_wp_error($post_from))   ? $post_from[0]->name   : '';
                            $route_to    = (!empty($post_to)     && !is_wp_error($post_to))     ? $post_to[0]->name     : '';
                            $route_ppl   = (!empty($post_people) && !is_wp_error($post_people)) ? $post_people[0]->name : '';
                        ?>
                            <div class="price-card">
                                <div class="price-card__route">
                                    <?php if ($route_from) : ?>
                                        <span class="price-card__location"><?php echo esc_html($route_from); ?></span>
                                        <span class="price-card__arrow" aria-hidden="true">&rarr;</span>
                                    <?php endif; ?>
                                    <?php if ($route_to) : ?>
                                        <span class="price-card__location"><?php echo esc_html($route_to); ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($route_ppl) : ?>
                                    <p class="price-card__people"><?php echo esc_html($route_ppl); ?></p>
                                <?php endif; ?>

                                <div class="price-card__breakdown">
                                    <?php if ($one_way) : ?>
                                        <div class="price-card__fare price-card__fare--one-way">
                                            <div class="price-card__fare-header">
                                                <span class="price-card__fare-icon" aria-hidden="true">&rarr;</span>
                                                <span class="price-card__fare-label">One Way</span>
                                            </div>
                                            <div class="price-card__fare-row">
                                                <span class="price-card__fare-tax-label">Excluding tax</span>
                                                <span class="price-card__fare-amount"><?php echo $one_way['ex']; ?></span>
                                            </div>
                                            <div class="price-card__fare-row price-card__fare-row--inc">
                                                <span class="price-card__fare-tax-label">Including tax (<?php echo $tax_rate; ?>%)</span>
                                                <span class="price-card__fare-amount price-card__fare-amount--inc"><?php echo $one_way['inc']; ?></span>
                                            </div>
                                            <a href="<?php echo fh_book_url($price_post->ID, 'one_way', $from_id, $to_id, $people_id); ?>" class="price-card__book-btn">
                                                Book One Way
                                            </a>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($rt) : ?>
                                        <div class="price-card__fare price-card__fare--return">
                                            <div class="price-card__fare-header">
                                                <span class="price-card__fare-icon" aria-hidden="true">&#8644;</span>
                                                <span class="price-card__fare-label">Return</span>
                                            </div>
                                            <div class="price-card__fare-row">
                                                <span class="price-card__fare-tax-label">Excluding tax</span>
                                                <span class="price-card__fare-amount"><?php echo $rt['ex']; ?></span>
                                            </div>
                                            <div class="price-card__fare-row price-card__fare-row--inc">
                                                <span class="price-card__fare-tax-label">Including tax (<?php echo $tax_rate; ?>%)</span>
                                                <span class="price-card__fare-amount price-card__fare-amount--inc"><?php echo $rt['inc']; ?></span>
                                            </div>
                                            <a href="<?php echo fh_book_url($price_post->ID, 'round_trip', $from_id, $to_id, $people_id); ?>" class="price-card__book-btn">
                                                Book Return Trip
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <p class="price-card__tax-explainer">
                                    <strong>Excluding tax</strong> is the fare charged by Flights Holbox.
                                    <strong>Including tax</strong> adds the <?php echo $tax_rate; ?>% Mexican federal departure tax collected at the airport, so you know the full amount you'll pay.
                                </p>

                                <?php if ($price_note) : ?>
                                    <p class="price-card__note"><?php echo esc_html($price_note); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($booking_price_id && $booking_trip_type) :
                        $book_post = get_post($booking_price_id);
                        if ($book_post && $book_post->post_type === 'prices') :
                            $book_froms  = get_the_terms($booking_price_id, 'locations_from');
                            $book_tos    = get_the_terms($booking_price_id, 'locations_to');
                            $book_people = get_the_terms($booking_price_id, 'number_of_people');
                            $book_from   = (!empty($book_froms)  && !is_wp_error($book_froms))  ? $book_froms[0]->name  : '';
                            $book_to     = (!empty($book_tos)    && !is_wp_error($book_tos))    ? $book_tos[0]->name    : '';
                            $book_ppl    = (!empty($book_people) && !is_wp_error($book_people)) ? $book_people[0]->name : '';
                            $book_base   = $booking_trip_type === 'one_way'
                                ? (float) get_field('price_one_way',    $booking_price_id)
                                : (float) get_field('price_round_trip', $booking_price_id);
                            $book_tax    = (float) (get_field('federal_tax_rate', $booking_price_id) ?: 16);
                            $book_inc    = round($book_base * (1 + $book_tax / 100), 2);
                            $book_label  = $booking_trip_type === 'one_way' ? 'One Way' : 'Round Trip';

                            $field_values = [
                                'location_from' => $book_from,
                                'location_to'   => $book_to,
                                'no_of_people'  => $book_ppl,
                                'cost_usd'      => $book_base,
                                'cost_inc_tax'  => $book_inc,
                                'return_trip'   => $book_label,
                            ];
                    ?>
                        <div id="booking-form" class="prices-page__booking">
                            <div class="prices-page__booking-header">
                                <h2 class="prices-page__booking-title">Request Availability</h2>
                                <p class="prices-page__booking-summary">
                                    <span class="prices-page__booking-tag"><?php echo esc_html($book_label); ?></span>
                                    <span><?php echo esc_html($book_from); ?> &rarr; <?php echo esc_html($book_to); ?></span>
                                    &middot; <span><?php echo esc_html($book_ppl); ?></span>
                                    &middot; <span class="prices-page__booking-price">$<?php echo number_format($book_inc, 2); ?> inc. tax</span>
                                </p>
                            </div>
                            <div class="prices-page__booking-form">
                                <?php gravity_form(1, false, false, false, $field_values, true); ?>
                            </div>
                        </div>
                    <?php endif; endif; ?>

                <?php else : ?>
                    <div class="prices-page__no-results">
                        <p>No prices found for that combination. Try adjusting your search or <a href="<?php echo esc_url(site_url('/flight-prices')); ?>">view all prices</a>.</p>
                    </div>
                <?php endif; ?>

            <?php else : ?>
                <div class="prices-page__prompt">
                    <p>Choose your departure, destination and number of people above to see prices.</p>
                    <a href="<?php echo esc_url(site_url('/flight-prices')); ?>" class="button">View all prices</a>
                </div>
            <?php endif; ?>

        </div>
    </div>

</div>

<?php
// Anything added to this page in the block editor (FAQ, testimonials,
// features, another booking form, etc.) renders here, below the search
// bar and results.
the_content();
?>

<?php get_footer(); ?>
