document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.querySelector('.nav-toggle');
  const nav = document.querySelector('#main-nav');
  toggle?.addEventListener('click', () => {
    const open = toggle.getAttribute('aria-expanded') === 'true';
    toggle.setAttribute('aria-expanded', String(!open));
    nav?.classList.toggle('is-open', !open);
  });

  const checkIn = document.querySelector('[name="check_in"]');
  const checkOut = document.querySelector('[name="check_out"]');
  const price = document.querySelector('[data-price-per-night]');
  const total = document.querySelector('[data-total-price]');
  const nights = document.querySelector('[data-total-nights]');
  const updateEstimate = () => {
    if (!checkIn?.value || !checkOut?.value) return;
    const start = new Date(`${checkIn.value}T00:00:00`);
    const end = new Date(`${checkOut.value}T00:00:00`);
    const count = Math.round((end - start) / 86400000);
    const rate = Number(price?.dataset.pricePerNight || 0);
    if (count > 0) {
      if (nights) nights.textContent = `${count} night${count === 1 ? '' : 's'}`;
      if (total) total.textContent = new Intl.NumberFormat('en-LK', { maximumFractionDigits: 0 }).format(count * rate);
    } else if (nights) {
      nights.textContent = 'Choose valid dates';
      if (total) total.textContent = '—';
    }
  };
  checkIn?.addEventListener('change', () => {
    if (checkOut) checkOut.min = checkIn.value;
    updateEstimate();
  });
  checkOut?.addEventListener('change', updateEstimate);

  document.querySelectorAll('[data-confirm]').forEach((button) => {
    button.addEventListener('click', (event) => {
      if (!window.confirm(button.dataset.confirm || 'Continue with this change?')) event.preventDefault();
    });
  });
});
