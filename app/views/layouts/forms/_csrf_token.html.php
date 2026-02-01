<input type="hidden" name="token" value="<?= hash_hmac('sha256', $formAction, $_SESSION['token']) ?>" />
