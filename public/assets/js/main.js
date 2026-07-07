/* ============================================================
   main.js — สคริปต์ฝั่งหน้าเว็บ
   - สลับ Dark/Light (จำค่าไว้ใน localStorage)
   - เมนูมือถือ
   - Animated Counter
   - Scroll reveal
   - Loader ตอนโหลดหน้า
   - Toast auto-dismiss + ยืนยันก่อนลบ
   ============================================================ */

(function () {
    'use strict';

    /* ---------- Dark Mode ---------- */
    const THEME_KEY = 'school-theme';
    const root = document.documentElement;

    function applyTheme(theme) {
        root.setAttribute('data-theme', theme);
        const btn = document.querySelector('.theme-toggle');
        if (btn) btn.textContent = theme === 'dark' ? '☀️' : '🌙';
    }

    // ใช้ค่าที่เคยเลือก หรือตามระบบ
    const saved = localStorage.getItem(THEME_KEY);
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    applyTheme(saved || (prefersDark ? 'dark' : 'light'));

    document.addEventListener('click', function (e) {
        if (e.target.closest('.theme-toggle')) {
            const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            localStorage.setItem(THEME_KEY, next);
            applyTheme(next);
        }
        // เมนูมือถือ
        if (e.target.closest('.nav-toggle')) {
            document.querySelector('.nav-links')?.classList.toggle('open');
        }
        // ยืนยันก่อนลบ (ใช้ SweetAlert2 ถ้ามี ไม่งั้น confirm ปกติ)
        const delBtn = e.target.closest('[data-confirm]');
        if (delBtn) {
            e.preventDefault();
            const form = delBtn.closest('form');
            const msg = delBtn.getAttribute('data-confirm') || 'ยืนยันการลบ?';
            if (window.Swal) {
                Swal.fire({
                    title: msg, icon: 'warning', showCancelButton: true,
                    confirmButtonText: 'ลบเลย', cancelButtonText: 'ยกเลิก',
                    confirmButtonColor: '#C0392B'
                }).then(r => { if (r.isConfirmed) form.submit(); });
            } else if (confirm(msg)) {
                form.submit();
            }
        }
    });

    /* ---------- Animated Counter ---------- */
    function animateCounter(el) {
        const target = parseInt(el.dataset.target || '0', 10);
        const duration = 1400;
        const start = performance.now();
        function tick(now) {
            const p = Math.min((now - start) / duration, 1);
            // ease-out
            const eased = 1 - Math.pow(1 - p, 3);
            el.textContent = Math.floor(eased * target).toLocaleString('th-TH');
            if (p < 1) requestAnimationFrame(tick);
            else el.textContent = target.toLocaleString('th-TH');
        }
        requestAnimationFrame(tick);
    }

    /* ---------- Scroll reveal + counter trigger ---------- */
    const io = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('in');
            if (entry.target.classList.contains('num') && entry.target.dataset.target) {
                animateCounter(entry.target);
            }
            io.unobserve(entry.target);
        });
    }, { threshold: 0.2 });

    document.querySelectorAll('.reveal, .num[data-target]').forEach(el => io.observe(el));

    /* ---------- Loader ---------- */
    window.addEventListener('load', () => {
        document.querySelector('.loader-overlay')?.classList.add('hide');
    });

    /* ---------- Toast auto-dismiss ---------- */
    document.querySelectorAll('.toast').forEach(t => {
        setTimeout(() => {
            t.style.transition = 'opacity .4s, transform .4s';
            t.style.opacity = '0';
            t.style.transform = 'translateX(20px)';
            setTimeout(() => t.remove(), 400);
        }, 3500);
    });
})();

/* ============================================================
   Banner Slider (หน้าแรก) — เลื่อนอัตโนมัติ + ปุ่มลูกศร + จุด
   ============================================================ */
(function () {
    const slider = document.getElementById('bannerSlider');
    if (!slider) return;
    const track = slider.querySelector('.banner-track');
    const slides = slider.querySelectorAll('.banner-slide');
    if (slides.length < 2) return;

    const dotsWrap = slider.querySelector('.banner-dots');
    let idx = 0, timer;

    // สร้างจุดบอกตำแหน่ง
    slides.forEach((_, i) => {
        const dot = document.createElement('button');
        if (i === 0) dot.classList.add('active');
        dot.addEventListener('click', () => go(i));
        dotsWrap.appendChild(dot);
    });
    const dots = dotsWrap.querySelectorAll('button');

    function go(i) {
        idx = (i + slides.length) % slides.length;
        track.style.transform = `translateX(-${idx * 100}%)`;
        dots.forEach((d, n) => d.classList.toggle('active', n === idx));
        restart();
    }
    function restart() { clearInterval(timer); timer = setInterval(() => go(idx + 1), 5000); }

    slider.querySelector('.next')?.addEventListener('click', () => go(idx + 1));
    slider.querySelector('.prev')?.addEventListener('click', () => go(idx - 1));
    restart();
})();

/* ============================================================
   Cookie consent (Phase 3) — จำการยอมรับใน localStorage
   ============================================================ */
(function () {
    const banner = document.getElementById('cookieBanner');
    if (!banner) return;
    const KEY = 'school-cookie-consent';
    if (!localStorage.getItem(KEY)) {
        banner.hidden = false;
    }
    document.getElementById('cookieAccept')?.addEventListener('click', function () {
        localStorage.setItem(KEY, '1');
        banner.hidden = true;
    });
})();
