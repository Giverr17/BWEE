document.addEventListener('DOMContentLoaded', () => {
  /* ===== HEADER SCROLL & MOBILE DRAWER ===== */
  const siteHeader = document.getElementById('siteHeader');
  const menuBtn = document.getElementById('menuBtn');
  const drawerCloseBtn = document.getElementById('drawerCloseBtn');
  const mobileDrawer = document.getElementById('mobileDrawer');
  const mobileDrawerBackdrop = document.getElementById('mobileDrawerBackdrop');

  // Sticky / Scrolled Header Effect
  if (siteHeader) {
    const handleScroll = () => {
      if (window.scrollY > 20) {
        siteHeader.classList.add('header-scrolled');
      } else {
        siteHeader.classList.remove('header-scrolled');
      }
    };
    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll();
  }

  // Mobile Drawer Navigation
  function openDrawer() {
    if (!mobileDrawer) return;
    mobileDrawer.classList.add('open');
    if (mobileDrawerBackdrop) mobileDrawerBackdrop.classList.add('open');
    if (menuBtn) {
      menuBtn.classList.add('open');
      menuBtn.setAttribute('aria-expanded', 'true');
    }
    mobileDrawer.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }

  function closeDrawer() {
    if (!mobileDrawer) return;
    mobileDrawer.classList.remove('open');
    if (mobileDrawerBackdrop) mobileDrawerBackdrop.classList.remove('open');
    if (menuBtn) {
      menuBtn.classList.remove('open');
      menuBtn.setAttribute('aria-expanded', 'false');
    }
    mobileDrawer.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }

  if (menuBtn) {
    menuBtn.addEventListener('click', () => {
      mobileDrawer && mobileDrawer.classList.contains('open') ? closeDrawer() : openDrawer();
    });
  }

  if (drawerCloseBtn) {
    drawerCloseBtn.addEventListener('click', closeDrawer);
  }

  if (mobileDrawerBackdrop) {
    mobileDrawerBackdrop.addEventListener('click', closeDrawer);
  }

  // Close on Escape key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && mobileDrawer && mobileDrawer.classList.contains('open')) {
      closeDrawer();
    }
  });

  // Close when any drawer navigation link is clicked
  if (mobileDrawer) {
    mobileDrawer.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', closeDrawer);
    });
  }



  /* ===== TYPEWRITER EFFECT ===== */
  const missionHeadline = 'Research-backed. Community-built.';
  const typeTarget = document.getElementById('typewriterTarget');
  const typeCursor = document.getElementById('typewriterCursor');
  let typewriterRan = false;

  function runTypewriter() {
    if (typewriterRan || !typeTarget) return;
    typewriterRan = true;
    const headline = typeTarget.dataset.text || missionHeadline;
    let i = 0;
    function typeNext() {
      if (i < headline.length) {
        typeTarget.textContent += headline.charAt(i);
        i++;
        setTimeout(typeNext, 32);
      } else if (typeCursor) {
        setTimeout(() => {
          typeCursor.style.display = 'none';
        }, 1000);
      }
    }
    typeNext();
  }

  const missionSection = document.getElementById('mission');
  if (missionSection && 'IntersectionObserver' in window) {
    const typeObserver = new IntersectionObserver(
      entries => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            runTypewriter();
            typeObserver.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.3 }
    );
    typeObserver.observe(missionSection);
  } else {
    runTypewriter();
  }

  /* ===== SCROLL-LINKED EVENT CARD SHUFFLE ===== */
  const shuffleCards = [
    { el: document.getElementById('eventCard1'), x0: 106, y0: 34, r0: -11, s0: 0.94 },
    { el: document.getElementById('eventCard2'), x0: 0,   y0: 14, r0: 3,   s0: 1 },
    { el: document.getElementById('eventCard3'), x0: -106, y0: 34, r0: 11,  s0: 0.94 },
  ];
  const eventsSection = document.getElementById('events');
  let shuffleTicking = false;

  function applyShuffle(progress) {
    // Disable horizontal shuffle effect on mobile/tablet to avoid overflow and misaligned stacks
    if (window.innerWidth <= 860) {
      shuffleCards.forEach(c => {
        if (c.el) {
          c.el.style.transform = '';
          c.el.style.zIndex = '';
        }
      });
      return;
    }

    shuffleCards.forEach((c, i) => {
      if (!c.el) return;
      const x = c.x0 * (1 - progress);
      const y = c.y0 * (1 - progress);
      const r = c.r0 * (1 - progress);
      const s = c.s0 + (1 - c.s0) * progress;
      c.el.style.transform = `translate(${x}%, ${y}px) rotate(${r}deg) scale(${s})`;
      c.el.style.zIndex = String(10 - i);
    });
  }

  function updateShuffle() {
    shuffleTicking = false;
    if (!eventsSection) return;
    const rect = eventsSection.getBoundingClientRect();
    const vh = window.innerHeight;
    let progress = (vh - rect.top) / (vh * 1.05);
    progress = Math.max(0, Math.min(1, progress));
    applyShuffle(progress);
  }

  function onScroll() {
    if (!shuffleTicking) {
      shuffleTicking = true;
      requestAnimationFrame(updateShuffle);
    }
  }

  if (eventsSection) {
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll);
    updateShuffle();
  }

  /* ===== GET INVOLVED CARDS ROLLOUT REVEAL ===== */
  const involveCards = document.querySelectorAll('.involve-card');
  if (involveCards.length > 0) {
    if (window.innerWidth <= 860) {
      involveCards.forEach(card => card.classList.add('rolled-out'));
    } else if ('IntersectionObserver' in window) {
      const rollObserver = new IntersectionObserver(
        entries => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {
              entry.target.classList.add('rolled-out');
              rollObserver.unobserve(entry.target);
            }
          });
        },
        { threshold: 0.15 }
      );
      involveCards.forEach(card => rollObserver.observe(card));
    } else {
      involveCards.forEach(card => card.classList.add('rolled-out'));
    }
  }
});

// Keep the scroll position when switching language
(function () {
  const KEY = 'bwee_scroll';

  document.addEventListener('click', function (e) {
    const link = e.target.closest('a[href*="/lang/"]');
    if (!link) return;
    try {
      sessionStorage.setItem(KEY, JSON.stringify({
        path: location.pathname,
        y: window.scrollY,
      }));
    } catch (err) {}
  });

  function restore() {
    try {
      const saved = JSON.parse(sessionStorage.getItem(KEY) || 'null');
      if (!saved) return;
      sessionStorage.removeItem(KEY);
      if (saved.path === location.pathname) {
        window.scrollTo({ top: saved.y, behavior: 'instant' });
      }
    } catch (err) {}
  }

  document.addEventListener('DOMContentLoaded', restore);
})();
