<?php

define('THEMEROOT', get_stylesheet_directory_uri());

require_once get_stylesheet_directory() . '/src/Autoloader.php';
\Theme\Autoloader::register();

\Theme\Config\ACFBlocks::init();
\Theme\Config\Plugins\ACFPro\ACFPro::init();

class Theme_Setup
{
    public array $assets_map;

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
    }

    public function registerNavMenus()
    {
        register_nav_menu('primary-menu', 'Primary Menu');
        register_nav_menu('footer-menu', 'Footer Menu');
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
        if (
            !$this->isViteHMRAvailable() &&
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
        return !empty($this->getViteDevServerAddress()) &&
            defined('WP_ENVIRONMENT_TYPE') &&
            WP_ENVIRONMENT_TYPE === 'local';
    }
}

new Theme_Setup();


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
}

/**
 * Render a <select> dropdown populated from a WordPress taxonomy.
 *
 * @param string $taxonomy   Taxonomy slug.
 * @param array  $args       Optional WP get_terms() args.
 * @param string $all_label  Text for the default "Select" option.
 * @return string            HTML <select> element.
 */
function fh_get_categories_dropdown(string $taxonomy, array $args = [], string $all_label = 'Select'): string
{
    $terms = get_terms($taxonomy, array_merge(['hide_empty' => false], $args));

    $html  = '<select id="' . esc_attr($taxonomy) . '" name="' . esc_attr($taxonomy) . '" class="search-form__select" data-taxonomy="' . esc_attr($taxonomy) . '">';
    $html .= '<option value="0" selected="selected">' . esc_html($all_label) . '</option>';

    if (!is_wp_error($terms)) {
        foreach ($terms as $term) {
            $html .= '<option value="' . esc_attr($term->term_id) . '">' . esc_html($term->name) . '</option>';
        }
    }

    $html .= '</select>';

    return $html;
}
