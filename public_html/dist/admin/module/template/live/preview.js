"use strict";
(self["webpackChunk"] = self["webpackChunk"] || []).push([["/module/template/live/preview"],{

/***/ "./module/template/admin/live/preview.js":
/*!***********************************************!*\
  !*** ./module/template/admin/live/preview.js ***!
  \***********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.esm.js");
/* harmony import */ var _components_block_preview__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./components/block-preview */ "./module/template/admin/live/components/block-preview.vue");
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { _defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }


window.LiveEventBus = new vue__WEBPACK_IMPORTED_MODULE_1__["default"]({});
alert(1);
window.LivePreview = new vue__WEBPACK_IMPORTED_MODULE_1__["default"]({
  el: '#live-preview',
  data: {
    items: current_template_items,
    message: {
      content: '',
      type: false
    },
    onSaving: false,
    s: '',
    selectedBlockId: ''
  },
  mounted: function mounted() {
    var _this = this;
    this.$nextTick(function () {
      window.addEventListener('message', function (message) {
        var _message$data, _message$data3;
        // ...
        if (message !== null && message !== void 0 && (_message$data = message.data) !== null && _message$data !== void 0 && _message$data.action) {
          var _message$data2;
          switch (message === null || message === void 0 ? void 0 : (_message$data2 = message.data) === null || _message$data2 === void 0 ? void 0 : _message$data2.action) {
            case "set_items":
              _this.setItems(message === null || message === void 0 ? void 0 : (_message$data3 = message.data) === null || _message$data3 === void 0 ? void 0 : _message$data3.data);
              break;
            case "select-item":
              $('body,html').animate({
                scrollTop: document.getElementById("block-" + message.data.data).offsetTop
              }, 'fast');
              _this.selectedBlockId = message.data.data;
              break;
            case "save_block":
              _this.updateBlock(message.data.data.id, message.data.data.model);
              break;
          }
        }
      });
    });
  },
  created: function created() {
    var _this2 = this;
    LiveEventBus.$on('select-item', function (id) {
      _this2.selectItem(id);
    });
  },
  methods: {
    setItems: function setItems(items) {
      this[items] = items;
    },
    selectItem: function selectItem(id) {
      this.selectedBlockId = id;
      window.parent.postMessage({
        'action': 'select-item',
        data: {
          id: id
        }
      }, "*");
    },
    updateBlock: function updateBlock(id, model) {
      var _this3 = this;
      // Ajax upload model HTML
      this.$set(this.items, id, _objectSpread(_objectSpread({}, this.items[id]), {}, {
        model: model
      }));
      this.items[id].onLoading = true;
      $.ajax({
        url: '/admin/module/template/live/block-preview',
        method: 'post',
        dataType: 'json',
        data: {
          block: this.items[id].type,
          model: model
        },
        success: function success(json) {
          _this3.items[id].onLoading = false;
          if (json.preview) {
            _this3.items[id].preview = json.preview;
            $(document).trigger('preview-updated', {
              id: id,
              type: _this3.items[id].type,
              json: json
            });
          }
        },
        error: function error() {
          _this3.items[id].onLoading = false;
        }
      });
    }
  },
  components: {
    LivePreviewItem: _components_block_preview__WEBPACK_IMPORTED_MODULE_0__["default"]
  }
});
$('a').on('click', function (e) {
  e.preventDefault();
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./module/template/admin/live/components/block-preview.vue?vue&type=script&lang=js&":
/*!**********************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./module/template/admin/live/components/block-preview.vue?vue&type=script&lang=js& ***!
  \**********************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  name: 'live-preview-item',
  data: function data() {
    return {};
  },
  props: {
    items: {
      type: Object
    },
    id: {
      type: String
    },
    selectedBlockId: ''
  },
  computed: {
    block: function block() {
      var _this$items$this$id;
      return (_this$items$this$id = this.items[this.id]) !== null && _this$items$this$id !== void 0 ? _this$items$this$id : {
        nodes: []
      };
    }
  },
  methods: {
    selectBlock: function selectBlock(e) {
      e.stopPropagation();
      if (this.id === 'ROOT') return;
      window.LiveEventBus.$emit('select-item', this.id);
    }
  }
});

/***/ }),

/***/ "./scss/app.scss":
/*!***********************!*\
  !*** ./scss/app.scss ***!
  \***********************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "../../module/page/admin/scss/builder.scss":
/*!*************************************************!*\
  !*** ../../module/page/admin/scss/builder.scss ***!
  \*************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./module/template/admin/scss/live.scss":
/*!**********************************************!*\
  !*** ./module/template/admin/scss/live.scss ***!
  \**********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./module/template/admin/live/components/block-preview.vue":
/*!*****************************************************************!*\
  !*** ./module/template/admin/live/components/block-preview.vue ***!
  \*****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _block_preview_vue_vue_type_template_id_00d925e2___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./block-preview.vue?vue&type=template&id=00d925e2& */ "./module/template/admin/live/components/block-preview.vue?vue&type=template&id=00d925e2&");
/* harmony import */ var _block_preview_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./block-preview.vue?vue&type=script&lang=js& */ "./module/template/admin/live/components/block-preview.vue?vue&type=script&lang=js&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _block_preview_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _block_preview_vue_vue_type_template_id_00d925e2___WEBPACK_IMPORTED_MODULE_0__.render,
  _block_preview_vue_vue_type_template_id_00d925e2___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "module/template/admin/live/components/block-preview.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./module/template/admin/live/components/block-preview.vue?vue&type=script&lang=js&":
/*!******************************************************************************************!*\
  !*** ./module/template/admin/live/components/block-preview.vue?vue&type=script&lang=js& ***!
  \******************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_block_preview_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!../../../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./block-preview.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./module/template/admin/live/components/block-preview.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_block_preview_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./module/template/admin/live/components/block-preview.vue?vue&type=template&id=00d925e2&":
/*!************************************************************************************************!*\
  !*** ./module/template/admin/live/components/block-preview.vue?vue&type=template&id=00d925e2& ***!
  \************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_block_preview_vue_vue_type_template_id_00d925e2___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   "staticRenderFns": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_block_preview_vue_vue_type_template_id_00d925e2___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_block_preview_vue_vue_type_template_id_00d925e2___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./block-preview.vue?vue&type=template&id=00d925e2& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./module/template/admin/live/components/block-preview.vue?vue&type=template&id=00d925e2&");


/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./module/template/admin/live/components/block-preview.vue?vue&type=template&id=00d925e2&":
/*!***************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./module/template/admin/live/components/block-preview.vue?vue&type=template&id=00d925e2& ***!
  \***************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* binding */ render),
/* harmony export */   "staticRenderFns": () => (/* binding */ staticRenderFns)
/* harmony export */ });
var render = function () {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    {
      staticClass: "live-block-preview",
      class: {
        selected: _vm.selectedBlockId === _vm.id,
        selectable: _vm.id !== "ROOT",
      },
      attrs: { id: "block-" + _vm.id },
      on: { click: _vm.selectBlock },
    },
    [
      _c("div", { staticClass: "block-info" }, [
        _c("div", [_vm._v(_vm._s(_vm.block.name))]),
      ]),
      _vm._v(" "),
      _c("div", {
        staticClass: "block-preview",
        domProps: { innerHTML: _vm._s(_vm.block.preview) },
      }),
      _vm._v(" "),
      _vm.block.nodes && _vm.block.nodes.length
        ? _c(
            "div",
            { staticClass: "live-block-children" },
            _vm._l(_vm.block.nodes, function (childId, index) {
              return _c("live-preview-item", {
                key: index,
                attrs: {
                  items: _vm.items,
                  id: childId,
                  "selected-block-id": _vm.selectedBlockId,
                },
              })
            }),
            1
          )
        : _vm._e(),
    ]
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js":
/*!********************************************************************!*\
  !*** ./node_modules/vue-loader/lib/runtime/componentNormalizer.js ***!
  \********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ normalizeComponent)
/* harmony export */ });
/* globals __VUE_SSR_CONTEXT__ */

// IMPORTANT: Do NOT use ES2015 features in this file (except for modules).
// This module is a runtime utility for cleaner component module output and will
// be included in the final webpack user bundle.

function normalizeComponent (
  scriptExports,
  render,
  staticRenderFns,
  functionalTemplate,
  injectStyles,
  scopeId,
  moduleIdentifier, /* server only */
  shadowMode /* vue-cli only */
) {
  // Vue.extend constructor export interop
  var options = typeof scriptExports === 'function'
    ? scriptExports.options
    : scriptExports

  // render functions
  if (render) {
    options.render = render
    options.staticRenderFns = staticRenderFns
    options._compiled = true
  }

  // functional template
  if (functionalTemplate) {
    options.functional = true
  }

  // scopedId
  if (scopeId) {
    options._scopeId = 'data-v-' + scopeId
  }

  var hook
  if (moduleIdentifier) { // server build
    hook = function (context) {
      // 2.3 injection
      context =
        context || // cached call
        (this.$vnode && this.$vnode.ssrContext) || // stateful
        (this.parent && this.parent.$vnode && this.parent.$vnode.ssrContext) // functional
      // 2.2 with runInNewContext: true
      if (!context && typeof __VUE_SSR_CONTEXT__ !== 'undefined') {
        context = __VUE_SSR_CONTEXT__
      }
      // inject component styles
      if (injectStyles) {
        injectStyles.call(this, context)
      }
      // register component module identifier for async chunk inferrence
      if (context && context._registeredComponents) {
        context._registeredComponents.add(moduleIdentifier)
      }
    }
    // used by ssr in case component is cached and beforeCreate
    // never gets called
    options._ssrRegister = hook
  } else if (injectStyles) {
    hook = shadowMode
      ? function () {
        injectStyles.call(
          this,
          (options.functional ? this.parent : this).$root.$options.shadowRoot
        )
      }
      : injectStyles
  }

  if (hook) {
    if (options.functional) {
      // for template-only hot-reload because in that case the render fn doesn't
      // go through the normalizer
      options._injectStyles = hook
      // register for functional component in vue file
      var originalRender = options.render
      options.render = function renderWithStyleInjection (h, context) {
        hook.call(context)
        return originalRender(h, context)
      }
    } else {
      // inject component registration as beforeCreate hook
      var existing = options.beforeCreate
      options.beforeCreate = existing
        ? [].concat(existing, hook)
        : [hook]
    }
  }

  return {
    exports: scriptExports,
    options: options
  }
}


/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ __webpack_require__.O(0, ["module/template/admin/live/live","module/page/css/builder","css/app","/js/vendor"], () => (__webpack_exec__("./module/template/admin/live/preview.js"), __webpack_exec__("./scss/app.scss"), __webpack_exec__("../../module/page/admin/scss/builder.scss"), __webpack_exec__("./module/template/admin/scss/live.scss")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ }
]);