
const routes = [
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', component: () => import('pages/IndexPage.vue') },
      { path: 'mercados', component: () => import('pages/MercadosList.vue') },
      { path: 'produtos', component: () => import('pages/ProdutosList.vue') },
      { path: 'listas', component: () => import('pages/listas/ListasList.vue') },
      { path: 'lista/:id', component: () => import('pages/listas/ListaDeCompras.vue') },
      { path: 'carrinhos', component: () => import('pages/carrinhos/CarrinhosList.vue') },
      { path: 'carrinho/:id', component: () => import('pages/carrinhos/CarrinhoDeCompras.vue') },
    ]
  },

  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue')
  }
]

export default routes
