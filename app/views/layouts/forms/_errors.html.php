<div class="warning">
  <?php foreach ($errors as $error) : ?>
    <?= t("attributes." . toSnakeCase($error["class"]) . "." . $error["attribute"]) ?> <?= $error["message"] ?><br>
  <?php endforeach; ?>
</div>
