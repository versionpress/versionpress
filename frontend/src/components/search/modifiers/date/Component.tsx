import * as React from 'react';
import * as DayPicker from 'react-day-picker';
import * as moment from 'moment';

import ModifierComponent from '../ModifierComponent';

import 'react-day-picker/lib/style.css';

export default class DateComponent extends ModifierComponent<{}> {

  onUpClicked = () => {
    const { activeTokenIndex, token, onChangeTokenModel } = this.props;

    const date = token.value;
    const cursorLocationType = this.getCursorLocationType();

    if (date && cursorLocationType) {
      const newDate = moment(date).add(1, cursorLocationType);
      onChangeTokenModel(activeTokenIndex, newDate, false);
    }
  };

  onDownClicked = () => {
    const { activeTokenIndex, token, onChangeTokenModel } = this.props;

    const date = token.value;
    const cursorLocationType = this.getCursorLocationType();

    if (date && cursorLocationType) {
      const newDate = moment(date).subtract(1, cursorLocationType);
      onChangeTokenModel(activeTokenIndex, newDate, false);
    }
  };

  onSelect = () => {
    const { activeTokenIndex, token, onChangeTokenModel } = this.props;
    const date = token.value ? moment(token.value) : moment();
    onChangeTokenModel(activeTokenIndex, date, true);
  };

  onMouseDown = (e: React.MouseEvent) => {
    e.preventDefault();
  }

  onDayClick = (e: React.SyntheticEvent, day: any) => {
    const { activeTokenIndex, onChangeTokenModel } = this.props;
    onChangeTokenModel(activeTokenIndex, day, true);
  };

  getCursorLocationType = (): moment.UnitOfTime => {
    const { cursor, token } = this.props;
    const modifierLength = token.modifier.length;

    if (cursor >= modifierLength) {
      if (cursor < (modifierLength + 5)) {
        return 'years';
      } else if (cursor < (modifierLength + 8)) {
        return 'months';
      } else if (cursor < (modifierLength + 11)) {
        return 'days';
      }
    }
  }

  render() {
    const { token } = this.props;
    const selectedDay = token.value ? new Date(token.value) : new Date();

    return (
      <div onMouseDown={this.onMouseDown} className='Search-hintMenu-container'>
        <DayPicker
          initialMonth={selectedDay}
          onDayClick={this.onDayClick}
          selectedDays={day => DayPicker.DateUtils.isSameDay(selectedDay, day)}
        />
      </div>
    );
  }

}
