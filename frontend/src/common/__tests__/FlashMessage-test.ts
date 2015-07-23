/// <reference path='../../../typings/tsd.d.ts' />

import React = require('react/addons');
import FlashMessage = require('../FlashMessage.react');
import utils = require('../../common/__tests__/utils');

describe('FlashMessage', () => {
  it('renders message correctly', () => {
    const props = <FlashMessage.Props> {
      code: 'testCode',
      message: 'Test message'
    };
    const component = utils.render(React.createElement(FlashMessage, props));

    expect(component.props.className).to.contain(props.code);
    expect(component.props.children).to.deep.equal(
      React.DOM.p(null, props.message)
    );
  });

  it('doesn\'t render when code isn\'t specified', () => {
    const props = <FlashMessage.Props> {
      code: null,
      message: 'Test message'
    };
    const component = utils.render(React.createElement(FlashMessage, props));

    expect(component).to.equal(null);
  });
});

