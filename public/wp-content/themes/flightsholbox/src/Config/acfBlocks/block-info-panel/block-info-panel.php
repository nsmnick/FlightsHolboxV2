<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$is_preview && !$hide_panel && !$preview_popup_image) {
    $heading = get_field('heading');
    $intro   = get_field('intro');
    $cards   = get_field('cards') ?: [];

    $cards = array_values(array_filter($cards, function ($card) {
        return !empty($card['title']);
    }));

    if (!$cards) {
        echo '<p style="padding:2rem;opacity:0.4;font-style:italic;">No cards added yet — edit the block to add some.</p>';
        return;
    }
?>

<section class="block-info-panel <?php echo $generic_block_settings_classes; ?>">
    <div class="container <?php echo $generic_container_class; ?>">

        <?php if ($heading) : ?>
            <h2 class="block-info-panel__heading"><?php echo esc_html($heading); ?></h2>
        <?php endif; ?>

        <?php if ($intro) : ?>
            <p class="block-info-panel__intro"><?php echo esc_html($intro); ?></p>
        <?php endif; ?>

        <div class="block-info-panel__grid">
            <?php foreach ($cards as $card) :
                $icon_id  = $card['icon'] ?? null;
                $title    = $card['title'] ?? '';
                $text     = $card['text'] ?? '';
                $link     = $card['button']['button_link'] ?? null;
                $colour   = $card['button']['button_colour'] ?? 'gold';
                $btn_class = 'button' . ($colour !== 'gold' ? ' button--' . $colour : '');
            ?>
                <div class="block-info-panel__card">
                    <?php if ($icon_id) : ?>
                        <span class="block-info-panel__icon" aria-hidden="true">
                            <?php echo wp_get_attachment_image($icon_id, 'thumbnail', false, ['loading' => 'lazy']); ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($title) : ?>
                        <span class="block-info-panel__title"><?php echo esc_html($title); ?></span>
                    <?php endif; ?>

                    <?php if ($text) : ?>
                        <p class="block-info-panel__text"><?php echo nl2br(esc_html($text)); ?></p>
                    <?php endif; ?>

                    <?php if ($link && !empty($link['url'])) : ?>
                        <a
                            class="block-info-panel__button <?php echo esc_attr($btn_class); ?>"
                            href="<?php echo esc_url($link['url']); ?>"
                            <?php if (!empty($link['target'])) : ?>target="<?php echo esc_attr($link['target']); ?>" rel="noopener noreferrer"<?php endif; ?>
                        >
                            <?php echo esc_html($link['title'] ?: 'Find out more'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<?php
}
?>
