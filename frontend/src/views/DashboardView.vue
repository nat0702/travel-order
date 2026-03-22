<template>
  <div class="dashboard-page">
    <header class="dashboard-header">
      <div>
        <h1>Pedidos de Viagem</h1>
        <p class="subtitle">
          Bem-vinda, {{ user?.name }} ({{ user?.role }})
        </p>
      </div>

      <button class="logout-button" @click="handleLogout">
        Sair
      </button>
    </header>

    <section class="content-card">
      <div class="section-header">
        <h2>Lista de pedidos</h2>
        <button class="refresh-button" @click="fetchTravelOrders" :disabled="loading">
          {{ loading ? 'Carregando...' : 'Atualizar' }}
        </button>
      </div>

      <p v-if="errorMessage" class="error-message">
        {{ errorMessage }}
      </p>

      <p v-if="loading" class="info-message">
        Carregando pedidos...
      </p>

      <p v-else-if="travelOrders.length === 0" class="info-message">
        Nenhum pedido encontrado.
      </p>
      

      <div v-else class="table-wrapper">

        <div class="filters">
        <select v-model="selectedStatus" @change="fetchTravelOrders">
            <option value="">Todos</option>
            <option value="solicitado">Solicitado</option>
            <option value="aprovado">Aprovado</option>
            <option value="cancelado">Cancelado</option>
        </select>
        </div>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Solicitante</th>
              <th>Destino</th>
              <th>Data de ida</th>
              <th>Data de volta</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="order in travelOrders" :key="order.id">
              <td>{{ order.id }}</td>
              <td>{{ order.user?.name ?? '—' }}</td>
              <td>{{ order.destination }}</td>
              <td>{{ formatDate(order.departure_date) }}</td>
              <td>{{ formatDate(order.return_date) }}</td>
              <td>
                <span class="status-badge" :class="order.status">
                  {{ order.status }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import { getUser, logout } from '@/services/authService'

const router = useRouter()

const user = ref(getUser())
const travelOrders = ref([])
const loading = ref(false)
const errorMessage = ref('')
const selectedStatus = ref('')

async function fetchTravelOrders() {
  loading.value = true
  errorMessage.value = ''

  try {
    const params = {}

    if (selectedStatus.value) {
      params.status = selectedStatus.value
    }

    const response = await api.get('/travel-orders', { params })

    travelOrders.value = response.data.data ?? response.data
  } catch (error) {
    errorMessage.value =
      error?.response?.data?.message || 'Não foi possível carregar os pedidos.'
  } finally {
    loading.value = false
  }
}

function handleLogout() {
  logout()
  router.push('/login')
}

function formatDate(date) {
  if (!date) return '—'

  return new Date(date).toLocaleDateString('pt-BR')
}

onMounted(() => {
  fetchTravelOrders()
})
</script>

<style scoped>
.dashboard-page {
  min-height: 100vh;
  background: #f4f6f8;
  padding: 32px;
}

.dashboard-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
  gap: 16px;
}

.dashboard-header h1 {
  margin: 0;
  font-size: 28px;
  color: #111827;
}

.subtitle {
  margin: 6px 0 0;
  color: #6b7280;
  font-size: 14px;
}

.content-card {
  background: #ffffff;
  border-radius: 14px;
  padding: 24px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
}

.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
  gap: 16px;
}

.section-header h2 {
  margin: 0;
  font-size: 20px;
  color: #1f2937;
}

.logout-button,
.refresh-button {
  height: 40px;
  border: none;
  border-radius: 8px;
  padding: 0 16px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
}

.logout-button {
  background: #111827;
  color: #ffffff;
}

.refresh-button {
  background: #2563eb;
  color: #ffffff;
}

.logout-button:disabled,
.refresh-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.error-message {
  color: #dc2626;
  margin-bottom: 16px;
}

.info-message {
  color: #6b7280;
  font-size: 14px;
}

.table-wrapper {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th,
td {
  text-align: left;
  padding: 14px 12px;
  border-bottom: 1px solid #e5e7eb;
  font-size: 14px;
}

th {
  color: #374151;
  font-weight: 700;
  background: #f9fafb;
}

td {
  color: #4b5563;
}

.status-badge {
  display: inline-block;
  padding: 6px 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
  text-transform: capitalize;
}

.status-badge.solicitado {
  background: #fef3c7;
  color: #92400e;
}

.status-badge.aprovado {
  background: #dcfce7;
  color: #166534;
}

.status-badge.cancelado {
  background: #fee2e2;
  color: #991b1b;
}

.filters {
  margin-bottom: 16px;
}

.filters select {
  height: 40px;
  padding: 0 12px;
  border-radius: 8px;
  border: 1px solid #d1d5db;
}
</style>