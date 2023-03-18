<template>
  <q-page-async :loading="loading" padding>
    <q-table
      :columns="tableColumns"
      :rows="tableRowsComputed"
      :pagination="tablePagination"
      :filter="tableFilter"
      class="q-mb-xl"
      rows-per-page-label="Itens por página:"
      no-data-label="Nenhum mercado registrado"
    >
      <template v-slot:top>
        <div class="full-width">
          <p class="text-h6 text-weight-regular q-mb-sm">Mercados</p>
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
            @click="editarItem(props.row.id, props.row.nome)"
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

    <q-dialog v-model="dialogEditor" @hide="resetarFormulario">
      <q-card style="width: 26rem; max-width: 100%">
        <q-form @submit.prevent="salvarItem">
          <q-toolbar>
            <q-toolbar-title>{{ iptId ? 'Editar' : 'Cadastrar' }} mercado</q-toolbar-title>
            <q-btn flat round dense icon="close" v-close-popup />
          </q-toolbar>
          <q-card-section class="q-gutter-y-lg">
            <q-input
              label="Nome"
              v-model="iptNome"
              :rules="iptNomeRules"
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

    <q-page-sticky position="bottom-right" :offset="[18, 12]">
      <q-btn fab icon="add" color="green" push @click="iptId = null; dialogEditor = true">
        <q-tooltip anchor="center left" self="center right" :offset="[10, 10]">NOVO</q-tooltip>
      </q-btn>
    </q-page-sticky>
  </q-page-async>
</template>

<script>
import {computed, defineComponent, ref} from 'vue'
import moment from 'src/libs/moment'
import QPageAsync from 'components/QPageAsync.vue'
export default defineComponent({
  components: {QPageAsync},
  setup() {
    const tableColumns = [
      { field: 'id', name: 'id', label: 'ID', sortable: true, align: 'left' },
      { field: 'nome', name: 'nome', label: 'Nome', sortable: true, align: 'left' },
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
    iptId: null,
    iptNome: '',
    iptNomeRules: [v => !!v && !!v.length > 0 || 'Campo obrigatório'],
  }),
  methods: {
    async loadData(spinner = true) {
      try {
        this.loading = !!spinner
        const params = {table: 'mercados'}
        const {data} = await this.$api.get('/crud', {params})
        this.tableRows = data
        this.loading = false
      } catch (e) {
        this.$router.push('/')
      }
    },
    async removerItem(id, nome) {
      this.$q.dialog({
        title: 'Remover mercado',
        message: `O mercado "${nome}" será removido.`,
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
          const params = {table: 'mercados', id}
          await this.$api.delete('/crud', {params})
          await this.loadData(false)
          this.$q.notify({type: 'positive', message: 'Lista removida', timeout: 2500})
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
          'nome': this.iptNome.trim().toUpperCase(),
        }
        if (this.iptId) await this.$api.put('/crud', {table: 'mercados', data})
        else await this.$api.post('/crud', {table: 'mercados', data})
        await this.loadData(false)
        this.dialogEditor = false
        this.$q.notify({type: 'positive', message: 'Registrado com sucesso', timeout: 2500})
      } finally {
        dialog.hide()
      }
    },
    resetarFormulario() {
      this.iptId = null
      this.iptNome = ''
    },
    editarItem(id, nome) {
      this.iptId = id
      this.iptNome = nome
      this.dialogEditor = true
    },
  },
  created() {
    this.loadData()
  }
})
</script>
