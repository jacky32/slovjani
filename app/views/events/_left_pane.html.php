<section id="leftpane">
  <div class=''>
    <ul class="listarticles">
      <li><?= t("menu.events") ?></li>
      <li>
        <?= $this->renderPartial('layouts/pagination/_previous_button') ?>
      </li>
      <?php if (count($events) == 0) : ?>
        <li>
          <div>
            <?= t("events.index.no_events_found") ?>
          </div>
        </li>
      <?php else : ?>
        <?php foreach ($events as $index => $event) : ?>
          <?= $this->renderPartial('events/_event', ['event' => $event, 'id' => isset($id) ? $id : null, 'index' => $index]) ?>
        <?php endforeach; ?>
      <?php endif; ?>
      <?= $this->renderPartial('layouts/pagination/_bottom_pagination') ?>
    </ul>
  </div>
</section>
