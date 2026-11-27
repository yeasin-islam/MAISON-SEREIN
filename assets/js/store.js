(() => {
  const toastRegion = document.querySelector('[data-toast]');
  const showToast = (message, isError = false) => {
    if (!toastRegion || !message) return;
    const toast = document.createElement('div');
    toast.className = `toast${isError ? ' is-error' : ''}`;
    toast.textContent = message;
    toastRegion.append(toast);
    window.setTimeout(() => toast.remove(), 4200);
  };

  const writeMessage = (form, message, isError = false) => {
    const target = form.querySelector('.form-message');
    if (!target) return;
    target.textContent = message || '';
    target.classList.toggle('is-error', isError);
  };

  document.addEventListener('submit', async (event) => {
    const form = event.target.closest('form[data-api-form]');
    if (!form) return;
    event.preventDefault();
    if (form.dataset.submitting === 'true') return;
    form.dataset.submitting = 'true';
    const submitter = event.submitter;
    if (submitter) submitter.disabled = true;
    writeMessage(form, '');
    try {
      const response = await fetch(form.getAttribute('action'), {
        method: 'POST',
        body: new FormData(form, submitter || undefined),
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
      });
      const result = await response.json();
      if (!response.ok || !result.ok) {
        writeMessage(form, result.message || 'We could not complete that request.', true);
        if (result.redirect) window.location.assign(result.redirect);
        return;
      }
      writeMessage(form, result.message || 'Saved.');
      if (typeof result.cart_count === 'number') {
        document.querySelectorAll('[data-cart-count]').forEach((node) => { node.textContent = String(result.cart_count); });
        document.querySelectorAll('[data-cart-link]').forEach((node) => {
          node.setAttribute('aria-label', `Shopping Bag, ${result.cart_count} ${result.cart_count === 1 ? 'item' : 'items'}`);
        });
      }
      if (typeof result.saved === 'boolean') {
        const productId = Number(form.querySelector('input[name="product_id"]')?.value);
        document.querySelectorAll('[data-wishlist-button]').forEach((button) => {
          if (Number(button.dataset.productId) !== productId) return;
          const productName = button.dataset.productName || 'fragrance';
          button.classList.toggle('is-saved', result.saved);
          button.setAttribute('aria-pressed', String(result.saved));
          button.setAttribute('aria-label', `${result.saved ? 'Remove' : 'Add'} ${productName} ${result.saved ? 'from' : 'to'} wishlist`);
          button.title = result.saved ? 'Remove from wishlist' : 'Add to wishlist';
        });
        if (!result.saved && form.matches('[data-wishlist-remove]')) {
          form.closest('[data-account-wishlist-item]')?.remove();
          const remaining = document.querySelectorAll('[data-account-wishlist-item]').length;
          const count = document.querySelector('[data-wishlist-total]');
          const grid = document.querySelector('[data-account-wishlist-grid]');
          const empty = document.querySelector('[data-account-wishlist-empty]');
          if (count) count.textContent = String(Math.max(0, Number(count.textContent) - 1));
          if (grid) grid.hidden = remaining === 0;
          if (empty) empty.hidden = remaining !== 0;
        }
      }
      if (result.order && form.dataset.resultContainer) renderOrder(result.order, document.querySelector(form.dataset.resultContainer), form);
      if (form.matches('[data-order-cancel]') && result.order_status === 'cancelled') {
        const card = form.closest('.track-order-card');
        const status = card?.querySelector('.status-pill');
        if (status) status.textContent = 'cancelled';
        form.remove();
        showToast(result.message || 'Your order has been cancelled.');
      }
      if (!form.querySelector('.form-message') && result.message) showToast(result.message);
      if (form.dataset.redirectSuccess) window.location.assign(form.dataset.redirectSuccess);
      else if (result.redirect) window.location.assign(result.redirect);
      else if (result.reload) window.setTimeout(() => window.location.reload(), 1500);
      else if (form.dataset.success && form.querySelector('.form-message')) writeMessage(form, form.dataset.success);
    } catch {
      writeMessage(form, 'We could not reach the store. Please try again.', true);
    } finally {
      delete form.dataset.submitting;
      if (submitter && submitter.isConnected) submitter.disabled = false;
    }
  });

  function renderOrder(order, container, lookupForm) {
    if (!container) return;
    container.replaceChildren();
    const card = document.createElement('article');
    card.className = 'track-order-card';
    const heading = document.createElement('div');
    heading.className = 'track-order-heading';
    const orderTitle = document.createElement('h2');
    orderTitle.textContent = `Order ${order.order_number}`;
    const status = document.createElement('span');
    status.className = 'status-pill';
    status.textContent = `${order.order_status} · ${String(order.payment_status).replaceAll('_', ' ')}`;
    heading.append(orderTitle, status);
    card.append(heading);
    const total = document.createElement('p');
    total.textContent = `Total ৳${new Intl.NumberFormat('en-US').format(Number(order.grand_total))}`;
    card.append(total);
    const items = document.createElement('ul');
    for (const item of order.items || []) {
      const row = document.createElement('li');
      row.textContent = `${item.product_name} · ${item.size_label} · Qty ${item.quantity}`;
      items.append(row);
    }
    card.append(items);
    const history = document.createElement('ol');
    history.className = 'track-history';
    for (const item of order.history || []) {
      const row = document.createElement('li');
      row.className = 'is-done';
      const label = document.createElement('strong');
      label.textContent = item.status;
      const note = document.createElement('small');
      note.textContent = item.note || '';
      row.append(label, note);
      history.append(row);
    }
    card.append(history);
    if (lookupForm && order.can_cancel === true) {
      const cancelForm = document.createElement('form');
      cancelForm.setAttribute('action', lookupForm.getAttribute('action'));
      cancelForm.method = 'post';
      cancelForm.className = 'track-cancel-form';
      cancelForm.dataset.apiForm = '';
      cancelForm.dataset.orderCancel = '';
      const csrf = document.createElement('input');
      csrf.type = 'hidden'; csrf.name = 'csrf';
      csrf.value = lookupForm.querySelector('input[name="csrf"]')?.value || '';
      const action = document.createElement('input');
      action.type = 'hidden'; action.name = 'action'; action.value = 'track.cancel';
      const orderNumber = document.createElement('input');
      orderNumber.type = 'hidden'; orderNumber.name = 'order_number';
      orderNumber.value = lookupForm.querySelector('[name="order_number"]')?.value || order.order_number;
      const phone = document.createElement('input');
      phone.type = 'hidden'; phone.name = 'phone';
      phone.value = lookupForm.querySelector('[name="phone"]')?.value || '';
      const button = document.createElement('button');
      button.type = 'submit'; button.className = 'button button-light'; button.textContent = 'Cancel this order';
      const message = document.createElement('p');
      message.className = 'form-message'; message.setAttribute('aria-live', 'polite');
      cancelForm.append(csrf, action, orderNumber, phone, button, message);
      card.append(cancelForm);
    }
    container.append(card);
  }

  const themeButton = document.querySelector('[data-theme-toggle]');
  const readCookie = (name) => document.cookie.split('; ').find((cookie) => cookie.startsWith(`${name}=`))?.split('=').slice(1).join('=');
  const readSavedTheme = () => {
    try { return localStorage.getItem('maison-serein-theme'); } catch { return null; }
  };
  const saveTheme = (theme) => {
    try { localStorage.setItem('maison-serein-theme', theme); } catch { }
    const secure = window.location.protocol === 'https:' ? '; Secure' : '';
    document.cookie = `theme=${theme}; Max-Age=31536000; Path=/; SameSite=Lax${secure}`;
  };
  const setTheme = (theme) => {
    document.documentElement.dataset.theme = theme;
    if (themeButton) {
      themeButton.setAttribute('aria-label', theme === 'dark' ? 'Switch to light theme' : 'Switch to dark theme');
      themeButton.setAttribute('aria-pressed', String(theme === 'dark'));
      themeButton.title = theme === 'dark' ? 'Switch to light theme' : 'Switch to dark theme';
    }
    document.querySelector('meta[name="theme-color"]')?.setAttribute('content', theme === 'dark' ? '#171916' : '#f5f2ed');
  };
  const savedTheme = readSavedTheme() || readCookie('theme');
  setTheme(savedTheme === 'dark' || savedTheme === 'light' ? savedTheme : 'light');
  themeButton?.addEventListener('click', () => {
    const next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
    setTheme(next);
    saveTheme(next);
  });

  document.querySelectorAll('[data-contact-mailto]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      event.preventDefault();
      if (!form.reportValidity()) return;
      const fields = new FormData(form);
      const subject = String(fields.get('subject') || 'Message to Maison Serein');
      const body = [
        `Name: ${fields.get('name') || ''}`,
        `Email: ${fields.get('email') || ''}`,
        '',
        String(fields.get('message') || ''),
      ].join('\n');
      const recipient = form.dataset.recipient;
      if (!recipient) {
        writeMessage(form, 'A verified customer-care email is not configured.', true);
        return;
      }
      window.location.href = `mailto:${recipient}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
      writeMessage(form, 'Your email app should open with this message. The website does not send or store it.');
    });
  });

  const menuButton = document.querySelector('[data-menu-toggle]');
  const mainNav = document.querySelector('[data-main-nav]');
  const searchToggle = document.querySelector('[data-search-toggle]');
  const searchForm = document.querySelector('#site-search-form');
  const setSearchOpen = (open) => {
    searchForm?.classList.toggle('is-open', open);
    searchToggle?.setAttribute('aria-expanded', String(open));
    searchToggle?.setAttribute('aria-label', open ? 'Close search' : 'Open search');
    if (open) {
      mainNav?.classList.remove('is-open');
      menuButton?.setAttribute('aria-expanded', 'false');
      menuButton?.setAttribute('aria-label', 'Open navigation');
      searchForm?.querySelector('input[type="search"]')?.focus();
    }
  };
  searchToggle?.addEventListener('click', () => {
    setSearchOpen(searchToggle.getAttribute('aria-expanded') !== 'true');
  });
  menuButton?.addEventListener('click', () => {
    const isOpen = menuButton.getAttribute('aria-expanded') === 'true';
    if (!isOpen) setSearchOpen(false);
    menuButton.setAttribute('aria-expanded', String(!isOpen));
    menuButton.setAttribute('aria-label', isOpen ? 'Open navigation' : 'Close navigation');
    mainNav?.classList.toggle('is-open', !isOpen);
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && searchToggle?.getAttribute('aria-expanded') === 'true') {
      setSearchOpen(false);
      searchToggle.focus();
    }
  });
  mainNav?.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => {
    menuButton?.setAttribute('aria-expanded', 'false');
    menuButton?.setAttribute('aria-label', 'Open navigation');
    mainNav.classList.remove('is-open');
  }));

  const paymentField = document.querySelector('[data-transaction-field]');
  const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
  const updatePaymentField = () => {
    if (!paymentField || !paymentRadios.length) return;
    const selected = document.querySelector('input[name="payment_method"]:checked')?.value;
    const requiresId = Boolean(selected && selected !== 'cod');
    paymentField.hidden = !requiresId;
    paymentField.querySelector('input').required = requiresId;
  };
  paymentRadios.forEach((radio) => radio.addEventListener('change', updatePaymentField));
  updatePaymentField();

  const checkoutForm = document.querySelector('form.checkout-layout[data-checkout-form]');
  if (checkoutForm && checkoutForm.dataset.quoteInitialized !== 'true') {
    checkoutForm.dataset.quoteInitialized = 'true';
    const formatMoney = (amount) => `৳${new Intl.NumberFormat('en-US', { maximumFractionDigits: 2 }).format(Number(amount || 0))}`;
    const couponInput = checkoutForm.querySelector('[name="discount_code"]');
    const zoneSelect = checkoutForm.querySelector('[name="delivery_zone_id"]');
    const phoneInput = checkoutForm.querySelector('[name="phone"]');
    const couponMessage = checkoutForm.querySelector('[data-coupon-message]');
    const couponStatus = checkoutForm.querySelector('[data-coupon-code]');
    const removeCoupon = checkoutForm.querySelector('[data-remove-coupon]');
    const itemsContainer = checkoutForm.querySelector('[data-checkout-items]');
    let quoteRequest = 0;
    let activeQuoteController = null;

    const renderQuote = (quote) => {
      if (itemsContainer) {
        itemsContainer.replaceChildren();
        for (const item of quote.items || []) {
          const row = document.createElement('article');
          row.className = 'summary-product';
          const image = document.createElement('img');
          image.src = item.image_path || 'assets/images/amber-veil.png';
          image.alt = item.name || 'Fragrance';
          const details = document.createElement('div');
          details.className = 'summary-product-details';
          const name = document.createElement('strong');
          name.textContent = item.name;
          const variant = document.createElement('small');
          variant.textContent = `${item.size_label} · Qty ${item.quantity}`;
          const unitPrice = document.createElement('span');
          unitPrice.className = 'summary-unit-price';
          if (Number(item.regular_price) > Number(item.offer_price)) {
            const original = document.createElement('del');
            original.textContent = formatMoney(item.regular_price);
            unitPrice.append(original);
          }
          const selling = document.createElement('span');
          selling.textContent = `${formatMoney(item.offer_price)} each`;
          unitPrice.append(selling);
          details.append(name, variant, unitPrice);
          const total = document.createElement('strong');
          total.className = 'summary-product-total';
          total.textContent = formatMoney(item.line_total);
          row.append(image, details, total);
          itemsContainer.append(row);
        }
      }

      const setText = (selector, value) => {
        const node = checkoutForm.querySelector(selector);
        if (node) node.textContent = value;
      };
      setText('[data-checkout-subtotal]', formatMoney(quote.subtotal));
      setText('[data-product-discount]', formatMoney(quote.product_discount));
      setText('[data-coupon-discount]', formatMoney(quote.coupon_discount));
      setText('[data-delivery-charge]', formatMoney(quote.delivery_fee));
      setText('[data-grand-total]', formatMoney(quote.grand_total));
      setText('[data-delivery-zone]', quote.delivery_zone
        ? `${quote.delivery_zone.name}${quote.delivery_zone.estimated_days ? ` · ${quote.delivery_zone.estimated_days}` : ''}`
        : 'Choose an area to see the charge');
      setText('[data-coupon-code]', quote.coupon_code
        ? `Applied: ${quote.coupon_code}`
        : 'No offer code applied');
      if (removeCoupon) removeCoupon.hidden = !quote.coupon_code;
    };

    const quoteCheckout = async (codeOverride) => {
      const requestId = ++quoteRequest;
      activeQuoteController?.abort();
      activeQuoteController = new AbortController();
      checkoutForm.setAttribute('aria-busy', 'true');
      const deliveryZone = checkoutForm.querySelector('[data-delivery-zone]');
      if (zoneSelect?.value && deliveryZone) deliveryZone.textContent = 'Updating delivery charge…';
      if (couponMessage) {
        couponMessage.textContent = 'Updating your order total…';
        couponMessage.classList.remove('is-error');
      }
      const body = new FormData(checkoutForm);
      body.set('action', 'checkout.quote');
      body.set('delivery_zone_id', zoneSelect?.value || '');
      if (typeof codeOverride === 'string') body.set('discount_code', codeOverride);
      try {
        const response = await fetch(checkoutForm.getAttribute('action'), {
          method: 'POST',
          body,
          headers: { Accept: 'application/json' },
          credentials: 'same-origin',
          signal: activeQuoteController.signal,
        });
        const result = await response.json();
        if (requestId !== quoteRequest) return;
        if (!response.ok || !result.ok) {
          if (couponMessage) {
            couponMessage.textContent = result.message || 'We could not update the order summary.';
            couponMessage.classList.add('is-error');
          }
          if (deliveryZone && zoneSelect?.value) deliveryZone.textContent = 'Delivery charge could not be loaded.';
          return;
        }
        renderQuote(result.quote);
        if (couponInput && typeof codeOverride === 'string') couponInput.value = codeOverride;
        const couponError = result.quote.coupon_error || '';
        if (couponMessage) {
          couponMessage.textContent = couponError || result.message || '';
          couponMessage.classList.toggle('is-error', Boolean(couponError));
        }
      } catch (error) {
        if (error.name === 'AbortError' || requestId !== quoteRequest) return;
        if (couponMessage) {
          couponMessage.textContent = 'We could not reach the store. Please try again.';
          couponMessage.classList.add('is-error');
        }
        if (deliveryZone && zoneSelect?.value) deliveryZone.textContent = 'Delivery charge could not be loaded.';
      } finally {
        if (requestId === quoteRequest) {
          checkoutForm.removeAttribute('aria-busy');
          activeQuoteController = null;
        }
      }
    };

    zoneSelect?.addEventListener('change', () => quoteCheckout(couponInput?.value.trim().toUpperCase() || ''));
    phoneInput?.addEventListener('change', () => {
      const code = couponInput?.value.trim().toUpperCase() || '';
      if (code) quoteCheckout(code);
    });
    checkoutForm.querySelector('[data-apply-coupon]')?.addEventListener('click', () => {
      const code = couponInput?.value.trim().toUpperCase() || '';
      if (!code) {
        if (couponMessage) {
          couponMessage.textContent = 'Enter an offer code.';
          couponMessage.classList.add('is-error');
        }
        return;
      }
      quoteCheckout(code);
    });
    removeCoupon?.addEventListener('click', () => {
      quoteCheckout('');
      if (couponInput) couponInput.value = '';
    });
    quoteCheckout(couponInput?.value.trim().toUpperCase() || '');
  }

  const productWhatsAppLink = document.querySelector('[data-whatsapp-link]');
  if (productWhatsAppLink) {
    const quantityInput = document.querySelector(productWhatsAppLink.dataset.whatsappQuantity || '#quantity');
    const updateProductWhatsAppLink = () => {
      const selectedVariant = document.querySelector('input[name="variant_id"]:checked');
      const size = selectedVariant?.dataset.sizeLabel || productWhatsAppLink.dataset.selectedSize || 'To be confirmed';
      const quantity = Math.max(1, Math.min(10, Number(quantityInput?.value) || 1));
      const unitPrice = Number(selectedVariant?.dataset.priceValue || productWhatsAppLink.dataset.selectedPrice || 0);
      const formatPrice = (amount) => new Intl.NumberFormat('en-US', { maximumFractionDigits: 0 }).format(amount);
      const message = [
        'Hello, I’m interested in this fragrance. This is an inquiry only, not a confirmed order.',
        `Product: ${productWhatsAppLink.dataset.productName}`,
        `Size: ${size}`,
        `Quantity: ${quantity}`,
        `Unit price: BDT ${formatPrice(unitPrice)}`,
        `Item total: BDT ${formatPrice(unitPrice * quantity)}`,
      ].join('\n');
      productWhatsAppLink.href = `https://wa.me/${productWhatsAppLink.dataset.whatsappNumber}?text=${encodeURIComponent(message)}`;
    };
    document.querySelectorAll('input[name="variant_id"]').forEach((radio) => radio.addEventListener('change', updateProductWhatsAppLink));
    quantityInput?.addEventListener('input', updateProductWhatsAppLink);
    updateProductWhatsAppLink();
  }

  const cartWhatsAppLink = document.querySelector('[data-whatsapp-cart]');
  const cartWhatsAppData = document.getElementById('whatsapp-cart-data');
  if (cartWhatsAppLink && cartWhatsAppData) {
    try {
      const items = JSON.parse(cartWhatsAppData.textContent);
      const formatPrice = (amount) => new Intl.NumberFormat('en-US', { maximumFractionDigits: 0 }).format(amount);
      const lines = items.map((item) => `${item.product} — ${item.size} × ${item.quantity} · BDT ${formatPrice(item.unit_price)} each · BDT ${formatPrice(item.line_total)}`);
      const subtotal = items.reduce((total, item) => total + Number(item.line_total || 0), 0);
      const message = [
        'Hello, I have a question about this bag. This is an inquiry only, not a confirmed order.',
        'Bag summary:',
        ...lines,
        `Bag subtotal: BDT ${formatPrice(subtotal)}`,
        'Delivery, discounts, and the confirmed total are calculated at website checkout.',
      ].join('\n');
      cartWhatsAppLink.href = `https://wa.me/${cartWhatsAppLink.dataset.whatsappNumber}?text=${encodeURIComponent(message)}`;
    } catch {
      cartWhatsAppLink.removeAttribute('href');
    }
  }

  document.querySelectorAll('input[name="variant_id"][data-price-value]').forEach((radio) => {
    radio.addEventListener('change', () => {
      const price = document.querySelector('[data-price]');
      const quantity = document.querySelector('input[name="quantity"]');
      const stock = Number(radio.dataset.stockValue || 0);
      if (price) price.textContent = `৳${new Intl.NumberFormat('en-US', { maximumFractionDigits: 0 }).format(Number(radio.dataset.priceValue))}`;
      if (quantity) quantity.max = String(Math.max(1, Math.min(10, stock)));
    });
  });

  const accountNav = document.querySelector('[data-account-nav]');
  if (accountNav) {
    const accountLinks = Array.from(accountNav.querySelectorAll('a[href^="#"]'));
    const accountSections = accountLinks
      .map((link) => document.querySelector(link.getAttribute('href')))
      .filter(Boolean);
    const setActiveAccountLink = (sectionId) => {
      accountLinks.forEach((link) => {
        if (link.hash === `#${sectionId}`) link.setAttribute('aria-current', 'page');
        else link.removeAttribute('aria-current');
      });
    };
    accountLinks.forEach((link) => {
      link.addEventListener('click', () => setActiveAccountLink(link.hash.slice(1)));
    });
    if ('IntersectionObserver' in window && accountSections.length) {
      const accountObserver = new IntersectionObserver((entries) => {
        const visibleSections = entries.filter((entry) => entry.isIntersecting);
        if (!visibleSections.length) return;
        visibleSections.sort((a, b) => b.intersectionRatio - a.intersectionRatio);
        setActiveAccountLink(visibleSections[0].target.id);
      }, { rootMargin: '-15% 0px -65% 0px', threshold: [0, 0.2, 0.5] });
      accountSections.forEach((section) => accountObserver.observe(section));
    }
  }

  const popup = document.querySelector('[data-promo-modal]');
  const backdrop = document.querySelector('[data-promo-backdrop]');
  const popupClose = document.querySelector('[data-promo-close]');
  if (popup && backdrop && popupClose) {
    const popupId = popup.dataset.popupId || 'house-popup';
    const frequency = popup.dataset.frequency || 'session';
    const storageKey = `promo:${popupId}`;
    const wasShown = () => {
      try {
        return (frequency === 'once' ? localStorage : sessionStorage).getItem(storageKey) === 'seen';
      } catch {
        try { return sessionStorage.getItem(storageKey) === 'seen'; } catch { return false; }
      }
    };
    const rememberShown = () => {
      try {
        (frequency === 'once' ? localStorage : sessionStorage).setItem(storageKey, 'seen');
      } catch {
        try { sessionStorage.setItem(storageKey, 'seen'); } catch { /* Storage may be disabled. */ }
      }
    };
    let previousFocus = null;
    let showTimer = 0;
    const closePopup = () => {
      popup.hidden = true;
      backdrop.hidden = true;
      document.body.classList.remove('promo-open');
      if (previousFocus && typeof previousFocus.focus === 'function') previousFocus.focus();
      previousFocus = null;
    };
    if (!wasShown()) showTimer = window.setTimeout(() => {
      previousFocus = document.activeElement;
      popup.hidden = false;
      backdrop.hidden = false;
      document.body.classList.add('promo-open');
      rememberShown();
      popupClose.focus();
    }, 1200);
    popupClose.addEventListener('click', closePopup);
    backdrop.addEventListener('click', closePopup);
    document.addEventListener('keydown', (event) => {
      if (popup.hidden) return;
      if (event.key === 'Escape') {
        closePopup();
        return;
      }
      if (event.key !== 'Tab') return;
      const focusable = Array.from(popup.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), [tabindex]:not([tabindex="-1"])'))
        .filter((element) => !element.hidden);
      if (!focusable.length) return;
      const first = focusable[0];
      const last = focusable[focusable.length - 1];
      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
      }
    });
    const bannerData = document.getElementById('promo-banner-data');
    const items = bannerData ? JSON.parse(bannerData.textContent) : [];
    let currentIndex = 0;
    const renderPromo = (index) => {
      if (!items.length) return;
      currentIndex = (index + items.length) % items.length;
      const item = items[currentIndex];
      const image = popup.querySelector('[data-promo-image]');
      const title = popup.querySelector('[data-promo-title]');
      const subtitle = popup.querySelector('[data-promo-subtitle]');
      const link = popup.querySelector('[data-promo-link]');
      const position = popup.querySelector('[data-promo-position]');
      if (image) { image.src = item.image_path || 'assets/images/amber-veil.png'; image.alt = ''; }
      if (title) title.textContent = item.title || '';
      if (subtitle) subtitle.textContent = item.subtitle || '';
      if (link) { link.href = item.cta_url || '?page=shop'; link.textContent = item.cta_label || 'Explore the collection'; }
      if (position) position.textContent = `${currentIndex + 1} / ${items.length}`;
    };
    popup.querySelector('[data-promo-prev]')?.addEventListener('click', () => renderPromo(currentIndex - 1));
    popup.querySelector('[data-promo-next]')?.addEventListener('click', () => renderPromo(currentIndex + 1));
    window.addEventListener('pagehide', () => window.clearTimeout(showTimer), { once: true });
  }

  document.querySelectorAll('[data-countdown]').forEach((node) => {
    const endAt = new Date(node.dataset.countdown).getTime();
    if (!Number.isFinite(endAt)) return;
    const update = () => {
      const seconds = Math.max(0, Math.floor((endAt - Date.now()) / 1000));
      if (!seconds) { node.textContent = 'This release has ended.'; return false; }
      const days = Math.floor(seconds / 86400);
      const hours = Math.floor((seconds % 86400) / 3600);
      const minutes = Math.floor((seconds % 3600) / 60);
      const remaining = seconds % 60;
      node.replaceChildren(document.createTextNode('Available for '));
      const strong = document.createElement('strong');
      strong.textContent = `${days}d ${hours}h ${minutes}m ${remaining}s`;
      node.append(strong);
      return true;
    };
    if (update()) {
      const timer = window.setInterval(() => { if (!update()) window.clearInterval(timer); }, 1000);
    }
  });

  document.querySelectorAll('[data-gallery-image]').forEach((button) => button.addEventListener('click', () => {
    const image = document.querySelector('[data-main-product-image]');
    const source = button.querySelector('img');
    if (!image || !source) return;
    image.src = source.src;
    image.alt = source.alt;
    document.querySelectorAll('[data-gallery-image]').forEach((item) => item.setAttribute('aria-pressed', String(item === button)));
  }));

  document.querySelectorAll('[data-shop-sort]').forEach((select) => select.addEventListener('change', () => {
    const params = new URLSearchParams(window.location.search);
    params.set('sort', select.value);
    params.delete('p');
    window.location.assign(`?${params.toString()}`);
  }));

  document.querySelectorAll('[data-price-filter]').forEach((form) => {
    const minPrice = form.querySelector('[name="min_price"]');
    const maxPrice = form.querySelector('[name="max_price"]');
    const error = form.querySelector('#price-range-error');
    if (!minPrice || !maxPrice) return;
    const validatePriceRange = () => {
      const invalid = minPrice.value !== '' && maxPrice.value !== '' && Number(minPrice.value) > Number(maxPrice.value);
      const message = invalid ? 'Maximum price must be greater than or equal to minimum price.' : '';
      maxPrice.setCustomValidity(message);
      if (error) {
        error.textContent = message;
        error.hidden = !invalid;
      }
      return !invalid;
    };
    minPrice.addEventListener('input', validatePriceRange);
    maxPrice.addEventListener('input', validatePriceRange);
    form.addEventListener('submit', (event) => {
      if (validatePriceRange()) return;
      event.preventDefault();
      maxPrice.reportValidity();
    });
  });

  const filterShell = document.querySelector('[data-filter-shell]');
  const filterPanel = filterShell?.querySelector('[data-filter-panel]');
  const filterOpen = document.querySelector('[data-filter-open]');
  const filterClose = filterShell?.querySelector('[data-filter-close]');
  const filterBackdrop = filterShell?.querySelector('[data-filter-backdrop]');
  if (filterShell && filterPanel && filterOpen && filterBackdrop && filterShell.dataset.drawerInitialized !== 'true') {
    filterShell.dataset.drawerInitialized = 'true';
    let returnFocus = null;
    const focusableSelector = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';
    const closeFilterDrawer = (restoreFocus = true) => {
      filterShell.classList.remove('is-open');
      filterBackdrop.hidden = true;
      filterOpen.setAttribute('aria-expanded', 'false');
      filterPanel.removeAttribute('role');
      filterPanel.removeAttribute('aria-modal');
      document.body.classList.remove('filter-drawer-open');
      if (restoreFocus && returnFocus?.isConnected) returnFocus.focus();
    };
    const openFilterDrawer = () => {
      returnFocus = document.activeElement;
      filterBackdrop.hidden = false;
      filterShell.classList.add('is-open');
      filterOpen.setAttribute('aria-expanded', 'true');
      filterPanel.setAttribute('role', 'dialog');
      filterPanel.setAttribute('aria-modal', 'true');
      document.body.classList.add('filter-drawer-open');
      filterClose?.focus();
    };
    filterOpen.addEventListener('click', openFilterDrawer);
    filterClose?.addEventListener('click', () => closeFilterDrawer());
    filterBackdrop.addEventListener('click', (event) => {
      if (event.target === filterBackdrop) closeFilterDrawer();
    });
    document.addEventListener('keydown', (event) => {
      if (!filterShell.classList.contains('is-open')) return;
      if (event.key === 'Escape') {
        closeFilterDrawer();
        return;
      }
      if (event.key !== 'Tab') return;
      const focusable = [...filterPanel.querySelectorAll(focusableSelector)].filter((element) => element.getClientRects().length > 0);
      if (!focusable.length) {
        event.preventDefault();
        filterPanel.focus();
        return;
      }
      const first = focusable[0];
      const last = focusable[focusable.length - 1];
      if (event.shiftKey && (document.activeElement === first || !filterPanel.contains(document.activeElement))) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && (document.activeElement === last || !filterPanel.contains(document.activeElement))) {
        event.preventDefault();
        first.focus();
      }
    });
    filterShell.querySelector('form')?.addEventListener('submit', () => closeFilterDrawer(false));
    window.addEventListener('resize', () => {
      if (window.matchMedia('(min-width: 901px)').matches && filterShell.classList.contains('is-open')) closeFilterDrawer(false);
    });
  }
})();
