// Fade timing here must match the .faq-item / .support-panel__topic CSS
// transition duration in _support-panel.scss — the setTimeout that hides an
// element is what lets the opacity transition actually finish first.
const HIDE_DELAY = 250;

function setFaqOpen(item, open) {
  const details = item.tagName === 'DETAILS' ? item : item.querySelector('details');
  if (details) details.open = open;
}

// Applies each topic's data-max-questions cap by hiding items past that
// index — tracked separately from search's own show/hide so clearing a
// search restores the collapsed state instead of leaving everything
// permanently expanded just because a search pass happened to touch it.
function collapseTopic(topic) {
  const max = parseInt(topic.dataset.maxQuestions, 10);
  if (!max) return;

  const items = Array.from(topic.querySelectorAll('.faq-item'));
  items.forEach((item, i) => {
    item.style.display = i < max ? '' : 'none';
  });
}

export default function initSupportPanel() {
  document.querySelectorAll('.support-panel').forEach((panel) => {
    const search = panel.querySelector('.support-panel__search');
    const noResults = panel.querySelector('.support-panel__no-results');
    const navItems = Array.from(panel.querySelectorAll('.support-panel__nav-item'));
    const topics = Array.from(panel.querySelectorAll('.support-panel__topic'));

    if (!search || !navItems.length || !topics.length) return;

    // Topics an editor has expanded via "Show More" stay expanded even
    // after a search is cleared, rather than snapping back to collapsed.
    const expandedTopics = new Set();

    topics.forEach((topic) => {
      collapseTopic(topic);

      const showMoreBtn = topic.querySelector('.support-panel__show-more');

      if (showMoreBtn) {
        showMoreBtn.addEventListener('click', () => {
          topic.querySelectorAll('.faq-item').forEach((item) => {
            item.style.display = '';
          });
          expandedTopics.add(topic.dataset.topic);
          showMoreBtn.hidden = true;
        });
      }
    });

    let lastActiveTopic = navItems[0]?.dataset.topic;
    let searching = false;

    function showTopic(slug) {
      navItems.forEach((btn) => btn.classList.toggle('is-active', btn.dataset.topic === slug));
      topics.forEach((topic) => {
        topic.hidden = topic.dataset.topic !== slug;
      });
    }

    function activateTopic(slug) {
      lastActiveTopic = slug;
      showTopic(slug);
    }

    navItems.forEach((btn) => {
      btn.addEventListener('click', () => {
        // Selecting a topic while a search is active clears it — clicking a
        // tab is a clear signal the visitor wants to browse that topic
        // specifically, not keep filtering across all of them.
        if (searching) {
          search.value = '';
          searching = false;
        }
        activateTopic(btn.dataset.topic);
      });
    });

    function filter() {
      const query = search.value.toLowerCase().trim();

      if (!query) {
        if (searching) {
          searching = false;
          topics.forEach((topic) => {
            topic.querySelectorAll('.faq-item').forEach((item) => {
              item.style.opacity = '';
              setFaqOpen(item, false);
            });
            // Re-collapse back to the max-questions cap, unless this topic
            // was explicitly expanded via "Show More" first.
            if (expandedTopics.has(topic.dataset.topic)) {
              topic.querySelectorAll('.faq-item').forEach((item) => {
                item.style.display = '';
              });
            } else {
              collapseTopic(topic);
            }
          });
          showTopic(lastActiveTopic);
        }
        return;
      }

      searching = true;
      navItems.forEach((btn) => btn.classList.remove('is-active'));

      let totalVisible = 0;

      topics.forEach((topic) => {
        const items = topic.querySelectorAll('.faq-item');
        let topicVisible = 0;

        items.forEach((item) => {
          const match = (item.dataset.search || '').includes(query);

          if (match) {
            clearTimeout(item._hideTimer);
            item.style.display = '';
            requestAnimationFrame(() => {
              item.style.opacity = '1';
            });
            setFaqOpen(item, true);
            topicVisible++;
          } else {
            item.style.opacity = '0';
            clearTimeout(item._hideTimer);
            item._hideTimer = setTimeout(() => {
              item.style.display = 'none';
            }, HIDE_DELAY);
          }
        });

        topic.hidden = topicVisible === 0;
        totalVisible += topicVisible;
      });

      if (noResults) noResults.hidden = totalVisible > 0;
    }

    search.addEventListener('input', filter);
  });
}
