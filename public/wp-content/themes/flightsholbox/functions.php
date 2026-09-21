<?php

define('THEMEROOT', get_stylesheet_directory_uri());

require_once get_stylesheet_directory() . '/src/Autoloader.php';
\Theme\Autoloader::register();

\Theme\Config\Init::init();
//\Theme\Config\Lockdown::init();

\Theme\Config\ACFBlocks::init();


\Theme\Config\Plugins\ACFPro\ACFPro::init();
\Theme\Config\GoogleReviews::init();
\Theme\Config\RestAPI::init();
\Theme\Config\Lockdown::init();

class Theme_Setup
{
    public array $assets_map;
    private ?bool $viteHMRAvailable = null;

    public function __construct()
    {
        $this->assets_map = $this->getAssetsMap();

        add_action('after_setup_theme', [$this, 'themeSupports']);

        add_filter('init', [$this, 'registerNavMenus']);

        add_filter('wp_enqueue_scripts', [$this, 'enqueueStyles']);
        add_filter('wp_enqueue_scripts', [$this, 'enqueueScripts']);

        add_action('enqueue_block_editor_assets', [$this, 'enqueueBlockEditorAssets']);

        add_action('login_enqueue_scripts', [$this, 'enqueueLoginStyles']);
        add_filter('login_headerurl', [$this, 'customLoginLogoUrl']);

        add_filter('excerpt_length', [$this, 'excerptLength'], 999);
        add_filter('excerpt_more', [$this, 'excerptMore'], 999);

        add_action('login_body_class', [$this, 'addEnvironmentClass']);
        add_filter('body_class', [$this, 'addEnvironmentClass']);
    }

    private function getAssetsMap()
    {
        $assets_map_path = get_stylesheet_directory() . '/dist/.vite/manifest.json';

        if (file_exists($assets_map_path)) {
            return json_decode(file_get_contents($assets_map_path), true);
        }

        return [];
    }

    public function themeSupports()
    {
        add_theme_support('html5', ['gallery', 'caption']);
        add_theme_support('post-thumbnails');
        add_theme_support('title-tag');

        add_image_size('hero-slide', 1280, 720, true);
    }

    public function registerNavMenus()
    {
        register_nav_menu('primary-menu', 'Primary Menu');
        register_nav_menu('footer-menu', 'Footer Menu — Information');
        register_nav_menu('footer-menu-pickups', 'Footer Menu — Popular Pick Ups');
        register_nav_menu('footer-menu-destinations', 'Footer Menu — Popular Destinations');
    }

    public function enqueueScripts()
    {
        if (!$this->isViteHMRAvailable()) {
            if (array_key_exists('assets/index.js', $this->assets_map)) {
                wp_enqueue_script(
                    'theme-script',
                    get_stylesheet_directory_uri() . '/dist/' . $this->assets_map['assets/index.js']["file"],
                    [],
                    null,
                    []
                );
                $this->loadJSScriptAsESModule('theme-script');
            }
        } else {
            $theme_path = parse_url(get_stylesheet_directory_uri(), PHP_URL_PATH);

            wp_enqueue_script(
                'vite-client',
                $this->getViteDevServerAddress() . $theme_path . '/dist/@vite/client',
                [],
                null,
                []
            );
            $this->loadJSScriptAsESModule('vite-client');

            wp_enqueue_script(
                'vite-script',
                $this->getViteDevServerAddress() . $theme_path . '/dist/assets/index.js',
                [],
                null,
                []
            );
            $this->loadJSScriptAsESModule('vite-script');
        }
    }

    public function enqueueStyles()
    {
        // Remove WordPress block/global styles — this theme manages all its own CSS
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('wp-block-library-theme');
        wp_dequeue_style('global-styles');
        wp_dequeue_style('classic-theme-styles');

        if (!$this->isViteHMRAvailable()) {
            if (
                array_key_exists('assets/index.js', $this->assets_map) &&
                array_key_exists('css', $this->assets_map['assets/index.js'])
            ) {
                foreach ($this->assets_map['assets/index.js']["css"] as $style_path) {
                    wp_enqueue_style(
                        'theme-styles',
                        get_stylesheet_directory_uri() . '/dist/' . $style_path,
                        [],
                        false,
                        'all'
                    );
                }
            }
        } else {
            // Request the compiled stylesheet directly (rather than relying on
            // Vite's JS-injected <style> tag) so it arrives as a real blocking
            // <link>, matching production and avoiding a flash-of-unstyled-content
            // layout shift while still hot-reloading through Vite's dev server.
            $theme_path = parse_url(get_stylesheet_directory_uri(), PHP_URL_PATH);

            wp_enqueue_style(
                'theme-styles',
                $this->getViteDevServerAddress() . $theme_path . '/dist/assets/styles/styles.scss?direct',
                [],
                null,
                'all'
            );
        }
    }

    public function enqueueBlockEditorAssets()
    {
        // Load compiled theme CSS inside the block editor so ACF blocks render correctly
        if (!$this->isViteHMRAvailable()) {
            if (
                array_key_exists('assets/index.js', $this->assets_map) &&
                array_key_exists('css', $this->assets_map['assets/index.js'])
            ) {
                foreach ($this->assets_map['assets/index.js']["css"] as $style_path) {
                    wp_enqueue_style(
                        'theme-editor-styles',
                        get_stylesheet_directory_uri() . '/dist/' . $style_path,
                        [],
                        false,
                        'all'
                    );
                }
            }
        } else {
            $theme_path = parse_url(get_stylesheet_directory_uri(), PHP_URL_PATH);

            wp_enqueue_style(
                'theme-editor-styles',
                $this->getViteDevServerAddress() . $theme_path . '/dist/assets/styles/styles.scss?direct',
                [],
                null,
                'all'
            );
        }
    }

    public function enqueueLoginStyles()
    {
        if (
            array_key_exists('assets/login.js', $this->assets_map) &&
            array_key_exists('css', $this->assets_map['assets/login.js'])
        ) {
            foreach ($this->assets_map['assets/login.js']["css"] as $style_path) {
                wp_enqueue_style(
                    'login-styles',
                    get_stylesheet_directory_uri() . '/dist/' . $style_path,
                    [],
                    false,
                    'all'
                );
            }
        }
    }

    public function customLoginLogoUrl()
    {
        return site_url();
    }

    public function excerptLength()
    {
        return 20;
    }

    public function excerptMore()
    {
        return '&hellip;';
    }

    public function addEnvironmentClass($classes = '')
    {
        $environment = wp_get_environment_type();

        if ($environment !== 'production') {
            $classes[] = 'env-' . $environment;
        }

        return $classes;
    }

    public function loadJSScriptAsESModule($script_handle)
    {
        add_filter(
            'script_loader_tag',
            function ($tag, $handle, $src) use ($script_handle) {
                if ($script_handle === $handle) {
                    return sprintf(
                        '<script type="module" src="%s"></script>',
                        esc_url($src)
                    );
                }
                return $tag;
            },
            10,
            3
        );
    }

    public function getViteDevServerAddress()
    {
        if (defined('VITE_DEV_SERVER_URL')) {
            return VITE_DEV_SERVER_URL;
        }

        return '';
    }

    public function isViteHMRAvailable()
    {
        if ($this->viteHMRAvailable === null) {
            $this->viteHMRAvailable = $this->checkViteHMRAvailable();
        }

        return $this->viteHMRAvailable;
    }

    /**
     * Beyond checking that we're in local dev, also confirms the Vite dev
     * server is actually reachable — so if `npm run dev` isn't running,
     * the theme silently falls back to the built /dist assets instead of
     * emitting <script> tags that 404 against a dead dev server.
     */
    private function checkViteHMRAvailable(): bool
    {
        $address = $this->getViteDevServerAddress();

        if (empty($address) || !defined('WP_ENVIRONMENT_TYPE') || WP_ENVIRONMENT_TYPE !== 'local') {
            return false;
        }

        $parts = parse_url($address);
        $host = $parts['host'] ?? '127.0.0.1';
        $port = $parts['port'] ?? (($parts['scheme'] ?? 'http') === 'https' ? 443 : 80);

        $connection = @fsockopen($host, $port, $errno, $errstr, 0.2);

        if (!$connection) {
            return false;
        }

        fclose($connection);

        return true;
    }
}

new Theme_Setup();


// ─── Images: generate WebP sub-sizes instead of bloated PNG/JPEG ───────────
// Only affects the generated thumbnail/medium/large/etc. sizes, not the
// original upload — keeps media-library originals untouched.

add_filter('image_editor_output_format', function (array $formats): array {
    $formats['image/png'] = 'image/webp';
    $formats['image/jpeg'] = 'image/webp';
    return $formats;
});


// ─── TinyMCE: enable text colour picker in all wysiwyg fields ──────────────

add_filter('mce_buttons_2', function (array $buttons): array {
    $buttons[] = 'forecolor';
    return $buttons;
});

// Make the colour palette match the FH brand colours
add_filter('tiny_mce_before_init', function (array $settings): array {
    $settings['textcolor_map'] = json_encode([
        '000000',
        'Black',
        '1b161c',
        'FH Dark',
        '00467b',
        'FH Navy',
        'e6af2a',
        'FH Gold',
        '464749',
        'FH Charcoal',
        '888888',
        'Grey',
        'ffffff',
        'White',
    ]);
    $settings['textcolor_cols'] = '7';
    $settings['textcolor_rows'] = '1';
    return $settings;
});

// The palette above is 7 fixed swatches, but TinyMCE's colour picker still
// exposes a "Custom colour..." option beyond them — and that writes
// `rgb(r, g, b)`, not hex. wp_kses_post() (via safecss_filter_attr()) then
// silently drops the whole `color` declaration: its allowlist of CSS
// functions that survive sanitization only covers var/calc/min/max/minmax/
// clamp/repeat, not rgb()/rgba(), so any colour picked outside the 7 presets
// vanished on the frontend even though it saved and previewed fine in the
// editor. Explicitly allow the safe, numeric-only rgb()/rgba() pattern
// through rather than loosening CSS sanitization generally.
add_filter('safecss_filter_attr_allow_css', function (bool $allow_css, string $css_test_string): bool {
    if ($allow_css) {
        return $allow_css;
    }

    if (preg_match('/^[a-z-]+\s*:\s*rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*(?:,\s*(?:0|1|0?\.\d+)\s*)?\)\s*$/i', $css_test_string)) {
        return true;
    }

    return $allow_css;
}, 10, 2);


// ─── Flights Holbox: CPT, Taxonomies & Booking helpers ─────────────────────

add_action('init', 'fh_register_post_types');

function fh_register_post_types()
{
    register_post_type('prices', [
        'labels' => [
            'name'          => __('Prices'),
            'singular_name' => __('Price'),
            'menu_name'     => __('Prices'),
            'add_new'       => __('Add New Price'),
            'add_new_item'  => __('Add New Price'),
            'edit_item'     => __('Edit Price'),
            'all_items'     => __('All Prices'),
            'not_found'     => __('No Prices found.'),
        ],
        'menu_icon'    => 'dashicons-format-aside',
        'public'       => true,
        'has_archive'  => false,
        'supports'     => ['title', 'editor', 'excerpt'],
        'menu_position' => 7,
        'show_in_rest' => true,
    ]);

    register_taxonomy('locations_from', 'prices', [
        'labels' => [
            'name'         => 'From Location',
            'add_new_item' => 'Add New Location',
        ],
        'show_ui'          => true,
        'show_admin_column' => true,
        'show_tagcloud'    => false,
        'hierarchical'     => true,
        'show_in_rest'     => true,
    ]);

    register_taxonomy('locations_to', 'prices', [
        'labels' => [
            'name'         => 'To Location',
            'add_new_item' => 'Add New Location',
        ],
        'show_ui'          => true,
        'show_admin_column' => true,
        'show_tagcloud'    => false,
        'hierarchical'     => true,
        'show_in_rest'     => true,
    ]);

    register_taxonomy('number_of_people', 'prices', [
        'labels' => [
            'name'         => 'Number of People',
            'add_new_item' => 'Add New Option',
        ],
        'show_ui'          => true,
        'show_admin_column' => true,
        'show_tagcloud'    => false,
        'hierarchical'     => true,
        'show_in_rest'     => true,
    ]);

    register_post_type('airports', [
        'labels' => [
            'name'          => __('Airports'),
            'singular_name' => __('Airport'),
            'menu_name'     => __('Airports'),
            'add_new'       => __('Add New Airport'),
            'add_new_item'  => __('Add New Airport'),
            'edit_item'     => __('Edit Airport'),
            'all_items'     => __('All Airports'),
            'not_found'     => __('No Airports found.'),
        ],
        'menu_icon'     => 'dashicons-airplane',
        'public'        => true,
        'has_archive'   => true,
        'rewrite'       => ['slug' => 'airports'],
        // 'editor' enables the block editor canvas on Airport posts, so any
        // ACF block can be added — single-airports.php calls the_content()
        // to actually render whatever's placed there, below the fixed
        // hero/introduction/content_sections fields.
        'supports'      => ['title', 'thumbnail', 'editor'],
        'menu_position' => 8,
        'show_in_rest'  => true,
    ]);

    register_post_type('activities', [
        'labels' => [
            'name'          => __('Activities'),
            'singular_name' => __('Activity'),
            'menu_name'     => __('Activities'),
            'add_new'       => __('Add New Activity'),
            'add_new_item'  => __('Add New Activity'),
            'edit_item'     => __('Edit Activity'),
            'all_items'     => __('All Activities'),
            'not_found'     => __('No Activities found.'),
        ],
        'menu_icon'     => 'dashicons-palmtree',
        'public'        => true,
        'has_archive'   => true,
        'rewrite'       => ['slug' => 'activities'],
        // 'editor' enables the block editor canvas on Activity posts, so any
        // ACF block can be added — single-activities.php calls the_content()
        // to actually render whatever's placed there, below the fixed
        // hero/introduction/content_sections fields.
        'supports'      => ['title', 'thumbnail', 'editor'],
        'menu_position' => 9,
        'show_in_rest'  => true,
    ]);

    // Question = post title, Answer = native post content (WYSIWYG) — no ACF
    // fields needed on the FAQ post itself. Grouped by faq_topic so the
    // Support Panel block can render its topic tabs, and so the FAQ Panel
    // block can offer a "select FAQs" picker scoped to real content.
    register_post_type('faq', [
        'labels' => [
            'name'          => __('FAQs'),
            'singular_name' => __('FAQ'),
            'menu_name'     => __('FAQs'),
            'add_new'       => __('Add New FAQ'),
            'add_new_item'  => __('Add New FAQ'),
            'edit_item'     => __('Edit FAQ'),
            'all_items'     => __('All FAQs'),
            'not_found'     => __('No FAQs found.'),
        ],
        'menu_icon'     => 'dashicons-editor-help',
        'public'        => true,
        'has_archive'   => false,
        'supports'      => ['title', 'editor', 'page-attributes'],
        'menu_position' => 10,
        'show_in_rest'  => true,
    ]);

    register_taxonomy('faq_topic', 'faq', [
        'labels' => [
            'name'         => 'FAQ Topic',
            'add_new_item' => 'Add New Topic',
        ],
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_tagcloud'     => false,
        'hierarchical'      => true,
        'show_in_rest'      => true,
    ]);
}

/**
 * Render a <select> dropdown populated from a WordPress taxonomy.
 *
 * @param string $taxonomy   Taxonomy slug.
 * @param array  $args       Optional WP get_terms() args.
 * @param string $all_label  Text for the default "Select" option.
 * @return string            HTML <select> element.
 */
// ─── Gravity Forms: Transfer Booking Form (ID 1) ──────────────────────────────

add_filter('gform_pre_render_1',            'fh_gf_inject_booking_summary');
add_filter('gform_pre_validation_1',        'fh_gf_inject_booking_summary');
add_filter('gform_pre_submission_filter_1', 'fh_gf_inject_booking_summary');
function fh_gf_inject_booking_summary($form)
{
    $price_id  = isset($_GET['price_id'])  ? (int) $_GET['price_id']  : 0;
    $trip_type = (isset($_GET['trip_type']) && in_array($_GET['trip_type'], ['one_way', 'round_trip']))
        ? $_GET['trip_type'] : '';

    if (!$price_id || !$trip_type) return $form;

    $post = get_post($price_id);
    if (!$post || $post->post_type !== 'prices') return $form;

    $froms  = get_the_terms($price_id, 'locations_from');
    $tos    = get_the_terms($price_id, 'locations_to');
    $people = get_the_terms($price_id, 'number_of_people');
    $from   = (!empty($froms)  && !is_wp_error($froms))  ? $froms[0]->name  : '';
    $to     = (!empty($tos)    && !is_wp_error($tos))    ? $tos[0]->name    : '';
    $ppl    = (!empty($people) && !is_wp_error($people)) ? $people[0]->name : '';
    $base   = $trip_type === 'one_way'
        ? (float) get_field('price_one_way',   $price_id)
        : (float) get_field('price_round_trip', $price_id);
    $tax    = (float) (get_field('federal_tax_rate', $price_id) ?: 16);
    $inc    = round($base * (1 + $tax / 100), 2);
    $label  = $trip_type === 'one_way' ? 'One Way' : 'Round Trip';

    $html  = '<div class="booking-summary-block">';
    $html .= '<div class="booking-summary-block__row"><strong>Transfer:</strong> ' . esc_html($label) . '</div>';
    $html .= '<div class="booking-summary-block__row"><strong>From:</strong> ' . esc_html($from) . '</div>';
    $html .= '<div class="booking-summary-block__row"><strong>To:</strong> ' . esc_html($to) . '</div>';
    $html .= '<div class="booking-summary-block__row"><strong>Group size:</strong> ' . esc_html($ppl) . '</div>';
    $html .= '<div class="booking-summary-block__row"><strong>Cost (ex. ' . $tax . '% federal tax):</strong> $' . number_format($base, 2) . '</div>';
    $html .= '<div class="booking-summary-block__row booking-summary-block__row--total"><strong>Total inc. tax:</strong> $' . number_format($inc, 2) . '</div>';
    $html .= '</div>';

    foreach ($form['fields'] as &$field) {
        if ((int) $field->id === 16) {
            $field->content = $html;
            break;
        }
    }

    return $form;
}

add_filter('gform_notification_1', 'fh_gf_booking_notification', 10, 3);
function fh_gf_booking_notification($notification, $form, $entry)
{
    if ($notification['name'] !== 'Admin Notification') return $notification;

    $price_id  = isset($_GET['price_id'])  ? (int) $_GET['price_id']  : 0;
    $trip_type = isset($_GET['trip_type']) ? sanitize_key($_GET['trip_type']) : '';

    if (!$price_id) return $notification;

    $froms = get_the_terms($price_id, 'locations_from');
    $tos   = get_the_terms($price_id, 'locations_to');
    $from  = (!empty($froms) && !is_wp_error($froms)) ? $froms[0]->name : '';
    $to    = (!empty($tos)   && !is_wp_error($tos))   ? $tos[0]->name   : '';
    $label = $trip_type === 'one_way' ? 'One Way' : 'Round Trip';

    if ($from && $to) {
        $notification['subject'] = 'New Booking Request: ' . $label . ' – ' . $from . ' → ' . $to;
    }

    return $notification;
}

// Formats a Gravity Forms date field's raw value as "DD / Month / YYYY"
// (e.g. "25 / December / 2026") — used everywhere a booking date is shown
// (both notification emails and the Booking Summary Panel block), so the
// format stays identical across all three.
//
// The Transfer Date field's own input mask displays as dd/mm/yyyy, but GF
// actually stores date field values in the entry as Y-m-d regardless of
// the display format configured on the field — that's the format that
// needs parsing here, not the one shown to the person filling in the form.
function fh_format_booking_date(string $raw_date): string
{
    $date = DateTime::createFromFormat('Y-m-d', $raw_date);

    if (!$date) {
        return esc_html($raw_date);
    }

    return $date->format('d') . ' / ' . $date->format('F') . ' / ' . $date->format('Y');
}

// The single source of truth for "what a booking confirmation shows" —
// used by both notification emails below AND the Booking Summary Panel
// block (src/Config/acfBlocks/booking-summary-panel/), so the page and the
// emails can never drift out of sync with each other. Returns an ordered
// list of [label, value_html] pairs rather than markup directly, since the
// emails render them as table rows and the page block renders them as
// .booking-summary-block__row divs.
function fh_gf_booking_entry_rows(array $entry): array
{
    $is_round_trip = ($entry['44'] ?? '') === 'Round Trip';

    $rows   = [];
    $rows[] = ['Booking ID', '#' . esc_html($entry['id'] ?? '')];
    $rows[] = ['Trip Type', $is_round_trip ? 'Round Trip' : 'One Way'];
    $rows[] = ['Route', esc_html($entry['18'] ?? '') . ' &rarr; ' . esc_html($entry['19'] ?? '')];
    $rows[] = ['Date of Flight', fh_format_booking_date($entry['1'] ?? '') . ' at ' . esc_html($entry['3'] ?? '')];

    if ($is_round_trip) {
        $rows[] = ['Return Date', fh_format_booking_date($entry['41'] ?? '') . ' at ' . esc_html($entry['42'] ?? '')];
    }

    $rows[] = ['Passenger Name(s)', nl2br(esc_html($entry['2'] ?? ''))];
    $rows[] = ['Number of Passengers', esc_html($entry['34'] ?? '')];
    $rows[] = ['Luggage Pieces', esc_html($entry['45'] ?? '0') . ' carry-on, ' . esc_html($entry['46'] ?? '0') . ' checked'];
    $rows[] = ['Luggage Weight', esc_html($entry['32'] ?? '') . ' kg'];
    $rows[] = ['Email', esc_html($entry['6'] ?? '')];
    $rows[] = ['Phone Number', esc_html($entry['7'] ?? '')];
    $rows[] = [
        'Cost',
        '$' . esc_html(number_format((float) ($entry['22'] ?? 0), 2)) . ' excl. tax &middot; $'
            . esc_html(number_format((float) ($entry['38'] ?? 0), 2)) . ' incl. tax',
    ];

    return $rows;
}

// Renders fh_gf_booking_entry_rows() as <tr> table rows for the two HTML
// emails below — kept separate from that function since the Booking
// Summary Panel block renders the same rows as plain divs instead.
function fh_gf_render_booking_rows_html(array $rows): string
{
    $rows_html = '';

    foreach ($rows as $i => [$label, $value]) {
        $border = $i < count($rows) - 1 ? 'border-bottom:1px solid #f1f1ee;' : '';
        $rows_html .= '<tr>'
            . '<td style="padding:10px 16px;' . $border . 'font-size:13px;color:#464749;white-space:nowrap;vertical-align:top;"><strong>' . esc_html($label) . '</strong></td>'
            . '<td style="padding:10px 16px;' . $border . 'font-size:14px;color:#1b161c;">' . $value . '</td>'
            . '</tr>';
    }

    return $rows_html;
}

// Shared branded HTML shell (navy header, white card, charcoal footer)
// wrapping whichever heading/intro/rows a specific booking notification
// needs — keeps the two emails visually consistent without duplicating
// the table markup.
function fh_gf_build_branded_email_html(string $heading, string $intro_html, string $rows_html, string $after_rows_html = ''): string
{
    $site_name = get_bloginfo('name');
    $site_url  = site_url('/');

    return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="margin:0;padding:0;background:#f1f1ee;font-family:Arial,Helvetica,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f1ee;padding:30px 0;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;">
<tr>
<td style="background:#00467b;padding:24px 32px;text-align:center;">
<span style="color:#ffffff;font-size:22px;font-weight:bold;">{$site_name}</span>
</td>
</tr>
<tr>
<td style="padding:32px;">
<h1 style="margin:0 0 8px;color:#00467b;font-size:22px;font-family:Arial,Helvetica,sans-serif;">{$heading}</h1>
{$intro_html}
<table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #d9d9d6;border-radius:6px;">
{$rows_html}
</table>
{$after_rows_html}
</td>
</tr>
<tr>
<td style="background:#464749;padding:20px 32px;text-align:center;">
<span style="color:#ffffff;font-size:13px;">{$site_name} &middot; <a href="{$site_url}" style="color:#ffffff;">{$site_url}</a></span>
</td>
</tr>
</table>
</td></tr>
</table>
</body>
</html>
HTML;
}

// Both booking notification emails send from FH_BOOKING_NOTIFICATION_EMAIL
// (wp-config.php) rather than Gravity Forms' own hardcoded sender address —
// falls back to the site's admin email (Settings > General) if that
// constant is left blank, so this is a no-op change until someone sets it.
add_filter('gform_notification_1', 'fh_gf_booking_email_sender', 5, 3);
function fh_gf_booking_email_sender($notification, $form, $entry)
{
    if (!in_array($notification['name'], ['Customer Confirmation', 'Admin Notification'], true)) {
        return $notification;
    }

    $admin_email = get_option('admin_email');
    $from_email  = (defined('FH_BOOKING_NOTIFICATION_EMAIL') && FH_BOOKING_NOTIFICATION_EMAIL)
        ? FH_BOOKING_NOTIFICATION_EMAIL
        : $admin_email;

    $notification['from']     = $from_email;
    $notification['fromName'] = get_bloginfo('name');

    if ($notification['name'] === 'Admin Notification') {
        $notification['to']     = $admin_email;
        $notification['toType'] = 'email';
    }

    return $notification;
}

// Replaces the Customer Confirmation notification's body with a branded
// HTML summary of the actual booking (previously just a generic "we'll be
// in touch" message with no booking details, plus a broken image reference
// left over from the pre-migration site).
add_filter('gform_notification_1', 'fh_gf_customer_confirmation_email', 10, 3);
function fh_gf_customer_confirmation_email($notification, $form, $entry)
{
    if ($notification['name'] !== 'Customer Confirmation') return $notification;

    $notification['message'] = fh_gf_render_customer_confirmation_html($entry);

    // Otherwise GF converts every newline in the HTML above into an extra
    // <br /> — its auto-formatting is meant for plain-text messages, not a
    // fully hand-built HTML email like this one.
    $notification['disableAutoformat'] = true;

    return $notification;
}

// The customer-facing confirmation email — visually distinct from the
// (much simpler) admin notification, matching a design the client's boss
// supplied. Built entirely from real HTML/inline CSS + two images (hero
// photo, logo), NOT the shared fh_gf_build_branded_email_html() shell used
// by the admin email, since this one's structure genuinely diverges: hero
// photo, success checkmark, and a "What's next" steps panel that email has
// no equivalent of.
//
// The "What's next" steps are built with plain numbered circles and real
// text — not a single exported graphic — deliberately: a big share of
// inboxes (Outlook, Gmail) hide images until the recipient clicks "show
// images", and the step titles/descriptions need to still be readable at
// that point. The fragile bits a real graphic would help with (overlapping
// number badges, a dotted connector line) are skipped rather than
// approximated with unreliable email CSS.
function fh_gf_render_customer_confirmation_html(array $entry): string
{
    $site_name    = get_bloginfo('name');
    $site_url     = site_url('/');
    $admin_email  = (defined('FH_BOOKING_NOTIFICATION_EMAIL') && FH_BOOKING_NOTIFICATION_EMAIL)
        ? FH_BOOKING_NOTIFICATION_EMAIL
        : get_option('admin_email');
    $rows_html    = fh_gf_render_booking_rows_html(fh_gf_booking_entry_rows($entry));
    $hero_url   = esc_url(THEMEROOT . '/email-assets/hero-holbox.jpg');
    $logo_url   = esc_url(THEMEROOT . '/email-assets/logo.png');

    $steps = [
        ['01', 'Request Received', 'We have received your flight details.'],
        ['02', 'Availability &amp; Payment', 'Our team will confirm availability and send you a secure payment link.'],
        ['03', 'Flight Confirmed', 'Once payment has been received, we will send your final flight confirmation with all details.'],
    ];

    $steps_html = '';
    foreach ($steps as [$number, $title, $description]) {
        $steps_html .= <<<HTML
<td class="step-col" width="33%" valign="top" align="center" style="padding:0 10px;">
<table cellpadding="0" cellspacing="0" style="margin:0 auto 12px;">
<tr><td width="48" height="48" align="center" valign="middle" style="background:#00467b;border-radius:50%;color:#ffffff;font-size:16px;font-weight:bold;font-family:Arial,Helvetica,sans-serif;">{$number}</td></tr>
</table>
<p style="margin:0 0 4px;color:#1b161c;font-size:14px;font-weight:bold;font-family:Arial,Helvetica,sans-serif;">{$title}</p>
<p style="margin:0;color:#656565;font-size:12px;line-height:1.5;font-family:Arial,Helvetica,sans-serif;">{$description}</p>
</td>
HTML;
    }

    return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
  /* Stacks the "What's next" steps to one full-width column per row on
     phones, instead of staying cramped at ~180px each — most mobile email
     clients (iOS Mail, the Gmail app, Outlook mobile) respect this. */
  @media only screen and (max-width: 600px) {
    .step-col {
      display: block !important;
      width: 100% !important;
      padding: 0 20px 20px !important;
    }
  }
</style>
</head>
<body style="margin:0;padding:0;background:#f1f1ee;font-family:Arial,Helvetica,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f1ee;padding:30px 0;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;">

<tr>
<td>
<img src="{$hero_url}" width="600" alt="{$site_name}" style="display:block;width:100%;max-width:600px;height:auto;">
</td>
</tr>

<tr>
<td align="center" style="padding:20px 0 0;">
<img src="{$logo_url}" width="90" height="90" alt="{$site_name}" style="display:block;margin-top:-65px;border-radius:50%;border:4px solid #ffffff;">
</td>
</tr>

<tr>
<td align="center" style="padding:16px 32px 0;">
<span style="display:inline-block;width:40px;height:40px;line-height:40px;border-radius:50%;background:#e6f4ea;color:#2e7d32;font-size:20px;font-weight:bold;">&#10003;</span>
</td>
</tr>

<tr>
<td align="center" style="padding:16px 32px 0;">
<h1 style="margin:0 0 6px;color:#00467b;font-size:24px;font-family:Arial,Helvetica,sans-serif;">Thank You for Your Booking Request</h1>
<p style="margin:0;color:#2e7d32;font-size:14px;font-weight:bold;">Your flight request has been successfully submitted.</p>
</td>
</tr>

<tr>
<td align="center" style="padding:16px 40px 0;">
<p style="margin:0;color:#656565;font-size:14px;line-height:1.7;">
Thank you for choosing {$site_name}. Our team will now review your flight details and availability. We will contact you within the next 24 hours with the next steps and payment information.
</p>
</td>
</tr>

<tr>
<td style="padding:32px;">
<h2 style="margin:0 0 20px;color:#00467b;font-size:20px;text-align:center;font-family:Arial,Helvetica,sans-serif;">Booking Request Received</h2>
<table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #d9d9d6;border-radius:6px;">
{$rows_html}
</table>
</td>
</tr>

<tr>
<td style="padding:8px 32px 32px;">
<h2 style="margin:0 0 20px;color:#00467b;font-size:20px;text-align:center;font-family:Arial,Helvetica,sans-serif;">What's next?</h2>
<table width="100%" cellpadding="0" cellspacing="0">
<tr>
{$steps_html}
</tr>
</table>
</td>
</tr>

<tr>
<td style="padding:8px 32px 32px;text-align:center;">
<h2 style="margin:0 0 8px;color:#00467b;font-size:18px;font-family:Arial,Helvetica,sans-serif;">Please Review Your Request</h2>
<p style="margin:0 0 4px;color:#656565;font-size:13px;line-height:1.6;">
If you notice any mistakes or need to make changes to your request, please contact us at<br>
<a href="mailto:{$admin_email}" style="color:#00467b;font-weight:bold;">{$admin_email}</a>
</p>
<p style="margin:0;color:#656565;font-size:13px;line-height:1.6;">
or simply submit a new booking request with the correct details.
</p>
</td>
</tr>

<tr>
<td style="background:#464749;padding:20px 32px;text-align:center;">
<span style="color:#ffffff;font-size:13px;">{$site_name} &middot; <a href="{$site_url}" style="color:#ffffff;">{$site_url}</a></span>
</td>
</tr>

</table>
</td></tr>
</table>
</body>
</html>
HTML;
}

// Replaces the Admin Notification's body with the same branded HTML
// summary as the customer email, addressed internally instead — previously
// this was Gravity Forms' default plain-text field dump.
add_filter('gform_notification_1', 'fh_gf_admin_notification_email', 10, 3);
function fh_gf_admin_notification_email($notification, $form, $entry)
{
    if ($notification['name'] !== 'Admin Notification') return $notification;

    $rows_html  = fh_gf_render_booking_rows_html(fh_gf_booking_entry_rows($entry));
    $intro_html = <<<HTML
<p style="margin:0 0 24px;color:#464749;font-size:15px;line-height:1.6;">
A new booking request has just come in — details below.
</p>
HTML;

    $notification['message'] = fh_gf_build_branded_email_html('New Booking Request', $intro_html, $rows_html);
    $notification['disableAutoformat'] = true;

    return $notification;
}

// Register "confirmation_token" as searchable Gravity Forms entry meta —
// required for GFAPI::get_entries()'s field_filters to be able to look an
// entry up by it, since it isn't a real form field.
add_filter('gform_entry_meta', 'fh_gf_register_confirmation_token_meta');
function fh_gf_register_confirmation_token_meta($entry_meta)
{
    $entry_meta['confirmation_token'] = [
        'label'             => 'Confirmation Token',
        'is_numeric'        => false,
        'is_default_column' => false,
    ];

    return $entry_meta;
}

// Overrides whatever Confirmation is configured in the GF admin for this
// form — always redirects to the booking confirmation page with a random,
// unguessable per-entry token, NOT the entry's own (sequential, guessable)
// ID, so a visitor can't view another customer's booking just by editing
// the URL.
//
// The token is generated right here rather than in a separate
// gform_after_submission hook — that hook actually fires AFTER GF has
// already processed notifications and the confirmation, not before, so a
// token saved there is never ready in time for this filter to use it.
add_filter('gform_confirmation_1', 'fh_gf_booking_confirmation_redirect', 10, 4);
function fh_gf_booking_confirmation_redirect($confirmation, $form, $entry, $ajax)
{
    $token = gform_get_meta($entry['id'], 'confirmation_token');

    if (!$token) {
        $token = bin2hex(random_bytes(20));
        gform_update_meta($entry['id'], 'confirmation_token', $token);
    }

    return [
        'redirect' => add_query_arg('token', $token, site_url('/booking-confirmation/')),
    ];
}


// ─── Categories dropdown helper ────────────────────────────────────────────────

function fh_get_categories_dropdown(string $taxonomy, array $args = [], string $all_label = 'Select', int $selected_id = 0): string
{
    $terms = get_terms($taxonomy, array_merge(['hide_empty' => false], $args));

    $html  = '<select id="' . esc_attr($taxonomy) . '" name="' . esc_attr($taxonomy) . '" class="search-form__select" data-taxonomy="' . esc_attr($taxonomy) . '">';
    $html .= '<option value="0"' . ($selected_id === 0 ? ' selected="selected"' : '') . '>' . esc_html($all_label) . '</option>';

    if (!is_wp_error($terms)) {
        foreach ($terms as $term) {
            $selected = $selected_id === (int) $term->term_id ? ' selected="selected"' : '';
            $html .= '<option value="' . esc_attr($term->term_id) . '"' . $selected . '>' . esc_html($term->name) . '</option>';
        }
    }

    $html .= '</select>';

    return $html;
}
