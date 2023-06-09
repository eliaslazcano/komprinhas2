<template>
  <q-page-async :loading="loading" padding>
    <q-table
      :columns="tableColumns"
      :rows="tableRowsComputed"
      :pagination="tablePagination"
      :filter="tableFilter"
      class="q-mb-xl"
      rows-per-page-label="Produtos por página:"
      no-data-label="Nenhum produto registrado"
    >
      <template v-slot:top>
        <div class="full-width">
          <p class="text-h6 text-weight-regular q-mb-sm">Produtos</p>
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
            color="orange"
            icon="edit"
            @click="$refs['dialog-editor'].editarItem(props.row.id, props.row.nome, props.row.codigo)"
            round
            dense
            flat
          >
            <q-tooltip anchor="center left" self="center right" :offset="[10, 10]">EDITAR</q-tooltip>
          </q-btn>
          <q-btn
            color="negative"
            icon="remove_circle"
            @click="removerItem(props.row.id, props.row.nome)"
            round
            dense
            flat
          >
            <q-tooltip anchor="center left" self="center right" :offset="[10, 10]">REMOVER PRODUTO</q-tooltip>
          </q-btn>
        </q-td>
      </template>
    </q-table>

    <dialog-produto-component v-model="dialogEditor" ref="dialog-editor" :after-save="() => loadData(false)" />

    <q-page-sticky position="bottom-right" :offset="[18, 12]">
      <q-btn fab icon="add" color="green" push @click="iptId = null; dialogEditor = true">
        <q-tooltip anchor="center left" self="center right" :offset="[10, 10]">NOVO</q-tooltip>
      </q-btn>
    </q-page-sticky>
  </q-page-async>
</template>

<script>
import {defineComponent, ref, computed} from 'vue'
import QPageAsync from 'components/QPageAsync.vue'
import DialogProdutoComponent from 'pages/produtos/DialogProdutoComponent.vue';
export default defineComponent({
  components: {DialogProdutoComponent, QPageAsync},
  setup() {
    const tableColumns = [
      { field: 'id', name: 'id', label: 'ID', sortable: true, align: 'left' },
      { field: 'nome', name: 'nome', label: 'Nome', sortable: true, align: 'left' },
      { field: 'codigo', name: 'codigo', label: 'Cód.', sortable: true, align: 'left' },
      { field: 'opcoes', name: 'opcoes', label: 'Opções', sortable: false },
    ]
    const tableRows = ref([])
    const tableRowsComputed = computed(() => tableRows.value.filter(i => !i.deletado_em))
    const tablePagination = { sortBy: 'nome', rowsPerPage: 10 }
    const tableFilter = ref('')
    return {tableColumns, tableRows, tableRowsComputed, tablePagination, tableFilter}
  },
  data: () => ({
    loading: true,
    dialogEditor: false,
  }),
  methods: {
    async loadData(spinner = true) {
      try {
        this.loading = !!spinner
        const params = {table: 'produtos'}
        const {data} = await this.$api.get('/crud', {params})
        this.tableRows = data
        this.loading = false
      } catch (e) {
        this.$router.push('/')
      }
    },
    async removerItem(id, nome) {
      this.$q.dialog({
        title: 'Remover produto',
        message: `O produto "${nome}" será removido.`,
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
          const params = {table: 'produtos', id}
          await this.$api.delete('/crud', {params})
          await this.loadData(false)
          this.$q.notify({type: 'positive', message: 'Produto removido', timeout: 2500})
        } finally {
          dialog.hide()
        }
      })
    },
  },
  created() {
    this.loadData()
  }
})
</script>
