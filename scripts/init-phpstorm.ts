import * as shell from 'shelljs';
import * as utils from './script-utils';
import { repoRoot } from "./script-utils";

//------------------------------------
utils.printTaskHeading('Copying .idea for plugins/versionpress')
shell.cp('-rn', `${repoRoot}/.ide-tpl/.idea-versionpress/.`, `${repoRoot}/plugins/versionpress/.idea`);

//------------------------------------
utils.printTaskHeading('Copying .idea for frontend')
shell.cp('-rn', `${repoRoot}/.ide-tpl/.idea-frontend/.`, `${repoRoot}/frontend/.idea`);
