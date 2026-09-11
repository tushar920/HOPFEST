(() => {
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  const targets = document.querySelectorAll('section, .mpg, .zone, .challenge-photo, .family-highlight, .city-node, .date-card');
  targets.forEach((el, i) => {
    el.classList.add('p2-reveal');
    el.style.setProperty('--p2-i', Math.min(i % 5, 4));
  });

  if (!reduceMotion) {
    const io = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('p2-visible');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -6% 0px' });
    targets.forEach(el => io.observe(el));
  } else {
    targets.forEach(el => el.classList.add('p2-visible'));
  }

  const parallaxEls = [...document.querySelectorAll('.object')];
  let ticking = false;
  const parallax = () => {
    if (!reduceMotion) {
      const y = window.scrollY;
      parallaxEls.forEach((el, i) => {
        const speed = (i % 3 + 1) * 0.025;
        el.style.translate = `0 ${Math.round(y * speed) % 60}px`;
      });
    }
    ticking = false;
  };
  window.addEventListener('scroll', () => {
    if (!ticking) {
      requestAnimationFrame(parallax);
      ticking = true;
    }
  }, { passive: true });

  if (!reduceMotion && window.matchMedia('(pointer:fine)').matches) {
    document.querySelectorAll('.zone, .mpg, .challenge-photo, .date-card').forEach(card => {
      const original = card.style.transform;
      card.classList.add('p2-cursor');
      card.addEventListener('pointermove', event => {
        const rect = card.getBoundingClientRect();
        const x = (event.clientX - rect.left) / rect.width - 0.5;
        const y = (event.clientY - rect.top) / rect.height - 0.5;
        card.style.transform = `perspective(700px) rotateX(${-y * 2.2}deg) rotateY(${x * 2.2}deg) translate(-3px,-4px)`;
      });
      card.addEventListener('pointerleave', () => { card.style.transform = original; });
    });
  }
})();
