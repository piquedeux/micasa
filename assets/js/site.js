const papers = document.querySelectorAll('.paper');

if ('IntersectionObserver' in window) {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12 });

  papers.forEach((paper) => observer.observe(paper));
} else {
  papers.forEach((paper) => paper.classList.add('is-visible'));
}

document.querySelectorAll('.project-tile, .product-card').forEach((card) => {
  card.addEventListener('pointermove', (event) => {
    const rect = card.getBoundingClientRect();
    const x = (event.clientX - rect.left) / rect.width - 0.5;
    const y = (event.clientY - rect.top) / rect.height - 0.5;
    card.style.setProperty('--start-rotate', `${x * 1.4 + y * -1.2}deg`);
  });

  card.addEventListener('pointerleave', () => {
    card.style.removeProperty('--start-rotate');
  });
});
