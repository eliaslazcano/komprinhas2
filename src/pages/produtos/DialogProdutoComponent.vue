<template>
  <q-dialog @hide="resetarFormulario">
    <q-card style="width: 26rem; max-width: 100%">
      <q-form @submit.prevent="salvarItem">
        <q-toolbar>
          <q-toolbar-title>{{ iptId ? 'Editar' : 'Cadastrar' }} produto</q-toolbar-title>
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
          <q-input
            label="Código"
            v-model="iptCodigo"
            type="tel"
            clearable
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
</template>

<script>
import {defineComponent} from 'vue'
export default defineComponent({
  props: {
    afterSave: { type: Function, default: null }
  },
  data: () => ({
    iptId: null,
    iptNome: '',
    iptNomeRules: [v => !!v && !!v.length > 0 || 'Campo obrigatório'],
    iptCodigo: '',
  }),
  methods: {
    resetarFormulario() {
      this.iptId = null
      this.iptNome = ''
      this.iptCodigo = ''
    },
    async salvarItem() {
      const dialog = this.$q.dialog({
        message: 'Registrando',
        progress: true,
        persistent: true,
        ok: false
      })
      try {
        const body = {
          'id': this.iptId,
          'nome': this.iptNome.trim().toUpperCase(),
          'codigo': this.iptCodigo ? this.iptCodigo : null
        }
        if (this.iptId) {
          await this.$api.put('/crud', {table: 'produtos', data: body})
          if (this.afterSave) await this.afterSave(this.iptId)
        } else {
          const {data} = await this.$api.post('/crud', {table: 'produtos', data})
          if (this.afterSave) await this.afterSave(data.id)
        }
        this.$emit('update:modelValue', false)
        this.$q.notify({type: 'positive', message: 'Registrado com sucesso', timeout: 2500})
      } finally {
        dialog.hide()
      }
    },
    editarItem(id, nome, codigo) {
      this.iptId = id
      this.iptNome = nome
      this.iptCodigo = codigo
      this.$emit('update:modelValue', true)
    },
  }
})
</script>
