<?php

namespace Theme;

class Utils
{
    public static function debug(mixed $variable): void
    {
        echo '<pre>' . print_r($variable, true) . '</pre>';
    }

    public static function getTemplateName(): string
    {
        global $template;
        return basename($template);
    }

    public static function getExcerpt($postID)
    {
        $excerpt = get_the_excerpt($postID);

        if (has_excerpt($postID)) {
            $excerpt = wp_trim_words($excerpt, apply_filters("excerpt_length", 30));
        }

        return $excerpt;
    }

    public static function getTrimmedHeading($heading)
    {
        if ($heading) {
            $heading = wp_trim_words($heading, 16);
        }

        return $heading;
    }

    public static function get_image_html($image_id, $sizes = 1): string
    {
        if ($image_id === 0) {
            return '';
        }

        switch ($sizes) {
            case 3:
                $sizes = '(max-width: 480px) 100vw, (max-width: 1024px) 50vw, 33.33vw';
                break;
            case 2:
                $sizes = '(max-width: 480px) 100vw, 50vw';
                break;
            default:
                $sizes = '100vw';
        }

        $image_src = wp_get_attachment_image_url($image_id, 'full');
        $image_srcset = wp_get_attachment_image_srcset($image_id, 'full');
        $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
        return <<<IMAGE
            <img src="{$image_src}" srcset="{$image_srcset}" sizes="{$sizes}" alt="{$image_alt}">
        IMAGE;
    }

    /**
     * Build a class string from the shared "Generic Block Settings" ACF field group
     * (top_padding, bottom_padding, panel_decoration, custom_class, background_colour).
     */
    public static function get_generic_block_settings_classes($generic_block_settings)
    {
        $top_padding = (isset($generic_block_settings['top_padding']) ? $generic_block_settings['top_padding'] : '');
        $bottom_padding = (isset($generic_block_settings['bottom_padding']) ? $generic_block_settings['bottom_padding'] : '');
        $background_colour = (isset($generic_block_settings['background_colour']) ? $generic_block_settings['background_colour'] : '');
        $panel_decoration_value = (isset($generic_block_settings['panel_decoration']) ? $generic_block_settings['panel_decoration'] : 'br-none');

        $generic_block_class = '';
        $generic_block_class .= ($top_padding == "default" ? '' : ' tp-' . $top_padding);
        $generic_block_class .= ($bottom_padding == "default" ? '' : ' bp-' . $bottom_padding);

        if ($panel_decoration_value != 'none') {
            $generic_block_class .= ' ' . $panel_decoration_value;
        }

        $custom_class = (isset($generic_block_settings['custom_class']) ? $generic_block_settings['custom_class'] : '');

        if ($custom_class != '') {
            $generic_block_class .= ' ' . $custom_class . ' ';
        }

        if ($background_colour) {
            $generic_block_class .= ' bgc-' . $background_colour . ' ';
        }

        return $generic_block_class;
    }

    public static function get_container_size_class($generic_block_settings)
    {
        $size = isset($generic_block_settings['container_size']) ? $generic_block_settings['container_size'] : '';
        if (!$size || $size === 'default') {
            return '';
        }
        return 'container--' . $size;
    }

    public static function get_link_url($link_url, $link_page)
    {
        if ($link_page) {
            return $link_page;
        } else {
            return $link_url;
        }
    }

    /**
     * Recursively find all blocks of a given name within a parsed block tree
     * (as returned by parse_blocks()), including blocks nested inside
     * containers like Group or Columns.
     */
    public static function find_blocks_by_name(array $blocks, string $block_name): array
    {
        $found = [];

        foreach ($blocks as $block) {
            if (($block['blockName'] ?? null) === $block_name) {
                $found[] = $block;
            }

            if (!empty($block['innerBlocks'])) {
                $found = array_merge($found, self::find_blocks_by_name($block['innerBlocks'], $block_name));
            }
        }

        return $found;
    }

    /**
     * The anchor id a Table Panel is reachable at: its manually-set HTML anchor
     * if one was given, otherwise a slug derived from its heading, otherwise a
     * fallback based on the block's own id. Shared so the Table Panel and the
     * Hyperlink Panel that jumps to it always agree on the same id.
     */
    public static function get_table_panel_anchor(string $heading, string $explicit_anchor = '', string $block_id = ''): string
    {
        if ($explicit_anchor !== '') {
            return $explicit_anchor;
        }

        if ($heading !== '') {
            return sanitize_title($heading);
        }

        return 'table-' . preg_replace('/[^a-zA-Z0-9_-]/', '', $block_id);
    }
}
