<div class="navbar bg-base-100 shadow-sm">
  <div class="navbar-start">
    <div class="dropdown">
      <div tabindex="0" role="button" class="btn btn-ghost lg:hidden">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" />
        </svg>
      </div>
      <ul
        tabindex="0"
        class="menu menu-sm dropdown-content bg-base-100 rounded-box z-1 mt-3 w-52 p-2 shadow">
        <li><a href="/">Medžuslovjanski jazyk</a></li>
        <li><a href="/posts">Příspěvky</a></li>
      </ul>
    </div>
    <a class="btn btn-ghost text-xl" href="/">název</a>
  </div>
  <div class="navbar-center hidden lg:flex">
    <ul class="menu menu-horizontal px-1">
      <li><a href="/">Medžuslovjanski jazyk</a></li>
      <li><a href="/posts">Příspěvky</a></li>
      <?php
      if ($this->auth->isLoggedIn()) {
        echo '
          <li><a href="/admin/ekonomika">Ekonomika</a></li>
          <li><a href="/admin/cinnost">Činnost</a></li>
          <li><a href="/admin/hlasovani">Hlasování</a></li>
        ';
      }
      ?>
    </ul>
  </div>
  <div class="navbar-end">
    <?php
    if ($this->auth->isLoggedIn()) {
      echo '<form action="/logout" method="POST">
              <button class="btn" type="submit">Odhlásit</button>
            </form>
            ';
    } else {
      echo '<a href="/login" class="btn">Přihlásit</a>';
    }
    ?>
  </div>
</div>
