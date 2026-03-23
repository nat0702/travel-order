<template>
  <div class="dashboard-page">
    <header class="dashboard-header">
      <div>
        <h1>Pedidos de Viagem</h1>
        <p class="subtitle">
          Olá, {{ user?.name }} ({{ user?.role }})
        </p>
      </div>

      <button class="logout-button" @click="handleLogout">
        Sair
      </button>
    </header>

    <section class="content-card">
      <div class="section-header">
        <h2>Lista de pedidos</h2>
        <button class="new-button" @click="showCreateForm = !showCreateForm">
          {{ showCreateForm ? 'Fechar formulário' : 'Novo Pedido' }}
        </button>

        <button class="refresh-button" @click="fetchTravelOrders" :disabled="loading">
          {{ loading ? 'Carregando...' : 'Atualizar' }}
        </button>
      </div>

      <p v-if="errorMessage" class="error-message">
        {{ errorMessage }}
      </p>

      <p v-if="successMessage" class="success-message">
        {{ successMessage }}
      </p>

      <p v-if="loading" class="info-message">
        Carregando pedidos...
      </p>

      <p v-else-if="travelOrders.length === 0" class="info-message">
        Nenhum pedido encontrado.
      </p>
      
      <div v-if="showCreateForm" class="create-form-card">
    <h3>Novo pedido de viagem</h3>

    <form @submit.prevent="handleCreateTravelOrder" class="create-form">
        <div class="form-group">
        <label for="destination">Destino</label>
        <input
            id="destination"
            v-model="createForm.destination"
            type="text"
            placeholder="Digite o destino"
            required
        />
        </div>

        <div class="form-group">
        <label for="departure_date">Data de ida</label>
        <input
            id="departure_date"
            v-model="createForm.departure_date"
            type="date"
            required
        />
        </div>

        <div class="form-group">
        <label for="return_date">Data de volta</label>
        <input
            id="return_date"
            v-model="createForm.return_date"
            type="date"
            required
        />
        </div>

        <div class="form-actions">
        <button type="submit" class="submit-button" :disabled="loading">
            {{ loading ? 'Salvando...' : 'Salvar pedido' }}
        </button>
        </div>
    </form>
    </div>
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
              <th v-if="user?.role === 'admin'">Ações</th>
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
              <td v-if="user?.role === 'admin'" class="actions-cell">
                <template v-if="order.status === 'solicitado'">
                    <button
                    class="approve-button"
                    @click="updateStatus(order.id, 'aprovado')"
                    :disabled="loading"
                    >
                    Aprovar
                    </button>

                    <button
                    class="cancel-button"
                    @click="updateStatus(order.id, 'cancelado')"
                    :disabled="loading"
                    >
                    Cancelar
                    </button>
                </template>

                <span v-else class="no-actions">—</span>
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
const successMessage = ref('')
const showCreateForm = ref(false)

const createForm = ref({
  destination: '',
  departure_date: '',
  return_date: '',
})

async function handleCreateTravelOrder() {
  loading.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    await api.post('/travel-orders', createForm.value)

    successMessage.value = 'Pedido criado com sucesso.'
    showCreateForm.value = false

    createForm.value = {
      destination: '',
      departure_date: '',
      return_date: '',
    }

    await fetchTravelOrders()
  } catch (error) {
    if (error?.response?.status === 422) {
      const errors = error.response.data.errors
      errorMessage.value = Object.values(errors).flat().join(' ')
    } else {
      errorMessage.value =
        error?.response?.data?.message || 'Não foi possível criar o pedido.'
    }
  } finally {
    loading.value = false
  }
}

async function fetchTravelOrders() {
  loading.value = true
  errorMessage.value = ''
  successMessage.value = ''

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

async function updateStatus(id, status) {
  loading.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    await api.patch(`/travel-orders/${id}/status`, { status })

    successMessage.value =
      status === 'aprovado'
        ? 'Pedido aprovado com sucesso.'
        : 'Pedido cancelado com sucesso.'

    await fetchTravelOrders()
  } catch (error) {
    errorMessage.value =
      error?.response?.data?.message || 'Erro ao atualizar status.'
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
.header-actions {
  display: flex;
  gap: 12px;
}

.new-button,
.submit-button {
  height: 40px;
  border: none;
  border-radius: 8px;
  padding: 0 16px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  background: #059669;
  color: #ffffff;
}

.new-button:disabled,
.submit-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.create-form-card {
  margin-bottom: 20px;
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 20px;
}

.create-form-card h3 {
  margin: 0 0 16px;
  font-size: 18px;
  color: #1f2937;
}

.create-form {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 16px;
  align-items: end;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-group label {
  font-size: 14px;
  font-weight: 600;
  color: #374151;
}

.form-group input {
  height: 40px;
  padding: 0 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
  outline: none;
}

.form-group input:focus {
  border-color: #2563eb;
}

.form-actions {
  display: flex;
  align-items: end;
}

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

.success-message {
  color: #059669;
  margin-bottom: 16px;
}

.actions-cell {
  white-space: nowrap;
}

.approve-button,
.cancel-button {
  height: 32px;
  border: none;
  border-radius: 6px;
  padding: 0 10px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  margin-right: 8px;
}

.approve-button {
  background: #16a34a;
  color: #ffffff;
}

.cancel-button {
  background: #dc2626;
  color: #ffffff;
}

.approve-button:disabled,
.cancel-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.no-actions {
  color: #9ca3af;
}
</style>