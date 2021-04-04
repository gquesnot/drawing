<template>
  <div>
    <table class="table table-striped">
      <tr>
        <td>Titre</td>
        <td>Description</td>
      </tr>

      <tr v-for="item in items" :key="item.id">
        <td> {{ item.titre }} </td>
        <td> {{ item.description }} </td>
        <td>
          <button class="btn btn-primary" @click="clickRow(item.id)">
            Go
          </button>
        </td>
      </tr>
    </table>
  </div>
</template>

<script>

export default {
  data () {
    return {
      items: []
    }
  },
  mounted () {
    const vm = this
    vm.$http.get('api/userData')
      .then(response => {
        console.log(response.data)
        response.data.drawings.forEach(draw => {
          vm.items.push({
            id: draw.id,
            titre: draw.title,
            description: draw.description
          })
        })
      })
  },
  methods: {
    clickRow (drawId) {
      this.$router.push('draw/' + drawId)
    }
  }
}
</script>

<style>
</style>
