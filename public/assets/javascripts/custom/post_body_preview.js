(() => {
  /**
   * Reusable live HTML preview for any textarea/input.
   *
   * Required data attributes on source element:
   * - data-live-preview="true"
   * - data-preview-target="targetElementId"
   * - data-preview-endpoint="/admin/previews/preview_markup"
   *
   * Optional:
   * - data-preview-param="body"            (default: body)
   * - data-preview-delay="2000"            (default: 2000 ms)
   * - data-preview-response-key="html"     (default: html)
   */
  const SELECTOR = '[data-live-preview="true"]';

  const parseDelay = (value, fallback) => {
    const parsed = Number.parseInt(value ?? '', 10);
    return Number.isFinite(parsed) && parsed >= 0 ? parsed : fallback;
  };

  const mountLivePreview = (source) => {
    const {
      previewTarget,
      previewEndpoint,
      previewParam = 'body',
      previewResponseKey = 'html',
      previewParser,
      previewResourceType,
      previewResourceId,
      previewAdminContext,
      previewErrorText = '',
      previewLoadingText = '',
    } = source.dataset;

    const previewDelay = parseDelay(source.dataset.previewDelay, 800);
    const target = document.getElementById(previewTarget || '');

    if (!target || !previewEndpoint) {
      return;
    }

    target.setAttribute('aria-live', 'polite');
    target.setAttribute('aria-atomic', 'true');

    if (previewLoadingText) {
      target.textContent = previewLoadingText;
    }

    let debounceTimer = null;
    let activeController = null;

    const renderPreview = async () => {
      if (activeController) {
        activeController.abort();
      }

      activeController = new AbortController();
      target.setAttribute('aria-busy', 'true');
      const payloadData = { [previewParam]: source.value || '' };
      if (previewParser) {
        payloadData.parser = previewParser;
      }
      if (previewResourceType) {
        payloadData.resource_type = previewResourceType;
      }
      if (previewResourceId) {
        payloadData.resource_id = previewResourceId;
      }
      if (previewAdminContext) {
        payloadData.admin_context = previewAdminContext;
      }

      const payload = new URLSearchParams(payloadData);

      try {
        const response = await fetch(previewEndpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'X-Requested-With': 'XMLHttpRequest',
          },
          body: payload.toString(),
          signal: activeController.signal,
        });

        if (!response.ok) {
          throw new Error('Preview request failed');
        }

        const data = await response.json();
        target.innerHTML = data?.[previewResponseKey] ?? '';
        target.setAttribute('aria-busy', 'false');
      } catch (error) {
        if (error?.name === 'AbortError') {
          return;
        }

        target.innerHTML = previewErrorText
          ? '<p class="post-preview-error">' + previewErrorText + '</p>'
          : '';
        target.setAttribute('aria-busy', 'false');
      }
    };

    source.addEventListener('input', () => {
      if (debounceTimer) {
        window.clearTimeout(debounceTimer);
      }

      debounceTimer = window.setTimeout(renderPreview, previewDelay);
    });

    renderPreview();
  };

  const init = () => {
    document.querySelectorAll(SELECTOR).forEach(mountLivePreview);
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }
})();
