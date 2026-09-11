<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$hide_panel && !$preview_popup_image) {
    $steps = get_field('steps') ?: [];

    if (!$steps) {
        return;
    }
?>

<section class="card-steps-panel <?php echo $generic_block_settings_classes; ?>">
    <div class="container <?php echo esc_attr($generic_container_class); ?>">
        <div class="card-steps-panel__grid">
            <?php foreach ($steps as $i => $step) :
                $icon         = $step['icon'] ?? null;
                $step_heading = $step['heading'] ?? '';
                $step_text    = $step['text'] ?? '';
                $completed    = !empty($step['completed']);
            ?>
                <div class="card-steps-panel__step">
                    <span class="card-steps-panel__icon-wrap">
                        <span class="card-steps-panel__icon" aria-hidden="true">
                            <?php if ($icon) : ?>
                                <?php echo wp_get_attachment_image($icon, 'full'); ?>
                            <?php endif; ?>
                        </span>

                        <?php if ($completed) : ?>
                            <span class="card-steps-panel__badge">
                                <svg width="12" height="12" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M3 8.5L6.5 12L13 4.5" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span class="sr-only">Completed</span>
                            </span>
                        <?php endif; ?>
                    </span>

                    <span class="card-steps-panel__number" aria-hidden="true"><?php echo esc_html(sprintf('%02d', $i + 1)); ?></span>

                    <?php if ($step_heading) : ?>
                        <h3 class="card-steps-panel__heading"><?php echo esc_html($step_heading); ?></h3>
                    <?php endif; ?>

                    <?php if ($step_text) : ?>
                        <p class="card-steps-panel__text"><?php echo esc_html($step_text); ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php
}
?>
