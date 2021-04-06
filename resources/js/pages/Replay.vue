<template>

</template>

<script>
export default {
  name: 'Replay',
  props: ['canvas', 'data'],
  data () {
    return {
      replayRange: 0,
      replayItem: 0,
      minRange: 0,
      maxRange: 0,
      index: 0,
      state: 0,
      items: []

    }
  },
  beforeUpdate () {
    this.draw_id = this.$route.params.draw_id
    const vm = this
    const allData = vm.$props.data
    if (allData.length > 0) {
      vm.items = allData.data
      vm.maxRange = vm.items.length
      console.log(vm.maxRange)
      vm.$props.canvas.loadFromJSON(vm.items[vm.index].data)
      console.log(this.$props.canvas)
      vm.interval = window.setInterval(() => {
        vm.loadNext()
      }, 1000)
    }
  },
  methods: {
    setByRange () {
      console.log(this.index)
      if (this.index > this.maxRange) {
        this.index = this.items.length - 1
      }
      this.$props.canvas.loadFromJSON(this.items[this.index].data)
    },
    play () {
      this.state = 1
    },
    pause () {
      this.state = 0
    },
    stop () {
      this.state = 0
      this.index = 0
    },
    loadNext () {
      console.log(this.index)
      if (typeof this.index === 'string') {
        this.index = parseInt(this.index)
      }
      if (this.state === 1) {
        this.index += 1
        if (this.index < this.maxRange) {
          this.$props.canvas.loadFromJSON(this.items[this.index].data)
        } else {
          this.index = this.maxRange
          this.state = 0
        }
      }
    }
  }
}
</script>

<style scoped>

</style>
