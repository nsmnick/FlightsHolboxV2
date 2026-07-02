<?php

namespace Theme\Config;

class ACFBlocks
{
    public static function init()
    {
        // Remove block directory
        remove_action('enqueue_block_editor_assets', 'wp_enqueue_editor_block_directory_assets');

        // Register ACF blocks
        add_action('init', [self::class, 'register_acf_blocks']);
        add_filter('allowed_block_types_all', [self::class, 'set_allowed_block_types'], 10, 2);
        add_action('after_setup_theme', [self::class, 'remove_theme_patterns']);
    }

    // Register custom ACF blocks. Add one line per block folder in /acfBlocks.
    public static function register_acf_blocks()
    {
        register_block_type(__DIR__ . '/acfBlocks/example-text-panel');
        register_block_type(__DIR__ . '/acfBlocks/hero-panel');
        register_block_type(__DIR__ . '/acfBlocks/booking-panel');
        register_block_type(__DIR__ . '/acfBlocks/text-panel');
        register_block_type(__DIR__ . '/acfBlocks/video-panel');
        register_block_type(__DIR__ . '/acfBlocks/faq-panel');
        register_block_type(__DIR__ . '/acfBlocks/image-column-panel');
        register_block_type(__DIR__ . '/acfBlocks/feature-panel');
        register_block_type(__DIR__ . '/acfBlocks/testimonial-panel');
    }

    // Remove WP default blocks and allocate which blocks can be used by pages and posts by default.
    public static function set_allowed_block_types($block_editor_context, $editor_context)
    {
        if (!empty($editor_context->post)) {
            if ('post' === $editor_context->post->post_type) {
                return array(
                    'core/freeform'
                );
            }

            if ('page' === $editor_context->post->post_type) {
                return array(
                    'acf/example-text-panel',
                    'acf/hero-panel',
                    'acf/booking-panel',
                    'acf/text-panel',
                    'acf/video-panel',
                    'acf/faq-panel',
                    'acf/image-column-panel',
                    'acf/feature-panel',
                    'acf/testimonial-panel',
                );
            }
        }

        return $block_editor_context;
    }

    // Remove core patterns from interface
    public static function remove_theme_patterns()
    {
        remove_theme_support('core-block-patterns');
    }
}
