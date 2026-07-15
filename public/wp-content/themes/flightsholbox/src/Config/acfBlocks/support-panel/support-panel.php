<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$preview_popup_image && !$hide_panel) {
    $heading = get_field('heading');
    $intro   = get_field('intro');

    // Topics are the faq_topic taxonomy terms, and each topic's FAQs are real
    // "faq" CPT posts assigned to that term — not a manually-entered
    // repeater — so this block (and the FAQ Panel block's "select FAQs"
    // picker) both draw from the same real content instead of duplicating it.
    //
    // "Select FAQs" lets an editor restrict this instance to specific FAQs
    // (grouped by whichever topic each one belongs to) instead of always
    // showing every published FAQ — leave it empty to keep the default
    // "show everything" hub behaviour.
    $selected_faqs = get_field('selected_faqs') ?: [];
    $topics        = [];

    if ($selected_faqs) {
        $topics_by_term = [];

        foreach ($selected_faqs as $post) {
            $terms = get_the_terms($post->ID, 'faq_topic');
            $term  = (!empty($terms) && !is_wp_error($terms)) ? $terms[0] : null;
            $key   = $term ? $term->term_id : 0;

            if (!isset($topics_by_term[$key])) {
                $topics_by_term[$key] = [
                    'slug'  => $term ? $term->slug : 'general',
                    'name'  => $term ? $term->name : 'General',
                    'intro' => $term ? $term->description : '',
                    'faqs'  => [],
                ];
            }

            $topics_by_term[$key]['faqs'][] = [
                'question' => $post->post_title,
                'answer'   => apply_filters('the_content', $post->post_content),
            ];
        }

        $topics = array_values($topics_by_term);
    } else {
        $faq_topics = get_terms(['taxonomy' => 'faq_topic', 'hide_empty' => true]);

        if (!is_wp_error($faq_topics)) {
            foreach ($faq_topics as $term) {
                $faq_posts = get_posts([
                    'post_type'      => 'faq',
                    'posts_per_page' => -1,
                    'orderby'        => 'menu_order title',
                    'order'          => 'ASC',
                    'tax_query'      => [[
                        'taxonomy' => 'faq_topic',
                        'field'    => 'term_id',
                        'terms'    => $term->term_id,
                    ]],
                ]);

                if (!$faq_posts) {
                    continue;
                }

                $topics[] = [
                    'slug'  => $term->slug,
                    'name'  => $term->name,
                    'intro' => $term->description,
                    'faqs'  => array_map(fn($post) => [
                        'question' => $post->post_title,
                        'answer'   => apply_filters('the_content', $post->post_content),
                    ], $faq_posts),
                ];
            }
        }
    }

    if (!$topics) {
        echo '<p style="padding:2rem;opacity:0.4;font-style:italic;">No FAQ topics with published FAQs yet — add FAQs and assign them an FAQ Topic.</p>';
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
