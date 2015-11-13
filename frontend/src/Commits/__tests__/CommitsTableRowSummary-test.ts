/// <reference path='../../../typings/typings.d.ts' />
/// <reference path='../Commits.d.ts' />

import React = require('react/addons');
import CommitsTableRowSummary = require('../CommitsTableRowSummary.react');
import utils = require('../../common/__tests__/utils');

const testUtils = React.addons.TestUtils;

describe('CommitsTableRowSummary', () => {
  var onUndo: Sinon.SinonSpy;
  var onRollback: Sinon.SinonSpy;
  var props: CommitsTableRowSummary.Props;

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
      isInitial: false,
      changes: []
    };
    props = {
      commit: commit,
      onUndo: onUndo,
      onRollback: onRollback,
      onDetailsLevelChanged: () => {},
      detailsLevel: 'none'
    };
    done();
  });

  it('renders correctly when enabled', () => {
    const component = utils.render(React.createElement(CommitsTableRowSummary, props));
    expect(component.type).to.equal('tr');
    expect(component.props.className).not.to.contain('disabled');
    expect(utils.getChildrenByClass(component, 'vp-table-undo', 3).length).to.equal(1);
    expect(utils.getChildrenByClass(component, 'vp-table-rollback', 3).length).to.equal(1);
  });

  it('renders correctly when disabled', () => {
    props.commit.isEnabled = false;
    const component = utils.render(React.createElement(CommitsTableRowSummary, props));
    expect(component.type).to.equal('tr');
    expect(component.props.className).to.contain('disabled');
    expect(utils.getChildrenByClass(component, 'vp-table-undo', 3).length).to.equal(0);
    expect(utils.getChildrenByClass(component, 'vp-table-rollback', 3).length).to.equal(0);
  });

  it('should not render undo/rollback when not able to', () => {
    props.commit.canUndo = false;
    props.commit.canRollback = false;
    const component = utils.render(React.createElement(CommitsTableRowSummary, props));
    expect(utils.getChildrenByClass(component, 'vp-table-undo', 3).length).to.equal(0);
    expect(utils.getChildrenByClass(component, 'vp-table-rollback', 3).length).to.equal(0);
  });

  it('calls callback on undo', () => {
    const table = React.DOM.table(null, React.DOM.tbody(null, React.createElement(CommitsTableRowSummary, props)));
    const component = testUtils.renderIntoDocument(table);
    const undoButton = testUtils.findRenderedDOMComponentWithClass(component, 'vp-table-undo').getDOMNode();
    React.addons.TestUtils.Simulate.click(undoButton);
    expect(onUndo).to.be.calledOnce;
  });

  it('calls callback on rollback', () => {
    const table = React.DOM.table(null, React.DOM.tbody(null, React.createElement(CommitsTableRowSummary, props)));
    const component = testUtils.renderIntoDocument(table);
    const rollbackButton = testUtils.findRenderedDOMComponentWithClass(component, 'vp-table-rollback').getDOMNode();
    React.addons.TestUtils.Simulate.click(rollbackButton);
    expect(onRollback).to.be.calledOnce;
  });

});
