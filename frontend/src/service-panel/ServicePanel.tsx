import * as React from 'react';

import Button from './Button';
import Panel from './panel/Panel';
import VpTitle from './VpTitle';

import './ServicePanel.less';

interface ServicePanelProps {
  isVisible: boolean;
  onButtonClick(e: React.MouseEvent): void;
}

const ServicePanel: React.StatelessComponent<ServicePanelProps> = ({ isVisible, onButtonClick }) => (
  <div>
    <Button onClick={onButtonClick} />
    <VpTitle />
    <Panel isVisible={isVisible} />
  </div>
);

export default ServicePanel;
