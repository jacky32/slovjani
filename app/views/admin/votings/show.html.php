<?= $this->renderPartial("admin/votings/_left_pane", ['votings' => $votings, "id" => $voting->id, 'errors' => isset($errors) ? $errors : []]) ?>
<section id="rightpane">
  <h1>
    <?= $voting->name ?>
  </h1>
  <small>
    <?= t("creator") ?>: <?= $voting->creator->username ?><br>
    <?php
    if (isset($voting->created_at)) {
      $date = new DateTime($voting->created_at);
      $formatted = $date->format('d.m.Y H:i');
      echo t("created_at") . ": " . $formatted;
    }
    ?><br>
    <?php
    if (isset($voting->updated_at)) {
      $date = new DateTime($voting->updated_at);
      $formatted = $date->format('d.m.Y H:i');
      echo t("updated_at") . ": " . $formatted;
    }
    ?>
  </small>
  <p><?= htmlspecialchars($voting->description) ?></p>

  <hr>
  <h2><?= t("votings.show.questions_title") ?></h2>
  <ol>
    <?php foreach ($voting->questions->get() as $question) {
      $this->renderPartial("admin/votings/_question", ['voting' => $voting, 'question' => $question]);
    } ?>
  </ol>
  <a href='/admin/votings/<?= $voting->id ?>/questions/new' class='button'><?= t("questions.new.title") ?></a>
  <hr>

  <?php if ($voting->status == "DRAFT") : ?>
    <form action='/admin/votings/<?= $voting->id ?>' method='POST'>
      <?php $this->renderCSRFToken('/admin/votings/' . $voting->id); ?>
      <input type='hidden' name='voting[status]' value='IN_PROGRESS' />
      <button class='button' type='submit'><?= t("votings.show.start_voting") ?></button>
    </form>
  <?php endif; ?>

  <?php if ($voting->status == "IN_PROGRESS") : ?>
    <form action='/admin/votings/<?= $voting->id ?>' method='POST'>
      <?php $this->renderCSRFToken('/admin/votings/' . $voting->id); ?>
      <input type='hidden' name='voting[status]' value='COMPLETED' />
      <button class='button' type='submit'><?= t("votings.show.end_voting") ?></button>
    </form>
    <form action='/admin/votings/<?= $voting->id ?>' method='POST'>
      <?php $this->renderCSRFToken('/admin/votings/' . $voting->id); ?>
      <input type='hidden' name='voting[status]' value='CANCELLED' />
      <button class='button' type='submit'><?= t("votings.show.cancel_voting") ?></button>
    </form>
  <?php endif; ?>

  <?php if ($voting->creator_id == $this->auth->getUserId()) : ?>
    <a href='/admin/votings/<?= $voting->id ?>/edit' class='button'><?= t("edit") ?></a>
    <form action='/admin/votings/<?= $voting->id ?>/destroy' method='POST'>
      <?php $this->renderCSRFToken('/admin/votings/destroy'); ?>
      <input type='hidden' name='id' value='<?= $voting->id ?>' />
      <?= ($voting->creator_id == $this->auth->getUserId() ? "<button class='button' type='submit'>" . t("delete") . "</button>" : "") ?>
    </form>
  <?php endif; ?>
</section>
