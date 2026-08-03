<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$preview_popup_image && !$hide_panel) {
    $cards = get_field('cards') ?: [];

    if (!$cards) {
        echo '<p style="padding:2rem;opacity:0.4;font-style:italic;">No cards added yet — edit the block to add up to three.</p>';
        return;
    }

    $cards = array_slice($cards, 0, 3);

    $count_modifier = [1 => '', 2 => ' card-panel__grid--two', 3 => ' card-panel__grid--three'][count($cards)] ?? '';
?>

<section class="card-panel content <?php echo $generic_block_settings_classes; ?>">
    <div class="container animate fade-up <?php echo esc_attr($generic_container_class); ?>">
        <div class="card-panel__grid<?php echo esc_attr($count_modifier); ?>">
            <?php foreach ($cards as $card) :
                $card_title = $card['card_title'] ?? '';
                $show_steps = !empty($card['show_step_numbers']);
                $items      = $card['items'] ?: [];
            ?>
                <div class="card-panel__card">
                    <?php if ($card_title) : ?>
                        <div class="card-panel__header">
                            <h3 class="card-panel__title"><?php echo esc_html($card_title); ?></h3>
                            <span class="card-panel__accent" aria-hidden="true"></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($items) : ?>
                        <div class="card-panel__body">
                            <?php foreach ($items as $i => $item) :
                                $heading = $item['item_heading'] ?? '';
                                $text    = $item['item_text'] ?? '';
                            ?>
                                <div class="card-panel__item<?php echo $show_steps ? ' card-panel__item--step' : ''; ?>">
                                    <?php if ($show_steps) : ?>
                                        <span class="card-panel__step-number" aria-hidden="true"><?php echo (int) ($i + 1); ?></span>
                                    <?php endif; ?>
                                    <div class="card-panel__item-content">
                                        <?php if ($heading) : ?>
                                            <h4 class="card-panel__item-heading"><?php echo esc_html($heading); ?></h4>
                                        <?php endif; ?>
                                        <?php if ($text) : ?>
                                            <div class="card-panel__item-text"><?php echo wp_kses_post($text); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php
}
?>
