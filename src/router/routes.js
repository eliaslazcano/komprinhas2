
const routes = [
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', component: () => import('pages/IndexPage.vue') },
      { path: 'mercados', component: () => import('pages/MercadosList.vue') },
      { path: 'produtos', component: () => import('pages/ProdutosList.vue') },
      { path: 'listas', component: () => import('pages/listas/ListasList.vue') },
      { path: 'carrinhos', component: () => import('pages/carrinhos/CarrinhosList.vue') },
    ]
  },

  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue')
  }
]

export default routes
