function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
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
import React, { Component } from 'react';
import clsx from 'clsx';
import Button from '@material-ui/core/Button';
import ListItem from '@material-ui/core/ListItem';
import ListItemText from '@material-ui/core/ListItemText';
import Typography from '@material-ui/core/Typography';
import Chip from '@material-ui/core/Chip';
import SanitizedHtml from '../containers/SanitizedHtml';
import TruncatedHit from '../lib/TruncatedHit';
import { ScrollTo } from './ScrollTo';

/** */
export var SearchHit = /*#__PURE__*/function (_Component) {
  /** */
  function SearchHit(props) {
    var _this;
    _classCallCheck(this, SearchHit);
    _this = _callSuper(this, SearchHit, [props]);
    _this.handleClick = _this.handleClick.bind(_this);
    return _this;
  }

  /**
   * Announce the annotation content if the component is mounted selected
   */
  _inherits(SearchHit, _Component);
  return _createClass(SearchHit, [{
    key: "componentDidMount",
    value: function componentDidMount() {
      var selected = this.props.selected;
      if (selected) this.announceHit();
    }

    /**
     * Announce hit if the hit has been selected
     */
  }, {
    key: "componentDidUpdate",
    value: function componentDidUpdate(prevProps) {
      var selected = this.props.selected;
      if (selected && selected !== prevProps.selected) {
        this.announceHit();
      }
    }

    /** */
  }, {
    key: "handleClick",
    value: function handleClick() {
      var _this$props = this.props,
        annotation = _this$props.annotation,
        annotationId = _this$props.annotationId,
        selectAnnotation = _this$props.selectAnnotation;
      if (annotation && annotationId) selectAnnotation(annotationId);
    }

    /**
     * Pass content describing the hit to the announcer prop (intended for screen readers)
     */
  }, {
    key: "announceHit",
    value: function announceHit() {
      var _this$props2 = this.props,
        annotation = _this$props2.annotation,
        annotationLabel = _this$props2.annotationLabel,
        announcer = _this$props2.announcer,
        canvasLabel = _this$props2.canvasLabel,
        hit = _this$props2.hit,
        index = _this$props2.index,
        t = _this$props2.t,
        total = _this$props2.total;
      if (!hit) return;
      var truncatedHit = new TruncatedHit(hit, annotation);
      announcer([t('pagination', {
        current: index + 1,
        total: total
      }), canvasLabel, annotationLabel, truncatedHit.before, truncatedHit.match, truncatedHit.after].join(' '));
    }

    /** */
  }, {
    key: "render",
    value: function render() {
      var _this$props3 = this.props,
        adjacent = _this$props3.adjacent,
        annotation = _this$props3.annotation,
        annotationLabel = _this$props3.annotationLabel,
        canvasLabel = _this$props3.canvasLabel,
        classes = _this$props3.classes,
        companionWindowId = _this$props3.companionWindowId,
        containerRef = _this$props3.containerRef,
        hit = _this$props3.hit,
        focused = _this$props3.focused,
        index = _this$props3.index,
        showDetails = _this$props3.showDetails,
        selected = _this$props3.selected,
        t = _this$props3.t,
        windowSelected = _this$props3.windowSelected;
      if (focused && !selected) return null;
      var truncatedHit = focused ? hit : hit && new TruncatedHit(hit, annotation);
      var truncated = hit && truncatedHit.before !== hit.before && truncatedHit.after !== hit.after;
      var canvasLabelHtmlId = "".concat(companionWindowId, "-").concat(index);
      return /*#__PURE__*/React.createElement(ScrollTo, {
        containerRef: containerRef,
        offsetTop: 96 // offset for the height of the form above
        ,
        scrollTo: windowSelected && !focused
      }, /*#__PURE__*/React.createElement(ListItem, {
        className: clsx(classes.listItem, _defineProperty(_defineProperty(_defineProperty(_defineProperty({}, classes.adjacent, adjacent), classes.selected, selected), classes.focused, focused), classes.windowSelected, windowSelected)),
        button: !selected,
        component: "li",
        onClick: this.handleClick,
        selected: selected
      }, /*#__PURE__*/React.createElement(ListItemText, {
        primaryTypographyProps: {
          variant: 'body1'
        }
      }, /*#__PURE__*/React.createElement(Typography, {
        variant: "subtitle2",
        className: classes.subtitle
      }, /*#__PURE__*/React.createElement(Chip, {
        component: "span",
        label: index + 1,
        className: classes.hitCounter
      }), /*#__PURE__*/React.createElement("span", {
        id: canvasLabelHtmlId
      }, canvasLabel)), annotationLabel && /*#__PURE__*/React.createElement(Typography, {
        variant: "subtitle2"
      }, annotationLabel), hit && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(SanitizedHtml, {
        ruleSet: "iiif",
        htmlString: truncatedHit.before
      }), ' ', /*#__PURE__*/React.createElement("strong", null, /*#__PURE__*/React.createElement(SanitizedHtml, {
        ruleSet: "iiif",
        htmlString: truncatedHit.match
      })), ' ', /*#__PURE__*/React.createElement(SanitizedHtml, {
        ruleSet: "iiif",
        htmlString: truncatedHit.after
      }), ' ', truncated && !focused && /*#__PURE__*/React.createElement(Button, {
        className: classes.inlineButton,
        onClick: showDetails,
        color: "secondary",
        size: "small",
        "aria-describedby": canvasLabelHtmlId
      }, t('more'))), !hit && annotation && /*#__PURE__*/React.createElement(SanitizedHtml, {
        ruleSet: "iiif",
        htmlString: annotation.chars
      }))));
    }
  }]);
}(Component);
SearchHit.defaultProps = {
  adjacent: false,
  annotation: undefined,
  annotationId: undefined,
  annotationLabel: undefined,
  canvasLabel: undefined,
  classes: {},
  companionWindowId: undefined,
  containerRef: undefined,
  focused: false,
  hit: undefined,
  index: undefined,
  selectAnnotation: function selectAnnotation() {},
  selected: false,
  showDetails: function showDetails() {},
  t: function t(k) {
    return k;
  },
  total: undefined,
  windowSelected: false
};