<template>
  <q-page-async :loading="loading" padding>
    <q-table
      :columns="tableColumns"
      :rows="tableRows"
      :pagination="tablePagination"
      :filter="tableFilter"
      class="q-mb-xl"
      rows-per-page-label="Itens por página:"
      no-data-label="Nenhum carrinho registrado"
    >
      <template v-slot:top>
        <div class="full-width">
          <p class="text-h6 text-weight-regular q-mb-sm">Carrinhos de compras</p>
          <q-input label="Pesquisar" v-model="tableFilter" outlined dense>
            <template v-slot:prepend>
              <q-icon name="search" />
            </template>
          </q-input>
        </div>
      </template>
      <template v-slot:body-cell-mercado="props">
        <q-td :props="props">
          <router-link :to="`/carrinho/${props.row.mercado}`">{{ props.value }}</router-link>
        </q-td>
      </template>
      <template v-slot:body-cell-opcoes="props">
        <q-td :props="props">
          <q-btn
            color="orange"
            icon="edit"
            @click="editarItem(props.row.id, props.row.mercado)"
            round
            dense
            flat
          >
            <q-tooltip anchor="center left" self="center right" :offset="[10, 10]">EDITAR</q-tooltip>
          </q-btn>
          <q-btn
            color="negative"
            icon="remove_circle"
            @click="removerItem(props.row.id, props.row.mercado)"
            round
            dense
            flat
          >
            <q-tooltip anchor="center left" self="center right" :offset="[10, 10]">REMOVER PRODUTO</q-tooltip>
          </q-btn>
        </q-td>
      </template>
    </q-table>

    <q-dialog v-model="dialogEditor" @hide="resetarFormulario">
      <q-card style="width: 26rem; max-width: 100%">
        <q-form @submit.prevent="salvarItem">
          <q-toolbar>
            <q-toolbar-title>{{ iptId ? 'Editar' : 'Cadastrar' }} carrinho</q-toolbar-title>
            <q-btn flat round dense icon="close" v-close-popup />
          </q-toolbar>
          <q-card-section class="q-gutter-y-lg">
            <q-select
              label="Mercado"
              v-model="iptMercado"
              :rules="iptMercadoRules"
              :options="iptMercadoOptions"
              option-value="id"
              option-label="nome"
              input-debounce="250"
              behavior="dialog"
              @filter="fnFiltroMercado"
              use-input
              lazy-rules
              outlined
            ></q-select>
          </q-card-section>
          <q-card-actions class="justify-center">
            <q-btn dense color="negative" label="Cancelar" v-close-popup />
            <q-btn dense color="primary" label="Confirmar" type="submit"/>
          </q-card-actions>
        </q-form>
      </q-card>
    </q-dialog>

    <q-page-sticky position="bottom-right" :offset="[18, 12]">
      <q-btn fab icon="add" color="green" push @click="iptId = null; dialogEditor = true">
        <q-tooltip anchor="center left" self="center right" :offset="[10, 10]">NOVO</q-tooltip>
      </q-btn>
    </q-page-sticky>
  </q-page-async>
</template>

<script>
import {defineComponent, ref} from 'vue'
import moment from 'src/libs/moment'
import QPageAsync from 'components/QPageAsync.vue'
export default defineComponent({
  components: {QPageAsync},
  setup() {
    const mercados = ref([])
    const getMercadoNome = (id) => mercados.value.find(i => i.id === id)?.nome

    const tableColumns = [
      { field: 'id', name: 'id', label: 'ID', sortable: true, align: 'left' },
      { field: (r) => getMercadoNome(r.mercado), name: 'mercado', label: 'Mercado', sortable: true, align: 'left' },
      { field: 'criado_em', name: 'criado_em', label: 'Data', sortable: true, align: 'left', format: (v) => moment(v).format('DD/MM/YYYY HH:mm') },
      { field: 'opcoes', name: 'opcoes', label: 'Opções', sortable: false },
    ]
    const tableRows = ref([])
    const tablePagination = { sortBy: 'id', rowsPerPage: 10, descending: true }
    const tableFilter = ref('')
    return {tableColumns, tableRows, tablePagination, tableFilter,mercados, getMercadoNome}
  },
  data: () => ({
    loading: true,
    dialogEditor: false,
    iptId: null,

    iptMercado: '',
    iptMercadoRules: [v => !!v || 'Campo obrigatório'],
    iptMercadoOptions: [],
  }),
  methods: {
    async loadData(spinner = true) {
      try {
        this.loading = !!spinner

        let params = {table: 'mercados'}
        const {data: mercados} = await this.$api.get('/crud', {params})
        this.mercados = mercados

        params = {table: 'carrinhos'}
        const {data} = await this.$api.get('/crud', {params})
        this.tableRows = data

        this.loading = false
      } catch (e) {
        this.$router.push('/')
      }
    },
    async removerItem(id) {
      this.$q.dialog({
        title: 'Remover carrinho',
        message: `O carrinho ${id} será removido.`,
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
          const params = {table: 'carrinhos', id}
          await this.$api.delete('/crud', {params})
          await this.loadData(false)
          this.$q.notify({type: 'positive', message: 'Carrinho removido', timeout: 2500})
        } finally {
          dialog.hide()
        }
      })
    },
    async salvarItem() {
      const dialog = this.$q.dialog({
        message: 'Registrando',
        progress: true,
        persistent: true,
        ok: false
      })
      try {
        const data = {
          'id': this.iptId,
          'mercado': this.iptMercado.id,
        }
        if (this.iptId) await this.$api.put('/crud', {table: 'carrinhos', data})
        else await this.$api.post('/crud', {table: 'carrinhos', data})
        await this.loadData(false)
        this.dialogEditor = false
        this.$q.notify({type: 'positive', message: 'Registrado com sucesso', timeout: 2500})
      } finally {
        dialog.hide()
      }
    },
    resetarFormulario() {
      this.iptId = null
      this.iptMercado = null
    },
    editarItem(id, mercado) {
      this.iptId = id
      this.iptMercado = this.mercados.find(i => i.id === mercado)
      this.dialogEditor = true
    },
    fnFiltroMercado(val, update) {
      if (val === '') {
        update(() => {this.iptMercadoOptions = this.mercados.filter(i => !i.deletado_em)})
      } else {
        update(() => {
          const needle = val.toLowerCase()
          this.iptMercadoOptions = this.mercados.filter(i => !i.deletado_em && i.nome.toLowerCase().indexOf(needle) > -1)
        })
      }
    },
  },
  created() {
    this.loadData()
  }
})
</script>
