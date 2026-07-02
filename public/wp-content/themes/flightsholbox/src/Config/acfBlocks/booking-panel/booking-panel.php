<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$is_preview && !$hide_panel && !$preview_popup_image) {
    $sister_site_url   = get_field('sister_site_url');
    $sister_site_label = get_field('sister_site_label') ?: 'Search for land transfers';
?>

<section class="booking-panel">
    <div class="container">
        <div class="booking-panel__inner">

            <form
                role="search"
                method="POST"
                id="search-embedded-form"
                class="search-form"
                action="<?php echo esc_url(site_url('/flights')); ?>"
            >
                <div class="search-form__group search-form__group--from">
                    <label class="search-form__label" for="locations_from">FROM</label>
                    <?php echo fh_get_categories_dropdown('locations_from', [], 'Select'); ?>
                </div>

                <div class="search-form__group search-form__group--to">
                    <label class="search-form__label" for="locations_to">TO</label>
                    <?php echo fh_get_categories_dropdown('locations_to', [], 'Select'); ?>
                </div>

                <div class="search-form__group search-form__group--people">
                    <label class="search-form__label" for="number_of_people">PEOPLE</label>
                    <?php echo fh_get_categories_dropdown('number_of_people', [], 'Select'); ?>
                </div>

                <div class="search-form__group search-form__group--button">
                    <button id="search-submit" class="search-form__button" type="submit">
                        SEE PRICES
                    </button>
                </div>
            </form>

            <div class="search-options">
                <a class="search-link" href="<?php echo esc_url(site_url('/transfers-price-list')); ?>">
                    See all prices
                </a>
                <?php if ($sister_site_url) : ?>
                    <a class="search-link" href="<?php echo esc_url($sister_site_url); ?>">
                        <?php echo esc_html($sister_site_label); ?>
                    </a>
                <?php endif; ?>
            </div>

        </div>
    </div>
</section>

<?php
}
?>
