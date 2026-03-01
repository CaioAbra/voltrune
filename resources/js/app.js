import './bootstrap';

document.documentElement.classList.add('js');

const body = document.body;

const initMobileMenu = () => {
  const toggle = document.querySelector('[data-menu-toggle]');
  const menu = document.querySelector('[data-menu]');

  if (!toggle || !menu) return;

  toggle.addEventListener('click', () => {
    const isOpen = menu.classList.toggle('is-open');
    toggle.setAttribute('aria-expanded', String(isOpen));
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
    }, 170);
  };

  const showCard = (card) => {
    card.classList.remove('is-filtered');

    if (reduceMotion) {
      card.classList.remove('is-filtering-out');
      return;
    }

    card.classList.add('is-filtering-out');
    requestAnimationFrame(() => {
      card.classList.remove('is-filtering-out');
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
    item.setAttribute('data-stagger', String(index * 45));
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

document.addEventListener('DOMContentLoaded', () => {
  initMobileMenu();
  initSmoothScroll();
  initModals();
  initPortfolioFilter();
  initQuestReveal();
  initFaqAccordion();
  initPhoneMask();
});
