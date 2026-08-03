<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$preview_popup_image && !$hide_panel) {
    $heading = get_field('heading');
    $steps   = get_field('steps') ?: [];

    if (!$heading && !$steps) {
        return;
    }
?>

<div class="faq-steps-panel">
    <?php if ($heading) : ?>
        <h2 class="faq-steps-panel__heading"><?php echo esc_html($heading); ?></h2>
    <?php endif; ?>

    <?php if ($steps) : ?>
        <div class="faq-steps-panel__list">
            <?php foreach ($steps as $i => $step) :
                $step_heading = $step['steps_heading'] ?? '';
                $step_text    = $step['step_text'] ?? '';
            ?>
                <div class="faq-steps-panel__step">
                    <span class="faq-steps-panel__number" aria-hidden="true"><?php echo esc_html($i + 1); ?></span>
                    <div class="faq-steps-panel__step-content">
                        <?php if ($step_heading) : ?>
                            <h3 class="faq-steps-panel__step-heading"><?php echo esc_html($step_heading); ?></h3>
                        <?php endif; ?>

                        <?php if ($step_text) : ?>
                            <div class="faq-steps-panel__step-text"><?php echo wp_kses_post($step_text); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
}
?>
