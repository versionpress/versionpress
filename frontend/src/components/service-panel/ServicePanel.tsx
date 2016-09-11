import * as React from 'react';

import Button from './Button';
import Panel from './panel/Panel';

import './ServicePanel.less';

interface ServicePanelProps {
  children?: React.ReactNode;
  isVisible: boolean;
  onButtonClick(e: React.MouseEvent): void;
}

const ServicePanel: React.StatelessComponent<ServicePanelProps> = ({ children, isVisible, onButtonClick }) => (
  <div>
    <Button onClick={onButtonClick} />
    {children}
    <Panel isVisible={isVisible} />
  </div>
);

export default ServicePanel;
