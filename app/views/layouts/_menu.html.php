<nav id="header">
  <span id="header-logo-lat"><?= t("menu.header.logo.lat") ?><br></span>
  <span id="header-logo-cyr"><?= t("menu.header.logo.cyr") ?><br></span>
  <ul id="header-text" class="menu">
    <li><a href="/"><?= t('menu.root') ?></a></li>
    <li><a href="/posts"><?= t('menu.posts') ?></a></li>
    <?php
    if ($this->auth->isLoggedIn()) {
      echo '
        <li><a href="/admin/ekonomika">' . t("menu.economics") . '</a></li>
        <li><a href="/admin/cinnost">' . t("menu.activity") . '</a></li>
        <li><a href="/admin/hlasovani">' . t("menu.votings") . '</a></li>
      ';
    }
    ?>
    <li>
      <?php
      if ($this->auth->isLoggedIn()) {
        echo '<form action="/logout" method="POST">
                <button type="submit">' . t("menu.logout") . '</button>
              </form>
              ';
      } else {
        echo '<a href="/login">' . t("menu.login") . '</a>';
      }
      ?>
    </li>
  </ul>
</nav>
