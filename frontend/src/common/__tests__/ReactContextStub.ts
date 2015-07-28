/// <reference path='../../../typings/tsd.d.ts' />

/* tslint:disable:variable-name */

import React = require('react');

function stubContext(BaseComponent, context) {

  if (typeof context === 'undefined' || context === null) {
    context = {};
  }

  var _contextTypes = {};
  var _context = context;

  try {
    Object.keys(_context).forEach((key) => {
      _contextTypes[key] = React.PropTypes.any;
    });
  } catch (err) {
    throw new TypeError('createdStubbedContextComponent requires an object');
  }

  var StubbedContextParent = React.createClass({
    displayName: 'StubbedContextParent',
    childContextTypes: <React.ValidationMap<any>> _contextTypes,
    getChildContext() { return _context; },
    contextTypes: <React.ValidationMap<any>> _contextTypes,

    render() {
      return <React.ReactElement<any>> React.Children.only(this.props.children);
    }
  });

  var StubbedContextHandler = React.createClass({
    displayName: 'StubbedContextHandler',
    childContextTypes: <React.ValidationMap<any>> _contextTypes,
    getChildContext() { return _context; },

    getWrappedElement() { return this._wrappedElement; },
    getWrappedParentElement() { return this._wrappedParentElement; },

    render() {
      this._wrappedElement = React.createElement(BaseComponent,
        Object.assign({}, this.state, this.props)
      );
      this._wrappedParentElement = React.createElement(
        StubbedContextParent,
        {},
        this._wrappedElement
      );
      return this._wrappedParentElement;
    }
  });

  BaseComponent.contextTypes = Object.assign({}, BaseComponent.contextTypes, _contextTypes);

  return StubbedContextHandler;
}

export = stubContext;

