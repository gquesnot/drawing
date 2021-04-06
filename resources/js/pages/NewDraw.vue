<template>
  <div class="centerx con-exemple-prompt">
    <!-- <vs-button @click="openPrompt" color="primary" type="border">Run prompt</vs-button> -->
    <div class="modelx">
      {{ val == null ? 'null' : val }}
    </div>
    <div class="centerx">

    </div>
    <vs-prompt
      :active.sync="activePrompt"
      @cancel="val=''"
    >
      <div class="con-exemple-prompt mt-10 mb-10">
        <template lang="html">
          <h5>HELLO</h5>
        </template>
      </div>
    </vs-prompt>
  </div>
</template>

<script>

export default {

  data () {
    return {
      activePrompt: false,
      val: '',
      valMultipe: {
        value1: '',
        value2: ''
      }

    }
  },
  mounted () {
  },
  methods: {
    close () {
      this.$vs.notify({
        color: 'danger',
        title: 'Closed',
        text: 'You close a dialog!'
      })
      console.log('DIALOG CLOSED')
    },
    openConfirm () {
      this.$vs.dialog({
        type: 'confirm',
        color: 'primary',
        title: 'Confirm',
        text: 'êtes-vous sur de vouloir ouvrir détail Commande ?',
        accept: this.acceptAlert

      })
    },
    acceptAlert (e) {
      const vm = this
      vm.activePrompt = true
      setTimeout(function () {
        const dialog = document.querySelector('.vs-dialog')
        dialog.setAttribute('style', 'position:relative !important; top:0 !important;')
        dialog.style.cssText = 'position:relative !important; top:0 !important;'
      }, 1)
      console.log('THIS', vm)
    }
  }
}
</script>

<style scoped>

</style>
