import './bootstrap';

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

import Chart from 'chart.js/auto';
window.Chart = Chart;

// ─── Dark Mode ────────────────────────────────────────────────────────────────
function applyTheme(theme) {
    document.documentElement.classList.toggle('dark', theme === 'dark');
}
applyTheme(localStorage.getItem('theme') || 'light');

window.toggleDarkMode = function () {
    const next = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
    localStorage.setItem('theme', next);
    applyTheme(next);
    const btn = document.getElementById('dark-mode-btn');
    if (btn) btn.innerHTML = next === 'dark' ? sunIcon() : moonIcon();
};

function moonIcon() {
    return `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>`;
}
function sunIcon() {
    return `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"></path></svg>`;
}

document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('dark-mode-btn');
    if (btn) btn.innerHTML = document.documentElement.classList.contains('dark') ? sunIcon() : moonIcon();
});

// ─── Page Progress Bar ─────────────────────────────────────────────────────────
const bar = document.getElementById('page-progress');
if (bar) {
    let w = 0;
    const iv = setInterval(() => {
        w = Math.min(w + Math.random() * 15, 90);
        bar.style.width = w + '%';
    }, 100);
    window.addEventListener('load', () => {
        clearInterval(iv);
        bar.style.width = '100%';
        setTimeout(() => bar.style.opacity = '0', 300);
    });
}

// ─── Hover Quick-View (Products table) ────────────────────────────────────────
let hoverTimer = null;
let activePopover = null;

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-product-id]').forEach(row => {
        row.addEventListener('mouseenter', e => {
            hoverTimer = setTimeout(async () => {
                const pid = row.dataset.productId;
                try {
                    const res = await fetch(`/stock/${pid}/preview`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    });
                    if (!res.ok) return;
                    const data = await res.json();
                    showPopover(row, data);
                } catch (_) {}
            }, 400);
        });
        row.addEventListener('mouseleave', () => {
            clearTimeout(hoverTimer);
            if (activePopover) { activePopover.remove(); activePopover = null; }
        });
    });
});

function showPopover(row, data) {
    if (activePopover) { activePopover.remove(); }
    const rect = row.getBoundingClientRect();
    const pop = document.createElement('div');
    pop.className = 'fixed z-[999] bg-white border border-gray-200 rounded-2xl shadow-2xl p-4 w-72 text-sm pointer-events-none';
    pop.style.top = (rect.bottom + 8 + window.scrollY) + 'px';
    pop.style.left = (rect.left + window.scrollX) + 'px';

    const typeColors = { IN: 'text-green-600', OUT: 'text-red-500', ADJUST: 'text-amber-600' };

    pop.innerHTML = `
        <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-3">Last 3 Movements</p>
        ${data.length === 0 ? '<p class="text-gray-400 text-center py-2">No movements yet.</p>' : data.map(m => `
        <div class="flex justify-between items-center py-1.5 border-b border-gray-100 last:border-0">
            <div>
                <span class="font-semibold ${typeColors[m.type] || 'text-gray-600'}">${m.type}</span>
                <span class="text-gray-400 ml-2 text-xs">${m.ago}</span>
            </div>
            <span class="font-mono font-bold ${typeColors[m.type] || ''}">${m.signed_quantity}</span>
        </div>`).join('')}
    `;
    document.body.appendChild(pop);
    activePopover = pop;
}
