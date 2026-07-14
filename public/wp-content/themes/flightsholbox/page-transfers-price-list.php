<?php get_header(); ?>

<?php
// Build price index: $index[$from][$to][$people] = ['one_way' => ..., 'round_trip' => ..., 'tax_rate' => ...]
$all_prices = new WP_Query([
    'post_type'      => 'prices',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
]);

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

        $one_way_base = (float) get_field('price_one_way',    $p->ID);
        $rt_base      = (float) get_field('price_round_trip', $p->ID);
        $tax_rate     = (float) (get_field('federal_tax_rate', $p->ID) ?: 16);
        $note         = get_field('price_note', $p->ID);
        $place_info   = get_field('place_info', $p->ID);

        $price_index[$from_id][$to_id][$people_id] = [
            'one_way_ex'  => $one_way_base ? '$' . number_format($one_way_base, 2) : null,
            'one_way_inc' => $one_way_base ? '$' . number_format($one_way_base * (1 + $tax_rate / 100), 2) : null,
            'rt_ex'       => $rt_base      ? '$' . number_format($rt_base, 2) : null,
            'rt_inc'      => $rt_base      ? '$' . number_format($rt_base * (1 + $tax_rate / 100), 2) : null,
            'tax_rate'    => $tax_rate,
            'note'        => $note,
            'place_info'  => $place_info,
        ];
    }
}
wp_reset_postdata();

$from_terms   = get_terms(['taxonomy' => 'locations_from',   'hide_empty' => true,  'orderby' => 'name']);
$to_terms     = get_terms(['taxonomy' => 'locations_to',     'hide_empty' => true,  'orderby' => 'name']);
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
                    if (empty($price_index[$from->term_id])) continue;
                ?>
                    <div class="price-table-group">
                        <h2 class="price-table-group__heading">
                            <span class="price-table-group__label">From</span>
                            <?php echo esc_html($from->name); ?>
                        </h2>

                        <?php foreach (['one_way' => 'One Way', 'round_trip' => 'Round Trip'] as $type => $type_label) :
                            // Check whether any prices of this type exist for this from
                            $has_type = false;
                            foreach ($price_index[$from->term_id] as $to_prices) {
                                foreach ($to_prices as $entry) {
                                    if ($entry[$type . '_ex']) { $has_type = true; break 2; }
                                }
                            }
                            if (!$has_type) continue;
                        ?>
                            <h3 class="price-table-group__type"><?php echo $type_label; ?></h3>

                            <div class="price-table-wrap">
                                <table class="price-table">
                                    <thead>
                                        <tr>
                                            <th class="price-table__destination-head">Destination</th>
                                            <?php foreach ($people_terms as $people) : ?>
                                                <th colspan="2"><?php echo esc_html($people->name); ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                        <tr class="price-table__subhead">
                                            <th></th>
                                            <?php foreach ($people_terms as $people) : ?>
                                                <th class="price-table__tax-head">Ex. tax</th>
                                                <th class="price-table__tax-head price-table__tax-head--inc">Inc. tax</th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($to_terms as $to) :
                                            if (empty($price_index[$from->term_id][$to->term_id])) continue;
                                            $row = $price_index[$from->term_id][$to->term_id];
                                        ?>
                                            <tr>
                                                <td class="price-table__destination"><?php echo esc_html($to->name); ?></td>
                                                <?php foreach ($people_terms as $people) :
                                                    $entry = $row[$people->term_id] ?? null;
                                                    $ex    = $entry ? $entry[$type . '_ex']  : null;
                                                    $inc   = $entry ? $entry[$type . '_inc'] : null;
                                                ?>
                                                    <td class="price-table__price">
                                                        <?php if ($ex) : ?>
                                                            <span class="price-table__amount"><?php echo $ex; ?></span>
                                                        <?php else : ?>
                                                            <span class="price-table__unavailable">—</span>
                                                        <?php endif; ?>
                                                        <?php if ($entry && $entry['place_info']) : ?>
                                                            <details class="price-table__place-info">
                                                                <summary class="price-table__place-info-toggle">Plane Info</summary>
                                                                <div class="price-table__place-info-content"><?php echo nl2br(esc_html($entry['place_info'])); ?></div>
                                                            </details>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="price-table__price price-table__price--inc">
                                                        <?php if ($inc) : ?>
                                                            <span class="price-table__amount price-table__amount--inc"><?php echo $inc; ?></span>
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
                        <?php endforeach; ?>

                    </div>
                <?php endforeach; ?>

            <?php else : ?>
                <div class="prices-page__no-results">
                    <p>No prices have been added yet.</p>
                </div>
            <?php endif; ?>

        </div>
    </div>

</div>

<?php
// Anything added to this page in the block editor (FAQ, testimonials,
// features, another booking form, etc.) renders here, below the price tables.
the_content();
?>

<?php get_footer(); ?>
