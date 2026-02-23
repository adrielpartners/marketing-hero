(function () {
  const confirms = document.querySelectorAll('[data-mh-confirm]');
  confirms.forEach((el) => {
    el.addEventListener('click', (event) => {
      if (!window.confirm(el.getAttribute('data-mh-confirm') || 'Are you sure?')) {
        event.preventDefault();
      }
    });
  });

  document.querySelectorAll('[data-mh-open-modal]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const modal = document.getElementById(btn.getAttribute('data-mh-open-modal'));
      if (modal) {
        modal.hidden = false;
        document.body.classList.add('mh-modal-open');
      }
    });
  });

  document.querySelectorAll('[data-mh-close-modal]').forEach((el) => {
    el.addEventListener('click', () => {
      const modal = el.closest('.mh-modal');
      if (modal) {
        modal.hidden = true;
        document.body.classList.remove('mh-modal-open');
      }
    });
  });

  const range = document.querySelector('[data-mh-range]');
  const customFields = document.querySelectorAll('[data-mh-custom-range]');
  if (range) {
    const sync = () => {
      const show = range.value === 'custom';
      customFields.forEach((field) => {
        field.hidden = !show;
      });
    };
    range.addEventListener('change', sync);
    sync();
  }
})();
