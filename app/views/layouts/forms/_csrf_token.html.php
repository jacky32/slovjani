<input type="hidden" name="token" value="<?php
                                          echo hash_hmac('sha256', $formAction, $_SESSION['token']);
                                          ?>" />
