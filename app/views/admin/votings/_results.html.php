<?php if ($voting->status !== "DRAFT") : ?>
  <h2><?= t("votings.show.results") ?></h2>
  <ol>
    <?php foreach ($voting->questions->get() as $question) : ?>
      <li>
        <strong><?= htmlspecialchars($question->name) ?></strong>
        <ul>
          <?php
          $options = ['YES' => 0, 'NO' => 0, 'ABSTAIN' => 0];
          $users_questions = $question->users_questions->get();
          foreach ($users_questions as $uq) {
            if (array_key_exists($uq->chosen_option, $options)) {
              $options[$uq->chosen_option]++;
            }
          }
          foreach ($options as $option => $count) : ?>
            <li><?= t("enums.question_options.$option") ?>: <?= $count ?></li>
          <?php endforeach; ?>
        </ul>
      </li>
    <?php endforeach; ?>
  </ol>
<?php endif; ?>
