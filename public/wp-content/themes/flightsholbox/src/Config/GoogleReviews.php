<?php

namespace Theme\Config;

class GoogleReviews
{
    const OPTION_KEY = 'fh_google_reviews_data';
    const CRON_HOOK = 'fh_refresh_google_reviews';

    public static function init()
    {
        // Keep the recurring refresh scheduled, and backfill once if we've
        // never fetched. Both run on admin_init only, never on a visitor's
        // page load, so the front end never waits on a live Google API call.
        add_action('admin_init', [self::class, 'schedule_refresh']);
        add_action(self::CRON_HOOK, [self::class, 'fetch_and_store']);
    }

    public static function schedule_refresh()
    {
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_event(time(), 'daily', self::CRON_HOOK);
        }

        if (get_option(self::OPTION_KEY) === false) {
            self::fetch_and_store();
        }
    }

    // Cached data written by the daily refresh. Block templates read this —
    // they never call the Google API directly.
    public static function get_data()
    {
        $data = get_option(self::OPTION_KEY, false);
        return $data ?: null;
    }

    public static function fetch_and_store()
    {
        $place_id = function_exists('get_field') ? get_field('google_place_id', 'option') : '';

        if (!$place_id || !defined('GOOGLE_PLACES_API_KEY') || !GOOGLE_PLACES_API_KEY) {
            return;
        }

        $url = add_query_arg(
            [
                'place_id' => $place_id,
                'fields' => 'name,rating,user_ratings_total,url,reviews',
                'key' => GOOGLE_PLACES_API_KEY,
            ],
            'https://maps.googleapis.com/maps/api/place/details/json'
        );

        $response = wp_remote_get($url, ['timeout' => 15]);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (($body['status'] ?? '') !== 'OK' || empty($body['result'])) {
            return;
        }

        $result = $body['result'];

        update_option(self::OPTION_KEY, [
            'name' => $result['name'] ?? '',
            'rating' => $result['rating'] ?? 0,
            'review_count' => $result['user_ratings_total'] ?? 0,
            'google_url' => $result['url'] ?? '',
            'reviews' => $result['reviews'] ?? [],
            'fetched_at' => time(),
        ], false);
    }
}
