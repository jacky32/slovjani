<?php
if (isset($errors) && count($errors) > 0) {
  echo "<div class='error'>";
  foreach ($errors as $error) {
    echo $error . "<br>";
  }
  echo "</div>";
}
