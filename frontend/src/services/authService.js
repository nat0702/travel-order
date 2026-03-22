import api from './api'

const TOKEN_KEY = 'token'
const USER_KEY = 'user'

export async function login(email, password) {
  const response = await api.post('/login', {
    email,
    password,
  })

  const { access_token, user } = response.data

  localStorage.setItem(TOKEN_KEY, access_token)
  localStorage.setItem(USER_KEY, JSON.stringify(user))

  return response.data
}

export function logout() {
  localStorage.removeItem(TOKEN_KEY)
  localStorage.removeItem(USER_KEY)
}

export function getToken() {
  return localStorage.getItem(TOKEN_KEY)
}

export function getUser() {
  const user = localStorage.getItem(USER_KEY)

  return user ? JSON.parse(user) : null
}

export function isAuthenticated() {
  return !!getToken()
}