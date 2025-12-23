<nav id="header">
  <div class="hidden navbar-center lg:flex">
    <ul class="px-1 menu menu-horizontal">
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
    </ul>
  </div>
  <div class="navbar-end">
    <?php
    if ($this->auth->isLoggedIn()) {
      echo '<form action="/logout" method="POST">
              <button class="btn" type="submit">' . t("menu.logout") . '</button>
            </form>
            ';
    } else {
      echo '<a href="/login" class="btn">' . t("menu.login") . '</a>';
    }
    ?>
  </div>
</nav>
