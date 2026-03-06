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
  initArcaneAccents();
});
