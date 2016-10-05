export function setCursor(node: HTMLInputElement, position: number) {
  if (node) {
    if (node.createTextRange) {
      var textRange = node.createTextRange();
      textRange.collapse(true);
      textRange.moveEnd('character', position);
      textRange.moveStart('character', position);
      textRange.select();
      return true;
    } else if (node.setSelectionRange) {
      node.setSelectionRange(position, position);
      return true;
    }
  }
  return false;
}
