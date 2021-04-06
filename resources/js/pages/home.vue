<template>
  <div>
    <div class="d-flex flex-row">
      <div class="input-group ml-3 mb-3 w-25">
        <div class="input-group-prepend">
          <span id="basic-addon1" class="input-group-text">Title</span>
        </div>
        <input v-model="newTitle" type="text" class="form-control" placeholder="" aria-describedby="basic-addon1">
      </div>
      <div class="input-group ml-3 mb-3 w-25">
        <div class="input-group-prepend">
          <span id="basic-addon3" class="input-group-text">Ratio H/W</span>
        </div>
        <input v-model="newRatio" type="text" class="form-control" placeholder="" aria-describedby="basic-addon3">
      </div>
      <div class="w-25">
        <button class="btn btn-primary mb-3 ml-3" @click="newDraw">
          New Draw
        </button>
      </div>
    </div>
    <table class="table">
      <thead>
        <tr>
          <th scope="col">
            Titre
          </th>
          <th scope="col">
            Description
          </th>
          <th scope="col">
            Go
          </th>
          <th scope="col">
            Delete
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in items" :key="item.id">
          <td> {{ item.titre }} </td>
          <td> {{ item.description }} </td>
          <td>
            <p class="h1 mb-2">
              <b-icon icon="arrow-right-short" @click="clickDraw(item.id)" />
            </p>
          </td>
          <td>
            <p class="h1 mb-2">
              <b-icon icon="x" @click="deleteDraw(item.id)" />
            </p>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>

export default {

  data () {
    return {
      items: [],
      newTitle: '',
      newRatio: '2/3'
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
    clickDraw (drawId) {
      this.$router.push('draw/' + drawId)
    },
    newDraw () {
      const vm = this
      const widthHeight = vm.newRatio.split('/')
      vm.$http.post('../api/newDraw', { title: vm.newTitle, height: parseInt(widthHeight[0]), width: parseInt(widthHeight[1]) })
        .then(response => {
          this.$router.push('draw/' + response.data.id)
        })
    },
    deleteDraw (drawId) {
      const vm = this
      vm.$http.post('../api/deleteDraw/' + drawId)
        .then(response => {
          for (let i = 0; i < vm.items.length; i++) {
            if (vm.items[i].id === drawId) {
              vm.items.pop(i)
            }
          }
        })
    }
  }
}
</script>

<style>
</style>
