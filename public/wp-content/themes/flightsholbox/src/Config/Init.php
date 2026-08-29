<?php

namespace Theme\Config;

class Init
{

    public static function init()
    {
        // V3 ACF Blocks
        add_filter('acf/blocks/default_block_version', [self::class, 'set_block_version'], 10, 2);

        add_action('admin_head', [self::class, 'tidy_up_WP_interface']);
    }

    // Set all blocks to v3
    public static function set_block_version($version, $block)
    {
        return 3;
    }


    // Overridden styles to hide preview button in top navigation and tidy up interface elements
    public static function tidy_up_WP_interface()
    {
        echo '<style type="text/css">
        .editor-preview-dropdown {
            display: none !important;
        }
        .mce-content-body {
            border: solid 1px #ccc !important;
            min-height: 300px;
            padding: 10px; 
        }

        #poststuff .postbox-container {
            width: 93%;
            margin: 0 auto;
            border: solid 1px #ccc;
            float: unset;           
        }

        .components-modal__frame.is-full-screen {
                width: 80% !important;
            }


        .block-editor-block-list__block.is-selected {
            border: solid 1px #357ab5;
        }


        .components-placeholder__label {
            svg {
                display: none;
            }
        }


        :root :where(.editor-styles-wrapper)::after {
           content: "";
            display: block;
            height: 0px !important;
        }

        </style>
    ';

        if (function_exists('wpseo_init')) {
            echo '<style>.wpseo-metabox-content,.wpseo-meta-section,.wpseo-meta-section-react,.postbox,.wp-block,.acf-postbox {max-width: 95%;margin: 40px auto;}</style>';
        }
    }
}
