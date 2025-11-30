<template>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h1 class="display-4 mb-4">Vue + Bootstrap Пример</h1>
                
                <!-- Bootstrap Alert -->
                <div class="alert alert-success alert-dismissible fade show" role="alert" v-if="showAlert">
                    <strong>Успешно!</strong> Vue и Bootstrap работают вместе!
                    <button type="button" class="btn-close" @click="showAlert = false"></button>
                </div>
                
                <!-- Bootstrap Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Интерактивный компонент</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Счетчик: <strong>{{ counter }}</strong></p>
                        
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-primary" @click="increment">
                                <i class="bi bi-plus"></i> Увеличить
                            </button>
                            <button type="button" class="btn btn-secondary" @click="decrement">
                                <i class="bi bi-dash"></i> Уменьшить
                            </button>
                            <button type="button" class="btn btn-danger" @click="reset">
                                <i class="bi bi-arrow-clockwise"></i> Сбросить
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Bootstrap Form -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Форма с валидацией</h5>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="handleSubmit">
                            <div class="mb-3">
                                <label for="name" class="form-label">Имя</label>
                                <input type="text" 
                                       class="form-control" 
                                       :class="{ 'is-invalid': errors.name }"
                                       id="name" 
                                       v-model="form.name"
                                       placeholder="Введите ваше имя">
                                <div class="invalid-feedback" v-if="errors.name">
                                    {{ errors.name }}
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" 
                                       class="form-control" 
                                       :class="{ 'is-invalid': errors.email }"
                                       id="email" 
                                       v-model="form.email"
                                       placeholder="example@mail.com">
                                <div class="invalid-feedback" v-if="errors.email">
                                    {{ errors.email }}
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-success" :disabled="loading">
                                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                Отправить
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ExampleComponent',
    data() {
        return {
            counter: 0,
            showAlert: true,
            loading: false,
            form: {
                name: '',
                email: ''
            },
            errors: {}
        };
    },
    methods: {
        increment() {
            this.counter++;
        },
        decrement() {
            this.counter--;
        },
        reset() {
            this.counter = 0;
        },
        validateForm() {
            this.errors = {};
            
            if (!this.form.name) {
                this.errors.name = 'Имя обязательно для заполнения';
            }
            
            if (!this.form.email) {
                this.errors.email = 'Email обязателен для заполнения';
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email)) {
                this.errors.email = 'Некорректный email адрес';
            }
            
            return Object.keys(this.errors).length === 0;
        },
        async handleSubmit() {
            if (!this.validateForm()) {
                return;
            }
            
            this.loading = true;
            
            // Имитация отправки формы
            setTimeout(() => {
                alert(`Форма отправлена!\nИмя: ${this.form.name}\nEmail: ${this.form.email}`);
                this.form = { name: '', email: '' };
                this.errors = {};
                this.loading = false;
            }, 1000);
        }
    }
};
</script>

<style scoped>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.btn-group .btn {
    margin-right: 0.25rem;
}

.btn-group .btn:last-child {
    margin-right: 0;
}
</style>


