<?php
$footer_phone       = get_field('footer_phone', 'option');
$footer_whatsapp    = get_field('footer_whatsapp', 'option');
$footer_email       = get_field('footer_email', 'option');
$footer_address     = get_field('footer_address', 'option');
$footer_facebook    = get_field('footer_social_facebook', 'option');
$footer_instagram   = get_field('footer_social_instagram', 'option');
$footer_tripadvisor = get_field('footer_social_tripadvisor', 'option');
?>

</main>

<footer>

  <section class="footer-top">
    <div class="container">
      <div class="two-col-container">

        <div class="two-col-container__col1">
          <div class="site-footer-logo"></div>

          <div class="footer-contact-details">
            <?php if ($footer_phone) : ?>
              <div class="details-container">
                <span class="details">Call us</span>
                <span class="contact-icon contact-icon--phone" aria-hidden="true"></span>
                <a href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $footer_phone)); ?>"><?php echo esc_html($footer_phone); ?></a>
              </div>
            <?php endif; ?>

            <?php if ($footer_whatsapp) : ?>
              <div class="details-container">
                <span class="details">WhatsApp</span>
                <span class="contact-icon contact-icon--whatsapp" aria-hidden="true"></span>
                <a href="https://wa.me/<?php echo esc_attr(preg_replace('/[^0-9]/', '', $footer_whatsapp)); ?>" target="_blank" rel="noopener"><?php echo esc_html($footer_whatsapp); ?></a>
              </div>
            <?php endif; ?>

            <?php if ($footer_email) : ?>
              <div class="details-container">
                <span class="details">E-mail</span>
                <span class="contact-icon contact-icon--email" aria-hidden="true"></span>
                <a href="mailto:<?php echo esc_attr($footer_email); ?>"><?php echo esc_html($footer_email); ?></a>
              </div>
            <?php endif; ?>
          </div>

          <?php if ($footer_address) : ?>
            <p class="footer-address"><?php echo nl2br(esc_html($footer_address)); ?></p>
          <?php endif; ?>

          <?php if ($footer_facebook || $footer_instagram || $footer_tripadvisor) : ?>
            <div class="footer-socials">
              <p class="footer-socials__heading">Follow us on social media</p>
              <div class="social-channels social-channels--row">
                <?php if ($footer_facebook) : ?>
                  <a href="<?php echo esc_url($footer_facebook); ?>" target="_blank" rel="noopener" aria-label="Facebook">
                    <span class="social-icon social-icon--facebook"></span>
                  </a>
                <?php endif; ?>
                <?php if ($footer_instagram) : ?>
                  <a href="<?php echo esc_url($footer_instagram); ?>" target="_blank" rel="noopener" aria-label="Instagram">
                    <span class="social-icon social-icon--instagram"></span>
                  </a>
                <?php endif; ?>
                <?php if ($footer_tripadvisor) : ?>
                  <a href="<?php echo esc_url($footer_tripadvisor); ?>" target="_blank" rel="noopener" aria-label="TripAdvisor">
                    <span class="social-icon social-icon--tripadvisor"></span>
                  </a>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>
        </div>

        <?php
        $footer_menu_locations = [
          'footer-menu' => 'Information',
          'footer-menu-pickups' => 'Popular Pick Ups',
          'footer-menu-destinations' => 'Popular Destinations',
        ];
        $active_footer_menus = array_filter(array_keys($footer_menu_locations), 'has_nav_menu');
        ?>

        <?php if ($active_footer_menus) : ?>
          <div class="two-col-container__col2">
            <div class="footer-menu-container footer-menu-container--<?php echo count($active_footer_menus) === 3 ? 'three' : 'two'; ?>">
              <?php foreach ($active_footer_menus as $location) : ?>
                <div class="footer-menu-container__col">
                  <h3><?php echo esc_html($footer_menu_locations[$location]); ?></h3>
                  <ul>
                    <?php
                    wp_nav_menu([
                      'theme_location' => $location,
                      'container' => '',
                      'items_wrap' => '%3$s',
                      'fallback_cb' => false,
                    ]);
                    ?>
                  </ul>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

      </div>
    </div>
  </section>

  <section class="footer-bottom">
    <div class="container">
      <div class="two-col-container">
        <div class="two-col-container__col">
          <div class="copyright-message">&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?></div>
        </div>
        <div class="two-col-container__col">
          <div class="privacy-message">
            <a href="/terms-and-conditions/">Terms &amp; Conditions</a>
            <a href="/privacy-policy/">Privacy Policy</a>
          </div>
        </div>
      </div>
    </div>
  </section>

</footer>

<?php wp_footer(); ?>

</body>

</html>
