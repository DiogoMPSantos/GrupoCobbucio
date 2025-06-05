export default {
  getToken() {
    return localStorage.getItem('token')
  },
  setToken(token) {
    localStorage.setItem('token', token)
  },
  logout() {
    localStorage.removeItem('token')
  }
}
