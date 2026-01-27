<nav id="header">
  <span id="header-logo-lat"><?= t("menu.header.logo.lat") ?><br></span>
  <span id="header-logo-cyr"><?= t("menu.header.logo.cyr") ?><br></span>
  <ul id="header-text" class="menu">
    <li><a href="/"><?= t('menu.root') ?></a></li>
    <li><a href="/posts"><?= t('menu.posts') ?></a></li>
    <?php
    if ($this->auth->isLoggedIn()) {
      echo '
        <li><a href="/admin/economics">' . t("menu.economics") . '</a></li>
        <li><a href="/admin/pursuits">' . t("menu.pursuits") . '</a></li>
        <li><a href="/admin/votings">' . t("menu.votings") . '</a></li>
      ';
    }
    ?>
    <li>
      <?php
      if ($this->auth->isLoggedIn()) {
        echo '<form action="/logout" method="POST">
                <button type="submit" class="button">' . t("menu.logout") . '</button>
              </form>
              ';
      } else {
        echo '<a href="/login" class="button">' . t("menu.login") . '</a>';
      }
      ?>
    </li>
  </ul>
</nav>
