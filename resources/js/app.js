import './bootstrap';

import Alpine from 'alpinejs';
import { createIcons, icons } from 'lucide';
import Chart from 'chart.js/auto';

// Production Bridge: Expose libraries to window for legacy Blade scripts
window.Alpine = Alpine;
window.lucide = { createIcons, icons };
window.Chart = Chart;

// Global Icon Initialization
document.addEventListener('DOMContentLoaded', () => {
    createIcons({ icons });
});

// Alpine also handles it in some views - ensure sync
window.addEventListener('alpine:init', () => {
    createIcons({ icons });
});

Alpine.start();
