// ============================================
// Footer year
// ============================================
document.getElementById('year').textContent = new Date().getFullYear();

// ============================================
// Mobile menu toggle
// ============================================
const menuToggle = document.getElementById('menuToggle');
const mobileTabs = document.getElementById('mobileTabs');

menuToggle.addEventListener('click', () => {
  const isOpen = mobileTabs.classList.toggle('open');
  menuToggle.classList.toggle('is-open', isOpen);
  menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
});

// Close mobile menu after tapping a link
mobileTabs.querySelectorAll('a').forEach(link => {
  link.addEventListener('click', () => {
    mobileTabs.classList.remove('open');
    menuToggle.classList.remove('is-open');
    menuToggle.setAttribute('aria-expanded', 'false');
  });
});

// ============================================
// Scroll-spy: highlight active tab based on section in view
// ============================================
const sections = document.querySelectorAll('main section[id]');
const allTabs = document.querySelectorAll('.tab');

const spyObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      const id = entry.target.getAttribute('id');
      allTabs.forEach(tab => {
        tab.classList.toggle('active', tab.dataset.target === id);
      });
    }
  });
}, { rootMargin: '-40% 0px -50% 0px', threshold: 0 });

sections.forEach(section => spyObserver.observe(section));

// ============================================
// Scroll reveal for cards
// ============================================
const revealTargets = document.querySelectorAll(
  '.skill-card, .timeline-item, .project-card, .strength-card, .edu-card, .contact-card'
);

revealTargets.forEach(el => el.classList.add('reveal'));

const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('in-view');
      revealObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.15 });

revealTargets.forEach(el => revealObserver.observe(el));

// ============================================
// Contact form — submits to Formspree, no page reload
// ============================================
const contactForm = document.getElementById('contactForm');
const formNote = document.getElementById('formNote');
const formSubmitBtn = contactForm.querySelector('button[type="submit"]');
const formSubmitLabel = formSubmitBtn.textContent;

contactForm.addEventListener('submit', async (e) => {
  e.preventDefault();

  const name = contactForm.elements['name'].value.trim();
  const firstName = name ? ', ' + name.split(' ')[0] : '';

  formSubmitBtn.disabled = true;
  formSubmitBtn.textContent = 'Sending…';
  formNote.style.color = '';
  formNote.textContent = 'Sending your message…';

  try {
    const response = await fetch(contactForm.action, {
      method: 'POST',
      body: new FormData(contactForm),
      headers: { 'Accept': 'application/json' },
    });

    if (response.ok) {
      formNote.style.color = '';
      formNote.textContent = `Thanks${firstName} — your message is on its way. I'll get back to you soon.`;
      contactForm.reset();
    } else {
      const data = await response.json().catch(() => null);
      const message = data && data.errors
        ? data.errors.map(err => err.message).join(', ')
        : 'Something went wrong sending that. Please try again or email me directly.';
      formNote.style.color = '#f87171';
      formNote.textContent = message;
    }
  } catch (err) {
    formNote.style.color = '#f87171';
    formNote.textContent = 'Network error — please email rajanparmar609@gmail.com directly.';
  } finally {
    formSubmitBtn.disabled = false;
    formSubmitBtn.textContent = formSubmitLabel;
  }
});