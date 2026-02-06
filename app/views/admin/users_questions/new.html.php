<?= $this->renderPartial("admin/votings/_left_pane", ['votings' => $votings, 'errors' => isset($errors) ? $errors : []]) ?>

<form action='/admin/votings/<?= $voting->id ?>/users_questions' method='POST'>
  <?php $this->renderCSRFToken('/admin/votings/' . $voting->id . '/users_questions'); ?>
  <?php foreach ($questions as $index => $question) : ?>
    <fieldset>
      <legend><?= $question->name ?></legend>
      <p><?= htmlspecialchars($question->description ?? '') ?></p>
      <?php foreach (["YES", "NO", "ABSTAIN"] as $option) : ?>
        <div>
          <input type='radio' id='option_<?= $option ?>_question_<?= $question->id ?>' name='users_question[<?= $index ?>][chosen_option]' value='<?= $option ?>' required />
          <label for='option_<?= $option ?>_question_<?= $question->id ?>'><?= t("enums.question_options." . $option) ?></label>
        </div>
      <?php endforeach; ?>
      <input type='hidden' name='users_question[<?= $index ?>][question_id]' value='<?= $question->id ?>' />
    </fieldset>
  <?php endforeach; ?>
  <button class='button' type='submit'><?= t("users_questions.new.submit") ?></button>
</form>
<hr>
