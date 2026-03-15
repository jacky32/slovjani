(() => {
  const config = globalThis.phpAppRecaptcha;
  if (!config || !config.v3SiteKey) {
    return;
  }

  const tokenInput = document.getElementById('recaptcha-v3-token');
  if (!tokenInput) {
    return;
  }

  const loadRecaptchaApi = () => {
    return new Promise((resolve, reject) => {
      if (globalThis.grecaptcha) {
        resolve();
        return;
      }

      const script = document.createElement('script');
      script.src = `https://www.google.com/recaptcha/api.js?render=${encodeURIComponent(config.v3SiteKey)}`;
      script.async = true;
      script.defer = true;
      script.onload = () => resolve();
      script.onerror = () => reject(new Error('Unable to load reCAPTCHA API'));
      document.head.appendChild(script);
    });
  };

  const executeV3 = () => {
    if (!globalThis.grecaptcha) {
      return;
    }

    globalThis.grecaptcha.ready(() => {
      globalThis.grecaptcha
        .execute(config.v3SiteKey, { action: config.action ?? 'login' })
        .then((token) => {
          tokenInput.value = token;
        })
        .catch(() => {
          tokenInput.value = '';
        });
    });
  };

  const renderV2Widget = () => {
    if (
      config.needsV2 !== true ||
      !config.v2SiteKey ||
      !globalThis.grecaptcha
    ) {
      return;
    }

    const widget = document.getElementById('recaptcha-v2-widget');
    if (!widget || widget.dataset.rendered === 'true') {
      return;
    }

    globalThis.grecaptcha.ready(() => {
      globalThis.grecaptcha.render(widget, {
        sitekey: config.v2SiteKey,
      });
      widget.dataset.rendered = 'true';
    });
  };

  loadRecaptchaApi()
    .then(() => {
      executeV3();
      renderV2Widget();

      if (config.needsV2 === true) {
        // If v2 is required, refresh v3 token regularly while user solves the challenge.
        globalThis.setInterval(executeV3, 90 * 1000);
      }
    })
    .catch(() => {
      tokenInput.value = '';
    });
})();
