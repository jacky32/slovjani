<nav id="header">
  <span id="header-logo-lat"><?= t("menu.header.logo.lat") ?><br></span>
  <span id="header-logo-cyr"><?= t("menu.header.logo.cyr") ?><br></span>
  <div id="header-text">
    <ul class="menu">
      <li>
        <a class="<?= $this->controller == "HomeController" ? "active" : "" ?>" href="/"><?= t('menu.root') ?></a>
      </li>
      <li>
        <a class="<?= $this->controller == "PostsController" ? "active" : "" ?>" href="/posts"><?= t('menu.posts') ?></a>
      </li>
      <li>
        <a class="<?= $this->controller == "EventsController" ? "active" : "" ?>" href="/events"><?= t('menu.events') ?></a>
      </li>
      <?php if ($this->auth->isLoggedIn()): ?>
    </ul>
    <ul class="menu">
      <li><?= t("menu.eoffice") ?></li>
      <?php foreach (
          [
            ["AdminPostsController", "/admin/posts", t("menu.posts")],
            ["AdminUsersController", "/admin/users", t("menu.users")],
            ["AdminEventsController", "/admin/events", t("menu.events")],
            ["AdminVotingsController", "/admin/votings", t("menu.votings")]
          ] as $link_data
        ): ?>
        <li>
          <a
            class="<?= $this->controller == $link_data[0] ? "active" : "" ?>"
            href="<?= $link_data[1] ?>">
            <?= $link_data[2] ?>
          </a>
        </li>
      <?php endforeach; ?>
    <?php endif; ?>
    <li>
      <?php if ($this->auth->isLoggedIn()): ?>
        <a>
          <form action="/logout" method="POST">
            <button type="submit" class="button"><?= t("menu.logout") ?></button>
          </form>
        </a>
      <?php else: ?>
        <a href="/login" class="button"><?= t("menu.login") ?></a>
      <?php endif; ?>
    </li>
    </ul>
  </div>
</nav>
