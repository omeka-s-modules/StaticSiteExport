"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.ZoomControls = void 0;
var _react = _interopRequireWildcard(require("react"));
var _AddCircleOutlineSharp = _interopRequireDefault(require("@material-ui/icons/AddCircleOutlineSharp"));
var _RemoveCircleOutlineSharp = _interopRequireDefault(require("@material-ui/icons/RemoveCircleOutlineSharp"));
var _RestoreZoomIcon = _interopRequireDefault(require("./icons/RestoreZoomIcon"));
var _MiradorMenuButton = _interopRequireDefault(require("../containers/MiradorMenuButton"));
function _interopRequireDefault(e) { return e && e.__esModule ? e : { "default": e }; }
function _getRequireWildcardCache(e) { if ("function" != typeof WeakMap) return null; var r = new WeakMap(), t = new WeakMap(); return (_getRequireWildcardCache = function _getRequireWildcardCache(e) { return e ? t : r; })(e); }
function _interopRequireWildcard(e, r) { if (!r && e && e.__esModule) return e; if (null === e || "object" != typeof e && "function" != typeof e) return { "default": e }; var t = _getRequireWildcardCache(r); if (t && t.has(e)) return t.get(e); var n = { __proto__: null }, a = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var u in e) if ("default" !== u && {}.hasOwnProperty.call(e, u)) { var i = a ? Object.getOwnPropertyDescriptor(e, u) : null; i && (i.get || i.set) ? Object.defineProperty(n, u, i) : n[u] = e[u]; } return n["default"] = e, t && t.set(e, n), n; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == typeof i ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != typeof t || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != typeof i) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _callSuper(t, o, e) { return o = _getPrototypeOf(o), _possibleConstructorReturn(t, _isNativeReflectConstruct() ? Reflect.construct(o, e || [], _getPrototypeOf(t).constructor) : o.apply(t, e)); }
function _possibleConstructorReturn(t, e) { if (e && ("object" == typeof e || "function" == typeof e)) return e; if (void 0 !== e) throw new TypeError("Derived constructors may only return object or undefined"); return _assertThisInitialized(t); }
function _assertThisInitialized(e) { if (void 0 === e) throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); return e; }
function _isNativeReflectConstruct() { try { var t = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); } catch (t) {} return (_isNativeReflectConstruct = function _isNativeReflectConstruct() { return !!t; })(); }
function _getPrototypeOf(t) { return _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function (t) { return t.__proto__ || Object.getPrototypeOf(t); }, _getPrototypeOf(t); }
function _inherits(t, e) { if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function"); t.prototype = Object.create(e && e.prototype, { constructor: { value: t, writable: !0, configurable: !0 } }), Object.defineProperty(t, "prototype", { writable: !1 }), e && _setPrototypeOf(t, e); }
function _setPrototypeOf(t, e) { return _setPrototypeOf = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function (t, e) { return t.__proto__ = e, t; }, _setPrototypeOf(t, e); }
/**
 */
var ZoomControls = exports.ZoomControls = /*#__PURE__*/function (_Component) {
  /**
   * constructor -
   */
  function ZoomControls(props) {
    var _this;
    _classCallCheck(this, ZoomControls);
    _this = _callSuper(this, ZoomControls, [props]);
    _this.handleZoomInClick = _this.handleZoomInClick.bind(_this);
    _this.handleZoomOutClick = _this.handleZoomOutClick.bind(_this);
    return _this;
  }

  /**
   * @private
   */
  _inherits(ZoomControls, _Component);
  return _createClass(ZoomControls, [{
    key: "handleZoomInClick",
    value: function handleZoomInClick() {
      var _this$props = this.props,
        windowId = _this$props.windowId,
        updateViewport = _this$props.updateViewport,
        viewer = _this$props.viewer;
      updateViewport(windowId, {
        zoom: viewer.zoom * 2
      });
    }

    /**
     * @private
     */
  }, {
    key: "handleZoomOutClick",
    value: function handleZoomOutClick() {
      var _this$props2 = this.props,
        windowId = _this$props2.windowId,
        updateViewport = _this$props2.updateViewport,
        viewer = _this$props2.viewer;
      updateViewport(windowId, {
        zoom: viewer.zoom / 2
      });
    }

    /**
     * render
     * @return
     */
  }, {
    key: "render",
    value: function render() {
      var _this$props3 = this.props,
        displayDivider = _this$props3.displayDivider,
        showZoomControls = _this$props3.showZoomControls,
        classes = _this$props3.classes,
        t = _this$props3.t,
        zoomToWorld = _this$props3.zoomToWorld;
      if (!showZoomControls) {
        return /*#__PURE__*/_react["default"].createElement(_react["default"].Fragment, null);
      }
      return /*#__PURE__*/_react["default"].createElement("div", {
        className: classes.zoom_controls
      }, /*#__PURE__*/_react["default"].createElement(_MiradorMenuButton["default"], {
        "aria-label": t('zoomIn'),
        onClick: this.handleZoomInClick
      }, /*#__PURE__*/_react["default"].createElement(_AddCircleOutlineSharp["default"], null)), /*#__PURE__*/_react["default"].createElement(_MiradorMenuButton["default"], {
        "aria-label": t('zoomOut'),
        onClick: this.handleZoomOutClick
      }, /*#__PURE__*/_react["default"].createElement(_RemoveCircleOutlineSharp["default"], null)), /*#__PURE__*/_react["default"].createElement(_MiradorMenuButton["default"], {
        "aria-label": t('zoomReset'),
        onClick: function onClick() {
          return zoomToWorld(false);
        }
      }, /*#__PURE__*/_react["default"].createElement(_RestoreZoomIcon["default"], null)), displayDivider && /*#__PURE__*/_react["default"].createElement("span", {
        className: classes.divider
      }));
    }
  }]);
}(_react.Component);
ZoomControls.defaultProps = {
  displayDivider: true,
  showZoomControls: false,
  t: function t(key) {
    return key;
  },
  updateViewport: function updateViewport() {},
  viewer: {},
  windowId: ''
};