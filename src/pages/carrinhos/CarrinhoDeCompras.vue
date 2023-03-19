<template>
  <q-page-async :loading="loading" padding>
    <q-table
      :columns="tableColumns"
      :rows="tableRows"
      :pagination="tablePagination"
      :filter="tableFilter"
      class="q-mb-xl"
      rows-per-page-label="Itens por página:"
      no-data-label="Nenhum item registrado"
      hide-pagination
    >
      <template v-slot:top>
        <div class="full-width">
          <p class="text-h6 text-weight-regular q-mb-sm">Carrinho de compras</p>
          <q-input label="Pesquisar" v-model="tableFilter" outlined dense>
            <template v-slot:prepend>
              <q-icon name="search" />
            </template>
          </q-input>
        </div>
      </template>
      <template v-slot:body-cell-opcoes="props">
        <q-td :props="props">
          <q-btn
            color="negative"
            icon="remove_circle"
            @click="removerItem(props.row.id)"
            round
            dense
            flat
          >
            <q-tooltip anchor="center left" self="center right" :offset="[10, 10]">REMOVER ITEM</q-tooltip>
          </q-btn>
        </q-td>
      </template>
      <template v-slot:bottom-row>
        <q-tr>
          <q-td colspan="6">
            <span class="text-weight-medium">Total R$ {{ totalGeral }}</span>
          </q-td>
        </q-tr>
      </template>
    </q-table>

    <q-dialog v-model="dialogEditor" @hide="resetarFormulario">
      <q-card style="width: 26rem; max-width: 100%">
        <q-form @submit.prevent="adicionarItem">
          <q-toolbar>
            <q-toolbar-title>Adicionar item</q-toolbar-title>
            <q-btn flat round dense icon="close" v-close-popup />
          </q-toolbar>
          <q-card-section class="q-gutter-y-lg">
            <q-select
              label="Produto"
              v-model="iptProduto"
              :rules="iptProdutoRules"
              :options="iptProdutoOptions"
              option-value="id"
              option-label="nome"
              input-debounce="250"
              behavior="dialog"
              @filter="fnFiltroProduto"
              use-input
              lazy-rules
              outlined
            >
              <template v-slot:append>
                <q-btn flat round icon="add_circle" color="positive" @click.stop="dialogCadastrarProduto = true"/>
              </template>
            </q-select>
            <q-input
              label="Quantidade (Un/Kg)"
              v-model="iptQuantidade"
              :rules="iptQuantidadeRules"
              type="tel"
              lazy-rules
              outlined
            />
            <q-input
              label="Preço unitário"
              v-model="iptPreco"
              :rules="iptPrecoRules"
              type="tel"
              hint="Preço por Un. ou Kg"
              lazy-rules
              outlined
            />
          </q-card-section>
          <q-card-actions class="justify-center">
            <q-btn dense color="negative" label="Cancelar" v-close-popup />
            <q-btn dense color="primary" label="Confirmar" type="submit"/>
          </q-card-actions>
        </q-form>
      </q-card>
    </q-dialog>

    <dialog-produto-component v-model="dialogCadastrarProduto" :after-save="afterCadastrarProduto"/>

    <q-page-sticky position="bottom-right" :offset="[18, 12]">
      <q-btn fab icon="add" color="green" push @click="dialogEditor = true">
        <q-tooltip anchor="center left" self="center right" :offset="[10, 10]">ADICIONAR</q-tooltip>
      </q-btn>
    </q-page-sticky>
  </q-page-async>
</template>

<script>
import {defineComponent, ref, computed} from 'vue'
import moment from 'src/libs/moment'
import QPageAsync from 'components/QPageAsync.vue'
import DialogProdutoComponent from 'pages/produtos/DialogProdutoComponent.vue'
export default defineComponent({
  components: {DialogProdutoComponent, QPageAsync},
  setup() {
    const produtos = ref([])
    const getProdutoNome = (id) => produtos.value.find(i => i.id === id)?.nome

    const tableColumns = [
      { field: (r) => getProdutoNome(r.produto), name: 'produto', label: 'Produto', sortable: true, align: 'left' },
      { field: 'quantidade', name: 'quantidade', label: 'Qtd Un.', sortable: true, align: 'left', format: (v) => v.toFixed(2) },
      { field: 'preco', name: 'preco', label: 'Preço Un.', sortable: true, align: 'left', format: (v) => `R$ ${v.toFixed(2)}` },
      { field: (r) => r.quantidade * r.preco, name: 'total', label: 'Total', sortable: true, align: 'left', format: (v) => `R$ ${v.toFixed(2)}` },
      { field: 'criado_em', name: 'criado_em', label: 'Data', sortable: true, align: 'left', format: (v) => moment(v).format('DD/MM/YYYY HH:mm') },
      { field: 'opcoes', name: 'opcoes', label: 'Opções', sortable: false },
    ]
    const tableRows = ref([])
    const tablePagination = { sortBy: 'criado_em', rowsPerPage: 0, descending: true }
    const tableFilter = ref('')
    const totalGeral = computed(() => tableRows.value.reduce((previousValue, currentValue) => {
      return previousValue + (currentValue.quantidade * currentValue.preco)
    }, 0).toFixed(2))

    const iptProdutoRules = [v => !!v || 'Campo obrigatório']
    const iptQuantidadeRules = [
      v => !!v && !!v.trim() && Number(v) > 0 || 'Informe a quantidade',
      v => !!v && !v.includes(',') || 'Use ponto ao invés de virgula'
    ]
    const iptPrecoRules = [
      v => !!v && !!v.trim() && Number(v) >= 0 || 'Informe o preço',
      v => !!v && !v.includes(',') || 'Use ponto ao invés de virgula'
    ]
    return {tableColumns, tableRows, tablePagination, tableFilter, totalGeral, produtos, iptProdutoRules, iptQuantidadeRules, iptPrecoRules}
  },
  data: () => ({
    loading: true,
    dialogEditor: false,
    dialogCadastrarProduto: false,

    iptProduto: null,
    iptProdutoOptions: [],
    iptQuantidade: '',
    iptPreco: '',
  }),
  methods: {
    async loadData(spinner = true) {
      try {
        this.loading = !!spinner

        let params = {table: 'produtos'}
        const {data: produtos} = await this.$api.get('/crud', {params})
        this.produtos = produtos

        const carrinhoId = Number(this.$route.params.id)
        params = {table: 'carrinho_itens', filter: 'carrinho', value: carrinhoId}
        const {data} = await this.$api.get('/crud', {params})
        this.tableRows = data

        this.loading = false
      } catch (e) {
        this.$router.push('/')
      }
    },
    async removerItem(id) {
      const item = this.tableRows.find(i => i.id === id)
      const produto = this.produtos.find(i => i.id === item.produto)
      this.$q.dialog({
        title: 'Remover item',
        message: `O item "${produto.nome}" será removido.`,
        html: true,
        cancel: true,
        persistent: false,
      }).onOk(async () => {
        const dialog = this.$q.dialog({
          message: 'Removendo',
          progress: true,
          persistent: true,
          ok: false
        })
        try {
          const params = {table: 'carrinho_itens', id}
          await this.$api.delete('/crud', {params})
          await this.loadData(false)
          this.$q.notify({type: 'positive', message: 'Item removido', timeout: 2500})
        } finally {
          dialog.hide()
        }
      })
    },
    async adicionarItem() {
      const dialog = this.$q.dialog({
        message: 'Registrando',
        progress: true,
        persistent: true,
        ok: false
      })
      try {
        const data = {
          'carrinho': Number(this.$route.params.id),
          'produto': this.iptProduto.id,
          'quantidade': Number(this.iptQuantidade),
          'preco': Number(this.iptPreco)
        }
        await this.$api.post('/crud', {table: 'carrinho_itens', data})
        await this.loadData(false)
        this.dialogEditor = false
        this.$q.notify({type: 'positive', message: 'Registrado com sucesso', timeout: 2500})
      } finally {
        dialog.hide()
      }
    },
    fnFiltroProduto (val, update) {
      if (val === '') {
        update(() => {this.iptProdutoOptions = this.produtos.filter(i => !i.deletado_em)})
      } else {
        update(() => {
          const needle = val.toLowerCase()
          this.iptProdutoOptions = this.produtos.filter(i => !i.deletado_em && i.nome.toLowerCase().indexOf(needle) > -1)
        })
      }
    },
    resetarFormulario() {
      this.iptProduto = null
      this.iptQuantidade = ''
      this.iptPreco = ''
    },
    async afterCadastrarProduto(id) {
      const params = {table: 'produtos'}
      const {data: produtos} = await this.$api.get('/crud', {params})
      this.produtos = produtos
      this.iptProduto = produtos.find(i => i.id === id)
    },
  },
  created() {
    this.loadData()
  }
})
</script>
