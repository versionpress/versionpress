/// <reference path='../../../typings/typings.d.ts' />

import React = require('react/addons');
import ServicePanel = require('../ServicePanel.react');
import utils = require('../../common/__tests__/utils');

const testUtils = React.addons.TestUtils;

describe('ServicePanel', () => {
  var onSubmit: Sinon.SinonSpy;
  var props: ServicePanel.Props;

  beforeEach((done) => {
    onSubmit = sinon.spy();
    props = {
      display: true,
      onSubmit: onSubmit
    };
    done();
  });

  it('renders the form correctly', () => {
    const component = utils.render(React.createElement(ServicePanel, props));
    const forms = utils.getChildrenByType(component, 'form');
    expect(forms.length).to.equal(1);

    const form = forms[0];
    expect(utils.getChildren(form, 3, (c => c.type === 'input')).length).to.equal(2);
    expect(utils.getChildByName(form, 'email', 3)).to.exist;
    expect(utils.getChildByName(form, 'description', 3)).to.exist;
  });

  it('submits the form correctly', () => {
    const component = testUtils.renderIntoDocument(React.createElement(ServicePanel, props));
    const values = {
      email: 'test@email.com',
      description: 'Test description.'
    };

    const form = testUtils.findRenderedDOMComponentWithTag(component, 'form').getDOMNode();
    const email: any = utils.findRenderedDOMComponentWithId(component, 'ServicePanel-email').getDOMNode();
    const description: any = utils.findRenderedDOMComponentWithId(component, 'ServicePanel-description').getDOMNode();

    email.value = values.email;
    description.value = values.description;

    React.addons.TestUtils.Simulate.submit(form);

    expect(onSubmit).to.be.calledOnce;
    expect(onSubmit).to.be.calledWith(values);
  });

});
