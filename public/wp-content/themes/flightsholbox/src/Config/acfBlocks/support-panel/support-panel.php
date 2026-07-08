<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$preview_popup_image && !$hide_panel) {
    $heading = get_field('heading');
    $intro   = get_field('intro');
    $topics  = get_field('topics') ?: [];

    // Drop topics with no name and no FAQs — keeps a half-filled-in repeater
    // row from rendering an empty tab. Each topic also needs a stable slug
    // for the nav button/panel pairing and the search-visibility toggling.
    $topics = array_values(array_filter(array_map(function ($topic, $index) {
        $name = $topic['topic_name'] ?? '';
        $faqs = array_values(array_filter($topic['faqs'] ?: [], fn($faq) => !empty($faq['question'])));

        if (!$name || !$faqs) {
            return null;
        }

        return [
            'slug'  => sanitize_title($name) ?: ('topic-' . $index),
            'name'  => $name,
            'intro' => $topic['topic_intro'] ?? '',
            'faqs'  => $faqs,
        ];
    }, $topics, array_keys($topics))));

    if (!$topics) {
        echo '<p style="padding:2rem;opacity:0.4;font-style:italic;">No topics added yet — edit the block to add support topics.</p>';
        return;
    }
?>

<section class="support-panel animate fade-up <?php echo $generic_block_settings_classes; ?>">
    <div class="container <?php echo $generic_container_class; ?>">

        <?php if ($heading) : ?>
            <h1 class="support-panel__heading"><?php echo esc_html($heading); ?></h1>
        <?php endif; ?>

        <?php if ($intro) : ?>
            <p class="support-panel__intro"><?php echo esc_html($intro); ?></p>
        <?php endif; ?>

        <div class="support-panel__search-wrap">
            <input
                type="text"
                class="support-panel__search"
                placeholder="Search for an answer&hellip;"
                aria-label="Search support topics"
            >
            <p class="support-panel__no-results" hidden>No matching questions found.</p>
        </div>

        <div class="support-panel__layout">
            <nav class="support-panel__nav" aria-label="Support topics">
                <?php foreach ($topics as $i => $topic) : ?>
                    <button
                        type="button"
                        class="support-panel__nav-item<?php echo $i === 0 ? ' is-active' : ''; ?>"
                        data-topic="<?php echo esc_attr($topic['slug']); ?>"
                    >
                        <?php echo esc_html($topic['name']); ?>
                    </button>
                <?php endforeach; ?>
            </nav>

            <div class="support-panel__content">
                <?php foreach ($topics as $i => $topic) : ?>
                    <div
                        class="support-panel__topic<?php echo $i === 0 ? ' is-active' : ''; ?>"
                        data-topic="<?php echo esc_attr($topic['slug']); ?>"
                        <?php if ($i !== 0) : ?>hidden<?php endif; ?>
                    >
                        <h2 class="support-panel__topic-heading"><?php echo esc_html($topic['name']); ?></h2>

                        <?php if ($topic['intro']) : ?>
                            <p class="support-panel__topic-intro"><?php echo esc_html($topic['intro']); ?></p>
                        <?php endif; ?>

                        <div class="faq-panel__accordion">
                            <?php foreach ($topic['faqs'] as $faq) :
                                $question = $faq['question'] ?? '';
                                $answer   = $faq['answer']   ?? '';
                                $search   = strtolower($question . ' ' . wp_strip_all_tags($answer));
                            ?>
                                <details class="faq-item" data-search="<?php echo esc_attr($search); ?>">
                                    <summary class="faq-item__summary">
                                        <span class="faq-item__question"><?php echo esc_html($question); ?></span>
                                    </summary>
                                    <div class="faq-item__content">
                                        <?php echo wp_kses_post($answer); ?>
                                    </div>
                                </details>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</section>

<?php
}
?>
