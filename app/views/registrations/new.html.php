<section class='hero'>
  <div class='hero-content'>
    <form action="/registration" method="POST">
      <?= $this->renderErrors($errors) ?>
      <fieldset class="p-4 border fieldset bg-base-200 border-base-300 rounded-box w-xs">
        <legend class="fieldset-legend"><?= t("registrations.new.title") ?></legend>

        <label class='my-1 floating-label'>
          <span><?= User::humanAttributeName("username") ?></span>
          <input
            required
            type='text'
            name='username'
            placeholder='<?= User::humanAttributeName("username") ?>'
            class='input input-md' />
        </label>

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

        <button class="mt-4 btn btn-primary"><?= t("registrations.new.submit") ?></button>

        <a href="/login" class="mt-2 btn btn-neutral"><?= t("registrations.new.back_to_login") ?></a>
      </fieldset>
    </form>
  </div>
</section>
