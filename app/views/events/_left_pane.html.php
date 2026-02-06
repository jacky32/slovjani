<section id="leftpane">
  <div class=''>
    <ul class="listarticles">
      <li><?= t("menu.events") ?></li>
      <?php
      if (count($events) == 0) {
        echo "<li >
            <div >
              " . t("events.index.no_events_found") . "
            </div>
          </li>";
      } else {
        foreach ($events as $event) {
          $this->renderPartial('events/_event', ['event' => $event, 'id' => isset($id) ? $id : null]);
        }
      }
      ?>
    </ul>
  </div>
</section>
