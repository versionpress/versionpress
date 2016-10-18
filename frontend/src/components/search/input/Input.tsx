/// <reference path='../Search.d.ts' />

import * as React from 'react';

import './Input.less';

interface InputProps {
  nodeRef?: React.Ref<HTMLInputElement>;
  maxLength?: number;
  placeholder?: string;
  value?: string;
  disabled?: boolean;
  onBlur?: React.FocusEventHandler<HTMLInputElement>;
  onFocus?: React.FocusEventHandler<HTMLInputElement>;
  onClick?: React.MouseEventHandler<HTMLInputElement>;
  onCut?: React.ClipboardEventHandler<HTMLInputElement>;
  onPaste?: React.ClipboardEventHandler<HTMLInputElement>;
  onKeyDown?: React.KeyboardEventHandler<HTMLInputElement>;
  onKeyUp?: React.KeyboardEventHandler<HTMLInputElement>;
  onChange?(e: React.FormEvent<HTMLInputElement>): void;
}

const Input: React.StatelessComponent<InputProps> = (props) => {
  const {
    nodeRef = null,
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
    onChange = () => {},
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
      autoComplete={null}
      onBlur={onBlur}
      onFocus={onFocus}
      onClick={onClick}
      onCut={onCut}
      onPaste={onPaste}
      onKeyDown={onKeyDown}
      onKeyUp={onKeyUp}
      onChange={onChange}
      ref={nodeRef}
    />
  );

};

export default Input;
