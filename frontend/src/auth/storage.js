export const TOKEN_STORAGE_KEY = 'campus_marketplace_token'
export const USER_STORAGE_KEY = 'campus_marketplace_user'

export function getStoredToken() {
  return sessionStorage.getItem(TOKEN_STORAGE_KEY)
}

export function getStoredUser() {
  const rawUser = sessionStorage.getItem(USER_STORAGE_KEY)

  if (!rawUser) {
    return null
  }

  try {
    return JSON.parse(rawUser)
  } catch {
    clearStoredSession()
    return null
  }
}

export function persistSession(token, user) {
  sessionStorage.setItem(TOKEN_STORAGE_KEY, token)
  sessionStorage.setItem(USER_STORAGE_KEY, JSON.stringify(user))
}

export function clearStoredSession() {
  sessionStorage.removeItem(TOKEN_STORAGE_KEY)
  sessionStorage.removeItem(USER_STORAGE_KEY)
}
