import * as React from 'react';

import Button from './Button';
import VpTitle from './VpTitle';
import VpInfo from './VpInfo';
import './ServicePanel.less';

interface ServicePanelProps {
  isVisible: boolean;
  onButtonClick(e: React.MouseEvent): void;
}

const ServicePanel: React.StatelessComponent<ServicePanelProps> = ({ isVisible, onButtonClick }) => (
  <div>
    <Button onClick={onButtonClick} />
    <VpTitle />
    <VpInfo isVisible={isVisible} />
  </div>
);

export default ServicePanel;
