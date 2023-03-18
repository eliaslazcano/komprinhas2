import { boot } from 'quasar/wrappers'
import { Notify } from 'quasar'
import axios from 'axios'

const api = axios.create({
  baseURL: 'http://localhost:3000',
  timeout: 90000,
  timeoutErrorMessage: 'timeout'
})

api.interceptors.response.use(value => value, error => {
  if ((navigator && navigator.onLine === false) || (error.message && error.message.toLowerCase() === 'network error')) Notify.create({ type: 'negative', message: 'Parece que você está sem conexão com a internet.'})
  else if (error.message === 'timeout') Notify.create({ type: 'negative', message: 'O processamento está tomando mais tempo do que o permitido. Sua conexão pode estar ruim.'})
  else if (error.response && error.response.data && (error.response.data.mensagem || error.response.data.msg)) Notify.create({ type: 'negative', message: error.response.data.mensagem || error.response.data.msg})
  else if (error.message) Notify.create({ type: 'negative', message: error.message})
  else if (error.code === 'ECONNABORTED') Notify.create({ type: 'negative', message: 'O processamento foi cancelado pelo dispositivo, parece haver um problema de conexão.'})
  else if (error.code === 'ERR_NETWORK') Notify.create({ type: 'negative', message: 'Seu dispositivo fracassou ao tentar estabelecer conexão.'})
  else Notify.create({ type: 'negative', message: 'Ocorreu uma falha ao tentar realizar o processamento, a conexão pode estar ruim.'})
  return Promise.reject(error)
})

export default boot(({ app }) => {
  // for use inside Vue files (Options API) through this.$axios and this.$api

  app.config.globalProperties.$axios = axios
  // ^ ^ ^ this will allow you to use this.$axios (for Vue Options API form)
  //       so you won't necessarily have to import axios in each vue file

  app.config.globalProperties.$api = api
  // ^ ^ ^ this will allow you to use this.$api (for Vue Options API form)
  //       so you can easily perform requests against your app's API
})

export { api }
