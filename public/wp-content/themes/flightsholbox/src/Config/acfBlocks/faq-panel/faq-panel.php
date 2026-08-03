<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$preview_popup_image && !$hide_panel) {
    $heading       = get_field('faq_heading');
    $selected_faqs = get_field('selected_faqs') ?: [];

    // Prefer FAQs picked from the real "faq" CPT (shared with the Support
    // page) — the manually-typed "questions" repeater only kicks in as a
    // fallback for existing blocks that haven't been switched over yet.
    if ($selected_faqs) {
        $questions = array_map(fn($post) => [
            'question' => $post->post_title,
            'answer'   => apply_filters('the_content', $post->post_content),
        ], $selected_faqs);
    } else {
        $questions = get_field('questions') ?: [];
    }
?>

<section class="faq-panel <?php echo $generic_block_settings_classes; ?>">
     <div class="container <?php echo $generic_container_class; ?>">

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
                            <?php
                            // force_balance_tags() guards against a stray/unmatched closing
                            // tag inside an answer's rich content (e.g. an extra </div>)
                            // prematurely closing .faq-panel__accordion itself — which
                            // would silently break every FAQ item after the offending one
                            // out of their intended column width.
                            echo wp_kses_post(force_balance_tags($answer));
                            ?>
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
