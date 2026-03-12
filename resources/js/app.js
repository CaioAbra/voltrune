import './bootstrap';

document.documentElement.classList.add('js');

const body = document.body;

const parseLocalizedNumber = (rawValue) => {
  if (rawValue === null || rawValue === undefined) return null;

  const normalized = String(rawValue)
    .trim()
    .replace(/\s+/g, '')
    .replace(/[R$\u00A0]/g, '');

  if (normalized === '') return null;

  const commaIndex = normalized.lastIndexOf(',');
  const dotIndex = normalized.lastIndexOf('.');
  const decimalIndex = Math.max(commaIndex, dotIndex);

  if (decimalIndex >= 0) {
    const integerPart = normalized.slice(0, decimalIndex).replace(/[^\d-]/g, '');
    const decimalPart = normalized.slice(decimalIndex + 1).replace(/\D/g, '');
    const rebuilt = `${integerPart || '0'}.${decimalPart}`;
    const parsed = Number(rebuilt);
    return Number.isFinite(parsed) ? parsed : null;
  }

  const parsed = Number(normalized.replace(/[^\d-]/g, ''));
  return Number.isFinite(parsed) ? parsed : null;
};

const formatCurrencyBRL = (value) => new Intl.NumberFormat('pt-BR', {
  style: 'currency',
  currency: 'BRL',
  minimumFractionDigits: 2,
  maximumFractionDigits: 2,
}).format(value);

const formatDecimalPtBr = (value) => value.toFixed(2).replace('.', ',');

const initNumberSteppers = () => {
  const inputs = Array.from(document.querySelectorAll('input.hub-auth-input[type="number"]'));
  if (!inputs.length) return;

  inputs.forEach((input) => {
    if (!(input instanceof HTMLInputElement)) return;
    if (input.closest('.hub-number-field')) return;

    const wrapper = document.createElement('div');
    wrapper.className = 'hub-number-field';

    const upButton = document.createElement('button');
    upButton.type = 'button';
    upButton.className = 'hub-number-stepper hub-number-stepper--up';
    upButton.setAttribute('aria-label', 'Aumentar valor');
    upButton.innerHTML = '<span aria-hidden="true">+</span>';

    const downButton = document.createElement('button');
    downButton.type = 'button';
    downButton.className = 'hub-number-stepper hub-number-stepper--down';
    downButton.setAttribute('aria-label', 'Diminuir valor');
    downButton.innerHTML = '<span aria-hidden="true">-</span>';

    const parent = input.parentNode;
    if (!parent) return;

    parent.insertBefore(wrapper, input);
    wrapper.appendChild(input);
    wrapper.appendChild(upButton);
    wrapper.appendChild(downButton);

    const syncDisabledState = () => {
      const isDisabled = input.disabled;
      upButton.disabled = isDisabled;
      downButton.disabled = isDisabled;
    };

    const dispatchValueEvents = () => {
      input.dispatchEvent(new Event('input', { bubbles: true }));
      input.dispatchEvent(new Event('change', { bubbles: true }));
    };

    upButton.addEventListener('click', () => {
      input.stepUp();
      dispatchValueEvents();
      input.focus();
    });

    downButton.addEventListener('click', () => {
      input.stepDown();
      dispatchValueEvents();
      input.focus();
    });

    syncDisabledState();
  });
};

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

const initCurrencyInputs = () => {
  const inputs = Array.from(document.querySelectorAll('[data-currency-brl]'));
  if (!inputs.length) return;

  const formatInput = (input) => {
    if (!(input instanceof HTMLInputElement)) return;
    const parsed = parseLocalizedNumber(input.value);
    input.value = parsed === null ? '' : formatCurrencyBRL(parsed);
  };

  const normalizeInput = (input) => {
    if (!(input instanceof HTMLInputElement)) return;
    const parsed = parseLocalizedNumber(input.value);
    input.value = parsed === null ? '' : String(parsed.toFixed(2));
  };

  inputs.forEach((input) => {
    if (!(input instanceof HTMLInputElement)) return;

    formatInput(input);

    input.addEventListener('focus', () => {
      const parsed = parseLocalizedNumber(input.value);
      input.value = parsed === null ? '' : formatDecimalPtBr(parsed);
    });

    input.addEventListener('blur', () => {
      formatInput(input);
    });

    const form = input.closest('form');
    if (!form || form.dataset.currencyNormalized === 'true') return;

    form.dataset.currencyNormalized = 'true';
    form.addEventListener('submit', () => {
      form.querySelectorAll('[data-currency-brl]').forEach((field) => {
        normalizeInput(field);
      });
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

  const geocodingStatusLabel = (value) => {
    switch (String(value || '').trim().toLowerCase()) {
      case 'ready':
        return 'Geolocalizacao pronta';
      case 'not_found':
        return 'Localizacao nao encontrada';
      case 'address_loaded':
        return 'Endereco identificado, refinando localizacao';
      case 'not_requested':
        return 'Aguardando CEP';
      default:
        return 'Buscando geolocalizacao do endereco...';
    }
  };

  sections.forEach((section) => {
    const zipInput = section.parentElement?.querySelector('[data-cep-input]');
    const streetInput = section.parentElement?.querySelector('[data-cep-street]');
    const districtInput = section.parentElement?.querySelector('[data-cep-district]');
    const cityInput = section.parentElement?.querySelector('[data-cep-city]');
    const stateInput = section.parentElement?.querySelector('[data-cep-state]');
    const feedback = section.parentElement?.querySelector('[data-cep-feedback]');
    const geocodingStatusElements = Array.from(section.parentElement?.querySelectorAll('[data-geocoding-status]') || []);

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
      geocodingStatusElements.forEach((element) => {
        if (!(element instanceof HTMLElement)) return;
        element.textContent = geocodingStatusLabel(value);
      });
    };

    const fillAddress = (payload) => {
      streetInput.value = payload.logradouro || '';
      districtInput.value = payload.bairro || '';
      cityInput.value = payload.localidade || '';
      stateInput.value = payload.uf || '';

      cityInput.dispatchEvent(new Event('input', { bubbles: true }));
      stateInput.dispatchEvent(new Event('input', { bubbles: true }));
      cityInput.dispatchEvent(new Event('change', { bubbles: true }));
      stateInput.dispatchEvent(new Event('change', { bubbles: true }));
      section.dispatchEvent(new CustomEvent('solar:address-autofilled', { bubbles: true }));
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
    let zipLookupTimeout = null;

    zipInput.addEventListener('input', () => {
      zipInput.value = formatZipCode(zipInput.value);
      const digits = zipInput.value.replace(/\D/g, '');

      if (digits.length < 8) {
        lastZipSearched = '';
        setFeedback('Digite um CEP valido para preencher rua, bairro, cidade e UF.', '');
        setGeocodingStatus('not_requested');
        return;
      }

      if (zipLookupTimeout !== null) {
        window.clearTimeout(zipLookupTimeout);
      }

      // Nao esperar blur: quando CEP fica completo, inicia busca rapidamente.
      zipLookupTimeout = window.setTimeout(() => {
        lookupZipCode();
      }, 180);
    });

    zipInput.addEventListener('blur', () => {
      lookupZipCode();
    });
  });
};

const initSolarSizingForm = () => {
  const sections = Array.from(document.querySelectorAll('[data-sizing-form]'));
  if (!sections.length) return;

  const roundTo = (value, decimals = 2) => {
    const factor = 10 ** decimals;
    return Math.round(value * factor) / factor;
  };

  sections.forEach((section) => {
    const root = section.closest('[data-solar-project-form]');
    if (!root) return;

    const monthlyInput = root.querySelector('[data-sizing-monthly]');
    const modulePowerInput = root.querySelector('[data-sizing-module-power]');
    const systemPowerInput = root.querySelector('[data-sizing-system-power]');
    const moduleQuantityInput = root.querySelector('[data-sizing-module-quantity]');
    const generationInput = root.querySelector('[data-sizing-generation]');
    const inverterInput = root.querySelector('[data-sizing-inverter]');
    const energyBillInput = root.querySelector('[data-pricing-energy-bill]');
    const note = section.querySelector('[data-sizing-note]');
    const systemPreview = section.querySelector('[data-sizing-preview="system-power"]');
    const modulePreview = section.querySelector('[data-sizing-preview="module-power"]');
    const moduleCountPreview = section.querySelector('[data-sizing-preview="modules"]');
    const factorPreview = section.querySelector('[data-sizing-preview="factor"]');
    const generationPreview = section.querySelector('[data-sizing-preview="generation"]');
    const areaPreview = section.querySelector('[data-sizing-preview="area"]');
    const pricingRatePreview = root.querySelector('[data-pricing-preview="rate"]');
    const pricingTotalPreview = root.querySelector('[data-pricing-preview="total"]');
    const pricingSavingsPreview = root.querySelector('[data-pricing-preview="savings"]');
    const pricingMarginPreview = root.querySelector('[data-pricing-preview="margin"]');
    const pricingSourcePreview = root.querySelector('[data-pricing-preview="source"]');
    const pricingKitCostPreview = root.querySelector('[data-pricing-preview="kit-cost"]');
    const pricingGrossProfitPreview = root.querySelector('[data-pricing-preview="gross-profit"]');
    const pricingRoiPreview = root.querySelector('[data-pricing-preview="roi"]');
    const pricingNote = root.querySelector('[data-pricing-note]');
    const financialMonthlyPreview = root.querySelector('[data-financial-preview="monthly"]');
    const financialAnnualPreview = root.querySelector('[data-financial-preview="annual"]');
    const financialLifetimePreview = root.querySelector('[data-financial-preview="lifetime"]');
    const financialPaybackPreview = root.querySelector('[data-financial-preview="payback"]');
    const financialRoiPreview = root.querySelector('[data-financial-preview="roi"]');
    const financialNote = root.querySelector('[data-financial-note]');
    const suggestedPriceInput = root.querySelector('[data-pricing-suggested-price]');
    const summaryName = root.querySelector('[data-project-summary-name]');
    const summaryCustomer = root.querySelector('[data-project-summary="customer"]');
    const summaryLocation = root.querySelector('[data-project-summary="location"]');
    const summaryStatus = root.querySelector('[data-project-summary="status"]');
    const summaryConsumption = root.querySelector('[data-project-summary="consumption"]');
    const summaryPower = root.querySelector('[data-project-summary="power"]');
    const summaryModules = root.querySelector('[data-project-summary="modules"]');
    const summaryGeneration = root.querySelector('[data-project-summary="generation"]');
    const summaryPrice = root.querySelector('[data-project-summary="price"]');
    const summarySavings = root.querySelector('[data-project-summary="savings"]');
    const summaryAnnualSavings = root.querySelector('[data-project-summary="annual-savings"]');
    const summaryLifetimeSavings = root.querySelector('[data-project-summary="lifetime-savings"]');
    const customerSelect = root.querySelector('[data-project-customer-select]');
    const projectNameInput = root.querySelector('[data-project-name]');
    const projectStatusInput = root.querySelector('[data-project-status]');
    const cityInput = root.querySelector('[data-project-city]');
    const stateInput = root.querySelector('[data-project-state]');
    const zipInput = root.querySelector('[data-cep-input]');
    const streetInput = root.querySelector('[data-cep-street]');
    const numberInput = root.querySelector('#number');
    const districtInput = root.querySelector('[data-cep-district]');
    const solarFactorDisplay = root.querySelector('[data-solar-factor-display]');
    const solarRadiationDisplay = root.querySelector('[data-solar-radiation-display]');
    const solarFactorSourceDisplay = root.querySelector('[data-solar-factor-source-display]');
    const solarFactorMessage = root.querySelector('[data-solar-factor-message]');
    const technicalPanel = root.querySelector('.solar-technical-panel');
    const technicalSignals = Array.from(root.querySelectorAll('.solar-technical-panel__signal'));
    const geocodingStatusDisplays = Array.from(root.querySelectorAll('[data-geocoding-status]'));
    const geocodingPrecisionDisplays = Array.from(root.querySelectorAll('[data-geocoding-precision-display]'));
    const automationSyncStatus = root.querySelector('[data-automation-sync-status]');
    const automationPreviewUrl = root.getAttribute('data-automation-preview-url') || '';
    const projectId = root.getAttribute('data-project-id') || '';
    const effectivePricingPerKwp = Number(
      (section.getAttribute('data-pricing-effective-per-kwp')
        || root.getAttribute('data-pricing-effective-per-kwp')
        || root.getAttribute('data-pricing-default-per-kwp')
        || '0').replace(',', '.'),
    );
    const defaultPricingPerKwp = Number(
      (root.getAttribute('data-pricing-default-per-kwp') || '4200').replace(',', '.'),
    );
    let pricingPerKwp = effectivePricingPerKwp;
    let pricingSource = root.getAttribute('data-pricing-source') || 'fallback';
    const regionalPriceLookup = JSON.parse(root.getAttribute('data-regional-price-lookup') || '{}');
    const marginPercent = Number(root.getAttribute('data-margin-percent')?.replace(',', '.') || 0);
    const defaultInverterModel = root.getAttribute('data-default-inverter-model') || '';
    const residualMinimumCost = Number(root.getAttribute('data-residual-minimum-cost')?.replace(',', '.') || 70);
    let solarFactorUsed = Number(root.getAttribute('data-solar-factor-used')?.replace(',', '.') || 130);
    let solarFactorSource = root.getAttribute('data-solar-factor-source') || 'fallback';
    let automationPreviewTimeout = null;
    let automationPreviewAbortController = null;
    let lastAutomationFingerprint = '';
    let pendingAutomationFingerprint = '';

    if (!(monthlyInput instanceof HTMLInputElement)) return;
    if (!(modulePowerInput instanceof HTMLInputElement)) return;
    if (!(systemPowerInput instanceof HTMLInputElement)) return;
    if (!(moduleQuantityInput instanceof HTMLInputElement)) return;
    if (!(generationInput instanceof HTMLInputElement)) return;
    if (!(suggestedPriceInput instanceof HTMLInputElement)) return;
    if (!(inverterInput instanceof HTMLInputElement)) return;

    const readNumber = (input) => {
      if (!(input instanceof HTMLInputElement)) return null;
      if (input.value.trim() === '') return null;
      const parsed = parseLocalizedNumber(input.value);
      return Number.isFinite(parsed) ? parsed : null;
    };
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const counterAnimationMap = new WeakMap();
    const debounce = (callback, delay = 380) => {
      let timeoutId = null;

      return (...args) => {
        if (timeoutId !== null) {
          window.clearTimeout(timeoutId);
        }

        timeoutId = window.setTimeout(() => {
          callback(...args);
        }, delay);
      };
    };

    const formatNumber = (value, suffix = '', decimals = 2) => `${value.toFixed(decimals).replace('.', ',')}${suffix}`;
    const writeNumber = (input, value, decimals = 2) => {
      if (!(input instanceof HTMLInputElement) || value === null || !Number.isFinite(value)) return;
      input.value = decimals === 0 ? String(Math.round(value)) : value.toFixed(decimals);
    };

    const formatCurrency = (value) => `R$ ${value.toFixed(2).replace('.', ',')}`;
    const formatCurrencyMonthly = (value) => `${formatCurrency(value)}/mes`;
    const animateNumber = (element, targetValue, formatter, options = {}) => {
      if (!(element instanceof HTMLElement)) return;
      if (!Number.isFinite(targetValue)) return;

      const {
        duration = 360,
        highlightClass = 'is-counting',
      } = options;
      const container = element.closest('.solar-system-hero-metric');
      const activeClassTarget = container || element;
      const previousState = counterAnimationMap.get(element);

      if (previousState?.rafId) {
        window.cancelAnimationFrame(previousState.rafId);
      }

      const startingValue = previousState && Number.isFinite(previousState.value)
        ? previousState.value
        : targetValue;
      const diff = targetValue - startingValue;

      if (prefersReducedMotion || Math.abs(diff) < 0.01) {
        element.textContent = formatter(targetValue);
        counterAnimationMap.set(element, { value: targetValue, rafId: null });
        return;
      }

      activeClassTarget.classList.add(highlightClass);
      const startedAt = performance.now();

      const tick = (now) => {
        const elapsed = Math.min((now - startedAt) / duration, 1);
        const eased = 1 - ((1 - elapsed) ** 3);
        const currentValue = startingValue + (diff * eased);
        element.textContent = formatter(currentValue);

        if (elapsed < 1) {
          const rafId = window.requestAnimationFrame(tick);
          counterAnimationMap.set(element, { value: currentValue, rafId });
          return;
        }

        element.textContent = formatter(targetValue);
        counterAnimationMap.set(element, { value: targetValue, rafId: null });
        window.setTimeout(() => {
          activeClassTarget.classList.remove(highlightClass);
        }, 120);
      };

      const rafId = window.requestAnimationFrame(tick);
      counterAnimationMap.set(element, { value: startingValue, rafId });
    };
    const geocodingStatusLabel = (value) => {
      switch (String(value || '').trim().toLowerCase()) {
        case 'ready':
          return 'Geolocalizacao pronta';
        case 'not_found':
          return 'Localizacao nao encontrada';
        case 'address_loaded':
          return 'Endereco identificado, refinando localizacao';
        case 'not_requested':
          return 'Aguardando CEP';
        default:
          return 'Buscando geolocalizacao do endereco...';
      }
    };
    const updateGeocodingProgressByInput = () => {
      const hasCity = cityInput instanceof HTMLInputElement && cityInput.value.trim() !== '';
      const hasState = stateInput instanceof HTMLInputElement && stateInput.value.trim() !== '';
      const hasStreet = streetInput instanceof HTMLInputElement && streetInput.value.trim() !== '';
      const hasNumber = numberInput instanceof HTMLInputElement && numberInput.value.trim() !== '';

      if (hasCity && hasState && hasStreet && hasNumber) {
        geocodingStatusDisplays.forEach((element) => {
          if (element instanceof HTMLElement) {
            element.textContent = 'Refinando localizacao exata do endereco...';
          }
        });
        return;
      }

      if (hasCity && hasState) {
        geocodingStatusDisplays.forEach((element) => {
          if (element instanceof HTMLElement) {
            element.textContent = 'Cidade identificada, buscando coordenadas...';
          }
        });
      }
    };
    const setAutomationSyncStatus = (value, factorSource = solarFactorSource) => {
      if (!(automationSyncStatus instanceof HTMLElement)) return;
      const clearSignalState = () => {
        automationSyncStatus.classList.remove('is-loading', 'is-error');
        technicalSignals.forEach((signal) => signal.classList.remove('is-live'));
      };

      clearSignalState();
      technicalPanel?.classList.toggle('is-syncing', value === 'loading');

      if (value === 'loading') {
        automationSyncStatus.classList.add('is-loading');
        automationSyncStatus.textContent = 'Atualizando';
        return;
      }

      if (value === 'error') {
        automationSyncStatus.classList.add('is-error');
        automationSyncStatus.textContent = 'Fallback';
        return;
      }

      if (factorSource === 'pvgis') {
        automationSyncStatus.textContent = 'Regional';
        technicalSignals.forEach((signal) => signal.classList.add('is-live'));
        return;
      }

      automationSyncStatus.textContent = 'Padrao';
    };
    let currentModulesValue = null;
    const resolveRegionalPricing = () => {
      const currentState = stateInput instanceof HTMLInputElement
        ? stateInput.value.trim().toUpperCase()
        : '';

      if (root.getAttribute('data-pricing-source') === 'company') {
        pricingPerKwp = effectivePricingPerKwp;
        pricingSource = 'company';
        return;
      }

      const regionalPrice = Number(regionalPriceLookup[currentState] || 0);

      if (regionalPrice > 0) {
        pricingPerKwp = regionalPrice;
        pricingSource = 'regional';
      } else {
        pricingPerKwp = defaultPricingPerKwp;
        pricingSource = 'fallback';
      }
    };

    const estimateMonthlySavings = (energyBillValue) => {
      if (!Number.isFinite(energyBillValue) || energyBillValue <= 0) return null;
      return roundTo(Math.max(energyBillValue - residualMinimumCost, 0), 2);
    };
    const estimateArea = (systemPower) => {
      if (Number.isFinite(currentModulesValue) && currentModulesValue > 0) {
        return roundTo(currentModulesValue * 2.3, 2);
      }
      if (!Number.isFinite(systemPower) || systemPower <= 0) return null;
      return roundTo(systemPower * 4.5, 2);
    };
    const estimateKitCost = (suggestedPrice) => {
      if (!Number.isFinite(suggestedPrice) || suggestedPrice <= 0) return null;
      const effectiveMargin = marginPercent > 0 ? marginPercent : 22;
      return roundTo(suggestedPrice / (1 + (effectiveMargin / 100)), 2);
    };
    const estimateGrossProfit = (suggestedPrice, kitCost) => {
      if (!Number.isFinite(suggestedPrice) || suggestedPrice <= 0 || !Number.isFinite(kitCost) || kitCost <= 0) {
        return null;
      }

      return roundTo(suggestedPrice - kitCost, 2);
    };
    const estimatePaybackMonths = (suggestedPrice, monthlySavings) => {
      if (!Number.isFinite(suggestedPrice) || suggestedPrice <= 0 || !Number.isFinite(monthlySavings) || monthlySavings <= 0) {
        return null;
      }

      return Math.ceil(suggestedPrice / monthlySavings);
    };
    const estimateRoi = (suggestedPrice, annualSavings) => {
      if (!Number.isFinite(suggestedPrice) || suggestedPrice <= 0 || !Number.isFinite(annualSavings) || annualSavings <= 0) {
        return null;
      }

      return roundTo((annualSavings / suggestedPrice) * 100, 1);
    };
    const estimateEquivalentSolarRadiationDaily = (solarFactor) => {
      if (!Number.isFinite(solarFactor) || solarFactor <= 0) return null;
      return roundTo(solarFactor / 30, 2);
    };
    const updateGeocodingDisplays = (status, precisionLabel) => {
      geocodingStatusDisplays.forEach((element) => {
        if (element instanceof HTMLElement && status) {
          element.textContent = geocodingStatusLabel(status);
        }
      });

      geocodingPrecisionDisplays.forEach((element) => {
        if (element instanceof HTMLElement && precisionLabel) {
          element.textContent = precisionLabel;
        }
      });
    };
    const applyAutomationPreviewData = (payload) => {
      if (!payload || typeof payload !== 'object') return;

      if (Number.isFinite(Number(payload.solar_factor))) {
        solarFactorUsed = Number(payload.solar_factor);
        root.setAttribute('data-solar-factor-used', String(payload.solar_factor));
      }

      if (typeof payload.solar_factor_source === 'string' && payload.solar_factor_source) {
        solarFactorSource = payload.solar_factor_source;
        root.setAttribute('data-solar-factor-source', payload.solar_factor_source);
      }

      if (Number.isFinite(Number(payload.pricing_per_kwp))) {
        pricingPerKwp = Number(payload.pricing_per_kwp);
      }

      if (typeof payload.pricing_source === 'string' && payload.pricing_source) {
        pricingSource = payload.pricing_source;
      }

      if (typeof payload.geocoding_status === 'string') {
        updateGeocodingDisplays(payload.geocoding_status, payload.geocoding_precision_label || '');
      }

      if (solarFactorMessage instanceof HTMLElement) {
        const message = typeof payload.solar_factor_message === 'string' ? payload.solar_factor_message.trim() : '';
        solarFactorMessage.textContent = message;
        solarFactorMessage.hidden = message === '';
      }

      setAutomationSyncStatus('ok', solarFactorSource);

      if (monthlyInput.value.trim() !== '') {
        applySizing();
      } else {
        updatePreview();
      }
    };
    const buildAutomationFingerprint = () => {
      const zip = zipInput instanceof HTMLInputElement ? zipInput.value.trim() : '';
      const city = cityInput instanceof HTMLInputElement ? cityInput.value.trim().toUpperCase() : '';
      const state = stateInput instanceof HTMLInputElement ? stateInput.value.trim().toUpperCase() : '';
      const street = streetInput instanceof HTMLInputElement ? streetInput.value.trim().toUpperCase() : '';
      const number = numberInput instanceof HTMLInputElement ? numberInput.value.trim().toUpperCase() : '';

      if (!city || !state) return '';

      return [zip, city, state, street, number].join('|');
    };
    const runAutomationPreview = async () => {
      if (!automationPreviewUrl) return;

      const fingerprint = buildAutomationFingerprint();

      if (!fingerprint || fingerprint === lastAutomationFingerprint || fingerprint === pendingAutomationFingerprint) {
        return;
      }

      if (automationPreviewAbortController) {
        automationPreviewAbortController.abort();
      }

      automationPreviewAbortController = new AbortController();
      pendingAutomationFingerprint = fingerprint;
      setAutomationSyncStatus('loading');
      updateGeocodingProgressByInput();

      const params = new URLSearchParams();
      if (projectId) params.set('project_id', projectId);

      [
        ['zip_code', zipInput],
        ['street', streetInput],
        ['number', numberInput],
        ['district', districtInput],
        ['city', cityInput],
        ['state', stateInput],
        ['monthly_consumption_kwh', monthlyInput],
        ['energy_bill_value', energyBillInput],
        ['module_power', modulePowerInput],
        ['module_quantity', moduleQuantityInput],
        ['system_power_kwp', systemPowerInput],
        ['estimated_generation_kwh', generationInput],
        ['inverter_model', inverterInput],
      ].forEach(([key, input]) => {
        if (input instanceof HTMLInputElement && input.value.trim() !== '') {
          params.set(key, input.value.trim());
        }
      });

      try {
        const response = await fetch(`${automationPreviewUrl}?${params.toString()}`, {
          method: 'GET',
          headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
          },
          signal: automationPreviewAbortController.signal,
        });

        if (!response.ok) return;

        const payload = await response.json();
        applyAutomationPreviewData(payload);
        lastAutomationFingerprint = fingerprint;
        pendingAutomationFingerprint = '';
        setAutomationSyncStatus('ok');
      } catch (error) {
        pendingAutomationFingerprint = '';
        if (error?.name !== 'AbortError') {
          setAutomationSyncStatus('error');
          updatePreview();
        }
      }
    };
    const scheduleAutomationPreview = (delay = 140) => {
      if (automationPreviewTimeout) {
        window.clearTimeout(automationPreviewTimeout);
      }

      automationPreviewTimeout = window.setTimeout(() => {
        runAutomationPreview();
      }, delay);
    };

    const updateSummary = () => {
      const currentPower = readNumber(systemPowerInput);
      const currentSuggestedPrice = readNumber(suggestedPriceInput);
      const currentConsumption = readNumber(monthlyInput);
      const currentModules = readNumber(moduleQuantityInput);
      const currentEnergyBill = readNumber(energyBillInput);
      const currentMonthlySavings = estimateMonthlySavings(currentEnergyBill);
      const currentGeneration = readNumber(generationInput);
      const currentAnnualSavings = currentMonthlySavings !== null ? roundTo(currentMonthlySavings * 12, 2) : null;
      const currentLifetimeSavings = currentAnnualSavings !== null ? roundTo(currentAnnualSavings * 25, 2) : null;

      if (summaryName instanceof HTMLElement && projectNameInput instanceof HTMLInputElement) {
        summaryName.textContent = projectNameInput.value.trim() || 'Projeto solar em preparacao';
      }

      if (summaryCustomer instanceof HTMLElement && customerSelect instanceof HTMLSelectElement) {
        summaryCustomer.textContent = customerSelect.value !== ''
          ? customerSelect.options[customerSelect.selectedIndex]?.textContent?.trim() || 'Cliente pendente'
          : 'Cliente pendente';
      }

      if (summaryLocation instanceof HTMLElement && cityInput instanceof HTMLInputElement && stateInput instanceof HTMLInputElement) {
        const locationParts = [cityInput.value.trim(), stateInput.value.trim().toUpperCase()].filter(Boolean);
        summaryLocation.textContent = locationParts.length ? locationParts.join(' / ') : 'Local pendente';
      }

      if (summaryStatus instanceof HTMLElement && projectStatusInput instanceof HTMLSelectElement) {
        summaryStatus.textContent = projectStatusInput.options[projectStatusInput.selectedIndex]?.textContent?.trim() || 'Rascunho';
      }

      if (summaryConsumption instanceof HTMLElement) {
        summaryConsumption.textContent = currentConsumption && currentConsumption > 0
          ? formatNumber(currentConsumption, ' kWh')
          : 'Aguardando consumo';
      }

      if (summaryPower instanceof HTMLElement) {
        summaryPower.textContent = currentPower && currentPower > 0
          ? formatNumber(currentPower, ' kWp')
          : 'Aguardando consumo';
      }

      if (summaryModules instanceof HTMLElement) {
        summaryModules.textContent = currentModules && currentModules > 0
          ? String(Math.round(currentModules))
          : 'Aguardando sistema';
      }

      if (summaryGeneration instanceof HTMLElement) {
        if (currentGeneration && currentGeneration > 0) {
          animateNumber(summaryGeneration, currentGeneration, (value) => formatNumber(value, ' kWh/mes'));
        } else {
          summaryGeneration.textContent = 'Aguardando sistema';
        }
      }

      if (summaryPrice instanceof HTMLElement) {
        if (currentSuggestedPrice && currentSuggestedPrice > 0) {
          animateNumber(summaryPrice, currentSuggestedPrice, (value) => formatCurrency(value));
        } else {
          summaryPrice.textContent = 'Aguardando pre-orcamento';
        }
      }

      if (summarySavings instanceof HTMLElement) {
        if (currentMonthlySavings !== null) {
          animateNumber(summarySavings, currentMonthlySavings, (value) => formatCurrency(value));
        } else {
          summarySavings.textContent = 'Aguardando conta';
        }
      }

      if (summaryAnnualSavings instanceof HTMLElement) {
        summaryAnnualSavings.textContent = currentAnnualSavings !== null
          ? formatCurrency(currentAnnualSavings)
          : 'Aguardando simulacao';
      }

      if (summaryLifetimeSavings instanceof HTMLElement) {
        summaryLifetimeSavings.textContent = currentLifetimeSavings !== null
          ? formatCurrency(currentLifetimeSavings)
          : 'Aguardando simulacao';
      }
    };

    const applyDefaultInverter = () => {
      if (defaultInverterModel && inverterInput.value.trim() === '') {
        inverterInput.value = defaultInverterModel;
      }
    };

    const updateDerivedFieldsFromPower = ({ onlyEmpty = false } = {}) => {
      const currentPower = readNumber(systemPowerInput);
      const currentModulePower = readNumber(modulePowerInput) ?? 550;

      if (currentPower && currentPower > 0 && currentModulePower > 0) {
        if (!onlyEmpty || moduleQuantityInput.value.trim() === '') {
          writeNumber(moduleQuantityInput, Math.ceil((currentPower * 1000) / currentModulePower), 0);
        }

        if (!onlyEmpty || generationInput.value.trim() === '') {
          writeNumber(generationInput, roundTo(currentPower * solarFactorUsed, 2), 2);
        }
      }

      if (pricingPerKwp > 0 && currentPower && currentPower > 0 && (!onlyEmpty || suggestedPriceInput.value.trim() === '')) {
        writeNumber(suggestedPriceInput, roundTo(currentPower * pricingPerKwp, 2), 2);
      }

      applyDefaultInverter();
      updatePreview();
    };

    const updatePreview = () => {
      const currentPower = readNumber(systemPowerInput);
      const currentModulePower = readNumber(modulePowerInput);
      const currentModules = readNumber(moduleQuantityInput);
      const currentGeneration = readNumber(generationInput);
      const currentSuggestedPrice = readNumber(suggestedPriceInput);
      const currentEnergyBill = readNumber(energyBillInput);
      const currentMonthlySavings = estimateMonthlySavings(currentEnergyBill);
      const currentAnnualSavings = currentMonthlySavings !== null ? roundTo(currentMonthlySavings * 12, 2) : null;
      const currentLifetimeSavings = currentAnnualSavings !== null ? roundTo(currentAnnualSavings * 25, 2) : null;
      currentModulesValue = currentModules;
      const currentArea = estimateArea(currentPower);
      const currentKitCost = estimateKitCost(currentSuggestedPrice);
      const currentGrossProfit = estimateGrossProfit(currentSuggestedPrice, currentKitCost);
      const currentPaybackMonths = estimatePaybackMonths(currentSuggestedPrice, currentMonthlySavings);
      const currentRoi = estimateRoi(currentSuggestedPrice, currentAnnualSavings);

      if (systemPreview instanceof HTMLElement) {
        systemPreview.textContent = currentPower && currentPower > 0
          ? formatNumber(currentPower, ' kWp')
          : 'Aguardando consumo';
      }

      if (modulePreview instanceof HTMLElement) {
        modulePreview.textContent = currentModulePower && currentModulePower > 0
          ? `${Math.round(currentModulePower)} W`
          : '550 W';
      }

      if (factorPreview instanceof HTMLElement) {
        factorPreview.textContent = `${formatNumber(solarFactorUsed, '', 2)} kWh/kWp/mes`;
      }

      if (moduleCountPreview instanceof HTMLElement) {
        moduleCountPreview.textContent = currentModules && currentModules > 0
          ? String(Math.round(currentModules))
          : 'Aguardando sistema';
      }

      if (generationPreview instanceof HTMLElement) {
        if (currentGeneration && currentGeneration > 0) {
          animateNumber(generationPreview, currentGeneration, (value) => formatNumber(value, ' kWh'));
        } else {
          generationPreview.textContent = 'Aguardando sistema';
        }
      }

      if (areaPreview instanceof HTMLElement) {
        areaPreview.textContent = currentArea !== null
          ? formatNumber(currentArea, ' m2')
          : 'Aguardando sistema';
      }

      if (solarFactorDisplay instanceof HTMLElement) {
        solarFactorDisplay.textContent = `${formatNumber(solarFactorUsed, '', 2)} kWh/kWp/mes`;
      }

      if (solarRadiationDisplay instanceof HTMLElement) {
        const radiation = estimateEquivalentSolarRadiationDaily(solarFactorUsed);
        solarRadiationDisplay.textContent = radiation !== null
          ? `${formatNumber(radiation, '', 2)} kWh/m2/dia`
          : 'Aguardando fator';
      }

      if (solarFactorSourceDisplay instanceof HTMLElement) {
        solarFactorSourceDisplay.textContent = solarFactorSource === 'pvgis' ? 'PVGIS' : 'PADRAO';
      }

      if (pricingRatePreview instanceof HTMLElement) {
        pricingRatePreview.textContent = pricingPerKwp > 0
          ? formatCurrency(pricingPerKwp)
          : 'Indisponivel';
      }

      if (pricingSourcePreview instanceof HTMLElement) {
        pricingSourcePreview.textContent = pricingSource === 'company'
          ? 'Preco da empresa'
          : pricingSource === 'regional'
            ? 'Media regional'
            : 'Fallback padrao nacional';
      }

      if (pricingKitCostPreview instanceof HTMLElement) {
        pricingKitCostPreview.textContent = currentKitCost !== null
          ? formatCurrency(currentKitCost)
          : 'Aguardando sistema';
      }

      if (pricingGrossProfitPreview instanceof HTMLElement) {
        pricingGrossProfitPreview.textContent = currentGrossProfit !== null
          ? formatCurrency(currentGrossProfit)
          : 'Aguardando sistema';
      }

      if (pricingRoiPreview instanceof HTMLElement) {
        if (currentRoi !== null) {
          animateNumber(pricingRoiPreview, currentRoi, (value) => `${value.toFixed(1).replace('.', ',')}%`);
        } else {
          pricingRoiPreview.textContent = 'Aguardando simulacao';
        }
      }

      if (pricingTotalPreview instanceof HTMLElement) {
        if (currentSuggestedPrice && currentSuggestedPrice > 0) {
          animateNumber(pricingTotalPreview, currentSuggestedPrice, (value) => formatCurrency(value));
        } else {
          pricingTotalPreview.textContent = 'Aguardando dimensionamento';
        }
      }

      if (pricingSavingsPreview instanceof HTMLElement) {
        if (currentMonthlySavings !== null) {
          animateNumber(pricingSavingsPreview, currentMonthlySavings, (value) => formatCurrencyMonthly(value));
        } else {
          pricingSavingsPreview.textContent = 'Informe a conta de energia';
        }
      }

      if (financialMonthlyPreview instanceof HTMLElement) {
        if (currentMonthlySavings !== null) {
          animateNumber(financialMonthlyPreview, currentMonthlySavings, (value) => formatCurrency(value));
        } else {
          financialMonthlyPreview.textContent = 'Informe a conta de energia';
        }
      }

      if (financialAnnualPreview instanceof HTMLElement) {
        financialAnnualPreview.textContent = currentAnnualSavings !== null
          ? formatCurrency(currentAnnualSavings)
          : 'Aguardando simulacao';
      }

      if (financialLifetimePreview instanceof HTMLElement) {
        financialLifetimePreview.textContent = currentLifetimeSavings !== null
          ? formatCurrency(currentLifetimeSavings)
          : 'Aguardando simulacao';
      }

      if (financialPaybackPreview instanceof HTMLElement) {
        financialPaybackPreview.textContent = currentPaybackMonths !== null
          ? `${currentPaybackMonths} meses`
          : 'Aguardando simulacao';
      }

      if (financialRoiPreview instanceof HTMLElement) {
        if (currentRoi !== null) {
          animateNumber(financialRoiPreview, currentRoi, (value) => `${value.toFixed(1).replace('.', ',')}%`);
        } else {
          financialRoiPreview.textContent = 'Aguardando simulacao';
        }
      }

      if (pricingMarginPreview instanceof HTMLElement) {
        pricingMarginPreview.textContent = marginPercent > 0
          ? `${marginPercent.toFixed(2).replace('.', ',')}%`
          : 'Nao configurada';
      }

      if (note instanceof HTMLElement) {
        if (currentPower && currentModules && currentGeneration) {
          note.textContent = `Sugestao ativa com fator ${formatNumber(solarFactorUsed, '', 2)}: ${formatNumber(currentPower, ' kWp')}, ${Math.round(currentModules)} modulos e ${formatNumber(currentGeneration, ' kWh')} estimados.`;
        } else {
          note.textContent = 'Preencha o consumo mensal para gerar a sugestao automatica usando o fator regional salvo no projeto.';
        }
      }

      if (pricingNote instanceof HTMLElement) {
        if (pricingPerKwp <= 0) {
          pricingNote.textContent = 'Preco por kWp indisponivel no momento.';
        } else if (currentSuggestedPrice && currentSuggestedPrice > 0) {
          const savingsSuffix = currentMonthlySavings !== null
            ? ` Economia mensal estimada: ${formatCurrencyMonthly(currentMonthlySavings)}.`
            : '';
          const sourcePrefix = pricingSource === 'company'
            ? `Pre-orcamento ativo com preco proprio (${formatCurrency(pricingPerKwp)}/kWp): `
            : `Preco sugerido baseado em media de mercado. Voce pode ajustar manualmente. Referencia atual: ${formatCurrency(pricingPerKwp)}/kWp. `;
          pricingNote.textContent = `${sourcePrefix}${formatCurrency(currentSuggestedPrice)} com base na potencia atual do sistema.${savingsSuffix}`;
        } else {
          pricingNote.textContent = 'Informe o consumo mensal para gerar o pre-orcamento automatico.';
        }
      }

      if (financialNote instanceof HTMLElement) {
        if (currentMonthlySavings !== null && currentAnnualSavings !== null && currentLifetimeSavings !== null) {
          const paybackSuffix = currentPaybackMonths !== null ? ` Retorno estimado: ${currentPaybackMonths} meses.` : '';
          const roiSuffix = currentRoi !== null ? ` ROI aproximado: ${currentRoi.toFixed(1).replace('.', ',')}% ao ano.` : '';
          financialNote.textContent = `Simulacao automatica ativa: ${formatCurrency(currentMonthlySavings)}/mes, ${formatCurrency(currentAnnualSavings)}/ano e ${formatCurrency(currentLifetimeSavings)} em 25 anos, considerando residual de ${formatCurrency(residualMinimumCost)}.${paybackSuffix}${roiSuffix}`;
        } else {
          financialNote.textContent = `Informe o valor da conta de energia para gerar a simulacao financeira automatica com residual minimo de ${formatCurrency(residualMinimumCost)}.`;
        }
      }

      updateSummary();
    };

    const applySizing = () => {
      const monthlyConsumption = readNumber(monthlyInput);
      const modulePower = readNumber(modulePowerInput) ?? 550;
      resolveRegionalPricing();

      if (monthlyConsumption === null || monthlyConsumption <= 0 || modulePower <= 0 || solarFactorUsed <= 0) {
        updatePreview();
        return;
      }

      const suggestedPower = roundTo(monthlyConsumption / solarFactorUsed, 2);
      const suggestedModules = Math.ceil((suggestedPower * 1000) / modulePower);
      const suggestedGeneration = roundTo(suggestedPower * solarFactorUsed, 2);

      writeNumber(systemPowerInput, suggestedPower, 2);
      writeNumber(moduleQuantityInput, suggestedModules, 0);
      writeNumber(generationInput, suggestedGeneration, 2);

      if (pricingPerKwp > 0) {
        writeNumber(suggestedPriceInput, roundTo(suggestedPower * pricingPerKwp, 2), 2);
      }

      applyDefaultInverter();
      updatePreview();
    };
    const debouncedApplySizing = debounce(() => applySizing(), 180);
    const debouncedUpdateDerivedFromPower = debounce(() => updateDerivedFieldsFromPower(), 140);
    const debouncedPreviewRefresh = debounce(() => updatePreview(), 180);

    applyDefaultInverter();
    resolveRegionalPricing();
    updateDerivedFieldsFromPower({ onlyEmpty: true });
    updatePreview();
    setAutomationSyncStatus('ok', solarFactorSource);
    scheduleAutomationPreview(0);

    monthlyInput.addEventListener('input', debouncedApplySizing);
    modulePowerInput.addEventListener('input', debouncedApplySizing);
    systemPowerInput.addEventListener('input', debouncedUpdateDerivedFromPower);
    monthlyInput.addEventListener('change', () => applySizing());
    modulePowerInput.addEventListener('change', () => applySizing());
    systemPowerInput.addEventListener('change', () => updateDerivedFieldsFromPower());
    moduleQuantityInput.addEventListener('input', updatePreview);
    generationInput.addEventListener('input', debouncedPreviewRefresh);
    suggestedPriceInput.addEventListener('input', debouncedPreviewRefresh);
    energyBillInput?.addEventListener('input', debouncedPreviewRefresh);
    inverterInput.addEventListener('input', updatePreview);
    customerSelect?.addEventListener('change', updatePreview);
    projectNameInput?.addEventListener('input', updatePreview);
    projectStatusInput?.addEventListener('change', updatePreview);
    cityInput?.addEventListener('input', () => {
      resolveRegionalPricing();
      debouncedPreviewRefresh();
      scheduleAutomationPreview();
    });
    cityInput?.addEventListener('change', () => {
      resolveRegionalPricing();
      updatePreview();
      scheduleAutomationPreview(0);
    });
    stateInput?.addEventListener('input', () => {
      resolveRegionalPricing();
      debouncedApplySizing();
      scheduleAutomationPreview();
    });
    stateInput?.addEventListener('change', () => {
      resolveRegionalPricing();
      applySizing();
      scheduleAutomationPreview(0);
    });
    zipInput?.addEventListener('input', () => scheduleAutomationPreview());
    zipInput?.addEventListener('change', () => scheduleAutomationPreview(0));
    streetInput?.addEventListener('input', () => scheduleAutomationPreview());
    streetInput?.addEventListener('change', () => scheduleAutomationPreview(0));
    numberInput?.addEventListener('input', () => scheduleAutomationPreview());
    numberInput?.addEventListener('change', () => scheduleAutomationPreview(0));
    districtInput?.addEventListener('input', () => scheduleAutomationPreview());
    districtInput?.addEventListener('change', () => scheduleAutomationPreview(0));
    root.addEventListener('solar:address-autofilled', () => {
      scheduleAutomationPreview(80);
    });
  });
};

const initSolarMarketPriceFill = () => {
  const triggers = Array.from(document.querySelectorAll('[data-market-price-fill]'));
  if (!triggers.length) return;

  triggers.forEach((trigger) => {
    if (!(trigger instanceof HTMLButtonElement)) return;

    const root = trigger.closest('form');
    const input = root?.querySelector('[data-market-price-input]');
    if (!(input instanceof HTMLInputElement)) return;

    trigger.addEventListener('click', () => {
      const marketValue = Number(trigger.dataset.marketPriceFill || '4200');
      input.value = Number.isFinite(marketValue) ? formatCurrencyBRL(marketValue) : formatCurrencyBRL(4200);
      input.focus();
    });
  });
};

const initEnergyUtilityAutoSelect = () => {
  const selects = Array.from(document.querySelectorAll('[data-utility-select]'));
  if (!selects.length) return;

  const normalize = (value) => value
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .trim()
    .toLowerCase();

  selects.forEach((select) => {
    if (!(select instanceof HTMLSelectElement)) return;

    const root = select.closest('[data-cep-lookup]') || select.closest('.solar-flow-section');
    if (!(root instanceof HTMLElement)) return;

    const cityInput = root.querySelector('[data-cep-city]');
    const stateInput = root.querySelector('[data-cep-state]');
    const feedback = root.querySelector('[data-utility-feedback]');

    if (!(cityInput instanceof HTMLInputElement)) return;
    if (!(stateInput instanceof HTMLInputElement)) return;

    const lookup = JSON.parse(select.dataset.utilityLookup || '[]');
    let manualOverride = select.value !== '';
    const placeholderLabel = select.options[0]?.textContent?.trim() || 'Selecionar automaticamente';

    const findUtilityById = (utilityId) => {
      const normalizedId = String(utilityId || '').trim();
      if (!normalizedId) return null;
      return lookup.find((utility) => String(utility.id) === normalizedId) || null;
    };

    const currentLocation = () => ({
      city: normalize(cityInput.value || ''),
      state: (stateInput.value || '').trim().toUpperCase(),
    });

    const filteredUtilities = () => {
      const { city, state } = currentLocation();

      if (!state) {
        return [];
      }

      const byState = lookup.filter((utility) => utility.state === state);

      if (!city) {
        return byState;
      }

      const byCity = byState.filter((utility) => (
        !Array.isArray(utility.cities)
        || utility.cities.length === 0
        || utility.cities.includes(city)
      ));

      return byCity.length > 0 ? byCity : byState;
    };

    const renderUtilityOptions = (utilities) => {
      const currentValue = select.value;
      select.innerHTML = '';
      select.append(new Option(placeholderLabel, ''));

      utilities.forEach((utility) => {
        const option = new Option(`${utility.name} (${utility.state})`, String(utility.id));
        select.append(option);
      });

      if (currentValue !== '' && utilities.some((utility) => String(utility.id) === currentValue)) {
        select.value = currentValue;
      } else {
        select.value = '';
      }
    };

    const selectedUtilityMatchesLocation = () => {
      const selected = findUtilityById(select.value);
      if (!selected) return false;

      const { city, state } = currentLocation();
      if (!state || selected.state !== state) return false;
      if (!city) return true;
      if (!Array.isArray(selected.cities) || selected.cities.length === 0) return true;

      return selected.cities.includes(city);
    };

    const setFeedback = (message, state = '') => {
      if (!(feedback instanceof HTMLElement)) return;
      feedback.textContent = message;
      feedback.classList.remove('is-success', 'is-error');
      if (state) {
        feedback.classList.add(state);
      }
    };

    const resolveUtility = () => {
      const utilities = filteredUtilities();
      renderUtilityOptions(utilities);

      if (manualOverride && select.value !== '') {
        if (selectedUtilityMatchesLocation()) return;

        // Endereco mudou e invalida a escolha anterior.
        manualOverride = false;
        select.value = '';
      }

      const city = normalize(cityInput.value || '');
      const state = (stateInput.value || '').trim().toUpperCase();

      if (!state) {
        manualOverride = false;
        select.value = '';
        setFeedback('Informe a UF para carregar as concessionarias da regiao.');
        return;
      }

      if (!city) {
        if (select.value === '') {
          setFeedback('Concessionarias filtradas por UF. Informe a cidade para sugestao automatica precisa.');
        }
        return;
      }

      if (utilities.length === 0) {
        select.value = '';
        setFeedback('Nenhuma concessionaria encontrada para esta UF no catalogo atual.', 'is-error');
        return;
      }

      const matched = utilities.find((utility) => Array.isArray(utility.cities) && utility.cities.includes(city));

      if (matched) {
        select.value = String(matched.id);
        manualOverride = false;
        setFeedback(`Concessionaria sugerida automaticamente: ${matched.name}.`, 'is-success');
      } else if (!manualOverride) {
        select.value = '';
        setFeedback('Nenhuma concessionaria do catalogo foi encontrada para esta cidade. Ajuste manualmente se necessario.', 'is-error');
      } else if (select.value === '') {
        setFeedback('Selecione manualmente uma concessionaria da mesma UF.', 'is-error');
      }
    };

    select.addEventListener('change', () => {
      manualOverride = select.value !== '';

      if (select.value === '') {
        manualOverride = false;
        resolveUtility();
        return;
      }

      if (!selectedUtilityMatchesLocation()) {
        manualOverride = false;
        select.value = '';
        resolveUtility();
        return;
      }

      const selectedLabel = select.options[select.selectedIndex]?.textContent?.trim() || 'Concessionaria selecionada.';
      setFeedback(`${selectedLabel} selecionada manualmente.`, 'is-success');
    });

    cityInput.addEventListener('input', resolveUtility);
    stateInput.addEventListener('input', resolveUtility);
    cityInput.addEventListener('change', resolveUtility);
    stateInput.addEventListener('change', resolveUtility);
    root.addEventListener('solar:address-autofilled', resolveUtility);

    resolveUtility();
  });
};

const initSolarProjectShowcase = () => {
  const showcases = Array.from(document.querySelectorAll('[data-solar-showcase]'));
  if (!showcases.length) return;

  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const currencyFormatter = new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
  const formatters = {
    currency: (value) => currencyFormatter.format(value),
    kwp: (value) => `${value.toFixed(2).replace('.', ',')} kWp`,
    kwh: (value) => `${value.toFixed(2).replace('.', ',')} kWh/mes`,
    months: (value) => `${Math.round(value)} meses`,
    percent: (value) => `${value.toFixed(1).replace('.', ',')}%`,
  };

  showcases.forEach((showcase) => {
    const metrics = Array.from(showcase.querySelectorAll('[data-show-animate-number]'));

    metrics.forEach((metric, index) => {
      if (!(metric instanceof HTMLElement)) return;

      const targetValue = Number(metric.dataset.showValue || '');
      const formatterKey = metric.dataset.showFormat || 'currency';
      const formatter = formatters[formatterKey] || ((value) => String(value));
      const card = metric.closest('.solar-project-showcase-metric');

      if (!Number.isFinite(targetValue) || prefersReducedMotion) {
        return;
      }

      const duration = 520;
      const delay = index * 70;

      window.setTimeout(() => {
        const startedAt = performance.now();
        card?.classList.add('is-counting');

        const tick = (now) => {
          const elapsed = Math.min((now - startedAt) / duration, 1);
          const eased = 1 - ((1 - elapsed) ** 3);
          metric.textContent = formatter(targetValue * eased);

          if (elapsed < 1) {
            window.requestAnimationFrame(tick);
            return;
          }

          metric.textContent = formatter(targetValue);
          window.setTimeout(() => {
            card?.classList.remove('is-counting');
          }, 120);
        };

        window.requestAnimationFrame(tick);
      }, delay);
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
  initNumberSteppers();
  initCurrencyInputs();
  initSubmitLocks();
  initCustomSelects();
  initPasswordToggles();
  initFlashAlerts();
  initZipCodeLookup();
  initSolarSizingForm();
  initSolarMarketPriceFill();
  initEnergyUtilityAutoSelect();
  initSolarProjectShowcase();
  initErrorEasterEggs();
  initArcaneAccents();
});
