<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$hide_panel && !$preview_popup_image) {
    $heading = get_field('route_map_heading');

    // The geographic bounding box assets/images/map_background.webp covers
    // (bundled as a theme asset — see _route-map-panel.scss). Hardcoded
    // alongside the image itself, since one is meaningless without the
    // other — if the image is ever replaced, these four must change with
    // it, or every pin below will be mispositioned. Confirmed against real
    // coordinates for all five current locations before locking in.
    $map_bounds_north = 21.90549;
    $map_bounds_south = 20.03626;
    $map_bounds_west  = -90.03953;
    $map_bounds_east  = -86.43567;

    // Walk every priced route and group by from+to term pair, collecting
    // one fare row per number_of_people option. Locations without real
    // coordinates (map_lat/map_lng on the taxonomy term) are left off the map.
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

            $from_pos = Theme\Utils::latlng_to_percent(
                get_field('map_lat', $from),
                get_field('map_lng', $from),
                $map_bounds_north,
                $map_bounds_south,
                $map_bounds_west,
                $map_bounds_east
            );
            $to_pos = Theme\Utils::latlng_to_percent(
                get_field('map_lat', $to),
                get_field('map_lng', $to),
                $map_bounds_north,
                $map_bounds_south,
                $map_bounds_west,
                $map_bounds_east
            );

            if ($from_pos === null || $to_pos === null) continue;

            $pins[$from->term_id] = ['name' => $from->name, 'x' => $from_pos['x'], 'y' => $from_pos['y']];
            $pins[$to->term_id]   = ['name' => $to->name,   'x' => $to_pos['x'],   'y' => $to_pos['y']];

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

        <?php if ($heading) : ?>
            <h2 class="route-map-panel__heading"><?php echo esc_html($heading); ?></h2>
        <?php endif; ?>

        <div class="route-map">

            <div class="route-map__stage" role="img" aria-label="Map of the Yucatán Peninsula showing our destinations">
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
