<section id="leftpane">
  <div class=''>
    <ul class="listarticles">
      <li><?= t("menu.votings") ?></li>
      <li>
        <?= $this->renderPartial('layouts/pagination/_previous_button') ?>
      </li>
      <?php if (count($votings) == 0) : ?>
        <li>
          <div>
            <?= t("votings.index.no_votings_found") ?>
          </div>
        </li>
      <?php else : ?>
        <?php foreach ($votings as $index => $voting) : ?>
          <?= $this->renderPartial('admin/votings/_voting', ['voting' => $voting, 'id' => isset($id) ? $id : null, 'index' => $index]) ?>
        <?php endforeach; ?>
      <?php endif; ?>
      <?= $this->renderPartial('layouts/pagination/_bottom_pagination') ?>
    </ul>
  </div>
</section>
