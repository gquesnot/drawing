<template>
  <div style="min-width: 90%;" class="m-auto">
    <div class="input-group mb-3">
      <div class="input-group-prepend">
        <span id="basic-addon1" class="input-group-text">Title</span>
      </div>
      <input v-model="title" type="text" class="form-control" :placeholder="title" :aria-label="title" aria-describedby="basic-addon1">
      <div class="input-group-append">
        <button class="btn btn-primary" @click="changeTitle">
          Change title
        </button>
      </div>
    </div>

    <div class="d-flex flex-row m-auto">
      <div style="position: relative">
        <canvas id="myLayout" ref="layer" width="720" height="480" class="border border-dark" style="position: absolute !important;bottom: 0;left: 0; z-index:-1; opacity: 0.5" />
        <canvas id="myCanvas" ref="can" width="720" height="480" class="border border-dark mb-5" style="z-index:1" />
      </div>

      <div id="options" style="margin-left: 10px;">
        <b-tabs content-class="mt-3">
          <b-tab title="Draw" active @click="switchToDraw">
            <button v-if="isDrawingMode" class="btn btn-info" @click="switchDrawingMode">
              Cancel drawing mode
            </button>
            <button v-else class="btn btn-info" @click="switchDrawingMode">
              Enter drawing mode
            </button>
            <br>
            <button id="clear-canvas" class="btn btn-info" @click="clearCanvas">
              Clear
            </button><br>

            <div id="drawing-mode-options">
              <div class="input-group mb-3">
                <div class="input-group-prepend">
                  <label for="drawing-mode-selector">Mode</label>
                </div>
                <select id="drawing-mode-selector" v-model="drawingBrush" class="custom-select" @change="switchDrawingBrush">
                  <option>Pencil</option>
                  <option>Circle</option>
                  <option>Spray</option>
                  <option>Pattern</option>

                  <option>hLine</option>
                  <option>vLine</option>
                  <option>square</option>
                  <option>diamond</option>
                  <option>texture</option>
                </select>
              </div>
              <label for="drawing-line-width">Line width:</label>
              <span class="info">{{ drawingWidth }}</span><input id="drawing-line-width" v-model="drawingWidth" class="form-range" type="range" min="0" max="150" @change="switchDrawingBrush"><br>

              <label for="drawing-color">Line color:</label>
              <input id="drawing-color" v-model="drawingColor" type="color" @change="changeDrawingColor"><br>

              <label for="drawing-shadow-color">Shadow color:</label>
              <input id="drawing-shadow-color" v-model="drawingShadowColor" type="color" @change="changeDrawingShadowColor"><br>

              <label for="drawing-shadow-width">Shadow width:</label>
              <span class="info">{{ drawingShadowWidth }}</span><input id="drawing-shadow-width" v-model="drawingShadowWidth" class="form-range" type="range" min="0" max="50" @change="changeDrawingShadowWidth"><br>

              <label for="drawing-shadow-offset">Shadow offset:</label>
              <span class="info">{{ drawingShadowOffset }}</span><input id="drawing-shadow-offset" v-model="drawingShadowOffset" class="form-range" type="range" min="0" max="50" @change="changeDrawingShadowOffset"><br>
              <button v-if="!hasLayout" class="btn btn-primary" @click="addLayoutCanvas">
                Add layout
              </button>
              <button v-else class="btn btn-primary" @click="removeLayout">
                remove layout
              </button>
              <div v-if="hasLayout">
                <select v-model="drawingLayout" class="custom-select" @change="switchLayer">
                  <option v-for="layer in layoutList" :key="layer.value" :value="layer.value">
                    {{ layer.text }}
                  </option>
                </select>

                <label for="customRange5" class="form-label">Opacity</label>
                <input id="customRange5" v-model="layoutOpacity" type="range" class="form-range" min="0" max="1" step="0.1" @change="changeLayoutOpacity">
              </div>
              <select v-model="selectImage" class="custom-select">
                <option v-for="image in imagesList" :key="image.id" :value="image.filePath">
                  {{ image.name }}
                </option>
              </select>
              <button class="btn btn-primary" @click="addSelectedImage">
                add this image
              </button>
            </div>
          </b-tab>
          <b-tab title="Replay" @click="switchToReplay">
            <div>
              <span class="info">{{ replayIndex }}</span><input id="drawing-line-width_1" v-model="replayIndex" class="form-range" type="range" min="0" :max="dataLength" @change="setByRange"><br>
              <div class="d-flex flex-row">
                <div v-if="replaySate === 0">
                  <p class="h1 mb-2">
                    <b-icon icon="play" @click="play" />
                  </p>
                </div>
                <div v-else>
                  <p class="h1 mb-2">
                    <b-icon icon="pause" @click="pause" />
                  </p>
                </div>
                <p class="h1 mb-2">
                  <b-icon icon="stop" @click="stop" />
                </p>
              </div>
            </div>
          </b-tab>
          <b-tab title="filter" @click="switchToFilter" />
          <b-tab title="Import" style="max-width: 300px" @click="switchToImport">
            <form id="formUpload" method="post" action="../api/uploadImages" enctype="multipart/form-data">
              <div class="flex w-full h-screen items-center justify-center text-center">
                <div class="p-12 bg-gray-100 border border-gray-300" @dragover="dragOver" @dragleave="dragLeave" @drop="dragDrop">
                  <input id="assetsFieldHandle" ref="file" type="file" multiple
                         name="fields[assetsFieldHandle][]" class="w-px h-px opacity-0 form-control overflow-hidden absolute" accept=".pdf,.jpg,.jpeg,.png" @change="fileAdded"
                  >

                  <label for="assetsFieldHandle" class="block cursor-pointer">
                    <div>
                      <span class="underline">click here</span> to upload your files
                    </div>
                  </label>
                  <ul v-if="fileList.length" v-cloak class="mt-4">
                    <button ref="file" class="ml-2 btn btn-primary" type="submit" title="send file" @click="sendForm">
                      upload
                    </button>
                  </ul>
                </div>
              </div>
              <form />
            </form>
          </b-tab>
        </b-tabs>
      </div>
    </div>
  </div>
</template>

<script>
import { fabric } from 'fabric'
import $ from 'jquery'
export default {

  data () {
    return {
      canvas: null,
      title: '',
      hasLayout: false,
      fileList: [],
      imagesList: [],
      layoutOpacity: 0.5,
      layoutList: [],
      imagePath: '',
      selectImage: '',
      drawingLayout: '',
      isDrawingMode: false,
      patternBrush: {},
      drawingBrush: 'Pencil',
      drawingWidth: 30,
      drawingColor: '#005E7A',
      drawingShadowWidth: 0,
      drawingShadowColor: '#005E7A',
      drawingShadowOffset: 0,
      stopSave: false,
      save: '',
      dataLength: 0,
      replayIndex: 0,
      replaySate: 0,
      windows : {
        height:0,
        width:0
      }

    }
  },
  mounted () {
    const vm = this
    const ref = this.$refs.can
    vm.draw_id = vm.$route.params.draw_id
    this.canvas = new fabric.Canvas(ref)
    console.log($('#myLayout').parentNode)
    vm.windows.height = $('div.container:nth-child(2)').height()
    vm.windows.width = $('div.container:nth-child(2)').width() - 390
    $('#myLayout').hide()
    vm.$http.get('../api/getDraw/' + vm.draw_id)
      .then(response => {
        console.log(response.data)
        vm.draw = response.data
        vm.title = response.data.title
        vm.canvas.setHeight((vm.windows.width / response.data.width) * response.data.height)
        vm.canvas.setWidth(vm.windows.width)

        if (response.data.data !== null) {
          vm.reloadData(response.data.data)
          vm.loadDraw()
        }

        vm.initPatternBrush()
        vm.initControl()
        vm.applyDrawingMode()
        vm.canvas.on({
          'object:modifed': vm.saveData,
          'object:added': vm.saveData,
          'object:removed': vm.saveData,
          'object:moved': vm.saveData,
          'object:scaled': vm.saveData,
          'object:rotated': vm.saveData,
          'object:skewed': vm.saveData
        })
        vm.interval = window.setInterval(() => {
          vm.loadNext()
        }, 1000)
        vm.$http.get('../api/userData')
          .then(response => {
            console.log(response.data)
            response.data.drawings.forEach(draw => {
              vm.layoutList.push({
                value: draw.id,
                text: draw.title,
                data: draw.data
              })
            })
            vm.$http.get('../api/getImageList')
              .then(response => {
                console.log('imageList', response.data)
                vm.imagesList = response.data.images
                vm.imagePath = response.data.imagePath
              })
          })
      })
  },
  methods: {
    addSelectedImage () {
      const vm = this
      if (this.selectImage !== '') {
        fabric.Image.fromURL('http://localhost/public/' + this.selectImage, function (myImg) {
          console.log('myimg', myImg.width, myImg.height)
          const img1 = myImg.set({ left: 0, top: 0, width: myImg.width, height: myImg.height })
          vm.canvas.add(img1)
        })
      }
    },
    addLayoutCanvas () {
      this.hasLayout = !this.hasLayout
      const layerRef = this.$refs.layer
      this.layoutCanvas = new fabric.Canvas(layerRef)
      $($('#myLayout')[0].parentNode).css('position', 'absolute')
      $($('#myLayout')[0].parentNode).css('left', '0')
      $($('#myLayout')[0].parentNode).css('top', '0')
      $('#myLayout').show()
    },
    removeLayout () {
      this.hasLayout = !this.hasLayout
      this.layoutCanvas.clear()
      this.layoutCanvas.dispose()
      this.layoutCanvas = null
      $('#myLayout').hide()
    },
    switchLayer () {
      const vm = this
      if (vm.drawingLayout === undefined) {
        const layerRef = vm.$refs.layer
        vm.layoutCanvas = new fabric.Canvas(layerRef)
      } else {
        vm.layoutList.forEach(elem => {
          if (elem.value === vm.drawingLayout) {
            console.log('load' + elem.text)
            vm.layoutCanvas.loadFromJSON(elem.data)
          }
        })
      }
    },
    switchToFilter () {

    },
    sendForm (e) {
      e.preventDefault()
      const form = $('#formUpload')[0]
      const formData = new FormData(form)
      const vm = this
      vm.$http.post($(form).attr('action'), formData)
        .then(response => {
          console.log('upload', response.data)
          this.imagesList = this.imagesList.concat(response.data)
        })
    },
    changeLayoutOpacity () {
      $('#myLayout').css('opacity', this.layoutOpacity)
    },
    setByRange () {
      console.log('byrange', this.replayIndex, this.dataLength)
      this.replayIndex = parseInt(this.replayIndex)
      this.dataLength = parseInt(this.dataLength)
      if (this.replayIndex > this.dataLength) {
        this.replayIndex = this.dataLength
      }
      this.loadDraw(this.replayIndex)
    },
    fileAdded () {
      this.fileList = [...this.$refs.file.files]
    },
    dragOver (event) {
      event.preventDefault()
      if (!event.currentTarget.classList.contains('bg-green-300')) {
        event.currentTarget.classList.remove('bg-gray-100')
        event.currentTarget.classList.add('bg-green-300')
      }
    },
    dragLeave (event) {
      event.currentTarget.classList.add('bg-gray-100')
      event.currentTarget.classList.remove('bg-green-300')
    },
    dragDrop (event) {
      event.preventDefault()
      this.$refs.file.files = event.dataTransfer.files
      this.fileAdded() // Trigger the onChange event manually
      // Clean up
      event.currentTarget.classList.add('bg-gray-100')
      event.currentTarget.classList.remove('bg-green-300')
    },
    play () {
      this.replaySate = 1
    },
    pause () {
      this.replaySate = 0
    },
    stop () {
      this.replaySate = 0
      this.replayIndex = 0
    },
    loadDraw (index = undefined) {
      this.stopSave = true
      if (index === undefined) {
        console.log('load', JSON.stringify(this.datas))
        this.canvas.loadFromJSON(JSON.stringify(this.datas))
      } else {
        let tmp = [...this.datas.objects]
        console.log(this.datas, index)
        console.log('lengthOfObect', tmp.length, this.datas.objects.length)
        tmp = tmp.slice(0, index + 1)
        console.log('lengthOfObect', tmp.length, this.datas.objects.length)
        this.canvas.loadFromJSON(JSON.stringify({ version: this.datas.version, objects: tmp }))
      }
      this.stopSave = false
    },
    loadNext () {
      console.log(this.replayIndex)
      const vm = this
      if (typeof this.replayIndex === 'string') {
        this.replayIndex = parseInt(this.replayIndex)
      }
      if (this.replaySate === 1) {
        this.replayIndex += 1
        if (this.replayIndex <= this.dataLength) {
          vm.loadDraw(this.replayIndex)
        } else {
          this.replayIndex = this.dataLength
          this.replaySate = 0
        }
      }
    },
    reloadData (datas) {
      const vm = this
      vm.datas = JSON.parse(datas)

      vm.dataLength = vm.datas.objects.length - 1
      console.log(vm.datas.objects, vm.dataLength)
    },
    saveData (evt) {
      const vm = this
      if (!vm.stopSave) {
        const tmpsave = JSON.stringify(this.canvas)
        vm.datas = JSON.parse(tmpsave)
        vm.dataLength = vm.datas.objects.length - 1
        vm.save = tmpsave
        vm.$http.post('../api/saveData/' + vm.draw_id, { data: tmpsave })
          .then(response => {
          })
      }
    },
    switchToImport () {
      const vm = this
      this.stopSave = true
    },
    switchToReplay () {
      const vm = this
      this.stopSave = true
      vm.loadDraw(this.replayIndex)
    },
    switchToDraw () {
      const vm = this
      this.stopSave = false
      vm.loadDraw()
      vm.replaySate = 0
    },
    changeTitle () {
      const vm = this
      vm.$http.post('../api/changeTitle/' + vm.draw_id, { title: vm.title })
        .then(response => {
          console.log(response.data.code)
        })
    },
    switchDrawingMode () {
      this.isDrawingMode = !this.isDrawingMode
      this.canvas.isDrawingMode = this.isDrawingMode
      this.applyDrawingMode()
    },
    applyDrawingMode () {
      if (this.isDrawingMode) {
        this.switchDrawingBrush()
        this.changeDrawingColor()
        this.changeDrawingWidth()
        this.changeDrawingShadowWidth()
        this.changeDrawingShadowOffset()
        this.changeDrawingShadowColor()
      }
    },
    clearCanvas () {
      this.canvas.clear()
    },
    switchDrawingBrush () {
      const vm = this
      console.log(vm.drawingBrush)
      if (vm.patternBrush[vm.drawingBrush + 'PatternBrush'] !== undefined) {
        vm.canvas.freeDrawingBrush = vm.patternBrush[vm.drawingBrush + 'PatternBrush']
      } else {
        vm.canvas.freeDrawingBrush = new fabric[vm.drawingBrush + 'Brush'](vm.canvas)
      }
      if (vm.canvas.freeDrawingBrush) {
        const brush = vm.canvas.freeDrawingBrush
        brush.color = vm.drawingColor
        if (brush.getPatternSrc) {
          // eslint-disable-next-line no-useless-call
          brush.source = brush.getPatternSrc.call(brush)
        }
        brush.width = vm.drawingWidth || 1
        brush.shadow = new fabric.Shadow({
          blur: vm.drawingShadowWidth || 0,
          offsetX: 0,
          offsetY: 0,
          affectStroke: true,
          color: vm.drawingColor
        })
      }
    },
    changeDrawingColor () {
      const brush = this.canvas.freeDrawingBrush
      brush.color = this.drawingColor
      if (brush.getPatternSrc) {
        // eslint-disable-next-line no-useless-call
        brush.source = brush.getPatternSrc.call(brush)
      }
    },
    changeDrawingShadowColor () {
      this.canvas.freeDrawingBrush.shadow.color = this.drawingShadowColor
    },
    changeDrawingWidth () {
      this.canvas.freeDrawingBrush.width = this.drawingWidth || 1
    },
    changeDrawingShadowWidth () {
      this.canvas.freeDrawingBrush.shadow.width = this.drawingShadowWidth || 0
    },
    changeDrawingShadowOffset () {
      this.canvas.freeDrawingBrush.shadow.offsetX = this.drawingShadowOffset || 0
      this.canvas.freeDrawingBrush.shadow.offsetY = this.drawingShadowOffset || 0
    },
    initControl () {
      const vm = this
      const deleteIcon = "data:image/svg+xml,%3C%3Fxml version='1.0' encoding='utf-8'%3F%3E%3C!DOCTYPE svg PUBLIC '-//W3C//DTD SVG 1.1//EN' 'http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd'%3E%3Csvg version='1.1' id='Ebene_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' width='595.275px' height='595.275px' viewBox='200 215 230 470' xml:space='preserve'%3E%3Ccircle style='fill:%23F44336;' cx='299.76' cy='439.067' r='218.516'/%3E%3Cg%3E%3Crect x='267.162' y='307.978' transform='matrix(0.7071 -0.7071 0.7071 0.7071 -222.6202 340.6915)' style='fill:white;' width='65.545' height='262.18'/%3E%3Crect x='266.988' y='308.153' transform='matrix(0.7071 0.7071 -0.7071 0.7071 398.3889 -83.3116)' style='fill:white;' width='65.544' height='262.179'/%3E%3C/g%3E%3C/svg%3E"

      const cloneIcon = "data:image/svg+xml,%3C%3Fxml version='1.0' encoding='iso-8859-1'%3F%3E%3Csvg version='1.1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' viewBox='0 0 55.699 55.699' width='100px' height='100px' xml:space='preserve'%3E%3Cpath style='fill:%23010002;' d='M51.51,18.001c-0.006-0.085-0.022-0.167-0.05-0.248c-0.012-0.034-0.02-0.067-0.035-0.1 c-0.049-0.106-0.109-0.206-0.194-0.291v-0.001l0,0c0,0-0.001-0.001-0.001-0.002L34.161,0.293c-0.086-0.087-0.188-0.148-0.295-0.197 c-0.027-0.013-0.057-0.02-0.086-0.03c-0.086-0.029-0.174-0.048-0.265-0.053C33.494,0.011,33.475,0,33.453,0H22.177 c-3.678,0-6.669,2.992-6.669,6.67v1.674h-4.663c-3.678,0-6.67,2.992-6.67,6.67V49.03c0,3.678,2.992,6.669,6.67,6.669h22.677 c3.677,0,6.669-2.991,6.669-6.669v-1.675h4.664c3.678,0,6.669-2.991,6.669-6.669V18.069C51.524,18.045,51.512,18.025,51.51,18.001z M34.454,3.414l13.655,13.655h-8.985c-2.575,0-4.67-2.095-4.67-4.67V3.414z M38.191,49.029c0,2.574-2.095,4.669-4.669,4.669H10.845 c-2.575,0-4.67-2.095-4.67-4.669V15.014c0-2.575,2.095-4.67,4.67-4.67h5.663h4.614v10.399c0,3.678,2.991,6.669,6.668,6.669h10.4 v18.942L38.191,49.029L38.191,49.029z M36.777,25.412h-8.986c-2.574,0-4.668-2.094-4.668-4.669v-8.985L36.777,25.412z M44.855,45.355h-4.664V26.412c0-0.023-0.012-0.044-0.014-0.067c-0.006-0.085-0.021-0.167-0.049-0.249 c-0.012-0.033-0.021-0.066-0.036-0.1c-0.048-0.105-0.109-0.205-0.194-0.29l0,0l0,0c0-0.001-0.001-0.002-0.001-0.002L22.829,8.637 c-0.087-0.086-0.188-0.147-0.295-0.196c-0.029-0.013-0.058-0.021-0.088-0.031c-0.086-0.03-0.172-0.048-0.263-0.053 c-0.021-0.002-0.04-0.013-0.062-0.013h-4.614V6.67c0-2.575,2.095-4.67,4.669-4.67h10.277v10.4c0,3.678,2.992,6.67,6.67,6.67h10.399 v21.616C49.524,43.26,47.429,45.355,44.855,45.355z'/%3E%3C/svg%3E%0A"

      const deleteImg = document.createElement('img')
      deleteImg.src = deleteIcon

      const cloneImg = document.createElement('img')
      cloneImg.src = cloneIcon

      fabric.Object.prototype.transparentCorners = false
      fabric.Object.prototype.cornerColor = 'blue'
      fabric.Object.prototype.cornerStyle = 'circle'
      fabric.Object.prototype.controls.deleteControl = new fabric.Control({
        x: 0.5,
        y: -0.5,
        offsetY: -16,
        offsetX: 16,
        cursorStyle: 'pointer',
        mouseUpHandler: vm.deleteObject,
        render: vm.renderIcon(deleteImg),
        cornerSize: 24
      })

      fabric.Object.prototype.controls.clone = new fabric.Control({
        x: -0.5,
        y: -0.5,
        offsetY: -16,
        offsetX: -16,
        cursorStyle: 'pointer',
        mouseUpHandler: vm.cloneObject,
        render: vm.renderIcon(cloneImg),
        cornerSize: 24
      })
    },
    renderIcon (icon) {
      return function renderIcon (ctx, left, top, styleOverride, fabricObject) {
        const size = this.cornerSize
        ctx.save()
        ctx.translate(left, top)
        ctx.rotate(fabric.util.degreesToRadians(fabricObject.angle))
        ctx.drawImage(icon, -size / 2, -size / 2, size, size)
        ctx.restore()
      }
    },
    deleteObject (eventData, transform) {
      const target = transform.target
      const canvas = target.canvas
      canvas.remove(target)
      canvas.requestRenderAll()
    },
    cloneObject (eventData, transform) {
      const target = transform.target
      const canvas = target.canvas
      target.clone(function (cloned) {
        cloned.left += 10
        cloned.top += 10
        canvas.add(cloned)
      })
    },
    initPatternBrush () {
      const vm = this
      if (fabric.PatternBrush) {
        vm.patternBrush.vLinePatternBrush = new fabric.PatternBrush(vm.canvas)
        vm.patternBrush.vLinePatternBrush.getPatternSrc = function () {
          const patternCanvas = fabric.document.createElement('canvas')
          patternCanvas.width = patternCanvas.height = 10
          const ctx = patternCanvas.getContext('2d')

          ctx.strokeStyle = this.color
          ctx.lineWidth = 5
          ctx.beginPath()
          ctx.moveTo(0, 5)
          ctx.lineTo(10, 5)
          ctx.closePath()
          ctx.stroke()

          return patternCanvas
        }

        vm.patternBrush.hLinePatternBrush = new fabric.PatternBrush(vm.canvas)
        vm.patternBrush.hLinePatternBrush.getPatternSrc = function () {
          const patternCanvas = fabric.document.createElement('canvas')
          patternCanvas.width = patternCanvas.height = 10
          const ctx = patternCanvas.getContext('2d')

          ctx.strokeStyle = this.color
          ctx.lineWidth = 5
          ctx.beginPath()
          ctx.moveTo(5, 0)
          ctx.lineTo(5, 10)
          ctx.closePath()
          ctx.stroke()

          return patternCanvas
        }

        vm.patternBrush.squarePatternBrush = new fabric.PatternBrush(vm.canvas)
        vm.patternBrush.squarePatternBrush.getPatternSrc = function () {
          const squareWidth = 10; const squareDistance = 2

          const patternCanvas = fabric.document.createElement('canvas')
          patternCanvas.width = patternCanvas.height = squareWidth + squareDistance
          const ctx = patternCanvas.getContext('2d')

          ctx.fillStyle = this.color
          ctx.fillRect(0, 0, squareWidth, squareWidth)

          return patternCanvas
        }

        vm.patternBrush.diamondPatternBrush = new fabric.PatternBrush(vm.canvas)
        vm.patternBrush.diamondPatternBrush.getPatternSrc = function () {
          const squareWidth = 10; const squareDistance = 5
          const patternCanvas = fabric.document.createElement('canvas')
          const rect = new fabric.Rect({
            width: squareWidth,
            height: squareWidth,
            angle: 45,
            fill: this.color
          })

          const canvasWidth = rect.getBoundingRect().width

          patternCanvas.width = patternCanvas.height = canvasWidth + squareDistance
          rect.set({ left: canvasWidth / 2, top: canvasWidth / 2 })

          const ctx = patternCanvas.getContext('2d')
          rect.render(ctx)

          return patternCanvas
        }

        vm.patternBrush.texturePatternBrush = new fabric.PatternBrush(vm.canvas)
        const img = new Image()
        img.src = '../assets/test.png'
        vm.patternBrush.texturePatternBrush.source = img
      }
    }
  }
}
</script>

<style>
  .cloak{
    display: none
  }
</style>
