<?php get_header(); ?>

<?php
$from_id   = isset($_GET['locations_from'])   ? (int) $_GET['locations_from']   : 0;
$to_id     = isset($_GET['locations_to'])     ? (int) $_GET['locations_to']     : 0;
$people_id = isset($_GET['number_of_people']) ? (int) $_GET['number_of_people'] : 0;

$from_term   = ($from_id   > 0) ? get_term($from_id,   'locations_from')   : null;
$to_term     = ($to_id     > 0) ? get_term($to_id,     'locations_to')     : null;
$people_term = ($people_id > 0) ? get_term($people_id, 'number_of_people') : null;

$searched = $from_id || $to_id || $people_id;

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
                            $one_way_base  = (float) get_field('price_one_way',   $price_post->ID);
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

                <?php else : ?>
                    <div class="prices-page__no-results">
                        <p>No prices found for that combination. Try adjusting your search or <a href="<?php echo esc_url(site_url('/transfers-price-list')); ?>">view all prices</a>.</p>
                    </div>
                <?php endif; ?>

            <?php else : ?>
                <div class="prices-page__prompt">
                    <p>Choose your departure, destination and number of people above to see prices.</p>
                    <a href="<?php echo esc_url(site_url('/transfers-price-list')); ?>" class="button">View all prices</a>
                </div>
            <?php endif; ?>

        </div>
    </div>

</div>

<?php get_footer(); ?>
