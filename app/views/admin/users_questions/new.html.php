<?= $this->renderPartial("admin/votings/_left_pane", ['votings' => $votings, 'id' => $voting->id, 'errors' => isset($errors) ? $errors : []]) ?>

<section id="rightpane">
  <h1><?= t("users_questions.new.title") ?></h1>
  <form action='/admin/votings/<?= $voting->id ?>/users_questions' method='POST'>
    <?php $this->renderCSRFToken('/admin/votings/' . $voting->id . '/users_questions'); ?>
    <?php foreach ($questions as $index => $question): ?>
      <div class="show-section">
        <h2 class="show-section__title"><?= htmlspecialchars($question->name ?? '') ?></h2>
        <?php if (!empty($parsed_question_descriptions[$question->id] ?? '')): ?>
          <div class="show-section__description post-parsed-content"><?= $parsed_question_descriptions[$question->id] ?></div>
        <?php endif; ?>
        <?php foreach (["YES", "NO", "ABSTAIN"] as $option): ?>
          <div>
            <label for='option_<?= $option ?>_question_<?= $question->id ?>' class="voting-radio-label">
              <input type='radio' id='option_<?= $option ?>_question_<?= $question->id ?>' name='users_question[<?= $index ?>][chosen_option]' value='<?= $option ?>' required />
              <span><?= t("enums.question_options." . $option) ?></span>
            </label>
          </div>
        <?php endforeach; ?>
        <input type='hidden' name='users_question[<?= $index ?>][question_id]' value='<?= $question->id ?>' />
      </div>
    <?php endforeach; ?>
    <button class='button' type='submit'><?= $this->renderIcon('check') ?> <?= t("users_questions.new.submit") ?></button>
  </form>
</section>
