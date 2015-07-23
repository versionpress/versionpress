/// <reference path='../../../typings/tsd.d.ts' />

import React = require('react/addons');
import ProgressBar = require('../ProgressBar.react');
import utils = require('../../common/__tests__/utils');

describe('ProgressBar', () => {
  var shallowRenderer: React.ShallowRenderer;
  var instance: ProgressBar;

  beforeEach((done) => {
    shallowRenderer = utils.getRenderer(React.createElement(ProgressBar));
    instance = utils.getComponent(shallowRenderer);
    done();
  });

  it('is hidden when created', () => {
    const component = shallowRenderer.getRenderOutput();
    const bar = utils.getChildByClass(component, 'ProgressBar-bar');
    expect(bar.props.style.display).to.equal('none');
  });

  it('shows when progress is set', () => {
    instance.progress(20);
    const component = shallowRenderer.getRenderOutput();
    const bar = utils.getChildByClass(component, 'ProgressBar-bar');
    expect(bar.props.style.display).not.to.equal('none');
  });

  it('hides when progress is full', () => {
    instance.progress(100);
    const component = shallowRenderer.getRenderOutput();
    const bar = utils.getChildByClass(component, 'ProgressBar-bar');
    expect(bar.props.style.display).to.equal('none');
  });
});
