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
        <q-item-label header>Menu</q-item-label>
        <EssentialLink v-for="link in linksEssenciais" :key="link.title" v-bind="link" />
        <q-item-label header>Relatórios</q-item-label>
        <EssentialLink v-for="link in linksRelatorios" :key="link.title" v-bind="link" />
      </q-list>
    </q-drawer>

    <q-page-container>
      <router-view v-slot="{ Component }">
        <transition appear mode="out-in" enter-active-class="animated fadeIn">
          <component :is="Component" />
        </transition>
      </router-view>
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
    const leftDrawerOpen = ref(false)
    const toggleLeftDrawer = () => leftDrawerOpen.value = !leftDrawerOpen.value
    const linksEssenciais = [
      {
        title: 'Carrinho de compras',
        caption: 'Acompanhe o carrinho nas compras',
        icon: 'shopping_cart',
        to: '/carrinhos'
      },
      {
        title: 'Lista de compras',
        caption: 'Planeje o que precisa comprar',
        icon: 'assignment',
        to: '/listas'
      },
      {
        title: 'Mercados',
        caption: 'Cadastro de supermercados',
        icon: 'store',
        to: '/mercados'
      },
      {
        title: 'Produtos',
        caption: 'Cadastro de produtos conhecidos',
        icon: 'liquor',
        to: '/produtos'
      },
    ]
    const linksRelatorios = [
      {
        title: 'Gastos por período',
        caption: 'Veja o quanto gastou',
        icon: 'request_quote',
        to: '/relatorios/gastos'
      }
    ]
    return { leftDrawerOpen, toggleLeftDrawer, linksEssenciais, linksRelatorios }
  }
})
</script>
