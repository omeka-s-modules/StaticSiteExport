"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.CollectionInfo = void 0;
var _react = _interopRequireWildcard(require("react"));
var _Button = _interopRequireDefault(require("@material-ui/core/Button"));
var _Typography = _interopRequireDefault(require("@material-ui/core/Typography"));
var _ViewListSharp = _interopRequireDefault(require("@material-ui/icons/ViewListSharp"));
var _CollapsibleSection = _interopRequireDefault(require("../containers/CollapsibleSection"));
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
 * ManifestInfo
 */
var CollectionInfo = exports.CollectionInfo = /*#__PURE__*/function (_Component) {
  /** */
  function CollectionInfo(props) {
    var _this;
    _classCallCheck(this, CollectionInfo);
    _this = _callSuper(this, CollectionInfo, [props]);
    _this.openCollectionDialog = _this.openCollectionDialog.bind(_this);
    return _this;
  }

  /** */
  _inherits(CollectionInfo, _Component);
  return _createClass(CollectionInfo, [{
    key: "openCollectionDialog",
    value: function openCollectionDialog() {
      var _this$props = this.props,
        collectionPath = _this$props.collectionPath,
        showCollectionDialog = _this$props.showCollectionDialog,
        windowId = _this$props.windowId;
      var manifestId = collectionPath[collectionPath.length - 1];
      showCollectionDialog(manifestId, collectionPath.slice(0, -1), windowId);
    }

    /**
     * render
     * @return
     */
  }, {
    key: "render",
    value: function render() {
      var _this$props2 = this.props,
        collectionLabel = _this$props2.collectionLabel,
        collectionPath = _this$props2.collectionPath,
        id = _this$props2.id,
        t = _this$props2.t;
      if (collectionPath.length === 0) return null;
      return /*#__PURE__*/_react["default"].createElement(_CollapsibleSection["default"], {
        id: "".concat(id, "-collection"),
        label: t('collection')
      }, collectionLabel && /*#__PURE__*/_react["default"].createElement(_Typography["default"], {
        "aria-labelledby": "".concat(id, "-resource ").concat(id, "-resource-heading"),
        id: "".concat(id, "-resource-heading"),
        variant: "h4"
      }, collectionLabel), /*#__PURE__*/_react["default"].createElement(_Button["default"], {
        color: "primary",
        onClick: this.openCollectionDialog,
        startIcon: /*#__PURE__*/_react["default"].createElement(_ViewListSharp["default"], null)
      }, t('showCollection')));
    }
  }]);
}(_react.Component);
CollectionInfo.defaultProps = {
  collectionLabel: null,
  collectionPath: [],
  t: function t(key) {
    return key;
  },
  windowId: null
};