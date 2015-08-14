/// <reference path='../../../typings/tsd.d.ts' />
/// <reference path='../Commits.d.ts' />

import React = require('react/addons');
import CommitsTableRow = require('../CommitsTableRow.react');
import utils = require('../../common/__tests__/utils');

const testUtils = React.addons.TestUtils;

describe('CommitsTableRow', () => {
  var onUndo: Sinon.SinonSpy;
  var onRollback: Sinon.SinonSpy;
  var props: CommitsTableRow.Props;

  beforeEach((done) => {
    onUndo = sinon.spy();
    onRollback = sinon.spy();
    const commit: Commit = {
      message: 'Test message',
      date: '2015-01-01T01:23:34',
      hash: 'abcdef',
      canUndo: true,
      canRollback: true,
      isEnabled: true,
      changes: []
    };
    props = {
      commit: commit,
      onUndo: onUndo,
      onRollback: onRollback
    };
    done();
  });

  it('renders correctly when enabled', () => {
    const component = utils.render(React.createElement(CommitsTableRow, props));
    expect(component.type).to.equal('tr');
    expect(component.props.className).not.to.contain('disabled');
    expect(utils.getChildrenByClass(component, 'vp-table-undo', 3).length).to.equal(1);
    expect(utils.getChildrenByClass(component, 'vp-table-rollback', 3).length).to.equal(1);
  });

  it('renders correctly when disabled', () => {
    props.commit.isEnabled = false;
    const component = utils.render(React.createElement(CommitsTableRow, props));
    expect(component.type).to.equal('tr');
    expect(component.props.className).to.contain('disabled');
    expect(utils.getChildrenByClass(component, 'vp-table-undo', 3).length).to.equal(0);
    expect(utils.getChildrenByClass(component, 'vp-table-rollback', 3).length).to.equal(0);
  });

  it('should not render undo/rollback when not able to', () => {
    props.commit.canUndo = false;
    props.commit.canRollback = false;
    const component = utils.render(React.createElement(CommitsTableRow, props));
    expect(utils.getChildrenByClass(component, 'vp-table-undo', 3).length).to.equal(0);
    expect(utils.getChildrenByClass(component, 'vp-table-rollback', 3).length).to.equal(0);
  });

  it('calls callback on undo', () => {
    const component = testUtils.renderIntoDocument(React.createElement(CommitsTableRow, props));
    const undoButton = testUtils.findRenderedDOMComponentWithClass(component, 'vp-table-undo').getDOMNode();
    React.addons.TestUtils.Simulate.click(undoButton);
    expect(onUndo).to.be.calledOnce;
  });

  it('calls callback on rollback', () => {
    const component = testUtils.renderIntoDocument(React.createElement(CommitsTableRow, props));
    const rollbackButton = testUtils.findRenderedDOMComponentWithClass(component, 'vp-table-rollback').getDOMNode();
    React.addons.TestUtils.Simulate.click(rollbackButton);
    expect(onRollback).to.be.calledOnce;
  });

});
