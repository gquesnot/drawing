<template>
  <div class="d-flex flex-row">
    <canvas id="myCanvas" ref="can" width="720px" height="480px" class="w-75 border border-dark" />

    <div style="display: inline-block; margin-left: 10px" class="w-25">
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
      isDrawingMode: false,
      patternBrush: {},
      drawingBrush: '',
      drawingWidth: 30,
      drawingColor: '#005E7A',
      drawingShadowWidth: 0,
      drawingShadowColor: '#005E7A',
      drawingShadowOffset: 0,
      save: ''
    }
  },
  mounted () {
    const vm = this
    const ref = this.$refs.can
    vm.draw_id = vm.$route.params.draw_id
    this.canvas = new fabric.Canvas(ref)
    const rect = new fabric.Rect({
      fill: 'red',
      width: 30,
      height: 30
    })
    vm.canvas.add(rect)
    vm.$http.get('../api/getData/' + vm.draw_id)
      .then(response => {
        if (response.data.length !== 0) {
          vm.save = response.data.data
          vm.canvas.loadFromJSON(response.data.data)
        } else {
          const rect = new fabric.Rect({
            fill: 'red',
            width: 30,
            height: 30
          })
          vm.canvas.add(rect)
        }
        vm.initPatternBrush()
        vm.applyDrawingMode()
        window.setInterval(() => {
          vm.saveData()
        }, 1000)
      })
  },
  methods: {
    saveData () {
      const vm = this
      const tmpsave = JSON.stringify(this.canvas)
      if (vm.save !== tmpsave) {
        vm.save = tmpsave
        vm.$http.post('../api/addData/' + vm.draw_id, { data: tmpsave })
          .then(response => {
          })
      }
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
</style>
