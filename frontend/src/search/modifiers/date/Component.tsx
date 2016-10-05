/// <reference path='../../../../typings/browser.d.ts' />
/// <reference path='../../../../custom_typings/index.d.ts' />

import * as React from 'react';
import * as DayPicker from 'react-day-picker';

import ModifierComponent from '../ModifierComponent';

import 'react-day-picker/lib/style.css';

export default class DateComponent extends ModifierComponent<{}> {

  onUpClicked = () => {

  };

  onDownClicked = () => {

  };

  onSelect = () => {

  };

  onClick = (e: React.MouseEvent) => {
    console.log('onClick');
    e.preventDefault();
    e.stopPropagation();
    console.log('onClick');
  }

  onDayClick = (e: React.SyntheticEvent, day: any, modifiers: any) => {
    console.log('onDayClick');
    e.preventDefault();
    e.stopPropagation();
    console.log('onClick');
    console.log(day);
    console.log(modifiers);
  };

  render() {
    return (
      <div onClick={this.onClick} className='Search-hintMenu-container'>
        <span className='Search-hintMenu-arrow' />
        <span className='Search-hintMenu-arrowBorder' />
        <DayPicker
          onDayClick={this.onDayClick}
        />
      </div>
    );
  }

}
