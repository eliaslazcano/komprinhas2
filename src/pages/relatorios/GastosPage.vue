<template>
  <q-page-async :loading="loading">
    <q-table>
      <template v-slot:top>
        <div class="full-width">
          <p class="text-h6 text-weight-regular q-mb-sm">Compras realizadas</p>
          <q-form @submit.prevent="loadData()">
            <div class="row q-col-gutter-x-sm q-col-gutter-y-sm">
              <div class="col-12 col-md-6">
                <q-select
                  label="Filtrar por mercado"
                  v-model="iptFiltroMercado"
                  :options="iptFiltroMercadoOpcoesFiltrado"
                  input-debounce="250"
                  behavior="dialog"
                  @filter="fnFiltrarMercado"
                  use-input
                  outlined
                  dense
                >
                  <template v-slot:prepend>
                    <q-icon name="person" />
                  </template>
                  <template v-slot:no-option>
                    <q-item>
                      <q-item-section class="text-grey">
                        Nenhum resultado
                      </q-item-section>
                    </q-item>
                  </template>
                </q-select>
              </div>
              <div class="col-6">
                <q-input
                  v-model="iptDataInicio"
                  label="Data inicio"
                  mask="##/##/####"
                  type="tel"
                  :rules="rulesData"
                  outlined
                  dense
                >
                  <template v-slot:append>
                    <q-icon name="event" class="cursor-pointer">
                      <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                        <q-date v-model="iptDataInicio" mask="DD/MM/YYYY">
                          <div class="row items-center justify-end">
                            <q-btn v-close-popup label="Ok" color="primary" flat />
                          </div>
                        </q-date>
                      </q-popup-proxy>
                    </q-icon>
                  </template>
                </q-input>
              </div>
              <div class="col-6">
                <q-input
                  v-model="iptDataFim"
                  label="Data limite"
                  mask="##/##/####"
                  type="tel"
                  :rules="rulesData"
                  outlined
                  dense
                >
                  <template v-slot:append>
                    <q-icon name="event" class="cursor-pointer">
                      <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                        <q-date v-model="iptDataFim" mask="DD/MM/YYYY">
                          <div class="row items-center justify-end">
                            <q-btn v-close-popup label="Ok" color="primary" flat />
                          </div>
                        </q-date>
                      </q-popup-proxy>
                    </q-icon>
                  </template>
                </q-input>
              </div>
              <div class="col-12 text-center q-pa-none">
                <q-btn
                  size="sm"
                  label="Recarregar"
                  icon="refresh"
                  color="primary"
                  type="submit"
                />
              </div>
            </div>
          </q-form>
        </div>
      </template>
    </q-table>
  </q-page-async>
</template>

<script>
import {defineComponent, ref, computed} from 'vue'
import QPageAsync from 'components/QPageAsync.vue'
import moment from 'src/libs/moment'
export default defineComponent({
  components: {QPageAsync},
  setup() {
    const mercados = ref([])
    const iptFiltroMercadoOpcoes = computed(() => [{id: null, nome: 'TODOS'}, ...mercados.value.filter(i => !i.deletado_em)])
    const getMercadoNome = (id) => {
      const x = mercados.value.find(i => i.id === id)
      return x ? x.nome : ''
    }

    const rulesData = [
      v => !!v || 'Coloque a data',
      v => !!v && v.length === 10 && moment(v, 'DD/MM/YYYY').isValid() || 'Insira uma data válida',
    ]

    const tableRows = ref([])
    return {mercados, iptFiltroMercadoOpcoes, getMercadoNome, rulesData, tableRows}
  },
  data: () => ({
    loading: true,

    iptFiltroMercado: {id: null, nome: 'TODOS'},
    iptFiltroMercadoOpcoesFiltrado: [],

    iptDataInicio: moment().subtract(30,'days').format('DD/MM/YYYY'),
    iptDataFim: moment().format('DD/MM/YYYY'),
  }),
  methods: {
    async loadData() {
      try {
        const params = {table: 'mercados'}
        const {data: mercados} = await this.$api.get('/crud', {params})
        this.mercados = mercados

        const {data: gastos} = await this.$api.get('/relatorios/gastos', {params})
        this.tableRows = gastos
      } catch (e) {
        this.$router.push('/')
      } finally {
        this.loading = false
      }
    },
    fnFiltrarMercado(val, update) {
      if (!val) {
        update(() => {this.iptFiltroMercadoOpcoesFiltrado = this.iptFiltroMercadoOpcoes})
      } else {
        update(() => {
          const needle = val.toLowerCase()
          this.iptFiltroMercadoOpcoesFiltrado = this.iptFiltroMercadoOpcoes.filter(i => i.nome.toLowerCase().indexOf(needle) > -1)
        })
      }
    }
  }
})
</script>
