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

        <button class="mt-4 btn btn-primary"><?= t("sessions.new.submit") ?></button>
        <a href="/registration" class="mt-2 btn btn-neutral"><?= t("sessions.new.to_register") ?></a>
      </fieldset>
    </form>
  </div>
</section>
