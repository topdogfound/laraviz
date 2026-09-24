import axios from 'axios'

const baseURL = window.__LARAVIZ__?.apiBase ?? '/laraviz/api'
const csrfToken = window.__LARAVIZ__?.csrfToken ?? ''

export const api = axios.create({
    baseURL,
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    },
    withCredentials: true,
})

api.interceptors.response.use(
    res => res,
    err => {
        const msg = err.response?.data?.error
            ?? err.response?.data?.message
            ?? err.message
            ?? 'Unknown error'
        return Promise.reject(new Error(msg))
    }
)
