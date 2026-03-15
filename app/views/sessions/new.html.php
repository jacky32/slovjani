<section class='hero'>
  <div class='hero-content'>
    <form action="/login" method="POST">
      <?= $this->renderErrors() ?>
      <fieldset class="p-4 border fieldset bg-base-200 border-base-300 rounded-box w-xs">
        <legend class="fieldset-legend"><?= t("sessions.new.title") ?></legend>

        <label class='my-1 floating-label'>
          <span><?= User::humanAttributeName("email") ?></span>
          <input
            required
            type='email'
            name='email'
            placeholder='<?= User::humanAttributeName("email") ?>'
            class='input input-md' />
        </label>

        <label class='my-1 floating-label'>
          <span><?= User::humanAttributeName("password") ?></span>
          <input
            required
            type='password'
            name='password'
            placeholder='<?= User::humanAttributeName("password") ?>'
            class='input input-md' />
        </label>

        <?php if (($recaptchaEnabled ?? false) && ($recaptchaV3SiteKey ?? '') !== ''): ?>
          <input type="hidden" name="recaptcha_v3_token" id="recaptcha-v3-token" value="" />
        <?php endif; ?>

        <?php if (($recaptchaEnabled ?? false) && ($recaptchaV2SiteKey ?? '') !== '' && ($requireRecaptchaV2 ?? false)): ?>
          <div class="mt-4">
            <p><?= t("sessions.recaptcha.v2_prompt") ?></p>
            <div id="recaptcha-v2-widget"></div>
          </div>
        <?php endif; ?>

        <button class="mt-4 btn btn-primary"><?= $this->renderIcon('arrow-right-on-rectangle') ?> <?= t("sessions.new.submit") ?></button>
      </fieldset>
    </form>

    <?php if (($canBootstrapDefaultAdmin ?? false) === true): ?>
      <form action="/login/bootstrap_default_admin" method="POST" class="mt-4">
        <?= $this->renderCSRFToken('/login/bootstrap_default_admin') ?>
        <fieldset class="p-4 border fieldset bg-base-200 border-base-300 rounded-box w-xs">
          <legend class="fieldset-legend"><?= t("sessions.bootstrap_default_admin.title") ?></legend>
          <p><?= t("sessions.bootstrap_default_admin.description") ?></p>
          <button class="mt-3 btn btn-secondary" type="submit">
            <?= $this->renderIcon('user-plus') ?> <?= t("sessions.bootstrap_default_admin.submit") ?>
          </button>
        </fieldset>
      </form>
    <?php endif; ?>
  </div>
</section>

<?php if (($recaptchaEnabled ?? false) && ($recaptchaV3SiteKey ?? '') !== ''): ?>
  <script>
    globalThis.phpAppRecaptcha = {
      v3SiteKey: "<?= htmlspecialchars($recaptchaV3SiteKey) ?>",
      v2SiteKey: "<?= htmlspecialchars($recaptchaV2SiteKey ?? '') ?>",
      action: 'login',
      needsV2: <?= ($requireRecaptchaV2 ?? false) ? 'true' : 'false' ?>
    };
  </script>
  <script src="<?= asset_path('/assets/javascripts/custom/sessions_login_recaptcha.js') ?>"></script>
<?php endif; ?>
