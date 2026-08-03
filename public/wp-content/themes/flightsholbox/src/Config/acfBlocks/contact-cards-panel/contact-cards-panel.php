<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$preview_popup_image && !$hide_panel) {
    // Splits a textarea's lines into a clean array — used for both the
    // checklist and the contact-details block, so editors can add/remove/
    // reorder lines just by typing, no repeater required. Declared as a
    // local closure (not a named function) since this template can be
    // included more than once per page if the block is used twice.
    $lines = function ($raw) {
        if (!$raw) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode("\n", $raw))));
    };

    $heading = get_field('heading');
    $intro   = get_field('intro');
    $cards   = get_field('cards') ?: [];

    $policy_icon    = get_field('policy_icon');
    $policy_heading = get_field('policy_heading');
    $policy_text    = get_field('policy_text');

    if (!$heading && !$intro && !$cards && !$policy_heading && !$policy_text) {
        return;
    }
?>

<section class="contact-cards-panel <?php echo $generic_block_settings_classes; ?>">
     <div class="container <?php echo esc_attr($generic_container_class); ?>">

        <?php if ($heading || $intro) : ?>
            <div class="contact-cards-panel__heading">
                <?php if ($heading) : ?>
                    <h2><?php echo esc_html($heading); ?></h2>
                <?php endif; ?>

                <?php if ($intro) : ?>
                    <div class="contact-cards-panel__intro"><?php echo wp_kses_post($intro); ?></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($cards) : ?>
            <div class="contact-cards-panel__grid">
                <?php foreach ($cards as $card) :
                    $icon           = $card['icon'] ?? '';
                    $card_heading   = $card['card_heading'] ?? '';
                    $card_subheading = $card['card_subheading'] ?? '';
                    $card_text      = $card['card_text'] ?? '';
                    $list_items     = $lines($card['list_items'] ?? '');
                    $contact_lines  = $lines($card['contact_info'] ?? '');

                    $buttons = array_filter(array_map(function ($button) {
                        $link = $button['button_link'] ?? null;
                        if (!$button || !$link || empty($link['url'])) {
                            return null;
                        }

                        $colour = $button['button_colour'] ?? 'navy';
                        return [
                            'link'  => $link,
                            'class' => 'button' . ($colour !== 'gold' ? ' button--' . $colour : ''),
                        ];
                    }, [$card['button_1'] ?? null, $card['button_2'] ?? null]));
                ?>
                    <div class="contact-cards-panel__card">
                        <?php if ($icon) : ?>
                            <span class="contact-cards-panel__icon" aria-hidden="true">
                                <?php echo wp_get_attachment_image($icon, 'thumbnail'); ?>
                            </span>
                        <?php endif; ?>

                        <?php if ($card_heading) : ?>
                            <h3 class="contact-cards-panel__card-heading"><?php echo esc_html($card_heading); ?></h3>
                        <?php endif; ?>

                        <?php if ($card_subheading) : ?>
                            <h4 class="contact-cards-panel__card-subheading"><?php echo esc_html($card_subheading); ?></h4>
                        <?php endif; ?>

                        <?php if ($card_text) : ?>
                            <div class="contact-cards-panel__card-text"><?php echo wp_kses_post($card_text); ?></div>
                        <?php endif; ?>

                        <?php if ($list_items) : ?>
                            <ul class="contact-cards-panel__list">
                                <?php foreach ($list_items as $item) : ?>
                                    <li><?php echo esc_html($item); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <?php if ($contact_lines) : ?>
                            <?php if ($list_items) : ?><hr class="contact-cards-panel__divider"><?php endif; ?>
                            <div class="contact-cards-panel__contact-info">
                                <?php foreach ($contact_lines as $line) : ?>
                                    <span class="contact-cards-panel__contact-line"><?php echo esc_html($line); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($buttons) : ?>
                            <div class="contact-cards-panel__buttons">
                                <?php foreach ($buttons as $btn) : ?>
                                    <a
                                        class="<?php echo esc_attr($btn['class']); ?> contact-cards-panel__button"
                                        href="<?php echo esc_url($btn['link']['url']); ?>"
                                        <?php if (!empty($btn['link']['target'])) : ?>target="<?php echo esc_attr($btn['link']['target']); ?>" rel="noopener noreferrer"<?php endif; ?>
                                    >
                                        <?php echo esc_html($btn['link']['title'] ?: 'Learn more'); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($policy_heading || $policy_text) : ?>
            <div class="contact-cards-panel__policy">
                <?php if ($policy_icon) : ?>
                    <span class="contact-cards-panel__policy-icon" aria-hidden="true">
                        <?php echo wp_get_attachment_image($policy_icon, 'thumbnail'); ?>
                    </span>
                <?php endif; ?>

                <?php if ($policy_heading) : ?>
                    <h2 class="contact-cards-panel__policy-heading"><?php echo esc_html($policy_heading); ?></h2>
                <?php endif; ?>

                <?php if ($policy_text) : ?>
                    <div class="contact-cards-panel__policy-text"><?php echo wp_kses_post($policy_text); ?></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php
}
?>
