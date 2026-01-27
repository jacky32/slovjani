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
  <a href='/admin/votings/<?= $voting->id ?>/edit' class='button'><?= t("edit") ?></a>
  <form action='/admin/votings/<?= $voting->id ?>/destroy' method='POST'>
    <?php $this->renderCSRFToken('/admin/votings/destroy'); ?>
    <input type='hidden' name='id' value='<?= $voting->id ?>' />
    <?= ($voting->creator_id == $this->auth->getUserId() ? "<button class='button' type='submit'>" . t("delete") . "</button>" : "") ?>
  </form>
</section>
