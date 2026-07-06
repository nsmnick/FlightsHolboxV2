<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no">

    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

    <header>
        <div id="page-header" class="page-header">
            <div class="header-container">

                <a href="<?php echo site_url(); ?>/" id="badgeLogoTarget" class="site-badge-logo-target" aria-label="<?php bloginfo('name'); ?>"></a>

                <a href="<?php echo site_url(); ?>/" id="badgeLogoMorph" class="site-badge-logo-morph" aria-hidden="true" tabindex="-1">
                    <span class="site-badge-logo-morph__img"></span>
                </a>

                <div class="container">
                    <div class="header-detail-container">
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

                <div class="mobile-menu-toggle">
                    <div id="mobile-menu-toggle" class="mobile-menu-toggle__button">
                        <div class="mobile-menu-toggle__left"></div>
                        <div class="mobile-menu-toggle__right"></div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main id="main">
