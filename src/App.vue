<template>
  <router-view />
</template>

<script>
import { defineComponent, watch } from 'vue'
import { useQuasar } from 'quasar'
export default defineComponent({
  name: 'App',
  setup() {
    const $q = useQuasar()
    const darkStorage = localStorage.getItem('dark')
    if (darkStorage !== null) $q.dark.set(JSON.parse(darkStorage))
    else $q.dark.set(true)
    watch(() => $q.dark.isActive, val => {
      localStorage.setItem('dark', JSON.stringify(val))
    })
  }
})
</script>
