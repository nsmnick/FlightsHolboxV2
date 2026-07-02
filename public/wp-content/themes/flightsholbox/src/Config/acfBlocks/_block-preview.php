<?php

// Config:
//
// The block preview can be set up in a number of ways:
//
// 1) If no preview image is set, the block title is shown in the preview window and main editor window.
// 2) If a preview image is added, and "_is_preview": "true" is set in block.json, it displays in the
//    pop-up block preview window.
// 3) If "show_image_in_editor": true is set in block.json, the image is also shown in the body of the
//    CMS editor.
//
// Optional setting in the block.json "acf" section: "hideFieldsInSidebar": true.
echo 'This is a test';
$show_image_in_editor = isset($block['example']['attributes']['data']['show_image_in_editor'])
    ? $block['example']['attributes']['data']['show_image_in_editor']
    : false;

$preview_image = isset($block['example']['attributes']['data']['preview_image_help'])
    ? $block['example']['attributes']['data']['preview_image_help']
    : false;

$preview_popup_image = false;

// Check preview image in preview window
if (!empty($block['data']['_is_preview'])) {
    echo '<img src="' . THEMEROOT . $preview_image . '" style="width:100%; height:auto;">';
    $preview_popup_image = true;
}

if ($show_image_in_editor) {
    echo '<img src="' . THEMEROOT . $preview_image . '" style="width:100%; height:auto;">';
}
