<template>
    <div v-if="unreadNotifications.length" class="notifications-container">
    <div
      v-for="notification in unreadNotifications"
      :key="notification.id"
      class="notification-toast"
    >
      <div class="notification-content">
        <p>{{ notification.data.message }}</p>
        <button
          class="close-notification-button"
          @click="markNotificationAsRead(notification.id)"
        >
          Fechar
        </button>
      </div>
    </div>
  </div>
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
import { computed, onMounted, ref } from 'vue'
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
const notifications = ref([])

const createForm = ref({
  destination: '',
  departure_date: '',
  return_date: '',
})

const unreadNotifications = computed(() =>
  notifications.value.filter((notification) => !notification.read_at)
)

async function fetchNotifications() {
  try {
    const response = await api.get('/notifications')
    notifications.value = response.data.data ?? []
  } catch (error) {
    console.error('Erro ao carregar notificações:', error)
  }
}

async function markNotificationAsRead(notificationId) {
  try {
    await api.patch(`/notifications/${notificationId}/read`)
    notifications.value = notifications.value.filter(
      (notification) => notification.id !== notificationId
    )
  } catch (error) {
    console.error('Erro ao marcar notificação como lida:', error)
  }
}

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
  fetchNotifications()
})
</script>

