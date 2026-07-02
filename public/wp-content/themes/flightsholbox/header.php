<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="format-detection" content="telephone=no">

    <title>
        <?php wp_title(' - ', true, 'right'); ?>
    </title>

    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
    <link rel="icon" type="image/png" href="<?php echo THEMEROOT; ?>/images/favicon.png">

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

    <header>
        <div id="page-header" class="page-header">
            <div class="header-container">
                <div class="container">
                    <div class="header-detail-container">

                        <div class="header-detail-container__col1">
                            <a href="<?php echo site_url(); ?>/">
                                <div class="site-header-logo"></div>
                            </a>
                        </div>

                        <div class="header-detail-container__col2">
                            <nav id="main-menu" class="main-menu main-menu--unscrolled">
                                <ul>
                                    <?php
                                    wp_nav_menu([
                                        'theme_location' => 'primary-menu',
                                        'container' => '',
                                        'items_wrap' => '%3$s'
                                    ]);
                                    ?>
                                </ul>
                            </nav>
                        </div>

                    </div>
                </div>

                <div class="mobile-menu-toggle">
                    <div id="mobile-menu-toggle" class="mobile-menu-toggle__button">
                        <div class="mobile-menu-toggle__left"></div>
                        <div class="mobile-menu-toggle__right"></div>
                    </div>
                </div>
            </div>
        </div>
    </header>
