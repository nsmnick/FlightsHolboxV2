<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$preview_popup_image && !$hide_panel) {
    $label   = get_field('label');
    $heading = get_field('heading');
    $intro   = get_field('intro');
    $steps   = get_field('steps') ?: [];
    $button  = get_field('button');

    if (!$heading && !$steps) {
        return;
    }

    $link      = $button['button_link'] ?? null;
    $colour    = $button['button_colour'] ?? 'navy';
    $btn_class = 'button' . ($colour !== 'gold' ? ' button--' . $colour : '');
?>

<section class="card-process-panel <?php echo $generic_block_settings_classes; ?>">
     <div class="container <?php echo esc_attr($generic_container_class); ?>">

        <?php if ($label || $heading || $intro) : ?>
            <div class="card-process-panel__heading">
                <?php if ($label) : ?>
                    <p class="card-process-panel__label"><?php echo esc_html($label); ?></p>
                <?php endif; ?>

                <?php if ($heading) : ?>
                    <h2><?php echo esc_html($heading); ?></h2>
                <?php endif; ?>

                <?php if ($intro) : ?>
                    <p class="card-process-panel__intro"><?php echo esc_html($intro); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($steps) : ?>
            <div class="card-process-panel__grid">
                <?php foreach ($steps as $i => $step) :
                    $icon         = $step['icon'] ?? null;
                    $step_heading = $step['heading'] ?? '';
                    $step_text    = $step['text'] ?? '';
                ?>
                    <div class="card-process-panel__step">
                        <span class="card-process-panel__number"><?php echo esc_html(sprintf('%02d', $i + 1)); ?></span>

                        <?php if ($icon) : ?>
                            <span class="card-process-panel__icon" aria-hidden="true">
                                <?php echo wp_get_attachment_image($icon, 'full'); ?>
                            </span>
                        <?php endif; ?>

                        <?php if ($step_heading) : ?>
                            <h3><?php echo esc_html($step_heading); ?></h3>
                        <?php endif; ?>

                        <?php if ($step_text) : ?>
                            <p><?php echo esc_html($step_text); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($link && !empty($link['url'])) : ?>
            <div class="card-process-panel__button-wrap">
                <a
                    class="<?php echo esc_attr($btn_class); ?>"
                    href="<?php echo esc_url($link['url']); ?>"
                    <?php if (!empty($link['target'])) : ?>target="<?php echo esc_attr($link['target']); ?>" rel="noopener noreferrer"<?php endif; ?>
                >
                    <?php echo esc_html($link['title'] ?: 'Find out more'); ?>
                </a>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php
}
?>
