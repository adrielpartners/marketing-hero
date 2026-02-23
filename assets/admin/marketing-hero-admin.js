document.addEventListener('click', function (event) {
  const button = event.target.closest('[data-mh-confirm]');
  if (!button) {
    return;
  }

  const message = button.getAttribute('data-mh-confirm') || 'Are you sure?';
  if (!window.confirm(message)) {
    event.preventDefault();
  }
});

document.addEventListener('change', function (event) {
  const select = event.target.closest('select[name="range"]');
  if (!select) {
    return;
  }

  const form = select.closest('form');
  if (form) {
    form.submit();
  }
});
