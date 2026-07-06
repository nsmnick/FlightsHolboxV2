<?php
include __DIR__ . '/../_block-generics.php';
include __DIR__ . '/../_block-preview.php';

if (!$is_preview && !$hide_panel && !$preview_popup_image) {
    $video_url = get_field('video_url');

    $embed_url = '';
    $thumbnail_url = '';
    if ($video_url) {
        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $video_url, $m)) {
            $embed_url = 'https://www.youtube-nocookie.com/embed/' . $m[1] . '?rel=0&autoplay=1';
            $thumbnail_url = 'https://i.ytimg.com/vi/' . $m[1] . '/hqdefault.jpg';
        } elseif (preg_match('/vimeo\.com\/(?:video\/)?(\d+)/', $video_url, $m)) {
            $embed_url = 'https://player.vimeo.com/video/' . $m[1] . '?title=0&byline=0&portrait=0&autoplay=1';
        }
    }

    if (!$embed_url) {
        return;
    }
?>

<section class="video-panel animate fade-in <?php echo $generic_block_settings_classes; ?>">
    <div class="container <?php echo $generic_container_class; ?>">
        <div class="video-panel__embed">
            <button
                type="button"
                class="video-panel__facade"
                data-embed-url="<?php echo esc_attr($embed_url); ?>"
                <?php if ($thumbnail_url) : ?>style="background-image: url('<?php echo esc_url($thumbnail_url); ?>')"<?php endif; ?>
                aria-label="Play video"
            >
                <span class="video-panel__play-icon" aria-hidden="true"></span>
            </button>
        </div>
    </div>
</section>

<?php
}
?>
