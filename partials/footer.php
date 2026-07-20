<?php $is_admin_page = str_contains($body_class ?? '', 'page-admin'); ?>
  </main>
  <?php if (!$is_admin_page): ?>
  <footer class="site-footer">
    <p class="spiked-label">MICASA® / ARCHIVE GROWS WITH EVERY DROP</p>
    <p class="spiked-label">MODE / FILM / MUSIK / DESIGN / HALTUNG</p>
  </footer>
  <script src="<?= e(asset_url('assets/js/site.js')) ?>" defer></script>
  <?php endif; ?>
  <?php if (!$is_admin_page): ?>
    <nav class="language-switch" aria-label="Language">
      <a class="<?= current_lang() === 'de' ? 'is-active' : '' ?>" href="<?= e(language_url('de')) ?>">DE</a>
      <a class="<?= current_lang() === 'en' ? 'is-active' : '' ?>" href="<?= e(language_url('en')) ?>">EN</a>
    </nav>
  <?php endif; ?>
</body>
</html>
