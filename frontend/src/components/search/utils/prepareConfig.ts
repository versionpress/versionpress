/// <reference path='../Search.d.ts' />
import * as moment from 'moment';

export function prepareConfig(config: SearchConfig): SearchConfig {
  const modifiers: SearchConfigItemContent[] = getAllModifiers(config);
  config['_default'] = {
    type: 'default',
    content: getDefaultContent(config, modifiers),
  };
  return config;
}

function getAllModifiers(config: SearchConfig): SearchConfigItemContent[] {
  let modifiers = [];
  for (let key in config) {
    if (key.substr(0, 1) === '_') {
      continue;
    }

    const configItem = config[key];
    const section = configItem.type === 'date' ? 'time' : 'modifiers';
    modifiers.push({
      value: key,
      label: configItem.type === 'date' ? moment().format('YYYY-MM-DD') : configItem.defaultHint,
      modifier: true,
      section,
    });
  }
  return modifiers;
}

function getDefaultContent(config: SearchConfig, modifiersList: SearchConfigItemContent[]): SearchConfigItemContent[] {
  let allList: SearchConfigItemContent[] = [];

  for (let key in config) {
    const configItem = config[key];

    if (configItem.type === 'list' && configItem.content) {
      const list = configItem.content.map(item => {
        return {
          label: item.label,
          value: key + item.value,
          fullText: true,
          section: configItem.sectionTitle,
        };
      });
      allList = allList.concat(list);
    }
  }
  return allList.concat(modifiersList);
}
