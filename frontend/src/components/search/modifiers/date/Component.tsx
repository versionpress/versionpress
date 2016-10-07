import * as React from 'react';
import * as DayPicker from 'react-day-picker';
import * as moment from 'moment';

import ModifierComponent from '../ModifierComponent';

import 'react-day-picker/lib/style.css';

export default class DateComponent extends ModifierComponent<{}> {

  onUpClicked = () => {
    const { activeTokenIndex, adapter, token, onChangeTokenModel } = this.props;

    const date = token.value;
    const cursorLocationType = this.getCursorLocationType();

    if (adapter.isValueValid(date) && date && cursorLocationType) {
      const newDate = moment(date).add(1, cursorLocationType);
      onChangeTokenModel(activeTokenIndex, newDate, false);
    }
  };

  onDownClicked = () => {
    const { activeTokenIndex, adapter, token, onChangeTokenModel } = this.props;

    const date = token.value;
    const cursorLocationType = this.getCursorLocationType();

    if (adapter.isValueValid(date) && date && cursorLocationType) {
      const newDate = moment(date).subtract(1, cursorLocationType);
      onChangeTokenModel(activeTokenIndex, newDate, false);
    }
  };

  onSelect = () => {
    const { activeTokenIndex, token, onChangeTokenModel } = this.props;
    const { value } = token;

    if (!value) {
      return false;
    }

    onChangeTokenModel(activeTokenIndex, value, true);
    return true;
  };

  onMouseDown = (e: React.MouseEvent) => {
    e.preventDefault();
  }

  onDayClick = (e: React.SyntheticEvent, day: any) => {
    const { activeTokenIndex, onChangeTokenModel } = this.props;
    onChangeTokenModel(activeTokenIndex, moment(day), true);
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

  isDateValid(date: Date) {
    return Object.prototype.toString.call(date) === '[object Date]'
           && !isNaN(date.getTime());
  }

  render() {
    const { token } = this.props;
    const selectedDay = new Date(token.value);
    const isValid = this.isDateValid(selectedDay);

    return (
      <div onMouseDown={this.onMouseDown} className='Search-hintMenu-container'>
        {isValid
          ? <DayPicker
              initialMonth={selectedDay}
              onDayClick={this.onDayClick}
              selectedDays={day => DayPicker.DateUtils.isSameDay(selectedDay, day)}
            />
          : <DayPicker
              onDayClick={this.onDayClick}
            />
        }
      </div>
    );
  }

}
