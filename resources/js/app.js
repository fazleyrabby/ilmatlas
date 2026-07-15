import 'flowbite';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.css';
import { createIcons, icons } from 'lucide';
import './compare-tray';

window.Alpine = Alpine;
Alpine.plugin(collapse);
Alpine.start();

window.TomSelect = TomSelect;

function initTomSelect() {
    document.querySelectorAll('select[multiple]:not(.ts-ready):not(#district-filter), select[data-tomselect]:not(.ts-ready)').forEach((el) => {
        el.classList.add('ts-ready');
        new TomSelect(el, {
            plugins: el.multiple ? ['remove_button'] : [],
            persist: false,
            create: false,
            hideSelected: true,
        });
    });
}

function renderIcons() {
    createIcons({ icons });
}

document.addEventListener('DOMContentLoaded', () => {
    renderIcons();
    initTomSelect();
    initHeroVideo();
});

document.addEventListener('alpine:initialized', renderIcons);

function initHeroVideo() {
    const video = document.querySelector('.hero-video');
    if (!video) return;
    const tryPlay = () => {
        const p = video.play();
        if (p && typeof p.catch === 'function') {
            p.catch((err) => console.warn('[hero-video] autoplay prevented:', err.message));
        }
    };
    tryPlay();
    video.addEventListener('error', () => {
        console.error('[hero-video] error:', video.error && video.error.code, video.currentSrc);
    }, true);
    video.addEventListener('loadeddata', () => console.info('[hero-video] loaded, readyState', video.readyState));

    const startOnInteract = () => {
        tryPlay();
        document.removeEventListener('click', startOnInteract);
        document.removeEventListener('touchstart', startOnInteract);
    };
    document.addEventListener('click', startOnInteract, { once: true });
    document.addEventListener('touchstart', startOnInteract, { once: true });
}
