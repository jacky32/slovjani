<section class='hero'>
  <div class='hero-content'>
    <form action="/login" method="POST">
      <?php $this->renderErrors(); ?>
      <fieldset class="fieldset bg-base-200 border-base-300 rounded-box w-xs border p-4">
        <legend class="fieldset-legend">Přihlášení</legend>

        <label class='floating-label my-1'>
          <span>Email</span>
          <input
            required
            type='email'
            name='email'
            placeholder='Email'
            class='input input-md' />
        </label>

        <label class='floating-label my-1'>
          <span>Heslo</span>
          <input
            required
            type='password'
            name='password'
            placeholder='Heslo'
            class='input input-md' />
        </label>

        <button class="btn btn-primary mt-4">Přihlásit</button>

        <a a href="/registration" class="btn btn-neutral mt-2">K registraci</a>
      </fieldset>
    </form>
  </div>
</section>
