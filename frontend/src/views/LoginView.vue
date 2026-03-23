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

