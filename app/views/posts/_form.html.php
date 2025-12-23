<form action="/posts" method="POST">
  <?php $this->renderErrors(); ?>
  <?php $this->renderCSRFToken('/posts'); ?>

  <fieldset class="fieldset bg-base-200 border-base-300 rounded-box w-xs border p-4">
    <legend class="fieldset-legend">Nový příspěvek</legend>

    <label class='floating-label my-1'>
      <span>Název</span>
      <input
        required
        type='text'
        name='name'
        placeholder='Název'
        class='input input-md' />
    </label>

    <label class='floating-label my-1'>
      <span>Obsah</span>
      <textarea
        required
        name='body'
        placeholder='Obsah'
        class='input input-md'></textarea>
    </label>

    <button class="btn btn-primary mt-4">Přidat příspěvek</button>
  </fieldset>
</form>
