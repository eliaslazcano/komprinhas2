<template>
  <q-layout view="hHh Lpr lFf">
    <q-header
      :bordered="$q.dark.isActive"
      :elevated="!$q.dark.isActive"
      :class="{'bg-dark':$q.dark.isActive}"
    >
      <q-toolbar>
        <q-btn flat dense round icon="menu" aria-label="Menu" @click="toggleLeftDrawer" />
        <q-toolbar-title>Komprinhas</q-toolbar-title>
        <q-btn
          flat
          round
          :icon="$q.dark.isActive ? 'light_mode' : 'dark_mode'"
          @click="$q.dark.toggle"
        />
      </q-toolbar>
    </q-header>

    <q-drawer
      v-model="leftDrawerOpen" show-if-above bordered>
      <q-list>
        <q-item-label header>Ferramentas</q-item-label>
        <EssentialLink v-for="link in essentialLinks" :key="link.title" v-bind="link" />
      </q-list>
    </q-drawer>

    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script>
import { defineComponent, ref } from 'vue'
import EssentialLink from 'components/EssentialLink.vue'
export default defineComponent({
  name: 'MainLayout',
  components: { EssentialLink },
  setup () {
    const essentialLinks = [
      {
        title: 'Carrinho de compras',
        caption: '',
        icon: 'shopping_cart',
        to: '/carrinhos'
      },
      {
        title: 'Lista de compras',
        caption: '',
        icon: 'format_list_bulleted',
        to: '/listas'
      },
      {
        title: 'Produtos',
        caption: '',
        icon: 'liquor',
        to: '/produtos'
      },
    ]
    const leftDrawerOpen = ref(false)
    const toggleLeftDrawer = () => leftDrawerOpen.value = !leftDrawerOpen.value
    return { essentialLinks, leftDrawerOpen, toggleLeftDrawer }
  }
})
</script>
