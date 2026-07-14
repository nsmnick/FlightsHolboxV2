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

            // Plane Info lives on the number_of_people term itself (aircraft
            // type is shared across every route using that traveler-count
            // tier), not per Price post.
            $place_info = (!empty($people) && !is_wp_error($people))
                ? get_field('plane_info', 'number_of_people_' . $people[0]->term_id)
                : null;

            $routes[$route_key]['fares'][] = [
                'people'      => $people_name,
                'one_way_ex'  => $one_way_base ? number_format($one_way_base, 2) : null,
                'one_way_inc' => $one_way_base ? number_format($one_way_base * (1 + $tax_rate / 100), 2) : null,
                'rt_ex'       => $rt_base ? number_format($rt_base, 2) : null,
                'rt_inc'      => $rt_base ? number_format($rt_base * (1 + $tax_rate / 100), 2) : null,
                'tax_rate'    => $tax_rate,
                'place_info'  => $place_info ?: null,
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
                    <defs>
                        <?php
                        // Positioned so the plane artwork's own visual centre
                        // (not the square image's centre — the silhouette
                        // sits off-centre in its source canvas) lands on the
                        // path point animateMotion moves it to, and rotated
                        // 45° to correct for the artwork's nose pointing
                        // up-right rather than along the local +x axis that
                        // animateMotion's rotate="auto" measures from.
                        ?>
                        <image
                            id="route-map-plane-icon"
                            href="<?php echo esc_url(THEMEROOT . '/images/route-map-plane.png'); ?>"
                            x="-2.25"
                            y="-1.9425"
                            width="4.2"
                            height="4.2"
                        />
                    </defs>
                    <?php
                    // Phase 1: draw every route's line, and queue one flight
                    // per unordered city pair (a pair priced in both
                    // directions still gets two clickable lines, but only
                    // one plane — otherwise it'd double up visually).
                    $flight_queue         = [];
                    $rendered_plane_pairs = [];

                    foreach ($routes as $route_key => $route) :
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
                        $path_id     = 'route-map-path-' . esc_attr($route_key);
                    ?>
                        <g class="route-map__route" data-route="<?php echo $route_data; ?>" tabindex="0" role="button" aria-label="<?php echo $route_label; ?>">
                            <path class="route-map__route-hit" d="<?php echo esc_attr($path_d); ?>" vector-effect="non-scaling-stroke" />
                            <path id="<?php echo $path_id; ?>" class="route-map__route-line" d="<?php echo esc_attr($path_d); ?>" vector-effect="non-scaling-stroke" />
                        </g>
                    <?php
                        $pair_key = implode('-', [
                            min($route['from']->term_id, $route['to']->term_id),
                            max($route['from']->term_id, $route['to']->term_id),
                        ]);

                        if (!isset($rendered_plane_pairs[$pair_key])) {
                            $rendered_plane_pairs[$pair_key] = true;
                            $flight_queue[] = [
                                'path_id'  => $path_id,
                                // Flight duration scales gently with route
                                // distance so longer hops take longer to fly.
                                'duration' => $fmt(min(max($dist * 0.045, 2.8), 6)),
                            ];
                        }
                    endforeach;

                    // Phase 2: only a small, fixed number of planes fly at
                    // once rather than one per route — with a couple dozen
                    // routes, one each was a wall of planes. Every route
                    // still gets flown eventually though: routes are dealt
                    // round-robin across the plane slots, each plane flies
                    // its assigned routes in turn, forever. A pure-SMIL
                    // version of that hand-off (chaining <animateMotion>
                    // elements via "id.end", looping the last back to the
                    // first) turned out unreliable — browsers wouldn't
                    // consistently keep the loop alive past the first lap.
                    // Server renders the first leg of each plane's route so
                    // motion still starts immediately even before JS runs;
                    // route-map.js (initRouteMapPlanes) takes over from
                    // there via the SMIL endEvent + beginElement() DOM API,
                    // advancing each plane to its next assigned route.
                    $max_planes  = 4;
                    $plane_count = min($max_planes, count($flight_queue));
                    $plane_slots = array_fill(0, $plane_count, []);

                    foreach ($flight_queue as $i => $flight) {
                        $plane_slots[$i % $plane_count][] = $flight;
                    }

                    foreach ($plane_slots as $slot_index => $slot_flights) :
                        if (!$slot_flights) continue;
                        $first  = $slot_flights[0];
                        $single = count($slot_flights) === 1;
                    ?>
                        <g
                            class="route-map__plane"
                            transform="rotate(45)"
                            <?php if (!$single) : ?>data-flights="<?php echo esc_attr(wp_json_encode($slot_flights)); ?>"<?php endif; ?>
                            aria-hidden="true"
                        >
                            <use href="#route-map-plane-icon" />
                            <animateMotion
                                dur="<?php echo $first['duration']; ?>s"
                                begin="<?php echo $fmt($slot_index * 0.6); ?>s"
                                repeatCount="<?php echo $single ? 'indefinite' : '1'; ?>"
                                rotate="auto"
                            >
                                <mpath href="#<?php echo $first['path_id']; ?>" xlink:href="#<?php echo $first['path_id']; ?>" />
                            </animateMotion>
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
