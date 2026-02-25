import './bootstrap';

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
  const cards = Array.from(document.querySelectorAll('[data-portfolio-grid] [data-tags]'));

  if (!buttons.length || !cards.length) return;

  buttons.forEach((button) => {
    button.addEventListener('click', () => {
      const filter = button.getAttribute('data-filter');

      buttons.forEach((item) => item.classList.remove('is-active'));
      button.classList.add('is-active');

      cards.forEach((card) => {
        if (!filter || filter === 'all') {
          card.classList.remove('is-hidden');
          return;
        }

        const tags = (card.getAttribute('data-tags') || '').split(',').map((tag) => tag.trim());
        const shouldShow = tags.includes(filter);
        card.classList.toggle('is-hidden', !shouldShow);
      });
    });
  });
};

const initFaqAccordion = () => {
  const groups = Array.from(document.querySelectorAll('[data-faq-group]'));
  if (!groups.length) return;

  groups.forEach((group) => {
    const items = Array.from(group.querySelectorAll('details.faq-item'));
    if (!items.length) return;

    items.forEach((item) => {
      item.addEventListener('toggle', () => {
        if (!item.open) return;

        items.forEach((other) => {
          if (other !== item) other.open = false;
        });
      });
    });
  });
};

document.addEventListener('DOMContentLoaded', () => {
  initMobileMenu();
  initSmoothScroll();
  initModals();
  initPortfolioFilter();
  initFaqAccordion();
});
