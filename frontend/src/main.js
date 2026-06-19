import { createApp } from 'vue'
import router from './router'
import pinia from './stores/pinia'

import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'
import './style.css'

import App from './App.vue'

const app = createApp(App)

app.use(pinia)
app.use(router)

router.isReady().then(() => {
  app.mount('#app')
})
