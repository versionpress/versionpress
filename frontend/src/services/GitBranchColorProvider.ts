const colors = ['#2ecc71', '#3498db', '#f1c40f', '#8e44ad', '#e67e22', '#2c3e50', '#bdc3c7', '#c0392b',
                '#1abc9c', '#2980b9', '#f39c12', '#9b59b6', '#7f8c8d', '#00ff66', '#1f7a43', '#00d8e6'];
function hasValue(obj, value) {
  return Object.keys(obj).some((key) => obj[key] === value);
}

export const getGitBranchColor = (function() {
  const memo = {
    'master': '#e74c3c',
  };

  return function (branchName: string) {
    if (branchName in memo) {
      return memo[branchName];
    } else {
      const unusedColors = colors.filter((item: string) => !hasValue(memo, item));
      const color = unusedColors.length > 0
        ? unusedColors[0]
        : colors[Object.keys(memo).length % colors.length - 1];
      memo[branchName] = color;
      return color;
    }
  };
})();
