/* =============================================
   ШАШЛЫК АРМО v2.0 — script.js
   ============================================= */

// ===== PRELOADER =====
(function () {
  const preloader = document.getElementById('preloader');
  const fill = document.getElementById('preloaderFill');
  const plEmbers = document.getElementById('plEmbers');

  // Создаём угольки в прелоадере
  for (let i = 0; i < 20; i++) {
    const e = document.createElement('div');
    e.className = 'ember';
    const size = Math.random() * 3 + 1;
    e.style.cssText = `
      width:${size}px; height:${size}px;
      background: ${['#ff6b35','#ffb347','#ffd166','#e63946'][Math.floor(Math.random()*4)]};
      left:${Math.random()*100}%; bottom:${Math.random()*30}%;
      animation-duration:${Math.random()*4+3}s;
      animation-delay:${Math.random()*3}s;
      box-shadow:0 0 ${size*2}px currentColor;
    `;
    plEmbers.appendChild(e);
  }

  let progress = 0;
  const interval = setInterval(() => {
    progress += Math.random() * 12 + 4;
    if (progress >= 100) { progress = 100; clearInterval(interval); }
    fill.style.width = progress + '%';
    if (progress >= 100) {
      setTimeout(() => {
        preloader.classList.add('hidden');
        document.body.style.overflow = '';
      }, 400);
    }
  }, 80);

  document.body.style.overflow = 'hidden';
})();

// ===== ПРОМО БАННЕР + NAVBAR ПОЗИЦИЯ =====
const promoBanner = document.getElementById('promoBanner');
const navbar = document.getElementById('navbar');
const BANNER_H = 42;

function updateNavbar() {
  const bannerVisible = promoBanner && promoBanner.style.display !== 'none';
  const bannerH = bannerVisible ? BANNER_H : 0;
  const scrollY = window.scrollY;

  // Плавно сдвигаем навбар вверх пока баннер не прокручен
  const newTop = Math.max(0, bannerH - scrollY);
  navbar.style.top = newTop + 'px';
  navbar.classList.toggle('scrolled', scrollY > bannerH);
}

// Кнопка закрытия баннера — пересчитываем позицию
const promoBannerCloseBtn = document.querySelector('.promo-close');
if (promoBannerCloseBtn) {
  promoBannerCloseBtn.addEventListener('click', () => {
    updateNavbar();
  });
}

// ===== NAVBAR СКРОЛЛ =====
const navBurger = document.getElementById('navBurger');
const navMobile = document.getElementById('navMobile');

window.addEventListener('scroll', updateNavbar, { passive: true });
updateNavbar(); // инициализация при загрузке

navBurger.addEventListener('click', () => {
  const isOpen = navMobile.classList.toggle('open');
  const [s1, s2, s3] = navBurger.querySelectorAll('span');
  if (isOpen) {
    s1.style.transform = 'rotate(45deg) translate(5px, 5px)';
    s2.style.opacity = '0';
    s3.style.transform = 'rotate(-45deg) translate(5px, -5px)';
  } else {
    s1.style.transform = s2.style.opacity = s3.style.transform = '';
    s2.style.opacity = '1';
  }
});
navMobile.querySelectorAll('a').forEach(a => {
  a.addEventListener('click', () => {
    navMobile.classList.remove('open');
    navBurger.querySelectorAll('span').forEach(s => { s.style.transform = ''; s.style.opacity = '1'; });
  });
});

// ===== УГОЛЬКИ / ИСКРЫ =====
(function spawnEmbers() {
  const field = document.getElementById('emberField');
  if (!field) return;
  const colors = ['rgba(255,107,53,.9)', 'rgba(255,179,71,.9)', 'rgba(255,209,102,.8)', 'rgba(230,57,70,.85)'];
  for (let i = 0; i < 50; i++) {
    const e = document.createElement('div');
    e.className = 'ember';
    const sz = Math.random() * 4 + 1;
    const col = colors[Math.floor(Math.random() * colors.length)];
    e.style.cssText = `
      width:${sz}px; height:${sz}px; background:${col};
      left:${Math.random()*100}%; bottom:${Math.random()*25}%;
      animation-duration:${Math.random()*7+5}s;
      animation-delay:${Math.random()*10}s;
      box-shadow:0 0 ${sz*3}px ${col};
    `;
    field.appendChild(e);
  }
})();

// ===== ВКЛАДКИ МЕНЮ =====
document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const tabId = btn.dataset.tab;
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.menu-grid').forEach(g => g.classList.remove('active'));
    btn.classList.add('active');
    const target = document.getElementById('tab-' + tabId);
    if (target) {
      target.classList.add('active');
      target.querySelectorAll('[data-aos]').forEach(el => el.classList.remove('aos-visible'));
      setTimeout(checkAos, 60);
    }
  });
});

// ===== AOS =====
function checkAos() {
  const vh = window.innerHeight;
  document.querySelectorAll('[data-aos]:not(.aos-visible)').forEach((el, i) => {
    if (el.getBoundingClientRect().top < vh - 50) {
      setTimeout(() => el.classList.add('aos-visible'), i * 70);
    }
  });
}
window.addEventListener('scroll', checkAos, { passive: true });
checkAos();

// ===== ПЛАВНЫЙ СКРОЛЛ =====
document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', e => {
    const target = document.querySelector(a.getAttribute('href'));
    if (target) {
      e.preventDefault();
      window.scrollTo({ top: target.getBoundingClientRect().top + window.scrollY - 80, behavior: 'smooth' });
    }
  });
});

// ===== СЧЁТЧИКИ HERO =====
let countersStarted = false;
const heroStats = document.querySelector('.hero-stats');
if (heroStats) {
  new IntersectionObserver(([entry]) => {
    if (entry.isIntersecting && !countersStarted) {
      countersStarted = true;
      document.querySelectorAll('.stat-num[data-target]').forEach(el => {
        const target = parseFloat(el.dataset.target);
        const suffix = el.dataset.suffix || '';
        const isFloat = target % 1 !== 0;
        const duration = 1800;
        let start = null;
        const step = ts => {
          if (!start) start = ts;
          const p = Math.min((ts - start) / duration, 1);
          const eased = 1 - Math.pow(1 - p, 3);
          const val = isFloat ? (eased * target).toFixed(1) : Math.floor(eased * target);
          el.textContent = val + suffix;
          if (p < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
      });
    }
  }, { threshold: 0.5 }).observe(heroStats);
}

// ===== ПАРАЛЛАКС HERO =====
const heroContent = document.querySelector('.hero-content');
window.addEventListener('scroll', () => {
  if (!heroContent) return;
  const sy = window.scrollY;
  heroContent.style.transform = `translateY(${sy * 0.22}px)`;
  heroContent.style.opacity = Math.max(0, 1 - sy / 650);
}, { passive: true });

// ===== 3D КАРТОЧКИ МЕНЮ =====
document.addEventListener('mousemove', e => {
  document.querySelectorAll('.menu-card').forEach(card => {
    const rect = card.getBoundingClientRect();
    if (e.clientX < rect.left || e.clientX > rect.right || e.clientY < rect.top || e.clientY > rect.bottom) return;
    const x = ((e.clientX - rect.left) / rect.width - .5) * 10;
    const y = ((e.clientY - rect.top) / rect.height - .5) * 10;
    card.style.transform = `translateY(-10px) perspective(900px) rotateX(${-y * .4}deg) rotateY(${x * .4}deg)`;
  });
});
document.querySelectorAll('.menu-card').forEach(card => {
  card.addEventListener('mouseleave', () => card.style.transform = '');
});

// ===== МОДАЛ ЗВОНКА =====
function callOrder() {
  document.getElementById('callModal').classList.add('active');
  document.body.style.overflow = 'hidden';
}
function closeModal() {
  document.getElementById('callModal').classList.remove('active');
  document.body.style.overflow = '';
}
document.addEventListener('keydown', e => e.key === 'Escape' && closeModal());

// ===== АКТИВНЫЙ ПУНКТ НАВИГАЦИИ =====
const sections = document.querySelectorAll('section[id]');
const navLinks = document.querySelectorAll('.nav-links a');
window.addEventListener('scroll', () => {
  const pos = window.scrollY + 120;
  sections.forEach(sec => {
    if (pos >= sec.offsetTop && pos < sec.offsetTop + sec.offsetHeight) {
      navLinks.forEach(a => {
        a.classList.toggle('active-link', a.getAttribute('href') === '#' + sec.id);
      });
    }
  });
}, { passive: true });

// ===== КОНСОЛЬ =====
console.log('%c🔥 Золотая Рыбка — Шашлычная 2026 🔥', 'font-size:22px;font-weight:700;color:#ff6b35;');
console.log('%c+7 (915) 055-70-55  |  МО, Мытищинский р-н, д.Бородино, д.51', 'font-size:13px;color:#f4a261;');
