export function setCursor(node: HTMLInputElement, position: number) {
  if (node) {
    if ((node as any).createTextRange) {
      var textRange = (node as any).createTextRange();
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
