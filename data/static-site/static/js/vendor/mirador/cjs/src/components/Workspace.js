"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.Workspace = void 0;
var _react = _interopRequireDefault(require("react"));
var _classnames = _interopRequireDefault(require("classnames"));
var _Grid = _interopRequireDefault(require("@material-ui/core/Grid"));
var _Typography = _interopRequireDefault(require("@material-ui/core/Typography"));
var _Window = _interopRequireDefault(require("../containers/Window"));
var _WorkspaceMosaic = _interopRequireDefault(require("../containers/WorkspaceMosaic"));
var _WorkspaceElastic = _interopRequireDefault(require("../containers/WorkspaceElastic"));
var _cssNs = _interopRequireDefault(require("../config/css-ns"));
var _IIIFDropTarget = require("./IIIFDropTarget");
function _interopRequireDefault(e) { return e && e.__esModule ? e : { "default": e }; }
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
 * Represents a work area that contains any number of windows
 * @memberof Workspace
 * @private
 */
var Workspace = exports.Workspace = /*#__PURE__*/function (_React$Component) {
  /** */
  function Workspace(props) {
    var _this;
    _classCallCheck(this, Workspace);
    _this = _callSuper(this, Workspace, [props]);
    _this.handleDrop = _this.handleDrop.bind(_this);
    return _this;
  }

  /** */
  _inherits(Workspace, _React$Component);
  return _createClass(Workspace, [{
    key: "handleDrop",
    value: function handleDrop(_ref, props, monitor) {
      var canvasId = _ref.canvasId,
        manifestId = _ref.manifestId,
        manifestJson = _ref.manifestJson;
      var _this$props = this.props,
        addWindow = _this$props.addWindow,
        allowNewWindows = _this$props.allowNewWindows;
      if (!allowNewWindows) return;
      addWindow({
        canvasId: canvasId,
        manifest: manifestJson,
        manifestId: manifestId
      });
    }

    /**
     * Determine which workspace to render by configured type
     */
  }, {
    key: "workspaceByType",
    value: function workspaceByType() {
      var _this$props2 = this.props,
        workspaceId = _this$props2.workspaceId,
        workspaceType = _this$props2.workspaceType,
        windowIds = _this$props2.windowIds;
      if (this.maximizedWindows()) {
        return this.maximizedWindows();
      }
      if (windowIds.length === 0) return this.zeroWindows();
      switch (workspaceType) {
        case 'elastic':
          return /*#__PURE__*/_react["default"].createElement(_WorkspaceElastic["default"], null);
        case 'mosaic':
          return /*#__PURE__*/_react["default"].createElement(_WorkspaceMosaic["default"], null);
        default:
          return windowIds.map(function (windowId) {
            return /*#__PURE__*/_react["default"].createElement(_Window["default"], {
              key: "".concat(windowId, "-").concat(workspaceId),
              windowId: windowId
            });
          });
      }
    }

    /** */
  }, {
    key: "zeroWindows",
    value: function zeroWindows() {
      var t = this.props.t;
      return /*#__PURE__*/_react["default"].createElement(_Grid["default"], {
        alignItems: "center",
        container: true,
        style: {
          height: '100%'
        }
      }, /*#__PURE__*/_react["default"].createElement(_Grid["default"], {
        xs: 12,
        item: true
      }, /*#__PURE__*/_react["default"].createElement(_Typography["default"], {
        variant: "h1",
        component: "div",
        align: "center"
      }, t('welcome'))));
    }

    /**
     * Determine whether or not there are maximized windows
     */
  }, {
    key: "maximizedWindows",
    value: function maximizedWindows() {
      var _this$props3 = this.props,
        maximizedWindowIds = _this$props3.maximizedWindowIds,
        workspaceId = _this$props3.workspaceId;
      if (maximizedWindowIds.length > 0) {
        return maximizedWindowIds.map(function (windowId) {
          return /*#__PURE__*/_react["default"].createElement(_Window["default"], {
            key: "".concat(windowId, "-").concat(workspaceId),
            windowId: windowId,
            className: (0, _classnames["default"])((0, _cssNs["default"])('workspace-maximized-window'))
          });
        });
      }
      return false;
    }

    /**
     * render
     */
  }, {
    key: "render",
    value: function render() {
      var _this$props4 = this.props,
        classes = _this$props4.classes,
        isWorkspaceControlPanelVisible = _this$props4.isWorkspaceControlPanelVisible,
        t = _this$props4.t;
      return /*#__PURE__*/_react["default"].createElement(_IIIFDropTarget.IIIFDropTarget, {
        onDrop: this.handleDrop
      }, /*#__PURE__*/_react["default"].createElement("div", {
        className: (0, _classnames["default"])((0, _cssNs["default"])('workspace-viewport'), isWorkspaceControlPanelVisible && (0, _cssNs["default"])('workspace-with-control-panel'), isWorkspaceControlPanelVisible && classes.workspaceWithControlPanel, classes.workspaceViewport)
      }, /*#__PURE__*/_react["default"].createElement(_Typography["default"], {
        variant: "srOnly",
        component: "h1"
      }, t('miradorViewer')), this.workspaceByType()));
    }
  }]);
}(_react["default"].Component);
Workspace.defaultProps = {
  addWindow: function addWindow() {},
  allowNewWindows: true,
  maximizedWindowIds: [],
  windowIds: []
};