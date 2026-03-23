<?= $this->renderPartial("events/_left_pane", ['events' => $events, 'errors' => isset($errors) ? $errors : []]) ?>
<section id="rightpane">
  <?= t("events.index.placeholder") ?>
  <br><br>
  <iframe title="<?= t('events.index.calendar_title') ?>" src="https://calendar.google.com/calendar/embed?height=600&wkst=1&ctz=Europe%2FPrague&showPrint=0&title=Slovjani&src=ZmU2NjRlMDZhMzZjZmFlZGFhMjA2ZDk1MTc0OTczYmY4OTY0MjFkMTg0ZmFmYTkwMDliMTQ4ZjZlZTM5NzA4OEBncm91cC5jYWxlbmRhci5nb29nbGUuY29t&color=%234285f4" style="border-width:0" width="800" height="600" frameborder="0" scrolling="no" loading="lazy"></iframe>
</section>
