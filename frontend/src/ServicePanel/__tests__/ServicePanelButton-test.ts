/// <reference path='../../../typings/tsd.d.ts' />

import React = require('react/addons');
import ServicePanelButton = require('../ServicePanelButton.react');
import utils = require('../../common/__tests__/utils');

const testUtils = React.addons.TestUtils;

describe('ServicePanelButton', () => {
  var onClick: Sinon.SinonSpy;
  var props: ServicePanelButton.Props;

  beforeEach((done) => {
    onClick = sinon.spy();
    props = { onClick: onClick };
    done();
  });

  it('renders correctly', () => {
    const component = utils.render(React.createElement(ServicePanelButton, props));

    expect(component.type).to.equal('button');
  });

  it('calls callback on click', () => {
    const component = testUtils.renderIntoDocument(React.createElement(ServicePanelButton, props));
    const button = testUtils.findRenderedDOMComponentWithTag(component, 'button').getDOMNode();
    React.addons.TestUtils.Simulate.click(button);
    expect(onClick).to.be.calledOnce;
  });

});
