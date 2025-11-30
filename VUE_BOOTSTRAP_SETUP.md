# Vue + Bootstrap Интеграция

## Установка завершена ✅

В проект успешно добавлены:
- **Vue.js 3.4.21** - прогрессивный JavaScript фреймворк
- **Bootstrap 5.3.3** - CSS фреймворк
- **@vitejs/plugin-vue** - плагин Vite для Vue

## Структура файлов

```
resources/
├── js/
│   ├── app.js              # Главный файл с инициализацией Vue
│   ├── bootstrap.js        # Настройка axios
│   └── components/
│       ├── PackagesList.vue      # Пример компонента с Bootstrap
│       └── ExampleComponent.vue  # Демонстрационный компонент
├── css/
│   └── app.css            # Импорт Bootstrap CSS
└── views/
    ├── client/
    │   └── packages-vue.blade.php  # Пример использования
    └── examples/
        └── vue-bootstrap-example.blade.php
```

## Использование

### 1. Создание Vue компонента

Создайте файл в `resources/js/components/YourComponent.vue`:

```vue
<template>
    <div class="container">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ title }}</h5>
                <p class="card-text">{{ message }}</p>
                <button class="btn btn-primary" @click="handleClick">
                    Нажми меня
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'YourComponent',
    data() {
        return {
            title: 'Заголовок',
            message: 'Сообщение'
        };
    },
    methods: {
        handleClick() {
            alert('Кнопка нажата!');
        }
    }
};
</script>

<style scoped>
/* Ваши стили */
</style>
```

### 2. Использование в Blade шаблоне

В вашем Blade файле добавьте элемент с атрибутом `data-vue-app`:

```blade
<div id="vue-app" data-vue-app="YourComponent"></div>
```

Где `YourComponent` - это имя вашего Vue компонента (без расширения .vue).

### 3. Bootstrap компоненты

Все компоненты Bootstrap доступны напрямую в Vue шаблонах:

```vue
<template>
    <!-- Кнопки -->
    <button class="btn btn-primary">Основная</button>
    <button class="btn btn-secondary">Вторичная</button>
    
    <!-- Карточки -->
    <div class="card">
        <div class="card-header">Заголовок</div>
        <div class="card-body">Содержимое</div>
    </div>
    
    <!-- Модальные окна -->
    <div class="modal fade" :class="{ 'show': showModal }">
        <div class="modal-dialog">
            <div class="modal-content">
                <!-- Содержимое модального окна -->
            </div>
        </div>
    </div>
    
    <!-- Формы -->
    <form @submit.prevent="handleSubmit">
        <div class="mb-3">
            <label class="form-label">Имя</label>
            <input type="text" class="form-control" v-model="name">
        </div>
        <button type="submit" class="btn btn-primary">Отправить</button>
    </form>
</template>
```

### 4. Доступ к Bootstrap JavaScript

Bootstrap JavaScript доступен через глобальный объект `window.bootstrap`:

```javascript
// В Vue компоненте
methods: {
    showModal() {
        const modalElement = document.getElementById('myModal');
        const modal = new window.bootstrap.Modal(modalElement);
        modal.show();
    }
}
```

## Примеры

### Пример 1: PackagesList.vue

Компонент для отображения списка пакетов с использованием Bootstrap карточек и модальных окон.

**Использование:**
```blade
<div id="vue-packages-app" data-vue-app="PackagesList"></div>
```

### Пример 2: ExampleComponent.vue

Демонстрационный компонент с формами, кнопками и валидацией.

**Использование:**
```blade
<div id="vue-example-app" data-vue-app="ExampleComponent"></div>
```

## Команды разработки

```bash
# Запуск dev сервера с hot reload
npm run dev

# Сборка для production
npm run build
```

## Важные замечания

1. **Tailwind CSS**: Bootstrap и Tailwind CSS могут конфликтовать. Если нужен только Bootstrap, можно удалить Tailwind из `app.css`.

2. **Совместимость**: Для избежания конфликтов стилей, используйте префиксы или настройте порядок импорта CSS.

3. **Vue компоненты**: Все компоненты должны экспортировать объект с `name` и быть в формате Single File Component (.vue).

4. **Инициализация**: Vue приложения автоматически монтируются при загрузке страницы для всех элементов с атрибутом `data-vue-app`.

## Дополнительные ресурсы

- [Vue.js Документация](https://vuejs.org/)
- [Bootstrap 5 Документация](https://getbootstrap.com/docs/5.3/)
- [Vite Документация](https://vitejs.dev/)


