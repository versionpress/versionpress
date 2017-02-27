/// <reference path='../../components/search/Search.d.ts' />

import * as moment from 'moment';

export function prepareConfig(config: SearchConfig): SearchConfig {
  config['_default'] = {
    type: 'default',
    content: getDefaultContent(config),
  };
  return config;
}

function getDefaultContent(config: SearchConfig): SearchConfigItemContent[] {
  let defaultContent: SearchConfigItemContent[] = [];

  for (let key in config) {
    if (key.substr(0, 1) === '_') {
      continue;
    }

    const configItem = config[key];

    defaultContent.push({
      value: key,
      label: configItem.type === 'date' ? moment().format('YYYY-MM-DD') : configItem.defaultHint,
      modifier: true,
      section: configItem.type === 'date' ? 'time' : 'modifiers',
    });

    if (configItem.type === 'list' && configItem.content) {
      const list = configItem.content.map(item => ({
        label: item.label,
        value: key + item.value,
        fullText: true,
        section: configItem.sectionTitle,
      }));
      defaultContent = defaultContent.concat(list);
    }
  }

  return defaultContent;
}
