import * as React from 'react';

import Button from './Button';
import FlashMessage from './flash-message/FlashMessage';
import Panel from './panel/Panel';

import './ServicePanel.less';

interface ServicePanelProps {
  children?: React.ReactNode;
  message: InfoMessage;
  isVisible: boolean;
  onButtonClick(e: React.MouseEvent): void;
}

const ServicePanel: React.StatelessComponent<ServicePanelProps> = ({ children, message, isVisible, onButtonClick }) => (
  <div>
    <Button onClick={onButtonClick} />
    {children}
    {message &&
      <FlashMessage message={message} />
    }
    <Panel isVisible={isVisible} />
  </div>
);

export default ServicePanel;
