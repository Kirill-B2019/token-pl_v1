import './bootstrap';
import { createApp } from 'vue';

// Импорт Bootstrap JavaScript
import * as bootstrap from 'bootstrap';

// Создаем глобальный объект для доступа к Bootstrap из Vue компонентов
window.bootstrap = bootstrap;

// Инициализация Vue приложений
document.addEventListener('DOMContentLoaded', () => {
    // Находим все элементы с атрибутом data-vue-app
    const vueApps = document.querySelectorAll('[data-vue-app]');
    
    vueApps.forEach(element => {
        const appName = element.getAttribute('data-vue-app');
        
        // Динамический импорт компонентов
        import(`./components/${appName}.vue`).then(module => {
            const component = module.default;
            createApp(component).mount(element);
        }).catch(error => {
            console.warn(`Vue component ${appName} not found:`, error);
        });
    });
    
    // Инициализация Bootstrap компонентов (модальные окна, dropdown и т.д.)
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});

// Экспортируем для использования в других модулях
export { createApp };

