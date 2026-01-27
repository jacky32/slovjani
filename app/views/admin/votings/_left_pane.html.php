<section id="leftpane">
  <div class=''>
    <ul class="listarticles">
      <li><?= t("menu.votings") ?></li>
      <?php
      if (count($votings) == 0) {
        echo "<li >
            <div >
              " . t("votings.index.no_votings_found") . "
            </div>
          </li>";
      } else {
        foreach ($votings as $voting) {
          $this->renderPartial('admin/votings/_voting', ['voting' => $voting, 'id' => isset($id) ? $id : null]);
        }
      }
      ?>
    </ul>
  </div>
</section>
