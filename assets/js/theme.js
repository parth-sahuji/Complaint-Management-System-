/* theme.js — Dark / Light mode toggle */
(function () {
  'use strict';

  // Default is dark; no localStorage persistence per spec
  let isDark = true;

  function applyTheme(dark) {
    document.documentElement.setAttribute('data-theme', dark ? 'dark' : 'light');
    const btn = document.getElementById('themeToggle');
    if (btn) btn.textContent = dark ? '☀️' : '🌙';
    isDark = dark;
  }

  function toggleTheme() {
    applyTheme(!isDark);
  }

  document.addEventListener('DOMContentLoaded', function () {
    // Apply default dark theme
    applyTheme(true);

    const btn = document.getElementById('themeToggle');
    if (btn) btn.addEventListener('click', toggleTheme);
  });

  // Expose globally
  window.toggleTheme = toggleTheme;
})();
