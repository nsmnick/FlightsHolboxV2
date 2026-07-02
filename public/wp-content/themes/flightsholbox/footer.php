<div class="wave">
  <svg viewBox="0 0 2000 130" preserveAspectRatio="xMidYMid meet">
    <path
      d="
        M0 95
        C300 55, 600 50, 900 75
        C1200 110, 1550 140, 2000 90
        L2000 260
        L0 260
        Z"
      class="wave-fill" />
    <path
      d="
        M0 95
        C300 55, 600 50, 900 75
        C1200 110, 1550 140, 2000 90"
      class="wave-stroke" />
  </svg>
</div>

<footer>

  <section class="footer-top">
    <div class="container">
      <div class="two-col-container">

        <div class="two-col-container__col1">
          <div class="site-footer-logo"></div>
        </div>

        <div class="two-col-container__col2">
          <div class="footer-menu-container">
            <div class="footer-menu-container__col">
              <h3>Information</h3>
              <ul>
                <?php
                wp_nav_menu([
                  'theme_location' => 'footer-menu',
                  'container' => '',
                  'items_wrap' => '%3$s'
                ]);
                ?>
              </ul>
            </div>
          </div>
        </div>

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
