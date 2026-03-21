/* animation.js — Orb parallax & UI entrance effects */
(function () {
  'use strict';

  /* ── Orb mouse parallax ───────────────── */
  let mouseX = 0, mouseY = 0;
  let currentX = 0, currentY = 0;
  let rafId = null;

  function lerp(a, b, t) { return a + (b - a) * t; }

  function animateOrbs() {
    currentX = lerp(currentX, mouseX, 0.04);
    currentY = lerp(currentY, mouseY, 0.04);

    const orbs = document.querySelectorAll('.orb');
    orbs.forEach(function (orb, i) {
      const factor = (i + 1) * 12;
      orb.style.transform =
        'translate(' + (currentX * factor * 0.01) + 'px, ' +
                       (currentY * factor * 0.01) + 'px)';
    });

    rafId = requestAnimationFrame(animateOrbs);
  }

  document.addEventListener('mousemove', function (e) {
    mouseX = e.clientX - window.innerWidth  / 2;
    mouseY = e.clientY - window.innerHeight / 2;
  });

  /* ── Staggered card entrance (Intersection Observer) ── */
  function observeCards() {
    if (!('IntersectionObserver' in window)) return;

    const observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1 });

    document.querySelectorAll('.complaint-card').forEach(function (card) {
      observer.observe(card);
    });
  }

  /* ── Sidebar collapse (desktop) ── */
  function initSidebar() {
    const sidebar     = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const sidebarBtn  = document.getElementById('sidebarToggle');
    const mobileBtn   = document.getElementById('mobileSidebarToggle');
    const overlay     = document.getElementById('sidebarOverlay');

    if (sidebarBtn && sidebar) {
      sidebarBtn.addEventListener('click', function () {
        sidebar.classList.toggle('collapsed');
        if (mainContent) mainContent.classList.toggle('expanded');
        // rotate toggle icon
        sidebarBtn.textContent = sidebar.classList.contains('collapsed') ? '›' : '‹';
      });
    }

    // Mobile sidebar
    if (mobileBtn && sidebar) {
      mobileBtn.addEventListener('click', function () {
        sidebar.classList.toggle('mobile-open');
        if (overlay) overlay.classList.toggle('active');
      });
    }
    if (overlay) {
      overlay.addEventListener('click', function () {
        if (sidebar) sidebar.classList.remove('mobile-open');
        overlay.classList.remove('active');
      });
    }
  }

  /* ── Complaint card expand/collapse ── */
  function initCards() {
    document.addEventListener('click', function (e) {
      const card = e.target.closest('.complaint-card');
      if (!card) return;
      // Don't toggle if clicking on a button/link inside
      if (e.target.closest('button, a')) return;
      card.classList.toggle('open');
    });
  }

  /* ── Search & filter ── */
  function initDashboardControls() {
    const searchInput = document.getElementById('searchInput');
    const pills       = document.querySelectorAll('.pill');
    let activeFilter  = 'all';

    function filterCards() {
      const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
      const cards = document.querySelectorAll('.complaint-card');
      let visible = 0;

      cards.forEach(function (card) {
        const title    = (card.dataset.title    || '').toLowerCase();
        const category = (card.dataset.category || '').toLowerCase();
        const status   = (card.dataset.status   || '').toLowerCase();

        const matchSearch = !query || title.includes(query) || category.includes(query);
        const matchFilter = activeFilter === 'all' || status === activeFilter;

        const show = matchSearch && matchFilter;
        card.style.display = show ? '' : 'none';
        if (show) visible++;
      });

      const empty = document.getElementById('emptyState');
      if (empty) empty.style.display = visible === 0 ? '' : 'none';
    }

    if (searchInput) {
      searchInput.addEventListener('input', filterCards);
    }

    pills.forEach(function (pill) {
      pill.addEventListener('click', function () {
        pills.forEach(function (p) { p.classList.remove('active'); });
        pill.classList.add('active');
        activeFilter = pill.dataset.filter;
        filterCards();
      });
    });
  }

  /* ── Init on DOM ready ── */
  document.addEventListener('DOMContentLoaded', function () {
    rafId = requestAnimationFrame(animateOrbs);
    observeCards();
    initSidebar();
    initCards();
    initDashboardControls();
  });

  window.observeNewCards = observeCards; // expose for dynamic content
})();
