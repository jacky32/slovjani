<nav id="header">
  <span id="header-logo-lat"><?= t("menu.header.logo.lat") ?><br></span>
  <span id="header-logo-cyr"><?= t("menu.header.logo.cyr") ?><br></span>
  <ul id="header-text" class="menu">
    <li><a href="/"><?= t('menu.root') ?></a></li>
    <li><a href="/posts"><?= t('menu.posts') ?></a></li>
    <li><a href="/events"><?= t('menu.events') ?></a></li>
    <?php if ($this->auth->isLoggedIn()): ?>
      <li><a href="/admin/economics"><?= t("menu.economics") ?></a></li>
      <li><a href="/admin/events"><?= t("menu.events") ?></a></li>
      <li><a href="/admin/votings"><?= t("menu.votings") ?></a></li>
    <?php endif; ?>
    <li>
      <?php if ($this->auth->isLoggedIn()): ?>
        <form action="/logout" method="POST">
          <button type="submit" class="button"><?= t("menu.logout") ?></button>
        </form>
      <?php else: ?>
        <a href="/login" class="button"><?= t("menu.login") ?></a>
      <?php endif; ?>
    </li>
  </ul>
</nav>
