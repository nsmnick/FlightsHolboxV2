<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$preview_popup_image && !$hide_panel) {
    $heading   = get_field('faq_heading');
    $questions = get_field('questions') ?: [];
?>

<section class="faq-panel <?php echo $generic_block_settings_classes; ?>">
    <div class="container">

        <?php if ($heading) : ?>
            <h2 class="faq-panel__title"><?php echo esc_html($heading); ?></h2>
        <?php endif; ?>

        <?php if ($questions) : ?>
            <div class="faq-panel__accordion">
                <?php foreach ($questions as $item) :
                    $question = $item['question'] ?? '';
                    $answer   = $item['answer']   ?? '';
                    if (!$question) continue;
                ?>
                    <details class="faq-item">
                        <summary class="faq-item__summary">
                            <span class="faq-item__question"><?php echo esc_html($question); ?></span>
                        </summary>
                        <div class="faq-item__content">
                            <?php echo wp_kses_post($answer); ?>
                        </div>
                    </details>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <p style="opacity:0.4; font-style:italic;">No questions added yet — edit the block to add FAQ items.</p>
        <?php endif; ?>

    </div>
</section>

<?php
}
?>
