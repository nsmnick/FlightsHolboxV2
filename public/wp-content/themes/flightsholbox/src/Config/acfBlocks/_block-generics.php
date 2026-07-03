<?php

// Generic block settings are passed from a panel, but if a panel is called directly this won't exist.

$generic_block_settings = get_field('panel_settings');

$generic_block_settings_classes = Theme\Utils::get_generic_block_settings_classes($generic_block_settings);
$generic_container_class = Theme\Utils::get_container_size_class($generic_block_settings);

$hide_panel = false;

// Render anchor tag if filled in in CMS
if (isset($block['anchor']) && $block['anchor'] != false) {
    echo '<a style="scroll-margin-top: 140px;" name="' . $block['anchor'] . '" id="' . $block['anchor'] . '"></a>';
}
