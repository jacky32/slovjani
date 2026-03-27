<?= $this->renderPartial("admin/events/_left_pane", ['events' => $events, 'errors' => isset($errors) ? $errors : []]) ?>
<section id="rightpane">
  <br><br>
  <a href='/admin/events/new' class='button'><?= $this->renderIcon('plus-circle') ?> <?= t("events.new.title") ?></a>
  <br><br>
  <iframe title="<?= t('events.index.admin_calendar_title') ?>" src="https://calendar.google.com/calendar/embed?height=600&wkst=1&ctz=Europe%2FPrague&showPrint=0&title=Slovjani%20neve%C5%99ejn%C3%BD%20kalend%C3%A1%C5%99&src=ZmU2NjRlMDZhMzZjZmFlZGFhMjA2ZDk1MTc0OTczYmY4OTY0MjFkMTg0ZmFmYTkwMDliMTQ4ZjZlZTM5NzA4OEBncm91cC5jYWxlbmRhci5nb29nbGUuY29t&src=YTY2OWJiYzk4ZDU2MjllYjBkNmEyNzkyZDVkZjExNjM0MjI0ZTMxNWZmMmIwOGNlMzBiMzFiNTcyNmE5OTFiZkBncm91cC5jYWxlbmRhci5nb29nbGUuY29t&color=%234285f4&color=%238e24aa" style="border-width:0" width="800" height="600" frameborder="0" scrolling="no" loading="lazy"></iframe>
</section>
