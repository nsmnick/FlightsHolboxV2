<?php

namespace Theme\Config;

class RestAPI
{
    public static function init()
    {
        add_filter('rest_api_init', [self::class, 'registerCustomRestFields']);
    }

    public static function registerCustomRestFields()
    {
        register_rest_field('post', 'custom_fields', [
            'get_callback' => [self::class, 'getPostFields'],
            'update_callback' => null,
            'schema' => null,
        ]);
    }

    public static function getPostFields(array $object): array
    {
        $image_id = get_post_thumbnail_id($object['id']);

        return [
            'featured_image_src' => $image_id ? wp_get_attachment_image_url($image_id, 'medium_large') : false,
            'featured_image_srcset' => $image_id ? wp_get_attachment_image_srcset($image_id, 'medium_large') : false,
            'featured_image_alt' => $image_id ? get_post_meta($image_id, '_wp_attachment_image_alt', true) : '',
            'formatted_date' => get_the_date('jS M Y', $object['id']),
            'categories_list' => wp_get_post_categories($object['id'], ['fields' => 'names']),
            'link' => get_the_permalink($object['id']),
            'excerpt' => wp_trim_words(get_the_excerpt($object['id']), 20),
        ];
    }
}
