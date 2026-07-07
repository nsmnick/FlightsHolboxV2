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

    /**
     * Web Mercator latitude -> linear "Mercator Y" — the y-coordinate used by
     * every standard web map projection (Mapbox, Google Maps, OSM tiles), not
     * plain latitude. Needed because latitude spacing is non-linear in Mercator;
     * naive linear interpolation would silently drift pins off true position.
     */
    private static function mercator_y(float $lat_deg): float
    {
        $lat_deg = max(-85.05112878, min(85.05112878, $lat_deg));
        return log(tan(M_PI / 4 + deg2rad($lat_deg) / 2));
    }

    /**
     * Convert a lat/lng pair into an x/y percent position (0-100) within a
     * static map image, given the geographic bounding box the image was
     * exported to cover. Returns null (skip rendering) if any input is
     * missing/non-numeric or the bounds are degenerate — mirrors the old
     * map_x/map_y empty-value skip behaviour used before real coordinates.
     *
     * Deliberately uses is_numeric() rather than checking against '' / null:
     * ACF's get_field() returns false (not '' or null) for a field that
     * doesn't exist yet or has never been saved for a given term, and a
     * bare === check would let that fall through to (float) false === 0.0
     * — a real coordinate in the Gulf of Guinea, not "missing".
     */
    public static function latlng_to_percent($lat, $lng, $north, $south, $west, $east): ?array
    {
        if (!is_numeric($lat) || !is_numeric($lng)) {
            return null;
        }
        if (!is_numeric($north) || !is_numeric($south) || !is_numeric($west) || !is_numeric($east)) {
            return null;
        }

        $lat = (float) $lat;
        $lng = (float) $lng;
        $north = (float) $north;
        $south = (float) $south;
        $west = (float) $west;
        $east = (float) $east;

        if ($north <= $south || $east <= $west) {
            return null;
        }

        // Longitude is linear in Mercator (meridians evenly spaced horizontally).
        $x = ($lng - $west) / ($east - $west) * 100;

        // Latitude must go through the Mercator transform first, then flip
        // (image y grows downward, latitude grows upward).
        $merc_lat = self::mercator_y($lat);
        $merc_north = self::mercator_y($north);
        $merc_south = self::mercator_y($south);
        $y = (1 - (($merc_lat - $merc_south) / ($merc_north - $merc_south))) * 100;

        return ['x' => $x, 'y' => $y];
    }
}
