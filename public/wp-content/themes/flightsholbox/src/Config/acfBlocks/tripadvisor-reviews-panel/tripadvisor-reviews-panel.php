<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$hide_panel && !$preview_popup_image) {
    $heading = get_field('heading');

    // Raw shortcode field (not a feed-ID field) so it works with whatever
    // Rich Showcase for Google Reviews already outputs (e.g. [grw id="90"])
    // without this block needing to know that plugin's internals. Left
    // blank hides the Google column entirely and this renders exactly as
    // it did before — the API/auto-refresh side is separate from this.
    $google_shortcode = trim((string) get_field('google_reviews_shortcode'));

    // Each variant below is a full TripAdvisor Widget Center embed for this
    // exact listing (locationId=12454502) — the ids/classes inside are
    // assigned by TripAdvisor's own script per widget instance and aren't
    // ours to regenerate, so each style is kept as a complete, exact block
    // rather than trying to parameterise the two into shared markup.
    $widgets = [
        'scrolling_rave' => [
            'label' => 'Scrolling Reviews',
            'html'  => '
                <div id="TA_cdsscrollingravewide718" class="TA_cdsscrollingravewide">
                    <ul id="QVIgK4oiN" class="TA_links KGFrGibomZ">
                        <li id="riC0nq1" class="qp760Cz">
                            <a target="_blank" href="https://www.tripadvisor.com/Attraction_Review-g616319-d12454502-Reviews-Flights_Holbox-Holbox_Island_Yucatan_Peninsula.html">
                                <img src="https://static.tacdn.com/img2/brand_refresh/Tripadvisor_lockup_vertical.svg" alt="TripAdvisor" class="widEXCIMG" id="CDSWIDEXCLOGO" />
                            </a>
                        </li>
                    </ul>
                </div>
                <script async src="https://www.jscache.com/wejs?wtype=cdsscrollingravewide&uniq=718&locationId=12454502&lang=en_US&border=true&shadow=true&display_version=2" data-loadtrk onload="this.loadtrk=true"></script>
            ',
        ],
        'ratings_only' => [
            'label' => 'Ratings Summary',
            'html'  => '
                <div id="TA_cdsratingsonlynarrow758" class="TA_cdsratingsonlynarrow">
                    <ul id="csisnN5bGE" class="TA_links XOxVY40fzUD0">
                        <li id="KpOPv57" class="1e22hrVl4">
                            <a target="_blank" href="https://www.tripadvisor.com/Attraction_Review-g616319-d12454502-Reviews-Flights_Holbox-Holbox_Island_Yucatan_Peninsula.html">
                                <img src="https://www.tripadvisor.com/img/cdsi/img2/branding/v2/Tripadvisor_lockup_horizontal_secondary_registered-18034-2.svg" alt="TripAdvisor" />
                            </a>
                        </li>
                    </ul>
                </div>
                <script async src="https://www.jscache.com/wejs?wtype=cdsratingsonlynarrow&uniq=758&locationId=12454502&lang=en_US&border=false&shadow=true&display_version=2" data-loadtrk onload="this.loadtrk=true"></script>
            ',
        ],
    ];

    $style      = get_field('style') ?: 'scrolling_rave';
    $widget     = $widgets[$style] ?? $widgets['scrolling_rave'];
    $has_google = $google_shortcode !== '';
?>

<section class="tripadvisor-reviews-panel content tripadvisor-reviews-panel--<?php echo esc_attr($style); ?><?php echo $has_google ? ' tripadvisor-reviews-panel--split' : ''; ?> <?php echo $generic_block_settings_classes; ?>">
    <div class="container <?php echo esc_attr($generic_container_class); ?>">

        <?php if ($heading) : ?>
            <h2 class="tripadvisor-reviews-panel__heading"><?php echo esc_html($heading); ?></h2>
        <?php endif; ?>

        <div class="tripadvisor-reviews-panel__row">
            <div class="tripadvisor-reviews-panel__widget">
                <?php echo $widget['html']; ?>
            </div>

            <?php if ($has_google) : ?>
                <div class="tripadvisor-reviews-panel__google">
                    <?php echo do_shortcode($google_shortcode); ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>

<?php
}
?>
