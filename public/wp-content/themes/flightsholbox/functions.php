<?php

define('THEMEROOT', get_stylesheet_directory_uri());

require_once get_stylesheet_directory() . '/src/Autoloader.php';
\Theme\Autoloader::register();

\Theme\Config\ACFBlocks::init();
\Theme\Config\Plugins\ACFPro\ACFPro::init();
\Theme\Config\GoogleReviews::init();

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
        '000000', 'Black',
        '1b161c', 'FH Dark',
        '00467b', 'FH Navy',
        'e6af2a', 'FH Gold',
        '464749', 'FH Charcoal',
        '888888', 'Grey',
        'ffffff', 'White',
    ]);
    $settings['textcolor_cols'] = '7';
    $settings['textcolor_rows'] = '1';
    return $settings;
});


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
        'supports'      => ['title', 'thumbnail'],
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
        'supports'      => ['title', 'thumbnail'],
        'menu_position' => 9,
        'show_in_rest'  => true,
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
function fh_gf_inject_booking_summary($form) {
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
function fh_gf_booking_notification($notification, $form, $entry) {
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
