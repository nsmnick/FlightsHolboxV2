<?php

namespace Theme\Config;

class Lockdown
{
    public static function init()
    {

        // Remove standard WP header links.
        remove_filter('wp_head', 'rsd_link');
        remove_filter('wp_head', 'wlwmanifest_link');
        remove_filter('wp_head', 'wp_generator');
        add_filter('wpseo_hide_version', '__return_true');

        // Disable unused Wordpress features.
        add_filter('admin_init', [self::class, 'removeAdminComments']);
        add_filter('admin_menu', function () {
            remove_menu_page('edit-comments.php');
        });
        add_filter('comments_array', '__return_empty_array', 10, 2);
        add_filter('comments_open', '__return_false', 20, 2);
        add_filter('pings_open', '__return_false', 20, 2);
        add_filter('show_admin_bar', '__return_false');
        add_filter('the_generator', '__return_empty_string');
        add_filter('xmlrpc_enabled', '__return_false');


        // Users
        add_filter('login_errors', [self::class, 'modifyLoginErrors']);
        add_action('init', [self::class, 'preventAuthorRequests']);
        add_action('rest_authentication_errors', [self::class, 'onlyAllowLoggedInRestAccessToUsers']);
        add_filter('wp_sitemaps_add_provider', [self::class, 'removeAuthorsFromSitemap'], 10, 2);
        add_filter('oembed_response_data', [self::class, 'removeAuthorFromOembed']);
        add_action('template_redirect', [self::class, 'redirectAuthorArchives']);
        add_filter('the_author_posts_link', [self::class, 'modifyTheAuthorPostsLink']);

        // Rest API

        add_filter('rest_endpoints', [self::class, 'removeUnusedEndpoints']);

        // Siteground Editor Purge Cache
        add_filter('sgo_purge_button_capabilities',  [self::class, 'sgo_add_new_role']);
    }



    public static function sgo_add_new_role($default_capabilities)
    {
        // Allow new user role to flush cache.
        $default_capabilities[] = 'delete_others_posts'; // For Editors.
        return $default_capabilities;
    }

    // Lockdown

    public static function removeAdminComments()
    {
        // Redirect any user trying to access comments page.
        global $pagenow;

        if ($pagenow === 'edit-comments.php') {
            wp_redirect(admin_url());
            exit;
        }

        // Remove comments metabox from dashboard.
        remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');

        // Remove comments links from admin bar.
        if (is_admin_bar_showing()) {
            remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
        }

        // Prevent subscribers viewing dashboard profile.
        if (is_admin() && !defined('DOING_AJAX') && (current_user_can('subscriber'))) {
            wp_redirect(home_url());
            exit;
        }

        // Disable support for comments and trackbacks in post types.
        foreach (get_post_types() as $post_type) {
            if (post_type_supports($post_type, 'comments')) {
                remove_post_type_support($post_type, 'comments');
                remove_post_type_support($post_type, 'trackbacks');
            }
        }
    }


    // Rest API

    public static function removeUnusedEndpoints(array $endpoints): array
    {
        // Remove unused Rest API End Points.
        if (isset($endpoints['/wp/v2/users'])) {
            unset($endpoints['/wp/v2/users']);
        }

        if (isset($endpoints['/wp/v2/users/(?P<id>[\d]+)'])) {
            unset($endpoints['/wp/v2/users/(?P<id>[\d]+)']);
        }

        if (isset($endpoints['/wp/v2/settings'])) {
            unset($endpoints['/wp/v2/settings']);
        }

        if (isset($endpoints['/wp/v2/settings/(?P<id>[\d]+)'])) {
            unset($endpoints['/wp/v2/settings/(?P<id>[\d]+)']);
        }

        if (isset($endpoints['/wp/v2/statuses'])) {
            unset($endpoints['/wp/v2/statuses']);
        }

        if (isset($endpoints['/wp/v2/statuses/(?P<id>[\d]+)'])) {
            unset($endpoints['/wp/v2/statuses/(?P<id>[\d]+)']);
        }

        if (isset($endpoints['/wp/v2/comments'])) {
            unset($endpoints['/wp/v2/comments']);
        }

        if (isset($endpoints['/wp/v2/comments/(?P<id>[\d]+)'])) {
            unset($endpoints['/wp/v2/comments/(?P<id>[\d]+)']);
        }

        return $endpoints;
    }

    // Users

    public static function modifyLoginErrors()
    {
        return 'An error occurred. Try again or if you are a bot, please don\'t.';
    }

    public static function preventAuthorRequests()
    {
        if (
            isset($_REQUEST['author'])
            && self::stringContainsNumbers($_REQUEST['author'])
            && ! is_user_logged_in()
        ) {
            wp_die('forbidden - number in author name not allowed = ' . esc_html($_REQUEST['author']));
        }
    }

    public static function onlyAllowLoggedInRestAccessToUsers($access)
    {
        if (is_user_logged_in()) {
            return $access;
        }

        if ((preg_match('/users/i', $_SERVER['REQUEST_URI']) !== 0)
            || (isset($_REQUEST['rest_route']) && (preg_match('/users/i', $_REQUEST['rest_route']) !== 0))
        ) {
            return new \WP_Error(
                'rest_cannot_access',
                'Only authenticated users can access the User endpoint REST API.',
                [
                    'status' => rest_authorization_required_code()
                ]
            );
        }

        return $access;
    }

    private static function stringContainsNumbers($string): bool
    {
        return preg_match('/\\d/', $string) > 0;
    }

    public static function removeAuthorsFromSitemap($provider, $name)
    {
        if ('users' === $name) {
            return false;
        }

        return $provider;
    }

    public static function removeAuthorFromOembed($data)
    {
        unset($data['author_url']);
        unset($data['author_name']);

        return $data;
    }

    public static function redirectAuthorArchives()
    {
        if (is_author() || isset($_GET['author'])) {
            wp_safe_redirect(esc_url(home_url('/')), 301);
        }
    }

    public static function modifyTheAuthorPostsLink($link)
    {
        if (! is_admin()) {
            return get_the_author();
        }
        return $link;
    }
}
