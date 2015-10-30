/// <reference path='../../../typings/typings.d.ts' />

import React = require('react/addons');
const testUtils = React.addons.TestUtils;

// To console.log stringified and pretty printed JSON. Useful for shallow rendered component for example.
export function log(value) {
  const str = JSON.stringify(value, null, 2);
  console.log(str);
}

export function render(component: React.ReactElement<any>) {
  return getRenderer(component).getRenderOutput();
}

export function getRenderer(component: React.ReactElement<any>): React.ShallowRenderer {
  const shallowRenderer = testUtils.createRenderer();
  shallowRenderer.render(component);
  return shallowRenderer;
}

export function getComponent(shallowRenderer: React.ShallowRenderer) {
  return (<any> shallowRenderer)._instance._instance;
}

/* tslint:disable max-line-length */
export function getChildren(component: React.ReactElement<any>, depth: number = 1, predicate: (child: React.ReactElement<any>) => boolean = (c) => true) {
  if (!component || !component.props || !component.props.children) {
    return null;
  }
  if (!Array.isArray(component.props.children)) {
    const prev = predicate(component.props.children) ? [component.props.children] : [];
    const item = getChildren(component.props.children, depth - 1, predicate);
    return item ? prev.concat(item) : prev;
  }
  const children = component.props.children.filter(c => c);
  return depth > 1
    ? children.reduce((prev, item) => {
      prev = predicate(item) ? prev.concat(item) : prev;
      item = getChildren(item, depth - 1, predicate);
      return item ? prev.concat(item) : prev;
    }, [])
    : children.filter(predicate);
}

export function getChildrenByClass(component: React.ReactElement<any>, className: string, depth: number = 1) {
  return getChildren(component, depth, ((c: React.ReactElement<any>) => c.props && c.props.className === className));
}

export function getChildByClass(component: React.ReactElement<any>, className: string, depth: number = 1) {
  return getChildrenByClass(component, className, depth)[0] || null;
}

export function getChildrenByType(component: React.ReactElement<any>, type: string | React.ComponentClass<any>, depth: number = 1) {
  return getChildren(component, depth, ((c: React.ReactElement<any>) => c.type === type));
}

export function getChildByType(component: React.ReactElement<any>, type: string| React.ComponentClass<any>, depth: number = 1) {
  return getChildrenByType(component, type, depth)[0] || null;
}

export function getChildrenByName(component: React.ReactElement<any>, name: string, depth: number = 1) {
  return getChildren(component, depth, ((c: React.ReactElement<any>) => c.props && c.props.name === name));
}

export function getChildByName(component: React.ReactElement<any>, name: string, depth: number = 1) {
  return getChildrenByName(component, name, depth)[0];
}

export function findRenderedDOMComponentWithId (root: React.Component<any, any>, componentId: string): React.DOMComponent<any> {
  var all = testUtils.findAllInRenderedTree(root, (inst) => {
    return testUtils.isDOMComponent(inst) && inst.props.id === componentId;
  });

  if (all.length !== 1) {
    throw new Error('Did not find exactly one match for componentId:' + componentId);
  }
  return <React.DOMComponent<any>> all[0];
}

/* tslint:disable:no-empty */
export function functionize(obj, func = function() {}) {
  var out = func;
  for (var i in obj) {
    if (obj.hasOwnProperty(i)) {
      out[i] = obj[i];
    }
  }
  return out;
}
