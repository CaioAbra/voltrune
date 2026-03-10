import './bootstrap';

document.documentElement.classList.add('js');

const body = document.body;

const initMobileMenu = () => {
  const toggle = document.querySelector('[data-menu-toggle]');
  const menu = document.querySelector('[data-menu]');

  if (!toggle || !menu) return;

  const closeMenu = () => {
    menu.classList.remove('is-open');
    toggle.setAttribute('aria-expanded', 'false');
    body.classList.remove('menu-open');
  };

  toggle.addEventListener('click', () => {
    const isOpen = menu.classList.toggle('is-open');
    toggle.setAttribute('aria-expanded', String(isOpen));
    body.classList.toggle('menu-open', isOpen);
  });

  menu.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', closeMenu);
  });

  document.addEventListener('click', (event) => {
    if (!(event.target instanceof Node)) return;
    if (toggle.contains(event.target) || menu.contains(event.target)) return;
    closeMenu();
  });

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    closeMenu();
  });
};

const initSmoothScroll = () => {
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener('click', (event) => {
      const href = anchor.getAttribute('href');
      if (!href || href === '#') return;

      const target = document.querySelector(href);
      if (!target) return;

      event.preventDefault();
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });
};

const initModals = () => {
  const modals = Array.from(document.querySelectorAll('[data-modal]'));
  if (!modals.length) return;

  let activeModal = null;
  let previousActiveElement = null;

  const getFocusableElements = (modal) =>
    Array.from(modal.querySelectorAll('button, [href], input, textarea, select, [tabindex]:not([tabindex="-1"])'))
      .filter((el) => !el.hasAttribute('disabled'));

  const closeModal = (modal) => {
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    body.classList.remove('modal-open');

    if (previousActiveElement) {
      previousActiveElement.focus();
    }

    activeModal = null;
  };

  const openModal = (modal) => {
    previousActiveElement = document.activeElement;
    activeModal = modal;
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    body.classList.add('modal-open');

    const focusable = getFocusableElements(modal);
    if (focusable.length) {
      focusable[0].focus();
    }
  };

  document.querySelectorAll('[data-modal-open]').forEach((trigger) => {
    trigger.addEventListener('click', () => {
      const id = trigger.getAttribute('data-modal-open');
      const modal = id ? document.getElementById(id) : null;
      if (modal) openModal(modal);
    });
  });

  modals.forEach((modal) => {
    modal.querySelectorAll('[data-modal-close]').forEach((closeTrigger) => {
      closeTrigger.addEventListener('click', () => closeModal(modal));
    });

    modal.querySelectorAll('a[href]').forEach((link) => {
      link.addEventListener('click', () => closeModal(modal));
    });
  });

  document.addEventListener('keydown', (event) => {
    if (!activeModal) return;

    if (event.key === 'Escape') {
      closeModal(activeModal);
      return;
    }

    if (event.key !== 'Tab') return;

    const focusable = getFocusableElements(activeModal);
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

  window.addEventListener('pageshow', () => {
    modals.forEach((modal) => {
      modal.classList.remove('is-open');
      modal.setAttribute('aria-hidden', 'true');
    });

    body.classList.remove('modal-open');
    activeModal = null;
  });
};

const initPortfolioFilter = () => {
  const buttons = Array.from(document.querySelectorAll('[data-filter]'));
  const cards = Array.from(document.querySelectorAll('[data-portfolio-grid] .quest-card[data-tags]'));
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  if (!buttons.length || !cards.length) return;

  const hideCard = (card) => {
    if (reduceMotion) {
      card.classList.add('is-filtered');
      card.classList.remove('is-filtering-out');
      return;
    }

    card.classList.add('is-filtering-out');
    window.setTimeout(() => {
      card.classList.add('is-filtered');
      card.classList.remove('is-filtering-out');
    }, 260);
  };

  const showCard = (card) => {
    card.classList.remove('is-filtered');

    if (reduceMotion) {
      card.classList.remove('is-filtering-out');
      return;
    }

    card.classList.add('is-filtering-out');
    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        card.classList.remove('is-filtering-out');
      });
    });
  };

  buttons.forEach((button) => {
    button.addEventListener('click', () => {
      const filter = button.getAttribute('data-filter');

      buttons.forEach((item) => {
        item.classList.remove('is-active');
        item.setAttribute('aria-pressed', 'false');
      });
      button.classList.add('is-active');
      button.setAttribute('aria-pressed', 'true');

      cards.forEach((card) => {
        if (!filter || filter === 'all') {
          showCard(card);
          return;
        }

        const tags = (card.getAttribute('data-tags') || '').split(',').map((tag) => tag.trim());
        const shouldShow = tags.includes(filter);
        if (shouldShow) {
          showCard(card);
        } else {
          hideCard(card);
        }
      });
    });
  });
};

const initQuestReveal = () => {
  const revealItems = Array.from(document.querySelectorAll('.quest-card[data-reveal]'));
  if (!revealItems.length) return;

  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduceMotion || !('IntersectionObserver' in window)) {
    revealItems.forEach((item) => item.classList.add('is-visible'));
    return;
  }

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;

        const target = entry.target;
        const stagger = Number(target.getAttribute('data-stagger') || 0);

        window.setTimeout(() => {
          target.classList.add('is-visible');
        }, stagger);

        observer.unobserve(target);
      });
    },
    {
      threshold: 0.16,
      rootMargin: '0px 0px -10% 0px',
    },
  );

  revealItems.forEach((item, index) => {
    item.setAttribute('data-stagger', String(index * 85));
    observer.observe(item);
  });
};

const initFaqAccordion = () => {
  const groups = Array.from(document.querySelectorAll('[data-faq-group]'));
  if (!groups.length) return;
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  groups.forEach((group) => {
    const items = Array.from(group.querySelectorAll('details.faq-item'));
    if (!items.length) return;

    const getAnswer = (item) => item.querySelector('.faq-answer');

    const closeItem = (item) => {
      const answer = getAnswer(item);
      if (!answer || !item.open) return;

      if (reduceMotion) {
        answer.style.height = '0px';
        item.open = false;
        return;
      }

      answer.style.height = `${answer.scrollHeight}px`;
      requestAnimationFrame(() => {
        answer.style.height = '0px';
      });
      window.setTimeout(() => {
        item.open = false;
      }, 320);
    };

    const openItem = (item) => {
      const answer = getAnswer(item);
      if (!answer) return;

      item.open = true;

      if (reduceMotion) {
        answer.style.height = 'auto';
        return;
      }

      answer.style.height = '0px';
      requestAnimationFrame(() => {
        answer.style.height = `${answer.scrollHeight}px`;
      });
      window.setTimeout(() => {
        answer.style.height = 'auto';
      }, 320);
    };

    items.forEach((item) => {
      const answer = getAnswer(item);
      const summary = item.querySelector('summary');
      if (!answer || !summary) return;

      answer.style.height = item.open ? 'auto' : '0px';

      summary.addEventListener('click', (event) => {
        event.preventDefault();

        if (item.open) {
          closeItem(item);
          return;
        }

        items.forEach((other) => {
          if (other !== item) closeItem(other);
        });

        openItem(item);
      });
    });
  });
};

const initPhoneMask = () => {
  const fields = Array.from(document.querySelectorAll('[data-phone-mask]'));
  if (!fields.length) return;

  const formatPhone = (value) => {
    const digits = value.replace(/\D/g, '').slice(0, 11);

    if (digits.length <= 2) return digits ? `(${digits}` : '';
    if (digits.length <= 7) return `(${digits.slice(0, 2)}) ${digits.slice(2)}`;
    if (digits.length <= 10) return `(${digits.slice(0, 2)}) ${digits.slice(2, 6)}-${digits.slice(6)}`;

    return `(${digits.slice(0, 2)}) ${digits.slice(2, 7)}-${digits.slice(7)}`;
  };

  fields.forEach((field) => {
    field.value = formatPhone(field.value);

    field.addEventListener('input', () => {
      field.value = formatPhone(field.value);
    });
  });
};

const initSubmitLocks = () => {
  const forms = Array.from(document.querySelectorAll('form[data-lock-submit]'));
  if (!forms.length) return;

  const setSubmittingState = (form, isSubmitting) => {
    const button = form.querySelector('[data-submit-button]');
    const label = form.querySelector('[data-submit-label]');

    form.dataset.isSubmitting = isSubmitting ? 'true' : 'false';
    form.setAttribute('aria-busy', isSubmitting ? 'true' : 'false');

    if (!button) return;

    const defaultLabel = button.getAttribute('data-default-label') || button.textContent?.trim() || 'Enviar';
    const loadingLabel = button.getAttribute('data-loading-label') || 'Enviando...';

    button.disabled = isSubmitting;
    button.classList.toggle('is-submitting', isSubmitting);

    if (label) {
      label.textContent = isSubmitting ? loadingLabel : defaultLabel;
    }
  };

  forms.forEach((form) => {
    setSubmittingState(form, false);

    form.addEventListener('submit', (event) => {
      if (form.dataset.isSubmitting === 'true') {
        event.preventDefault();
        return;
      }

      setSubmittingState(form, true);
    });
  });

  window.addEventListener('pageshow', () => {
    forms.forEach((form) => setSubmittingState(form, false));
  });
};

const initCustomSelects = () => {
  const selects = Array.from(document.querySelectorAll('[data-custom-select]'));
  if (!selects.length) return;

  let activeSelect = null;

  const closeSelect = (container) => {
    const trigger = container.querySelector('[data-custom-select-trigger]');
    const panel = container.querySelector('[data-custom-select-panel]');
    if (!trigger || !panel) return;

    container.classList.remove('is-open');
    trigger.setAttribute('aria-expanded', 'false');
    panel.hidden = true;

    if (activeSelect === container) {
      activeSelect = null;
    }
  };

  const openSelect = (container) => {
    const trigger = container.querySelector('[data-custom-select-trigger]');
    const panel = container.querySelector('[data-custom-select-panel]');
    if (!trigger || !panel) return;

    if (activeSelect && activeSelect !== container) {
      closeSelect(activeSelect);
    }

    container.classList.add('is-open');
    trigger.setAttribute('aria-expanded', 'true');
    panel.hidden = false;
    activeSelect = container;

    const selectedOption = panel.querySelector('.custom-select__option.is-selected');
    const fallbackOption = panel.querySelector('.custom-select__option');
    const target = selectedOption || fallbackOption;

    if (target instanceof HTMLElement) {
      target.focus();
    }
  };

  const syncSelectUi = (container) => {
    const native = container.querySelector('[data-custom-select-native]');
    const value = container.querySelector('[data-custom-select-value]');
    const optionButtons = Array.from(container.querySelectorAll('[data-custom-select-option]'));

    if (!native || !value) return;

    const selectedOption = native.options[native.selectedIndex] || native.options[0];
    const selectedText = selectedOption?.textContent?.trim() || 'Selecione um servico';
    const isPlaceholder = !native.value;

    value.textContent = selectedText;
    value.classList.toggle('is-placeholder', isPlaceholder);

    optionButtons.forEach((button) => {
      const isSelected = button.getAttribute('data-value') === native.value;
      button.classList.toggle('is-selected', isSelected);
      button.setAttribute('aria-selected', String(isSelected));
      button.tabIndex = isSelected ? 0 : -1;
    });
  };

  selects.forEach((container, index) => {
    const native = container.querySelector('[data-custom-select-native]');
    const trigger = container.querySelector('[data-custom-select-trigger]');
    const panel = container.querySelector('[data-custom-select-panel]');
    const value = container.querySelector('[data-custom-select-value]');

    if (!native || !trigger || !panel || !value) return;

    const listboxId = `${native.id || `custom-select-${index}`}-listbox`;
    trigger.setAttribute('aria-controls', listboxId);
    panel.id = listboxId;
    panel.setAttribute('role', 'listbox');

    panel.innerHTML = '';

    Array.from(native.options).forEach((option) => {
      if (!option.value) return;

      const optionButton = document.createElement('button');
      optionButton.type = 'button';
      optionButton.className = 'custom-select__option';
      optionButton.textContent = option.textContent;
      optionButton.setAttribute('role', 'option');
      optionButton.setAttribute('data-custom-select-option', '');
      optionButton.setAttribute('data-value', option.value);
      optionButton.tabIndex = -1;

      optionButton.addEventListener('click', () => {
        native.value = option.value;
        native.dispatchEvent(new Event('change', { bubbles: true }));
        closeSelect(container);
        trigger.focus();
      });

      optionButton.addEventListener('keydown', (event) => {
        const options = Array.from(panel.querySelectorAll('[data-custom-select-option]'));
        const currentIndex = options.findIndex((item) => item === optionButton);

        if (event.key === 'Escape') {
          event.preventDefault();
          closeSelect(container);
          trigger.focus();
          return;
        }

        if (event.key === 'Tab') {
          closeSelect(container);
          return;
        }

        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          optionButton.click();
          return;
        }

        if (!['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) return;

        event.preventDefault();

        let nextIndex = currentIndex;

        if (event.key === 'Home') nextIndex = 0;
        if (event.key === 'End') nextIndex = options.length - 1;
        if (event.key === 'ArrowDown') nextIndex = (currentIndex + 1) % options.length;
        if (event.key === 'ArrowUp') nextIndex = (currentIndex - 1 + options.length) % options.length;

        const nextOption = options[nextIndex];
        if (nextOption instanceof HTMLElement) {
          nextOption.focus();
        }
      });

      panel.appendChild(optionButton);
    });

    syncSelectUi(container);
    closeSelect(container);

    trigger.addEventListener('click', () => {
      if (container.classList.contains('is-open')) {
        closeSelect(container);
        return;
      }

      openSelect(container);
    });

    trigger.addEventListener('keydown', (event) => {
      if (!['ArrowDown', 'ArrowUp', 'Enter', ' '].includes(event.key)) return;

      event.preventDefault();
      openSelect(container);
    });

    native.addEventListener('change', () => {
      syncSelectUi(container);
    });
  });

  document.addEventListener('click', (event) => {
    if (!(event.target instanceof Node)) return;

    selects.forEach((container) => {
      if (container.contains(event.target)) return;
      closeSelect(container);
    });
  });

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape' || !activeSelect) return;

    const trigger = activeSelect.querySelector('[data-custom-select-trigger]');
    closeSelect(activeSelect);

    if (trigger instanceof HTMLElement) {
      trigger.focus();
    }
  });
};

const initPasswordToggles = () => {
  const fields = Array.from(document.querySelectorAll('[data-password-toggle]'));
  if (!fields.length) return;

  const applyState = (input, trigger, visible) => {
    input.type = visible ? 'text' : 'password';
    trigger.setAttribute('aria-pressed', String(visible));
    trigger.setAttribute('aria-label', visible ? 'Ocultar senha' : 'Mostrar senha');
    trigger.classList.toggle('is-visible', visible);
  };

  fields.forEach((field) => {
    const input = field.querySelector('[data-password-input]');
    const trigger = field.querySelector('[data-password-trigger]');
    if (!(input instanceof HTMLInputElement) || !(trigger instanceof HTMLButtonElement)) return;

    applyState(input, trigger, false);

    trigger.addEventListener('click', () => {
      const isVisible = input.type === 'text';
      applyState(input, trigger, !isVisible);
      input.focus({ preventScroll: true });
    });
  });
};

const initFlashAlerts = () => {
  const alerts = Array.from(document.querySelectorAll('[data-flash-alert]'));
  if (!alerts.length) return;

  const closeAlert = (alert) => {
    if (alert.dataset.flashClosed === 'true') return;

    alert.dataset.flashClosed = 'true';
    alert.classList.add('is-closing');

    window.setTimeout(() => {
      alert.classList.add('is-hidden');
      alert.setAttribute('hidden', 'hidden');
    }, 220);
  };

  alerts.forEach((alert) => {
    const timeout = Number(alert.getAttribute('data-flash-timeout') || 5000);
    const closeTrigger = alert.querySelector('[data-flash-close]');

    if (closeTrigger instanceof HTMLButtonElement) {
      closeTrigger.addEventListener('click', () => closeAlert(alert));
    }

    if (timeout > 0) {
      window.setTimeout(() => closeAlert(alert), timeout);
    }
  });
};

const initZipCodeLookup = () => {
  const sections = Array.from(document.querySelectorAll('[data-cep-lookup]'));
  if (!sections.length) return;

  const formatZipCode = (value) => {
    const digits = value.replace(/\D/g, '').slice(0, 8);
    if (digits.length <= 5) return digits;
    return `${digits.slice(0, 5)}-${digits.slice(5)}`;
  };

  sections.forEach((section) => {
    const zipInput = section.parentElement?.querySelector('[data-cep-input]');
    const streetInput = section.parentElement?.querySelector('[data-cep-street]');
    const districtInput = section.parentElement?.querySelector('[data-cep-district]');
    const cityInput = section.parentElement?.querySelector('[data-cep-city]');
    const stateInput = section.parentElement?.querySelector('[data-cep-state]');
    const feedback = section.parentElement?.querySelector('[data-cep-feedback]');
    const geocodingStatus = section.parentElement?.querySelector('[data-geocoding-status]');

    if (!(zipInput instanceof HTMLInputElement)) return;
    if (!(streetInput instanceof HTMLInputElement)) return;
    if (!(districtInput instanceof HTMLInputElement)) return;
    if (!(cityInput instanceof HTMLInputElement)) return;
    if (!(stateInput instanceof HTMLInputElement)) return;
    if (!(feedback instanceof HTMLElement)) return;

    let lastZipSearched = '';

    const setFeedback = (message, state) => {
      feedback.textContent = message;
      feedback.classList.remove('is-loading', 'is-success', 'is-error');
      if (state) {
        feedback.classList.add(state);
      }
    };

    const setGeocodingStatus = (value) => {
      if (geocodingStatus instanceof HTMLElement) {
        geocodingStatus.textContent = value.toUpperCase();
      }
    };

    const fillAddress = (payload) => {
      streetInput.value = payload.logradouro || '';
      districtInput.value = payload.bairro || '';
      cityInput.value = payload.localidade || '';
      stateInput.value = payload.uf || '';
    };

    const lookupZipCode = async () => {
      const digits = zipInput.value.replace(/\D/g, '');

      if (digits.length === 0) {
        setFeedback('Digite um CEP valido para preencher rua, bairro, cidade e UF.', '');
        setGeocodingStatus('not_requested');
        return;
      }

      if (digits.length < 8 || digits === lastZipSearched) {
        return;
      }

      lastZipSearched = digits;
      setFeedback('Buscando endereco pelo CEP...', 'is-loading');

      try {
        const response = await fetch(`https://viacep.com.br/ws/${digits}/json/`, {
          headers: {
            Accept: 'application/json',
          },
        });

        if (!response.ok) {
          throw new Error('Falha na consulta do CEP.');
        }

        const payload = await response.json();

        if (payload.erro) {
          throw new Error('CEP nao encontrado.');
        }

        fillAddress(payload);
        setFeedback('Endereco preenchido automaticamente. Voce pode ajustar os campos se precisar.', 'is-success');
        setGeocodingStatus('address_loaded');
      } catch (error) {
        setFeedback('Nao foi possivel localizar o CEP. Preencha o endereco manualmente.', 'is-error');
        setGeocodingStatus('pending');
      }
    };

    zipInput.value = formatZipCode(zipInput.value);

    zipInput.addEventListener('input', () => {
      zipInput.value = formatZipCode(zipInput.value);
      const digits = zipInput.value.replace(/\D/g, '');

      if (digits.length < 8) {
        lastZipSearched = '';
        setFeedback('Digite um CEP valido para preencher rua, bairro, cidade e UF.', '');
      }
    });

    zipInput.addEventListener('blur', () => {
      lookupZipCode();
    });
  });
};

const initArcaneAccents = () => {
  const targets = Array.from(
    document.querySelectorAll('.service-card, .mission-card, .hosting-box, .system-card, .quest-card, .faq-item'),
  );

  if (!targets.length) return;

  targets.forEach((target) => {
    const sparkX = `${12 + Math.random() * 74}%`;
    const sparkY = `${10 + Math.random() * 68}%`;
    const sparkSize = `${8 + Math.random() * 8}px`;
    const sparkDelay = `${(Math.random() * 2.6).toFixed(2)}s`;
    const sparkDuration = `${(4 + Math.random() * 2.8).toFixed(2)}s`;

    target.style.setProperty('--spark-x', sparkX);
    target.style.setProperty('--spark-y', sparkY);
    target.style.setProperty('--spark-size', sparkSize);
    target.style.setProperty('--spark-delay', sparkDelay);
    target.style.setProperty('--spark-duration', sparkDuration);

    if (target.querySelector('.arcane-orb')) return;

    const orbCount = target.classList.contains('quest-card') ? 2 : 1;

    for (let index = 0; index < orbCount; index += 1) {
      const orb = document.createElement('span');
      orb.className = `arcane-orb${index > 0 ? ' arcane-orb--alt' : ''}`;

      orb.style.setProperty('--orb-x', `${8 + Math.random() * 80}%`);
      orb.style.setProperty('--orb-y', `${8 + Math.random() * 76}%`);
      orb.style.setProperty('--orb-size', `${7 + Math.random() * 10}px`);
      orb.style.setProperty('--orb-delay', `${(Math.random() * 3.2).toFixed(2)}s`);
      orb.style.setProperty('--orb-duration', `${(6.8 + Math.random() * 4.2).toFixed(2)}s`);

      target.appendChild(orb);
    }
  });
};

const initErrorEasterEggs = () => {
  const shell = document.querySelector('[data-error-shell]');
  if (!shell) return;

  const triggers = Array.from(shell.querySelectorAll('[data-error-egg-trigger]'));
  if (!triggers.length) return;

  const closeAll = () => {
    triggers.forEach((trigger) => {
      if (!(trigger instanceof HTMLElement)) return;
      const panelId = trigger.getAttribute('data-error-egg-target');
      const panel = panelId ? document.getElementById(panelId) : null;
      trigger.setAttribute('aria-expanded', 'false');
      if (panel) panel.hidden = true;
    });
  };

  triggers.forEach((trigger) => {
    trigger.addEventListener('click', () => {
      const panelId = trigger.getAttribute('data-error-egg-target');
      const panel = panelId ? document.getElementById(panelId) : null;
      if (!panel) return;

      const isOpen = trigger.getAttribute('aria-expanded') === 'true';
      closeAll();

      if (!isOpen) {
        trigger.setAttribute('aria-expanded', 'true');
        panel.hidden = false;
      }
    });
  });

  const shyEggs = Array.from(shell.querySelectorAll('[data-error-egg-shy]'));

  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  const cubicBezierPoint = (p0, p1, p2, p3, t) => {
    const u = 1 - t;
    const tt = t * t;
    const uu = u * u;
    const uuu = uu * u;
    const ttt = tt * t;

    return {
      x: (uuu * p0.x) + (3 * uu * t * p1.x) + (3 * u * tt * p2.x) + (ttt * p3.x),
      y: (uuu * p0.y) + (3 * uu * t * p1.y) + (3 * u * tt * p2.y) + (ttt * p3.y),
    };
  };

  const animateBezier = (egg, controls, duration, mode) => new Promise((resolve) => {
    const startTime = performance.now();

    const step = (now) => {
      const elapsed = now - startTime;
      const t = Math.min(elapsed / duration, 1);
      const eased = (t < 0.5)
        ? 4 * t * t * t
        : 1 - ((-2 * t + 2) ** 3) / 2;

      const point = cubicBezierPoint(controls.p0, controls.p1, controls.p2, controls.p3, eased);

      if (mode === 'flee') {
        const scale = 1 - (0.28 * eased);
        const rotate = -8 * eased;
        egg.style.opacity = String(1 - (0.96 * eased));
        egg.style.transform = `translate3d(${point.x}px, ${point.y}px, 0) scale(${scale}) rotate(${rotate}deg)`;
      } else {
        const back = 1 - eased;
        const scale = 1 - (0.28 * back) + (0.04 * Math.sin(eased * Math.PI));
        const rotate = -8 * back;
        egg.style.opacity = String(Math.min(1, 0.1 + (0.9 * eased)));
        egg.style.transform = `translate3d(${point.x}px, ${point.y}px, 0) scale(${scale}) rotate(${rotate}deg)`;
      }

      if (t < 1) {
        window.requestAnimationFrame(step);
        return;
      }

      resolve();
    };

    window.requestAnimationFrame(step);
  });

  const runShyEscape = (egg) => {
    if (reduceMotion) {
      egg.classList.add('is-hidden');
      window.setTimeout(() => {
        egg.classList.remove('is-hidden');
      }, 1200);
      return;
    }

    const fleeDuration = 860;
    const returnDuration = 560;
    const hideDuration = 2300;

    const fleeRoutes = (() => {
      if (egg.classList.contains('error-egg--north')) {
        return [
          { x: -108, y: 34, c1x: -30, c1y: -18, c2x: -80, c2y: 12 },
          { x: 94, y: 38, c1x: 34, c1y: -16, c2x: 76, c2y: 14 },
          { x: -76, y: 62, c1x: -26, c1y: -10, c2x: -66, c2y: 32 },
        ];
      }

      if (egg.classList.contains('error-egg--west')) {
        return [
          { x: 104, y: -28, c1x: 24, c1y: -18, c2x: 84, c2y: -28 },
          { x: 116, y: 24, c1x: 22, c1y: 8, c2x: 84, c2y: 26 },
          { x: 88, y: -50, c1x: 20, c1y: -16, c2x: 64, c2y: -58 },
        ];
      }

      if (egg.classList.contains('error-egg--east')) {
        return [
          { x: -106, y: -22, c1x: -26, c1y: -16, c2x: -80, c2y: -26 },
          { x: -96, y: 26, c1x: -24, c1y: 8, c2x: -78, c2y: 24 },
          { x: -120, y: -44, c1x: -28, c1y: -12, c2x: -92, c2y: -52 },
        ];
      }

      return [
        { x: -90, y: 24, c1x: -28, c1y: -12, c2x: -66, c2y: 16 },
        { x: 90, y: -24, c1x: 28, c1y: -12, c2x: 66, c2y: -16 },
      ];
    })();

    const routeIndex = Number(egg.getAttribute('data-flee-route-index') || 0);
    const route = fleeRoutes[routeIndex % fleeRoutes.length];
    egg.setAttribute('data-flee-route-index', String(routeIndex + 1));
    egg.classList.remove('is-armed');
    egg.classList.add('is-fleeing');

    const fleeControls = {
      p0: { x: 0, y: 0 },
      p1: { x: route.c1x, y: route.c1y },
      p2: { x: route.c2x, y: route.c2y },
      p3: { x: route.x, y: route.y },
    };

    const returnControls = {
      p0: { x: route.x, y: route.y },
      p1: { x: route.c2x * 0.32, y: route.c2y * 0.32 },
      p2: { x: route.c1x * 0.18, y: route.c1y * 0.18 },
      p3: { x: 0, y: 0 },
    };

    animateBezier(egg, fleeControls, fleeDuration, 'flee')
      .then(() => {
        egg.classList.remove('is-fleeing');
        egg.classList.add('is-hidden');
        egg.style.transform = `translate3d(${route.x}px, ${route.y}px, 0) scale(0.72) rotate(-8deg)`;
        egg.style.opacity = '0';

        return new Promise((resolve) => {
          window.setTimeout(resolve, hideDuration);
        });
      })
      .then(() => {
        egg.classList.remove('is-hidden');
        egg.classList.add('is-returning');
        return animateBezier(egg, returnControls, returnDuration, 'return');
      })
      .then(() => {
        egg.classList.remove('is-returning');
        egg.style.opacity = '';
        egg.style.transform = '';
      });
  };

  shyEggs.forEach((egg) => {
    const trigger = egg.querySelector('[data-error-egg-trigger]');
    if (!(trigger instanceof HTMLButtonElement)) return;

    const canRunShyMode = () => window.matchMedia('(min-width: 1281px)').matches;

    let holdTimer = null;
    let armed = false;
    let suppressClick = false;

    const clearHold = () => {
      if (holdTimer) {
        window.clearTimeout(holdTimer);
        holdTimer = null;
      }
      egg.classList.remove('is-pressing');
      if (!armed) {
        egg.classList.remove('is-armed');
      }
    };

    trigger.addEventListener('pointerdown', (event) => {
      if (!canRunShyMode()) return;
      if (event.button !== 0) return;
      armed = false;
      egg.classList.add('is-pressing');

      holdTimer = window.setTimeout(() => {
        armed = true;
        egg.classList.add('is-armed');
      }, 430);
    });

    trigger.addEventListener('pointerup', () => {
      if (!canRunShyMode()) return;
      clearHold();

      if (!armed) return;

      suppressClick = true;
      closeAll();
      runShyEscape(egg);
      armed = false;

      window.setTimeout(() => {
        suppressClick = false;
      }, 180);
    });

    trigger.addEventListener('pointerleave', clearHold);
    trigger.addEventListener('pointercancel', clearHold);

    trigger.addEventListener('click', (event) => {
      if (!suppressClick) return;
      event.preventDefault();
      event.stopPropagation();
    });
  });

  document.addEventListener('click', (event) => {
    if (!(event.target instanceof Node)) return;
    if (triggers.some((trigger) => trigger.contains(event.target))) return;
    if ((event.target instanceof HTMLElement) && event.target.closest('.error-egg__panel')) return;
    closeAll();
  });

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    closeAll();
  });
};

document.addEventListener('DOMContentLoaded', () => {
  initMobileMenu();
  initSmoothScroll();
  initModals();
  initPortfolioFilter();
  initQuestReveal();
  initFaqAccordion();
  initPhoneMask();
  initSubmitLocks();
  initCustomSelects();
  initPasswordToggles();
  initFlashAlerts();
  initZipCodeLookup();
  initErrorEasterEggs();
  initArcaneAccents();
});
