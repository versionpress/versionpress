/// <reference path='../Search.d.ts' />

import * as React from 'react';

import './Input.less';

interface InputProps {
  ref?: React.Ref<HTMLInputElement>;
  maxLength?: number;
  placeholder?: string;
  value?: string;
  disabled?: boolean;
  onBlur?: React.FocusEventHandler;
  onFocus?: React.FocusEventHandler;
  onClick?: React.MouseEventHandler;
  onCut?: React.ClipboardEventHandler;
  onPaste?: React.ClipboardEventHandler;
  onKeyDown?: React.KeyboardEventHandler;
  onKeyUp?: React.KeyboardEventHandler;
}

const Input: React.StatelessComponent<InputProps> = (props) => {
  const {
    ref = null,
    maxLength = 250,
    placeholder = 'Search...',
    value = '',
    onBlur = () => {},
    onFocus = () => {},
    onClick = () => {},
    onCut = () => {},
    onPaste = () => {},
    onKeyDown = () => {},
    onKeyUp = () => {},
    disabled = false,
  } = props;

  return (
    <input
      type='text'
      maxLength={maxLength}
      placeholder={placeholder}
      className='Search-Input'
      defaultValue={value}
      disabled={disabled}
      spellCheck={false}
      autoComplete={false}
      onBlur={onBlur}
      onFocus={onFocus}
      onClick={onClick}
      onCut={onCut}
      onPaste={onPaste}
      onKeyDown={onKeyDown}
      onKeyUp={onKeyUp}
      ref={ref}
    />
  );

};

export default Input;
