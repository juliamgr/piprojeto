(() => {
  const button = document.querySelector('.menu-button');
  const nav = document.getElementById('main-nav');
  if (!button || !nav) return;
  const mobile = window.matchMedia('(max-width: 760px)');
  function closeMenu() { nav.classList.remove('is-open'); button.setAttribute('aria-expanded', 'false'); }
  function adapt() { button.hidden = !mobile.matches; closeMenu(); }
  document.documentElement.classList.add('has-js');
  button.addEventListener('click', () => {
    const open = button.getAttribute('aria-expanded') !== 'true';
    nav.classList.toggle('is-open', open); button.setAttribute('aria-expanded', String(open));
  });
  nav.addEventListener('click', event => { if (event.target.closest('a')) closeMenu(); });
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && button.getAttribute('aria-expanded') === 'true') { closeMenu(); button.focus(); }
  });
  document.addEventListener('click', event => { if (!nav.contains(event.target) && !button.contains(event.target)) closeMenu(); });
  mobile.addEventListener('change', adapt); adapt();
})();
