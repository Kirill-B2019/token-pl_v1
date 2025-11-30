# Быстрый старт: Vue + Bootstrap

## Шаг 1: Установка зависимостей (уже выполнено ✅)

```bash
npm install
```

## Шаг 2: Запуск dev сервера

```bash
npm run dev
```

## Шаг 3: Использование в Blade шаблоне

Добавьте в ваш Blade файл:

```blade
<div id="my-vue-app" data-vue-app="YourComponent"></div>
```

Где `YourComponent` - имя вашего Vue компонента из `resources/js/components/`.

## Примеры готовых компонентов

### 1. PackagesList - Список пакетов
```blade
<div id="vue-packages-app" data-vue-app="PackagesList"></div>
```

### 2. ExampleComponent - Демонстрационный компонент
```blade
<div id="vue-example-app" data-vue-app="ExampleComponent"></div>
```

## Создание нового компонента

1. Создайте файл `resources/js/components/MyComponent.vue`
2. Используйте в Blade: `<div data-vue-app="MyComponent"></div>`

## Bootstrap классы доступны сразу

Все классы Bootstrap можно использовать в Vue компонентах:

```vue
<template>
    <button class="btn btn-primary">Кнопка</button>
    <div class="card">Карточка</div>
    <div class="alert alert-success">Успех</div>
</template>
```

## Готово! 🎉

Теперь вы можете использовать Vue + Bootstrap в вашем проекте.


