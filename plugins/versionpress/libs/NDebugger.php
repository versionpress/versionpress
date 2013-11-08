<?php //netteloader=
/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 *
 * For more information please see http://nette.org
 * @package Nette\Diagnostics
 */

define('NETTE_DIR',dirname(__FILE__));interface
IBarPanel{function
getTab();function
getPanel();}class
NDebugBar{private$panels=array();public
function
addPanel(IBarPanel$panel,$id=NULL){if($id===NULL){$c=0;do{$id=get_class($panel).($c++?"-$c":'');}while(isset($this->panels[$id]));}$this->panels[$id]=$panel;return$this;}public
function
getPanel($id){return
isset($this->panels[$id])?$this->panels[$id]:NULL;}public
function
render(){$obLevel=ob_get_level();$panels=array();foreach($this->panels
as$id=>$panel){try{$panels[]=array('id'=>preg_replace('#[^a-z0-9]+#i','-',$id),'tab'=>$tab=(string)$panel->getTab(),'panel'=>$tab?(string)$panel->getPanel():NULL);}catch(Exception$e){$panels[]=array('id'=>"error-".preg_replace('#[^a-z0-9]+#i','-',$id),'tab'=>"Error in $id",'panel'=>'<h1>Error: '.$id.'</h1><div class="nette-inner">'.nl2br(htmlSpecialChars($e)).'</div>');while(ob_get_level()>$obLevel){ob_end_clean();}}}?>



<!-- Nette Debug Bar -->

<?php ob_start()?>
&nbsp;

<style id="nette-debug-style" class="nette">#nette-debug{display:none;position:fixed}body#nette-debug{margin:5px 5px 0;display:block}#nette-debug *{font:inherit;color:inherit;background:transparent;margin:0;padding:0;border:none;text-align:inherit;list-style:inherit}#nette-debug .nette-fixed-coords{position:fixed;_position:absolute;right:0;bottom:0;max-width:100%}#nette-debug a{color:#125EAE;text-decoration:none}#nette-debug .nette-panel a{color:#125EAE;text-decoration:none}#nette-debug a:hover,#nette-debug a:active,#nette-debug a:focus{background-color:#125EAE;color:white}#nette-debug .nette-panel h2,#nette-debug .nette-panel h3,#nette-debug .nette-panel p{margin:.4em 0}#nette-debug .nette-panel table{border-collapse:collapse;background:#FDF5CE}#nette-debug .nette-panel tr:nth-child(2n) td{background:#F7F0CB}#nette-debug .nette-panel td,#nette-debug .nette-panel th{border:1px solid #E6DFBF;padding:2px 5px;vertical-align:top;text-align:left}#nette-debug .nette-panel th{background:#F4F3F1;color:#655E5E;font-size:90%;font-weight:bold}#nette-debug .nette-panel pre,#nette-debug .nette-panel code{font:9pt/1.5 Consolas,monospace}#nette-debug table .nette-right{text-align:right}.nette-hidden,.nette-collapsed{display:none}#nette-debug-bar{font:normal normal 12px/21px Tahoma,sans-serif;color:#333;border:1px solid #c9c9c9;background:#EDEAE0 url('data:image/png;base64,R0lGODlhAQAVALMAAOTh1/Px6eHe1fHv5e/s4vLw6Ofk2u3q4PPw6PPx6PDt5PLw5+Dd1OXi2Ojm3Orn3iH5BAAAAAAALAAAAAABABUAAAQPMISEyhpYkfOcaQAgCEwEADs=') top;position:relative;overflow:auto;min-height:21px;_float:left;min-width:50px;white-space:nowrap;z-index:23181;opacity:.9;border-radius:3px;-moz-border-radius:3px;box-shadow:1px 1px 10px rgba(0,0,0,.15);-moz-box-shadow:1px 1px 10px rgba(0,0,0,.15);-webkit-box-shadow:1px 1px 10px rgba(0,0,0,.15)}#nette-debug-bar:hover{opacity:1}#nette-debug-bar ul{list-style:none none;margin-left:4px}#nette-debug-bar li{float:left}#nette-debug-bar img{vertical-align:middle;position:relative;top:-1px;margin-right:3px}#nette-debug-bar li a{color:#000;display:block;padding:0 4px}#nette-debug-bar li a:hover{color:black;background:#c3c1b8}#nette-debug-bar li .nette-warning{color:#D32B2B;font-weight:bold}#nette-debug-bar li>span{padding:0 4px}#nette-debug-logo{background:url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAC0AAAAPCAYAAABwfkanAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAABiFJREFUSMe1VglPlGcQ5i+1xjZNqxREtGq8ahCPWsVGvEDBA1BBRQFBDjkE5BYUzwpovRBUREBEBbl3OVaWPfj2vi82eTrvbFHamLRJ4yYTvm+u95mZZ96PoKAv+LOatXBYZ+Bx6uFy6DGnt1m0EOKwSmQzwmHTgX5B/1W+yM9GYJ02CX6/B/5ZF+w2A4x6FYGTYDVp4PdY2Tbrs5N+mnRa2Km4/wV6rhPzQQj5fDc1mJM5nd0iYdZtQWtrCxobGnDpUiledTynbuvg99mgUMhw924Trl2rR01NNSTNJE9iDpTV8innv4K2kZPLroPXbYLHZeSu2K1aeF0muJ2GvwGzmNSwU2E+svm8ZrgdBliMaha/34Vx+RAKCgpwpa4OdbW1UE/L2cc/68WtWzdRVlaG6uoqtD1/BA/pA1MIxLvtes7pc5vhoDOE/rOgbVSdf9aJWa8dBp0Kyg+jdLiTx2vQKWEyqGmcNkqg4iTC1+dzQatWkK+cJqPD7KyFaKEjvRuNjY24fLkGdXW1ePjwAeX4QHonDNI0A75+/RpqqqshH+6F2UAUMaupYXouykV0mp6SQ60coxgL8Z4aMg/4x675/V60v3jKB+Xl5WJibIC4KPEIS0qKqWv5GOh7BZ/HSIk9kA33o7y8DOfPZ6GQOipkXDZAHXKxr4ipqqpkKS6+iIrycgz2dyMnJxtVlZUsotNZWZmor79KBbvgpdjm5sfIzc1hv4L8fKJPDTfJZZc+gRYKr8sAEy2DcBRdEEk62ltx9uwZ5qNILoDU1l6mbrvx5EkzUlKSuTiR7PHjR3x4fv4FyIbeIic7G5WVFUyN+qtX+Lnt2SPcvn2LfURjhF7kE4WPDr+Bx+NEUVEhkpNPoImm5CSOl5aUIC3tLOMR59gtAY4HidGIzj14cB8ZGRkM8kJeHk6cOI4xWR8vSl5uLlJTT6O74xnT5lB8PM6cSYXVqILb5UBWZiYSExMYkE4zzjqX00QHG+h9AjPqMei0k3ywy2khMdNiq6BVCf04T6ekuBgJCUdRUVHOBQwPvkNSUiLjaGi4Q/5qFgYtHgTXRJdTT59GenoaA5gY64deq0Bc3EGuNj4+DnppEheLijhZRkY6SktLsGPHdi6irOwSFTRAgO04deokTSIFsbExuHfvLnFSx8DevelAfFwcA0lJTqZi5PDS9aci/sbE7Oe4wsICbtD27b/ye1NTI3FeSX4W2gdFALRD3A4eM44ePcKViuD79/8gnZP5Kg4+cCAW2dnnqUM2Lujw4UM4ePAA2ztfPsHIYA/sdOt43A50d7UFCjkUj+joXVBMDJDeDhcVk08cjd61C3v37uFYp8PKXX3X8xJRUTtw7FgSn3Xzxg10d7ZCqRjkM+02C7pettDNogqAFjzxuI3YHR2Nffv2coXy0V44HGZERm7kJNu2/cK8bW9rwbp1axnMnj27uUijQQOb1QyTcYZ3YMOGn/Hbzp1crAAvaDfY38O5hW3//n0ce+TIYWiUcub1xo0R2Lp1y8cYsUMWM125VhPe93Zj7do1vEPi26GfUdBFbhK8tGHrli1YsWwpgoOD0dXRQqAtXMCy8DBs3rwJoSGLsWrVclylBdoUGYlVK1dg9eqVCFsSSs8/4btvvmUwEnE0KTERISE/IiIiAsGLF2HhwgU8qbc97QgPX8qFr1mzGgu+/opzdL5o5l1aEhqC9evXYWlYKFYsD6e/YVj0w/dMGZVyBDMqeaDTRuKpkxYjIz2dOyeup6H3r2kkOuJ1H3N5Z1QUzp3LQF9vJ4xGLQYHXiM9LY0pEhsTg+PHj9HNcJu4OcL3uaQZY86LiZw8mcJTkmhBTUYJbU8fcoygobgWR4Z6iKtTPLE7d35HYkICT1dIZuY59HQ9412StBPQTMvw8Z6WaMNFxy3Gab4TeQT0M9IHwUT/G0i0MGIJ9CTiJjBIH+iQaQbC7+QnfEXiQL6xgF09TjETHCt8RbeMuil+D8RNsV1LHdQoZfR/iJJzCZuYmEE/Bd3MJNs/+0UURgFWJJ//aQ8k+CsxVTqnVytHObkQrUoG8T4/bs4u4ubbxLPwFzYNPc8HI2zijLm84l39Dx8hfwJenFezFBKKQwAAAABJRU5ErkJggg==') 0 50% no-repeat;min-width:45px;cursor:move}#nette-debug-logo span{display:none}#nette-debug-bar-bgl,#nette-debug-bar-bgx,#nette-debug-bar-bgr{position:absolute;z-index:-1;top:-7px;height:37px}#nette-debug .nette-panel{font:normal normal 12px/1.5 sans-serif;background:white;color:#333;text-align:left}#nette-debug h1{font:normal normal 23px/1.4 Tahoma,sans-serif;color:#575753;background:#EDEAE0;margin:-5px -5px 5px;padding:0 25px 5px 5px}#nette-debug .nette-mode-peek .nette-inner,#nette-debug .nette-mode-float .nette-inner{max-width:700px;max-height:500px;overflow:auto}#nette-debug .nette-panel .nette-icons{display:none}#nette-debug .nette-mode-peek{display:none;position:relative;z-index:23180;padding:5px;min-width:150px;min-height:50px;border:5px solid #EDEAE0;border-radius:5px;-moz-border-radius:5px}#nette-debug .nette-mode-peek h1{cursor:move}#nette-debug .nette-mode-float{position:relative;z-index:23179;padding:5px;min-width:150px;min-height:50px;border:5px solid #EDEAE0;border-radius:5px;-moz-border-radius:5px;opacity:.9;box-shadow:1px 1px 6px #666;-moz-box-shadow:1px 1px 6px rgba(0,0,0,.45);-webkit-box-shadow:1px 1px 6px #666}#nette-debug .nette-focused{z-index:23180;opacity:1}#nette-debug .nette-mode-float h1{cursor:move}#nette-debug .nette-mode-float .nette-icons{display:block;position:absolute;top:0;right:0;font-size:18px}#nette-debug .nette-icons a{color:#575753}#nette-debug .nette-icons a:hover{color:white}pre.nette-dump{color:#444;background:white}pre.nette-dump a,#nette-debug pre.nette-dump a{color:inherit;background:inherit}pre.nette-dump .php-array,pre.nette-dump .php-object,#nette-debug pre.nette-dump .php-array,#nette-debug pre.nette-dump .php-object{color:#C22}pre.nette-dump .php-string,#nette-debug pre.nette-dump .php-string{color:#080}pre.nette-dump .php-int,pre.nette-dump .php-float,#nette-debug pre.nette-dump .php-int,#nette-debug pre.nette-dump .php-float{color:#37D}pre.nette-dump .php-null,pre.nette-dump .php-bool,#nette-debug pre.nette-dump .php-null,#nette-debug pre.nette-dump .php-bool{color:black}pre.nette-dump .php-visibility,#nette-debug pre.nette-dump .php-visibility{font-size:85%;color:#999}@media print{#nette-debug *{display:none}}</style>

<!--[if lt IE 8]><style class="nette">#nette-debug-bar img{display:none}#nette-debug-bar li{border-left:1px solid #DCD7C8;padding:0 3px}#nette-debug-logo span{background:#edeae0;display:inline}</style><![endif]-->


<script id="nette-debug-script">/*<![CDATA[*/var Nette=Nette||{};
(function(){Nette.Class=function(a){var b=a.constructor||function(){},c,f=Object.prototype.hasOwnProperty;delete a.constructor;if(a.Extends){var d=function(){this.constructor=b};d.prototype=a.Extends.prototype;b.prototype=new d;delete a.Extends}if(a.Static){for(c in a.Static)f.call(a.Static,c)&&(b[c]=a.Static[c]);delete a.Static}for(c in a)f.call(a,c)&&(b.prototype[c]=a[c]);return b};Nette.Q=Nette.Class({Static:{factory:function(a){return new Nette.Q(a)},implement:function(a){var b,c=Nette.Q.implement,
f=Nette.Q.prototype,d=Object.prototype.hasOwnProperty;for(b in a)d.call(a,b)&&(c[b]=a[b],f[b]=function(a){return function(){return this.each(c[a],arguments)}}(b))}},constructor:function(a){if("string"===typeof a)a=this._find(document,a);else if(!a||a.nodeType||void 0===a.length||a===window)a=[a];for(var b=0,c=a.length;b<c;b++)a[b]&&(this[this.length++]=a[b])},length:0,find:function(a){return new Nette.Q(this._find(this[0],a))},_find:function(a,b){if(!a||!b)return[];if(document.querySelectorAll)return a.querySelectorAll(b);
if("#"===b.charAt(0))return[document.getElementById(b.substring(1))];var b=b.split("."),c=a.getElementsByTagName(b[0]||"*");if(b[1]){for(var f=[],d=RegExp("(^|\\s)"+b[1]+"(\\s|$)"),e=0,g=c.length;e<g;e++)d.test(c[e].className)&&f.push(c[e]);return f}return c},dom:function(){return this[0]},each:function(a,b){for(var c=0,f;c<this.length;c++)if(void 0!==(f=a.apply(this[c],b||[])))return f;return this}});var e=Nette.Q.factory,d=Nette.Q.implement;d({bind:function(a,b){if(document.addEventListener&&("mouseenter"===
a||"mouseleave"===a))var c=b,a="mouseenter"===a?"mouseover":"mouseout",b=function(a){for(var b=a.relatedTarget;b;b=b.parentNode)if(b===this)return;c.call(this,a)};var f=d.data.call(this),f=f.events=f.events||{};if(!f[a]){var h=this,e=f[a]=[],g=d.bind.genericHandler=function(a){a.target||(a.target=a.srcElement);a.preventDefault||(a.preventDefault=function(){a.returnValue=!1});a.stopPropagation||(a.stopPropagation=function(){a.cancelBubble=!0});a.stopImmediatePropagation=function(){this.stopPropagation();
b=e.length};for(var b=0;b<e.length;b++)e[b].call(h,a)};document.addEventListener?this.addEventListener(a,g,!1):document.attachEvent&&this.attachEvent("on"+a,g)}f[a].push(b)},addClass:function(a){this.className=this.className.replace(/^|\s+|$/g," ").replace(" "+a+" "," ")+" "+a},removeClass:function(a){this.className=this.className.replace(/^|\s+|$/g," ").replace(" "+a+" "," ")},hasClass:function(a){return-1<this.className.replace(/^|\s+|$/g," ").indexOf(" "+a+" ")},show:function(){var a=d.show.display=
d.show.display||{},b=this.tagName;if(!a[b]){var c=document.body.appendChild(document.createElement(b));a[b]=d.css.call(c,"display")}this.style.display=a[b]},hide:function(){this.style.display="none"},css:function(a){return this.currentStyle?this.currentStyle[a]:window.getComputedStyle?document.defaultView.getComputedStyle(this,null).getPropertyValue(a):void 0},data:function(){return this.nette?this.nette:this.nette={}},val:function(){var a;if(!this.nodeName){for(a=0,len=this.length;a<len;a++)if(this[a].checked)return this[a].value;
return null}if("select"===this.nodeName.toLowerCase()){a=this.selectedIndex;var b=this.options;if(0>a)return null;if("select-one"===this.type)return b[a].value;for(a=0,values=[],len=b.length;a<len;a++)b[a].selected&&values.push(b[a].value);return values}return"checkbox"===this.type?this.checked:this.value.replace(/^\s+|\s+$/g,"")},_trav:function(a,b,c){for(b=b.split(".");a&&!(1===a.nodeType&&(!b[0]||a.tagName.toLowerCase()===b[0])&&(!b[1]||d.hasClass.call(a,b[1])));)a=a[c];return e(a)},closest:function(a){return d._trav(this,
a,"parentNode")},prev:function(a){return d._trav(this.previousSibling,a,"previousSibling")},next:function(a){return d._trav(this.nextSibling,a,"nextSibling")},offset:function(a){for(var b=this,c=a?{left:-a.left||0,top:-a.top||0}:d.position.call(b);b=b.offsetParent;)c.left+=b.offsetLeft,c.top+=b.offsetTop;if(a)d.position.call(this,{left:-c.left,top:-c.top});else return c},position:function(a){if(a)this.nette&&this.nette.onmove&&this.nette.onmove.call(this,a),this.style.left=(a.left||0)+"px",this.style.top=
(a.top||0)+"px";else return{left:this.offsetLeft,top:this.offsetTop,width:this.offsetWidth,height:this.offsetHeight}},draggable:function(a){var b=e(this),c=document.documentElement,f,a=a||{};e(a.handle||this).bind("mousedown",function(e){e.preventDefault();e.stopPropagation();if(d.draggable.binded)return c.onmouseup(e);var i=b[0].offsetLeft-e.clientX,g=b[0].offsetTop-e.clientY;d.draggable.binded=!0;f=!1;c.onmousemove=function(c){c=c||event;f||(a.draggedClass&&b.addClass(a.draggedClass),a.start&&a.start(c,
b),f=!0);b.position({left:c.clientX+i,top:c.clientY+g});return!1};c.onmouseup=function(e){f&&(a.draggedClass&&b.removeClass(a.draggedClass),a.stop&&a.stop(e||event,b));d.draggable.binded=c.onmousemove=c.onmouseup=null;return!1}}).bind("click",function(a){f&&(a.stopImmediatePropagation(),preventClick=!1)})}})})();
(function(){Nette.Debug={};var e=Nette.Q.factory,d=Nette.Debug.Panel=Nette.Class({Extends:Nette.Q,Static:{PEEK:"nette-mode-peek",FLOAT:"nette-mode-float",WINDOW:"nette-mode-window",FOCUSED:"nette-focused",factory:function(a){return new d(a)},_toggle:function(a){var b=a.rel,b="#"===b.charAt(0)?e(b):e(a)["prev"===b?"prev":"next"](b.substring(4));"none"===b.css("display")?(b.show(),a.innerHTML=a.innerHTML.replace("►","▼")):(b.hide(),a.innerHTML=a.innerHTML.replace("▼","►"))}},constructor:function(a){Nette.Q.call(this,
"#nette-debug-panel-"+a.replace("nette-debug-panel-",""))},reposition:function(){this.hasClass(d.WINDOW)?window.resizeBy(document.documentElement.scrollWidth-document.documentElement.clientWidth,document.documentElement.scrollHeight-document.documentElement.clientHeight):(this.position(this.position()),this.position().width&&(document.cookie=this.dom().id+"="+this.position().left+":"+this.position().top+"; path=/"))},focus:function(){this.hasClass(d.WINDOW)?this.data().win.focus():(clearTimeout(this.data().blurTimeout),
this.addClass(d.FOCUSED).show())},blur:function(){this.removeClass(d.FOCUSED);if(this.hasClass(d.PEEK)){var a=this;this.data().blurTimeout=setTimeout(function(){a.hide()},50)}},toFloat:function(){this.removeClass(d.WINDOW).removeClass(d.PEEK).addClass(d.FLOAT).show().reposition();return this},toPeek:function(){this.removeClass(d.WINDOW).removeClass(d.FLOAT).addClass(d.PEEK).hide();document.cookie=this.dom().id+"=; path=/"},toWindow:function(){var a=this,b,c;c=this.offset();var f=this.dom().id;c.left+=
"number"===typeof window.screenLeft?window.screenLeft:window.screenX+10;c.top+="number"===typeof window.screenTop?window.screenTop:window.screenY+50;if(b=window.open("",f.replace(/-/g,"_"),"left="+c.left+",top="+c.top+",width="+c.width+",height="+(c.height+15)+",resizable=yes,scrollbars=yes"))c=b.document,c.write('<!DOCTYPE html><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><style>'+e("#nette-debug-style").dom().innerHTML+"</style><script>"+e("#nette-debug-script").dom().innerHTML+
'<\/script><body id="nette-debug">'),c.body.innerHTML='<div class="nette-panel nette-mode-window" id="'+f+'">'+this.dom().innerHTML+"</div>",b.Nette.Debug.Panel.factory(f).initToggler().reposition(),c.title=a.find("h1").dom().innerHTML,e([b]).bind("unload",function(){a.toPeek();b.close()}),e(c).bind("keyup",function(a){27===a.keyCode&&!a.shiftKey&&!a.altKey&&!a.ctrlKey&&!a.metaKey&&b.close()}),document.cookie=f+"=window; path=/",this.hide().removeClass(d.FLOAT).removeClass(d.PEEK).addClass(d.WINDOW).data().win=
b},init:function(){var a=this,b;a.data().onmove=function(a){var b=document,d=window.innerWidth||b.documentElement.clientWidth||b.body.clientWidth,b=window.innerHeight||b.documentElement.clientHeight||b.body.clientHeight;a.left=Math.max(Math.min(a.left,0.8*this.offsetWidth),0.2*this.offsetWidth-d);a.top=Math.max(Math.min(a.top,0.8*this.offsetHeight),this.offsetHeight-b)};e(window).bind("resize",function(){a.reposition()});a.draggable({handle:a.find("h1"),stop:function(){a.toFloat()}}).bind("mouseenter",
function(){a.focus()}).bind("mouseleave",function(){a.blur()});this.initToggler();a.find(".nette-icons").find("a").bind("click",function(b){"close"===this.rel?a.toPeek():a.toWindow();b.preventDefault()});(b=document.cookie.match(RegExp(a.dom().id+"=(window|(-?[0-9]+):(-?[0-9]+))")))?b[2]?a.toFloat().position({left:b[2],top:b[3]}):a.toWindow():a.addClass(d.PEEK)},initToggler:function(){var a=this;this.bind("click",function(b){var c=e(b.target).closest("a").dom();c&&c.rel&&(d._toggle(c),b.preventDefault(),
a.reposition())});return this}});Nette.Debug.Bar=Nette.Class({Extends:Nette.Q,constructor:function(){Nette.Q.call(this,"#nette-debug-bar")},init:function(){var a=this,b;a.data().onmove=function(a){var b=document,d=window.innerWidth||b.documentElement.clientWidth||b.body.clientWidth,b=window.innerHeight||b.documentElement.clientHeight||b.body.clientHeight;a.left=Math.max(Math.min(a.left,0),this.offsetWidth-d);a.top=Math.max(Math.min(a.top,0),this.offsetHeight-b)};e(window).bind("resize",function(){a.position(a.position())});
a.draggable({draggedClass:"nette-dragged",stop:function(){document.cookie=a.dom().id+"="+a.position().left+":"+a.position().top+"; path=/"}});a.find("a").bind("click",function(a){if("close"===this.rel)e("#nette-debug").hide(),window.opera&&e("body").show();else if(this.rel){var b=d.factory(this.rel);if(a.shiftKey)b.toFloat().toWindow();else if(b.hasClass(d.FLOAT)){var h=e(this).offset();b.offset({left:h.left-b.position().width+h.width+4,top:h.top-b.position().height-4}).toPeek()}else b.toFloat().position({left:b.position().left-
Math.round(100*Math.random())-20,top:b.position().top-Math.round(100*Math.random())-20}).reposition()}a.preventDefault()}).bind("mouseenter",function(){if(this.rel&&!("close"===this.rel||a.hasClass("nette-dragged"))){var b=d.factory(this.rel);b.focus();if(b.hasClass(d.PEEK)){var f=e(this).offset();b.offset({left:f.left-b.position().width+f.width+4,top:f.top-b.position().height-4})}}}).bind("mouseleave",function(){this.rel&&!("close"===this.rel||a.hasClass("nette-dragged"))&&d.factory(this.rel).blur()});
(b=document.cookie.match(RegExp(a.dom().id+"=(-?[0-9]+):(-?[0-9]+)")))&&a.position({left:b[1],top:b[2]});a.find("a").each(function(){this.rel&&"close"!==this.rel&&d.factory(this.rel).init()})}})})();/*]]>*/</script>


<?php foreach($panels
as$id=>$panel):if(!$panel['panel'])continue;?>
<div class="nette-fixed-coords">
	<div class="nette-panel" id="nette-debug-panel-<?php echo$panel['id']?>">
		<?php echo$panel['panel']?>
		<div class="nette-icons">
			<a href="#" title="open in window">&curren;</a>
			<a href="#" rel="close" title="close window">&times;</a>
		</div>
	</div>
</div>
<?php endforeach?>

<div class="nette-fixed-coords">
	<div id="nette-debug-bar">
		<ul>
			<li id="nette-debug-logo" title="PHP <?php echo
htmlSpecialChars(PHP_VERSION." |\n".(isset($_SERVER['SERVER_SOFTWARE'])?$_SERVER['SERVER_SOFTWARE']." |\n":'').(class_exists('NFramework')?'Nette Framework '.NFramework::VERSION.' ('.substr(NFramework::REVISION,8).')':''))?>">&nbsp;<span>Nette Framework</span></li>
			<?php foreach($panels
as$panel):if(!$panel['tab'])continue;?>
			<li><?php if($panel['panel']):?><a href="#" rel="<?php echo$panel['id']?>"><?php echo
trim($panel['tab'])?></a><?php else:echo'<span>',trim($panel['tab']),'</span>';endif?></li>
			<?php endforeach?>
			<li><a href="#" rel="close" title="close debug bar">&times;</a></li>
		</ul>
	</div>
</div>
<?php $output=ob_get_clean();?>


<div id="nette-debug"></div>

<script>
(function (onloadOrig) {
	window.onload = function() {
		if (typeof onloadOrig === 'function') onloadOrig();
		var debug = document.getElementById('nette-debug');
		document.body.appendChild(debug);
		debug.innerHTML = <?php echo
json_encode(@iconv('UTF-16','UTF-8//IGNORE',iconv('UTF-8','UTF-16//IGNORE',$output)))?>;
		for (var i = 0, scripts = debug.getElementsByTagName('script'); i < scripts.length; i++) eval(scripts[i].innerHTML);
		(new Nette.Debug.Bar).init();
		Nette.Q.factory(debug).show();
	};
})(window.onload);
</script>

<!-- /Nette Debug Bar -->
<?php }}class
NDebugBlueScreen{private$panels=array();public
function
addPanel($panel){if(!in_array($panel,$this->panels,TRUE)){$this->panels[]=$panel;}return$this;}public
function
render(Exception$exception){$panels=$this->panels;static$errorTypes=array(E_ERROR=>'Fatal Error',E_USER_ERROR=>'User Error',E_RECOVERABLE_ERROR=>'Recoverable Error',E_CORE_ERROR=>'Core Error',E_COMPILE_ERROR=>'Compile Error',E_PARSE=>'Parse Error',E_WARNING=>'Warning',E_CORE_WARNING=>'Core Warning',E_COMPILE_WARNING=>'Compile Warning',E_USER_WARNING=>'User Warning',E_NOTICE=>'Notice',E_USER_NOTICE=>'User Notice',E_STRICT=>'Strict');if(PHP_VERSION_ID>=50300){$errorTypes+=array(E_DEPRECATED=>'Deprecated',E_USER_WARNING=>'User Deprecated');}$title=($exception
instanceof
FatalErrorException&&isset($errorTypes[$exception->getSeverity()]))?$errorTypes[$exception->getSeverity()]:get_class($exception);$expandPath=NETTE_DIR.DIRECTORY_SEPARATOR;$counter=0;?><!DOCTYPE html><!-- "' --></script></style></pre></xmp></table>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="robots" content="noindex,noarchive">
	<meta name="generator" content="Nette Framework">

	<title><?php echo
htmlspecialchars($title)?></title><!-- <?php
$ex=$exception;echo
htmlspecialchars($ex->getMessage().($ex->getCode()?' #'.$ex->getCode():''));while((method_exists($ex,'getPrevious')&&$ex=$ex->getPrevious())||(isset($ex->previous)&&$ex=$ex->previous))echo
htmlspecialchars('; caused by '.get_class($ex).' '.$ex->getMessage().($ex->getCode()?' #'.$ex->getCode():''));?> -->

	<style type="text/css" class="nette">html{overflow-y:scroll}body{margin:0 0 2em;padding:0}#netteBluescreen{font:9pt/1.5 Verdana,sans-serif;background:white;color:#333;position:absolute;left:0;top:0;width:100%;z-index:23178;text-align:left}#netteBluescreen *{font:inherit;color:inherit;background:transparent;border:none;margin:0;padding:0;text-align:inherit;text-indent:0}#netteBluescreen b{font-weight:bold}#netteBluescreen i{font-style:italic}#netteBluescreen a{text-decoration:none;color:#328ADC;padding:2px 4px;margin:-2px -4px}#netteBluescreen a:hover,#netteBluescreen a:active,#netteBluescreen a:focus{color:#085AA3}#netteBluescreen a abbr{font-family:sans-serif;color:#BBB}#netteBluescreenIcon{position:absolute;right:.5em;top:.5em;z-index:23179;text-decoration:none;background:#CD1818;padding:3px}#netteBluescreenError{background:#CD1818;color:white;font:13pt/1.5 Verdana,sans-serif!important;display:block}#netteBluescreenError #netteBsSearch{color:#CD1818;font-size:.7em}#netteBluescreenError:hover #netteBsSearch{color:#ED8383}#netteBluescreen h1{font-size:18pt;font-weight:normal;text-shadow:1px 1px 0 rgba(0,0,0,.4);margin:.7em 0}#netteBluescreen h2{font:14pt/1.5 sans-serif!important;color:#888;margin:.6em 0}#netteBluescreen h3{font:bold 10pt/1.5 Verdana,sans-serif!important;margin:1em 0;padding:0}#netteBluescreen p,#netteBluescreen pre{margin:.8em 0}#netteBluescreen pre,#netteBluescreen code,#netteBluescreen table{font:9pt/1.5 Consolas,monospace!important}#netteBluescreen pre,#netteBluescreen table{background:#FDF5CE;padding:.4em .7em;border:1px dotted silver;overflow:auto}#netteBluescreen pre div{min-width:100%;float:left;_float:none;white-space:pre}#netteBluescreen table pre{padding:0;margin:0;border:none}#netteBluescreen pre .php-array,#netteBluescreen pre .php-object{color:#C22}#netteBluescreen pre .php-string{color:#080}#netteBluescreen pre .php-int,#netteBluescreen pre .php-float,#netteBluescreen pre .php-null,#netteBluescreen pre .php-bool{color:#328ADC}#netteBluescreen pre .php-visibility{font-size:85%;color:#998}#netteBluescreen pre.nette-dump a{color:#333}#netteBluescreen div.panel{padding:1px 25px}#netteBluescreen div.inner{background:#F4F3F1;padding:.1em 1em 1em;border-radius:8px;-moz-border-radius:8px;-webkit-border-radius:8px}#netteBluescreen table{border-collapse:collapse;width:100%}#netteBluescreen .outer{overflow:auto}#netteBluescreen td,#netteBluescreen th{vertical-align:top;text-align:left;padding:2px 6px;border:1px solid #e6dfbf}#netteBluescreen th{font-weight:bold}#netteBluescreen tr>:first-child{width:20%}#netteBluescreen tr:nth-child(2n),#netteBluescreen tr:nth-child(2n) pre{background-color:#F7F0CB}#netteBluescreen ol{margin:1em 0;padding-left:2.5em}#netteBluescreen ul{font:7pt/1.5 Verdana,sans-serif!important;padding:2em 4em;margin:1em 0 0;color:#777;background:#F6F5F3 url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAFEAAAAjCAMAAADbuxbOAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAADBQTFRF/fz24d7Y7Onj5uLd9vPu3drUzMvG09LN39zW8e7o2NbQ3NnT29jS0M7J1tXQAAAApvmsFgAAABB0Uk5T////////////////////AOAjXRkAAAKlSURBVHja7FbbsqQgDAwENEgc//9vN+SCWDtbtXPmZR/Wc6o02mlC58LA9ckFAOszvMV8xNgyUjyXhojfMVKvRL0ZHavxXYy5JrmchMdzou8YlTClxajtK8ZGGpWRoBr1+gFjKfHkJPbizabLgzE3pH7Iu4K980xgFvlrVzMZoVBWhtvouCDdcTDmTgMCJdVxJ9MKO6XxnliM7hxi5lbj2ZVM4l8DqYyKoNLYcfqBB1/LpHYxEcfVG6ZpMDgyFUVWY/Q1sSYPpIdSAKWqLWL0XqWiMWc4hpH0OQOMOAgdycY4N9Sb7wWANQs3rsDSdLAYiuxi5siVfOhBWIrtH0G3kNaF/8Q4kCPE1kMucG/ZMUBUCOgiKJkPuWWTLGVgLGpwns1DraUayCtoBqERyaYtVsm85NActRooezvSLO/sKZP/nq8n4+xcyjNsRu8zW6KWpdb7wjiQd4WrtFZYFiKHENSmWp6xshh96c2RQ+c7Lt+qbijyEjHWUJ/pZsy8MGIUuzNiPySK2Gqoh6ZTRF6ko6q3nVTkaA//itIrDpW6l3SLo8juOmqMXkYknu5FdQxWbhCfKHEGDhxxyTVaXJF3ZjSl3jMksjSOOKmne9pI+mcG5QvaUJhI9HpkmRo2NpCrDJvsktRhRE2MM6F2n7dt4OaMUq8bCctk0+PoMRzL+1l5PZ2eyM/Owr86gf8z/tOM53lom5+nVcFuB+eJVzlXwAYy9TZ9s537tfqcsJWbEU4nBngZo6FfO9T9CdhfBtmk2dLiAy8uS4zwOpMx2HqYbTC+amNeAYTpsP4SIgvWfUBWXxn3CMHW3ffd7k3+YIkx7w0t/CVGvcPejoeOlzOWzeGbawOHqXQGUTMZRcfj4XPCgW9y/fuvVn8zD9P1QHzv80uAAQA0i3Jer7Jr7gAAAABJRU5ErkJggg==') 99% 10px no-repeat;border-top:1px solid #DDD}#netteBluescreen .highlight{background:#CD1818;color:white;font-weight:bold;font-style:normal;display:block;padding:0 .4em;margin:0 -.4em}#netteBluescreen .line{color:#9F9C7F;font-weight:normal;font-style:normal}#netteBluescreen a[href^=editor\:]{color:inherit;border-bottom:1px dotted #C1D2E1}</style>
</head>



<body>
<div id="netteBluescreen">
	<a id="netteBluescreenIcon" href="#" rel="next"><abbr>&#x25bc;</abbr></a

	><div>
		<div id="netteBluescreenError" class="panel">
			<h1><?php echo
htmlspecialchars($title),($exception->getCode()?' #'.$exception->getCode():'')?></h1>

			<p><?php echo
htmlspecialchars($exception->getMessage())?> <a href="http://www.google.cz/search?sourceid=nette&amp;q=<?php echo
urlencode($title.' '.preg_replace('#\'.*\'|".*"#Us','',$exception->getMessage()))?>" id="netteBsSearch">search&#x25ba;</a></p>
		</div>



		<?php $ex=$exception;$level=0;?>
		<?php do{?>

			<?php if($level++):?>
			<div class="panel">
			<h2><a href="#" rel="netteBsPnl<?php echo++$counter?>">Caused by <abbr><?php echo($collapsed=$level>2)?'&#x25ba;':'&#x25bc;'?></abbr></a></h2>

			<div id="netteBsPnl<?php echo$counter?>" class="<?php echo$collapsed?'nette-collapsed ':''?>inner">
				<div class="panel">
					<h1><?php echo
htmlspecialchars(get_class($ex).($ex->getCode()?' #'.$ex->getCode():''))?></h1>

					<p><b><?php echo
htmlspecialchars($ex->getMessage())?></b></p>
				</div>
			<?php endif?>



			<?php foreach($panels
as$panel):?>
			<?php $panel=call_user_func($panel,$ex);if(empty($panel['tab'])||empty($panel['panel']))continue;?>
			<?php if(!empty($panel['bottom'])){continue;}?>
			<div class="panel">
				<h2><a href="#" rel="netteBsPnl<?php echo++$counter?>"><?php echo
htmlSpecialChars($panel['tab'])?> <abbr>&#x25bc;</abbr></a></h2>

				<div id="netteBsPnl<?php echo$counter?>" class="inner">
				<?php echo$panel['panel']?>
			</div></div>
			<?php endforeach?>



			<?php $stack=$ex->getTrace();$expanded=NULL?>
			<?php if(strpos($ex->getFile(),$expandPath)===0){foreach($stack
as$key=>$row){if(isset($row['file'])&&strpos($row['file'],$expandPath)!==0){$expanded=$key;break;}}}?>

			<div class="panel">
			<h2><a href="#" rel="netteBsPnl<?php echo++$counter?>">Source file <abbr><?php echo($collapsed=$expanded!==NULL)?'&#x25ba;':'&#x25bc;'?></abbr></a></h2>

			<div id="netteBsPnl<?php echo$counter?>" class="<?php echo$collapsed?'nette-collapsed ':''?>inner">
				<p><b>File:</b> <?php echo
NDebugHelpers::editorLink($ex->getFile(),$ex->getLine())?> &nbsp; <b>Line:</b> <?php echo$ex->getLine()?></p>
				<?php if(is_file($ex->getFile())):?><?php echo
self::highlightFile($ex->getFile(),$ex->getLine(),15,isset($ex->context)?$ex->context:NULL)?><?php endif?>
			</div></div>



			<?php if(isset($stack[0]['class'])&&$stack[0]['class']==='NDebugger'&&($stack[0]['function']==='_shutdownHandler'||$stack[0]['function']==='_errorHandler'))unset($stack[0])?>
			<?php if($stack):?>
			<div class="panel">
				<h2><a href="#" rel="netteBsPnl<?php echo++$counter?>">Call stack <abbr>&#x25bc;</abbr></a></h2>

				<div id="netteBsPnl<?php echo$counter?>" class="inner">
				<ol>
					<?php foreach($stack
as$key=>$row):?>
					<li><p>

					<?php if(isset($row['file'])&&is_file($row['file'])):?>
						<?php echo
NDebugHelpers::editorLink($row['file'],$row['line']),':',$row['line']?>
					<?php else:?>
						<i>inner-code</i><?php if(isset($row['line']))echo':',$row['line']?>
					<?php endif?>

					<?php if(isset($row['file'])&&is_file($row['file'])):?><a href="#" rel="netteBsSrc<?php echo"$level-$key"?>">source <abbr>&#x25ba;</abbr></a>&nbsp; <?php endif?>

					<?php if(isset($row['class']))echo
htmlspecialchars($row['class'].$row['type'])?>
					<?php echo
htmlspecialchars($row['function'])?>

					(<?php if(!empty($row['args'])):?><a href="#" rel="netteBsArgs<?php echo"$level-$key"?>">arguments <abbr>&#x25ba;</abbr></a><?php endif?>)
					</p>

					<?php if(!empty($row['args'])):?>
						<div class="nette-collapsed outer" id="netteBsArgs<?php echo"$level-$key"?>">
						<table>
						<?php

try{$r=isset($row['class'])?new
ReflectionMethod($row['class'],$row['function']):new
ReflectionFunction($row['function']);$params=$r->getParameters();}catch(Exception$e){$params=array();}foreach($row['args']as$k=>$v){echo'<tr><th>',htmlspecialchars(isset($params[$k])?'$'.$params[$k]->name:"#$k"),'</th><td>';echo
NDebugHelpers::clickableDump($v);echo"</td></tr>\n";}?>
						</table>
						</div>
					<?php endif?>


					<?php if(isset($row['file'])&&is_file($row['file'])):?>
						<div <?php if($expanded!==$key)echo'class="nette-collapsed"';?> id="netteBsSrc<?php echo"$level-$key"?>"><?php echo
self::highlightFile($row['file'],$row['line'])?></div>
					<?php endif?>

					</li>
					<?php endforeach?>
				</ol>
			</div></div>
			<?php endif?>



			<?php if(isset($ex->context)&&is_array($ex->context)):?>
			<div class="panel">
			<h2><a href="#" rel="netteBsPnl<?php echo++$counter?>">Variables <abbr>&#x25bc;</abbr></a></h2>

			<div id="netteBsPnl<?php echo$counter?>" class="inner">
			<div class="outer">
			<table>
			<?php

foreach($ex->context
as$k=>$v){echo'<tr><th>$',htmlspecialchars($k),'</th><td>',NDebugHelpers::clickableDump($v),"</td></tr>\n";}?>
			</table>
			</div>
			</div></div>
			<?php endif?>

		<?php }while((method_exists($ex,'getPrevious')&&$ex=$ex->getPrevious())||(isset($ex->previous)&&$ex=$ex->previous));?>
		<?php while(--$level)echo'</div></div>'?>



		<?php $bottomPanels=array()?>
		<?php foreach($panels
as$panel):?>
		<?php $panel=call_user_func($panel,NULL);if(empty($panel['tab'])||empty($panel['panel']))continue;?>
		<?php if(!empty($panel['bottom'])){$bottomPanels[]=$panel;continue;}?>
		<div class="panel">
			<h2><a href="#" rel="netteBsPnl<?php echo++$counter?>"><?php echo
htmlSpecialChars($panel['tab'])?> <abbr>&#x25ba;</abbr></a></h2>

			<div id="netteBsPnl<?php echo$counter?>" class="nette-collapsed inner">
			<?php echo$panel['panel']?>
		</div></div>
		<?php endforeach?>



		<div class="panel">
		<h2><a href="#" rel="netteBsPnl<?php echo++$counter?>">Environment <abbr>&#x25ba;</abbr></a></h2>

		<div id="netteBsPnl<?php echo$counter?>" class="nette-collapsed inner">
			<h3><a href="#" rel="netteBsPnl<?php echo++$counter?>">$_SERVER <abbr>&#x25bc;</abbr></a></h3>
			<div id="netteBsPnl<?php echo$counter?>" class="outer">
			<table>
			<?php

foreach($_SERVER
as$k=>$v)echo'<tr><th>',htmlspecialchars($k),'</th><td>',NDebugHelpers::clickableDump($v),"</td></tr>\n";?>
			</table>
			</div>


			<h3><a href="#" rel="netteBsPnl<?php echo++$counter?>">$_SESSION <abbr>&#x25bc;</abbr></a></h3>
			<div id="netteBsPnl<?php echo$counter?>" class="outer">
			<?php if(empty($_SESSION)):?>
			<p><i>empty</i></p>
			<?php else:?>
			<table>
			<?php

foreach($_SESSION
as$k=>$v)echo'<tr><th>',htmlspecialchars($k),'</th><td>',$k==='__NF'?'<i>Nette Session</i>':NDebugHelpers::clickableDump($v),"</td></tr>\n";?>
			</table>
			<?php endif?>
			</div>


			<?php if(!empty($_SESSION['__NF']['DATA'])):?>
			<h3><a href="#" rel="netteBsPnl<?php echo++$counter?>">Nette Session <abbr>&#x25bc;</abbr></a></h3>
			<div id="netteBsPnl<?php echo$counter?>" class="outer">
			<table>
			<?php

foreach($_SESSION['__NF']['DATA']as$k=>$v)echo'<tr><th>',htmlspecialchars($k),'</th><td>',NDebugHelpers::clickableDump($v),"</td></tr>\n";?>
			</table>
			</div>
			<?php endif?>


			<?php
$list=get_defined_constants(TRUE);if(!empty($list['user'])):?>
			<h3><a href="#" rel="netteBsPnl<?php echo++$counter?>">Constants <abbr>&#x25ba;</abbr></a></h3>
			<div id="netteBsPnl<?php echo$counter?>" class="outer nette-collapsed">
			<table>
			<?php

foreach($list['user']as$k=>$v){echo'<tr><th>',htmlspecialchars($k),'</th>';echo'<td>',NDebugHelpers::clickableDump($v),"</td></tr>\n";}?>
			</table>
			</div>
			<?php endif?>


			<h3><a href="#" rel="netteBsPnl<?php echo++$counter?>">Included files <abbr>&#x25ba;</abbr></a> (<?php echo
count(get_included_files())?>)</h3>
			<div id="netteBsPnl<?php echo$counter?>" class="outer nette-collapsed">
			<table>
			<?php

foreach(get_included_files()as$v){echo'<tr><td>',htmlspecialchars($v),"</td></tr>\n";}?>
			</table>
			</div>


			<h3><a href="#" rel="netteBsPnl<?php echo++$counter?>">Configuration options <abbr>&#x25ba;</abbr></a></h3>
			<div id="netteBsPnl<?php echo$counter?>" class="outer nette-collapsed">
			<?php ob_start();@phpinfo(INFO_CONFIGURATION|INFO_MODULES);echo
preg_replace('#^.+<body>|</body>.+$#s','',ob_get_clean())?>
			</div>
		</div></div>



		<div class="panel">
		<h2><a href="#" rel="netteBsPnl<?php echo++$counter?>">HTTP request <abbr>&#x25ba;</abbr></a></h2>

		<div id="netteBsPnl<?php echo$counter?>" class="nette-collapsed inner">
			<?php if(function_exists('apache_request_headers')):?>
			<h3>Headers</h3>
			<div class="outer">
			<table>
			<?php

foreach(apache_request_headers()as$k=>$v)echo'<tr><th>',htmlspecialchars($k),'</th><td>',htmlspecialchars($v),"</td></tr>\n";?>
			</table>
			</div>
			<?php endif?>


			<?php foreach(array('_GET','_POST','_COOKIE')as$name):?>
			<h3>$<?php echo
htmlspecialchars($name)?></h3>
			<?php if(empty($GLOBALS[$name])):?>
			<p><i>empty</i></p>
			<?php else:?>
			<div class="outer">
			<table>
			<?php

foreach($GLOBALS[$name]as$k=>$v)echo'<tr><th>',htmlspecialchars($k),'</th><td>',NDebugHelpers::clickableDump($v),"</td></tr>\n";?>
			</table>
			</div>
			<?php endif?>
			<?php endforeach?>
		</div></div>



		<div class="panel">
		<h2><a href="#" rel="netteBsPnl<?php echo++$counter?>">HTTP response <abbr>&#x25ba;</abbr></a></h2>

		<div id="netteBsPnl<?php echo$counter?>" class="nette-collapsed inner">
			<h3>Headers</h3>
			<?php if(headers_list()):?>
			<pre><?php

foreach(headers_list()as$s)echo
htmlspecialchars($s),'<br>';?></pre>
			<?php else:?>
			<p><i>no headers</i></p>
			<?php endif?>
		</div></div>



		<?php foreach($bottomPanels
as$panel):?>
		<div class="panel">
			<h2><a href="#" rel="netteBsPnl<?php echo++$counter?>"><?php echo
htmlSpecialChars($panel['tab'])?> <abbr>&#x25bc;</abbr></a></h2>

			<div id="netteBsPnl<?php echo$counter?>" class="inner">
			<?php echo$panel['panel']?>
		</div></div>
		<?php endforeach?>



		<ul>
			<li>Report generated at <?php echo@date('Y/m/d H:i:s',NDebugger::$time)?></li>
			<?php if(preg_match('#^https?://#',NDebugger::$source)):?>
				<li><a href="<?php echo
htmlSpecialChars(NDebugger::$source)?>"><?php echo
htmlSpecialChars(NDebugger::$source)?></a></li>
			<?php elseif(NDebugger::$source):?>
				<li><?php echo
htmlSpecialChars(NDebugger::$source)?></li>
			<?php endif?>
			<li>PHP <?php echo
htmlSpecialChars(PHP_VERSION)?></li>
			<?php if(isset($_SERVER['SERVER_SOFTWARE'])):?><li><?php echo
htmlSpecialChars($_SERVER['SERVER_SOFTWARE'])?></li><?php endif?>
			<?php if(class_exists('NFramework')):?><li><?php echo
htmlSpecialChars('Nette Framework '.NFramework::VERSION)?> <i>(revision <?php echo
htmlSpecialChars(NFramework::REVISION)?>)</i></li><?php endif?>
		</ul>
	</div>
</div>

<script type="text/javascript">/*<![CDATA[*/var bs=document.getElementById("netteBluescreen");document.body.appendChild(bs);document.onkeyup=function(b){b=b||window.event;if(27==b.keyCode&&!b.shiftKey&&!b.altKey&&!b.ctrlKey&&!b.metaKey)bs.onclick({target:document.getElementById("netteBluescreenIcon")})};
for(var i=0,styles=document.styleSheets;i<styles.length;i++)"nette"!==(styles[i].owningElement||styles[i].ownerNode).className?(styles[i].oldDisabled=styles[i].disabled,styles[i].disabled=!0):styles[i].addRule?styles[i].addRule(".nette-collapsed","display: none"):styles[i].insertRule(".nette-collapsed { display: none }",0);
bs.onclick=function(b){for(var b=b||window.event,a=b.target||b.srcElement;a&&a.tagName&&"a"!==a.tagName.toLowerCase();)a=a.parentNode;if(!a||!a.rel)return!0;for(var d=a.getElementsByTagName("abbr")[0],c="next"===a.rel?a.nextSibling:document.getElementById(a.rel);1!==c.nodeType;)c=c.nextSibling;b=c.currentStyle?"none"==c.currentStyle.display:"none"==getComputedStyle(c,null).display;try{d.innerHTML=String.fromCharCode(b?9660:9658)}catch(e){}c.style.display=b?"code"===c.tagName.toLowerCase()?"inline":
"block":"none";if("netteBluescreenIcon"===a.id){a=0;for(d=document.styleSheets;a<d.length;a++)if("nette"!==(d[a].owningElement||d[a].ownerNode).className)d[a].disabled=b?!0:d[a].oldDisabled}return!1};/*]]>*/</script>
</body>
</html>
<?php }public
static
function
highlightFile($file,$line,$lines=15,$vars=array()){$source=@file_get_contents($file);if($source){return
self::highlightPhp($source,$line,$lines,$vars);}}public
static
function
highlightPhp($source,$line,$lines=15,$vars=array()){if(function_exists('ini_set')){ini_set('highlight.comment','#998; font-style: italic');ini_set('highlight.default','#000');ini_set('highlight.html','#06B');ini_set('highlight.keyword','#D24; font-weight: bold');ini_set('highlight.string','#080');}$source=str_replace(array("\r\n","\r"),"\n",$source);$source=explode("\n",highlight_string($source,TRUE));$spans=1;$out=$source[0];$source=explode('<br />',$source[1]);array_unshift($source,NULL);$start=$i=max(1,$line-floor($lines*2/3));while(--$i>=1){if(preg_match('#.*(</?span[^>]*>)#',$source[$i],$m)){if($m[1]!=='</span>'){$spans++;$out.=$m[1];}break;}}$source=array_slice($source,$start,$lines,TRUE);end($source);$numWidth=strlen((string)key($source));foreach($source
as$n=>$s){$spans+=substr_count($s,'<span')-substr_count($s,'</span');$s=str_replace(array("\r","\n"),array('',''),$s);preg_match_all('#<[^>]+>#',$s,$tags);if($n==$line){$out.=sprintf("<span class='highlight'>%{$numWidth}s:    %s\n</span>%s",$n,strip_tags($s),implode('',$tags[0]));}else{$out.=sprintf("<span class='line'>%{$numWidth}s:</span>    %s\n",$n,$s);}}$out.=str_repeat('</span>',$spans).'</code>';$out=preg_replace_callback('#">\$(\w+)(&nbsp;)?</span>#',create_function('$m','extract(NCFix::$vars['.NCFix::uses(array('vars'=>$vars)).'], EXTR_REFS);
			return isset($vars[$m[1]])
				? \'" title="\' . str_replace(\'"\', \'&quot;\', strip_tags(NDebugHelpers::htmlDump($vars[$m[1]]))) . $m[0]
				: $m[0];
		'),$out);return"<pre><div>$out</div></pre>";}}final
class
NDefaultBarPanel
implements
IBarPanel{private$id;public$data;public
function
__construct($id){$this->id=$id;}public
function
getTab(){ob_start();$data=$this->data;if($this->id==='time'){?>
<span title="Execution time"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAJ6SURBVDjLjZO7T1NhGMY7Mji6uJgYt8bElTjof6CDg4sMSqIxJsRGB5F4TwQSIg1QKC0KWmkZEEsKtEcSxF5ohV5pKSicXqX3aqGn957z+PUEGopiGJ583/A+v3znvPkJAAjWR0VNJG0kGhKahCFhXcN3YBFfx8Kry6ym4xIzce88/fbWGY2k5WRb77UTTbWuYA9gDGg7EVmSIOF4g5T7HZKuMcSW5djWDyL0uRf0dCc8inYYxTcw9fAiCMBYB3gVj1z7gLhNTjKCqHkYP79KENC9Bq3uxrrqORzy+9D3tPAAccspVx1gWg0KbaZFbGllWFM+xrKkFQudV0CeDfJsjN4+C2nracjunoPq5VXIBrowMK4V1gG1LGyWdbZwCalsBYUyh2KFQzpXxVqkAGswD3+qBDpZwow9iYE5v26/VwfUQnnznyhvjguQYabIIpKpYD1ahI8UTT92MUSFuP5Z/9TBTgOgFrVjp3nakaG/0VmEfpX58pwzjUEquNk362s+PP8XYD/KpYTBHmRg9Wch0QX1R80dCZhYipudYQY2Auib8RmODVCa4hfUK4ngaiiLNFNFdKeCWWscXZMbWy9Unv9/gsIQU09a4pwvUeA3Uapy2C2wCKXL0DqTePLexbWPOv79E8f0UWrencZ2poxciUWZlKssB4bcHeE83NsFuMgpo2iIpMuNa1TNu4XjhggWvb+R2K3wZdLlAZl8Fd9jRb5sD+Xx0RJBx5gdom6VsMEFDyWF0WyCeSOFcDKPnRxZYTQL5Rc/nn1w4oFsBaIhC3r6FRh5erPRhYMyHdeFw4C6zkRhmijM7CnMu0AUZonCDCnRJBqSus5/ABD6Ba5CkQS8AAAAAElFTkSuQmCC"
/><?php echo
number_format((microtime(TRUE)-NDebugger::$time)*1000,1,'.',' ')?> ms</span>
<?php }elseif($this->id==='memory'){?>
<span title="The peak of allocated memory"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAGvSURBVDjLpZO7alZREEbXiSdqJJDKYJNCkPBXYq12prHwBezSCpaidnY+graCYO0DpLRTQcR3EFLl8p+9525xgkRIJJApB2bN+gZmqCouU+NZzVef9isyUYeIRD0RTz482xouBBBNHi5u4JlkgUfx+evhxQ2aJRrJ/oFjUWysXeG45cUBy+aoJ90Sj0LGFY6anw2o1y/mK2ZS5pQ50+2XiBbdCvPk+mpw2OM/Bo92IJMhgiGCox+JeNEksIC11eLwvAhlzuAO37+BG9y9x3FTuiWTzhH61QFvdg5AdAZIB3Mw50AKsaRJYlGsX0tymTzf2y1TR9WwbogYY3ZhxR26gBmocrxMuhZNE435FtmSx1tP8QgiHEvj45d3jNlONouAKrjjzWaDv4CkmmNu/Pz9CzVh++Yd2rIz5tTnwdZmAzNymXT9F5AtMFeaTogJYkJfdsaaGpyO4E62pJ0yUCtKQFxo0hAT1JU2CWNOJ5vvP4AIcKeao17c2ljFE8SKEkVdWWxu42GYK9KE4c3O20pzSpyyoCx4v/6ECkCTCqccKorNxR5uSXgQnmQkw2Xf+Q+0iqQ9Ap64TwAAAABJRU5ErkJggg=="
/><?php echo
function_exists('memory_get_peak_usage')?number_format(memory_get_peak_usage()/1000000,2,'.',' '):'n/a';?> MB</span>
<?php }elseif($this->id==='dumps'&&$this->data){?>
<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAIASURBVDjLpVPPaxNREJ6Vt01caH4oWk1T0ZKlGIo9RG+BUsEK4kEP/Q8qPXnpqRdPBf8A8Wahhx7FQ0GF9FJ6UksqwfTSBDGyB5HkkphC9tfb7jfbtyQQTx142byZ75v5ZnZWC4KALmICPy+2DkvKIX2f/POz83LxCL7nrz+WPNcll49DrhM9v7xdO9JW330DuXrrqkFSgig5iR2Cfv3t3gNxOnv5BwU+eZ5HuON5/PMPJZKJ+yKQfpW0S7TxdC6WJaWkyvff1LDaFRAeLZj05MHsiPTS6hua0PUqtwC5sHq9zv9RYWl+nu5cETcnJ1M0M5WlWq3GsX6/T+VymRzHDluZiGYAAsw0TQahV8uyyGq1qFgskm0bHIO/1+sx1rFtchJhArwEyIQ1Gg2WD2A6nWawHQJVDIWgIJfLhQowTIeE9D0mKAU8qPC0220afsWFQoH93W6X7yCDJ+DEBeBmsxnPIJVKxWQVUwry+XyUwBlKMKwA8jqdDhOVCqVAzQDVvXAXhOdGBFgymYwrGoZBmUyGjxCCdF0fSahaFdgoTHRxfTveMCXvWfkuE3Y+f40qhgT/nMitupzApdvT18bu+YeDQwY9Xl4aG9/d/URiMBhQq/dvZMeVghtT17lSZW9/rAKsvPa/r9Fc2dw+Pe0/xI6kM9mT5vtXy+Nw2kU/5zOGRpvuMIu0YAAAAABJRU5ErkJggg==" />variables
<?php }elseif($this->id==='errors'&&$this->data){?>
<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAIsSURBVDjLpVNLSJQBEP7+h6uu62vLVAJDW1KQTMrINQ1vPQzq1GOpa9EppGOHLh0kCEKL7JBEhVCHihAsESyJiE4FWShGRmauu7KYiv6Pma+DGoFrBQ7MzGFmPr5vmDFIYj1mr1WYfrHPovA9VVOqbC7e/1rS9ZlrAVDYHig5WB0oPtBI0TNrUiC5yhP9jeF4X8NPcWfopoY48XT39PjjXeF0vWkZqOjd7LJYrmGasHPCCJbHwhS9/F8M4s8baid764Xi0Ilfp5voorpJfn2wwx/r3l77TwZUvR+qajXVn8PnvocYfXYH6k2ioOaCpaIdf11ivDcayyiMVudsOYqFb60gARJYHG9DbqQFmSVNjaO3K2NpAeK90ZCqtgcrjkP9aUCXp0moetDFEeRXnYCKXhm+uTW0CkBFu4JlxzZkFlbASz4CQGQVBFeEwZm8geyiMuRVntzsL3oXV+YMkvjRsydC1U+lhwZsWXgHb+oWVAEzIwvzyVlk5igsi7DymmHlHsFQR50rjl+981Jy1Fw6Gu0ObTtnU+cgs28AKgDiy+Awpj5OACBAhZ/qh2HOo6i+NeA73jUAML4/qWux8mt6NjW1w599CS9xb0mSEqQBEDAtwqALUmBaG5FV3oYPnTHMjAwetlWksyByaukxQg2wQ9FlccaK/OXA3/uAEUDp3rNIDQ1ctSk6kHh1/jRFoaL4M4snEMeD73gQx4M4PsT1IZ5AfYH68tZY7zv/ApRMY9mnuVMvAAAAAElFTkSuQmCC"
/><span class="nette-warning"><?php echo
array_sum($data)?> errors</span>
<?php }return
ob_get_clean();}public
function
getPanel(){ob_start();$data=$this->data;if($this->id==='dumps'){?>
<style>#nette-debug .nette-DumpPanel h2{font:11pt/1.5 sans-serif;margin:0;padding:2px 8px;background:#3484d2;color:white}#nette-debug .nette-DumpPanel table{width:100%}#nette-debug .nette-DumpPanel a{color:#333;background:transparent}#nette-debug .nette-DumpPanel a abbr{font-family:sans-serif;color:#999}#nette-debug .nette-DumpPanel pre .php-array,#nette-debug .nette-DumpPanel pre .php-object{color:#c16549}</style>


<h1>Dumped variables</h1>

<div class="nette-inner nette-DumpPanel">
<?php foreach($data
as$item):?>
	<?php if($item['title']):?>
	<h2><?php echo
htmlspecialchars($item['title'])?></h2>
	<?php endif?>

	<table>
	<?php $i=0?>
	<?php foreach($item['dump']as$key=>$dump):?>
	<tr class="<?php echo$i++%
2?'nette-alt':''?>">
		<th><?php echo
htmlspecialchars($key)?></th>
		<td><?php echo$dump?></td>
	</tr>
	<?php endforeach?>
	</table>
<?php endforeach?>
</div>
<?php }elseif($this->id==='errors'){?>
<h1>Errors</h1>

<div class="nette-inner">
<table>
<?php $i=0?>
<?php foreach($data
as$item=>$count):list($message,$file,$line)=explode('|',$item)?>
<tr class="<?php echo$i++%
2?'nette-alt':''?>">
	<td class="nette-right"><?php echo$count?"$count\xC3\x97":''?></td>
	<td><pre><?php echo
htmlspecialchars($message),' in ',NDebugHelpers::editorLink($file,$line),':',$line?></pre></td>
</tr>
<?php endforeach?>
</table>
</div>
<?php }return
ob_get_clean();}}class
NFireLogger{const
DEBUG='debug',INFO='info',WARNING='warning',ERROR='error',CRITICAL='critical';private
static$payload=array('logs'=>array());public
static
function
log($message,$priority=self::DEBUG){if(!isset($_SERVER['HTTP_X_FIRELOGGER'])||headers_sent()){return
FALSE;}$item=array('name'=>'PHP','level'=>$priority,'order'=>count(self::$payload['logs']),'time'=>str_pad(number_format((microtime(TRUE)-NDebugger::$time)*1000,1,'.',' '),8,'0',STR_PAD_LEFT).' ms','template'=>'','message'=>'','style'=>'background:#767ab6');$args=func_get_args();if(isset($args[0])&&is_string($args[0])){$item['template']=array_shift($args);}if(isset($args[0])&&$args[0]instanceof
Exception){$e=array_shift($args);$trace=$e->getTrace();if(isset($trace[0]['class'])&&$trace[0]['class']==='NDebugger'&&($trace[0]['function']==='_shutdownHandler'||$trace[0]['function']==='_errorHandler')){unset($trace[0]);}$file=str_replace(dirname(dirname(dirname($e->getFile()))),"\xE2\x80\xA6",$e->getFile());$item['template']=($e
instanceof
ErrorException?'':get_class($e).': ').$e->getMessage().($e->getCode()?' #'.$e->getCode():'').' in '.$file.':'.$e->getLine();$item['pathname']=$e->getFile();$item['lineno']=$e->getLine();}else{$trace=debug_backtrace();if(isset($trace[1]['class'])&&$trace[1]['class']==='NDebugger'&&($trace[1]['function']==='fireLog')){unset($trace[0]);}foreach($trace
as$frame){if(isset($frame['file'])&&is_file($frame['file'])){$item['pathname']=$frame['file'];$item['lineno']=$frame['line'];break;}}}$item['exc_info']=array('','',array());$item['exc_frames']=array();foreach($trace
as$frame){$frame+=array('file'=>NULL,'line'=>NULL,'class'=>NULL,'type'=>NULL,'function'=>NULL,'object'=>NULL,'args'=>NULL);$item['exc_info'][2][]=array($frame['file'],$frame['line'],"$frame[class]$frame[type]$frame[function]",$frame['object']);$item['exc_frames'][]=$frame['args'];}if(isset($args[0])&&in_array($args[0],array(self::DEBUG,self::INFO,self::WARNING,self::ERROR,self::CRITICAL),TRUE)){$item['level']=array_shift($args);}$item['args']=$args;self::$payload['logs'][]=self::jsonDump($item,-1);foreach(str_split(base64_encode(@json_encode(self::$payload)),4990)as$k=>$v){header("FireLogger-de11e-$k:$v");}return
TRUE;}private
static
function
jsonDump(&$var,$level=0){if(is_bool($var)||is_null($var)||is_int($var)||is_float($var)){return$var;}elseif(is_string($var)){if(NDebugger::$maxLen&&strlen($var)>NDebugger::$maxLen){$var=substr($var,0,NDebugger::$maxLen)." \xE2\x80\xA6 ";}return@iconv('UTF-16','UTF-8//IGNORE',iconv('UTF-8','UTF-16//IGNORE',$var));}elseif(is_array($var)){static$marker;if($marker===NULL){$marker=uniqid("\x00",TRUE);}if(isset($var[$marker])){return"\xE2\x80\xA6RECURSION\xE2\x80\xA6";}elseif($level<NDebugger::$maxDepth||!NDebugger::$maxDepth){$var[$marker]=TRUE;$res=array();foreach($var
as$k=>&$v){if($k!==$marker){$res[self::jsonDump($k)]=self::jsonDump($v,$level+1);}}unset($var[$marker]);return$res;}else{return" \xE2\x80\xA6 ";}}elseif(is_object($var)){$arr=(array)$var;static$list=array();if(in_array($var,$list,TRUE)){return"\xE2\x80\xA6RECURSION\xE2\x80\xA6";}elseif($level<NDebugger::$maxDepth||!NDebugger::$maxDepth){$list[]=$var;$res=array("\x00"=>'(object) '.get_class($var));foreach($arr
as$k=>&$v){if($k[0]==="\x00"){$k=substr($k,strrpos($k,"\x00")+1);}$res[self::jsonDump($k)]=self::jsonDump($v,$level+1);}array_pop($list);return$res;}else{return" \xE2\x80\xA6 ";}}elseif(is_resource($var)){return"resource ".get_resource_type($var);}else{return"unknown type";}}}final
class
NDebugHelpers{public
static
function
editorLink($file,$line){if(NDebugger::$editor&&is_file($file)){$dir=dirname(strtr($file,'/',DIRECTORY_SEPARATOR));$base=isset($_SERVER['SCRIPT_FILENAME'])?dirname(dirname(strtr($_SERVER['SCRIPT_FILENAME'],'/',DIRECTORY_SEPARATOR))):dirname($dir);if(substr($dir,0,strlen($base))===$base){$dir='...'.substr($dir,strlen($base));}return
NHtml::el('a')->href(strtr(NDebugger::$editor,array('%file'=>rawurlencode($file),'%line'=>$line)))->title("$file:$line")->setHtml(htmlSpecialChars(rtrim($dir,DIRECTORY_SEPARATOR)).DIRECTORY_SEPARATOR.'<b>'.htmlSpecialChars(basename($file)).'</b>');}else{return
NHtml::el('span')->setText($file);}}public
static
function
textDump($var){return
htmlspecialchars_decode(strip_tags(self::htmlDump($var)),ENT_QUOTES);}public
static
function
htmlDump(&$var,$level=0){static$tableUtf,$tableBin,$reBinary='#[^\x09\x0A\x0D\x20-\x7E\xA0-\x{10FFFF}]#u';if($tableUtf===NULL){foreach(range("\x00","\xFF")as$ch){if(ord($ch)<32&&strpos("\r\n\t",$ch)===FALSE){$tableUtf[$ch]=$tableBin[$ch]='\\x'.str_pad(dechex(ord($ch)),2,'0',STR_PAD_LEFT);}elseif(ord($ch)<127){$tableUtf[$ch]=$tableBin[$ch]=$ch;}else{$tableUtf[$ch]=$ch;$tableBin[$ch]='\\x'.dechex(ord($ch));}}$tableBin["\\"]='\\\\';$tableBin["\r"]='\\r';$tableBin["\n"]='\\n';$tableBin["\t"]='\\t';$tableUtf['\\x']=$tableBin['\\x']='\\\\x';}if(is_bool($var)){return'<span class="php-bool">'.($var?'TRUE':'FALSE')."</span>\n";}elseif($var===NULL){return"<span class=\"php-null\">NULL</span>\n";}elseif(is_int($var)){return"<span class=\"php-int\">$var</span>\n";}elseif(is_float($var)){$var=var_export($var,TRUE);if(strpos($var,'.')===FALSE){$var.='.0';}return"<span class=\"php-float\">$var</span>\n";}elseif(is_string($var)){if(NDebugger::$maxLen&&strlen($var)>NDebugger::$maxLen){$s=htmlSpecialChars(substr($var,0,NDebugger::$maxLen),ENT_NOQUOTES,'ISO-8859-1').' ... ';}else{$s=htmlSpecialChars($var,ENT_NOQUOTES,'ISO-8859-1');}$s=strtr($s,preg_match($reBinary,$s)||preg_last_error()?$tableBin:$tableUtf);$len=strlen($var);return"<span class=\"php-string\">\"$s\"</span>".($len>1?" ($len)":"")."\n";}elseif(is_array($var)){$s='<span class="php-array">array</span>('.count($var).") ";$space=str_repeat($space1='   ',$level);$brackets=range(0,count($var)-1)===array_keys($var)?"[]":"{}";static$marker;if($marker===NULL){$marker=uniqid("\x00",TRUE);}if(empty($var)){}elseif(isset($var[$marker])){$brackets=$var[$marker];$s.="$brackets[0] *RECURSION* $brackets[1]";}elseif($level<NDebugger::$maxDepth||!NDebugger::$maxDepth){$s.="<code>$brackets[0]\n";$var[$marker]=$brackets;foreach($var
as$k=>&$v){if($k===$marker){continue;}$k=strtr($k,preg_match($reBinary,$k)||preg_last_error()?$tableBin:$tableUtf);$k=htmlSpecialChars(preg_match('#^\w+$#',$k)?$k:"\"$k\"");$s.="$space$space1<span class=\"php-key\">$k</span> => ".self::htmlDump($v,$level+1);}unset($var[$marker]);$s.="$space$brackets[1]</code>";}else{$s.="$brackets[0] ... $brackets[1]";}return$s."\n";}elseif(is_object($var)){if($var
instanceof
Closure){$rc=new
ReflectionFunction($var);$arr=array();foreach($rc->getParameters()as$param){$arr[]='$'.$param->getName();}$arr=array('file'=>$rc->getFileName(),'line'=>$rc->getStartLine(),'parameters'=>implode(', ',$arr));}else{$arr=(array)$var;}$s='<span class="php-object">'.get_class($var)."</span>(".count($arr).") ";$space=str_repeat($space1='   ',$level);static$list=array();if(empty($arr)){}elseif(in_array($var,$list,TRUE)){$s.="{ *RECURSION* }";}elseif($level<NDebugger::$maxDepth||!NDebugger::$maxDepth||$var
instanceof
Closure){$s.="<code>{\n";$list[]=$var;foreach($arr
as$k=>&$v){$m='';if($k[0]==="\x00"){$m=' <span class="php-visibility">'.($k[1]==='*'?'protected':'private').'</span>';$k=substr($k,strrpos($k,"\x00")+1);}$k=strtr($k,preg_match($reBinary,$k)||preg_last_error()?$tableBin:$tableUtf);$k=htmlSpecialChars(preg_match('#^\w+$#',$k)?$k:"\"$k\"");$s.="$space$space1<span class=\"php-key\">$k</span>$m => ".self::htmlDump($v,$level+1);}array_pop($list);$s.="$space}</code>";}else{$s.="{ ... }";}return$s."\n";}elseif(is_resource($var)){$type=get_resource_type($var);$s='<span class="php-resource">'.htmlSpecialChars($type)." resource</span> ";static$info=array('stream'=>'stream_get_meta_data','curl'=>'curl_getinfo');if(isset($info[$type])){$space=str_repeat($space1='   ',$level);$s.="<code>{\n";foreach(call_user_func($info[$type],$var)as$k=>$v){$s.=$space.$space1.'<span class="php-key">'.htmlSpecialChars($k)."</span> => ".self::htmlDump($v,$level+1);}$s.="$space}</code>";}return$s."\n";}else{return"<span>unknown type</span>\n";}}public
static
function
clickableDump($dump,$collapsed=FALSE){return'<pre class="nette-dump">'.preg_replace_callback('#^( *)((?>[^(\r\n]{1,200}))\((\d+)\) <code>#m',create_function('$m','extract(NCFix::$vars['.NCFix::uses(array('collapsed'=>$collapsed)).'], EXTR_REFS);
				return "$m[1]<a href=\'#\' rel=\'next\'>$m[2]($m[3]) "
					. (($m[1] || !$collapsed) && ($m[3] < 7)
					? \'<abbr>&#x25bc;</abbr> </a><code>\'
					: \'<abbr>&#x25ba;</abbr> </a><code class="nette-collapsed">\');
			'),self::htmlDump($dump)).'</pre>';}public
static
function
findTrace(array$trace,$method,&$index=NULL){$m=explode('::',$method);foreach($trace
as$i=>$item){if(isset($item['function'])&&$item['function']===end($m)&&isset($item['class'])===isset($m[1])&&(!isset($item['class'])||$item['class']===$m[0]||is_subclass_of($item['class'],$m[0]))){$index=$i;return$item;}}}}class
NLogger{const
DEBUG='debug',INFO='info',WARNING='warning',ERROR='error',CRITICAL='critical';public
static$emailSnooze=172800;public$mailer=array(__CLASS__,'defaultMailer');public$directory;public$email;public
function
log($message,$priority=self::INFO){if(!is_dir($this->directory)){throw
new
DirectoryNotFoundException("Directory '$this->directory' is not found or is not directory.");}if(is_array($message)){$message=implode(' ',$message);}$res=error_log(trim($message).PHP_EOL,3,$this->directory.'/'.strtolower($priority).'.log');if(($priority===self::ERROR||$priority===self::CRITICAL)&&$this->email&&$this->mailer&&@filemtime($this->directory.'/email-sent')+self::$emailSnooze<time()&&@file_put_contents($this->directory.'/email-sent','sent')){call_user_func($this->mailer,$message,$this->email);}return$res;}public
static
function
defaultMailer($message,$email){$host=php_uname('n');foreach(array('HTTP_HOST','SERVER_NAME','HOSTNAME')as$item){if(isset($_SERVER[$item])){$host=$_SERVER[$item];break;}}$parts=str_replace(array("\r\n","\n"),array("\n",PHP_EOL),array('headers'=>implode("\n",array("From: noreply@$host",'X-Mailer: Nette Framework','Content-Type: text/plain; charset=UTF-8','Content-Transfer-Encoding: 8bit'))."\n",'subject'=>"PHP: An error occurred on the server $host",'body'=>"[".@date('Y-m-d H:i:s')."] $message"));mail($email,$parts['subject'],$parts['body'],$parts['headers']);}}class
NHtml
implements
ArrayAccess,Countable{private$name;private$isEmpty;public$attrs=array();protected$children=array();public
static$xhtml=TRUE;public
static$emptyElements=array('img'=>1,'hr'=>1,'br'=>1,'input'=>1,'meta'=>1,'area'=>1,'embed'=>1,'keygen'=>1,'source'=>1,'base'=>1,'col'=>1,'link'=>1,'param'=>1,'basefont'=>1,'frame'=>1,'isindex'=>1,'wbr'=>1,'command'=>1);public
static
function
el($name=NULL,$attrs=NULL){$el=new
self;$parts=explode(' ',$name,2);$el->setName($parts[0]);if(is_array($attrs)){$el->attrs=$attrs;}elseif($attrs!==NULL){$el->setText($attrs);}return$el;}final
public
function
setName($name,$isEmpty=NULL){if($name!==NULL&&!is_string($name)){throw
new
InvalidArgumentException("Name must be string or NULL, ".gettype($name)." given.");}$this->name=$name;$this->isEmpty=$isEmpty===NULL?isset(self::$emptyElements[$name]):(bool)$isEmpty;return$this;}final
public
function
getName(){return$this->name;}final
public
function
isEmpty(){return$this->isEmpty;}public
function
addAttributes(array$attrs){$this->attrs=$attrs+$this->attrs;return$this;}final
public
function
__set($name,$value){$this->attrs[$name]=$value;}final
public
function&__get($name){return$this->attrs[$name];}final
public
function
__unset($name){unset($this->attrs[$name]);}final
public
function
__call($m,$args){$p=substr($m,0,3);if($p==='get'||$p==='set'||$p==='add'){$m=substr($m,3);$m[0]=$m[0]|"\x20";if($p==='get'){return
isset($this->attrs[$m])?$this->attrs[$m]:NULL;}elseif($p==='add'){$args[]=TRUE;}}if(count($args)===0){}elseif(count($args)===1){$this->attrs[$m]=$args[0];}elseif((string)$args[0]===''){$tmp=&$this->attrs[$m];}elseif(!isset($this->attrs[$m])||is_array($this->attrs[$m])){$this->attrs[$m][$args[0]]=$args[1];}else{$this->attrs[$m]=array($this->attrs[$m],$args[0]=>$args[1]);}return$this;}final
public
function
href($path,$query=NULL){if($query){$query=http_build_query($query,NULL,'&');if($query!==''){$path.='?'.$query;}}$this->attrs['href']=$path;return$this;}final
public
function
setHtml($html){if($html===NULL){$html='';}elseif(is_array($html)){throw
new
InvalidArgumentException("Textual content must be a scalar, ".gettype($html)." given.");}else{$html=(string)$html;}$this->removeChildren();$this->children[]=$html;return$this;}final
public
function
getHtml(){$s='';foreach($this->children
as$child){if(is_object($child)){$s.=$child->render();}else{$s.=$child;}}return$s;}final
public
function
setText($text){if(!is_array($text)){$text=htmlspecialchars((string)$text,ENT_NOQUOTES);}return$this->setHtml($text);}final
public
function
getText(){return
html_entity_decode(strip_tags($this->getHtml()),ENT_QUOTES,'UTF-8');}final
public
function
add($child){return$this->insert(NULL,$child);}final
public
function
create($name,$attrs=NULL){$this->insert(NULL,$child=self::el($name,$attrs));return$child;}public
function
insert($index,$child,$replace=FALSE){if($child
instanceof
NHtml||is_scalar($child)){if($index===NULL){$this->children[]=$child;}else{array_splice($this->children,(int)$index,$replace?1:0,array($child));}}else{throw
new
InvalidArgumentException("Child node must be scalar or Html object, ".(is_object($child)?get_class($child):gettype($child))." given.");}return$this;}final
public
function
offsetSet($index,$child){$this->insert($index,$child,TRUE);}final
public
function
offsetGet($index){return$this->children[$index];}final
public
function
offsetExists($index){return
isset($this->children[$index]);}public
function
offsetUnset($index){if(isset($this->children[$index])){array_splice($this->children,(int)$index,1);}}final
public
function
count(){return
count($this->children);}public
function
removeChildren(){$this->children=array();}final
public
function
getChildren(){return$this->children;}final
public
function
render($indent=NULL){$s=$this->startTag();if(!$this->isEmpty){if($indent!==NULL){$indent++;}foreach($this->children
as$child){if(is_object($child)){$s.=$child->render($indent);}else{$s.=$child;}}$s.=$this->endTag();}if($indent!==NULL){return"\n".str_repeat("\t",$indent-1).$s."\n".str_repeat("\t",max(0,$indent-2));}return$s;}final
public
function
__toString(){return$this->render();}final
public
function
startTag(){if($this->name){return'<'.$this->name.$this->attributes().(self::$xhtml&&$this->isEmpty?' />':'>');}else{return'';}}final
public
function
endTag(){return$this->name&&!$this->isEmpty?'</'.$this->name.'>':'';}final
public
function
attributes(){if(!is_array($this->attrs)){return'';}$s='';foreach($this->attrs
as$key=>$value){if($value===NULL||$value===FALSE){continue;}elseif($value===TRUE){if(self::$xhtml){$s.=' '.$key.'="'.$key.'"';}else{$s.=' '.$key;}continue;}elseif(is_array($value)){if($key==='data'){foreach($value
as$k=>$v){if($v!==NULL&&$v!==FALSE){$s.=' data-'.$k.'="'.htmlspecialchars((string)$v).'"';}}continue;}$tmp=NULL;foreach($value
as$k=>$v){if($v!=NULL){$tmp[]=$v===TRUE?$k:(is_string($k)?$k.':'.$v:$v);}}if($tmp===NULL){continue;}$value=implode($key==='style'||!strncmp($key,'on',2)?';':' ',$tmp);}else{$value=(string)$value;}$s.=' '.$key.'="'.htmlspecialchars($value).'"';}$s=str_replace('@','&#64;',$s);return$s;}public
function
__clone(){foreach($this->children
as$key=>$value){if(is_object($value)){$this->children[$key]=clone$value;}}}}function
debug(){NDebugger::$strictMode=TRUE;NDebugger::enable(NDebugger::DEVELOPMENT);}function
dump($var){foreach(func_get_args()as$arg){NDebugger::dump($arg);}return$var;}function
dlog($var=NULL){if(func_num_args()===0){NDebugger::log(new
Exception,'dlog');}foreach(func_get_args()as$arg){NDebugger::log($arg,'dlog');}return$var;}final
class
NDebugger{public
static$productionMode;public
static$consoleMode;public
static$time;private
static$ajaxDetected;public
static$source;public
static$editor='editor://open/?file=%file&line=%line';public
static$browser;public
static$maxDepth=3;public
static$maxLen=150;public
static$showLocation=FALSE;public
static$consoleColors=array('bool'=>'1;33','null'=>'1;33','int'=>'1;36','float'=>'1;36','string'=>'1;32','array'=>'1;31','key'=>'1;37','object'=>'1;31','visibility'=>'1;30','resource'=>'1;37');const
DEVELOPMENT=FALSE,PRODUCTION=TRUE,DETECT=NULL;public
static$blueScreen;public
static$strictMode=FALSE;public
static$scream=FALSE;public
static$onFatalError=array();private
static$enabled=FALSE;private
static$lastError=FALSE;public
static$logger;public
static$fireLogger;public
static$logDirectory;public
static$email;public
static$mailer;public
static$emailSnooze;public
static$bar;private
static$errorPanel;private
static$dumpPanel;const
DEBUG='debug',INFO='info',WARNING='warning',ERROR='error',CRITICAL='critical';final
public
function
__construct(){throw
new
NStaticClassException;}public
static
function
_init(){self::$time=isset($_SERVER['REQUEST_TIME_FLOAT'])?$_SERVER['REQUEST_TIME_FLOAT']:microtime(TRUE);self::$consoleMode=PHP_SAPI==='cli';self::$productionMode=self::DETECT;if(self::$consoleMode){self::$source=empty($_SERVER['argv'])?'cli':'cli: '.implode(' ',$_SERVER['argv']);}else{self::$ajaxDetected=isset($_SERVER['HTTP_X_REQUESTED_WITH'])&&$_SERVER['HTTP_X_REQUESTED_WITH']==='XMLHttpRequest';if(isset($_SERVER['REQUEST_URI'])){self::$source=(isset($_SERVER['HTTPS'])&&strcasecmp($_SERVER['HTTPS'],'off')?'https://':'http://').(isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:(isset($_SERVER['SERVER_NAME'])?$_SERVER['SERVER_NAME']:'')).$_SERVER['REQUEST_URI'];}}self::$logger=new
NLogger;self::$logDirectory=&self::$logger->directory;self::$email=&self::$logger->email;self::$mailer=&self::$logger->mailer;self::$emailSnooze=&NLogger::$emailSnooze;self::$fireLogger=new
NFireLogger;self::$blueScreen=new
NDebugBlueScreen;self::$blueScreen->addPanel(create_function('$e','
			if ($e instanceof NTemplateException) {
				return array(
					\'tab\' => \'Template\',
					\'panel\' => \'<p><b>File:</b> \' . NDebugHelpers::editorLink($e->sourceFile, $e->sourceLine)
					. \'&nbsp; <b>Line:</b> \' . ($e->sourceLine ? $e->sourceLine : \'n/a\') . \'</p>\'
					. ($e->sourceLine ? NDebugBlueScreen::highlightFile($e->sourceFile, $e->sourceLine) : \'\')
				);
			} elseif ($e instanceof NNeonException && preg_match(\'#line (\\d+)#\', $e->getMessage(), $m)) {
				if ($item = NDebugHelpers::findTrace($e->getTrace(), \'NConfigNeonAdapter::load\')) {
					return array(
						\'tab\' => \'NEON\',
						\'panel\' => \'<p><b>File:</b> \' . NDebugHelpers::editorLink($item[\'args\'][0], $m[1]) . \'&nbsp; <b>Line:</b> \' . $m[1] . \'</p>\'
							. NDebugBlueScreen::highlightFile($item[\'args\'][0], $m[1])
					);
				} elseif ($item = NDebugHelpers::findTrace($e->getTrace(), \'NNeon::decode\')) {
					return array(
						\'tab\' => \'NEON\',
						\'panel\' => NDebugBlueScreen::highlightPhp($item[\'args\'][0], $m[1])
					);
				}
			}
		'));self::$bar=new
NDebugBar;self::$bar->addPanel(new
NDefaultBarPanel('time'));self::$bar->addPanel(new
NDefaultBarPanel('memory'));self::$bar->addPanel(self::$errorPanel=new
NDefaultBarPanel('errors'));self::$bar->addPanel(self::$dumpPanel=new
NDefaultBarPanel('dumps'));}public
static
function
enable($mode=NULL,$logDirectory=NULL,$email=NULL){error_reporting(E_ALL|E_STRICT);if(is_bool($mode)){self::$productionMode=$mode;}elseif($mode!==self::DETECT||self::$productionMode===NULL){$list=is_string($mode)?preg_split('#[,\s]+#',$mode):(array)$mode;if(!isset($_SERVER['HTTP_X_FORWARDED_FOR'])){$list[]='127.0.0.1';$list[]='::1';}self::$productionMode=!in_array(isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:php_uname('n'),$list,TRUE);}if(is_string($logDirectory)){self::$logDirectory=realpath($logDirectory);if(self::$logDirectory===FALSE){throw
new
DirectoryNotFoundException("Directory '$logDirectory' is not found.");}}elseif($logDirectory===FALSE){self::$logDirectory=FALSE;}elseif(self::$logDirectory===NULL){self::$logDirectory=defined('APP_DIR')?APP_DIR.'/../log':getcwd().'/log';}if(self::$logDirectory){ini_set('error_log',self::$logDirectory.'/php_error.log');}if(function_exists('ini_set')){ini_set('display_errors',!self::$productionMode);ini_set('html_errors',FALSE);ini_set('log_errors',FALSE);}elseif(ini_get('display_errors')!=!self::$productionMode&&ini_get('display_errors')!==(self::$productionMode?'stderr':'stdout')){throw
new
NotSupportedException('Function ini_set() must be enabled.');}if($email){if(!is_string($email)){throw
new
InvalidArgumentException('Email address must be a string.');}self::$email=$email;}if(!self::$enabled){register_shutdown_function(array(__CLASS__,'_shutdownHandler'));set_exception_handler(array(__CLASS__,'_exceptionHandler'));set_error_handler(array(__CLASS__,'_errorHandler'));self::$enabled=TRUE;}}public
static
function
isEnabled(){return
self::$enabled;}public
static
function
log($message,$priority=self::INFO){if(self::$logDirectory===FALSE){return;}elseif(!self::$logDirectory){throw
new
InvalidStateException('Logging directory is not specified in NDebugger::$logDirectory.');}if($message
instanceof
Exception){$exception=$message;$message=($message
instanceof
FatalErrorException?'Fatal error: '.$exception->getMessage():get_class($exception).": ".$exception->getMessage())." in ".$exception->getFile().":".$exception->getLine();$hash=md5($exception.(method_exists($exception,'getPrevious')?$exception->getPrevious():(isset($exception->previous)?$exception->previous:'')));$exceptionFilename="exception-".@date('Y-m-d-H-i-s')."-$hash.html";foreach(new
DirectoryIterator(self::$logDirectory)as$entry){if(strpos($entry,$hash)){$exceptionFilename=$entry;$saved=TRUE;break;}}}elseif(!is_string($message)){$message=NDebugHelpers::textDump($message);}self::$logger->log(array(@date('[Y-m-d H-i-s]'),trim($message),self::$source?' @  '.self::$source:NULL,!empty($exceptionFilename)?' @@  '.$exceptionFilename:NULL),$priority);if(!empty($exceptionFilename)){$exceptionFilename=self::$logDirectory.'/'.$exceptionFilename;if(empty($saved)&&$logHandle=@fopen($exceptionFilename,'w')){ob_start();ob_start(create_function('$buffer','extract(NCFix::$vars['.NCFix::uses(array('logHandle'=>$logHandle)).'], EXTR_REFS); fwrite($logHandle, $buffer); '),4096);self::$blueScreen->render($exception);ob_end_flush();ob_end_clean();fclose($logHandle);}return
strtr($exceptionFilename,'\\/',DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR);}}public
static
function
_shutdownHandler(){if(!self::$enabled){return;}static$types=array(E_ERROR=>1,E_CORE_ERROR=>1,E_COMPILE_ERROR=>1,E_PARSE=>1);$error=error_get_last();if(isset($types[$error['type']])){self::_exceptionHandler(new
FatalErrorException($error['message'],0,$error['type'],$error['file'],$error['line'],NULL));}if(!connection_aborted()&&self::$bar&&!self::$productionMode&&self::isHtmlMode()){self::$bar->render();}}public
static
function
_exceptionHandler(Exception$exception){if(!headers_sent()){$protocol=isset($_SERVER['SERVER_PROTOCOL'])?$_SERVER['SERVER_PROTOCOL']:'HTTP/1.1';header($protocol.' 500',TRUE,500);}try{if(self::$productionMode){try{self::log($exception,self::ERROR);}catch(Exception$e){echo'FATAL ERROR: unable to log error';}if(self::$consoleMode){echo"ERROR: the server encountered an internal error and was unable to complete your request.\n";}elseif(self::isHtmlMode()){?>
<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name=robots content=noindex><meta name=generator content="Nette Framework">
<style>body{color:#333;background:white;width:500px;margin:100px auto}h1{font:bold 47px/1.5 sans-serif;margin:.6em 0}p{font:21px/1.5 Georgia,serif;margin:1.5em 0}small{font-size:70%;color:gray}</style>

<title>Server Error</title>

<h1>Server Error</h1>

<p>We're sorry! The server encountered an internal error and was unable to complete your request. Please try again later.</p>

<p><small>error 500</small></p>
<?php }}else{if(self::$consoleMode){echo"$exception\n";if($file=self::log($exception)){echo"(stored in $file)\n";if(self::$browser){exec(self::$browser.' '.escapeshellarg($file));}}}elseif(!connection_aborted()&&self::isHtmlMode()){self::$blueScreen->render($exception);if(self::$bar){self::$bar->render();}}elseif(!self::fireLog($exception,self::ERROR)){$file=self::log($exception);if(!headers_sent()){header("X-Nette-Error-Log: $file");}}}foreach(self::$onFatalError
as$handler){call_user_func($handler,$exception);}}catch(Exception$e){if(self::$productionMode){echo
self::isHtmlMode()?'<meta name=robots content=noindex>FATAL ERROR':'FATAL ERROR';}else{echo"FATAL ERROR: thrown ",get_class($e),': ',$e->getMessage(),"\nwhile processing ",get_class($exception),': ',$exception->getMessage(),"\n";}}self::$enabled=FALSE;exit(255);}public
static
function
_errorHandler($severity,$message,$file,$line,$context){if(self::$scream){error_reporting(E_ALL|E_STRICT);}if(self::$lastError!==FALSE&&($severity&error_reporting())===$severity){self::$lastError=new
ErrorException($message,0,$severity,$file,$line);return
NULL;}if($severity===E_RECOVERABLE_ERROR||$severity===E_USER_ERROR){throw
new
FatalErrorException($message,0,$severity,$file,$line,$context);}elseif(($severity&error_reporting())!==$severity){return
FALSE;}elseif(!self::$productionMode&&(is_bool(self::$strictMode)?self::$strictMode:((self::$strictMode&$severity)===$severity))){self::_exceptionHandler(new
FatalErrorException($message,0,$severity,$file,$line,$context));}static$types=array(E_WARNING=>'Warning',E_COMPILE_WARNING=>'Warning',E_USER_WARNING=>'Warning',E_NOTICE=>'Notice',E_USER_NOTICE=>'Notice',E_STRICT=>'Strict standards');if(PHP_VERSION_ID>=50300){$types+=array(E_DEPRECATED=>'Deprecated',E_USER_WARNING=>'Deprecated');}$message='PHP '.(isset($types[$severity])?$types[$severity]:'Unknown error').": $message";$count=&self::$errorPanel->data["$message|$file|$line"];if($count++){return
NULL;}elseif(self::$productionMode){self::log("$message in $file:$line",self::ERROR);return
NULL;}else{$ok=self::fireLog(new
ErrorException($message,0,$severity,$file,$line),self::WARNING);return!self::isHtmlMode()||(!self::$bar&&!$ok)?FALSE:NULL;}return
FALSE;}public
static
function
toStringException(Exception$exception){if(self::$enabled){self::_exceptionHandler($exception);}else{trigger_error($exception->getMessage(),E_USER_ERROR);}}public
static
function
tryError(){if(!self::$enabled&&self::$lastError===FALSE){set_error_handler(array(__CLASS__,'_errorHandler'));}self::$lastError=NULL;}public
static
function
catchError(&$error){if(!self::$enabled&&self::$lastError!==FALSE){restore_error_handler();}$error=self::$lastError;self::$lastError=FALSE;return(bool)$error;}public
static
function
dump($var,$return=FALSE){if(!$return&&self::$productionMode){return$var;}$output="<pre class=\"nette-dump\">".NDebugHelpers::htmlDump($var)."</pre>\n";if(!$return){$trace=PHP_VERSION_ID<50205?debug_backtrace():debug_backtrace(FALSE);$i=NDebugHelpers::findTrace($trace,'dump')?1:0;if(isset($trace[$i]['file'],$trace[$i]['line'])&&is_file($trace[$i]['file'])){$lines=file($trace[$i]['file']);preg_match('#dump\((.*)\)#',$lines[$trace[$i]['line']-1],$m);$output=substr_replace($output,' title="'.htmlspecialchars((isset($m[0])?"$m[0] \n":'')."in file {$trace[$i]['file']} on line {$trace[$i]['line']}").'"',4,0);if(self::$showLocation){$output=substr_replace($output,' <small>in '.NDebugHelpers::editorLink($trace[$i]['file'],$trace[$i]['line']).":{$trace[$i]['line']}</small>",-8,0);}}}if(self::$consoleMode){if(self::$consoleColors&&substr(getenv('TERM'),0,5)==='xterm'){$output=preg_replace_callback('#<span class="php-(\w+)">|</span>#',create_function('$m','
					return "\\033[" . (isset($m[1], NDebugger::$consoleColors[$m[1]]) ? NDebugger::$consoleColors[$m[1]] : \'0\') . "m";
				'),$output);}$output=htmlspecialchars_decode(strip_tags($output),ENT_QUOTES);}if($return){return$output;}else{echo$output;return$var;}}public
static
function
timer($name=NULL){static$time=array();$now=microtime(TRUE);$delta=isset($time[$name])?$now-$time[$name]:0;$time[$name]=$now;return$delta;}public
static
function
barDump($var,$title=NULL){if(!self::$productionMode){$dump=array();foreach((is_array($var)?$var:array(''=>$var))as$key=>$val){$dump[$key]=NDebugHelpers::clickableDump($val);}self::$dumpPanel->data[]=array('title'=>$title,'dump'=>$dump);}return$var;}public
static
function
fireLog($message){if(!self::$productionMode){return
self::$fireLogger->log($message);}}private
static
function
isHtmlMode(){return!self::$ajaxDetected&&!self::$consoleMode&&!preg_match('#^Content-Type: (?!text/html)#im',implode("\n",headers_list()));}public
static
function
addPanel(IBarPanel$panel,$id=NULL){return
self::$bar->addPanel($panel,$id);}}class
FatalErrorException
extends
Exception{private$severity;public
function
__construct($message,$code,$severity,$file,$line,$context){parent::__construct($message,$code);$this->severity=$severity;$this->file=$file;$this->line=$line;$this->context=$context;}public
function
getSeverity(){return$this->severity;}}class
NCFix{static$vars=array();static
function
uses($args){self::$vars[]=$args;return
count(self::$vars)-1;}}NDebugger::_init();