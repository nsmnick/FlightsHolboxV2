<?php get_header(); ?>

<?php
// Pull all published prices and index them for fast lookup
$all_prices = new WP_Query([
    'post_type'      => 'prices',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
]);

// $price_index[$from_id][$to_id][$people_id] = ['price' => ..., 'note' => ...]
$price_index = [];
if ($all_prices->have_posts()) {
    foreach ($all_prices->posts as $p) {
        $froms  = get_the_terms($p->ID, 'locations_from');
        $tos    = get_the_terms($p->ID, 'locations_to');
        $people = get_the_terms($p->ID, 'number_of_people');

        if (empty($froms) || is_wp_error($froms)) continue;
        if (empty($tos)   || is_wp_error($tos))   continue;

        $from_id   = $froms[0]->term_id;
        $to_id     = $tos[0]->term_id;
        $people_id = (!empty($people) && !is_wp_error($people)) ? $people[0]->term_id : 0;

        $price_index[$from_id][$to_id][$people_id] = [
            'price' => get_field('price', $p->ID),
            'note'  => get_field('price_note', $p->ID) ?: 'per person',
        ];
    }
}
wp_reset_postdata();

// All taxonomy terms for headers/rows
$from_terms   = get_terms(['taxonomy' => 'locations_from',   'hide_empty' => true, 'orderby' => 'name']);
$to_terms     = get_terms(['taxonomy' => 'locations_to',     'hide_empty' => true, 'orderby' => 'name']);
$people_terms = get_terms(['taxonomy' => 'number_of_people', 'hide_empty' => false, 'orderby' => 'name']);
?>

<div class="prices-page prices-page--list">

    <div class="prices-page__hero">
        <div class="prices-page__hero-overlay" aria-hidden="true"></div>
        <div class="container prices-page__hero-content">
            <h1 class="prices-page__title">Transfer Price List</h1>
            <p class="prices-page__subtitle">All available routes and prices</p>
        </div>
    </div>

    <div class="prices-page__body">
        <div class="container prices-page__container">

            <div class="prices-page__cta">
                <p>Looking for a specific route? Use our <a href="<?php echo esc_url(site_url('/flights')); ?>">price search</a>.</p>
            </div>

            <?php if (!empty($from_terms) && !is_wp_error($from_terms) && !empty($people_terms) && !is_wp_error($people_terms)) : ?>

                <?php foreach ($from_terms as $from) :
                    // Check this from has any prices before rendering a section
                    if (empty($price_index[$from->term_id])) continue;
                ?>
                    <div class="price-table-group">
                        <h2 class="price-table-group__heading">
                            <span class="price-table-group__label">From</span>
                            <?php echo esc_html($from->name); ?>
                        </h2>

                        <div class="price-table-wrap">
                            <table class="price-table">
                                <thead>
                                    <tr>
                                        <th class="price-table__destination-head">Destination</th>
                                        <?php foreach ($people_terms as $people) : ?>
                                            <th><?php echo esc_html($people->name); ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($to_terms as $to) :
                                        // Skip rows with no data for this from→to
                                        if (empty($price_index[$from->term_id][$to->term_id])) continue;
                                        $row = $price_index[$from->term_id][$to->term_id];
                                    ?>
                                        <tr>
                                            <td class="price-table__destination"><?php echo esc_html($to->name); ?></td>
                                            <?php foreach ($people_terms as $people) :
                                                $entry = $row[$people->term_id] ?? null;
                                            ?>
                                                <td class="price-table__price">
                                                    <?php if ($entry && $entry['price'] !== '' && $entry['price'] !== null) : ?>
                                                        <span class="price-table__amount">£<?php echo number_format((float) $entry['price'], 2); ?></span>
                                                    <?php else : ?>
                                                        <span class="price-table__unavailable">—</span>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>

            <?php else : ?>
                <div class="prices-page__no-results">
                    <p>No prices have been added yet. <a href="<?php echo esc_url(admin_url('post-new.php?post_type=prices')); ?>">Add some prices</a> in the admin.</p>
                </div>
            <?php endif; ?>

        </div>
    </div>

</div>

<?php get_footer(); ?>
