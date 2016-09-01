/// <reference path='../Search.d.ts' />

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
    const section = configItem.type === 'date' ? 'time' : 'others';
    modifiers.push({
      value: key,
      label: configItem.defaultHint,
      modifier: true,
      section: section,
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
  const modifiers = modifiersList.map(item => {
    item.section = 'modifiers';
    return item;
  });
  return allList.concat(modifiers);
}
