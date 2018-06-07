import * as shell from 'shelljs';
import * as utils from './script-utils';

utils.exitIfNotRunFromRootDir();

//------------------------------------
utils.printTaskHeading('Copying .idea for plugins/versionpress')
shell.cp('-rn', '.ide-tpl/.idea-versionpress/.', 'plugins/versionpress/.idea');

//------------------------------------
utils.printTaskHeading('Copying .idea for frontend')
shell.cp('-rn', '.ide-tpl/.idea-frontend/.', 'frontend/.idea');
