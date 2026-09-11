(() => {
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Scroll reveals: sections and key cards enter with controlled movement.
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

  // Scroll-linked movement for the existing festival doodles.
  const parallaxEls = [...document.querySelectorAll('.object')];
  let ticking = false;
  const parallax = () => {
    if (!reduceMotion) {
      const y = window.scrollY;
      parallaxEls.forEach((el, i) => {
        if (el.closest('.hop-dash')) return;
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

  // Desktop pointer tilt for cards. Touch devices stay clean and usable.
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

  // HOP DASH: a tiny, self-contained interaction built around the existing challenge section.
  const dash = document.querySelector('.hop-dash');
  const button = document.getElementById('hopDashBtn');
  const runner = document.getElementById('hopRunner');
  const score = document.getElementById('hopDashScore');
  const status = document.getElementById('hopDashStatus');
  if (dash && button && runner && score && status) {
    let hops = 0;
    const hop = () => {
      if (hops >= 10) hops = 0;
      hops += 1;
      runner.style.left = `${2 + hops * 9.2}%`;
      runner.style.transform = `translateY(-50%) rotate(${hops % 2 ? -8 : 7}deg) scale(${hops === 10 ? 1.15 : 1})`;
      score.textContent = `${hops} / 10 HOPS`;
      if (hops < 10) {
        status.textContent = hops < 4 ? 'Keep hopping!' : hops < 8 ? 'You’re flying!' : 'Almost there!';
      } else {
        status.textContent = 'FINISH! You beat the dash.';
        dash.classList.add('finish');
        button.textContent = 'HOP AGAIN →';
      }
    };
    button.addEventListener('click', hop);
  }
})();
