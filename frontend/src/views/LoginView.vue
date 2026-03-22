<template>
  <div class="login-page">
    <div class="login-card">
      <h1>Login</h1>
      <p class="subtitle">Acesse o sistema de pedidos de viagem corporativa</p>

      <form @submit.prevent="handleLogin" class="login-form">
        <div class="form-group">
          <label for="email">E-mail</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            placeholder="Digite seu e-mail"
            required
          />
        </div>

        <div class="form-group">
          <label for="password">Senha</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            placeholder="Digite sua senha"
            required
          />
        </div>

        <button type="submit" :disabled="loading">
          {{ loading ? 'Entrando...' : 'Entrar' }}
        </button>

        <p v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </p>
      </form>

      <div class="test-users">
        <h3>Usuários de teste</h3>
        <p><strong>Admin:</strong> admin@example.com / password</p>
        <p><strong>User:</strong> user@example.com / password</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { login } from '@/services/authService'

const router = useRouter()

const loading = ref(false)
const errorMessage = ref('')

const form = reactive({
  email: '',
  password: '',
})

async function handleLogin() {
  loading.value = true
  errorMessage.value = ''

  try {
    await login(form.email, form.password)
    router.push('/dashboard')
  } catch (error) {
    errorMessage.value =
      error?.response?.data?.message || 'Não foi possível realizar o login.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f4f6f8;
  padding: 24px;
}

.login-card {
  width: 100%;
  max-width: 420px;
  background: #ffffff;
  border-radius: 12px;
  padding: 32px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}

h1 {
  margin: 0 0 8px;
  font-size: 28px;
  color: #1f2937;
  text-align: center;
}

.subtitle {
  margin: 0 0 24px;
  color: #6b7280;
  text-align: center;
  font-size: 14px;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

label {
  font-size: 14px;
  font-weight: 600;
  color: #374151;
}

input {
  height: 44px;
  padding: 0 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
  outline: none;
  transition: border-color 0.2s ease;
}

input:focus {
  border-color: #2563eb;
}

button {
  height: 44px;
  border: none;
  border-radius: 8px;
  background: #2563eb;
  color: #ffffff;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.2s ease;
}

button:hover {
  opacity: 0.92;
}

button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.error-message {
  margin: 0;
  color: #dc2626;
  font-size: 14px;
  text-align: center;
}

.test-users {
  margin-top: 24px;
  padding-top: 20px;
  border-top: 1px solid #e5e7eb;
}

.test-users h3 {
  margin: 0 0 10px;
  font-size: 15px;
  color: #111827;
}

.test-users p {
  margin: 4px 0;
  font-size: 14px;
  color: #4b5563;
}
</style>