<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$hide_panel && !$preview_popup_image) {
    $map_image = get_field('map_image');

    if (!$map_image) {
        return;
    }

    // Walk every priced route and group by from+to term pair, collecting
    // one fare row per number_of_people option. Locations without a pin
    // position set (map_x/map_y on the taxonomy term) are left off the map.
    $prices_query = new WP_Query([
        'post_type'      => 'prices',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ]);

    $pins   = [];
    $routes = [];

    if ($prices_query->have_posts()) {
        foreach ($prices_query->posts as $price_post) {
            $froms  = get_the_terms($price_post->ID, 'locations_from');
            $tos    = get_the_terms($price_post->ID, 'locations_to');
            $people = get_the_terms($price_post->ID, 'number_of_people');

            if (empty($froms) || is_wp_error($froms)) continue;
            if (empty($tos)   || is_wp_error($tos))   continue;

            $from = $froms[0];
            $to   = $tos[0];

            $from_x = get_field('map_x', $from);
            $from_y = get_field('map_y', $from);
            $to_x   = get_field('map_x', $to);
            $to_y   = get_field('map_y', $to);

            if ($from_x === '' || $from_x === null || $from_y === '' || $from_y === null) continue;
            if ($to_x   === '' || $to_x   === null || $to_y   === '' || $to_y   === null) continue;

            $pins[$from->term_id] = ['name' => $from->name, 'x' => (float) $from_x, 'y' => (float) $from_y];
            $pins[$to->term_id]   = ['name' => $to->name,   'x' => (float) $to_x,   'y' => (float) $to_y];

            $route_key = $from->term_id . '-' . $to->term_id;

            if (!isset($routes[$route_key])) {
                $people_id = (!empty($people) && !is_wp_error($people)) ? $people[0]->term_id : '';

                $routes[$route_key] = [
                    'from'       => $from,
                    'to'         => $to,
                    'fares'      => [],
                    'search_url' => esc_url(site_url('/flights') . '?' . http_build_query(array_filter([
                        'locations_from'   => $from->term_id,
                        'locations_to'     => $to->term_id,
                        'number_of_people' => $people_id,
                    ]))),
                ];
            }

            $one_way_base = (float) get_field('price_one_way', $price_post->ID);
            $rt_base      = (float) get_field('price_round_trip', $price_post->ID);
            $tax_rate     = (float) (get_field('federal_tax_rate', $price_post->ID) ?: 16);
            $people_name  = (!empty($people) && !is_wp_error($people)) ? $people[0]->name : '';

            $routes[$route_key]['fares'][] = [
                'people'      => $people_name,
                'one_way_ex'  => $one_way_base ? number_format($one_way_base, 2) : null,
                'one_way_inc' => $one_way_base ? number_format($one_way_base * (1 + $tax_rate / 100), 2) : null,
                'rt_ex'       => $rt_base ? number_format($rt_base, 2) : null,
                'rt_inc'      => $rt_base ? number_format($rt_base * (1 + $tax_rate / 100), 2) : null,
                'tax_rate'    => $tax_rate,
            ];
        }
    }
    wp_reset_postdata();

    if (!$pins || !$routes) {
        return;
    }
?>

<section class="route-map-panel content animate fade-up <?php echo $generic_block_settings_classes; ?>">
    <div class="container <?php echo $generic_container_class; ?>">
        <div class="route-map">

            <div class="route-map__stage">
                <?php echo Theme\Utils::get_image_html($map_image, 1); ?>

                <svg class="route-map__lines" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
                    <?php foreach ($routes as $route) :
                        $from_pin = $pins[$route['from']->term_id];
                        $to_pin   = $pins[$route['to']->term_id];

                        // Gentle upward arc instead of a straight line — a
                        // fixed fraction of the point-to-point distance,
                        // offset "up" (toward smaller y) from the midpoint,
                        // gives a soft flight-path curve regardless of the
                        // route's own direction.
                        $dx = $to_pin['x'] - $from_pin['x'];
                        $dy = $to_pin['y'] - $from_pin['y'];
                        $dist = sqrt($dx * $dx + $dy * $dy);
                        $mid_x = ($from_pin['x'] + $to_pin['x']) / 2;
                        $mid_y = ($from_pin['y'] + $to_pin['y']) / 2 - ($dist * 0.16);

                        // number_format (not sprintf %F, which needs PHP 8+)
                        // always uses "." for the decimal point regardless
                        // of server locale — required for valid SVG path data.
                        $fmt = fn($n) => number_format((float) $n, 4, '.', '');
                        $path_d = 'M ' . $fmt($from_pin['x']) . ' ' . $fmt($from_pin['y'])
                            . ' Q ' . $fmt($mid_x) . ' ' . $fmt($mid_y)
                            . ' ' . $fmt($to_pin['x']) . ' ' . $fmt($to_pin['y']);

                        $route_data = esc_attr(wp_json_encode([
                            'from'  => $route['from']->name,
                            'to'    => $route['to']->name,
                            'fares' => $route['fares'],
                            'url'   => $route['search_url'],
                        ]));
                        $route_label = esc_attr($route['from']->name . ' to ' . $route['to']->name);
                    ?>
                        <g class="route-map__route" data-route="<?php echo $route_data; ?>" tabindex="0" role="button" aria-label="<?php echo $route_label; ?>">
                            <path class="route-map__route-hit" d="<?php echo esc_attr($path_d); ?>" vector-effect="non-scaling-stroke" />
                            <path class="route-map__route-line" d="<?php echo esc_attr($path_d); ?>" vector-effect="non-scaling-stroke" />
                        </g>
                    <?php endforeach; ?>
                </svg>

                <?php foreach ($pins as $pin) : ?>
                    <span class="route-map__pin" style="left: <?php echo $pin['x']; ?>%; top: <?php echo $pin['y']; ?>%;">
                        <span class="route-map__pin-dot" aria-hidden="true"></span>
                        <span class="route-map__pin-label"><?php echo esc_html($pin['name']); ?></span>
                    </span>
                <?php endforeach; ?>
            </div>

            <div class="route-map__info">
                <p class="route-map__info-prompt">Click a route on the map to see prices for that trip.</p>
            </div>

        </div>
    </div>
</section>

<?php
}
?>
