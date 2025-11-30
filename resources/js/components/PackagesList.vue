<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">Пакеты токенов (Vue + Bootstrap)</h2>
            </div>
        </div>
        
        <div class="row g-4" v-if="packages.length > 0">
            <div class="col-md-6 col-lg-4" v-for="pkg in packages" :key="pkg.id">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="card-title">{{ pkg.name }}</h5>
                                <p class="card-text text-muted small">{{ pkg.description }}</p>
                            </div>
                            <span v-if="pkg.discount_percentage" 
                                  class="badge bg-success">
                                -{{ pkg.discount_percentage }}%
                            </span>
                        </div>
                        
                        <dl class="row mb-3">
                            <dt class="col-6">Токенов:</dt>
                            <dd class="col-6">{{ formatNumber(pkg.token_amount, 4) }}</dd>
                            
                            <dt class="col-6">Цена:</dt>
                            <dd class="col-6">{{ formatNumber(pkg.price, 2) }} ₽</dd>
                        </dl>
                        
                        <button @click="handlePurchase(pkg)" 
                                class="btn btn-primary w-100">
                            Купить
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div v-else class="alert alert-info">
            Пакеты недоступны.
        </div>
        
        <!-- Bootstrap Modal для подтверждения покупки -->
        <div class="modal fade" :class="{ 'show': showModal }" 
             :style="{ display: showModal ? 'block' : 'none' }" 
             tabindex="-1" v-if="showModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Подтверждение покупки</h5>
                        <button type="button" class="btn-close" @click="closeModal"></button>
                    </div>
                    <div class="modal-body" v-if="selectedPackage">
                        <p>Вы хотите купить пакет <strong>{{ selectedPackage.name }}</strong>?</p>
                        <p>Цена: <strong>{{ formatNumber(selectedPackage.price, 2) }} ₽</strong></p>
                        <p>Токенов: <strong>{{ formatNumber(selectedPackage.token_amount, 4) }}</strong></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeModal">Отмена</button>
                        <button type="button" class="btn btn-primary" @click="confirmPurchase">Подтвердить</button>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="showModal" class="modal-backdrop fade show" @click="closeModal"></div>
    </div>
</template>

<script>
export default {
    name: 'PackagesList',
    data() {
        return {
            packages: [],
            showModal: false,
            selectedPackage: null,
            loading: false
        };
    },
    mounted() {
        this.loadPackages();
    },
    methods: {
        async loadPackages() {
            this.loading = true;
            try {
                // Здесь можно загрузить данные через API
                // const response = await axios.get('/api/packages');
                // this.packages = response.data;
                
                // Пример данных для демонстрации
                this.packages = [
                    {
                        id: 1,
                        name: 'Базовый пакет',
                        description: 'Начальный пакет для новых пользователей',
                        token_amount: 100.5,
                        price: 1000,
                        discount_percentage: 10
                    },
                    {
                        id: 2,
                        name: 'Стандартный пакет',
                        description: 'Популярный выбор',
                        token_amount: 500.25,
                        price: 5000,
                        discount_percentage: null
                    },
                    {
                        id: 3,
                        name: 'Премиум пакет',
                        description: 'Максимальная выгода',
                        token_amount: 1500.75,
                        price: 15000,
                        discount_percentage: 15
                    }
                ];
            } catch (error) {
                console.error('Ошибка загрузки пакетов:', error);
            } finally {
                this.loading = false;
            }
        },
        handlePurchase(pkg) {
            this.selectedPackage = pkg;
            this.showModal = true;
        },
        closeModal() {
            this.showModal = false;
            this.selectedPackage = null;
        },
        confirmPurchase() {
            if (this.selectedPackage) {
                // Здесь можно отправить запрос на покупку
                // window.location.href = `/client/packages/purchase/${this.selectedPackage.id}`;
                alert(`Покупка пакета "${this.selectedPackage.name}" подтверждена!`);
                this.closeModal();
            }
        },
        formatNumber(value, decimals = 2) {
            return parseFloat(value).toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        }
    }
};
</script>

<style scoped>
.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-5px);
}

.modal.show {
    display: block;
}
</style>

