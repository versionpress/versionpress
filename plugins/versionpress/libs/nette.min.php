<?php //netteloader=Nette\Framework

/**
 * Nette Framework (version 2.0.15 released on 2014-05-24, http://nette.org)
 *
 * Copyright (c) 2004, 2014 David Grudl (http://davidgrudl.com)
 */

if(!defined('PHP_VERSION_ID')){$tmp=explode('.',PHP_VERSION);define('PHP_VERSION_ID',($tmp[0]*10000+$tmp[1]*100+$tmp[2]));}if(PHP_VERSION_ID<50200){throw
new
Exception('Nette Framework requires PHP 5.2.0 or newer.');}error_reporting(E_ALL|E_STRICT);@set_magic_quotes_runtime(FALSE);@iconv_set_encoding('internal_encoding','UTF-8');extension_loaded('mbstring')&&mb_internal_encoding('UTF-8');umask(0);@header('X-Powered-By: Nette Framework');@header('Content-Type: text/html; charset=utf-8');empty($GLOBALS[0])&&$GLOBALS[0]=array();define('NETTE',TRUE);define('NETTE_DIR',dirname(__FILE__));define('NETTE_VERSION_ID',20015);define('NETTE_PACKAGE','PHP 5.2 prefixed');interface
IBarPanel{function
getTab();function
getPanel();}interface
IPresenter{function
run(NPresenterRequest$request);}interface
IPresenterFactory{function
getPresenterClass(&$name);function
createPresenter($name);}interface
IPresenterResponse{function
send(IHttpRequest$httpRequest,IHttpResponse$httpResponse);}interface
IRouter{const
ONE_WAY=1;const
SECURED=2;function
match(IHttpRequest$httpRequest);function
constructUrl(NPresenterRequest$appRequest,NUrl$refUrl);}interface
IFreezable{function
freeze();function
isFrozen();}interface
IComponent{const
NAME_SEPARATOR='-';function
getName();function
getParent();function
setParent(IComponentContainer$parent=NULL,$name=NULL);}interface
IComponentContainer
extends
IComponent{function
addComponent(IComponent$component,$name);function
removeComponent(IComponent$component);function
getComponent($name);function
getComponents($deep=FALSE,$filterType=NULL);}interface
ISignalReceiver{function
signalReceived($signal);}interface
IStatePersistent{function
loadState(array$params);function
saveState(array&$params);}interface
IRenderable{function
invalidateControl();function
isControlInvalid();}interface
ICacheStorage{function
read($key);function
lock($key);function
write($key,$data,array$dependencies);function
remove($key);function
clean(array$conditions);}interface
ICacheJournal{function
write($key,array$dependencies);function
clean(array$conditions);}interface
IConfigAdapter{function
load($file);function
dump(array$data);}interface
ISupplementalDriver{const
SUPPORT_COLUMNS_META='meta',SUPPORT_SEQUENCE='sequence',SUPPORT_SELECT_UNGROUPED_COLUMNS='ungrouped_cols';function
delimite($name);function
formatDateTime(DateTime$value);function
formatLike($value,$pos);function
applyLimit(&$sql,$limit,$offset);function
normalizeRow($row,$statement);function
getTables();function
getColumns($table);function
getIndexes($table);function
getForeignKeys($table);function
isSupported($item);}interface
IReflection{const
FIELD_TEXT='string',FIELD_BINARY='bin',FIELD_BOOL='bool',FIELD_INTEGER='int',FIELD_FLOAT='float',FIELD_DATE='date',FIELD_TIME='time',FIELD_DATETIME='datetime';function
getPrimary($table);function
getHasManyReference($table,$key);function
getBelongsToReference($table,$key);function
setConnection(NConnection$connection);}interface
IDIContainer{function
addService($name,$service);function
getService($name);function
removeService($name);function
hasService($name);}interface
IFormControl{function
loadHttpData();function
setValue($value);function
getValue();function
getRules();function
getErrors();function
isDisabled();function
translate($s,$count=NULL);}interface
ISubmitterControl
extends
IFormControl{function
isSubmittedBy();function
getValidationScope();}interface
IFormRenderer{function
render(NForm$form);}interface
IHttpRequest{const
GET='GET',POST='POST',HEAD='HEAD',PUT='PUT',DELETE='DELETE';function
getUrl();function
getQuery($key=NULL,$default=NULL);function
getPost($key=NULL,$default=NULL);function
getFile($key);function
getFiles();function
getCookie($key,$default=NULL);function
getCookies();function
getMethod();function
isMethod($method);function
getHeader($header,$default=NULL);function
getHeaders();function
isSecured();function
isAjax();function
getRemoteAddress();function
getRemoteHost();}interface
IHttpResponse{const
PERMANENT=2116333333;const
BROWSER=0;const
S200_OK=200,S204_NO_CONTENT=204,S300_MULTIPLE_CHOICES=300,S301_MOVED_PERMANENTLY=301,S302_FOUND=302,S303_SEE_OTHER=303,S303_POST_GET=303,S304_NOT_MODIFIED=304,S307_TEMPORARY_REDIRECT=307,S400_BAD_REQUEST=400,S401_UNAUTHORIZED=401,S403_FORBIDDEN=403,S404_NOT_FOUND=404,S405_METHOD_NOT_ALLOWED=405,S410_GONE=410,S500_INTERNAL_SERVER_ERROR=500,S501_NOT_IMPLEMENTED=501,S503_SERVICE_UNAVAILABLE=503;function
setCode($code);function
getCode();function
setHeader($name,$value);function
addHeader($name,$value);function
setContentType($type,$charset=NULL);function
redirect($url,$code=self::S302_FOUND);function
setExpiration($seconds);function
isSent();function
getHeaders();function
setCookie($name,$value,$expire,$path=NULL,$domain=NULL,$secure=NULL,$httpOnly=NULL);function
deleteCookie($name,$path=NULL,$domain=NULL,$secure=NULL);}interface
ISessionStorage{function
open($savePath,$sessionName);function
close();function
read($id);function
write($id,$data);function
remove($id);function
clean($maxlifetime);}interface
IUserStorage{const
MANUAL=1,INACTIVITY=2,BROWSER_CLOSED=4;const
CLEAR_IDENTITY=8;function
setAuthenticated($state);function
isAuthenticated();function
setIdentity(IIdentity$identity=NULL);function
getIdentity();function
setExpiration($time,$flags=0);function
getLogoutReason();}interface
IMacro{function
initialize();function
finalize();function
nodeOpened(NMacroNode$node);function
nodeClosed(NMacroNode$node);}interface
ITranslator{function
translate($message,$count=NULL);}interface
IMailer{function
send(NMail$mail);}interface
IAnnotation{function
__construct(array$values);}interface
IAuthenticator{const
USERNAME=0,PASSWORD=1;const
IDENTITY_NOT_FOUND=1,INVALID_CREDENTIAL=2,FAILURE=3,NOT_APPROVED=4;function
authenticate(array$credentials);}interface
IAuthorizator{const
ALL=NULL;const
ALLOW=TRUE;const
DENY=FALSE;function
isAllowed($role=self::ALL,$resource=self::ALL,$privilege=self::ALL);}interface
IIdentity{function
getId();function
getRoles();}interface
IResource{function
getResourceId();}interface
IRole{function
getRoleId();}interface
ITemplate{function
render();}interface
IFileTemplate
extends
ITemplate{function
setFile($file);function
getFile();}class
ArgumentOutOfRangeException
extends
InvalidArgumentException{}class
InvalidStateException
extends
RuntimeException{function
__construct($message='',$code=0,Exception$previous=NULL){if(PHP_VERSION_ID<50300){$this->previous=$previous;parent::__construct($message,$code);}else{parent::__construct($message,$code,$previous);}}}class
NotImplementedException
extends
LogicException{}class
NotSupportedException
extends
LogicException{}class
DeprecatedException
extends
NotSupportedException{}class
MemberAccessException
extends
LogicException{}class
IOException
extends
RuntimeException{}class
FileNotFoundException
extends
IOException{}class
DirectoryNotFoundException
extends
IOException{}class
NStaticClassException
extends
LogicException{}class
FatalErrorException
extends
Exception{private$severity;function
__construct($message,$code,$severity,$file,$line,$context,NException$previous=NULL){if(PHP_VERSION_ID<50300){$this->previous=$previous;parent::__construct($message,$code);}else{parent::__construct($message,$code,$previous);}$this->severity=$severity;$this->file=$file;$this->line=$line;$this->context=$context;}function
getSeverity(){return$this->severity;}}abstract
class
NObject{function
getReflection(){return
new
NClassReflection($this);}function
__call($name,$args){return
NObjectMixin::call($this,$name,$args);}static
function
__callStatic($name,$args){return
NObjectMixin::callStatic(get_called_class(),$name,$args);}static
function
extensionMethod($name,$callback=NULL){if(strpos($name,'::')===FALSE){$class=get_called_class();}else{list($class,$name)=explode('::',$name);}$class=new
NClassReflection($class);if($callback===NULL){return$class->getExtensionMethod($name);}else{$class->setExtensionMethod($name,$callback);}}function&__get($name){return
NObjectMixin::get($this,$name);}function
__set($name,$value){NObjectMixin::set($this,$name,$value);}function
__isset($name){return
NObjectMixin::has($this,$name);}function
__unset($name){NObjectMixin::remove($this,$name);}}class
NLimitedScope{final
function
__construct(){throw
new
NStaticClassException;}static
function
evaluate(){if(func_num_args()>1){foreach(func_get_arg(1)as$__k=>$__v)$$__k=$__v;unset($__k,$__v);}$res=eval('?>'.func_get_arg(0));if($res===FALSE&&($error=error_get_last())&&$error['type']===E_PARSE){throw
new
FatalErrorException($error['message'],0,$error['type'],$error['file'],$error['line'],NULL);}return$res;}static
function
load(){if(func_num_args()>1){if(func_get_arg(1)===TRUE){return require func_get_arg(0);}foreach(func_get_arg(1)as$__k=>$__v)$$__k=$__v;unset($__k,$__v);}return require func_get_arg(0);}}abstract
class
NAutoLoader
extends
NObject{static
private$loaders=array();public
static$count=0;static
function
load($type){foreach(func_get_args()as$type){if(!class_exists($type)){throw
new
InvalidStateException("Unable to load class or interface '$type'.");}}}static
function
getLoaders(){return
array_values(self::$loaders);}function
register(){if(!function_exists('spl_autoload_register')){throw
new
NotSupportedException('spl_autoload does not exist in this PHP installation.');}spl_autoload_register(array($this,'tryLoad'));self::$loaders[spl_object_hash($this)]=$this;}function
unregister(){unset(self::$loaders[spl_object_hash($this)]);return
spl_autoload_unregister(array($this,'tryLoad'));}abstract
function
tryLoad($type);}class
NDebugHelpers{static
function
editorLink($file,$line){if(NDebugger::$editor&&is_file($file)){$dir=dirname(strtr($file,'/',DIRECTORY_SEPARATOR));$base=isset($_SERVER['SCRIPT_FILENAME'])?dirname(dirname(strtr($_SERVER['SCRIPT_FILENAME'],'/',DIRECTORY_SEPARATOR))):dirname($dir);if(substr($dir,0,strlen($base))===$base){$dir='...'.substr($dir,strlen($base));}return
NHtml::el('a')->href(strtr(NDebugger::$editor,array('%file'=>rawurlencode($file),'%line'=>$line)))->title("$file:$line")->setHtml(htmlSpecialChars(rtrim($dir,DIRECTORY_SEPARATOR)).DIRECTORY_SEPARATOR.'<b>'.htmlSpecialChars(basename($file)).'</b>');}else{return
NHtml::el('span')->setText($file);}}static
function
htmlDump(&$var,$level=0){static$tableUtf,$tableBin,$reBinary='#[^\x09\x0A\x0D\x20-\x7E\xA0-\x{10FFFF}]#u';if($tableUtf===NULL){foreach(range("\x00","\xFF")as$ch){if(ord($ch)<32&&strpos("\r\n\t",$ch)===FALSE){$tableUtf[$ch]=$tableBin[$ch]='\\x'.str_pad(dechex(ord($ch)),2,'0',STR_PAD_LEFT);}elseif(ord($ch)<127){$tableUtf[$ch]=$tableBin[$ch]=$ch;}else{$tableUtf[$ch]=$ch;$tableBin[$ch]='\\x'.dechex(ord($ch));}}$tableBin["\\"]='\\\\';$tableBin["\r"]='\\r';$tableBin["\n"]='\\n';$tableBin["\t"]='\\t';$tableUtf['\\x']=$tableBin['\\x']='\\\\x';}if(is_bool($var)){return'<span class="php-bool">'.($var?'TRUE':'FALSE')."</span>\n";}elseif($var===NULL){return"<span class=\"php-null\">NULL</span>\n";}elseif(is_int($var)){return"<span class=\"php-int\">$var</span>\n";}elseif(is_float($var)){$var=json_encode($var);if(strpos($var,'.')===FALSE){$var.='.0';}return"<span class=\"php-float\">$var</span>\n";}elseif(is_string($var)){if(NDebugger::$maxLen&&strlen($var)>NDebugger::$maxLen){$s=htmlSpecialChars(substr($var,0,NDebugger::$maxLen),ENT_NOQUOTES,'ISO-8859-1').' ... ';}else{$s=htmlSpecialChars($var,ENT_NOQUOTES,'ISO-8859-1');}$s=strtr($s,preg_match($reBinary,$s)||preg_last_error()?$tableBin:$tableUtf);$len=strlen($var);return"<span class=\"php-string\">\"$s\"</span>".($len>1?" ($len)":"")."\n";}elseif(is_array($var)){$s='<span class="php-array">array</span>('.count($var).") ";$space=str_repeat($space1='   ',$level);$brackets=range(0,count($var)-1)===array_keys($var)?"[]":"{}";static$marker;if($marker===NULL){$marker=uniqid("\x00",TRUE);}if(empty($var)){}elseif(isset($var[$marker])){$brackets=$var[$marker];$s.="$brackets[0] *RECURSION* $brackets[1]";}elseif($level<NDebugger::$maxDepth||!NDebugger::$maxDepth){$s.="<code>$brackets[0]\n";$var[$marker]=$brackets;foreach($var
as$k=>&$v){if($k===$marker){continue;}$k=strtr($k,preg_match($reBinary,$k)||preg_last_error()?$tableBin:$tableUtf);$k=htmlSpecialChars(preg_match('#^\w+$#',$k)?$k:"\"$k\"");$s.="$space$space1<span class=\"php-key\">$k</span> => ".self::htmlDump($v,$level+1);}unset($var[$marker]);$s.="$space$brackets[1]</code>";}else{$s.="$brackets[0] ... $brackets[1]";}return$s."\n";}elseif(is_object($var)){if($var
instanceof
Closure){$rc=new
ReflectionFunction($var);$arr=array();foreach($rc->getParameters()as$param){$arr[]='$'.$param->getName();}$arr=array('file'=>$rc->getFileName(),'line'=>$rc->getStartLine(),'parameters'=>implode(', ',$arr));}else{$arr=(array)$var;}$s='<span class="php-object">'.get_class($var)."</span>(".count($arr).") ";$space=str_repeat($space1='   ',$level);static$list=array();if(empty($arr)){}elseif(in_array($var,$list,TRUE)){$s.="{ *RECURSION* }";}elseif($level<NDebugger::$maxDepth||!NDebugger::$maxDepth||$var
instanceof
Closure){$s.="<code>{\n";$list[]=$var;foreach($arr
as$k=>&$v){$m='';if($k[0]==="\x00"){$m=' <span class="php-visibility">'.($k[1]==='*'?'protected':'private').'</span>';$k=substr($k,strrpos($k,"\x00")+1);}$k=strtr($k,preg_match($reBinary,$k)||preg_last_error()?$tableBin:$tableUtf);$k=htmlSpecialChars(preg_match('#^\w+$#',$k)?$k:"\"$k\"");$s.="$space$space1<span class=\"php-key\">$k</span>$m => ".self::htmlDump($v,$level+1);}array_pop($list);$s.="$space}</code>";}else{$s.="{ ... }";}return$s."\n";}elseif(is_resource($var)){$type=get_resource_type($var);$s='<span class="php-resource">'.htmlSpecialChars($type)." resource</span> ";static$info=array('stream'=>'stream_get_meta_data','curl'=>'curl_getinfo');if(isset($info[$type])){$space=str_repeat($space1='   ',$level);$s.="<code>{\n";foreach(call_user_func($info[$type],$var)as$k=>$v){$s.=$space.$space1.'<span class="php-key">'.htmlSpecialChars($k)."</span> => ".self::htmlDump($v,$level+1);}$s.="$space}</code>";}return$s."\n";}else{return"<span>unknown type</span>\n";}}static
function
clickableDump($dump,$collapsed=FALSE){return'<pre class="nette-dump">'.preg_replace_callback('#^( *)((?>[^(\r\n]{1,200}))\((\d+)\) <code>#m',create_function('$m','extract($GLOBALS[0]['.array_push($GLOBALS[0],array('collapsed'=>$collapsed)).'-1], EXTR_REFS);
				return "$m[1]<a href=\'#\' rel=\'next\'>$m[2]($m[3]) "
					. (($m[1] || !$collapsed) && ($m[3] < 7)
					? \'<abbr>&#x25bc;</abbr> </a><code>\'
					: \'<abbr>&#x25ba;</abbr> </a><code class="nette-collapsed">\');
			'),self::htmlDump($dump)).'</pre>';}static
function
findTrace(array$trace,$method,&$index=NULL){$m=explode('::',$method);foreach($trace
as$i=>$item){if(isset($item['function'])&&$item['function']===end($m)&&isset($item['class'])===isset($m[1])&&(!isset($item['class'])||$item['class']===$m[0]||$m[0]==='*'||is_subclass_of($item['class'],$m[0]))){$index=$i;return$item;}}}}class
NHtml
extends
NObject
implements
ArrayAccess,Countable,IteratorAggregate{private$name;private$isEmpty;public$attrs=array();protected$children=array();public
static$xhtml=TRUE;public
static$emptyElements=array('img'=>1,'hr'=>1,'br'=>1,'input'=>1,'meta'=>1,'area'=>1,'embed'=>1,'keygen'=>1,'source'=>1,'base'=>1,'col'=>1,'link'=>1,'param'=>1,'basefont'=>1,'frame'=>1,'isindex'=>1,'wbr'=>1,'command'=>1,'track'=>1);static
function
el($name=NULL,$attrs=NULL){$el=new
self;$parts=explode(' ',$name,2);$el->setName($parts[0]);if(is_array($attrs)){$el->attrs=$attrs;}elseif($attrs!==NULL){$el->setText($attrs);}if(isset($parts[1])){foreach(NStrings::matchAll($parts[1].' ','#([a-z0-9:-]+)(?:=(["\'])?(.*?)(?(2)\\2|\s))?#i')as$m){$el->attrs[$m[1]]=isset($m[3])?$m[3]:TRUE;}}return$el;}function
setName($name,$isEmpty=NULL){if($name!==NULL&&!is_string($name)){throw
new
InvalidArgumentException(sprintf('Name must be string or NULL, %s given.',gettype($name)));}$this->name=$name;$this->isEmpty=$isEmpty===NULL?isset(self::$emptyElements[$name]):(bool)$isEmpty;return$this;}function
getName(){return$this->name;}function
isEmpty(){return$this->isEmpty;}function
addAttributes(array$attrs){$this->attrs=$attrs+$this->attrs;return$this;}function
__set($name,$value){$this->attrs[$name]=$value;}function&__get($name){return$this->attrs[$name];}function
__isset($name){return
isset($this->attrs[$name]);}function
__unset($name){unset($this->attrs[$name]);}function
__call($m,$args){$p=substr($m,0,3);if($p==='get'||$p==='set'||$p==='add'){$m=substr($m,3);$m[0]=$m[0]|"\x20";if($p==='get'){return
isset($this->attrs[$m])?$this->attrs[$m]:NULL;}elseif($p==='add'){$args[]=TRUE;}}if(count($args)===0){}elseif(count($args)===1){$this->attrs[$m]=$args[0];}elseif((string)$args[0]===''){$tmp=&$this->attrs[$m];}elseif(!isset($this->attrs[$m])||is_array($this->attrs[$m])){$this->attrs[$m][$args[0]]=$args[1];}else{$this->attrs[$m]=array($this->attrs[$m],$args[0]=>$args[1]);}return$this;}function
href($path,$query=NULL){if($query){$query=http_build_query($query,NULL,'&');if($query!==''){$path.='?'.$query;}}$this->attrs['href']=$path;return$this;}function
setHtml($html){if($html===NULL){$html='';}elseif(is_array($html)){throw
new
InvalidArgumentException(sprintf('Textual content must be a scalar, %s given.',gettype($html)));}else{$html=(string)$html;}$this->removeChildren();$this->children[]=$html;return$this;}function
getHtml(){$s='';foreach($this->children
as$child){if(is_object($child)){$s.=$child->render();}else{$s.=$child;}}return$s;}function
setText($text){if(!is_array($text)){$text=htmlspecialchars((string)$text,ENT_NOQUOTES);}return$this->setHtml($text);}function
getText(){return
html_entity_decode(strip_tags($this->getHtml()),ENT_QUOTES,'UTF-8');}function
add($child){return$this->insert(NULL,$child);}function
create($name,$attrs=NULL){$this->insert(NULL,$child=self::el($name,$attrs));return$child;}function
insert($index,$child,$replace=FALSE){if($child
instanceof
NHtml||is_scalar($child)){if($index===NULL){$this->children[]=$child;}else{array_splice($this->children,(int)$index,$replace?1:0,array($child));}}else{throw
new
InvalidArgumentException(sprintf('Child node must be scalar or Html object, %s given.',is_object($child)?get_class($child):gettype($child)));}return$this;}function
offsetSet($index,$child){$this->insert($index,$child,TRUE);}function
offsetGet($index){return$this->children[$index];}function
offsetExists($index){return
isset($this->children[$index]);}function
offsetUnset($index){if(isset($this->children[$index])){array_splice($this->children,(int)$index,1);}}function
count(){return
count($this->children);}function
removeChildren(){$this->children=array();}function
getIterator($deep=FALSE){if($deep){$deep=$deep>0?RecursiveIteratorIterator::SELF_FIRST:RecursiveIteratorIterator::CHILD_FIRST;return
new
RecursiveIteratorIterator(new
NGenericRecursiveIterator(new
ArrayIterator($this->children)),$deep);}else{return
new
NGenericRecursiveIterator(new
ArrayIterator($this->children));}}function
getChildren(){return$this->children;}function
render($indent=NULL){$s=$this->startTag();if(!$this->isEmpty){if($indent!==NULL){$indent++;}foreach($this->children
as$child){if(is_object($child)){$s.=$child->render($indent);}else{$s.=$child;}}$s.=$this->endTag();}if($indent!==NULL){return"\n".str_repeat("\t",$indent-1).$s."\n".str_repeat("\t",max(0,$indent-2));}return$s;}function
__toString(){return$this->render();}function
startTag(){if($this->name){return'<'.$this->name.$this->attributes().(self::$xhtml&&$this->isEmpty?' />':'>');}else{return'';}}function
endTag(){return$this->name&&!$this->isEmpty?'</'.$this->name.'>':'';}function
attributes(){if(!is_array($this->attrs)){return'';}$s='';foreach($this->attrs
as$key=>$value){if($value===NULL||$value===FALSE){continue;}elseif($value===TRUE){if(self::$xhtml){$s.=' '.$key.'="'.$key.'"';}else{$s.=' '.$key;}continue;}elseif(is_array($value)){if($key==='data'){foreach($value
as$k=>$v){if($v!==NULL&&$v!==FALSE){$q=strpos($v,'"')===FALSE?'"':"'";$s.=' data-'.$k.'='.$q.str_replace(array('&',$q),array('&amp;',$q==='"'?'&quot;':'&#39;'),$v).(strpos($v,'`')!==FALSE&&strpbrk($v,' <>"\'')===FALSE?' ':'').$q;}}continue;}$tmp=NULL;foreach($value
as$k=>$v){if($v!=NULL){$tmp[]=$v===TRUE?$k:(is_string($k)?$k.':'.$v:$v);}}if($tmp===NULL){continue;}$value=implode($key==='style'||!strncmp($key,'on',2)?';':' ',$tmp);}else{$value=(string)$value;}$q=strpos($value,'"')===FALSE?'"':"'";$s.=' '.$key.'='.$q.str_replace(array('&',$q),array('&amp;',$q==='"'?'&quot;':'&#39;'),$value).(strpos($value,'`')!==FALSE&&strpbrk($value,' <>"\'')===FALSE?' ':'').$q;}$s=str_replace('@','&#64;',$s);return$s;}function
__clone(){foreach($this->children
as$key=>$value){if(is_object($value)){$this->children[$key]=clone$value;}}}}class
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
function
__construct(){throw
new
NStaticClassException;}static
function
_init(){self::$time=isset($_SERVER['REQUEST_TIME_FLOAT'])?$_SERVER['REQUEST_TIME_FLOAT']:microtime(TRUE);self::$consoleMode=PHP_SAPI==='cli';self::$productionMode=self::DETECT;if(self::$consoleMode){self::$source=empty($_SERVER['argv'])?'cli':'cli: '.implode(' ',$_SERVER['argv']);}else{self::$ajaxDetected=isset($_SERVER['HTTP_X_REQUESTED_WITH'])&&$_SERVER['HTTP_X_REQUESTED_WITH']==='XMLHttpRequest';if(isset($_SERVER['REQUEST_URI'])){self::$source=(!empty($_SERVER['HTTPS'])&&strcasecmp($_SERVER['HTTPS'],'off')?'https://':'http://').(isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:(isset($_SERVER['SERVER_NAME'])?$_SERVER['SERVER_NAME']:'')).$_SERVER['REQUEST_URI'];}}self::$logger=new
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
NDefaultBarPanel('dumps'));}static
function
enable($mode=NULL,$logDirectory=NULL,$email=NULL){error_reporting(E_ALL|E_STRICT);if(is_bool($mode)){self::$productionMode=$mode;}elseif($mode!==self::DETECT||self::$productionMode===NULL){$list=is_string($mode)?preg_split('#[,\s]+#',$mode):(array)$mode;if(!isset($_SERVER['HTTP_X_FORWARDED_FOR'])){$list[]='127.0.0.1';$list[]='::1';}self::$productionMode=!in_array(isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:php_uname('n'),$list,TRUE);}if(is_string($logDirectory)){self::$logDirectory=realpath($logDirectory);if(self::$logDirectory===FALSE){echo
__METHOD__."() error: Log directory is not found or is not directory.\n";exit(254);}}elseif($logDirectory===FALSE){self::$logDirectory=FALSE;}elseif(self::$logDirectory===NULL){self::$logDirectory=defined('APP_DIR')?APP_DIR.'/../log':getcwd().'/log';}if(self::$logDirectory){ini_set('error_log',self::$logDirectory.'/php_error.log');}if(function_exists('ini_set')){ini_set('display_errors',!self::$productionMode);ini_set('html_errors',FALSE);ini_set('log_errors',FALSE);}elseif(ini_get('display_errors')!=!self::$productionMode&&ini_get('display_errors')!==(self::$productionMode?'stderr':'stdout')){echo
__METHOD__."() error: Unable to set 'display_errors' because function ini_set() is disabled.\n";exit(254);}if($email){if(!is_string($email)){echo
__METHOD__."() error: Email address must be a string.\n";exit(254);}self::$email=$email;}if(!defined('E_DEPRECATED')){define('E_DEPRECATED',8192);}if(!defined('E_USER_WARNING')){define('E_USER_WARNING',16384);}if(!self::$enabled){register_shutdown_function(array(__CLASS__,'_shutdownHandler'));set_exception_handler(array(__CLASS__,'_exceptionHandler'));set_error_handler(array(__CLASS__,'_errorHandler'));self::$enabled=TRUE;}}static
function
isEnabled(){return
self::$enabled;}static
function
log($message,$priority=self::INFO){if(self::$logDirectory===FALSE){return;}elseif(!self::$logDirectory){throw
new
InvalidStateException('Logging directory is not specified in NDebugger::$logDirectory.');}if($message
instanceof
Exception){$exception=$message;$message=($message
instanceof
FatalErrorException?'Fatal error: '.$exception->getMessage():get_class($exception).": ".$exception->getMessage())." in ".$exception->getFile().":".$exception->getLine();$hash=md5(preg_replace('~(Resource id #)\d+~','$1',$exception.(method_exists($exception,'getPrevious')?$exception->getPrevious():(isset($exception->previous)?$exception->previous:''))));$exceptionFilename="exception-".@date('Y-m-d-H-i-s')."-$hash.html";foreach(new
DirectoryIterator(self::$logDirectory)as$entry){if(strpos($entry,$hash)){$exceptionFilename=$entry;$saved=TRUE;break;}}}self::$logger->log(array(@date('[Y-m-d H-i-s]'),trim($message),self::$source?' @  '.self::$source:NULL,!empty($exceptionFilename)?' @@  '.$exceptionFilename:NULL),$priority);if(!empty($exceptionFilename)){$exceptionFilename=self::$logDirectory.'/'.$exceptionFilename;if(empty($saved)&&$logHandle=@fopen($exceptionFilename,'w')){ob_start();ob_start(create_function('$buffer','extract($GLOBALS[0]['.array_push($GLOBALS[0],array('logHandle'=>$logHandle)).'-1], EXTR_REFS); fwrite($logHandle, $buffer); '),4096);self::$blueScreen->render($exception);ob_end_flush();ob_end_clean();fclose($logHandle);}return
strtr($exceptionFilename,'\\/',DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR);}}static
function
_shutdownHandler(){if(!self::$enabled){return;}static$types=array(E_ERROR=>1,E_CORE_ERROR=>1,E_COMPILE_ERROR=>1,E_PARSE=>1);$error=error_get_last();if(isset($types[$error['type']])){self::_exceptionHandler(new
FatalErrorException($error['message'],0,$error['type'],$error['file'],$error['line'],NULL));}if(self::$bar&&!self::$productionMode&&self::isHtmlMode()){self::$bar->render();}}static
function
_exceptionHandler(Exception$exception){if(!headers_sent()){$protocol=isset($_SERVER['SERVER_PROTOCOL'])?$_SERVER['SERVER_PROTOCOL']:'HTTP/1.1';$code=isset($_SERVER['HTTP_USER_AGENT'])&&strpos($_SERVER['HTTP_USER_AGENT'],'MSIE ')!==FALSE?503:500;header("$protocol $code",TRUE,$code);}try{if(self::$productionMode){try{self::log($exception,self::ERROR);}catch(Exception$e){echo'FATAL ERROR: unable to log error';}if(self::$consoleMode){echo"ERROR: the server encountered an internal error and was unable to complete your request.\n";}elseif(self::isHtmlMode()){?>
<!DOCTYPE html>
<meta charset="utf-8">
<meta name=robots content=noindex>
<meta name=generator content="Nette Framework">
<script>document.body.innerHTML="";</script>
<style>body{color:#333;background:white;width:500px;margin:100px auto}h1{font:bold 47px/1.5 sans-serif;margin:.6em 0}p{font:21px/1.5 Georgia,serif;margin:1.5em 0}small{font-size:70%;color:gray}</style>

<title>Server Error</title>

<h1>Server Error</h1>

<p>We're sorry! The server encountered an internal error and
was unable to complete your request. Please try again later.</p>

<p><small>error 500</small></p>
<?php }}else{if(self::$consoleMode){echo"$exception\n";if($file=self::log($exception)){echo"(stored in $file)\n";if(self::$browser){exec(self::$browser.' '.escapeshellarg($file));}}}elseif(self::isHtmlMode()){self::$blueScreen->render($exception);if(self::$bar){self::$bar->render();}}elseif(!self::fireLog($exception)){$file=self::log($exception,self::ERROR);if(!headers_sent()){header("X-Nette-Error-Log: $file");}}}foreach(self::$onFatalError
as$handler){call_user_func($handler,$exception);}}catch(Exception$e){if(self::$productionMode){echo
self::isHtmlMode()?'<meta name=robots content=noindex>FATAL ERROR':'FATAL ERROR';}else{echo"FATAL ERROR: thrown ",get_class($e),': ',$e->getMessage(),"\nwhile processing ",get_class($exception),': ',$exception->getMessage(),"\n";}}self::$enabled=FALSE;exit(254);}static
function
_errorHandler($severity,$message,$file,$line,$context){if(self::$scream){error_reporting(E_ALL|E_STRICT);}if(self::$lastError!==FALSE&&($severity&error_reporting())===$severity){self::$lastError=new
ErrorException($message,0,$severity,$file,$line);return
NULL;}if($severity===E_RECOVERABLE_ERROR||$severity===E_USER_ERROR){if(NDebugHelpers::findTrace(PHP_VERSION_ID<50205?debug_backtrace():debug_backtrace(FALSE),'*::__toString')){$previous=isset($context['e'])&&$context['e']instanceof
Exception?$context['e']:NULL;self::_exceptionHandler(new
FatalErrorException($message,0,$severity,$file,$line,$context,$previous));}throw
new
FatalErrorException($message,0,$severity,$file,$line,$context);}elseif(($severity&error_reporting())!==$severity){return
FALSE;}elseif(!self::$productionMode&&(is_bool(self::$strictMode)?self::$strictMode:((self::$strictMode&$severity)===$severity))){self::_exceptionHandler(new
FatalErrorException($message,0,$severity,$file,$line,$context));}static$types=array(E_WARNING=>'Warning',E_COMPILE_WARNING=>'Warning',E_USER_WARNING=>'Warning',E_NOTICE=>'Notice',E_USER_NOTICE=>'Notice',E_STRICT=>'Strict standards',E_DEPRECATED=>'Deprecated',E_USER_WARNING=>'Deprecated');$message='PHP '.(isset($types[$severity])?$types[$severity]:'Unknown error').": $message";$count=&self::$errorPanel->data["$file|$line|$message"];if($count++){return
NULL;}elseif(self::$productionMode){self::log("$message in $file:$line",self::ERROR);return
NULL;}else{$ok=self::fireLog(new
ErrorException($message,0,$severity,$file,$line));return!self::isHtmlMode()||(!self::$bar&&!$ok)?FALSE:NULL;}}static
function
toStringException(Exception$exception){if(self::$enabled){self::_exceptionHandler($exception);}else{trigger_error($exception->getMessage(),E_USER_ERROR);}}static
function
tryError(){if(!self::$enabled&&self::$lastError===FALSE){set_error_handler(array(__CLASS__,'_errorHandler'));}self::$lastError=NULL;}static
function
catchError(&$error){if(!self::$enabled&&self::$lastError!==FALSE){restore_error_handler();}$error=self::$lastError;self::$lastError=FALSE;return(bool)$error;}static
function
dump($var,$return=FALSE){if(!$return&&self::$productionMode){return$var;}$output="<pre class=\"nette-dump\">".NDebugHelpers::htmlDump($var)."</pre>\n";if(!$return){$trace=PHP_VERSION_ID<50205?debug_backtrace():debug_backtrace(FALSE);$item=($tmp=NDebugHelpers::findTrace($trace,'dump'))?$tmp:NDebugHelpers::findTrace($trace,__CLASS__.'::dump');if(isset($item['file'],$item['line'])&&is_file($item['file'])){$lines=file($item['file']);preg_match('#dump\((.*)\)#',$lines[$item['line']-1],$m);$output=substr_replace($output,' title="'.htmlspecialchars((isset($m[0])?"$m[0] \n":'')."in file {$item['file']} on line {$item['line']}").'"',4,0);if(self::$showLocation){$output=substr_replace($output,' <small>in '.NDebugHelpers::editorLink($item['file'],$item['line']).":{$item['line']}</small>",-8,0);}}}if(self::$consoleMode){if(self::$consoleColors&&substr(getenv('TERM'),0,5)==='xterm'){$output=preg_replace_callback('#<span class="php-(\w+)">|</span>#',create_function('$m','
					return "\\033[" . (isset($m[1], NDebugger::$consoleColors[$m[1]]) ? NDebugger::$consoleColors[$m[1]] : \'0\') . "m";
				'),$output);}$output=htmlspecialchars_decode(strip_tags($output),ENT_QUOTES);}if($return){return$output;}else{echo$output;return$var;}}static
function
timer($name=NULL){static$time=array();$now=microtime(TRUE);$delta=isset($time[$name])?$now-$time[$name]:0;$time[$name]=$now;return$delta;}static
function
barDump($var,$title=NULL){if(!self::$productionMode){$dump=array();foreach((is_array($var)?$var:array(''=>$var))as$key=>$val){$dump[$key]=NDebugHelpers::clickableDump($val);}self::$dumpPanel->data[]=array('title'=>$title,'dump'=>$dump);}return$var;}static
function
fireLog($message){if(!self::$productionMode){return
self::$fireLogger->log($message);}}private
static
function
isHtmlMode(){return!self::$ajaxDetected&&!self::$consoleMode&&!preg_match('#^Content-Type: (?!text/html)#im',implode("\n",headers_list()));}static
function
addPanel(IBarPanel$panel,$id=NULL){return
self::$bar->addPanel($panel,$id);}}class
NLogger
extends
NObject{const
DEBUG='debug',INFO='info',WARNING='warning',ERROR='error',CRITICAL='critical';public
static$emailSnooze=172800;public$mailer=array(__CLASS__,'defaultMailer');public$directory;public$email;function
log($message,$priority=self::INFO){if(!is_dir($this->directory)){throw
new
DirectoryNotFoundException("Directory '$this->directory' is not found or is not directory.");}if(is_array($message)){$message=implode(' ',$message);}$res=error_log(trim($message).PHP_EOL,3,$this->directory.'/'.strtolower($priority).'.log');if(($priority===self::ERROR||$priority===self::CRITICAL)&&$this->email&&$this->mailer&&@filemtime($this->directory.'/email-sent')+self::$emailSnooze<time()&&@file_put_contents($this->directory.'/email-sent','sent')){NCallback::create($this->mailer)->invoke($message,$this->email);}return$res;}static
function
defaultMailer($message,$email){$host=php_uname('n');foreach(array('HTTP_HOST','SERVER_NAME','HOSTNAME')as$item){if(isset($_SERVER[$item])){$host=preg_replace('#[^\w.-]+#','',$_SERVER[$item]);break;}}$parts=str_replace(array("\r\n","\n"),array("\n",PHP_EOL),array('headers'=>implode("\n",array("From: noreply@$host",'X-Mailer: Nette Framework','Content-Type: text/plain; charset=UTF-8','Content-Transfer-Encoding: 8bit'))."\n",'subject'=>"PHP: An error occurred on the server $host",'body'=>"[".@date('Y-m-d H:i:s')."] $message"));mail($email,$parts['subject'],$parts['body'],$parts['headers']);}}class
NFireLogger
extends
NObject{const
DEBUG='debug',INFO='info',WARNING='warning',ERROR='error',CRITICAL='critical';private
static$payload=array('logs'=>array());static
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
jsonDump(&$var,$level=0){if(is_bool($var)||is_null($var)||is_int($var)||is_float($var)){return$var;}elseif(is_string($var)){if(NDebugger::$maxLen&&strlen($var)>NDebugger::$maxLen){$var=substr($var,0,NDebugger::$maxLen)." \xE2\x80\xA6 ";}return
NStrings::fixEncoding($var);}elseif(is_array($var)){static$marker;if($marker===NULL){$marker=uniqid("\x00",TRUE);}if(isset($var[$marker])){return"\xE2\x80\xA6RECURSION\xE2\x80\xA6";}elseif($level<NDebugger::$maxDepth||!NDebugger::$maxDepth){$var[$marker]=TRUE;$res=array();foreach($var
as$k=>&$v){if($k!==$marker){$res[self::jsonDump($k)]=self::jsonDump($v,$level+1);}}unset($var[$marker]);return$res;}else{return" \xE2\x80\xA6 ";}}elseif(is_object($var)){$arr=(array)$var;static$list=array();if(in_array($var,$list,TRUE)){return"\xE2\x80\xA6RECURSION\xE2\x80\xA6";}elseif($level<NDebugger::$maxDepth||!NDebugger::$maxDepth){$list[]=$var;$res=array("\x00"=>'(object) '.get_class($var));foreach($arr
as$k=>&$v){if($k[0]==="\x00"){$k=substr($k,strrpos($k,"\x00")+1);}$res[self::jsonDump($k)]=self::jsonDump($v,$level+1);}array_pop($list);return$res;}else{return" \xE2\x80\xA6 ";}}elseif(is_resource($var)){return"resource ".get_resource_type($var);}else{return"unknown type";}}}class
NDebugBlueScreen
extends
NObject{private$panels=array();function
addPanel($panel){if(!in_array($panel,$this->panels,TRUE)){$this->panels[]=$panel;}return$this;}function
render(Exception$exception){$panels=$this->panels;static$errorTypes=array(E_ERROR=>'Fatal Error',E_USER_ERROR=>'User Error',E_RECOVERABLE_ERROR=>'Recoverable Error',E_CORE_ERROR=>'Core Error',E_COMPILE_ERROR=>'Compile Error',E_PARSE=>'Parse Error',E_WARNING=>'Warning',E_CORE_WARNING=>'Core Warning',E_COMPILE_WARNING=>'Compile Warning',E_USER_WARNING=>'User Warning',E_NOTICE=>'Notice',E_USER_NOTICE=>'User Notice',E_STRICT=>'Strict',E_DEPRECATED=>'Deprecated',E_USER_WARNING=>'User Deprecated');$title=($exception
instanceof
FatalErrorException&&isset($errorTypes[$exception->getSeverity()]))?$errorTypes[$exception->getSeverity()]:get_class($exception);$expandPath=NETTE_DIR.DIRECTORY_SEPARATOR;$counter=0;?><!DOCTYPE html><!-- "' --></script></style></pre></xmp></table>
<html>
<head>
	<meta charset="utf-8">
	<meta name="robots" content="noindex">
	<meta name="generator" content="Nette Framework">

	<title><?php echo
htmlspecialchars($title)?></title><!-- <?php
$ex=$exception;echo
htmlspecialchars($ex->getMessage().($ex->getCode()?' #'.$ex->getCode():''));while((method_exists($ex,'getPrevious')&&$ex=$ex->getPrevious())||(isset($ex->previous)&&$ex=$ex->previous))echo
htmlspecialchars('; caused by '.get_class($ex).' '.$ex->getMessage().($ex->getCode()?' #'.$ex->getCode():''));?> -->

	<style type="text/css" class="nette-debug">html{overflow-y:scroll}#netteBluescreen{font:9pt/1.5 Verdana,sans-serif;background:white;color:#333;position:absolute;left:0;top:0;width:100%;text-align:left}#netteBluescreen *{font:inherit;color:inherit;background:transparent;border:none;margin:0;padding:0;text-align:inherit;text-indent:0}#netteBluescreen b{font-weight:bold}#netteBluescreen i{font-style:italic}#netteBluescreen a{text-decoration:none;color:#328ADC;padding:2px 4px;margin:-2px -4px}#netteBluescreen a:hover,#netteBluescreen a:active,#netteBluescreen a:focus{color:#085AA3}#netteBluescreen a abbr{font-family:sans-serif;color:#BBB}#netteBluescreenIcon{position:absolute;right:.5em;top:.5em;z-index:20000;text-decoration:none;background:#CD1818;padding:3px}#netteBluescreenError{background:#CD1818;color:white;font:13pt/1.5 Verdana,sans-serif!important;display:block}#netteBluescreenError #netteBsSearch{color:#CD1818;font-size:.7em}#netteBluescreenError:hover #netteBsSearch{color:#ED8383}#netteBluescreen h1{font-size:18pt;font-weight:normal;text-shadow:1px 1px 0 rgba(0,0,0,.4);margin:.7em 0}#netteBluescreen h2{font:14pt/1.5 sans-serif!important;color:#888;margin:.6em 0}#netteBluescreen h3{font:bold 10pt/1.5 Verdana,sans-serif!important;margin:1em 0;padding:0}#netteBluescreen p,#netteBluescreen pre{margin:.8em 0}#netteBluescreen pre,#netteBluescreen code,#netteBluescreen table{font:9pt/1.5 Consolas,monospace!important}#netteBluescreen pre,#netteBluescreen table{background:#FDF5CE;padding:.4em .7em;border:1px dotted silver;overflow:auto}#netteBluescreen pre div{min-width:100%;float:left;_float:none;white-space:pre}#netteBluescreen table pre{padding:0;margin:0;border:none}#netteBluescreen pre .php-array,#netteBluescreen pre .php-object{color:#C22}#netteBluescreen pre .php-string{color:#080}#netteBluescreen pre .php-int,#netteBluescreen pre .php-float,#netteBluescreen pre .php-null,#netteBluescreen pre .php-bool{color:#328ADC}#netteBluescreen pre .php-visibility{font-size:85%;color:#998}#netteBluescreen pre.nette-dump a{color:#333}#netteBluescreen div.panel{padding:1px 25px}#netteBluescreen div.inner{background:#F4F3F1;padding:.1em 1em 1em;border-radius:8px}#netteBluescreen table{border-collapse:collapse;width:100%}#netteBluescreen .outer{overflow:auto}#netteBluescreen td,#netteBluescreen th{vertical-align:top;text-align:left;padding:2px 6px;border:1px solid #e6dfbf}#netteBluescreen th{font-weight:bold}#netteBluescreen tr>:first-child{width:20%}#netteBluescreen tr:nth-child(2n),#netteBluescreen tr:nth-child(2n) pre{background-color:#F7F0CB}#netteBluescreen ol{margin:1em 0;padding-left:2.5em}#netteBluescreen ul{font:7pt/1.5 Verdana,sans-serif!important;padding:2em 4em;margin:1em 0 0;color:#777;background:#F6F5F3 url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAFEAAAAjCAMAAADbuxbOAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAADBQTFRF/fz24d7Y7Onj5uLd9vPu3drUzMvG09LN39zW8e7o2NbQ3NnT29jS0M7J1tXQAAAApvmsFgAAABB0Uk5T////////////////////AOAjXRkAAAKlSURBVHja7FbbsqQgDAwENEgc//9vN+SCWDtbtXPmZR/Wc6o02mlC58LA9ckFAOszvMV8xNgyUjyXhojfMVKvRL0ZHavxXYy5JrmchMdzou8YlTClxajtK8ZGGpWRoBr1+gFjKfHkJPbizabLgzE3pH7Iu4K980xgFvlrVzMZoVBWhtvouCDdcTDmTgMCJdVxJ9MKO6XxnliM7hxi5lbj2ZVM4l8DqYyKoNLYcfqBB1/LpHYxEcfVG6ZpMDgyFUVWY/Q1sSYPpIdSAKWqLWL0XqWiMWc4hpH0OQOMOAgdycY4N9Sb7wWANQs3rsDSdLAYiuxi5siVfOhBWIrtH0G3kNaF/8Q4kCPE1kMucG/ZMUBUCOgiKJkPuWWTLGVgLGpwns1DraUayCtoBqERyaYtVsm85NActRooezvSLO/sKZP/nq8n4+xcyjNsRu8zW6KWpdb7wjiQd4WrtFZYFiKHENSmWp6xshh96c2RQ+c7Lt+qbijyEjHWUJ/pZsy8MGIUuzNiPySK2Gqoh6ZTRF6ko6q3nVTkaA//itIrDpW6l3SLo8juOmqMXkYknu5FdQxWbhCfKHEGDhxxyTVaXJF3ZjSl3jMksjSOOKmne9pI+mcG5QvaUJhI9HpkmRo2NpCrDJvsktRhRE2MM6F2n7dt4OaMUq8bCctk0+PoMRzL+1l5PZ2eyM/Owr86gf8z/tOM53lom5+nVcFuB+eJVzlXwAYy9TZ9s537tfqcsJWbEU4nBngZo6FfO9T9CdhfBtmk2dLiAy8uS4zwOpMx2HqYbTC+amNeAYTpsP4SIgvWfUBWXxn3CMHW3ffd7k3+YIkx7w0t/CVGvcPejoeOlzOWzeGbawOHqXQGUTMZRcfj4XPCgW9y/fuvVn8zD9P1QHzv80uAAQA0i3Jer7Jr7gAAAABJRU5ErkJggg==') 99% 10px no-repeat;border-top:1px solid #DDD}#netteBluescreen .highlight{background:#CD1818;color:white;font-weight:bold;font-style:normal;display:block;padding:0 .4em;margin:0 -.4em}#netteBluescreen .line{color:#9F9C7F;font-weight:normal;font-style:normal}#netteBluescreen a[href^=editor\:]{color:inherit;border-bottom:1px dotted #C1D2E1}#netteBluescreen .caused{float:right;padding:.3em .6em;background:#df8075;border-radius:0 0 0 8px;white-space:nowrap}#netteBluescreen .caused a{color:white}</style>
</head>



<body>
<script>document.body.className+=" js";</script>
<div id="netteBluescreen">
	<a id="netteBluescreenIcon" href="#" rel="next"><abbr>&#x25bc;</abbr></a

	><div>
		<div id="netteBluescreenError" class="panel">
			<h1><?php echo
htmlspecialchars($title),($exception->getCode()?' #'.$exception->getCode():'')?></h1>

			<p><?php echo
htmlspecialchars($exception->getMessage(),ENT_IGNORE)?> <a href="https://www.google.com/search?sourceid=nette&amp;q=<?php echo
urlencode($title.' '.preg_replace('#\'.*\'|".*"#Us','',$exception->getMessage()))?>" id="netteBsSearch" rel="noreferrer">search&#x25ba;</a></p>
		</div>

		<?php if((method_exists($exception,'getPrevious')&&$prev=$exception->getPrevious())||(isset($exception->previous)&&$prev=$exception->previous)):?>
		<div class="caused">
			<a href="#netteCaused">Caused by <?php echo
get_class($prev)?></a>
		</div>
		<?php endif?>


		<?php $ex=$exception;$level=0;?>
		<?php do{?>

			<?php if($level++):?>
			<div class="panel"<?php if($level===2)echo' id="netteCaused"'?>>
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
			<h2><a href="#" rel="netteBsPnl<?php echo++$counter?>">Variables <abbr>&#x25ba;</abbr></a></h2>

			<div id="netteBsPnl<?php echo$counter?>" class="nette-collapsed inner">
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
preg_replace('#^.+<body>|</body>.+\z#s','',ob_get_clean())?>
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

<script type="text/javascript">/*<![CDATA[*/var bs=document.getElementById("netteBluescreen");document.body.appendChild(bs);document.onkeyup=function(b){b=b||window.event;if(27==b.keyCode&&!(b.shiftKey||b.altKey||b.ctrlKey||b.metaKey))bs.onclick({target:document.getElementById("netteBluescreenIcon")})};
for(var i=0,styles=document.styleSheets;i<styles.length;i++)"nette-debug"!==(styles[i].owningElement||styles[i].ownerNode).className?(styles[i].oldDisabled=styles[i].disabled,styles[i].disabled=!0):styles[i].addRule?styles[i].addRule(".nette-collapsed","display: none"):styles[i].insertRule(".nette-collapsed { display: none }",0);
bs.onclick=function(b){b=b||window.event;for(var a=b.target||b.srcElement;a&&a.tagName&&"a"!==a.tagName.toLowerCase();)a=a.parentNode;if(!a||!a.rel)return!0;for(var d=a.getElementsByTagName("abbr")[0],c="next"===a.rel?a.nextSibling:document.getElementById(a.rel);1!==c.nodeType;)c=c.nextSibling;b=c.currentStyle?"none"==c.currentStyle.display:"none"==getComputedStyle(c,null).display;try{d.innerHTML=String.fromCharCode(b?9660:9658)}catch(e){}c.style.display=b?"code"===c.tagName.toLowerCase()?"inline":
"block":"none";if("netteBluescreenIcon"===a.id)for(a=0,d=document.styleSheets;a<d.length;a++)"nette-debug"!==(d[a].owningElement||d[a].ownerNode).className&&(d[a].disabled=b?!0:d[a].oldDisabled);return!1};/*]]>*/</script>
</body>
</html>
<?php }static
function
highlightFile($file,$line,$lines=15,$vars=array()){$source=@file_get_contents($file);if($source){return
self::highlightPhp($source,$line,$lines,$vars);}}static
function
highlightPhp($source,$line,$lines=15,$vars=array()){if(function_exists('ini_set')){ini_set('highlight.comment','#998; font-style: italic');ini_set('highlight.default','#000');ini_set('highlight.html','#06B');ini_set('highlight.keyword','#D24; font-weight: bold');ini_set('highlight.string','#080');}$source=str_replace(array("\r\n","\r"),"\n",$source);$source=explode("\n",highlight_string($source,TRUE));$spans=1;$out=$source[0];$source=explode('<br />',$source[1]);array_unshift($source,NULL);$start=$i=max(1,$line-floor($lines*2/3));while(--$i>=1){if(preg_match('#.*(</?span[^>]*>)#',$source[$i],$m)){if($m[1]!=='</span>'){$spans++;$out.=$m[1];}break;}}$source=array_slice($source,$start,$lines,TRUE);end($source);$numWidth=strlen((string)key($source));foreach($source
as$n=>$s){$spans+=substr_count($s,'<span')-substr_count($s,'</span');$s=str_replace(array("\r","\n"),array('',''),$s);preg_match_all('#<[^>]+>#',$s,$tags);if($n==$line){$out.=sprintf("<span class='highlight'>%{$numWidth}s:    %s\n</span>%s",$n,strip_tags($s),implode('',$tags[0]));}else{$out.=sprintf("<span class='line'>%{$numWidth}s:</span>    %s\n",$n,$s);}}$out.=str_repeat('</span>',$spans).'</code>';$out=preg_replace_callback('#">\$(\w+)(&nbsp;)?</span>#',create_function('$m','extract($GLOBALS[0]['.array_push($GLOBALS[0],array('vars'=>$vars)).'-1], EXTR_REFS);
			return isset($vars[$m[1]])
				? \'" title="\' . str_replace(\'"\', \'&quot;\', strip_tags(NDebugHelpers::htmlDump($vars[$m[1]]))) . $m[0]
				: $m[0];
		'),$out);return"<pre><div>$out</div></pre>";}}class
NDebugBar
extends
NObject{private$panels=array();function
addPanel(IBarPanel$panel,$id=NULL){if($id===NULL){$c=0;do{$id=get_class($panel).($c++?"-$c":'');}while(isset($this->panels[$id]));}$this->panels[$id]=$panel;return$this;}function
render(){$obLevel=ob_get_level();$panels=array();foreach($this->panels
as$id=>$panel){try{$panels[]=array('id'=>preg_replace('#[^a-z0-9]+#i','-',$id),'tab'=>$tab=(string)$panel->getTab(),'panel'=>$tab?(string)$panel->getPanel():NULL);}catch(Exception$e){$panels[]=array('id'=>"error-".preg_replace('#[^a-z0-9]+#i','-',$id),'tab'=>"Error in $id",'panel'=>'<h1>Error: '.$id.'</h1><div class="nette-inner">'.nl2br(htmlSpecialChars($e)).'</div>');while(ob_get_level()>$obLevel){ob_end_clean();}}}?>



<!-- Nette Debug Bar -->

<?php ob_start()?>
&nbsp;

<style id="nette-debug-style" class="nette-debug">#nette-debug{display:none;position:fixed;direction:ltr}body#nette-debug{margin:5px 5px 0;display:block}body #nette-debug{position:absolute;bottom:0;right:0}#nette-debug *{font:inherit;color:inherit;background:transparent;margin:0;padding:0;border:none;text-align:inherit;list-style:inherit;opacity:1;border-radius:0;box-shadow:none}#nette-debug b,#nette-debug strong{font-weight:bold}#nette-debug i,#nette-debug em{font-style:italic}#nette-debug .nette-fixed-coords{position:fixed;_position:absolute;right:0;bottom:0;max-width:100%}#nette-debug a{color:#125EAE;text-decoration:none}#nette-debug .nette-panel a{color:#125EAE;text-decoration:none}#nette-debug a:hover,#nette-debug a:active,#nette-debug a:focus{background-color:#125EAE;color:white}#nette-debug .nette-panel h2,#nette-debug .nette-panel h3,#nette-debug .nette-panel p{margin:.4em 0}#nette-debug .nette-panel table{border-collapse:collapse;background:#FDF5CE}#nette-debug .nette-panel tr:nth-child(2n) td{background:#F7F0CB}#nette-debug .nette-panel td,#nette-debug .nette-panel th{border:1px solid #E6DFBF;padding:2px 5px;vertical-align:top;text-align:left}#nette-debug .nette-panel th{background:#F4F3F1;color:#655E5E;font-size:90%;font-weight:bold}#nette-debug .nette-panel pre,#nette-debug .nette-panel code{font:9pt/1.5 Consolas,monospace}#nette-debug table .nette-right{text-align:right}.nette-hidden,.nette-collapsed{display:none}#nette-debug-bar{font:normal normal 12px/21px Tahoma,sans-serif;color:#333;border:1px solid #c9c9c9;background:#EDEAE0 url('data:image/png;base64,R0lGODlhAQAVALMAAOTh1/Px6eHe1fHv5e/s4vLw6Ofk2u3q4PPw6PPx6PDt5PLw5+Dd1OXi2Ojm3Orn3iH5BAAAAAAALAAAAAABABUAAAQPMISEyhpYkfOcaQAgCEwEADs=') top;position:relative;overflow:auto;min-height:21px;_float:left;min-width:50px;white-space:nowrap;z-index:23181;opacity:.9;border-radius:3px;box-shadow:1px 1px 10px rgba(0,0,0,.15)}#nette-debug-bar:hover{opacity:1}#nette-debug-bar ul{list-style:none none;margin-left:4px}#nette-debug-bar li{float:left}#nette-debug-bar img{vertical-align:middle;position:relative;top:-1px;margin-right:3px}#nette-debug-bar li a{color:#000;display:block;padding:0 4px}#nette-debug-bar li a:hover{color:black;background:#c3c1b8}#nette-debug-bar li .nette-warning{color:#D32B2B;font-weight:bold}#nette-debug-bar li>span{padding:0 4px}#nette-debug-logo{background:url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAC0AAAAPCAYAAABwfkanAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAABiFJREFUSMe1VglPlGcQ5i+1xjZNqxREtGq8ahCPWsVGvEDBA1BBRQFBDjkE5BYUzwpovRBUREBEBbl3OVaWPfj2vi82eTrvbFHamLRJ4yYTvm+u95mZZ96PoKAv+LOatXBYZ+Bx6uFy6DGnt1m0EOKwSmQzwmHTgX5B/1W+yM9GYJ02CX6/B/5ZF+w2A4x6FYGTYDVp4PdY2Tbrs5N+mnRa2Km4/wV6rhPzQQj5fDc1mJM5nd0iYdZtQWtrCxobGnDpUiledTynbuvg99mgUMhw924Trl2rR01NNSTNJE9iDpTV8innv4K2kZPLroPXbYLHZeSu2K1aeF0muJ2GvwGzmNSwU2E+svm8ZrgdBliMaha/34Vx+RAKCgpwpa4OdbW1UE/L2cc/68WtWzdRVlaG6uoqtD1/BA/pA1MIxLvtes7pc5vhoDOE/rOgbVSdf9aJWa8dBp0Kyg+jdLiTx2vQKWEyqGmcNkqg4iTC1+dzQatWkK+cJqPD7KyFaKEjvRuNjY24fLkGdXW1ePjwAeX4QHonDNI0A75+/RpqqqshH+6F2UAUMaupYXouykV0mp6SQ60coxgL8Z4aMg/4x675/V60v3jKB+Xl5WJibIC4KPEIS0qKqWv5GOh7BZ/HSIk9kA33o7y8DOfPZ6GQOipkXDZAHXKxr4ipqqpkKS6+iIrycgz2dyMnJxtVlZUsotNZWZmor79KBbvgpdjm5sfIzc1hv4L8fKJPDTfJZZc+gRYKr8sAEy2DcBRdEEk62ltx9uwZ5qNILoDU1l6mbrvx5EkzUlKSuTiR7PHjR3x4fv4FyIbeIic7G5WVFUyN+qtX+Lnt2SPcvn2LfURjhF7kE4WPDr+Bx+NEUVEhkpNPoImm5CSOl5aUIC3tLOMR59gtAY4HidGIzj14cB8ZGRkM8kJeHk6cOI4xWR8vSl5uLlJTT6O74xnT5lB8PM6cSYXVqILb5UBWZiYSExMYkE4zzjqX00QHG+h9AjPqMei0k3ywy2khMdNiq6BVCf04T6ekuBgJCUdRUVHOBQwPvkNSUiLjaGi4Q/5qFgYtHgTXRJdTT59GenoaA5gY64deq0Bc3EGuNj4+DnppEheLijhZRkY6SktLsGPHdi6irOwSFTRAgO04deokTSIFsbExuHfvLnFSx8DevelAfFwcA0lJTqZi5PDS9aci/sbE7Oe4wsICbtD27b/ye1NTI3FeSX4W2gdFALRD3A4eM44ePcKViuD79/8gnZP5Kg4+cCAW2dnnqUM2Lujw4UM4ePAA2ztfPsHIYA/sdOt43A50d7UFCjkUj+joXVBMDJDeDhcVk08cjd61C3v37uFYp8PKXX3X8xJRUTtw7FgSn3Xzxg10d7ZCqRjkM+02C7pettDNogqAFjzxuI3YHR2Nffv2coXy0V44HGZERm7kJNu2/cK8bW9rwbp1axnMnj27uUijQQOb1QyTcYZ3YMOGn/Hbzp1crAAvaDfY38O5hW3//n0ce+TIYWiUcub1xo0R2Lp1y8cYsUMWM125VhPe93Zj7do1vEPi26GfUdBFbhK8tGHrli1YsWwpgoOD0dXRQqAtXMCy8DBs3rwJoSGLsWrVclylBdoUGYlVK1dg9eqVCFsSSs8/4btvvmUwEnE0KTERISE/IiIiAsGLF2HhwgU8qbc97QgPX8qFr1mzGgu+/opzdL5o5l1aEhqC9evXYWlYKFYsD6e/YVj0w/dMGZVyBDMqeaDTRuKpkxYjIz2dOyeup6H3r2kkOuJ1H3N5Z1QUzp3LQF9vJ4xGLQYHXiM9LY0pEhsTg+PHj9HNcJu4OcL3uaQZY86LiZw8mcJTkmhBTUYJbU8fcoygobgWR4Z6iKtTPLE7d35HYkICT1dIZuY59HQ9412StBPQTMvw8Z6WaMNFxy3Gab4TeQT0M9IHwUT/G0i0MGIJ9CTiJjBIH+iQaQbC7+QnfEXiQL6xgF09TjETHCt8RbeMuil+D8RNsV1LHdQoZfR/iJJzCZuYmEE/Bd3MJNs/+0UURgFWJJ//aQ8k+CsxVTqnVytHObkQrUoG8T4/bs4u4ubbxLPwFzYNPc8HI2zijLm84l39Dx8hfwJenFezFBKKQwAAAABJRU5ErkJggg==') 0 50% no-repeat;min-width:45px;cursor:move}#nette-debug-logo span{display:none}#nette-debug-bar-bgl,#nette-debug-bar-bgx,#nette-debug-bar-bgr{position:absolute;z-index:-1;top:-7px;height:37px}#nette-debug .nette-panel{font:normal normal 12px/1.5 sans-serif;background:white;color:#333;text-align:left}#nette-debug h1{font:normal normal 23px/1.4 Tahoma,sans-serif;color:#575753;margin:-5px -5px 5px;padding:0 25px 5px 5px}#nette-debug .nette-mode-peek .nette-inner,#nette-debug .nette-mode-float .nette-inner{max-width:700px;max-height:500px;overflow:auto}#nette-debug .nette-panel .nette-icons{display:none}#nette-debug .nette-mode-peek{display:none;position:relative;z-index:23180;padding:10px;min-width:150px;min-height:50px;border-radius:5px;box-shadow:1px 1px 20px rgba(102,102,102,0.36);border:1px solid rgba(0,0,0,0.1)}#nette-debug .nette-mode-peek h1{cursor:move}#nette-debug .nette-mode-float{position:relative;padding:10px;min-width:150px;min-height:50px;border-radius:5px;opacity:.95;box-shadow:1px 1px 30px rgba(102,102,102,0.36);border:1px solid rgba(0,0,0,0.1)}#nette-debug .nette-focused{opacity:1}#nette-debug .nette-mode-float h1{cursor:move}#nette-debug .nette-mode-float .nette-icons{display:block;position:absolute;top:0;right:5px;font-size:18px}#nette-debug .nette-icons a{color:#575753}#nette-debug .nette-icons a:hover{color:white}pre.nette-dump{color:#444;background:white}pre.nette-dump a,#nette-debug pre.nette-dump a{color:inherit;background:inherit}pre.nette-dump .php-array,pre.nette-dump .php-object,#nette-debug pre.nette-dump .php-array,#nette-debug pre.nette-dump .php-object{color:#C22}pre.nette-dump .php-string,#nette-debug pre.nette-dump .php-string{color:#080}pre.nette-dump .php-int,pre.nette-dump .php-float,#nette-debug pre.nette-dump .php-int,#nette-debug pre.nette-dump .php-float{color:#37D}pre.nette-dump .php-null,pre.nette-dump .php-bool,#nette-debug pre.nette-dump .php-null,#nette-debug pre.nette-dump .php-bool{color:black}pre.nette-dump .php-visibility,#nette-debug pre.nette-dump .php-visibility{font-size:85%;color:#999}@media print{#nette-debug *{display:none}}</style>

<!--[if lt IE 8]><style class="nette-debug">#nette-debug-bar img{display:none}#nette-debug-bar li{border-left:1px solid #DCD7C8;padding:0 3px}#nette-debug-logo span{background:#edeae0;display:inline}</style><![endif]-->


<script id="nette-debug-script">/*<![CDATA[*/var Nette=Nette||{};
(function(){Nette.Class=function(a){var b=Object.prototype.hasOwnProperty,c,d=b.call(a,"constructor")?a.constructor:function(){};delete a.constructor;if(a.Extends){var f=function(){this.constructor=d};f.prototype=a.Extends.prototype;d.prototype=new f;delete a.Extends}if(a.Static){for(c in a.Static)b.call(a.Static,c)&&(d[c]=a.Static[c]);delete a.Static}for(c in a)b.call(a,c)&&(d.prototype[c]=a[c]);return d};Nette.Q=Nette.Class({Static:{factory:function(a){return new Nette.Q(a)},implement:function(a){var b,
c=Nette.Q.implement,d=Nette.Q.prototype,f=Object.prototype.hasOwnProperty;for(b in a)f.call(a,b)&&(c[b]=a[b],d[b]=function(a){return function(){return this.each(c[a],arguments)}}(b))}},constructor:function(a){if("string"===typeof a)a=this._find(document,a);else if(!a||a.nodeType||void 0===a.length||a===window)a=[a];for(var b=0,c=a.length;b<c;b++)a[b]&&(this[this.length++]=a[b])},length:0,find:function(a){return new Nette.Q(this._find(this[0],a))},_find:function(a,b){if(a&&b){if(document.querySelectorAll)return a.querySelectorAll(b);
if("#"===b.charAt(0))return[document.getElementById(b.substring(1))];b=b.split(".");var c=a.getElementsByTagName(b[0]||"*");if(b[1]){for(var d=[],f=RegExp("(^|\\s)"+b[1]+"(\\s|$)"),e=0,h=c.length;e<h;e++)f.test(c[e].className)&&d.push(c[e]);return d}return c}return[]},dom:function(){return this[0]},each:function(a,b){for(var c=0,d;c<this.length;c++)if(void 0!==(d=a.apply(this[c],b||[])))return d;return this}});var k=Nette.Q.factory,e=Nette.Q.implement;e({bind:function(a,b){if(document.addEventListener&&
("mouseenter"===a||"mouseleave"===a)){var c=b;a="mouseenter"===a?"mouseover":"mouseout";b=function(a){for(var b=a.relatedTarget;b;b=b.parentNode)if(b===this)return;c.call(this,a)}}var d=e.data.call(this),d=d.events=d.events||{};if(!d[a]){var f=this,g=d[a]=[],h=e.bind.genericHandler=function(a){a.target||(a.target=a.srcElement);a.preventDefault||(a.preventDefault=function(){a.returnValue=!1});a.stopPropagation||(a.stopPropagation=function(){a.cancelBubble=!0});a.stopImmediatePropagation=function(){this.stopPropagation();
b=g.length};for(var b=0;b<g.length;b++)g[b].call(f,a)};document.addEventListener?this.addEventListener(a,h,!1):document.attachEvent&&this.attachEvent("on"+a,h)}d[a].push(b)},addClass:function(a){this.className=this.className.replace(/^|\s+|$/g," ").replace(" "+a+" "," ")+" "+a},removeClass:function(a){this.className=this.className.replace(/^|\s+|$/g," ").replace(" "+a+" "," ")},hasClass:function(a){return-1<this.className.replace(/^|\s+|$/g," ").indexOf(" "+a+" ")},show:function(){var a=e.show.display=
e.show.display||{},b=this.tagName;if(!a[b]){var c=document.body.appendChild(document.createElement(b));a[b]=e.css.call(c,"display")}this.style.display=a[b]},hide:function(){this.style.display="none"},css:function(a){return this.currentStyle?this.currentStyle[a]:window.getComputedStyle?document.defaultView.getComputedStyle(this,null).getPropertyValue(a):void 0},data:function(){return this.nette?this.nette:this.nette={}},val:function(){var a,b,c;if(!this.nodeName){a=0;for(b=this.length;a<b;a++)if(this[a].checked)return this[a].value;
return null}if("select"===this.nodeName.toLowerCase()){a=this.selectedIndex;var d=this.options;if(0>a)return null;if("select-one"===this.type)return d[a].value;a=0;c=[];for(b=d.length;a<b;a++)d[a].selected&&c.push(d[a].value);return c}return"checkbox"===this.type?this.checked:this.value.replace(/^\s+|\s+$/g,"")},_trav:function(a,b,c){for(b=b.split(".");a&&(1!==a.nodeType||b[0]&&a.tagName.toLowerCase()!==b[0]||b[1]&&!e.hasClass.call(a,b[1]));)a=a[c];return k(a)},closest:function(a){return e._trav(this,
a,"parentNode")},prev:function(a){return e._trav(this.previousSibling,a,"previousSibling")},next:function(a){return e._trav(this.nextSibling,a,"nextSibling")},offset:function(a){for(var b=this,c=a?{left:-a.left||0,top:-a.top||0}:e.position.call(b);b=b.offsetParent;)c.left+=b.offsetLeft,c.top+=b.offsetTop;if(a)e.position.call(this,{left:-c.left,top:-c.top});else return c},position:function(a){if(a)this.nette&&this.nette.onmove&&this.nette.onmove.call(this,a),this.style.left=(a.left||0)+"px",this.style.top=
(a.top||0)+"px";else return{left:this.offsetLeft,top:this.offsetTop,width:this.offsetWidth,height:this.offsetHeight}},draggable:function(a){var b=k(this),c=document.documentElement,d;a=a||{};k(a.handle||this).bind("mousedown",function(f){f.preventDefault();f.stopPropagation();if(e.draggable.binded)return c.onmouseup(f);var g=b[0].offsetLeft-f.clientX,h=b[0].offsetTop-f.clientY;e.draggable.binded=!0;d=!1;c.onmousemove=function(c){c=c||event;d||(a.draggedClass&&b.addClass(a.draggedClass),a.start&&a.start(c,
b),d=!0);b.position({left:c.clientX+g,top:c.clientY+h});return!1};c.onmouseup=function(f){d&&(a.draggedClass&&b.removeClass(a.draggedClass),a.stop&&a.stop(f||event,b));e.draggable.binded=c.onmousemove=c.onmouseup=null;return!1}}).bind("click",function(a){d&&a.stopImmediatePropagation()})}})})();
(function(){Nette.Debug={};var k=2E4,e=Nette.Q.factory,a=Nette.Debug.Panel=Nette.Class({Extends:Nette.Q,Static:{PEEK:"nette-mode-peek",FLOAT:"nette-mode-float",WINDOW:"nette-mode-window",FOCUSED:"nette-focused",factory:function(b){return new a(b)},_toggle:function(a){var c=a.rel,c="#"===c.charAt(0)?e(c):e(a)["prev"===c?"prev":"next"](c.substring(4));"none"===c.css("display")?(c.show(),a.innerHTML=a.innerHTML.replace("►","▼")):(c.hide(),a.innerHTML=a.innerHTML.replace("▼","►"))}},constructor:function(a){Nette.Q.call(this,
"#nette-debug-panel-"+a.replace("nette-debug-panel-",""))},reposition:function(){this.hasClass(a.WINDOW)?window.resizeBy(document.documentElement.scrollWidth-document.documentElement.clientWidth,document.documentElement.scrollHeight-document.documentElement.clientHeight):(this.position(this.position()),this.position().width&&(document.cookie=this.dom().id+"="+this.position().left+":"+this.position().top+"; path=/"))},focus:function(b){if(this.hasClass(a.WINDOW))this.data().win.focus();else{var c=
this;clearTimeout(this.data().displayTimeout);this.data().displayTimeout=setTimeout(function(){c.addClass(a.FOCUSED).show();c[0].style.zIndex=k++;b&&b()},50)}},blur:function(){this.removeClass(a.FOCUSED);if(this.hasClass(a.PEEK)){var b=this;clearTimeout(this.data().displayTimeout);this.data().displayTimeout=setTimeout(function(){b.hide()},50)}},toFloat:function(){this.removeClass(a.WINDOW).removeClass(a.PEEK).addClass(a.FLOAT).show().reposition();return this},toPeek:function(){this.removeClass(a.WINDOW).removeClass(a.FLOAT).addClass(a.PEEK).hide();
document.cookie=this.dom().id+"=; path=/"},toWindow:function(){var b=this,c,d;d=this.offset();var f=this.dom().id;d.left+="number"===typeof window.screenLeft?window.screenLeft:window.screenX+10;d.top+="number"===typeof window.screenTop?window.screenTop:window.screenY+50;if(c=window.open("",f.replace(/-/g,"_"),"left="+d.left+",top="+d.top+",width="+d.width+",height="+(d.height+15)+",resizable=yes,scrollbars=yes"))d=c.document,d.write('<!DOCTYPE html><meta charset="utf-8"><style>'+e("#nette-debug-style").dom().innerHTML+
"</style><script>"+e("#nette-debug-script").dom().innerHTML+'\x3c/script><body id="nette-debug">'),d.body.innerHTML='<div class="nette-panel nette-mode-window" id="'+f+'">'+this.dom().innerHTML+"</div>",c.Nette.Debug.Panel.factory(f).initToggler().reposition(),d.title=b.find("h1").dom().innerHTML,e([c]).bind("unload",function(){b.toPeek();c.close()}),e(d).bind("keyup",function(a){27!==a.keyCode||(a.shiftKey||a.altKey||a.ctrlKey||a.metaKey)||c.close()}),document.cookie=f+"=window; path=/",this.hide().removeClass(a.FLOAT).removeClass(a.PEEK).addClass(a.WINDOW).data().win=
c},init:function(){var b=this,c;b.data().onmove=function(a){var b=document,c=window.innerWidth||b.documentElement.clientWidth||b.body.clientWidth,b=window.innerHeight||b.documentElement.clientHeight||b.body.clientHeight;a.left=Math.max(Math.min(a.left,0.8*this.offsetWidth),0.2*this.offsetWidth-c);a.top=Math.max(Math.min(a.top,0.8*this.offsetHeight),this.offsetHeight-b)};e(window).bind("resize",function(){b.reposition()});b.draggable({handle:b.find("h1"),stop:function(){b.toFloat()}}).bind("mouseenter",
function(){b.focus()}).bind("mouseleave",function(){b.blur()});this.initToggler();b.find(".nette-icons").find("a").bind("click",function(a){"close"===this.rel?b.toPeek():b.toWindow();a.preventDefault()});(c=document.cookie.match(RegExp(b.dom().id+"=(window|(-?[0-9]+):(-?[0-9]+))")))?c[2]?b.toFloat().position({left:c[2],top:c[3]}):b.toWindow():b.addClass(a.PEEK)},initToggler:function(){var b=this;this.bind("click",function(c){var d=e(c.target).closest("a").dom();d&&d.rel&&(a._toggle(d),c.preventDefault(),
b.reposition())});return this}});Nette.Debug.Bar=Nette.Class({Extends:Nette.Q,constructor:function(){Nette.Q.call(this,"#nette-debug-bar")},init:function(){var b=this,c;b.data().onmove=function(a){var b=document,c=window.innerWidth||b.documentElement.clientWidth||b.body.clientWidth,b=window.innerHeight||b.documentElement.clientHeight||b.body.clientHeight;a.left=Math.max(Math.min(a.left,0),this.offsetWidth-c);a.top=Math.max(Math.min(a.top,0),this.offsetHeight-b)};e(window).bind("resize",function(){b.position(b.position())});
b.draggable({draggedClass:"nette-dragged",stop:function(){document.cookie=b.dom().id+"="+b.position().left+":"+b.position().top+"; path=/"}});b.find("a").bind("click",function(b){if("close"===this.rel)e("#nette-debug").hide(),window.opera&&e("body").show();else if(this.rel){var c=a.factory(this.rel);if(b.shiftKey)c.toFloat().toWindow();else if(c.hasClass(a.FLOAT)){var g=e(this).offset();c.offset({left:g.left-c.position().width+g.width+4,top:g.top-c.position().height-4}).toPeek()}else c.toFloat().position({left:c.position().left-
Math.round(100*Math.random())-20,top:c.position().top-Math.round(100*Math.random())-20}).reposition()}b.preventDefault()}).bind("mouseenter",function(){if(this.rel&&"close"!==this.rel&&!b.hasClass("nette-dragged")){var c=a.factory(this.rel),f=e(this);c.focus(function(){if(c.hasClass(a.PEEK)){var b=f.offset();c.offset({left:b.left-c.position().width+b.width+4,top:b.top-c.position().height-4})}})}}).bind("mouseleave",function(){this.rel&&("close"!==this.rel&&!b.hasClass("nette-dragged"))&&a.factory(this.rel).blur()});
(c=document.cookie.match(RegExp(b.dom().id+"=(-?[0-9]+):(-?[0-9]+)")))&&b.position({left:c[1],top:c[2]});b.find("a").each(function(){this.rel&&"close"!==this.rel&&a.factory(this.rel).init()})}})})();/*]]>*/</script>


<?php foreach($panels
as$panel):if(!$panel['panel'])continue;?>
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
(function(onloadOrig) {
	window.onload = function() {
		if (typeof onloadOrig === 'function') onloadOrig();
		var debug = document.getElementById('nette-debug');
		document.body.appendChild(debug);
		debug.innerHTML = <?php echo
json_encode(NStrings::fixEncoding($output))?>;
		for (var i = 0, scripts = debug.getElementsByTagName('script'); i < scripts.length; i++) eval(scripts[i].innerHTML);
		(new Nette.Debug.Bar).init();
		Nette.Q.factory(debug).show();
	};
})(window.onload);
</script>

<!-- /Nette Debug Bar -->
<?php }}class
NDefaultBarPanel
extends
NObject
implements
IBarPanel{private$id;public$data;function
__construct($id){$this->id=$id;}function
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
/><span class="nette-warning"><?php echo$sum=array_sum($data),$sum>1?' errors':' error'?></span>
<?php }return
ob_get_clean();}function
getPanel(){ob_start();$data=$this->data;if($this->id==='dumps'){?>
<style class="nette-debug">#nette-debug .nette-DumpPanel h2{font:11pt/1.5 sans-serif;margin:0;padding:2px 8px;background:#3484d2;color:white}#nette-debug .nette-DumpPanel table{width:100%}#nette-debug .nette-DumpPanel a{color:#333;background:transparent}#nette-debug .nette-DumpPanel a abbr{font-family:sans-serif;color:#999}#nette-debug .nette-DumpPanel pre .php-array,#nette-debug .nette-DumpPanel pre .php-object{color:#c16549}</style>


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
as$item=>$count):list($file,$line,$message)=explode('|',$item,3)?>
<tr class="<?php echo$i++%
2?'nette-alt':''?>">
	<td class="nette-right"><?php echo$count?"$count\xC3\x97":''?></td>
	<td><pre><?php echo
htmlspecialchars(NStrings::fixEncoding($message)),' in ',NDebugHelpers::editorLink($file,$line),':',$line?></pre></td>
</tr>
<?php endforeach?>
</table>
</div>
<?php }return
ob_get_clean();}}class
NSafeStream{const
PROTOCOL='safe';private$handle;private$tempHandle;private$file;private$tempFile;private$deleteFile;private$writeError=FALSE;static
function
register(){return
stream_wrapper_register(self::PROTOCOL,__CLASS__);}function
stream_open($path,$mode,$options,&$opened_path){$path=substr($path,strlen(self::PROTOCOL)+3);$flag=trim($mode,'crwax+');$mode=trim($mode,'tb');$use_path=(bool)(STREAM_USE_PATH&$options);if($mode==='r'){return$this->checkAndLock($this->tempHandle=fopen($path,'r'.$flag,$use_path),LOCK_SH);}elseif($mode==='r+'){if(!$this->checkAndLock($this->handle=fopen($path,'r'.$flag,$use_path),LOCK_EX)){return
FALSE;}}elseif($mode[0]==='x'){if(!$this->checkAndLock($this->handle=fopen($path,'x'.$flag,$use_path),LOCK_EX)){return
FALSE;}$this->deleteFile=TRUE;}elseif($mode[0]==='w'||$mode[0]==='a'||$mode[0]==='c'){if($this->checkAndLock($this->handle=@fopen($path,'x'.$flag,$use_path),LOCK_EX)){$this->deleteFile=TRUE;}elseif(!$this->checkAndLock($this->handle=fopen($path,'a+'.$flag,$use_path),LOCK_EX)){return
FALSE;}}else{trigger_error("Unknown mode $mode",E_USER_WARNING);return
FALSE;}$tmp='~~'.lcg_value().'.tmp';if(!$this->tempHandle=fopen($path.$tmp,(strpos($mode,'+')?'x+':'x').$flag,$use_path)){$this->clean();return
FALSE;}$this->tempFile=realpath($path.$tmp);$this->file=substr($this->tempFile,0,-strlen($tmp));if($mode==='r+'||$mode[0]==='a'||$mode[0]==='c'){$stat=fstat($this->handle);fseek($this->handle,0);if($stat['size']!==0&&stream_copy_to_stream($this->handle,$this->tempHandle)!==$stat['size']){$this->clean();return
FALSE;}if($mode[0]==='a'){fseek($this->tempHandle,0,SEEK_END);}}return
TRUE;}private
function
checkAndLock($handle,$lock){if(!$handle){return
FALSE;}elseif(!flock($handle,$lock)){fclose($handle);return
FALSE;}return
TRUE;}private
function
clean(){flock($this->handle,LOCK_UN);fclose($this->handle);if($this->deleteFile){unlink($this->file);}if($this->tempHandle){fclose($this->tempHandle);unlink($this->tempFile);}}function
stream_close(){if(!$this->tempFile){flock($this->tempHandle,LOCK_UN);fclose($this->tempHandle);return;}flock($this->handle,LOCK_UN);fclose($this->handle);fclose($this->tempHandle);if($this->writeError||!(substr(PHP_OS,0,3)==='WIN'?unlink($this->file):TRUE)||!rename($this->tempFile,$this->file)){unlink($this->tempFile);if($this->deleteFile){unlink($this->file);}}}function
stream_read($length){return
fread($this->tempHandle,$length);}function
stream_write($data){$len=strlen($data);$res=fwrite($this->tempHandle,$data,$len);if($res!==$len){$this->writeError=TRUE;}return$res;}function
stream_tell(){return
ftell($this->tempHandle);}function
stream_eof(){return
feof($this->tempHandle);}function
stream_seek($offset,$whence){return
fseek($this->tempHandle,$offset,$whence)===0;}function
stream_stat(){return
fstat($this->tempHandle);}function
url_stat($path,$flags){$path=substr($path,strlen(self::PROTOCOL)+3);return($flags&STREAM_URL_STAT_LINK)?@lstat($path):@stat($path);}function
unlink($path){$path=substr($path,strlen(self::PROTOCOL)+3);return
unlink($path);}}class
NApplication
extends
NObject{public
static$maxLoop=20;public$catchExceptions;public$errorPresenter;public$onStartup;public$onShutdown;public$onRequest;public$onResponse;public$onError;public$allowedMethods;private$requests=array();private$presenter;private$httpRequest;private$httpResponse;private$presenterFactory;private$router;function
__construct(IPresenterFactory$presenterFactory,IRouter$router,IHttpRequest$httpRequest,IHttpResponse$httpResponse){$this->httpRequest=$httpRequest;$this->httpResponse=$httpResponse;$this->presenterFactory=$presenterFactory;$this->router=$router;}function
run(){$request=NULL;$repeatedError=FALSE;do{try{if(count($this->requests)>self::$maxLoop){throw
new
NApplicationException('Too many loops detected in application life cycle.');}if(!$request){$this->onStartup($this);$request=$this->router->match($this->httpRequest);if(!$request
instanceof
NPresenterRequest){$request=NULL;throw
new
NBadRequestException('No route for HTTP request.');}if(strcasecmp($request->getPresenterName(),$this->errorPresenter)===0){throw
new
NBadRequestException('Invalid request. Presenter is not achievable.');}}$this->requests[]=$request;$this->onRequest($this,$request);$presenterName=$request->getPresenterName();try{$this->presenter=$this->presenterFactory->createPresenter($presenterName);}catch(NInvalidPresenterException$e){throw
new
NBadRequestException($e->getMessage(),404,$e);}$this->presenterFactory->getPresenterClass($presenterName);$request->setPresenterName($presenterName);$request->freeze();$response=$this->presenter->run($request);if($response){$this->onResponse($this,$response);}if($response
instanceof
NForwardResponse){$request=$response->getRequest();continue;}elseif($response
instanceof
IPresenterResponse){$response->send($this->httpRequest,$this->httpResponse);}break;}catch(Exception$e){$this->onError($this,$e);if(!$this->catchExceptions){$this->onShutdown($this,$e);throw$e;}if($repeatedError){$e=new
NApplicationException('An error occurred while executing error-presenter',0,$e);}if(!$this->httpResponse->isSent()){$this->httpResponse->setCode($e
instanceof
NBadRequestException?$e->getCode():500);}if(!$repeatedError&&$this->errorPresenter){$repeatedError=TRUE;if($this->presenter
instanceof
NPresenter){try{$this->presenter->forward(":$this->errorPresenter:",array('exception'=>$e));}catch(NAbortException$foo){$request=$this->presenter->getLastCreatedRequest();}}else{$request=new
NPresenterRequest($this->errorPresenter,NPresenterRequest::FORWARD,array('exception'=>$e));}}else{if($e
instanceof
NBadRequestException){$code=$e->getCode();}else{$code=500;NDebugger::log($e,NDebugger::ERROR);}$messages=array(0=>array('Oops...','Your browser sent a request that this server could not understand or process.'),403=>array('Access Denied','You do not have permission to view this page. Please try contact the web site administrator if you believe you should be able to view this page.'),404=>array('Page Not Found','The page you requested could not be found. It is possible that the address is incorrect, or that the page no longer exists. Please use a search engine to find what you are looking for.'),405=>array('Method Not Allowed','The requested method is not allowed for the URL.'),410=>array('Page Not Found','The page you requested has been taken off the site. We apologize for the inconvenience.'),500=>array('Server Error','We\'re sorry! The server encountered an internal error and was unable to complete your request. Please try again later.'));$message=isset($messages[$code])?$messages[$code]:$messages[0];?>
<!DOCTYPE html>
<meta charset="utf-8">
<meta name=robots content=noindex>
<meta name=generator content="Nette Framework">
<style>body{color:#333;background:white;width:500px;margin:100px auto}h1{font:bold 47px/1.5 sans-serif;margin:.6em 0}p{font:21px/1.5 Georgia,serif;margin:1.5em 0}small{font-size:70%;color:gray}</style>

<title><?php echo$message[0]?></title>

<h1><?php echo$message[0]?></h1>

<p><?php echo$message[1]?></p>

<?php if($code):?><p><small>error <?php echo$code?></small></p><?php endif?>
<?php
break;}}}while(1);$this->onShutdown($this,isset($e)?$e:NULL);}function
getRequests(){return$this->requests;}function
getPresenter(){return$this->presenter;}function
getRouter(){return$this->router;}function
getPresenterFactory(){return$this->presenterFactory;}function
storeRequest($expiration='+ 10 minutes'){return$this->presenter->storeRequest($expiration);}function
restoreRequest($key){return$this->presenter->restoreRequest($key);}}class
NRoutingDebugger
extends
NObject
implements
IBarPanel{private$router;private$httpRequest;private$routers=array();private$request;static
function
initializePanel(NApplication$application){NDebugger::$blueScreen->addPanel(create_function('$e','extract($GLOBALS[0]['.array_push($GLOBALS[0],array('application'=>$application)).'-1], EXTR_REFS);
			return $e ? NULL : array(
				\'tab\' => \'Nette Application\',
				\'panel\' => \'<h3>Requests</h3>\' . NDebugHelpers::clickableDump($application->getRequests())
					. \'<h3>Presenter</h3>\' . NDebugHelpers::clickableDump($application->getPresenter())
			);
		'));}function
__construct(IRouter$router,IHttpRequest$httpRequest){$this->router=$router;$this->httpRequest=$httpRequest;}function
getTab(){$this->analyse($this->router);ob_start();?>
<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAJHSURBVDjLlZPNi81hFMc/z7137p1mTCFvNZfGSzLIWNjZKRvFRoqNhRCSYm8xS3+AxRRZ2JAFJWJHSQqTQkbEzYwIM+6Yid/znJfH4prLXShOnb6r8/nWOd8Tcs78bz0/f+KMu50y05nK/wy+uHDylbutqS5extvGcxaWqtoGDA8PZ3dnrs2srQc2Zko41UXLmLdyDW5OfvsUkUgbYGbU63UAQggdmvMzFmzZCgTi7CQmkZwdEaX0JwDgTnGbTCaE0G4zw80omhPI92lcEtkNkdgJCCHwJX7mZvNaB0A14SaYJlwTrpHsTkoFlV1nt2c3x5YYo1/vM9A/gKpxdfwyu/v3teCayKq4JEwT5EB2R6WgYmrs2bYbcUNNUVfEhIfFYy69uci+1fuRX84mkawFSxd/4nVWUopUVIykwlQxRTJBTIDA4Pp1jBZPuNW4wUAPmCqWIn29X1k4f5Ku8g9mpKCkakRLVEs1auVuauVuyqHMo8ejNCe+sWPVTkQKXCMmkeZUmUZjETF1tc6ooly+fgUVw9So1/tRN6YnZji46QghBFKKuAouERNhMlbAHZFE6e7pB+He8MMw+GGI4xtOMf1+lsl3TQ4NHf19BSlaO1DB9BfMHdX0O0iqSgiBbJkjm491hClJbA1LxCURgpPzXwAHhg63necAIi3XngXLcRU0fof8ETMljIyM5LGxMcbHxzvy/6fuXdWgt6+PWncv1e4euqo1ZmabvHs5+jn8yzufO7hiiZmuNpNBM13rbvVSpbrXJE7/BMkHtU9jFIC/AAAAAElFTkSuQmCC"
/><?php if(empty($this->request)):?>no route<?php else:echo
htmlSpecialChars($this->request->getPresenterName().':'.(isset($this->request->parameters[NPresenter::ACTION_KEY])?$this->request->parameters[NPresenter::ACTION_KEY]:NPresenter::DEFAULT_ACTION).(isset($this->request->parameters[NPresenter::SIGNAL_KEY])?" {$this->request->parameters[NPresenter::SIGNAL_KEY]}!":''));endif?>
<?php
return
ob_get_clean();}function
getPanel(){ob_start();?>
<style class="nette-debug">#nette-debug .nette-RoutingPanel table{font:9pt/1.5 Consolas,monospace}#nette-debug .nette-RoutingPanel .yes td{color:green}#nette-debug .nette-RoutingPanel .may td{color:#67F}#nette-debug .nette-RoutingPanel pre,#nette-debug .nette-RoutingPanel code{display:inline}#nette-debug .nette-RoutingPanel code .nette-collapsed{display:none}</style>

<div class="nette-RoutingPanel">
<h1>
<?php if(empty($this->request)):?>
	no route
<?php else:?>
	<?php echo
htmlSpecialChars($this->request->getPresenterName().':'.(isset($this->request->parameters[NPresenter::ACTION_KEY])?$this->request->parameters[NPresenter::ACTION_KEY]:NPresenter::DEFAULT_ACTION).(isset($this->request->parameters[NPresenter::SIGNAL_KEY])?" {$this->request->parameters[NPresenter::SIGNAL_KEY]}!":''))?>
<?php endif?>
</h1>

<?php if(!empty($this->request)):?>
	<?php $params=$this->request->getParameters()?>
	<?php if(empty($params)):?>
		<p>No parameters.</p>

	<?php else:?>
		<table>
		<thead>
		<tr>
			<th>Parameter</th>
			<th>Value</th>
		</tr>
		</thead>
		<tbody>
		<?php unset($params[NPresenter::ACTION_KEY],$params[NPresenter::SIGNAL_KEY])?>
		<?php foreach($params
as$key=>$value):?>
		<tr>
			<td><code><?php echo
htmlSpecialChars($key)?></code></td>
			<td><?php if(is_string($value)):?><code><?php echo
htmlSpecialChars($value)?></code><?php else:echo
NDebugger::dump($value,TRUE);endif?></td>
		</tr>
		<?php endforeach?>
		</tbody>
		</table>
	<?php endif?>
<?php endif?>

<h2>Routers</h2>

<?php if(empty($this->routers)):?>
	<p>No routers defined.</p>

<?php else:?>
	<div class="nette-inner">
	<table>
	<thead>
	<tr>
		<th>Matched?</th>
		<th>Class</th>
		<th>Mask</th>
		<th>Defaults</th>
		<th>Module</th>
		<th>Request</th>
	</tr>
	</thead>

	<tbody>
	<?php foreach($this->routers
as$router):?>
	<tr class="<?php echo$router['matched']?>">
		<td><?php echo$router['matched']?></td>

		<td><code title="<?php echo
htmlSpecialChars($router['class'])?>"><?php echo
preg_replace('#.+\\\\#','',htmlSpecialChars($router['class']))?></code></td>

		<td><code><strong><?php echo
htmlSpecialChars($router['mask'])?></strong></code></td>

		<td><code>
		<?php foreach($router['defaults']as$key=>$value):?>
			<?php echo
htmlSpecialChars($key),"&nbsp;=&nbsp;",is_string($value)?htmlSpecialChars($value):str_replace("\n</pre",'</pre',NDebugHelpers::clickableDump($value,TRUE))?><br />
		<?php endforeach?>
		</code></td>

		<td><code><?php echo
htmlSpecialChars($router['module'])?></code></td>

		<td><?php if($router['request']):?><code>
		<?php $params=$router['request']->getParameters();?>
		<strong><?php echo
htmlSpecialChars($router['request']->getPresenterName().':'.(isset($params[NPresenter::ACTION_KEY])?$params[NPresenter::ACTION_KEY]:NPresenter::DEFAULT_ACTION))?></strong><br />
		<?php unset($params[NPresenter::ACTION_KEY])?>
		<?php foreach($params
as$key=>$value):?>
			<?php echo
htmlSpecialChars($key),"&nbsp;=&nbsp;",is_string($value)?htmlSpecialChars($value):str_replace("\n</pre",'</pre',NDebugHelpers::clickableDump($value,TRUE))?><br />
		<?php endforeach?>
		</code><?php endif?></td>
	</tr>
	<?php endforeach?>
	</tbody>
	</table>
	</div>
<?php endif?>
</div>
<?php
return
ob_get_clean();}private
function
analyse($router,$module=''){if($router
instanceof
NRouteList){foreach($router
as$subRouter){$this->analyse($subRouter,$module.$router->getModule());}return;}$matched='no';$request=$router->match($this->httpRequest);if($request){$request->setPresenterName($module.$request->getPresenterName());$matched='may';if(empty($this->request)){$this->request=$request;$matched='yes';}}$this->routers[]=array('matched'=>$matched,'class'=>get_class($router),'defaults'=>$router
instanceof
NRoute||$router
instanceof
NSimpleRouter?$router->getDefaults():array(),'mask'=>$router
instanceof
NRoute?$router->getMask():NULL,'request'=>$request,'module'=>rtrim($module,':'));}}class
NAbortException
extends
Exception{}class
NApplicationException
extends
Exception{function
__construct($message='',$code=0,Exception$previous=NULL){if(PHP_VERSION_ID<50300){$this->previous=$previous;parent::__construct($message,$code);}else{parent::__construct($message,$code,$previous);}}}class
NInvalidPresenterException
extends
Exception{}class
NBadRequestException
extends
Exception{protected$defaultCode=404;function
__construct($message='',$code=0,Exception$previous=NULL){if($code<200||$code>504){$code=$this->defaultCode;}if(PHP_VERSION_ID<50300){$this->previous=$previous;parent::__construct($message,$code);}else{parent::__construct($message,$code,$previous);}}}class
NForbiddenRequestException
extends
NBadRequestException{protected$defaultCode=403;}class
Nette_MicroPresenter
extends
NObject
implements
IPresenter{private$context;private$request;function
__construct(NDIContainer$context){$this->context=$context;}function
getContext(){return$this->context;}function
run(NPresenterRequest$request){$this->request=$request;$httpRequest=$this->context->getByType('IHttpRequest');if(!$httpRequest->isAjax()&&($request->isMethod('get')||$request->isMethod('head'))){$refUrl=clone$httpRequest->getUrl();$url=$this->context->getService('router')->constructUrl($request,$refUrl->setPath($refUrl->getScriptPath()));if($url!==NULL&&!$httpRequest->getUrl()->isEqual($url)){return
new
NRedirectResponse($url,IHttpResponse::S301_MOVED_PERMANENTLY);}}$params=$request->getParameters();if(!isset($params['callback'])){throw
new
NBadRequestException('Parameter callback is missing.');}$params['presenter']=$this;$callback=new
NCallback($params['callback']);$response=$callback->invokeArgs(NPresenterComponentReflection::combineArgs($callback->toReflection(),$params));if(is_string($response)){$response=array($response,array());}if(is_array($response)){if($response[0]instanceof
SplFileInfo){$response=$this->createTemplate('NFileTemplate')->setParameters($response[1])->setFile($response[0]);}else{$response=$this->createTemplate('NTemplate')->setParameters($response[1])->setSource($response[0]);}}if($response
instanceof
ITemplate){return
new
NTextResponse($response);}else{return$response;}}function
createTemplate($class=NULL,$latteFactory=NULL){$template=$class?new$class:new
NFileTemplate;$template->setParameters($this->request->getParameters());$template->presenter=$this;$template->context=$context=$this->context;$url=$context->getByType('IHttpRequest')->getUrl();$template->baseUrl=rtrim($url->getBaseUrl(),'/');$template->basePath=rtrim($url->getBasePath(),'/');$template->registerHelperLoader('NTemplateHelpers::loader');$template->setCacheStorage($context->getService('nette.templateCacheStorage'));$template->onPrepareFilters[]=create_function('$template','extract($GLOBALS[0]['.array_push($GLOBALS[0],array('latteFactory'=>$latteFactory)).'-1], EXTR_REFS);
			$template->registerFilter($latteFactory ? $latteFactory() : new NLatteFilter);
		');return$template;}function
redirectUrl($url,$code=IHttpResponse::S302_FOUND){return
new
NRedirectResponse($url,$code);}function
error($message=NULL,$code=IHttpResponse::S404_NOT_FOUND){throw
new
NBadRequestException($message,$code);}function
getRequest(){return$this->request;}}class
NPresenterFactory
extends
NObject
implements
IPresenterFactory{public$caseSensitive=FALSE;private$baseDir;private$cache=array();private$container;function
__construct($baseDir,NDIContainer$container){$this->baseDir=$baseDir;$this->container=$container;}function
createPresenter($name){$presenter=$this->container->createInstance($this->getPresenterClass($name));if(method_exists($presenter,'setContext')){$this->container->callMethod(array($presenter,'setContext'));}foreach(array_reverse(get_class_methods($presenter))as$method){if(substr($method,0,6)==='inject'){$this->container->callMethod(array($presenter,$method));}}if($presenter
instanceof
NPresenter&&$presenter->invalidLinkMode===NULL){$presenter->invalidLinkMode=$this->container->parameters['debugMode']?NPresenter::INVALID_LINK_WARNING:NPresenter::INVALID_LINK_SILENT;}return$presenter;}function
getPresenterClass(&$name){if(isset($this->cache[$name])){list($class,$name)=$this->cache[$name];return$class;}if(!is_string($name)||!NStrings::match($name,'#^[a-zA-Z\x7f-\xff][a-zA-Z0-9\x7f-\xff:]*\z#')){throw
new
NInvalidPresenterException("Presenter name must be alphanumeric string, '$name' is invalid.");}$class=$this->formatPresenterClass($name);if(!class_exists($class)){$file=$this->formatPresenterFile($name);if(is_file($file)&&is_readable($file)){NLimitedScope::load($file,TRUE);}if(!class_exists($class)){throw
new
NInvalidPresenterException("Cannot load presenter '$name', class '$class' was not found in '$file'.");}}$reflection=new
NClassReflection($class);$class=$reflection->getName();if(!$reflection->implementsInterface('IPresenter')){throw
new
NInvalidPresenterException("Cannot load presenter '$name', class '$class' is not IPresenter implementor.");}if($reflection->isAbstract()){throw
new
NInvalidPresenterException("Cannot load presenter '$name', class '$class' is abstract.");}$realName=$this->unformatPresenterClass($class);if($name!==$realName){if($this->caseSensitive){throw
new
NInvalidPresenterException("Cannot load presenter '$name', case mismatch. Real name is '$realName'.");}else{$this->cache[$name]=array($class,$realName);$name=$realName;}}else{$this->cache[$name]=array($class,$realName);}return$class;}function
formatPresenterClass($presenter){return
strtr($presenter,':','_').'Presenter';return
str_replace(':','Module\\',$presenter).'Presenter';}function
unformatPresenterClass($class){return
strtr(substr($class,0,-9),'_',':');return
str_replace('Module\\',':',substr($class,0,-9));}function
formatPresenterFile($presenter){$path='/'.str_replace(':','Module/',$presenter);return$this->baseDir.substr_replace($path,'/presenters',strrpos($path,'/'),0).'Presenter.php';}}abstract
class
NFreezableObject
extends
NObject
implements
IFreezable{private$frozen=FALSE;function
freeze(){$this->frozen=TRUE;}function
isFrozen(){return$this->frozen;}function
__clone(){$this->frozen=FALSE;}protected
function
updating(){if($this->frozen){$class=get_class($this);throw
new
InvalidStateException("Cannot modify a frozen object $class.");}}}class
NPresenterRequest
extends
NFreezableObject{const
FORWARD='FORWARD';const
SECURED='secured';const
RESTORED='restored';private$method;private$flags=array();private$name;private$params;private$post;private$files;function
__construct($name,$method,array$params,array$post=array(),array$files=array(),array$flags=array()){$this->name=$name;$this->method=$method;$this->params=$params;$this->post=$post;$this->files=$files;$this->flags=$flags;}function
setPresenterName($name){$this->updating();$this->name=$name;return$this;}function
getPresenterName(){return$this->name;}function
setParameters(array$params){$this->updating();$this->params=$params;return$this;}function
getParameters(){return$this->params;}function
setParams(array$params){trigger_error(__METHOD__.'() is deprecated; use setParameters() instead.',E_USER_WARNING);return$this->setParameters($params);}function
getParams(){trigger_error(__METHOD__.'() is deprecated; use getParameters() instead.',E_USER_WARNING);return$this->getParameters();}function
setPost(array$params){$this->updating();$this->post=$params;return$this;}function
getPost(){return$this->post;}function
setFiles(array$files){$this->updating();$this->files=$files;return$this;}function
getFiles(){return$this->files;}function
setMethod($method){$this->method=$method;return$this;}function
getMethod(){return$this->method;}function
isMethod($method){return
strcasecmp($this->method,$method)===0;}function
isPost(){return
strcasecmp($this->method,'post')===0;}function
setFlag($flag,$value=TRUE){$this->updating();$this->flags[$flag]=(bool)$value;return$this;}function
hasFlag($flag){return!empty($this->flags[$flag]);}}class
NFileResponse
extends
NObject
implements
IPresenterResponse{private$file;private$contentType;private$name;public$resuming=TRUE;function
__construct($file,$name=NULL,$contentType=NULL){if(!is_file($file)){throw
new
NBadRequestException("File '$file' doesn't exist.");}$this->file=$file;$this->name=$name?$name:basename($file);$this->contentType=$contentType?$contentType:'application/octet-stream';}function
getFile(){return$this->file;}function
getName(){return$this->name;}function
getContentType(){return$this->contentType;}function
send(IHttpRequest$httpRequest,IHttpResponse$httpResponse){$httpResponse->setContentType($this->contentType);$httpResponse->setHeader('Content-Disposition','attachment; filename="'.$this->name.'"');$filesize=$length=filesize($this->file);$handle=fopen($this->file,'r');if($this->resuming){$httpResponse->setHeader('Accept-Ranges','bytes');if(preg_match('#^bytes=(\d*)-(\d*)\z#',$httpRequest->getHeader('Range'),$matches)){list(,$start,$end)=$matches;if($start===''){$start=max(0,$filesize-$end);$end=$filesize-1;}elseif($end===''||$end>$filesize-1){$end=$filesize-1;}if($end<$start){$httpResponse->setCode(416);return;}$httpResponse->setCode(206);$httpResponse->setHeader('Content-Range','bytes '.$start.'-'.$end.'/'.$filesize);$length=$end-$start+1;fseek($handle,$start);}else{$httpResponse->setHeader('Content-Range','bytes 0-'.($filesize-1).'/'.$filesize);}}$httpResponse->setHeader('Content-Length',$length);while(!feof($handle)&&$length>0){echo$s=fread($handle,min(4e6,$length));$length-=strlen($s);}fclose($handle);}}class
NForwardResponse
extends
NObject
implements
IPresenterResponse{private$request;function
__construct(NPresenterRequest$request){$this->request=$request;}function
getRequest(){return$this->request;}function
send(IHttpRequest$httpRequest,IHttpResponse$httpResponse){}}class
NJsonResponse
extends
NObject
implements
IPresenterResponse{private$payload;private$contentType;function
__construct($payload,$contentType=NULL){if(!is_array($payload)&&!is_object($payload)){throw
new
InvalidArgumentException(sprintf('Payload must be array or object class, %s given.',gettype($payload)));}$this->payload=$payload;$this->contentType=$contentType?$contentType:'application/json';}function
getPayload(){return$this->payload;}function
getContentType(){return$this->contentType;}function
send(IHttpRequest$httpRequest,IHttpResponse$httpResponse){$httpResponse->setContentType($this->contentType);$httpResponse->setExpiration(FALSE);echo
NJson::encode($this->payload);}}class
NRedirectResponse
extends
NObject
implements
IPresenterResponse{private$url;private$code;function
__construct($url,$code=IHttpResponse::S302_FOUND){$this->url=(string)$url;$this->code=(int)$code;}function
getUrl(){return$this->url;}function
getCode(){return$this->code;}function
send(IHttpRequest$httpRequest,IHttpResponse$httpResponse){$httpResponse->redirect($this->url,$this->code);}}class
NTextResponse
extends
NObject
implements
IPresenterResponse{private$source;function
__construct($source){$this->source=$source;}function
getSource(){return$this->source;}function
send(IHttpRequest$httpRequest,IHttpResponse$httpResponse){if($this->source
instanceof
ITemplate){$this->source->render();}else{echo$this->source;}}}class
NCliRouter
extends
NObject
implements
IRouter{const
PRESENTER_KEY='action';private$defaults;function
__construct($defaults=array()){$this->defaults=$defaults;}function
match(IHttpRequest$httpRequest){if(empty($_SERVER['argv'])||!is_array($_SERVER['argv'])){return
NULL;}$names=array(self::PRESENTER_KEY);$params=$this->defaults;$args=$_SERVER['argv'];array_shift($args);$args[]='--';foreach($args
as$arg){$opt=preg_replace('#/|-+#A','',$arg);if($opt===$arg){if(isset($flag)||$flag=array_shift($names)){$params[$flag]=$arg;}else{$params[]=$arg;}$flag=NULL;continue;}if(isset($flag)){$params[$flag]=TRUE;$flag=NULL;}if($opt!==''){$pair=explode('=',$opt,2);if(isset($pair[1])){$params[$pair[0]]=$pair[1];}else{$flag=$pair[0];}}}if(!isset($params[self::PRESENTER_KEY])){throw
new
InvalidStateException('Missing presenter & action in route definition.');}$presenter=$params[self::PRESENTER_KEY];if($a=strrpos($presenter,':')){$params[self::PRESENTER_KEY]=substr($presenter,$a+1);$presenter=substr($presenter,0,$a);}return
new
NPresenterRequest($presenter,'CLI',$params);}function
constructUrl(NPresenterRequest$appRequest,NUrl$refUrl){return
NULL;}function
getDefaults(){return$this->defaults;}}class
NRoute
extends
NObject
implements
IRouter{const
PRESENTER_KEY='presenter';const
MODULE_KEY='module';const
CASE_SENSITIVE=256;const
HOST=1,PATH=2,RELATIVE=3;const
VALUE='value';const
PATTERN='pattern';const
FILTER_IN='filterIn';const
FILTER_OUT='filterOut';const
FILTER_TABLE='filterTable';const
FILTER_STRICT='filterStrict';const
OPTIONAL=0,PATH_OPTIONAL=1,CONSTANT=2;public
static$defaultFlags=0;public
static$styles=array('#'=>array(self::PATTERN=>'[^/]+',self::FILTER_IN=>'rawurldecode',self::FILTER_OUT=>array(__CLASS__,'param2path')),'?#'=>array(),'module'=>array(self::PATTERN=>'[a-z][a-z0-9.-]*',self::FILTER_IN=>array(__CLASS__,'path2presenter'),self::FILTER_OUT=>array(__CLASS__,'presenter2path')),'presenter'=>array(self::PATTERN=>'[a-z][a-z0-9.-]*',self::FILTER_IN=>array(__CLASS__,'path2presenter'),self::FILTER_OUT=>array(__CLASS__,'presenter2path')),'action'=>array(self::PATTERN=>'[a-z][a-z0-9-]*',self::FILTER_IN=>array(__CLASS__,'path2action'),self::FILTER_OUT=>array(__CLASS__,'action2path')),'?module'=>array(),'?presenter'=>array(),'?action'=>array());private$mask;private$sequence;private$re;private$metadata=array();private$xlat;private$type;private$flags;function
__construct($mask,$metadata=array(),$flags=0){if(is_string($metadata)){$a=strrpos($metadata,':');if(!$a){throw
new
InvalidArgumentException("Second argument must be array or string in format Presenter:action, '$metadata' given.");}$metadata=array(self::PRESENTER_KEY=>substr($metadata,0,$a),'action'=>$a===strlen($metadata)-1?NULL:substr($metadata,$a+1));}elseif($metadata
instanceof
Closure||$metadata
instanceof
NCallback){$metadata=array(self::PRESENTER_KEY=>'Nette:Micro','callback'=>$metadata);}$this->flags=$flags|self::$defaultFlags;$this->setMask($mask,$metadata);}function
match(IHttpRequest$httpRequest){$url=$httpRequest->getUrl();if($this->type===self::HOST){$path='//'.$url->getHost().$url->getPath();}elseif($this->type===self::RELATIVE){$basePath=$url->getBasePath();if(strncmp($url->getPath(),$basePath,strlen($basePath))!==0){return
NULL;}$path=(string)substr($url->getPath(),strlen($basePath));}else{$path=$url->getPath();}if($path!==''){$path=rtrim($path,'/').'/';}if(!$matches=NStrings::match($path,$this->re)){return
NULL;}$params=array();foreach($matches
as$k=>$v){if(is_string($k)&&$v!==''){$params[str_replace('___','-',$k)]=$v;}}foreach($this->metadata
as$name=>$meta){if(isset($params[$name])){}elseif(isset($meta['fixity'])&&$meta['fixity']!==self::OPTIONAL){$params[$name]=NULL;}}if($this->xlat){$params+=self::renameKeys($httpRequest->getQuery(),array_flip($this->xlat));}else{$params+=$httpRequest->getQuery();}foreach($this->metadata
as$name=>$meta){if(isset($params[$name])){if(!is_scalar($params[$name])){}elseif(isset($meta[self::FILTER_TABLE][$params[$name]])){$params[$name]=$meta[self::FILTER_TABLE][$params[$name]];}elseif(isset($meta[self::FILTER_TABLE])&&!empty($meta[self::FILTER_STRICT])){return
NULL;}elseif(isset($meta[self::FILTER_IN])){$params[$name]=call_user_func($meta[self::FILTER_IN],(string)$params[$name]);if($params[$name]===NULL&&!isset($meta['fixity'])){return
NULL;}}}elseif(isset($meta['fixity'])){$params[$name]=$meta[self::VALUE];}}if(!isset($params[self::PRESENTER_KEY])){throw
new
InvalidStateException('Missing presenter in route definition.');}elseif(!is_string($params[self::PRESENTER_KEY])){return
NULL;}if(isset($this->metadata[self::MODULE_KEY])){if(!isset($params[self::MODULE_KEY])){throw
new
InvalidStateException('Missing module in route definition.');}$presenter=$params[self::MODULE_KEY].':'.$params[self::PRESENTER_KEY];unset($params[self::MODULE_KEY],$params[self::PRESENTER_KEY]);}else{$presenter=$params[self::PRESENTER_KEY];unset($params[self::PRESENTER_KEY]);}return
new
NPresenterRequest($presenter,$httpRequest->getMethod(),$params,$httpRequest->getPost(),$httpRequest->getFiles(),array(NPresenterRequest::SECURED=>$httpRequest->isSecured()));}function
constructUrl(NPresenterRequest$appRequest,NUrl$refUrl){if($this->flags&self::ONE_WAY){return
NULL;}$params=$appRequest->getParameters();$metadata=$this->metadata;$presenter=$appRequest->getPresenterName();$params[self::PRESENTER_KEY]=$presenter;if(isset($metadata[self::MODULE_KEY])){$module=$metadata[self::MODULE_KEY];if(isset($module['fixity'])&&strncasecmp($presenter,$module[self::VALUE].':',strlen($module[self::VALUE])+1)===0){$a=strlen($module[self::VALUE]);}else{$a=strrpos($presenter,':');}if($a===FALSE){$params[self::MODULE_KEY]='';}else{$params[self::MODULE_KEY]=substr($presenter,0,$a);$params[self::PRESENTER_KEY]=substr($presenter,$a+1);}}foreach($metadata
as$name=>$meta){if(!isset($params[$name])){continue;}if(isset($meta['fixity'])){if($params[$name]===FALSE){$params[$name]='0';}if(is_scalar($params[$name])?strcasecmp($params[$name],$meta[self::VALUE])===0:$params[$name]===$meta[self::VALUE]){unset($params[$name]);continue;}elseif($meta['fixity']===self::CONSTANT){return
NULL;}}if(is_scalar($params[$name])&&isset($meta['filterTable2'][$params[$name]])){$params[$name]=$meta['filterTable2'][$params[$name]];}elseif(isset($meta['filterTable2'])&&!empty($meta[self::FILTER_STRICT])){return
NULL;}elseif(isset($meta[self::FILTER_OUT])){$params[$name]=call_user_func($meta[self::FILTER_OUT],$params[$name]);}if(isset($meta[self::PATTERN])&&!preg_match($meta[self::PATTERN],rawurldecode($params[$name]))){return
NULL;}}$sequence=$this->sequence;$brackets=array();$required=NULL;$url='';$i=count($sequence)-1;do{$url=$sequence[$i].$url;if($i===0){break;}$i--;$name=$sequence[$i];$i--;if($name===']'){$brackets[]=$url;}elseif($name[0]==='['){$tmp=array_pop($brackets);if($required<count($brackets)+1){if($name!=='[!'){$url=$tmp;}}else{$required=count($brackets);}}elseif($name[0]==='?'){continue;}elseif(isset($params[$name])&&$params[$name]!=''){$required=count($brackets);$url=$params[$name].$url;unset($params[$name]);}elseif(isset($metadata[$name]['fixity'])){if($required===NULL&&!$brackets){$url='';}else{$url=$metadata[$name]['defOut'].$url;}}else{return
NULL;}}while(TRUE);if($this->xlat){$params=self::renameKeys($params,$this->xlat);}$sep=ini_get('arg_separator.input');$query=http_build_query($params,'',$sep?$sep[0]:'&');if($query!=''){$url.='?'.$query;}if($this->type===self::RELATIVE){$url='//'.$refUrl->getAuthority().$refUrl->getBasePath().$url;}elseif($this->type===self::PATH){$url='//'.$refUrl->getAuthority().$url;}if(strpos($url,'//',2)!==FALSE){return
NULL;}$url=($this->flags&self::SECURED?'https:':'http:').$url;return$url;}private
function
setMask($mask,array$metadata){$this->mask=$mask;if(substr($mask,0,2)==='//'){$this->type=self::HOST;}elseif(substr($mask,0,1)==='/'){$this->type=self::PATH;}else{$this->type=self::RELATIVE;}foreach($metadata
as$name=>$meta){if(!is_array($meta)){$metadata[$name]=array(self::VALUE=>$meta,'fixity'=>self::CONSTANT);}elseif(array_key_exists(self::VALUE,$meta)){$metadata[$name]['fixity']=self::CONSTANT;}}$parts=NStrings::split($mask,'/<([^>#= ]+)(=[^># ]*)? *([^>#]*)(#?[^>\[\]]*)>|(\[!?|\]|\s*\?.*)/');$this->xlat=array();$i=count($parts)-1;if(isset($parts[$i-1])&&substr(ltrim($parts[$i-1]),0,1)==='?'){$matches=NStrings::matchAll($parts[$i-1],'/(?:([a-zA-Z0-9_.-]+)=)?<([^># ]+) *([^>#]*)(#?[^>]*)>/');foreach($matches
as$match){list(,$param,$name,$pattern,$class)=$match;if($class!==''){if(!isset(self::$styles[$class])){throw
new
InvalidStateException("Parameter '$name' has '$class' flag, but Route::\$styles['$class'] is not set.");}$meta=self::$styles[$class];}elseif(isset(self::$styles['?'.$name])){$meta=self::$styles['?'.$name];}else{$meta=self::$styles['?#'];}if(isset($metadata[$name])){$meta=$metadata[$name]+$meta;}if(array_key_exists(self::VALUE,$meta)){$meta['fixity']=self::OPTIONAL;}unset($meta['pattern']);$meta['filterTable2']=empty($meta[self::FILTER_TABLE])?NULL:array_flip($meta[self::FILTER_TABLE]);$metadata[$name]=$meta;if($param!==''){$this->xlat[$name]=$param;}}$i-=6;}$brackets=0;$re='';$sequence=array();$autoOptional=TRUE;do{array_unshift($sequence,$parts[$i]);$re=preg_quote($parts[$i],'#').$re;if($i===0){break;}$i--;$part=$parts[$i];if($part==='['||$part===']'||$part==='[!'){$brackets+=$part[0]==='['?-1:1;if($brackets<0){throw
new
InvalidArgumentException("Unexpected '$part' in mask '$mask'.");}array_unshift($sequence,$part);$re=($part[0]==='['?'(?:':')?').$re;$i-=5;continue;}$class=$parts[$i];$i--;$pattern=trim($parts[$i]);$i--;$default=$parts[$i];$i--;$name=$parts[$i];$i--;array_unshift($sequence,$name);if($name[0]==='?'){$re='(?:'.preg_quote(substr($name,1),'#').'|'.$pattern.')'.$re;$sequence[1]=substr($name,1).$sequence[1];continue;}if(preg_match('#[^a-z0-9_-]#i',$name)){throw
new
InvalidArgumentException("Parameter name must be alphanumeric string due to limitations of PCRE, '$name' given.");}if($class!==''){if(!isset(self::$styles[$class])){throw
new
InvalidStateException("Parameter '$name' has '$class' flag, but Route::\$styles['$class'] is not set.");}$meta=self::$styles[$class];}elseif(isset(self::$styles[$name])){$meta=self::$styles[$name];}else{$meta=self::$styles['#'];}if(isset($metadata[$name])){$meta=$metadata[$name]+$meta;}if($pattern==''&&isset($meta[self::PATTERN])){$pattern=$meta[self::PATTERN];}if($default!==''){$meta[self::VALUE]=(string)substr($default,1);$meta['fixity']=self::PATH_OPTIONAL;}$meta['filterTable2']=empty($meta[self::FILTER_TABLE])?NULL:array_flip($meta[self::FILTER_TABLE]);if(array_key_exists(self::VALUE,$meta)){if(isset($meta['filterTable2'][$meta[self::VALUE]])){$meta['defOut']=$meta['filterTable2'][$meta[self::VALUE]];}elseif(isset($meta[self::FILTER_OUT])){$meta['defOut']=call_user_func($meta[self::FILTER_OUT],$meta[self::VALUE]);}else{$meta['defOut']=$meta[self::VALUE];}}$meta[self::PATTERN]="#(?:$pattern)\\z#A".($this->flags&self::CASE_SENSITIVE?'':'iu');$re='(?P<'.str_replace('-','___',$name).'>(?U)'.$pattern.')'.$re;if($brackets){if(!isset($meta[self::VALUE])){$meta[self::VALUE]=$meta['defOut']=NULL;}$meta['fixity']=self::PATH_OPTIONAL;}elseif(!$autoOptional){unset($meta['fixity']);}elseif(isset($meta['fixity'])){$re='(?:'.$re.')?';$meta['fixity']=self::PATH_OPTIONAL;}else{$autoOptional=FALSE;}$metadata[$name]=$meta;}while(TRUE);if($brackets){throw
new
InvalidArgumentException("Missing closing ']' in mask '$mask'.");}$this->re='#'.$re.'/?\z#A'.($this->flags&self::CASE_SENSITIVE?'':'iu');$this->metadata=$metadata;$this->sequence=$sequence;}function
getMask(){return$this->mask;}function
getDefaults(){$defaults=array();foreach($this->metadata
as$name=>$meta){if(isset($meta['fixity'])){$defaults[$name]=$meta[self::VALUE];}}return$defaults;}function
getFlags(){return$this->flags;}function
getTargetPresenter(){if($this->flags&self::ONE_WAY){return
FALSE;}$m=$this->metadata;$module='';if(isset($m[self::MODULE_KEY])){if(isset($m[self::MODULE_KEY]['fixity'])&&$m[self::MODULE_KEY]['fixity']===self::CONSTANT){$module=$m[self::MODULE_KEY][self::VALUE].':';}else{return
NULL;}}if(isset($m[self::PRESENTER_KEY]['fixity'])&&$m[self::PRESENTER_KEY]['fixity']===self::CONSTANT){return$module.$m[self::PRESENTER_KEY][self::VALUE];}return
NULL;}private
static
function
renameKeys($arr,$xlat){if(empty($xlat)){return$arr;}$res=array();$occupied=array_flip($xlat);foreach($arr
as$k=>$v){if(isset($xlat[$k])){$res[$xlat[$k]]=$v;}elseif(!isset($occupied[$k])){$res[$k]=$v;}}return$res;}private
static
function
action2path($s){$s=preg_replace('#(.)(?=[A-Z])#','$1-',$s);$s=strtolower($s);$s=rawurlencode($s);return$s;}private
static
function
path2action($s){$s=strtolower($s);$s=preg_replace('#-(?=[a-z])#',' ',$s);$s=substr(ucwords('x'.$s),1);$s=str_replace(' ','',$s);return$s;}private
static
function
presenter2path($s){$s=strtr($s,':','.');$s=preg_replace('#([^.])(?=[A-Z])#','$1-',$s);$s=strtolower($s);$s=rawurlencode($s);return$s;}private
static
function
path2presenter($s){$s=strtolower($s);$s=preg_replace('#([.-])(?=[a-z])#','$1 ',$s);$s=ucwords($s);$s=str_replace('. ',':',$s);$s=str_replace('- ','',$s);return$s;}private
static
function
param2path($s){return
str_replace('%2F','/',rawurlencode($s));}static
function
addStyle($style,$parent='#'){if(isset(self::$styles[$style])){throw
new
InvalidArgumentException("Style '$style' already exists.");}if($parent!==NULL){if(!isset(self::$styles[$parent])){throw
new
InvalidArgumentException("Parent style '$parent' doesn't exist.");}self::$styles[$style]=self::$styles[$parent];}else{self::$styles[$style]=array();}}static
function
setStyleProperty($style,$key,$value){if(!isset(self::$styles[$style])){throw
new
InvalidArgumentException("Style '$style' doesn't exist.");}self::$styles[$style][$key]=$value;}}class
NArrayList
extends
NObject
implements
ArrayAccess,Countable,IteratorAggregate{private$list=array();function
getIterator(){return
new
ArrayIterator($this->list);}function
count(){return
count($this->list);}function
offsetSet($index,$value){if($index===NULL){$this->list[]=$value;}elseif($index<0||$index>=count($this->list)){throw
new
OutOfRangeException('Offset invalid or out of range');}else{$this->list[(int)$index]=$value;}}function
offsetGet($index){if($index<0||$index>=count($this->list)){throw
new
OutOfRangeException('Offset invalid or out of range');}return$this->list[(int)$index];}function
offsetExists($index){return$index>=0&&$index<count($this->list);}function
offsetUnset($index){if($index<0||$index>=count($this->list)){throw
new
OutOfRangeException('Offset invalid or out of range');}array_splice($this->list,(int)$index,1);}}class
NRouteList
extends
NArrayList
implements
IRouter{private$cachedRoutes;private$module;function
__construct($module=NULL){$this->module=$module?$module.':':'';}function
match(IHttpRequest$httpRequest){foreach($this
as$route){$appRequest=$route->match($httpRequest);if($appRequest!==NULL){$appRequest->setPresenterName($this->module.$appRequest->getPresenterName());return$appRequest;}}return
NULL;}function
constructUrl(NPresenterRequest$appRequest,NUrl$refUrl){if($this->cachedRoutes===NULL){$routes=array();$routes['*']=array();foreach($this
as$route){$presenter=$route
instanceof
NRoute?$route->getTargetPresenter():NULL;if($presenter===FALSE){continue;}if(is_string($presenter)){$presenter=strtolower($presenter);if(!isset($routes[$presenter])){$routes[$presenter]=$routes['*'];}$routes[$presenter][]=$route;}else{foreach($routes
as$id=>$foo){$routes[$id][]=$route;}}}$this->cachedRoutes=$routes;}if($this->module){if(strncasecmp($tmp=$appRequest->getPresenterName(),$this->module,strlen($this->module))===0){$appRequest=clone$appRequest;$appRequest->setPresenterName(substr($tmp,strlen($this->module)));}else{return
NULL;}}$presenter=strtolower($appRequest->getPresenterName());if(!isset($this->cachedRoutes[$presenter])){$presenter='*';}foreach($this->cachedRoutes[$presenter]as$route){$url=$route->constructUrl($appRequest,$refUrl);if($url!==NULL){return$url;}}return
NULL;}function
offsetSet($index,$route){if(!$route
instanceof
IRouter){throw
new
InvalidArgumentException('Argument must be IRouter descendant.');}parent::offsetSet($index,$route);}function
getModule(){return$this->module;}}class
NSimpleRouter
extends
NObject
implements
IRouter{const
PRESENTER_KEY='presenter';const
MODULE_KEY='module';private$module='';private$defaults;private$flags;function
__construct($defaults=array(),$flags=0){if(is_string($defaults)){$a=strrpos($defaults,':');if(!$a){throw
new
InvalidArgumentException("Argument must be array or string in format Presenter:action, '$defaults' given.");}$defaults=array(self::PRESENTER_KEY=>substr($defaults,0,$a),'action'=>$a===strlen($defaults)-1?NPresenter::DEFAULT_ACTION:substr($defaults,$a+1));}if(isset($defaults[self::MODULE_KEY])){$this->module=$defaults[self::MODULE_KEY].':';unset($defaults[self::MODULE_KEY]);}$this->defaults=$defaults;$this->flags=$flags;}function
match(IHttpRequest$httpRequest){if($httpRequest->getUrl()->getPathInfo()!==''){return
NULL;}$params=$httpRequest->getQuery();$params+=$this->defaults;if(!isset($params[self::PRESENTER_KEY])||!is_string($params[self::PRESENTER_KEY])){return
NULL;}$presenter=$this->module.$params[self::PRESENTER_KEY];unset($params[self::PRESENTER_KEY]);return
new
NPresenterRequest($presenter,$httpRequest->getMethod(),$params,$httpRequest->getPost(),$httpRequest->getFiles(),array(NPresenterRequest::SECURED=>$httpRequest->isSecured()));}function
constructUrl(NPresenterRequest$appRequest,NUrl$refUrl){if($this->flags&self::ONE_WAY){return
NULL;}$params=$appRequest->getParameters();$presenter=$appRequest->getPresenterName();if(strncasecmp($presenter,$this->module,strlen($this->module))===0){$params[self::PRESENTER_KEY]=substr($presenter,strlen($this->module));}else{return
NULL;}foreach($this->defaults
as$key=>$value){if(isset($params[$key])&&$params[$key]==$value){unset($params[$key]);}}$url=($this->flags&self::SECURED?'https://':'http://').$refUrl->getAuthority().$refUrl->getPath();$sep=ini_get('arg_separator.input');$query=http_build_query($params,'',$sep?$sep[0]:'&');if($query!=''){$url.='?'.$query;}return$url;}function
getDefaults(){return$this->defaults;}function
getFlags(){return$this->flags;}}class
NBadSignalException
extends
NBadRequestException{protected$defaultCode=403;}abstract
class
NComponent
extends
NObject
implements
IComponent{private$parent;private$name;private$monitors=array();function
__construct(IComponentContainer$parent=NULL,$name=NULL){if($parent!==NULL){$parent->addComponent($this,$name);}elseif(is_string($name)){$this->name=$name;}}function
lookup($type,$need=TRUE){if(!isset($this->monitors[$type])){$obj=$this->parent;$path=self::NAME_SEPARATOR.$this->name;$depth=1;while($obj!==NULL){if($obj
instanceof$type){break;}$path=self::NAME_SEPARATOR.$obj->getName().$path;$depth++;$obj=$obj->getParent();if($obj===$this){$obj=NULL;}}if($obj){$this->monitors[$type]=array($obj,$depth,substr($path,1),FALSE);}else{$this->monitors[$type]=array(NULL,NULL,NULL,FALSE);}}if($need&&$this->monitors[$type][0]===NULL){throw
new
InvalidStateException("Component '$this->name' is not attached to '$type'.");}return$this->monitors[$type][0];}function
lookupPath($type,$need=TRUE){$this->lookup($type,$need);return$this->monitors[$type][2];}function
monitor($type){if(empty($this->monitors[$type][3])){if($obj=$this->lookup($type,FALSE)){$this->attached($obj);}$this->monitors[$type][3]=TRUE;}}function
unmonitor($type){unset($this->monitors[$type]);}protected
function
attached($obj){}protected
function
detached($obj){}function
getName(){return$this->name;}function
getParent(){return$this->parent;}function
setParent(IComponentContainer$parent=NULL,$name=NULL){if($parent===NULL&&$this->parent===NULL&&$name!==NULL){$this->name=$name;return$this;}elseif($parent===$this->parent&&$name===NULL){return$this;}if($this->parent!==NULL&&$parent!==NULL){throw
new
InvalidStateException("Component '$this->name' already has a parent.");}if($parent===NULL){$this->refreshMonitors(0);$this->parent=NULL;}else{$this->validateParent($parent);$this->parent=$parent;if($name!==NULL){$this->name=$name;}$tmp=array();$this->refreshMonitors(0,$tmp);}return$this;}protected
function
validateParent(IComponentContainer$parent){}private
function
refreshMonitors($depth,&$missing=NULL,&$listeners=array()){if($this
instanceof
IComponentContainer){foreach($this->getComponents()as$component){if($component
instanceof
NComponent){$component->refreshMonitors($depth+1,$missing,$listeners);}}}if($missing===NULL){foreach($this->monitors
as$type=>$rec){if(isset($rec[1])&&$rec[1]>$depth){if($rec[3]){$this->monitors[$type]=array(NULL,NULL,NULL,TRUE);$listeners[]=array($this,$rec[0]);}else{unset($this->monitors[$type]);}}}}else{foreach($this->monitors
as$type=>$rec){if(isset($rec[0])){continue;}elseif(!$rec[3]){unset($this->monitors[$type]);}elseif(isset($missing[$type])){$this->monitors[$type]=array(NULL,NULL,NULL,TRUE);}else{$this->monitors[$type]=NULL;if($obj=$this->lookup($type,FALSE)){$listeners[]=array($this,$obj);}else{$missing[$type]=TRUE;}$this->monitors[$type][3]=TRUE;}}}if($depth===0){$method=$missing===NULL?'detached':'attached';foreach($listeners
as$item){$item[0]->$method($item[1]);}}}function
__clone(){if($this->parent===NULL){return;}elseif($this->parent
instanceof
NComponentContainer){$this->parent=$this->parent->_isCloning();if($this->parent===NULL){$this->refreshMonitors(0);}}else{$this->parent=NULL;$this->refreshMonitors(0);}}function
__sleep(){throw
new
NotImplementedException('Object serialization is not supported by class '.get_class($this));}function
__wakeup(){throw
new
NotImplementedException('Object unserialization is not supported by class '.get_class($this));}}class
NComponentContainer
extends
NComponent
implements
IComponentContainer{private$components=array();private$cloning;function
addComponent(IComponent$component,$name,$insertBefore=NULL){if($name===NULL){$name=$component->getName();}if(is_int($name)){$name=(string)$name;}elseif(!is_string($name)){throw
new
InvalidArgumentException(sprintf('Component name must be integer or string, %s given.',gettype($name)));}elseif(!preg_match('#^[a-zA-Z0-9_]+\z#',$name)){throw
new
InvalidArgumentException("Component name must be non-empty alphanumeric string, '$name' given.");}if(isset($this->components[$name])){throw
new
InvalidStateException("Component with name '$name' already exists.");}$obj=$this;do{if($obj===$component){throw
new
InvalidStateException("Circular reference detected while adding component '$name'.");}$obj=$obj->getParent();}while($obj!==NULL);$this->validateChildComponent($component);try{if(isset($this->components[$insertBefore])){$tmp=array();foreach($this->components
as$k=>$v){if($k===$insertBefore){$tmp[$name]=$component;}$tmp[$k]=$v;}$this->components=$tmp;}else{$this->components[$name]=$component;}$component->setParent($this,$name);}catch(Exception$e){unset($this->components[$name]);throw$e;}return$this;}function
removeComponent(IComponent$component){$name=$component->getName();if(!isset($this->components[$name])||$this->components[$name]!==$component){throw
new
InvalidArgumentException("Component named '$name' is not located in this container.");}unset($this->components[$name]);$component->setParent(NULL);}function
getComponent($name,$need=TRUE){if(is_int($name)){$name=(string)$name;}elseif(!is_string($name)){throw
new
InvalidArgumentException(sprintf('Component name must be integer or string, %s given.',gettype($name)));}else{$a=strpos($name,self::NAME_SEPARATOR);if($a!==FALSE){$ext=(string)substr($name,$a+1);$name=substr($name,0,$a);}if($name===''){throw
new
InvalidArgumentException('Component or subcomponent name must not be empty string.');}}if(!isset($this->components[$name])){$component=$this->createComponent($name);if($component
instanceof
IComponent&&$component->getParent()===NULL){$this->addComponent($component,$name);}}if(isset($this->components[$name])){if(!isset($ext)){return$this->components[$name];}elseif($this->components[$name]instanceof
IComponentContainer){return$this->components[$name]->getComponent($ext,$need);}elseif($need){throw
new
InvalidArgumentException("Component with name '$name' is not container and cannot have '$ext' component.");}}elseif($need){throw
new
InvalidArgumentException("Component with name '$name' does not exist.");}}protected
function
createComponent($name){$ucname=ucfirst($name);$method='createComponent'.$ucname;if($ucname!==$name&&method_exists($this,$method)&&$this->getReflection()->getMethod($method)->getName()===$method){$component=$this->$method($name);if(!$component
instanceof
IComponent&&!isset($this->components[$name])){$class=get_class($this);throw
new
UnexpectedValueException("Method $class::$method() did not return or create the desired component.");}return$component;}}function
getComponents($deep=FALSE,$filterType=NULL){$iterator=new
NRecursiveComponentIterator($this->components);if($deep){$deep=$deep>0?RecursiveIteratorIterator::SELF_FIRST:RecursiveIteratorIterator::CHILD_FIRST;$iterator=new
RecursiveIteratorIterator($iterator,$deep);}if($filterType){$iterator=new
NInstanceFilterIterator($iterator,$filterType);}return$iterator;}protected
function
validateChildComponent(IComponent$child){}function
__clone(){if($this->components){$oldMyself=reset($this->components)->getParent();$oldMyself->cloning=$this;foreach($this->components
as$name=>$component){$this->components[$name]=clone$component;}$oldMyself->cloning=NULL;}parent::__clone();}function
_isCloning(){return$this->cloning;}}abstract
class
NPresenterComponent
extends
NComponentContainer
implements
ISignalReceiver,IStatePersistent,ArrayAccess{protected$params=array();function
getPresenter($need=TRUE){return$this->lookup('NPresenter',$need);}function
getUniqueId(){return$this->lookupPath('NPresenter',TRUE);}protected
function
attached($presenter){if($presenter
instanceof
NPresenter){$this->loadState($presenter->popGlobalParameters($this->getUniqueId()));}}protected
function
validateParent(IComponentContainer$parent){parent::validateParent($parent);$this->monitor('NPresenter');}protected
function
tryCall($method,array$params){$rc=$this->getReflection();if($rc->hasMethod($method)){$rm=$rc->getMethod($method);if($rm->isPublic()&&!$rm->isAbstract()&&!$rm->isStatic()){$this->checkRequirements($rm);$rm->invokeArgs($this,$rc->combineArgs($rm,$params));return
TRUE;}}return
FALSE;}function
checkRequirements($element){}function
getReflection(){return
new
NPresenterComponentReflection($this);}function
loadState(array$params){$reflection=$this->getReflection();foreach($reflection->getPersistentParams()as$name=>$meta){if(isset($params[$name])){$type=gettype($meta['def']===NULL?$params[$name]:$meta['def']);if(!$reflection->convertType($params[$name],$type)){throw
new
NBadRequestException("Invalid value for persistent parameter '$name' in '{$this->getName()}', expected ".($type==='NULL'?'scalar':$type).".");}$this->$name=&$params[$name];}else{$params[$name]=&$this->$name;}}$this->params=$params;}function
saveState(array&$params,$reflection=NULL){$reflection=$reflection===NULL?$this->getReflection():$reflection;foreach($reflection->getPersistentParams()as$name=>$meta){if(isset($params[$name])){}elseif(array_key_exists($name,$params)){continue;}elseif(!isset($meta['since'])||$this
instanceof$meta['since']){$params[$name]=$this->$name;}else{continue;}$type=gettype($meta['def']===NULL?$params[$name]:$meta['def']);if(!NPresenterComponentReflection::convertType($params[$name],$type)){throw
new
NInvalidLinkException(sprintf("Invalid value for persistent parameter '%s' in '%s', expected %s.",$name,$this->getName(),$type==='NULL'?'scalar':$type));}if($params[$name]===$meta['def']||($meta['def']===NULL&&is_scalar($params[$name])&&(string)$params[$name]==='')){$params[$name]=NULL;}}}function
getParameter($name=NULL,$default=NULL){if(func_num_args()===0){return$this->params;}elseif(isset($this->params[$name])){return$this->params[$name];}else{return$default;}}function
getParameterId($name){$uid=$this->getUniqueId();return$uid===''?$name:$uid.self::NAME_SEPARATOR.$name;}function
getParam($name=NULL,$default=NULL){return
func_num_args()?$this->getParameter($name,$default):$this->getParameter();}function
getParamId($name){trigger_error(__METHOD__.'() is deprecated; use getParameterId() instead.',E_USER_WARNING);return$this->getParameterId($name);}static
function
getPersistentParams(){$rc=new
NClassReflection(func_get_arg(0));$params=array();foreach($rc->getProperties(ReflectionProperty::IS_PUBLIC)as$rp){if(!$rp->isStatic()&&$rp->hasAnnotation('persistent')){$params[]=$rp->getName();}}return$params;}function
signalReceived($signal){if(!$this->tryCall($this->formatSignalMethod($signal),$this->params)){$class=get_class($this);throw
new
NBadSignalException("There is no handler for signal '$signal' in class $class.");}}function
formatSignalMethod($signal){return$signal==NULL?NULL:'handle'.$signal;}function
link($destination,$args=array()){try{$_args=func_get_args();return$this->getPresenter()->createRequest($this,$destination,is_array($args)?$args:array_slice($_args,1),'link');}catch(NInvalidLinkException$e){return$this->getPresenter()->handleInvalidLink($e);}}function
lazyLink($destination,$args=array()){$_args=func_get_args();return
new
NLink($this,$destination,is_array($args)?$args:array_slice($_args,1));}function
isLinkCurrent($destination=NULL,$args=array()){if($destination!==NULL){$_args=func_get_args();$this->getPresenter()->createRequest($this,$destination,is_array($args)?$args:array_slice($_args,1),'test');}return$this->getPresenter()->getLastCreatedRequestFlag('current');}function
redirect($code,$destination=NULL,$args=array()){if(!is_numeric($code)){$args=$destination;$destination=$code;$code=NULL;}if(!is_array($args)){$args=array_slice(func_get_args(),is_numeric($code)?2:1);}$presenter=$this->getPresenter();$presenter->redirectUrl($presenter->createRequest($this,$destination,$args,'redirect'),$code);}function
offsetSet($name,$component){$this->addComponent($component,$name);}function
offsetGet($name){return$this->getComponent($name,TRUE);}function
offsetExists($name){return$this->getComponent($name,FALSE)!==NULL;}function
offsetUnset($name){$component=$this->getComponent($name,FALSE);if($component!==NULL){$this->removeComponent($component);}}}abstract
class
NControl
extends
NPresenterComponent
implements
IRenderable{private$template;private$invalidSnippets=array();public$snippetMode;function
getTemplate(){if($this->template===NULL){$value=$this->createTemplate();if(!$value
instanceof
ITemplate&&$value!==NULL){$class2=get_class($value);$class=get_class($this);throw
new
UnexpectedValueException("Object returned by $class::createTemplate() must be instance of ITemplate, '$class2' given.");}$this->template=$value;}return$this->template;}protected
function
createTemplate($class=NULL){$template=$class?new$class:new
NFileTemplate;$presenter=$this->getPresenter(FALSE);$template->onPrepareFilters[]=$this->templatePrepareFilters;$template->registerHelperLoader('NTemplateHelpers::loader');$template->control=$template->_control=$this;$template->presenter=$template->_presenter=$presenter;if($presenter
instanceof
NPresenter){$template->setCacheStorage($presenter->getContext()->getService('nette.templateCacheStorage'));$template->user=$presenter->getUser();$template->netteHttpResponse=$presenter->getHttpResponse();$template->netteCacheStorage=$presenter->getContext()->getByType('ICacheStorage');$template->baseUri=$template->baseUrl=rtrim($presenter->getHttpRequest()->getUrl()->getBaseUrl(),'/');$template->basePath=preg_replace('#https?://[^/]+#A','',$template->baseUrl);if($presenter->hasFlashSession()){$id=$this->getParameterId('flash');$template->flashes=$presenter->getFlashSession()->$id;}}if(!isset($template->flashes)||!is_array($template->flashes)){$template->flashes=array();}return$template;}function
templatePrepareFilters($template){$template->registerFilter($this->getPresenter()->getContext()->nette->createLatte());}function
getWidget($name){trigger_error(__METHOD__.'() is deprecated, use getComponent() instead.',E_USER_WARNING);return$this->getComponent($name);}function
flashMessage($message,$type='info'){$id=$this->getParameterId('flash');$messages=$this->getPresenter()->getFlashSession()->$id;$messages[]=$flash=(object)array('message'=>$message,'type'=>$type);$this->getTemplate()->flashes=$messages;$this->getPresenter()->getFlashSession()->$id=$messages;return$flash;}function
invalidateControl($snippet=NULL){$this->invalidSnippets[$snippet]=TRUE;}function
validateControl($snippet=NULL){if($snippet===NULL){$this->invalidSnippets=array();}else{unset($this->invalidSnippets[$snippet]);}}function
isControlInvalid($snippet=NULL){if($snippet===NULL){if(count($this->invalidSnippets)>0){return
TRUE;}else{$queue=array($this);do{foreach(array_shift($queue)->getComponents()as$component){if($component
instanceof
IRenderable){if($component->isControlInvalid()){return
TRUE;}}elseif($component
instanceof
IComponentContainer){$queue[]=$component;}}}while($queue);return
FALSE;}}else{return
isset($this->invalidSnippets[NULL])||isset($this->invalidSnippets[$snippet]);}}function
getSnippetId($name=NULL){return'snippet-'.$this->getUniqueId().'-'.$name;}}class
NFormContainer
extends
NComponentContainer
implements
ArrayAccess{public$onValidate;protected$currentGroup;protected$valid;function
setDefaults($values,$erase=FALSE){$form=$this->getForm(FALSE);if(!$form||!$form->isAnchored()||!$form->isSubmitted()){$this->setValues($values,$erase);}return$this;}function
setValues($values,$erase=FALSE){if($values
instanceof
Traversable){$values=iterator_to_array($values);}elseif(!is_array($values)){throw
new
InvalidArgumentException(sprintf('First parameter must be an array, %s given.',gettype($values)));}foreach($this->getComponents()as$name=>$control){if($control
instanceof
IFormControl){if(array_key_exists($name,$values)){$control->setValue($values[$name]);}elseif($erase){$control->setValue(NULL);}}elseif($control
instanceof
NFormContainer){if(array_key_exists($name,$values)){$control->setValues($values[$name],$erase);}elseif($erase){$control->setValues(array(),$erase);}}}return$this;}function
getValues($asArray=FALSE){$values=$asArray?array():new
NArrayHash;foreach($this->getComponents()as$name=>$control){if($control
instanceof
IFormControl&&!$control->isDisabled()&&!$control
instanceof
ISubmitterControl){$values[$name]=$control->getValue();}elseif($control
instanceof
NFormContainer){$values[$name]=$control->getValues($asArray);}}return$values;}function
isValid(){if($this->valid===NULL){$this->validate();}return$this->valid;}function
validate(){$this->valid=TRUE;foreach($this->getControls()as$control){if(!$control->getRules()->validate()){$this->valid=FALSE;}}$this->onValidate($this);}function
setCurrentGroup(NFormGroup$group=NULL){$this->currentGroup=$group;return$this;}function
getCurrentGroup(){return$this->currentGroup;}function
addComponent(IComponent$component,$name,$insertBefore=NULL){parent::addComponent($component,$name,$insertBefore);if($this->currentGroup!==NULL&&$component
instanceof
IFormControl){$this->currentGroup->add($component);}return$this;}function
getControls(){return$this->getComponents(TRUE,'IFormControl');}function
getForm($need=TRUE){return$this->lookup('NForm',$need);}function
addText($name,$label=NULL,$cols=NULL,$maxLength=NULL){return$this[$name]=new
NTextInput($label,$cols,$maxLength);}function
addPassword($name,$label=NULL,$cols=NULL,$maxLength=NULL){$control=new
NTextInput($label,$cols,$maxLength);$control->setType('password');return$this[$name]=$control;}function
addTextArea($name,$label=NULL,$cols=40,$rows=10){return$this[$name]=new
NTextArea($label,$cols,$rows);}function
addUpload($name,$label=NULL){return$this[$name]=new
NUploadControl($label);}function
addHidden($name,$default=NULL){$control=new
NHiddenField;$control->setDefaultValue($default);return$this[$name]=$control;}function
addCheckbox($name,$caption=NULL){return$this[$name]=new
NCheckbox($caption);}function
addRadioList($name,$label=NULL,array$items=NULL){return$this[$name]=new
NRadioList($label,$items);}function
addSelect($name,$label=NULL,array$items=NULL,$size=NULL){return$this[$name]=new
NSelectBox($label,$items,$size);}function
addMultiSelect($name,$label=NULL,array$items=NULL,$size=NULL){return$this[$name]=new
NMultiSelectBox($label,$items,$size);}function
addSubmit($name,$caption=NULL){return$this[$name]=new
NSubmitButton($caption);}function
addButton($name,$caption){return$this[$name]=new
NButton($caption);}function
addImage($name,$src=NULL,$alt=NULL){return$this[$name]=new
NImageButton($src,$alt);}function
addContainer($name){$control=new
NFormContainer;$control->currentGroup=$this->currentGroup;return$this[$name]=$control;}function
offsetSet($name,$component){$this->addComponent($component,$name);}function
offsetGet($name){return$this->getComponent($name,TRUE);}function
offsetExists($name){return$this->getComponent($name,FALSE)!==NULL;}function
offsetUnset($name){$component=$this->getComponent($name,FALSE);if($component!==NULL){$this->removeComponent($component);}}function
__clone(){throw
new
NotImplementedException('Form cloning is not supported yet.');}function
addFile($name,$label=NULL){trigger_error(__METHOD__.'() is deprecated; use addUpload() instead.',E_USER_WARNING);return$this->addUpload($name,$label);}}class
NForm
extends
NFormContainer{const
EQUAL=':equal',IS_IN=':equal',NOT_EQUAL=':notEqual',FILLED=':filled',BLANK=':blank',REQUIRED=self::FILLED,VALID=':valid';const
PROTECTION='NHiddenField::validateEqual';const
SUBMITTED=':submitted';const
MIN_LENGTH=':minLength',MAX_LENGTH=':maxLength',LENGTH=':length',EMAIL=':email',URL=':url',REGEXP=':regexp',PATTERN=':pattern',INTEGER=':integer',NUMERIC=':integer',FLOAT=':float',RANGE=':range';const
COUNT=':length';const
MAX_FILE_SIZE=':fileSize',MIME_TYPE=':mimeType',IMAGE=':image';const
GET='get',POST='post';const
TRACKER_ID='_form_';const
PROTECTOR_ID='_token_';public$onSuccess;public$onError;public$onSubmit;public$onInvalidSubmit;private$submittedBy;private$httpData;private$element;private$renderer;private$translator;private$groups=array();private$errors=array();public$httpRequest;function
__construct($name=NULL){$this->element=NHtml::el('form');$this->element->action='';$this->element->method=self::POST;$this->element->id=$name===NULL?NULL:'frm-'.$name;$this->monitor(__CLASS__);if($name!==NULL){$tracker=new
NHiddenField($name);$tracker->unmonitor(__CLASS__);$this[self::TRACKER_ID]=$tracker;}parent::__construct(NULL,$name);}protected
function
attached($obj){if($obj
instanceof
self){throw
new
InvalidStateException('Nested forms are forbidden.');}}function
getForm($need=TRUE){return$this;}function
setAction($url){$this->element->action=$url;return$this;}function
getAction(){return$this->element->action;}function
setMethod($method){if($this->httpData!==NULL){throw
new
InvalidStateException(__METHOD__.'() must be called until the form is empty.');}$this->element->method=strtolower($method);return$this;}function
getMethod(){return$this->element->method;}function
addProtection($message=NULL,$timeout=NULL){$session=$this->getSession()->getSection('Nette.Forms.Form/CSRF');$key="key$timeout";if(isset($session->$key)){$token=$session->$key;}else{$session->$key=$token=NStrings::random();}$session->setExpiration($timeout,$key);$this[self::PROTECTOR_ID]=new
NHiddenField($token);$this[self::PROTECTOR_ID]->addRule(self::PROTECTION,$message,$token);}function
addGroup($caption=NULL,$setAsCurrent=TRUE){$group=new
NFormGroup;$group->setOption('label',$caption);$group->setOption('visual',TRUE);if($setAsCurrent){$this->setCurrentGroup($group);}if(isset($this->groups[$caption])){return$this->groups[]=$group;}else{return$this->groups[$caption]=$group;}}function
removeGroup($name){if(is_string($name)&&isset($this->groups[$name])){$group=$this->groups[$name];}elseif($name
instanceof
NFormGroup&&in_array($name,$this->groups,TRUE)){$group=$name;$name=array_search($group,$this->groups,TRUE);}else{throw
new
InvalidArgumentException("Group not found in form '$this->name'");}foreach($group->getControls()as$control){$control->getParent()->removeComponent($control);}unset($this->groups[$name]);}function
getGroups(){return$this->groups;}function
getGroup($name){return
isset($this->groups[$name])?$this->groups[$name]:NULL;}function
setTranslator(ITranslator$translator=NULL){$this->translator=$translator;return$this;}function
getTranslator(){return$this->translator;}function
isAnchored(){return
TRUE;}function
isSubmitted(){if($this->submittedBy===NULL){$this->getHttpData();}return$this->submittedBy;}function
isSuccess(){return$this->isSubmitted()&&$this->isValid();}function
setSubmittedBy(ISubmitterControl$by=NULL){$this->submittedBy=$by===NULL?FALSE:$by;return$this;}function
getHttpData(){if($this->httpData===NULL){if(!$this->isAnchored()){throw
new
InvalidStateException('Form is not anchored and therefore can not determine whether it was submitted.');}$data=$this->receiveHttpData();$this->httpData=(array)$data;$this->submittedBy=is_array($data);}return$this->httpData;}function
fireEvents(){if(!$this->isSubmitted()){return;}elseif($this->submittedBy
instanceof
ISubmitterControl){if(!$this->submittedBy->getValidationScope()||$this->isValid()){$this->submittedBy->click();$valid=TRUE;}else{$this->submittedBy->onInvalidClick($this->submittedBy);}}if(isset($valid)||$this->isValid()){$this->onSuccess($this);}else{$this->onError($this);if($this->onInvalidSubmit){trigger_error(__CLASS__.'->onInvalidSubmit is deprecated; use onError instead.',E_USER_WARNING);$this->onInvalidSubmit($this);}}if($this->onSuccess){$this->onSubmit($this);}elseif($this->onSubmit){trigger_error(__CLASS__.'->onSubmit changed its behavior; use onSuccess instead.',E_USER_WARNING);if(isset($valid)||$this->isValid()){$this->onSubmit($this);}}}protected
function
receiveHttpData(){$httpRequest=$this->getHttpRequest();if(strcasecmp($this->getMethod(),$httpRequest->getMethod())){return;}if($httpRequest->isMethod('post')){$data=NArrays::mergeTree($httpRequest->getPost(),$httpRequest->getFiles());}else{$data=$httpRequest->getQuery();if(!$data){return;}}if($tracker=$this->getComponent(self::TRACKER_ID,FALSE)){if(!isset($data[self::TRACKER_ID])||$data[self::TRACKER_ID]!==$tracker->getValue()){return;}}return$data;}function
getValues($asArray=FALSE){$values=parent::getValues($asArray);unset($values[self::TRACKER_ID],$values[self::PROTECTOR_ID]);return$values;}function
addError($message){$this->valid=FALSE;if($message!==NULL&&!in_array($message,$this->errors,TRUE)){$this->errors[]=$message;}}function
getErrors(){return$this->errors;}function
hasErrors(){return(bool)$this->getErrors();}function
cleanErrors(){$this->errors=array();$this->valid=NULL;}function
getElementPrototype(){return$this->element;}function
setRenderer(IFormRenderer$renderer){$this->renderer=$renderer;return$this;}function
getRenderer(){if($this->renderer===NULL){$this->renderer=new
NDefaultFormRenderer;}return$this->renderer;}function
render(){$args=func_get_args();array_unshift($args,$this);echo
call_user_func_array(array($this->getRenderer(),'render'),$args);}function
__toString(){try{return$this->getRenderer()->render($this);}catch(Exception$e){if(func_num_args()){throw$e;}trigger_error("Exception in ".__METHOD__."(): {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}",E_USER_ERROR);}}protected
function
getHttpRequest(){if(!$this->httpRequest){$factory=new
NHttpRequestFactory;$this->httpRequest=$factory->setEncoding('UTF-8')->createHttpRequest();}return$this->httpRequest;}protected
function
getSession(){if(!$this->httpRequest){$this->httpRequest=NEnvironment::getHttpRequest();}return
NEnvironment::getSession();}}class
NAppForm
extends
NForm
implements
ISignalReceiver{function
__construct(IComponentContainer$parent=NULL,$name=NULL){parent::__construct();$this->monitor('NPresenter');if($parent!==NULL){$parent->addComponent($this,$name);}}function
getPresenter($need=TRUE){return$this->lookup('NPresenter',$need);}protected
function
attached($presenter){if($presenter
instanceof
NPresenter){$name=$this->lookupPath('NPresenter');if(!isset($this->getElementPrototype()->id)){$this->getElementPrototype()->id='frm-'.$name;}if(!$this->getAction()){$this->setAction(new
NLink($presenter,$name.self::NAME_SEPARATOR.'submit!',array()));}if(iterator_count($this->getControls())&&$this->isSubmitted()){foreach($this->getControls()as$control){if(!$control->isDisabled()){$control->loadHttpData();}}}}parent::attached($presenter);}function
isAnchored(){return(bool)$this->getPresenter(FALSE);}protected
function
receiveHttpData(){$presenter=$this->getPresenter();if(!$presenter->isSignalReceiver($this,'submit')){return;}$isPost=$this->getMethod()===self::POST;$request=$presenter->getRequest();if($request->isMethod('forward')||$request->isMethod('post')!==$isPost){return;}if($isPost){return
NArrays::mergeTree($request->getPost(),$request->getFiles());}else{return$request->getParameters();}}function
signalReceived($signal){if($signal==='submit'){if(!$this->getPresenter()->getRequest()->hasFlag(NPresenterRequest::RESTORED)){$this->fireEvents();}}else{$class=get_class($this);throw
new
NBadSignalException("Missing handler for signal '$signal' in $class.");}}}class
NInvalidLinkException
extends
Exception{function
__construct($message='',$code=0,Exception$previous=NULL){if(PHP_VERSION_ID<50300){$this->previous=$previous;parent::__construct($message,$code);}else{parent::__construct($message,$code,$previous);}}}class
NLink
extends
NObject{private$component;private$destination;private$params;function
__construct(NPresenterComponent$component,$destination,array$params){$this->component=$component;$this->destination=$destination;$this->params=$params;}function
getDestination(){return$this->destination;}function
setParameter($key,$value){$this->params[$key]=$value;return$this;}function
getParameter($key){return
isset($this->params[$key])?$this->params[$key]:NULL;}function
getParameters(){return$this->params;}function
__toString(){try{return(string)$this->component->link($this->destination,$this->params);}catch(Exception$e){if(func_num_args()){throw$e;}trigger_error("Exception in ".__METHOD__."(): {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}",E_USER_ERROR);}}}class
NMultiplier
extends
NPresenterComponent{private$factory;function
__construct($factory){parent::__construct();$this->factory=new
NCallback($factory);}protected
function
createComponent($name){return$this->factory->invoke($name,$this);}}abstract
class
NPresenter
extends
NControl
implements
IPresenter{const
INVALID_LINK_SILENT=1,INVALID_LINK_WARNING=2,INVALID_LINK_EXCEPTION=3;const
SIGNAL_KEY='do',ACTION_KEY='action',FLASH_KEY='_fid',DEFAULT_ACTION='default';public$invalidLinkMode;public$onShutdown;private$request;private$response;public$autoCanonicalize=TRUE;public$absoluteUrls=FALSE;private$globalParams;private$globalState;private$globalStateSinces;private$action;private$view;private$layout;private$payload;private$signalReceiver;private$signal;private$ajaxMode;private$startupCheck;private$lastCreatedRequest;private$lastCreatedRequestFlag;private$context;function
__construct(NDIContainer$context=NULL){$this->context=$context;if($context&&$this->invalidLinkMode===NULL){$this->invalidLinkMode=$context->parameters['productionMode']?self::INVALID_LINK_SILENT:self::INVALID_LINK_WARNING;}}function
getRequest(){return$this->request;}function
getPresenter($need=TRUE){return$this;}function
getUniqueId(){return'';}function
run(NPresenterRequest$request){try{$this->request=$request;$this->payload=new
stdClass;$this->setParent($this->getParent(),$request->getPresenterName());if(!$this->getHttpResponse()->isSent()){$this->getHttpResponse()->addHeader('Vary','X-Requested-With');}$this->initGlobalParameters();$this->checkRequirements($this->getReflection());$this->startup();if(!$this->startupCheck){$class=$this->getReflection()->getMethod('startup')->getDeclaringClass()->getName();throw
new
InvalidStateException("Method $class::startup() or its descendant doesn't call parent::startup().");}$this->tryCall($this->formatActionMethod($this->getAction()),$this->params);foreach($this->globalParams
as$id=>$foo){$this->getComponent($id,FALSE);}if($this->autoCanonicalize){$this->canonicalize();}if($this->getHttpRequest()->isMethod('head')){$this->terminate();}$this->processSignal();$this->beforeRender();$this->tryCall($this->formatRenderMethod($this->getView()),$this->params);$this->afterRender();$this->saveGlobalState();if($this->isAjax()){$this->payload->state=$this->getGlobalState();}$this->sendTemplate();}catch(NAbortException$e){if($this->isAjax())try{$hasPayload=(array)$this->payload;unset($hasPayload['state']);if($this->response
instanceof
NTextResponse&&$this->isControlInvalid()){$this->snippetMode=TRUE;$this->response->send($this->getHttpRequest(),$this->getHttpResponse());$this->sendPayload();}elseif(!$this->response&&$hasPayload){$this->sendPayload();}}catch(NAbortException$e){}if($this->hasFlashSession()){$this->getFlashSession()->setExpiration($this->response
instanceof
NRedirectResponse?'+ 30 seconds':'+ 3 seconds');}$this->onShutdown($this,$this->response);$this->shutdown($this->response);return$this->response;}}protected
function
startup(){$this->startupCheck=TRUE;}protected
function
beforeRender(){}protected
function
afterRender(){}protected
function
shutdown($response){}function
checkRequirements($element){$user=(array)$element->getAnnotation('User');if(in_array('loggedIn',$user)&&!$this->getUser()->isLoggedIn()){throw
new
NForbiddenRequestException;}}function
processSignal(){if($this->signal===NULL){return;}try{$component=$this->signalReceiver===''?$this:$this->getComponent($this->signalReceiver,FALSE);}catch(InvalidArgumentException$e){}if(isset($e)||$component===NULL){throw
new
NBadSignalException("The signal receiver component '$this->signalReceiver' is not found.",NULL,isset($e)?$e:NULL);}elseif(!$component
instanceof
ISignalReceiver){throw
new
NBadSignalException("The signal receiver component '$this->signalReceiver' is not ISignalReceiver implementor.");}$component->signalReceived($this->signal);$this->signal=NULL;}function
getSignal(){return$this->signal===NULL?NULL:array($this->signalReceiver,$this->signal);}function
isSignalReceiver($component,$signal=NULL){if($component
instanceof
NComponent){$component=$component===$this?'':$component->lookupPath(__CLASS__,TRUE);}if($this->signal===NULL){return
FALSE;}elseif($signal===TRUE){return$component===''||strncmp($this->signalReceiver.'-',$component.'-',strlen($component)+1)===0;}elseif($signal===NULL){return$this->signalReceiver===$component;}else{return$this->signalReceiver===$component&&strcasecmp($signal,$this->signal)===0;}}function
getAction($fullyQualified=FALSE){return$fullyQualified?':'.$this->getName().':'.$this->action:$this->action;}function
changeAction($action){if(is_string($action)&&NStrings::match($action,'#^[a-zA-Z0-9][a-zA-Z0-9_\x7f-\xff]*\z#')){$this->action=$action;$this->view=$action;}else{$this->error('Action name is not alphanumeric string.');}}function
getView(){return$this->view;}function
setView($view){$this->view=(string)$view;return$this;}function
getLayout(){return$this->layout;}function
setLayout($layout){$this->layout=$layout===FALSE?FALSE:(string)$layout;return$this;}function
sendTemplate(){$template=$this->getTemplate();if(!$template){return;}if($template
instanceof
IFileTemplate&&!$template->getFile()){$files=$this->formatTemplateFiles();foreach($files
as$file){if(is_file($file)){$template->setFile($file);break;}}if(!$template->getFile()){$file=preg_replace('#^.*([/\\\\].{1,70})\z#U',"\xE2\x80\xA6\$1",reset($files));$file=strtr($file,'/',DIRECTORY_SEPARATOR);$this->error("Page not found. Missing template '$file'.");}}$this->sendResponse(new
NTextResponse($template));}function
findLayoutTemplateFile(){if($this->layout===FALSE){return;}$files=$this->formatLayoutTemplateFiles();foreach($files
as$file){if(is_file($file)){return$file;}}if($this->layout){$file=preg_replace('#^.*([/\\\\].{1,70})\z#U',"\xE2\x80\xA6\$1",reset($files));$file=strtr($file,'/',DIRECTORY_SEPARATOR);throw
new
FileNotFoundException("Layout not found. Missing template '$file'.");}}function
formatLayoutTemplateFiles(){$name=$this->getName();$presenter=substr($name,strrpos(':'.$name,':'));$layout=$this->layout?$this->layout:'layout';$dir=dirname($this->getReflection()->getFileName());$dir=is_dir("$dir/templates")?$dir:dirname($dir);$list=array("$dir/templates/$presenter/@$layout.latte","$dir/templates/$presenter.@$layout.latte","$dir/templates/$presenter/@$layout.phtml","$dir/templates/$presenter.@$layout.phtml");do{$list[]="$dir/templates/@$layout.latte";$list[]="$dir/templates/@$layout.phtml";$dir=dirname($dir);}while($dir&&($name=substr($name,0,strrpos($name,':'))));return$list;}function
formatTemplateFiles(){$name=$this->getName();$presenter=substr($name,strrpos(':'.$name,':'));$dir=dirname($this->getReflection()->getFileName());$dir=is_dir("$dir/templates")?$dir:dirname($dir);return
array("$dir/templates/$presenter/$this->view.latte","$dir/templates/$presenter.$this->view.latte","$dir/templates/$presenter/$this->view.phtml","$dir/templates/$presenter.$this->view.phtml");}protected
static
function
formatActionMethod($action){return'action'.$action;}protected
static
function
formatRenderMethod($view){return'render'.$view;}function
getPayload(){return$this->payload;}function
isAjax(){if($this->ajaxMode===NULL){$this->ajaxMode=$this->getHttpRequest()->isAjax();}return$this->ajaxMode;}function
sendPayload(){$this->sendResponse(new
NJsonResponse($this->payload));}function
sendResponse(IPresenterResponse$response){$this->response=$response;$this->terminate();}function
terminate(){if(func_num_args()!==0){trigger_error(__METHOD__.' is not intended to send a Application\Response; use sendResponse() instead.',E_USER_WARNING);$this->sendResponse(func_get_arg(0));}throw
new
NAbortException();}function
forward($destination,$args=array()){if($destination
instanceof
NPresenterRequest){$this->sendResponse(new
NForwardResponse($destination));}$_args=func_get_args();$this->createRequest($this,$destination,is_array($args)?$args:array_slice($_args,1),'forward');$this->sendResponse(new
NForwardResponse($this->lastCreatedRequest));}function
redirectUrl($url,$code=NULL){if($this->isAjax()){$this->payload->redirect=(string)$url;$this->sendPayload();}elseif(!$code){$code=$this->getHttpRequest()->isMethod('post')?IHttpResponse::S303_POST_GET:IHttpResponse::S302_FOUND;}$this->sendResponse(new
NRedirectResponse($url,$code));}function
redirectUri($url,$code=NULL){trigger_error(__METHOD__.'() is deprecated; use '.__CLASS__.'::redirectUrl() instead.',E_USER_WARNING);$this->redirectUrl($url,$code);}function
error($message=NULL,$code=IHttpResponse::S404_NOT_FOUND){throw
new
NBadRequestException($message,$code);}function
backlink(){return$this->getAction(TRUE);}function
getLastCreatedRequest(){return$this->lastCreatedRequest;}function
getLastCreatedRequestFlag($flag){return!empty($this->lastCreatedRequestFlag[$flag]);}function
canonicalize(){if(!$this->isAjax()&&($this->request->isMethod('get')||$this->request->isMethod('head'))){try{$url=$this->createRequest($this,$this->action,$this->getGlobalState()+$this->request->getParameters(),'redirectX');}catch(NInvalidLinkException$e){}if(isset($url)&&!$this->getHttpRequest()->getUrl()->isEqual($url)){$this->sendResponse(new
NRedirectResponse($url,IHttpResponse::S301_MOVED_PERMANENTLY));}}}function
lastModified($lastModified,$etag=NULL,$expire=NULL){if($expire!==NULL){$this->getHttpResponse()->setExpiration($expire);}if(!$this->getHttpContext()->isModified($lastModified,$etag)){$this->terminate();}}protected
function
createRequest($component,$destination,array$args,$mode){static$presenterFactory,$router,$refUrl;if($presenterFactory===NULL){$presenterFactory=$this->getApplication()->getPresenterFactory();$router=$this->getApplication()->getRouter();$refUrl=new
NUrl($this->getHttpRequest()->getUrl());$refUrl->setPath($this->getHttpRequest()->getUrl()->getScriptPath());}$this->lastCreatedRequest=$this->lastCreatedRequestFlag=NULL;$a=strpos($destination,'#');if($a===FALSE){$fragment='';}else{$fragment=substr($destination,$a);$destination=substr($destination,0,$a);}$a=strpos($destination,'?');if($a!==FALSE){parse_str(substr($destination,$a+1),$args);$destination=substr($destination,0,$a);}$a=strpos($destination,'//');if($a===FALSE){$scheme=FALSE;}else{$scheme=substr($destination,0,$a);$destination=substr($destination,$a+2);}if(!$component
instanceof
NPresenter||substr($destination,-1)==='!'){$signal=rtrim($destination,'!');$a=strrpos($signal,':');if($a!==FALSE){$component=$component->getComponent(strtr(substr($signal,0,$a),':','-'));$signal=(string)substr($signal,$a+1);}if($signal==NULL){throw
new
NInvalidLinkException("Signal must be non-empty string.");}$destination='this';}if($destination==NULL){throw
new
NInvalidLinkException("Destination must be non-empty string.");}$current=FALSE;$a=strrpos($destination,':');if($a===FALSE){$action=$destination==='this'?$this->action:$destination;$presenter=$this->getName();$presenterClass=get_class($this);}else{$action=(string)substr($destination,$a+1);if($destination[0]===':'){if($a<2){throw
new
NInvalidLinkException("Missing presenter name in '$destination'.");}$presenter=substr($destination,1,$a-1);}else{$presenter=$this->getName();$b=strrpos($presenter,':');if($b===FALSE){$presenter=substr($destination,0,$a);}else{$presenter=substr($presenter,0,$b+1).substr($destination,0,$a);}}try{$presenterClass=$presenterFactory->getPresenterClass($presenter);}catch(NInvalidPresenterException$e){throw
new
NInvalidLinkException($e->getMessage(),NULL,$e);}}if(isset($signal)){$reflection=new
NPresenterComponentReflection(get_class($component));if($signal==='this'){$signal='';if(array_key_exists(0,$args)){throw
new
NInvalidLinkException("Unable to pass parameters to 'this!' signal.");}}elseif(strpos($signal,self::NAME_SEPARATOR)===FALSE){$method=$component->formatSignalMethod($signal);if(!$reflection->hasCallableMethod($method)){throw
new
NInvalidLinkException("Unknown signal '$signal', missing handler {$reflection->name}::$method()");}if($args){self::argsToParams(get_class($component),$method,$args);}}if($args&&array_intersect_key($args,$reflection->getPersistentParams())){$component->saveState($args);}if($args&&$component!==$this){$prefix=$component->getUniqueId().self::NAME_SEPARATOR;foreach($args
as$key=>$val){unset($args[$key]);$args[$prefix.$key]=$val;}}}if(is_subclass_of($presenterClass,__CLASS__)){if($action===''){$action=self::DEFAULT_ACTION;}$current=($action==='*'||strcasecmp($action,$this->action)===0)&&$presenterClass===get_class($this);$reflection=new
NPresenterComponentReflection($presenterClass);if($args||$destination==='this'){$method=call_user_func(array($presenterClass,'formatActionMethod'),$action);if(!$reflection->hasCallableMethod($method)){$method=call_user_func(array($presenterClass,'formatRenderMethod'),$action);if(!$reflection->hasCallableMethod($method)){$method=NULL;}}if($method===NULL){if(array_key_exists(0,$args)){throw
new
NInvalidLinkException("Unable to pass parameters to action '$presenter:$action', missing corresponding method.");}}elseif($destination==='this'){self::argsToParams($presenterClass,$method,$args,$this->params);}else{self::argsToParams($presenterClass,$method,$args);}}if($args&&array_intersect_key($args,$reflection->getPersistentParams())){$this->saveState($args,$reflection);}if($mode==='redirect'){$this->saveGlobalState();}$globalState=$this->getGlobalState($destination==='this'?NULL:$presenterClass);if($current&&$args){$tmp=$globalState+$this->params;foreach($args
as$key=>$val){if(http_build_query(array($val))!==(isset($tmp[$key])?http_build_query(array($tmp[$key])):'')){$current=FALSE;break;}}}$args+=$globalState;}if($action){$args[self::ACTION_KEY]=$action;}if(!empty($signal)){$args[self::SIGNAL_KEY]=$component->getParameterId($signal);$current=$current&&$args[self::SIGNAL_KEY]===$this->getParameter(self::SIGNAL_KEY);}if(($mode==='redirect'||$mode==='forward')&&$this->hasFlashSession()){$args[self::FLASH_KEY]=$this->getParameter(self::FLASH_KEY);}$this->lastCreatedRequest=new
NPresenterRequest($presenter,NPresenterRequest::FORWARD,$args,array(),array());$this->lastCreatedRequestFlag=array('current'=>$current);if($mode==='forward'||$mode==='test'){return;}$url=$router->constructUrl($this->lastCreatedRequest,$refUrl);if($url===NULL){unset($args[self::ACTION_KEY]);$params=urldecode(http_build_query($args,NULL,', '));throw
new
NInvalidLinkException("No route for $presenter:$action($params)");}if($mode==='link'&&$scheme===FALSE&&!$this->absoluteUrls){$hostUrl=$refUrl->getHostUrl().'/';if(strncmp($url,$hostUrl,strlen($hostUrl))===0){$url=substr($url,strlen($hostUrl)-1);}}return$url.$fragment;}private
static
function
argsToParams($class,$method,&$args,$supplemental=array()){$i=0;$rm=new
ReflectionMethod($class,$method);foreach($rm->getParameters()as$param){$name=$param->getName();if(array_key_exists($i,$args)){$args[$name]=$args[$i];unset($args[$i]);$i++;}elseif(array_key_exists($name,$args)){}elseif(array_key_exists($name,$supplemental)){$args[$name]=$supplemental[$name];}else{continue;}if($args[$name]===NULL){continue;}$def=$param->isDefaultValueAvailable()&&$param->isOptional()?$param->getDefaultValue():NULL;$type=$param->isArray()?'array':gettype($def);if(!NPresenterComponentReflection::convertType($args[$name],$type)){throw
new
NInvalidLinkException("Invalid value for parameter '$name' in method $class::$method(), expected ".($type==='NULL'?'scalar':$type).".");}if($args[$name]===$def||($def===NULL&&is_scalar($args[$name])&&(string)$args[$name]==='')){$args[$name]=NULL;}}if(array_key_exists($i,$args)){$method=$rm->getName();throw
new
NInvalidLinkException("Passed more parameters than method $class::$method() expects.");}}protected
function
handleInvalidLink(NInvalidLinkException$e){if($this->invalidLinkMode===self::INVALID_LINK_SILENT){return'#';}elseif($this->invalidLinkMode===self::INVALID_LINK_WARNING){return'error: '.$e->getMessage();}else{throw$e;}}function
storeRequest($expiration='+ 10 minutes'){$session=$this->getSession('Nette.Application/requests');do{$key=NStrings::random(5);}while(isset($session[$key]));$session[$key]=array($this->getUser()->getId(),$this->request);$session->setExpiration($expiration,$key);return$key;}function
restoreRequest($key){$session=$this->getSession('Nette.Application/requests');if(!isset($session[$key])||($session[$key][0]!==NULL&&$session[$key][0]!==$this->getUser()->getId())){return;}$request=clone$session[$key][1];unset($session[$key]);$request->setFlag(NPresenterRequest::RESTORED,TRUE);$params=$request->getParameters();$params[self::FLASH_KEY]=$this->getParameter(self::FLASH_KEY);$request->setParameters($params);$this->sendResponse(new
NForwardResponse($request));}static
function
getPersistentComponents(){return(array)NClassReflection::from(func_get_arg(0))->getAnnotation('persistent');}private
function
getGlobalState($forClass=NULL){$sinces=&$this->globalStateSinces;if($this->globalState===NULL){$state=array();foreach($this->globalParams
as$id=>$params){$prefix=$id.self::NAME_SEPARATOR;foreach($params
as$key=>$val){$state[$prefix.$key]=$val;}}$this->saveState($state,$forClass?new
NPresenterComponentReflection($forClass):NULL);if($sinces===NULL){$sinces=array();foreach($this->getReflection()->getPersistentParams()as$name=>$meta){$sinces[$name]=$meta['since'];}}$components=$this->getReflection()->getPersistentComponents();$iterator=$this->getComponents(TRUE,'IStatePersistent');foreach($iterator
as$name=>$component){if($iterator->getDepth()===0){$since=isset($components[$name]['since'])?$components[$name]['since']:FALSE;}$prefix=$component->getUniqueId().self::NAME_SEPARATOR;$params=array();$component->saveState($params);foreach($params
as$key=>$val){$state[$prefix.$key]=$val;$sinces[$prefix.$key]=$since;}}}else{$state=$this->globalState;}if($forClass!==NULL){$since=NULL;foreach($state
as$key=>$foo){if(!isset($sinces[$key])){$x=strpos($key,self::NAME_SEPARATOR);$x=$x===FALSE?$key:substr($key,0,$x);$sinces[$key]=isset($sinces[$x])?$sinces[$x]:FALSE;}if($since!==$sinces[$key]){$since=$sinces[$key];$ok=$since&&(is_subclass_of($forClass,$since)||$forClass===$since);}if(!$ok){unset($state[$key]);}}}return$state;}protected
function
saveGlobalState(){$this->globalParams=array();$this->globalState=$this->getGlobalState();}private
function
initGlobalParameters(){$this->globalParams=array();$selfParams=array();$params=$this->request->getParameters();if($this->isAjax()){$params+=$this->request->getPost();}foreach($params
as$key=>$value){if(!preg_match('#^((?:[a-z0-9_]+-)*)((?!\d+\z)[a-z0-9_]+)\z#i',$key,$matches)){continue;}elseif(!$matches[1]){$selfParams[$key]=$value;}else{$this->globalParams[substr($matches[1],0,-1)][$matches[2]]=$value;}}$this->changeAction(isset($selfParams[self::ACTION_KEY])?$selfParams[self::ACTION_KEY]:self::DEFAULT_ACTION);$this->signalReceiver=$this->getUniqueId();if(isset($selfParams[self::SIGNAL_KEY])){$param=$selfParams[self::SIGNAL_KEY];if(!is_string($param)){$this->error('Signal name is not string.');}$pos=strrpos($param,'-');if($pos){$this->signalReceiver=substr($param,0,$pos);$this->signal=substr($param,$pos+1);}else{$this->signalReceiver=$this->getUniqueId();$this->signal=$param;}if($this->signal==NULL){$this->signal=NULL;}}$this->loadState($selfParams);}function
popGlobalParameters($id){if(isset($this->globalParams[$id])){$res=$this->globalParams[$id];unset($this->globalParams[$id]);return$res;}else{return
array();}}function
hasFlashSession(){return!empty($this->params[self::FLASH_KEY])&&$this->getSession()->hasSection('Nette.Application.Flash/'.$this->params[self::FLASH_KEY]);}function
getFlashSession(){if(empty($this->params[self::FLASH_KEY])){$this->params[self::FLASH_KEY]=NStrings::random(4);}return$this->getSession('Nette.Application.Flash/'.$this->params[self::FLASH_KEY]);}function
injectPrimary(NDIContainer$context){$this->context=$context;}function
getContext(){return$this->context;}function
getService($name){return$this->context->getService($name);}protected
function
getHttpRequest(){return$this->context->getByType('IHttpRequest');}protected
function
getHttpResponse(){return$this->context->getByType('IHttpResponse');}protected
function
getHttpContext(){return$this->context->getByType('NHttpContext');}function
getApplication(){return$this->context->getByType('NApplication');}function
getSession($namespace=NULL){$handler=$this->context->getByType('NSession');return$namespace===NULL?$handler:$handler->getSection($namespace);}function
getUser(){return$this->context->getByType('NUser');}}class
NClassReflection
extends
ReflectionClass{private
static$extMethods;static
function
from($class){return
new
self($class);}function
__toString(){return'Class '.$this->getName();}function
hasEventProperty($name){if(preg_match('#^on[A-Z]#',$name)&&$this->hasProperty($name)){$rp=$this->getProperty($name);return$rp->isPublic()&&!$rp->isStatic();}return
FALSE;}function
setExtensionMethod($name,$callback){$l=&self::$extMethods[strtolower($name)];$l[strtolower($this->getName())]=new
NCallback($callback);$l['']=NULL;return$this;}function
getExtensionMethod($name){if(self::$extMethods===NULL||$name===NULL){$list=get_defined_functions();foreach($list['user']as$fce){$pair=explode('_prototype_',$fce);if(count($pair)===2){self::$extMethods[$pair[1]][$pair[0]]=new
NCallback($fce);self::$extMethods[$pair[1]]['']=NULL;}}if($name===NULL){return
NULL;}}$class=strtolower($this->getName());$l=&self::$extMethods[strtolower($name)];if(empty($l)){return
FALSE;}elseif(isset($l[''][$class])){return$l[''][$class];}$cl=$class;do{if(isset($l[$cl])){return$l[''][$class]=$l[$cl];}}while(($cl=strtolower(get_parent_class($cl)))!=='');foreach(class_implements($class)as$cl){$cl=strtolower($cl);if(isset($l[$cl])){return$l[''][$class]=$l[$cl];}}return$l[''][$class]=FALSE;}function
is($type){return$this->isSubclassOf($type)||strcasecmp($this->getName(),ltrim($type,'\\'))===0;}function
getConstructor(){return($ref=parent::getConstructor())?NMethodReflection::from($this->getName(),$ref->getName()):NULL;}function
getExtension(){return($name=$this->getExtensionName())?new
NExtensionReflection($name):NULL;}function
getInterfaces(){$res=array();foreach(parent::getInterfaceNames()as$val){$res[$val]=new
self($val);}return$res;}function
getMethod($name){return
new
NMethodReflection($this->getName(),$name);}function
getMethods($filter=-1){foreach($res=parent::getMethods($filter)as$key=>$val){$res[$key]=new
NMethodReflection($this->getName(),$val->getName());}return$res;}function
getParentClass(){return($ref=parent::getParentClass())?new
self($ref->getName()):NULL;}function
getProperties($filter=-1){foreach($res=parent::getProperties($filter)as$key=>$val){$res[$key]=new
NPropertyReflection($this->getName(),$val->getName());}return$res;}function
getProperty($name){return
new
NPropertyReflection($this->getName(),$name);}function
hasAnnotation($name){$res=NAnnotationsParser::getAll($this);return!empty($res[$name]);}function
getAnnotation($name){$res=NAnnotationsParser::getAll($this);return
isset($res[$name])?end($res[$name]):NULL;}function
getAnnotations(){return
NAnnotationsParser::getAll($this);}function
getDescription(){return$this->getAnnotation('description');}function
getReflection(){return
new
NClassReflection($this);}function
__call($name,$args){return
NObjectMixin::call($this,$name,$args);}function&__get($name){return
NObjectMixin::get($this,$name);}function
__set($name,$value){NObjectMixin::set($this,$name,$value);}function
__isset($name){return
NObjectMixin::has($this,$name);}function
__unset($name){NObjectMixin::remove($this,$name);}}class
NPresenterComponentReflection
extends
NClassReflection{private
static$ppCache=array();private
static$pcCache=array();private
static$mcCache=array();function
getPersistentParams($class=NULL){$class=$class===NULL?$this->getName():$class;$params=&self::$ppCache[$class];if($params!==NULL){return$params;}$params=array();if(is_subclass_of($class,'NPresenterComponent')){$defaults=get_class_vars($class);foreach(call_user_func(array($class,'getPersistentParams'),$class)as$name=>$meta){if(is_string($meta)){$name=$meta;}$params[$name]=array('def'=>$defaults[$name],'since'=>$class);}foreach($this->getPersistentParams(get_parent_class($class))as$name=>$param){if(isset($params[$name])){$params[$name]['since']=$param['since'];continue;}$params[$name]=$param;}}return$params;}function
getPersistentComponents($class=NULL){$class=$class===NULL?$this->getName():$class;$components=&self::$pcCache[$class];if($components!==NULL){return$components;}$components=array();if(is_subclass_of($class,'NPresenter')){foreach(call_user_func(array($class,'getPersistentComponents'),$class)as$name=>$meta){if(is_string($meta)){$name=$meta;}$components[$name]=array('since'=>$class);}$components=$this->getPersistentComponents(get_parent_class($class))+$components;}return$components;}function
hasCallableMethod($method){$class=$this->getName();$cache=&self::$mcCache[strtolower($class.':'.$method)];if($cache===NULL)try{$cache=FALSE;$rm=NMethodReflection::from($class,$method);$cache=$this->isInstantiable()&&$rm->isPublic()&&!$rm->isAbstract()&&!$rm->isStatic();}catch(ReflectionException$e){}return$cache;}static
function
combineArgs(ReflectionFunctionAbstract$method,$args){$res=array();$i=0;foreach($method->getParameters()as$param){$name=$param->getName();if(isset($args[$name])){$res[$i++]=$args[$name];$type=$param->isArray()?'array':($param->isDefaultValueAvailable()&&$param->isOptional()?gettype($param->getDefaultValue()):'NULL');if(!self::convertType($res[$i-1],$type)){$mName=$method
instanceof
ReflectionMethod?$method->getDeclaringClass()->getName().'::'.$method->getName():$method->getName();throw
new
NBadRequestException("Invalid value for parameter '$name' in method $mName(), expected ".($type==='NULL'?'scalar':$type).".");}}else{$res[$i++]=$param->isDefaultValueAvailable()&&$param->isOptional()?$param->getDefaultValue():($param->isArray()?array():NULL);}}return$res;}static
function
convertType(&$val,$type){if($val===NULL||is_object($val)){}elseif($type==='array'){if(!is_array($val)){return
FALSE;}}elseif(!is_scalar($val)){return
FALSE;}elseif($type!=='NULL'){$old=$val=($val===FALSE?'0':(string)$val);settype($val,$type);if($old!==($val===FALSE?'0':(string)$val)){return
FALSE;}}return
TRUE;}}class
NCache
extends
NObject
implements
ArrayAccess{const
PRIORITY='priority',EXPIRATION='expire',EXPIRE='expire',SLIDING='sliding',TAGS='tags',FILES='files',ITEMS='items',CONSTS='consts',CALLBACKS='callbacks',ALL='all';const
NAMESPACE_SEPARATOR="\x00";private$storage;private$namespace;private$key;private$data;function
__construct(ICacheStorage$storage,$namespace=NULL){$this->storage=$storage;$this->namespace=$namespace.self::NAMESPACE_SEPARATOR;}function
getStorage(){return$this->storage;}function
getNamespace(){return(string)substr($this->namespace,0,-1);}function
derive($namespace){$derived=new
self($this->storage,$this->namespace.$namespace);return$derived;}function
load($key,$fallback=NULL){$data=$this->storage->read($this->generateKey($key));if($data===NULL&&$fallback){return$this->save($key,new
NCallback($fallback));}return$data;}function
save($key,$data,array$dependencies=NULL){$this->release();$key=$this->generateKey($key);if($data
instanceof
NCallback||$data
instanceof
Closure){$this->storage->lock($key);$data=NCallback::create($data)->invokeArgs(array(&$dependencies));}if($data===NULL){$this->storage->remove($key);}else{$this->storage->write($key,$data,$this->completeDependencies($dependencies,$data));return$data;}}private
function
completeDependencies($dp,$data){if(is_object($data)){$dp[self::CALLBACKS][]=array(array(__CLASS__,'checkSerializationVersion'),get_class($data),NClassReflection::from($data)->getAnnotation('serializationVersion'));}if(isset($dp[NCache::EXPIRATION])){$dp[NCache::EXPIRATION]=NDateTime53::from($dp[NCache::EXPIRATION])->format('U')-time();}if(isset($dp[self::FILES])){foreach(array_unique((array)$dp[self::FILES])as$item){$dp[self::CALLBACKS][]=array(array(__CLASS__,'checkFile'),$item,@filemtime($item));}unset($dp[self::FILES]);}if(isset($dp[self::ITEMS])){$dp[self::ITEMS]=array_unique(array_map(array($this,'generateKey'),(array)$dp[self::ITEMS]));}if(isset($dp[self::CONSTS])){foreach(array_unique((array)$dp[self::CONSTS])as$item){$dp[self::CALLBACKS][]=array(array(__CLASS__,'checkConst'),$item,constant($item));}unset($dp[self::CONSTS]);}if(!is_array($dp)){$dp=array();}return$dp;}function
remove($key){$this->save($key,NULL);}function
clean(array$conditions=NULL){$this->release();$this->storage->clean((array)$conditions);}function
call($function){$key=func_get_args();return$this->load($key,create_function('','extract($GLOBALS[0]['.array_push($GLOBALS[0],array('function'=>$function,'key'=>$key)).'-1], EXTR_REFS);
			return NCallback::create($function)->invokeArgs(array_slice($key, 1));
		'));}function
wrap($function,array$dependencies=NULL){$cache=$this;return
create_function('','extract($GLOBALS[0]['.array_push($GLOBALS[0],array('cache'=>$cache,'function'=>$function,'dependencies'=>$dependencies)).'-1], EXTR_REFS);
			$_args=func_get_args(); $key = array($function, $_args);
			$data = $cache->load($key);
			if ($data === NULL) {
				$data = $cache->save($key, NCallback::create($function)->invokeArgs($key[1]), $dependencies);
			}
			return $data;
		');}function
start($key){$data=$this->load($key);if($data===NULL){return
new
NCachingHelper($this,$key);}echo$data;}protected
function
generateKey($key){return$this->namespace.md5(is_scalar($key)?$key:serialize($key));}function
offsetSet($key,$data){$this->save($key,$data);}function
offsetGet($key){$key=is_scalar($key)?(string)$key:serialize($key);if($this->key!==$key){$this->key=$key;$this->data=$this->load($key);}return$this->data;}function
offsetExists($key){$this->release();return$this->offsetGet($key)!==NULL;}function
offsetUnset($key){$this->save($key,NULL);}function
release(){$this->key=$this->data=NULL;}static
function
checkCallbacks($callbacks){foreach($callbacks
as$callback){if(!call_user_func_array(array_shift($callback),$callback)){return
FALSE;}}return
TRUE;}private
static
function
checkConst($const,$value){return
defined($const)&&constant($const)===$value;}private
static
function
checkFile($file,$time){return@filemtime($file)==$time;}private
static
function
checkSerializationVersion($class,$value){return
NClassReflection::from($class)->getAnnotation('serializationVersion')===$value;}}class
NCachingHelper
extends
NObject{public$dependencies;private$cache;private$key;function
__construct(NCache$cache,$key){$this->cache=$cache;$this->key=$key;ob_start();}function
end(array$dependencies=NULL){if($this->cache===NULL){throw
new
InvalidStateException('Output cache has already been saved.');}$this->cache->save($this->key,ob_get_flush(),(array)$dependencies+(array)$this->dependencies);$this->cache=NULL;}}class
NDevNullStorage
extends
NObject
implements
ICacheStorage{function
read($key){}function
lock($key){}function
write($key,$data,array$dependencies){}function
remove($key){}function
clean(array$conditions){}}class
NFileJournal
extends
NObject
implements
ICacheJournal{const
FILE='btfj.dat';const
FILE_MAGIC=0x6274666A;const
INDEX_MAGIC=0x696E6465;const
DATA_MAGIC=0x64617461;const
NODE_SIZE=4096;const
BITROT=12;const
HEADER_SIZE=4096;const
INT32_SIZE=4;const
INFO='i',TYPE='t',IS_LEAF='il',PREV_NODE='p',END='e',MAX='m',INDEX_DATA='id',LAST_INDEX='l';const
TAGS='t',PRIORITY='p',ENTRIES='e';const
DATA='d',KEY='k',DELETED='d';public
static$debug=FALSE;private$file;private$handle;private$lastNode=2;private$processIdentifier;private$nodeCache=array();private$nodeChanged=array();private$toCommit=array();private$deletedLinks=array();private$dataNodeFreeSpace=array();private
static$startNode=array(self::TAGS=>0,self::PRIORITY=>1,self::ENTRIES=>2,self::DATA=>3);function
__construct($dir){$this->file=$dir.'/'.self::FILE;}function
__destruct(){if($this->handle){$this->headerCommit();flock($this->handle,LOCK_UN);fclose($this->handle);$this->handle=FALSE;}}function
write($key,array$dependencies){$this->lock();$priority=!isset($dependencies[NCache::PRIORITY])?FALSE:(int)$dependencies[NCache::PRIORITY];$tags=empty($dependencies[NCache::TAGS])?FALSE:(array)$dependencies[NCache::TAGS];$exists=FALSE;$keyHash=crc32($key);list($entriesNodeId,$entriesNode)=$this->findIndexNode(self::ENTRIES,$keyHash);if(isset($entriesNode[$keyHash])){$entries=$this->mergeIndexData($entriesNode[$keyHash]);foreach($entries
as$link=>$foo){$dataNode=$this->getNode($link>>self::BITROT);if($dataNode[$link][self::KEY]===$key){if($dataNode[$link][self::TAGS]==$tags&&$dataNode[$link][self::PRIORITY]===$priority){if($dataNode[$link][self::DELETED]){$dataNode[$link][self::DELETED]=FALSE;$this->saveNode($link>>self::BITROT,$dataNode);}$exists=TRUE;}else{$toDelete=array();foreach($dataNode[$link][self::TAGS]as$tag){$toDelete[self::TAGS][$tag][$link]=TRUE;}if($dataNode[$link][self::PRIORITY]!==FALSE){$toDelete[self::PRIORITY][$dataNode[$link][self::PRIORITY]][$link]=TRUE;}$toDelete[self::ENTRIES][$keyHash][$link]=TRUE;$this->cleanFromIndex($toDelete);unset($dataNode[$link]);$this->saveNode($link>>self::BITROT,$dataNode);list($entriesNodeId,$entriesNode)=$this->findIndexNode(self::ENTRIES,$keyHash);}break;}}}if($exists===FALSE){$requiredSize=strlen($key)+75;if($tags){foreach($tags
as$tag){$requiredSize+=strlen($tag)+13;}}$requiredSize+=$priority?10:1;$freeDataNode=$this->findFreeDataNode($requiredSize);$data=$this->getNode($freeDataNode);if($data===FALSE){$data=array(self::INFO=>array(self::LAST_INDEX=>($freeDataNode<<self::BITROT),self::TYPE=>self::DATA));}$dataNodeKey=$this->findNextFreeKey($freeDataNode,$data);$data[$dataNodeKey]=array(self::KEY=>$key,self::TAGS=>$tags?$tags:array(),self::PRIORITY=>$priority,self::DELETED=>FALSE);$this->saveNode($freeDataNode,$data);$entriesNode[$keyHash][$dataNodeKey]=1;$this->saveNode($entriesNodeId,$entriesNode);if($tags){foreach($tags
as$tag){list($nodeId,$node)=$this->findIndexNode(self::TAGS,$tag);$node[$tag][$dataNodeKey]=1;$this->saveNode($nodeId,$node);}}if($priority!==FALSE){list($nodeId,$node)=$this->findIndexNode(self::PRIORITY,$priority);$node[$priority][$dataNodeKey]=1;$this->saveNode($nodeId,$node);}}$this->commit();$this->unlock();}function
clean(array$conditions){$this->lock();if(!empty($conditions[NCache::ALL])){$this->nodeCache=$this->nodeChanged=$this->dataNodeFreeSpace=array();$this->deleteAll();$this->unlock();return
NULL;}$toDelete=array(self::TAGS=>array(),self::PRIORITY=>array(),self::ENTRIES=>array());$entries=array();if(!empty($conditions[NCache::TAGS])){$entries=$this->cleanTags((array)$conditions[NCache::TAGS],$toDelete);}if(isset($conditions[NCache::PRIORITY])){$this->arrayAppend($entries,$this->cleanPriority((int)$conditions[NCache::PRIORITY],$toDelete));}$this->deletedLinks=array();$this->cleanFromIndex($toDelete);$this->commit();$this->unlock();return$entries;}private
function
cleanTags(array$tags,array&$toDelete){$entries=array();foreach($tags
as$tag){list(,$node)=$this->findIndexNode(self::TAGS,$tag);if(isset($node[$tag])){$ent=$this->cleanLinks($this->mergeIndexData($node[$tag]),$toDelete);$this->arrayAppend($entries,$ent);}}return$entries;}private
function
cleanPriority($priority,array&$toDelete){list(,$node)=$this->findIndexNode(self::PRIORITY,$priority);ksort($node);$allData=array();foreach($node
as$prior=>$data){if($prior===self::INFO){continue;}elseif($prior>$priority){break;}$this->arrayAppendKeys($allData,$this->mergeIndexData($data));}$nodeInfo=$node[self::INFO];while($nodeInfo[self::PREV_NODE]!==-1){$nodeId=$nodeInfo[self::PREV_NODE];$node=$this->getNode($nodeId);if($node===FALSE){if(self::$debug){throw
new
InvalidStateException("Cannot load node number $nodeId.");}break;}$nodeInfo=$node[self::INFO];unset($node[self::INFO]);foreach($node
as$data){$this->arrayAppendKeys($allData,$this->mergeIndexData($data));}}return$this->cleanLinks($allData,$toDelete);}private
function
cleanLinks(array$data,array&$toDelete){$return=array();$data=array_keys($data);sort($data);$max=count($data);$data[]=0;$i=0;while($i<$max){$searchLink=$data[$i];if(isset($this->deletedLinks[$searchLink])){++$i;continue;}$nodeId=$searchLink>>self::BITROT;$node=$this->getNode($nodeId);if($node===FALSE){if(self::$debug){throw
new
InvalidStateException("Cannot load node number $nodeId.");}++$i;continue;}do{$link=$data[$i];if(!isset($node[$link])){if(self::$debug){throw
new
InvalidStateException("Link with ID $searchLink is not in node $nodeId.");}continue;}elseif(isset($this->deletedLinks[$link])){continue;}$nodeLink=&$node[$link];if(!$nodeLink[self::DELETED]){$nodeLink[self::DELETED]=TRUE;$return[]=$nodeLink[self::KEY];}else{foreach($nodeLink[self::TAGS]as$tag){$toDelete[self::TAGS][$tag][$link]=TRUE;}if($nodeLink[self::PRIORITY]!==FALSE){$toDelete[self::PRIORITY][$nodeLink[self::PRIORITY]][$link]=TRUE;}$toDelete[self::ENTRIES][crc32($nodeLink[self::KEY])][$link]=TRUE;unset($node[$link]);$this->deletedLinks[$link]=TRUE;}}while(($data[++$i]>>self::BITROT)===$nodeId);$this->saveNode($nodeId,$node);}return$return;}private
function
cleanFromIndex(array$toDeleteFromIndex){foreach($toDeleteFromIndex
as$type=>$toDelete){ksort($toDelete);while(!empty($toDelete)){reset($toDelete);$searchKey=key($toDelete);list($masterNodeId,$masterNode)=$this->findIndexNode($type,$searchKey);if(!isset($masterNode[$searchKey])){if(self::$debug){throw
new
InvalidStateException('Bad index.');}unset($toDelete[$searchKey]);continue;}foreach($toDelete
as$key=>$links){if(isset($masterNode[$key])){foreach($links
as$link=>$foo){if(isset($masterNode[$key][$link])){unset($masterNode[$key][$link],$links[$link]);}}if(!empty($links)&&isset($masterNode[$key][self::INDEX_DATA])){$this->cleanIndexData($masterNode[$key][self::INDEX_DATA],$links,$masterNode[$key]);}if(empty($masterNode[$key])){unset($masterNode[$key]);}unset($toDelete[$key]);}else{break;}}$this->saveNode($masterNodeId,$masterNode);}}}private
function
mergeIndexData(array$data){while(isset($data[self::INDEX_DATA])){$id=$data[self::INDEX_DATA];unset($data[self::INDEX_DATA]);$childNode=$this->getNode($id);if($childNode===FALSE){if(self::$debug){throw
new
InvalidStateException("Cannot load node number $id.");}break;}$this->arrayAppendKeys($data,$childNode[self::INDEX_DATA]);}return$data;}private
function
cleanIndexData($nextNodeId,array$links,&$masterNodeLink){$prev=-1;while($nextNodeId&&!empty($links)){$nodeId=$nextNodeId;$node=$this->getNode($nodeId);if($node===FALSE){if(self::$debug){throw
new
InvalidStateException("Cannot load node number $nodeId.");}break;}foreach($links
as$link=>$foo){if(isset($node[self::INDEX_DATA][$link])){unset($node[self::INDEX_DATA][$link],$links[$link]);}}if(isset($node[self::INDEX_DATA][self::INDEX_DATA])){$nextNodeId=$node[self::INDEX_DATA][self::INDEX_DATA];}else{$nextNodeId=FALSE;}if(empty($node[self::INDEX_DATA])||(count($node[self::INDEX_DATA])===1&&$nextNodeId)){if($prev===-1){if($nextNodeId===FALSE){unset($masterNodeLink[self::INDEX_DATA]);}else{$masterNodeLink[self::INDEX_DATA]=$nextNodeId;}}else{$prevNode=$this->getNode($prev);if($prevNode===FALSE){if(self::$debug){throw
new
InvalidStateException("Cannot load node number $prev.");}}else{if($nextNodeId===FALSE){unset($prevNode[self::INDEX_DATA][self::INDEX_DATA]);if(empty($prevNode[self::INDEX_DATA])){unset($prevNode[self::INDEX_DATA]);}}else{$prevNode[self::INDEX_DATA][self::INDEX_DATA]=$nextNodeId;}$this->saveNode($prev,$prevNode);}}unset($node[self::INDEX_DATA]);}else{$prev=$nodeId;}$this->saveNode($nodeId,$node);}}private
function
getNode($id){if(isset($this->nodeCache[$id])){return$this->nodeCache[$id];}$binary=stream_get_contents($this->handle,self::NODE_SIZE,self::HEADER_SIZE+self::NODE_SIZE*$id);if(empty($binary)){return
FALSE;}list(,$magic,$length)=unpack('N2',$binary);if($magic!==self::INDEX_MAGIC&&$magic!==self::DATA_MAGIC){if(!empty($magic)){if(self::$debug){throw
new
InvalidStateException("Node $id has malformed header.");}$this->deleteNode($id);}return
FALSE;}$data=substr($binary,2*self::INT32_SIZE,$length-2*self::INT32_SIZE);$node=@unserialize($data);if($node===FALSE){$this->deleteNode($id);if(self::$debug){throw
new
InvalidStateException("Cannot unserialize node number $id.");}return
FALSE;}return$this->nodeCache[$id]=$node;}private
function
saveNode($id,array$node){if(count($node)===1){$nodeInfo=$node[self::INFO];if($nodeInfo[self::TYPE]!==self::DATA){if($nodeInfo[self::END]!==-1){$this->nodeCache[$id]=$node;$this->nodeChanged[$id]=TRUE;return;}if($nodeInfo[self::MAX]===-1){$max=PHP_INT_MAX;}else{$max=$nodeInfo[self::MAX];}list(,,$parentId)=$this->findIndexNode($nodeInfo[self::TYPE],$max,$id);if($parentId!==-1&&$parentId!==$id){$parentNode=$this->getNode($parentId);if($parentNode===FALSE){if(self::$debug){throw
new
InvalidStateException("Cannot load node number $parentId.");}}else{if($parentNode[self::INFO][self::END]===$id){if(count($parentNode)===1){$parentNode[self::INFO][self::END]=-1;}else{end($parentNode);$lastKey=key($parentNode);$parentNode[self::INFO][self::END]=$parentNode[$lastKey];unset($parentNode[$lastKey]);}}else{unset($parentNode[$nodeInfo[self::MAX]]);}$this->saveNode($parentId,$parentNode);}}if($nodeInfo[self::TYPE]===self::PRIORITY){if($nodeInfo[self::MAX]===-1){if($nodeInfo[self::PREV_NODE]!==-1){$prevNode=$this->getNode($nodeInfo[self::PREV_NODE]);if($prevNode===FALSE){if(self::$debug){throw
new
InvalidStateException("Cannot load node number {$nodeInfo[self::PREV_NODE]}.");}}else{$prevNode[self::INFO][self::MAX]=-1;$this->saveNode($nodeInfo[self::PREV_NODE],$prevNode);}}}else{list($nextId,$nextNode)=$this->findIndexNode($nodeInfo[self::TYPE],$nodeInfo[self::MAX]+1,NULL,$id);if($nextId!==-1&&$nextId!==$id){$nextNode[self::INFO][self::PREV_NODE]=$nodeInfo[self::PREV_NODE];$this->saveNode($nextId,$nextNode);}}}}$this->nodeCache[$id]=FALSE;}else{$this->nodeCache[$id]=$node;}$this->nodeChanged[$id]=TRUE;}private
function
commit(){do{foreach($this->nodeChanged
as$id=>$foo){if($this->prepareNode($id,$this->nodeCache[$id])){unset($this->nodeChanged[$id]);}}}while(!empty($this->nodeChanged));foreach($this->toCommit
as$node=>$str){$this->commitNode($node,$str);}$this->toCommit=array();}private
function
prepareNode($id,$node){if($node===FALSE){if($id<$this->lastNode){$this->lastNode=$id;}unset($this->nodeCache[$id]);unset($this->dataNodeFreeSpace[$id]);$this->deleteNode($id);return
TRUE;}$data=serialize($node);$dataSize=strlen($data)+2*self::INT32_SIZE;$isData=$node[self::INFO][self::TYPE]===self::DATA;if($dataSize>self::NODE_SIZE){if($isData){throw
new
InvalidStateException('Saving node is bigger than maximum node size.');}else{$this->bisectNode($id,$node);return
FALSE;}}$this->toCommit[$id]=pack('N2',$isData?self::DATA_MAGIC:self::INDEX_MAGIC,$dataSize).$data;if($this->lastNode<$id){$this->lastNode=$id;}if($isData){$this->dataNodeFreeSpace[$id]=self::NODE_SIZE-$dataSize;}return
TRUE;}private
function
commitNode($id,$str){fseek($this->handle,self::HEADER_SIZE+self::NODE_SIZE*$id);$written=fwrite($this->handle,$str);if($written===FALSE){throw
new
InvalidStateException("Cannot write node number $id to journal.");}}private
function
findIndexNode($type,$search,$childId=NULL,$prevId=NULL){$nodeId=self::$startNode[$type];$parentId=-1;while(TRUE){$node=$this->getNode($nodeId);if($node===FALSE){return
array($nodeId,array(self::INFO=>array(self::TYPE=>$type,self::IS_LEAF=>TRUE,self::PREV_NODE=>-1,self::END=>-1,self::MAX=>-1)),$parentId);}if($node[self::INFO][self::IS_LEAF]||$nodeId===$childId||$node[self::INFO][self::PREV_NODE]===$prevId){return
array($nodeId,$node,$parentId);}$parentId=$nodeId;if(isset($node[$search])){$nodeId=$node[$search];}else{foreach($node
as$key=>$childNode){if($key>$search
and$key!==self::INFO){$nodeId=$childNode;continue
2;}}$nodeId=$node[self::INFO][self::END];}}}private
function
findFreeNode($count=1){$id=$this->lastNode;$nodesId=array();do{if(isset($this->nodeCache[$id])){++$id;continue;}$offset=self::HEADER_SIZE+self::NODE_SIZE*$id;$binary=stream_get_contents($this->handle,self::INT32_SIZE,$offset);if(empty($binary)){$nodesId[]=$id;}else{list(,$magic)=unpack('N',$binary);if($magic!==self::INDEX_MAGIC&&$magic!==self::DATA_MAGIC){$nodesId[]=$id;}}++$id;}while(count($nodesId)!==$count);if($count===1){return$nodesId[0];}else{return$nodesId;}}private
function
findFreeDataNode($size){foreach($this->dataNodeFreeSpace
as$id=>$freeSpace){if($freeSpace>$size){return$id;}}$id=self::$startNode[self::DATA];while(TRUE){if(isset($this->dataNodeFreeSpace[$id])||isset($this->nodeCache[$id])){++$id;continue;}$offset=self::HEADER_SIZE+self::NODE_SIZE*$id;$binary=stream_get_contents($this->handle,2*self::INT32_SIZE,$offset);if(empty($binary)){$this->dataNodeFreeSpace[$id]=self::NODE_SIZE;return$id;}list(,$magic,$nodeSize)=unpack('N2',$binary);if(empty($magic)){$this->dataNodeFreeSpace[$id]=self::NODE_SIZE;return$id;}elseif($magic===self::DATA_MAGIC){$freeSpace=self::NODE_SIZE-$nodeSize;$this->dataNodeFreeSpace[$id]=$freeSpace;if($freeSpace>$size){return$id;}}++$id;}}private
function
bisectNode($id,array$node){$nodeInfo=$node[self::INFO];unset($node[self::INFO]);if(count($node)===1){$key=key($node);$dataId=$this->findFreeDataNode(self::NODE_SIZE);$this->saveNode($dataId,array(self::INDEX_DATA=>$node[$key],self::INFO=>array(self::TYPE=>self::DATA,self::LAST_INDEX=>($dataId<<self::BITROT))));unset($node[$key]);$node[$key][self::INDEX_DATA]=$dataId;$node[self::INFO]=$nodeInfo;$this->saveNode($id,$node);return;}ksort($node);$halfCount=ceil(count($node)/2);list($first,$second)=array_chunk($node,$halfCount,TRUE);end($first);$halfKey=key($first);if($id<=2){list($firstId,$secondId)=$this->findFreeNode(2);$first[self::INFO]=array(self::TYPE=>$nodeInfo[self::TYPE],self::IS_LEAF=>$nodeInfo[self::IS_LEAF],self::PREV_NODE=>-1,self::END=>-1,self::MAX=>$halfKey);$this->saveNode($firstId,$first);$second[self::INFO]=array(self::TYPE=>$nodeInfo[self::TYPE],self::IS_LEAF=>$nodeInfo[self::IS_LEAF],self::PREV_NODE=>$firstId,self::END=>$nodeInfo[self::END],self::MAX=>-1);$this->saveNode($secondId,$second);$parentNode=array(self::INFO=>array(self::TYPE=>$nodeInfo[self::TYPE],self::IS_LEAF=>FALSE,self::PREV_NODE=>-1,self::END=>$secondId,self::MAX=>-1),$halfKey=>$firstId);$this->saveNode($id,$parentNode);}else{$firstId=$this->findFreeNode();$first[self::INFO]=array(self::TYPE=>$nodeInfo[self::TYPE],self::IS_LEAF=>$nodeInfo[self::IS_LEAF],self::PREV_NODE=>$nodeInfo[self::PREV_NODE],self::END=>-1,self::MAX=>$halfKey);$this->saveNode($firstId,$first);$second[self::INFO]=array(self::TYPE=>$nodeInfo[self::TYPE],self::IS_LEAF=>$nodeInfo[self::IS_LEAF],self::PREV_NODE=>$firstId,self::END=>$nodeInfo[self::END],self::MAX=>$nodeInfo[self::MAX]);$this->saveNode($id,$second);list(,,$parent)=$this->findIndexNode($nodeInfo[self::TYPE],$halfKey);$parentNode=$this->getNode($parent);if($parentNode===FALSE){if(self::$debug){throw
new
InvalidStateException("Cannot load node number $parent.");}}else{$parentNode[$halfKey]=$firstId;ksort($parentNode);$this->saveNode($parent,$parentNode);}}}private
function
headerCommit(){fseek($this->handle,self::INT32_SIZE);@fwrite($this->handle,pack('N',$this->lastNode));}private
function
deleteNode($id){fseek($this->handle,0,SEEK_END);$end=ftell($this->handle);if($end<=(self::HEADER_SIZE+self::NODE_SIZE*($id+1))){$packedNull=pack('N',0);do{$binary=stream_get_contents($this->handle,self::INT32_SIZE,(self::HEADER_SIZE+self::NODE_SIZE*--$id));}while(empty($binary)||$binary===$packedNull);if(!ftruncate($this->handle,self::HEADER_SIZE+self::NODE_SIZE*($id+1))){throw
new
InvalidStateException('Cannot truncate journal file.');}}else{fseek($this->handle,self::HEADER_SIZE+self::NODE_SIZE*$id);$written=fwrite($this->handle,pack('N',0));if($written!==self::INT32_SIZE){throw
new
InvalidStateException("Cannot delete node number $id from journal.");}}}private
function
deleteAll(){if(!ftruncate($this->handle,self::HEADER_SIZE)){throw
new
InvalidStateException('Cannot truncate journal file.');}}private
function
lock(){if(!$this->handle){$this->prepare();}if(!flock($this->handle,LOCK_EX)){throw
new
InvalidStateException("Cannot acquire exclusive lock on journal file '$this->file'.");}$lastProcessIdentifier=stream_get_contents($this->handle,self::INT32_SIZE,self::INT32_SIZE*2);if($lastProcessIdentifier!==$this->processIdentifier){$this->nodeCache=$this->dataNodeFreeSpace=array();fseek($this->handle,self::INT32_SIZE*2);fwrite($this->handle,$this->processIdentifier);}}private
function
prepare(){if(!file_exists($this->file)){$init=@fopen($this->file,'xb');if(!$init){clearstatcache();if(!file_exists($this->file)){throw
new
InvalidStateException("Cannot create journal file '$this->file'.");}}else{$written=fwrite($init,pack('N2',self::FILE_MAGIC,$this->lastNode));fclose($init);if($written!==self::INT32_SIZE*2){throw
new
InvalidStateException("Cannot write journal header.");}}}$this->handle=fopen($this->file,'r+b');if(!$this->handle){throw
new
InvalidStateException("Cannot open journal file '$this->file'.");}if(!flock($this->handle,LOCK_SH)){throw
new
InvalidStateException('Cannot acquire shared lock on journal.');}$header=stream_get_contents($this->handle,2*self::INT32_SIZE,0);flock($this->handle,LOCK_UN);list(,$fileMagic,$this->lastNode)=unpack('N2',$header);if($fileMagic!==self::FILE_MAGIC){fclose($this->handle);$this->handle=FALSE;throw
new
InvalidStateException("Malformed journal file '$this->file'.");}$this->processIdentifier=pack('N',mt_rand());}private
function
unlock(){if($this->handle){fflush($this->handle);flock($this->handle,LOCK_UN);}}private
function
findNextFreeKey($nodeId,array&$nodeData){$newKey=$nodeData[self::INFO][self::LAST_INDEX]+1;$maxKey=($nodeId+1)<<self::BITROT;if($newKey>=$maxKey){$start=$nodeId<<self::BITROT;for($i=$start;$i<$maxKey;$i++){if(!isset($nodeData[$i])){return$i;}}throw
new
InvalidStateException("Node $nodeId is full.");}else{return++$nodeData[self::INFO][self::LAST_INDEX];}}private
function
arrayAppend(array&$array,array$append){foreach($append
as$value){$array[]=$value;}}private
function
arrayAppendKeys(array&$array,array$append){foreach($append
as$key=>$value){$array[$key]=$value;}}}class
NFileStorage
extends
NObject
implements
ICacheStorage{const
META_HEADER_LEN=28,META_TIME='time',META_SERIALIZED='serialized',META_EXPIRE='expire',META_DELTA='delta',META_ITEMS='di',META_CALLBACKS='callbacks';const
FILE='file',HANDLE='handle';public
static$gcProbability=0.001;public
static$useDirectories=TRUE;private$dir;private$useDirs;private$journal;private$locks;function
__construct($dir,ICacheJournal$journal=NULL){$this->dir=realpath($dir);if($this->dir===FALSE){throw
new
DirectoryNotFoundException("Directory '$dir' not found.");}$this->useDirs=(bool)self::$useDirectories;$this->journal=$journal;if(mt_rand()/mt_getrandmax()<self::$gcProbability){$this->clean(array());}}function
read($key){$meta=$this->readMetaAndLock($this->getCacheFile($key),LOCK_SH);if($meta&&$this->verify($meta)){return$this->readData($meta);}else{return
NULL;}}private
function
verify($meta){do{if(!empty($meta[self::META_DELTA])){if(filemtime($meta[self::FILE])+$meta[self::META_DELTA]<time()){break;}touch($meta[self::FILE]);}elseif(!empty($meta[self::META_EXPIRE])&&$meta[self::META_EXPIRE]<time()){break;}if(!empty($meta[self::META_CALLBACKS])&&!NCache::checkCallbacks($meta[self::META_CALLBACKS])){break;}if(!empty($meta[self::META_ITEMS])){foreach($meta[self::META_ITEMS]as$depFile=>$time){$m=$this->readMetaAndLock($depFile,LOCK_SH);if($m[self::META_TIME]!==$time||($m&&!$this->verify($m))){break
2;}}}return
TRUE;}while(FALSE);$this->delete($meta[self::FILE],$meta[self::HANDLE]);return
FALSE;}function
lock($key){$cacheFile=$this->getCacheFile($key);if($this->useDirs&&!is_dir($dir=dirname($cacheFile))){@mkdir($dir);}$handle=@fopen($cacheFile,'r+b');if(!$handle){$handle=fopen($cacheFile,'wb');if(!$handle){return;}}$this->locks[$key]=$handle;flock($handle,LOCK_EX);}function
write($key,$data,array$dp){$meta=array(self::META_TIME=>microtime());if(isset($dp[NCache::EXPIRATION])){if(empty($dp[NCache::SLIDING])){$meta[self::META_EXPIRE]=$dp[NCache::EXPIRATION]+time();}else{$meta[self::META_DELTA]=(int)$dp[NCache::EXPIRATION];}}if(isset($dp[NCache::ITEMS])){foreach((array)$dp[NCache::ITEMS]as$item){$depFile=$this->getCacheFile($item);$m=$this->readMetaAndLock($depFile,LOCK_SH);$meta[self::META_ITEMS][$depFile]=$m[self::META_TIME];unset($m);}}if(isset($dp[NCache::CALLBACKS])){$meta[self::META_CALLBACKS]=$dp[NCache::CALLBACKS];}if(!isset($this->locks[$key])){$this->lock($key);if(!isset($this->locks[$key])){return;}}$handle=$this->locks[$key];unset($this->locks[$key]);$cacheFile=$this->getCacheFile($key);if(isset($dp[NCache::TAGS])||isset($dp[NCache::PRIORITY])){if(!$this->journal){throw
new
InvalidStateException('CacheJournal has not been provided.');}$this->journal->write($cacheFile,$dp);}ftruncate($handle,0);if(!is_string($data)){$data=serialize($data);$meta[self::META_SERIALIZED]=TRUE;}$head=serialize($meta).'?>';$head='<?php //netteCache[01]'.str_pad((string)strlen($head),6,'0',STR_PAD_LEFT).$head;$headLen=strlen($head);$dataLen=strlen($data);do{if(fwrite($handle,str_repeat("\x00",$headLen),$headLen)!==$headLen){break;}if(fwrite($handle,$data,$dataLen)!==$dataLen){break;}fseek($handle,0);if(fwrite($handle,$head,$headLen)!==$headLen){break;}flock($handle,LOCK_UN);fclose($handle);return;}while(FALSE);$this->delete($cacheFile,$handle);}function
remove($key){unset($this->locks[$key]);$this->delete($this->getCacheFile($key));}function
clean(array$conditions){$all=!empty($conditions[NCache::ALL]);$collector=empty($conditions);if($all||$collector){$now=time();foreach(NFinder::find('_*')->from($this->dir)->childFirst()as$entry){$path=(string)$entry;if($entry->isDir()){@rmdir($path);continue;}if($all){$this->delete($path);}else{$meta=$this->readMetaAndLock($path,LOCK_SH);if(!$meta){continue;}if((!empty($meta[self::META_DELTA])&&filemtime($meta[self::FILE])+$meta[self::META_DELTA]<$now)||(!empty($meta[self::META_EXPIRE])&&$meta[self::META_EXPIRE]<$now)){$this->delete($path,$meta[self::HANDLE]);continue;}flock($meta[self::HANDLE],LOCK_UN);fclose($meta[self::HANDLE]);}}if($this->journal){$this->journal->clean($conditions);}return;}if($this->journal){foreach($this->journal->clean($conditions)as$file){$this->delete($file);}}}protected
function
readMetaAndLock($file,$lock){$handle=@fopen($file,'r+b');if(!$handle){return
NULL;}flock($handle,$lock);$head=stream_get_contents($handle,self::META_HEADER_LEN);if($head&&strlen($head)===self::META_HEADER_LEN){$size=(int)substr($head,-6);$meta=stream_get_contents($handle,$size,self::META_HEADER_LEN);$meta=@unserialize($meta);if(is_array($meta)){fseek($handle,$size+self::META_HEADER_LEN);$meta[self::FILE]=$file;$meta[self::HANDLE]=$handle;return$meta;}}flock($handle,LOCK_UN);fclose($handle);return
NULL;}protected
function
readData($meta){$data=stream_get_contents($meta[self::HANDLE]);flock($meta[self::HANDLE],LOCK_UN);fclose($meta[self::HANDLE]);if(empty($meta[self::META_SERIALIZED])){return$data;}else{return@unserialize($data);}}protected
function
getCacheFile($key){$file=urlencode($key);if($this->useDirs&&$a=strrpos($file,'%00')){$file=substr_replace($file,'/_',$a,3);}return$this->dir.'/_'.$file;}private
static
function
delete($file,$handle=NULL){if(@unlink($file)){if($handle){flock($handle,LOCK_UN);fclose($handle);}return;}if(!$handle){$handle=@fopen($file,'r+');}if($handle){flock($handle,LOCK_EX);ftruncate($handle,0);flock($handle,LOCK_UN);fclose($handle);@unlink($file);}}}class
NMemcachedStorage
extends
NObject
implements
ICacheStorage{const
META_CALLBACKS='callbacks',META_DATA='data',META_DELTA='delta';private$memcache;private$prefix;private$journal;static
function
isAvailable(){return
extension_loaded('memcache');}function
__construct($host='localhost',$port=11211,$prefix='',ICacheJournal$journal=NULL){if(!self::isAvailable()){throw
new
NotSupportedException("PHP extension 'memcache' is not loaded.");}$this->prefix=$prefix;$this->journal=$journal;$this->memcache=new
Memcache;if($host){$this->addServer($host,$port);}}function
addServer($host='localhost',$port=11211,$timeout=1){if($this->memcache->addServer($host,$port,TRUE,1,$timeout)===FALSE){$error=error_get_last();throw
new
InvalidStateException("Memcache::addServer(): $error[message].");}}function
getConnection(){return$this->memcache;}function
read($key){$key=$this->prefix.$key;$meta=$this->memcache->get($key);if(!$meta){return
NULL;}if(!empty($meta[self::META_CALLBACKS])&&!NCache::checkCallbacks($meta[self::META_CALLBACKS])){$this->memcache->delete($key,0);return
NULL;}if(!empty($meta[self::META_DELTA])){$this->memcache->replace($key,$meta,0,$meta[self::META_DELTA]+time());}return$meta[self::META_DATA];}function
lock($key){}function
write($key,$data,array$dp){if(isset($dp[NCache::ITEMS])){throw
new
NotSupportedException('Dependent items are not supported by MemcachedStorage.');}$key=$this->prefix.$key;$meta=array(self::META_DATA=>$data);$expire=0;if(isset($dp[NCache::EXPIRATION])){$expire=(int)$dp[NCache::EXPIRATION];if(!empty($dp[NCache::SLIDING])){$meta[self::META_DELTA]=$expire;}}if(isset($dp[NCache::CALLBACKS])){$meta[self::META_CALLBACKS]=$dp[NCache::CALLBACKS];}if(isset($dp[NCache::TAGS])||isset($dp[NCache::PRIORITY])){if(!$this->journal){throw
new
InvalidStateException('CacheJournal has not been provided.');}$this->journal->write($key,$dp);}$this->memcache->set($key,$meta,0,$expire);}function
remove($key){$this->memcache->delete($this->prefix.$key,0);}function
clean(array$conditions){if(!empty($conditions[NCache::ALL])){$this->memcache->flush();}elseif($this->journal){foreach($this->journal->clean($conditions)as$entry){$this->memcache->delete($entry,0);}}}}class
NMemoryStorage
extends
NObject
implements
ICacheStorage{private$data=array();function
read($key){return
isset($this->data[$key])?$this->data[$key]:NULL;}function
lock($key){}function
write($key,$data,array$dependencies){$this->data[$key]=$data;}function
remove($key){unset($this->data[$key]);}function
clean(array$conditions){if(!empty($conditions[NCache::ALL])){$this->data=array();}}}class
NPhpFileStorage
extends
NFileStorage{public$hint;protected
function
readData($meta){return
array('file'=>$meta[self::FILE],'handle'=>$meta[self::HANDLE]);}protected
function
getCacheFile($key){return
parent::getCacheFile(substr_replace($key,trim(strtr($this->hint,'\\/@','.._'),'.').'-',strpos($key,NCache::NAMESPACE_SEPARATOR)+1,0)).'.php';}}class
NArrayHash
extends
stdClass
implements
ArrayAccess,Countable,IteratorAggregate{static
function
from($arr,$recursive=TRUE){$obj=new
self;foreach($arr
as$key=>$value){if($recursive&&is_array($value)){$obj->$key=self::from($value,TRUE);}else{$obj->$key=$value;}}return$obj;}function
getIterator(){return
new
RecursiveArrayIterator($this);}function
count(){return
count((array)$this);}function
offsetSet($key,$value){if(!is_scalar($key)){throw
new
InvalidArgumentException(sprintf('Key must be either a string or an integer, %s given.',gettype($key)));}$this->$key=$value;}function
offsetGet($key){return$this->$key;}function
offsetExists($key){return
isset($this->$key);}function
offsetUnset($key){unset($this->$key);}}class
NCallback
extends
NObject{private$cb;static
function
create($callback,$m=NULL){return
new
self($callback,$m);}function
__construct($cb,$m=NULL){if($m!==NULL){$cb=array($cb,$m);}elseif($cb
instanceof
self){$this->cb=$cb->cb;return;}if(PHP_VERSION_ID<50202&&is_string($cb)&&strpos($cb,'::')){$cb=explode('::',$cb,2);}elseif(is_object($cb)&&!$cb
instanceof
NClosure){$cb=array($cb,'__invoke');}if(is_string($this->cb)&&$a=strrpos($this->cb,'\\')){$this->cb=substr($this->cb,$a+1);}elseif(is_array($this->cb)&&is_string($this->cb[0])&&$a=strrpos($this->cb[0],'\\')){$this->cb[0]=substr($this->cb[0],$a+1);}if(!is_callable($cb,TRUE)){throw
new
InvalidArgumentException("Invalid callback.");}$this->cb=$cb;}function
__invoke(){if(!is_callable($this->cb)){throw
new
InvalidStateException("Callback '$this' is not callable.");}$_args=func_get_args();return
call_user_func_array($this->cb,$_args);}function
invoke(){if(!is_callable($this->cb)){throw
new
InvalidStateException("Callback '$this' is not callable.");}$_args=func_get_args();return
call_user_func_array($this->cb,$_args);}function
invokeArgs(array$args){if(!is_callable($this->cb)){throw
new
InvalidStateException("Callback '$this' is not callable.");}return
call_user_func_array($this->cb,$args);}function
isCallable(){return
is_callable($this->cb);}function
getNative(){return$this->cb;}function
toReflection(){if(is_string($this->cb)&&strpos($this->cb,'::')){return
new
NMethodReflection($this->cb);}elseif(is_array($this->cb)){return
new
NMethodReflection($this->cb[0],$this->cb[1]);}elseif(is_object($this->cb)&&!$this->cb
instanceof
Closure){return
new
NMethodReflection($this->cb,'__invoke');}else{return
new
NFunctionReflection($this->cb);}}function
isStatic(){return
is_array($this->cb)?is_string($this->cb[0]):is_string($this->cb);}function
__toString(){if($this->cb
instanceof
Closure){return'{closure}';}elseif(is_string($this->cb)&&$this->cb[0]==="\0"){return'{lambda}';}else{is_callable($this->cb,TRUE,$textual);return$textual;}}}class
NDateTime53
extends
DateTime{const
MINUTE=60;const
HOUR=3600;const
DAY=86400;const
WEEK=604800;const
MONTH=2629800;const
YEAR=31557600;static
function
from($time){if($time
instanceof
DateTime||$time
instanceof
DateTimeInterface){return
new
self($time->format('Y-m-d H:i:s'),$time->getTimezone());}elseif(is_numeric($time)){if($time<=self::YEAR){$time+=time();}$tmp=new
self('@'.$time);$tmp->setTimeZone(new
DateTimeZone(date_default_timezone_get()));return$tmp;}else{return
new
self($time);}}function
__toString(){return$this->format('Y-m-d H:i:s');}function
modifyClone($modify=''){$dolly=clone$this;return$modify?$dolly->modify($modify):$dolly;}function
setTimestamp($timestamp){$zone=PHP_VERSION_ID===50206?new
DateTimeZone($this->getTimezone()->getName()):$this->getTimezone();$this->__construct('@'.$timestamp);$this->setTimeZone($zone);return$this;}function
getTimestamp(){$ts=$this->format('U');return
is_float($tmp=$ts*1)?$ts:$tmp;}function
modify($modify){parent::modify($modify);return$this;}static
function
__set_state($state){return
new
self($state['date'],new
DateTimeZone($state['timezone']));}function
__sleep(){$zone=$this->getTimezone()->getName();if($zone[0]==='+'){$this->fix=array($this->format('Y-m-d H:i:sP'));}else{$this->fix=array($this->format('Y-m-d H:i:s'),$zone);}return
array('fix');}function
__wakeup(){if(isset($this->fix[1])){$this->__construct($this->fix[0],new
DateTimeZone($this->fix[1]));}else{$this->__construct($this->fix[0]);}unset($this->fix);}}class
NEnvironment{const
DEVELOPMENT='development',PRODUCTION='production',CONSOLE='console';private
static$productionMode;private
static$createdAt;private
static$context;final
function
__construct(){throw
new
NStaticClassException;}static
function
isConsole(){return
PHP_SAPI==='cli';}static
function
isProduction(){if(self::$productionMode===NULL){self::$productionMode=!NConfigurator::detectDebugMode();}return
self::$productionMode;}static
function
setProductionMode($value=TRUE){self::$productionMode=(bool)$value;}static
function
setVariable($name,$value,$expand=TRUE){if($expand&&is_string($value)){$value=self::getContext()->expand($value);}self::getContext()->parameters[$name]=$value;}static
function
getVariable($name,$default=NULL){if(isset(self::getContext()->parameters[$name])){return
self::getContext()->parameters[$name];}elseif(func_num_args()>1){return$default;}else{throw
new
InvalidStateException("Unknown environment variable '$name'.");}}static
function
getVariables(){return
self::getContext()->parameters;}static
function
expand($s){return
self::getContext()->expand($s);}static
function
setContext(NDIContainer$context){if(self::$createdAt){throw
new
InvalidStateException('Configurator & SystemContainer has already been created automatically by NEnvironment at '.self::$createdAt);}self::$context=$context;}static
function
getContext(){if(self::$context===NULL){self::loadConfig();}return
self::$context;}static
function
getService($name){return
self::getContext()->getService($name);}static
function
__callStatic($name,$args){if(!$args&&strncasecmp($name,'get',3)===0){return
self::getService(lcfirst(substr($name,3)));}else{throw
new
MemberAccessException("Call to undefined static method NEnvironment::$name().");}}static
function
getHttpRequest(){return
self::getContext()->getByType('IHttpRequest');}static
function
getHttpContext(){return
self::getContext()->getByType('NHttpContext');}static
function
getHttpResponse(){return
self::getContext()->getByType('IHttpResponse');}static
function
getApplication(){return
self::getContext()->getByType('NApplication');}static
function
getUser(){return
self::getContext()->getByType('NUser');}static
function
getRobotLoader(){return
self::getContext()->getByType('NRobotLoader');}static
function
getCache($namespace=''){return
new
NCache(self::getService('cacheStorage'),$namespace);}static
function
getSession($namespace=NULL){return$namespace===NULL?self::getService('session'):self::getService('session')->getSection($namespace);}static
function
loadConfig($file=NULL,$section=NULL){if(self::$createdAt){throw
new
InvalidStateException('NConfigurator has already been created automatically by NEnvironment at '.self::$createdAt);}$configurator=new
NConfigurator;$configurator->setDebugMode(!self::isProduction())->setTempDirectory(defined('TEMP_DIR')?TEMP_DIR:'')->addParameters(array('container'=>array('class'=>'EnvironmentContainer')));if($file){$configurator->addConfig($file,$section);}self::$context=$configurator->createContainer();self::$createdAt='?';foreach(PHP_VERSION_ID<50205?debug_backtrace():debug_backtrace(FALSE)as$row){if(isset($row['file'])&&is_file($row['file'])&&strpos($row['file'],NETTE_DIR.DIRECTORY_SEPARATOR)!==0){self::$createdAt="$row[file]:$row[line]";break;}}return
self::getConfig();}static
function
getConfig($key=NULL,$default=NULL){$params=NArrayHash::from(self::getContext()->parameters);if(func_num_args()){return
isset($params[$key])?$params[$key]:$default;}else{return$params;}}}class
NFramework{const
NAME='Nette Framework',VERSION='2.0.15',VERSION_ID=20015,REVISION='released on 2014-05-24';public
static$iAmUsingBadHost=FALSE;final
function
__construct(){throw
new
NStaticClassException;}}class
NImage
extends
NObject{const
SHRINK_ONLY=1;const
STRETCH=2;const
FIT=0;const
FILL=4;const
EXACT=8;const
JPEG=IMAGETYPE_JPEG,PNG=IMAGETYPE_PNG,GIF=IMAGETYPE_GIF;const
EMPTY_GIF="GIF89a\x01\x00\x01\x00\x80\x00\x00\x00\x00\x00\x00\x00\x00!\xf9\x04\x01\x00\x00\x00\x00,\x00\x00\x00\x00\x01\x00\x01\x00\x00\x02\x02D\x01\x00;";const
ENLARGE=0;private$image;static
function
rgb($red,$green,$blue,$transparency=0){return
array('red'=>max(0,min(255,(int)$red)),'green'=>max(0,min(255,(int)$green)),'blue'=>max(0,min(255,(int)$blue)),'alpha'=>max(0,min(127,(int)$transparency)));}static
function
fromFile($file,&$format=NULL){if(!extension_loaded('gd')){throw
new
NotSupportedException('PHP extension GD is not loaded.');}$info=@getimagesize($file);switch($format=$info[2]){case
self::JPEG:return
new
self(imagecreatefromjpeg($file));case
self::PNG:return
new
self(imagecreatefrompng($file));case
self::GIF:return
new
self(imagecreatefromgif($file));default:throw
new
NUnknownImageFileException("Unknown image type or file '$file' not found.");}}static
function
getFormatFromString($s){$types=array('image/jpeg'=>self::JPEG,'image/gif'=>self::GIF,'image/png'=>self::PNG);$type=NMimeTypeDetector::fromString($s);return
isset($types[$type])?$types[$type]:NULL;}static
function
fromString($s,&$format=NULL){if(!extension_loaded('gd')){throw
new
NotSupportedException('PHP extension GD is not loaded.');}$format=self::getFormatFromString($s);return
new
self(imagecreatefromstring($s));}static
function
fromBlank($width,$height,$color=NULL){if(!extension_loaded('gd')){throw
new
NotSupportedException('PHP extension GD is not loaded.');}$width=(int)$width;$height=(int)$height;if($width<1||$height<1){throw
new
InvalidArgumentException('Image width and height must be greater than zero.');}$image=imagecreatetruecolor($width,$height);if(is_array($color)){$color+=array('alpha'=>0);$color=imagecolorallocatealpha($image,$color['red'],$color['green'],$color['blue'],$color['alpha']);imagealphablending($image,FALSE);imagefilledrectangle($image,0,0,$width-1,$height-1,$color);imagealphablending($image,TRUE);}return
new
self($image);}function
__construct($image){$this->setImageResource($image);imagesavealpha($image,TRUE);}function
getWidth(){return
imagesx($this->image);}function
getHeight(){return
imagesy($this->image);}protected
function
setImageResource($image){if(!is_resource($image)||get_resource_type($image)!=='gd'){throw
new
InvalidArgumentException('Image is not valid.');}$this->image=$image;return$this;}function
getImageResource(){return$this->image;}function
resize($width,$height,$flags=self::FIT){if($flags&self::EXACT){return$this->resize($width,$height,self::FILL)->crop('50%','50%',$width,$height);}list($newWidth,$newHeight)=self::calculateSize($this->getWidth(),$this->getHeight(),$width,$height,$flags);if($newWidth!==$this->getWidth()||$newHeight!==$this->getHeight()){$newImage=self::fromBlank($newWidth,$newHeight,self::RGB(0,0,0,127))->getImageResource();imagecopyresampled($newImage,$this->getImageResource(),0,0,0,0,$newWidth,$newHeight,$this->getWidth(),$this->getHeight());$this->image=$newImage;}if($width<0||$height<0){$newImage=self::fromBlank($newWidth,$newHeight,self::RGB(0,0,0,127))->getImageResource();imagecopyresampled($newImage,$this->getImageResource(),0,0,$width<0?$newWidth-1:0,$height<0?$newHeight-1:0,$newWidth,$newHeight,$width<0?-$newWidth:$newWidth,$height<0?-$newHeight:$newHeight);$this->image=$newImage;}return$this;}static
function
calculateSize($srcWidth,$srcHeight,$newWidth,$newHeight,$flags=self::FIT){if(substr($newWidth,-1)==='%'){$newWidth=round($srcWidth/100*abs($newWidth));$percents=TRUE;}else{$newWidth=(int)abs($newWidth);}if(substr($newHeight,-1)==='%'){$newHeight=round($srcHeight/100*abs($newHeight));$flags|=empty($percents)?0:self::STRETCH;}else{$newHeight=(int)abs($newHeight);}if($flags&self::STRETCH){if(empty($newWidth)||empty($newHeight)){throw
new
InvalidArgumentException('For stretching must be both width and height specified.');}if($flags&self::SHRINK_ONLY){$newWidth=round($srcWidth*min(1,$newWidth/$srcWidth));$newHeight=round($srcHeight*min(1,$newHeight/$srcHeight));}}else{if(empty($newWidth)&&empty($newHeight)){throw
new
InvalidArgumentException('At least width or height must be specified.');}$scale=array();if($newWidth>0){$scale[]=$newWidth/$srcWidth;}if($newHeight>0){$scale[]=$newHeight/$srcHeight;}if($flags&self::FILL){$scale=array(max($scale));}if($flags&self::SHRINK_ONLY){$scale[]=1;}$scale=min($scale);$newWidth=round($srcWidth*$scale);$newHeight=round($srcHeight*$scale);}return
array(max((int)$newWidth,1),max((int)$newHeight,1));}function
crop($left,$top,$width,$height){list($left,$top,$width,$height)=self::calculateCutout($this->getWidth(),$this->getHeight(),$left,$top,$width,$height);$newImage=self::fromBlank($width,$height,self::RGB(0,0,0,127))->getImageResource();imagecopy($newImage,$this->getImageResource(),0,0,$left,$top,$width,$height);$this->image=$newImage;return$this;}static
function
calculateCutout($srcWidth,$srcHeight,$left,$top,$newWidth,$newHeight){if(substr($newWidth,-1)==='%'){$newWidth=round($srcWidth/100*$newWidth);}if(substr($newHeight,-1)==='%'){$newHeight=round($srcHeight/100*$newHeight);}if(substr($left,-1)==='%'){$left=round(($srcWidth-$newWidth)/100*$left);}if(substr($top,-1)==='%'){$top=round(($srcHeight-$newHeight)/100*$top);}if($left<0){$newWidth+=$left;$left=0;}if($top<0){$newHeight+=$top;$top=0;}$newWidth=min((int)$newWidth,$srcWidth-$left);$newHeight=min((int)$newHeight,$srcHeight-$top);return
array($left,$top,$newWidth,$newHeight);}function
sharpen(){imageconvolution($this->getImageResource(),array(array(-1,-1,-1),array(-1,24,-1),array(-1,-1,-1)),16,0);return$this;}function
place(NImage$image,$left=0,$top=0,$opacity=100){$opacity=max(0,min(100,(int)$opacity));if(substr($left,-1)==='%'){$left=round(($this->getWidth()-$image->getWidth())/100*$left);}if(substr($top,-1)==='%'){$top=round(($this->getHeight()-$image->getHeight())/100*$top);}if($opacity===100){imagecopy($this->getImageResource(),$image->getImageResource(),$left,$top,0,0,$image->getWidth(),$image->getHeight());}elseif($opacity<>0){imagecopymerge($this->getImageResource(),$image->getImageResource(),$left,$top,0,0,$image->getWidth(),$image->getHeight(),$opacity);}return$this;}function
save($file=NULL,$quality=NULL,$type=NULL){if($type===NULL){switch(strtolower(pathinfo($file,PATHINFO_EXTENSION))){case'jpg':case'jpeg':$type=self::JPEG;break;case'png':$type=self::PNG;break;case'gif':$type=self::GIF;}}switch($type){case
self::JPEG:$quality=$quality===NULL?85:max(0,min(100,(int)$quality));return
imagejpeg($this->getImageResource(),$file,$quality);case
self::PNG:$quality=$quality===NULL?9:max(0,min(9,(int)$quality));return
imagepng($this->getImageResource(),$file,$quality);case
self::GIF:return$file===NULL?imagegif($this->getImageResource()):imagegif($this->getImageResource(),$file);default:throw
new
InvalidArgumentException('Unsupported image type.');}}function
toString($type=self::JPEG,$quality=NULL){ob_start();$this->save(NULL,$quality,$type);return
ob_get_clean();}function
__toString(){try{return$this->toString();}catch(Exception$e){if(func_num_args()){throw$e;}trigger_error("Exception in ".__METHOD__."(): {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}",E_USER_ERROR);}}function
send($type=self::JPEG,$quality=NULL){if($type!==self::GIF&&$type!==self::PNG&&$type!==self::JPEG){throw
new
InvalidArgumentException('Unsupported image type.');}header('Content-Type: '.image_type_to_mime_type($type));return$this->save(NULL,$quality,$type);}function
__call($name,$args){$function='image'.$name;if(function_exists($function)){foreach($args
as$key=>$value){if($value
instanceof
self){$args[$key]=$value->getImageResource();}elseif(is_array($value)&&isset($value['red'])){$args[$key]=imagecolorallocatealpha($this->getImageResource(),$value['red'],$value['green'],$value['blue'],$value['alpha']);}}array_unshift($args,$this->getImageResource());$res=call_user_func_array($function,$args);return
is_resource($res)&&get_resource_type($res)==='gd'?$this->setImageResource($res):$res;}return
parent::__call($name,$args);}}class
NUnknownImageFileException
extends
Exception{}class
NObjectMixin{private
static$methods;private
static$props;final
function
__construct(){throw
new
NStaticClassException;}static
function
call($_this,$name,$args){$class=get_class($_this);$isProp=self::hasProperty($class,$name);if($name===''){throw
new
MemberAccessException("Call to class '$class' method without name.");}elseif($isProp==='event'){if(is_array($_this->$name)||$_this->$name
instanceof
Traversable){foreach($_this->$name
as$handler){NCallback::create($handler)->invokeArgs($args);}}elseif($_this->$name!==NULL){throw
new
UnexpectedValueException("Property $class::$$name must be array or NULL, ".gettype($_this->$name)." given.");}}elseif($cb=NClassReflection::from($_this)->getExtensionMethod($name)){array_unshift($args,$_this);return$cb->invokeArgs($args);}else{throw
new
MemberAccessException("Call to undefined method $class::$name().");}}static
function
callProperty($_this,$name,$args){if(strlen($name)>3){$op=substr($name,0,3);$prop=strtolower($name[3]).substr($name,4);if($op==='add'&&self::hasProperty(get_class($_this),$prop.'s')){$_this->{$prop.'s'}[]=$args[0];return$_this;}elseif($op==='set'&&self::hasProperty(get_class($_this),$prop)){$_this->$prop=$args[0];return$_this;}elseif($op==='get'&&self::hasProperty(get_class($_this),$prop)){return$_this->$prop;}}return
self::call($_this,$name,$args);}static
function
callStatic($class,$method,$args){throw
new
MemberAccessException("Call to undefined static method $class::$method().");}static
function&get($_this,$name){$class=get_class($_this);$uname=ucfirst($name);if(!isset(self::$methods[$class])){self::$methods[$class]=array_flip(get_class_methods($class));}if($name===''){throw
new
MemberAccessException("Cannot read a class '$class' property without name.");}elseif(isset(self::$methods[$class][$m='get'.$uname])||isset(self::$methods[$class][$m='is'.$uname])){$val=$_this->$m();return$val;}elseif(isset(self::$methods[$class][$name])){$val=NCallback::create($_this,$name);return$val;}else{$type=isset(self::$methods[$class]['set'.$uname])?'a write-only':'an undeclared';throw
new
MemberAccessException("Cannot read $type property $class::\$$name.");}}static
function
set($_this,$name,$value){$class=get_class($_this);$uname=ucfirst($name);if(!isset(self::$methods[$class])){self::$methods[$class]=array_flip(get_class_methods($class));}if($name===''){throw
new
MemberAccessException("Cannot write to a class '$class' property without name.");}elseif(self::hasProperty($class,$name)){$_this->$name=$value;}elseif(isset(self::$methods[$class][$m='set'.$uname])){$_this->$m($value);}else{$type=isset(self::$methods[$class]['get'.$uname])||isset(self::$methods[$class]['is'.$uname])?'a read-only':'an undeclared';throw
new
MemberAccessException("Cannot write to $type property $class::\$$name.");}}static
function
remove($_this,$name){$class=get_class($_this);if(!self::hasProperty($class,$name)){throw
new
MemberAccessException("Cannot unset the property $class::\$$name.");}}static
function
has($_this,$name){$class=get_class($_this);$name=ucfirst($name);if(!isset(self::$methods[$class])){self::$methods[$class]=array_flip(get_class_methods($class));}return$name!==''&&(isset(self::$methods[$class]['get'.$name])||isset(self::$methods[$class]['is'.$name]));}private
static
function
hasProperty($class,$name){$prop=&self::$props[$class][$name];if($prop===NULL){$prop=FALSE;try{$rp=new
ReflectionProperty($class,$name);if($name===$rp->getName()&&$rp->isPublic()&&!$rp->isStatic()){$prop=preg_match('#^on[A-Z]#',$name)?'event':TRUE;}}catch(ReflectionException$e){}}return$prop;}}class
NRecursiveComponentIterator
extends
RecursiveArrayIterator
implements
Countable{function
hasChildren(){return$this->current()instanceof
IComponentContainer;}function
getChildren(){return$this->current()->getComponents();}function
count(){return
iterator_count($this);}}class
NConfigIniAdapter
extends
NObject
implements
IConfigAdapter{const
INHERITING_SEPARATOR='<',KEY_SEPARATOR='.',ESCAPED_KEY_SEPARATOR='..',RAW_SECTION='!';function
load($file){set_error_handler(create_function('$severity, $message',' // parse_ini_file returns FALSE on failure since PHP 5.2.7
			restore_error_handler();
			throw new InvalidStateException("parse_ini_file(): $message");
		'));$ini=parse_ini_file($file,TRUE);restore_error_handler();$data=array();foreach($ini
as$secName=>$secData){if(is_array($secData)){if(substr($secName,-1)===self::RAW_SECTION){$secName=substr($secName,0,-1);}else{$tmp=array();foreach($secData
as$key=>$val){$cursor=&$tmp;$key=str_replace(self::ESCAPED_KEY_SEPARATOR,"\xFF",$key);foreach(explode(self::KEY_SEPARATOR,$key)as$part){$part=str_replace("\xFF",self::KEY_SEPARATOR,$part);if(!isset($cursor[$part])||is_array($cursor[$part])){$cursor=&$cursor[$part];}else{throw
new
InvalidStateException("Invalid key '$key' in section [$secName] in file '$file'.");}}$cursor=$val;}$secData=$tmp;}$parts=explode(self::INHERITING_SEPARATOR,$secName);if(count($parts)>1){$secName=trim($parts[0]);$secData[NConfigHelpers::EXTENDS_KEY]=trim($parts[1]);}}$cursor=&$data;foreach(explode(self::KEY_SEPARATOR,$secName)as$part){if(!isset($cursor[$part])||is_array($cursor[$part])){$cursor=&$cursor[$part];}else{throw
new
InvalidStateException("Invalid section [$secName] in file '$file'.");}}if(is_array($secData)&&is_array($cursor)){$secData=NConfigHelpers::merge($secData,$cursor);}$cursor=$secData;}return$data;}function
dump(array$data){$output=array();foreach($data
as$name=>$secData){if(!is_array($secData)){$output=array();self::build($data,$output,'');break;}if($parent=NConfigHelpers::takeParent($secData)){$output[]="[$name ".self::INHERITING_SEPARATOR." $parent]";}else{$output[]="[$name]";}self::build($secData,$output,'');$output[]='';}return"; generated by Nette\n\n".implode(PHP_EOL,$output);}private
static
function
build($input,&$output,$prefix){foreach($input
as$key=>$val){$key=str_replace(self::KEY_SEPARATOR,self::ESCAPED_KEY_SEPARATOR,$key);if(is_array($val)){self::build($val,$output,$prefix.$key.self::KEY_SEPARATOR);}elseif(is_bool($val)){$output[]="$prefix$key = ".($val?'true':'false');}elseif(is_numeric($val)){$output[]="$prefix$key = $val";}elseif(is_string($val)){$output[]="$prefix$key = \"$val\"";}else{throw
new
InvalidArgumentException(sprintf("The '%s' item must be scalar or array, %s given.",$prefix.$key,gettype($val)));}}}}class
NConfigNeonAdapter
extends
NObject
implements
IConfigAdapter{const
INHERITING_SEPARATOR='<',PREVENT_MERGING='!';function
load($file){return$this->process((array)NNeon::decode(file_get_contents($file)));}private
function
process(array$arr){$res=array();foreach($arr
as$key=>$val){if(substr($key,-1)===self::PREVENT_MERGING){if(!is_array($val)&&$val!==NULL){throw
new
InvalidStateException("Replacing operator is available only for arrays, item '$key' is not array.");}$key=substr($key,0,-1);$val[NConfigHelpers::EXTENDS_KEY]=NConfigHelpers::OVERWRITE;}elseif(preg_match('#^(\S+)\s+'.self::INHERITING_SEPARATOR.'\s+(\S+)\z#',$key,$matches)){if(!is_array($val)&&$val!==NULL){throw
new
InvalidStateException("Inheritance operator is available only for arrays, item '$key' is not array.");}list(,$key,$val[NConfigHelpers::EXTENDS_KEY])=$matches;if(isset($res[$key])){throw
new
InvalidStateException("Duplicated key '$key'.");}}if(is_array($val)){$val=$this->process($val);}elseif($val
instanceof
NNeonEntity){$val=(object)array('value'=>$val->value,'attributes'=>$this->process($val->attributes));}$res[$key]=$val;}return$res;}function
dump(array$data){$tmp=array();foreach($data
as$name=>$secData){if($parent=NConfigHelpers::takeParent($secData)){$name.=' '.self::INHERITING_SEPARATOR.' '.$parent;}$tmp[$name]=$secData;}return"# generated by Nette\n\n".NNeon::encode($tmp,NNeon::BLOCK);}}class
NConfigPhpAdapter
extends
NObject
implements
IConfigAdapter{function
load($file){return
NLimitedScope::load($file);}function
dump(array$data){return"<?php // generated by Nette \nreturn ".NPhpHelpers::dump($data).';';}}class
NConfigCompiler
extends
NObject{private$extensions=array();private$container;private$config;private
static$reserved=array('services'=>1,'factories'=>1,'parameters'=>1);function
addExtension($name,NConfigCompilerExtension$extension){if(isset(self::$reserved[$name])){throw
new
InvalidArgumentException("Name '$name' is reserved.");}$this->extensions[$name]=$extension->setCompiler($this,$name);return$this;}function
getExtensions(){return$this->extensions;}function
getContainerBuilder(){return$this->container;}function
getConfig(){return$this->config;}function
compile(array$config,$className,$parentName){$this->config=$config;$this->container=new
NDIContainerBuilder;$this->processParameters();$this->processExtensions();$this->processServices();return$this->generateCode($className,$parentName);}function
processParameters(){if(isset($this->config['parameters'])){$this->container->parameters=$this->config['parameters'];}}function
processExtensions(){for($i=0;$slice=array_slice($this->extensions,$i,1);$i++){reset($slice)->loadConfiguration();}if($extra=array_diff_key($this->config,self::$reserved,$this->extensions)){$extra=implode("', '",array_keys($extra));throw
new
InvalidStateException("Found sections '$extra' in configuration, but corresponding extensions are missing.");}}function
processServices(){$this->parseServices($this->container,$this->config);foreach($this->extensions
as$name=>$extension){$this->container->addDefinition($name)->setClass('NDINestedAccessor',array('@container',$name))->setAutowired(FALSE);if(isset($this->config[$name])){$this->parseServices($this->container,$this->config[$name],$name);}}foreach($this->container->getDefinitions()as$name=>$def){$factory=$name.'Factory';if(!$def->shared&&!$def->internal&&!$this->container->hasDefinition($factory)){$this->container->addDefinition($factory)->setClass('NCallback',array('@container',NDIContainer::getMethodName($name,FALSE)))->setAutowired(FALSE)->tags=$def->tags;}}}function
generateCode($className,$parentName){foreach($this->extensions
as$extension){$extension->beforeCompile();$this->container->addDependency(NClassReflection::from($extension)->getFileName());}$classes[]=$class=$this->container->generateClass($parentName);$class->setName($className)->addMethod('initialize');foreach($this->extensions
as$extension){$extension->afterCompile($class);}$defs=$this->container->getDefinitions();ksort($defs);$list=array_keys($defs);foreach(array_reverse($defs,TRUE)as$name=>$def){if($def->class==='NDINestedAccessor'&&($found=preg_grep('#^'.$name.'\.#i',$list))){$list=array_diff($list,$found);$def->class=$className.'_'.preg_replace('#\W+#','_',$name);$class->documents=preg_replace("#\\S+(?= \\$$name\\z)#",$def->class,$class->documents);$classes[]=$accessor=new
NPhpClassType($def->class);foreach($found
as$item){if($defs[$item]->internal){continue;}$short=substr($item,strlen($name)+1);$accessor->addDocument($defs[$item]->shared?"@property {$defs[$item]->class} \$$short":"@method {$defs[$item]->class} create".ucfirst("$short()"));}}}return
implode("\n\n\n",$classes);}static
function
parseServices(NDIContainerBuilder$container,array$config,$namespace=NULL){$services=isset($config['services'])?$config['services']:array();$factories=isset($config['factories'])?$config['factories']:array();$all=array_merge($services,$factories);uasort($all,create_function('$a, $b','
			return strcmp(NConfigHelpers::isInheriting($a), NConfigHelpers::isInheriting($b));
		'));foreach($all
as$origName=>$def){$shared=array_key_exists($origName,$services);if((string)(int)$origName===(string)$origName){$name=(string)(count($container->getDefinitions())+1);}elseif($shared&&array_key_exists($origName,$factories)){throw
new
NServiceCreationException("It is not allowed to use services and factories with the same name: '$origName'.");}else{$name=($namespace?$namespace.'.':'').strtr($origName,'\\','_');}if(($parent=NConfigHelpers::takeParent($def))&&$parent!==$name){$container->removeDefinition($name);$definition=$container->addDefinition($name);if($parent!==NConfigHelpers::OVERWRITE){foreach($container->getDefinition($parent)as$k=>$v){$definition->$k=unserialize(serialize($v));}}}elseif($container->hasDefinition($name)){$definition=$container->getDefinition($name);if($definition->shared!==$shared){throw
new
NServiceCreationException("It is not allowed to use service and factory with the same name '$name'.");}}else{$definition=$container->addDefinition($name);}try{self::parseService($definition,$def,$shared);}catch(Exception$e){throw
new
NServiceCreationException("Service '$name': ".$e->getMessage(),NULL,$e);}}}static
function
parseService(NDIServiceDefinition$definition,$config,$shared=TRUE){if($config===NULL){return;}elseif(!is_array($config)){$config=array('class'=>NULL,'factory'=>$config);}$known=$shared?array('class','factory','arguments','setup','autowired','run','tags'):array('class','factory','arguments','setup','autowired','tags','internal','parameters');if($error=array_diff(array_keys($config),$known)){throw
new
InvalidStateException("Unknown key '".implode("', '",$error)."' in definition of service.");}$arguments=array();if(array_key_exists('arguments',$config)){NValidators::assertField($config,'arguments','array');$arguments=self::filterArguments($config['arguments']);$definition->setArguments($arguments);}if(array_key_exists('class',$config)||array_key_exists('factory',$config)){$definition->class=NULL;$definition->factory=NULL;}if(array_key_exists('class',$config)){NValidators::assertField($config,'class','string|stdClass|null');if($config['class']instanceof
stdClass){$definition->setClass($config['class']->value,self::filterArguments($config['class']->attributes));}else{$definition->setClass($config['class'],$arguments);}}if(array_key_exists('factory',$config)){NValidators::assertField($config,'factory','callable|stdClass|null');if($config['factory']instanceof
stdClass){$definition->setFactory($config['factory']->value,self::filterArguments($config['factory']->attributes));}else{$definition->setFactory($config['factory'],$arguments);}}if(isset($config['setup'])){if(NConfigHelpers::takeParent($config['setup'])){$definition->setup=array();}NValidators::assertField($config,'setup','list');foreach($config['setup']as$id=>$setup){NValidators::assert($setup,'callable|stdClass',"setup item #$id");if($setup
instanceof
stdClass){NValidators::assert($setup->value,'callable',"setup item #$id");$definition->addSetup($setup->value,self::filterArguments($setup->attributes));}else{$definition->addSetup($setup);}}}$definition->setShared($shared);if(isset($config['parameters'])){NValidators::assertField($config,'parameters','array');$definition->setParameters($config['parameters']);}if(isset($config['autowired'])){NValidators::assertField($config,'autowired','bool');$definition->setAutowired($config['autowired']);}if(isset($config['internal'])){NValidators::assertField($config,'internal','bool');$definition->setInternal($config['internal']);}if(isset($config['run'])){$config['tags']['run']=(bool)$config['run'];}if(isset($config['tags'])){NValidators::assertField($config,'tags','array');if(NConfigHelpers::takeParent($config['tags'])){$definition->tags=array();}foreach($config['tags']as$tag=>$attrs){if(is_int($tag)&&is_string($attrs)){$definition->addTag($attrs);}else{$definition->addTag($tag,$attrs);}}}}static
function
filterArguments(array$args){foreach($args
as$k=>$v){if($v==='...'){unset($args[$k]);}elseif($v
instanceof
stdClass&&isset($v->value,$v->attributes)){$args[$k]=new
NDIStatement($v->value,self::filterArguments($v->attributes));}}return$args;}}abstract
class
NConfigCompilerExtension
extends
NObject{protected$compiler;protected$name;function
setCompiler(NConfigCompiler$compiler,$name){$this->compiler=$compiler;$this->name=$name;return$this;}function
getConfig(array$defaults=NULL,$expand=TRUE){$config=$this->compiler->getConfig();$config=isset($config[$this->name])?$config[$this->name]:array();unset($config['services'],$config['factories']);$config=NConfigHelpers::merge($config,$defaults);return$expand?$this->compiler->getContainerBuilder()->expand($config):$config;}function
getContainerBuilder(){return$this->compiler->getContainerBuilder();}function
loadFromFile($file){$loader=new
NConfigLoader;$res=$loader->load($file);$container=$this->compiler->getContainerBuilder();foreach($loader->getDependencies()as$file){$container->addDependency($file);}return$res;}function
prefix($id){return
substr_replace($id,$this->name.'.',substr($id,0,1)==='@'?1:0,0);}function
loadConfiguration(){}function
beforeCompile(){}function
afterCompile(NPhpClassType$class){}}class
NConfigurator
extends
NObject{const
AUTO=NULL,NONE=FALSE;const
DEVELOPMENT='development',PRODUCTION='production';public$onCompile;protected$parameters;protected$files=array();function
__construct(){$this->parameters=$this->getDefaultParameters();}function
setDebugMode($value=TRUE){$this->parameters['debugMode']=is_string($value)||is_array($value)?self::detectDebugMode($value):(bool)$value;$this->parameters['productionMode']=!$this->parameters['debugMode'];return$this;}function
isDebugMode(){return!$this->parameters['productionMode'];}function
setTempDirectory($path){$this->parameters['tempDir']=$path;if(($cacheDir=$this->getCacheDirectory())&&!is_dir($cacheDir)){mkdir($cacheDir);}return$this;}function
addParameters(array$params){$this->parameters=NConfigHelpers::merge($params,$this->parameters);return$this;}protected
function
getDefaultParameters(){$trace=PHP_VERSION_ID<50205?debug_backtrace():debug_backtrace(FALSE);$debugMode=self::detectDebugMode();return
array('appDir'=>isset($trace[1]['file'])?dirname($trace[1]['file']):NULL,'wwwDir'=>isset($_SERVER['SCRIPT_FILENAME'])?dirname($_SERVER['SCRIPT_FILENAME']):NULL,'debugMode'=>$debugMode,'productionMode'=>!$debugMode,'environment'=>$debugMode?'development':'production','consoleMode'=>PHP_SAPI==='cli','container'=>array('class'=>'SystemContainer','parent'=>'NDIContainer'));}function
enableDebugger($logDirectory=NULL,$email=NULL){NDebugger::$strictMode=TRUE;NDebugger::enable($this->parameters['productionMode'],$logDirectory,$email);}function
createRobotLoader(){if(!($cacheDir=$this->getCacheDirectory())){throw
new
InvalidStateException("Set path to temporary directory using setTempDirectory().");}$loader=new
NRobotLoader;$loader->setCacheStorage(new
NFileStorage($cacheDir));$loader->autoRebuild=!$this->parameters['productionMode'];return$loader;}function
addConfig($file,$section=NULL){$this->files[]=array($file,$section===NULL?$this->parameters['environment']:$section);return$this;}function
loadConfig($file,$section=NULL){trigger_error(__METHOD__.'() is deprecated; use addConfig(file, [section])->createContainer() instead.',E_USER_WARNING);return$this->addConfig($file,$section)->createContainer();}function
createContainer(){if($cacheDir=$this->getCacheDirectory()){$cache=new
NCache(new
NPhpFileStorage($cacheDir),'Nette.Configurator');$cacheKey=array($this->parameters,$this->files);$cached=$cache->load($cacheKey);if(!$cached){$code=$this->buildContainer($dependencies);$cache->save($cacheKey,$code,array(NCache::FILES=>$dependencies));$cached=$cache->load($cacheKey);}NLimitedScope::load($cached['file'],TRUE);}elseif($this->files){throw
new
InvalidStateException("Set path to temporary directory using setTempDirectory().");}else{NLimitedScope::evaluate($this->buildContainer());}$container=new$this->parameters['container']['class'];$container->initialize();NEnvironment::setContext($container);return$container;}protected
function
buildContainer(&$dependencies=NULL){$loader=$this->createLoader();$config=array();$code="<?php\n";foreach($this->files
as$tmp){list($file,$section)=$tmp;$config=NConfigHelpers::merge($loader->load($file,$section),$config);$code.="// source: $file $section\n";}$code.="\n";$this->checkCompatibility($config);if(!isset($config['parameters'])){$config['parameters']=array();}$config['parameters']=NConfigHelpers::merge($config['parameters'],$this->parameters);$compiler=$this->createCompiler();$this->onCompile($this,$compiler);$code.=$compiler->compile($config,$this->parameters['container']['class'],$config['parameters']['container']['parent']);$dependencies=array_merge($loader->getDependencies(),$this->isDebugMode()?$compiler->getContainerBuilder()->getDependencies():array());return$code;}protected
function
checkCompatibility(array$config){foreach(array('service'=>'services','variable'=>'parameters','variables'=>'parameters','mode'=>'parameters','const'=>'constants')as$old=>$new){if(isset($config[$old])){throw
new
DeprecatedException("Section '$old' in configuration file is deprecated; use '$new' instead.");}}if(isset($config['services'])){foreach($config['services']as$key=>$def){foreach(array('option'=>'arguments','methods'=>'setup')as$old=>$new){if(is_array($def)&&isset($def[$old])){throw
new
DeprecatedException("Section '$old' in service definition is deprecated; refactor it into '$new'.");}}}}}protected
function
createCompiler(){$compiler=new
NConfigCompiler;$compiler->addExtension('php',new
NPhpExtension)->addExtension('constants',new
NConstantsExtension)->addExtension('nette',new
NNetteExtension);return$compiler;}protected
function
createLoader(){return
new
NConfigLoader;}protected
function
getCacheDirectory(){return
empty($this->parameters['tempDir'])?NULL:$this->parameters['tempDir'].'/cache';}static
function
detectDebugMode($list=NULL){$list=is_string($list)?preg_split('#[,\s]+#',$list):(array)$list;if(!isset($_SERVER['HTTP_X_FORWARDED_FOR'])){$list[]='127.0.0.1';$list[]='::1';}return
in_array(isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:php_uname('n'),$list,TRUE);}function
setProductionMode($value=TRUE){return$this->setDebugMode(is_bool($value)?!$value:$value);}function
isProductionMode(){return!$this->isDebugMode();}static
function
detectProductionMode($list=NULL){return!self::detectDebugMode($list);}}class
NConstantsExtension
extends
NConfigCompilerExtension{function
afterCompile(NPhpClassType$class){foreach($this->getConfig()as$name=>$value){$class->methods['initialize']->addBody('define(?, ?);',array($name,$value));}}}class
NNetteExtension
extends
NConfigCompilerExtension{public$defaults=array('xhtml'=>TRUE,'session'=>array('iAmUsingBadHost'=>NULL,'autoStart'=>'smart','expiration'=>NULL),'application'=>array('debugger'=>TRUE,'errorPresenter'=>NULL,'catchExceptions'=>'%productionMode%'),'routing'=>array('debugger'=>TRUE,'routes'=>array()),'security'=>array('debugger'=>TRUE,'frames'=>'SAMEORIGIN','users'=>array(),'roles'=>array(),'resources'=>array()),'mailer'=>array('smtp'=>FALSE),'database'=>array(),'forms'=>array('messages'=>array()),'container'=>array('debugger'=>FALSE),'debugger'=>array('email'=>NULL,'editor'=>NULL,'browser'=>NULL,'strictMode'=>NULL,'bar'=>array(),'blueScreen'=>array()));public$databaseDefaults=array('dsn'=>NULL,'user'=>NULL,'password'=>NULL,'options'=>NULL,'debugger'=>TRUE,'explain'=>TRUE,'reflection'=>'NDiscoveredReflection');function
loadConfiguration(){$container=$this->getContainerBuilder();$config=$this->getConfig($this->defaults);$container->addDefinition($this->prefix('cacheJournal'))->setClass('NFileJournal',array('%tempDir%'));$container->addDefinition('cacheStorage')->setClass('NFileStorage',array('%tempDir%/cache'));$container->addDefinition($this->prefix('templateCacheStorage'))->setClass('NPhpFileStorage',array('%tempDir%/cache'))->setAutowired(FALSE);$container->addDefinition($this->prefix('cache'))->setClass('NCache',array(1=>'%namespace%'))->setParameters(array('namespace'=>NULL));$container->addDefinition($this->prefix('httpRequestFactory'))->setClass('NHttpRequestFactory')->addSetup('setEncoding',array('UTF-8'))->setInternal(TRUE);$container->addDefinition('httpRequest')->setClass('NHttpRequest')->setFactory('@\NHttpRequestFactory::createHttpRequest');$container->addDefinition('httpResponse')->setClass('NHttpResponse');$container->addDefinition($this->prefix('httpContext'))->setClass('NHttpContext');$session=$container->addDefinition('session')->setClass('NSession');if(isset($config['session']['expiration'])){$session->addSetup('setExpiration',array($config['session']['expiration']));}if(isset($config['session']['iAmUsingBadHost'])){$session->addSetup('NFramework::$iAmUsingBadHost = ?;',array((bool)$config['session']['iAmUsingBadHost']));}unset($config['session']['expiration'],$config['session']['autoStart'],$config['session']['iAmUsingBadHost']);if(!empty($config['session'])){$session->addSetup('setOptions',array($config['session']));}$container->addDefinition($this->prefix('userStorage'))->setClass('NUserStorage');$user=$container->addDefinition('user')->setClass('NUser');if(!$container->parameters['productionMode']&&$config['security']['debugger']){$user->addSetup('NDebugger::$bar->addPanel(?)',array(new
NDIStatement('NUserPanel')));}if($config['security']['users']){$container->addDefinition($this->prefix('authenticator'))->setClass('NSimpleAuthenticator',array($config['security']['users']));}if($config['security']['roles']||$config['security']['resources']){$authorizator=$container->addDefinition($this->prefix('authorizator'))->setClass('NPermission');foreach($config['security']['roles']as$role=>$parents){$authorizator->addSetup('addRole',array($role,$parents));}foreach($config['security']['resources']as$resource=>$parents){$authorizator->addSetup('addResource',array($resource,$parents));}}$application=$container->addDefinition('application')->setClass('NApplication')->addSetup('$catchExceptions',$config['application']['catchExceptions'])->addSetup('$errorPresenter',$config['application']['errorPresenter']);if($config['application']['debugger']){$application->addSetup('NRoutingDebugger::initializePanel');}$container->addDefinition($this->prefix('presenterFactory'))->setClass('NPresenterFactory',array(isset($container->parameters['appDir'])?$container->parameters['appDir']:NULL));$router=$container->addDefinition('router')->setClass('NRouteList');foreach($config['routing']['routes']as$mask=>$action){$router->addSetup('$service[] = new NRoute(?, ?);',array($mask,$action));}if(!$container->parameters['productionMode']&&$config['routing']['debugger']){$application->addSetup('NDebugger::$bar->addPanel(?)',array(new
NDIStatement('NRoutingDebugger')));}if(empty($config['mailer']['smtp'])){$container->addDefinition($this->prefix('mailer'))->setClass('NSendmailMailer');}else{$container->addDefinition($this->prefix('mailer'))->setClass('NSmtpMailer',array($config['mailer']));}$container->addDefinition($this->prefix('mail'))->setClass('NMail')->addSetup('setMailer')->setShared(FALSE);$container->addDefinition($this->prefix('basicForm'))->setClass('NForm')->setShared(FALSE);$latte=$container->addDefinition($this->prefix('latte'))->setClass('NLatteFilter')->setShared(FALSE);if(empty($config['xhtml'])){$latte->addSetup('$service->getCompiler()->defaultContentType = ?',NLatteCompiler::CONTENT_HTML);}$container->addDefinition($this->prefix('template'))->setClass('NFileTemplate')->addSetup('registerFilter',array($latte))->addSetup('registerHelperLoader',array('NTemplateHelpers::loader'))->setShared(FALSE);$container->addDefinition($this->prefix('database'))->setClass('NDINestedAccessor',array('@container',$this->prefix('database')));if(isset($config['database']['dsn'])){$config['database']=array('default'=>$config['database']);}$autowired=TRUE;foreach((array)$config['database']as$name=>$info){if(!is_array($info)){continue;}$info+=$this->databaseDefaults+array('autowired'=>$autowired);$autowired=FALSE;foreach((array)$info['options']as$key=>$value){if(preg_match('#^PDO::\w+\z#',$key)){unset($info['options'][$key]);$info['options'][constant($key)]=$value;}}$connection=$container->addDefinition($this->prefix("database.$name"))->setClass('NConnection',array($info['dsn'],$info['user'],$info['password'],$info['options']))->setAutowired($info['autowired'])->addSetup('setCacheStorage')->addSetup('NDebugger::$blueScreen->addPanel(?)',array('NDatabasePanel::renderException'));if($info['reflection']){$connection->addSetup('setDatabaseReflection',is_string($info['reflection'])?array(new
NDIStatement(preg_match('#^[a-z]+\z#',$info['reflection'])?'Nette\Database\Reflection\\'.ucfirst($info['reflection']).'Reflection':$info['reflection'])):NConfigCompiler::filterArguments(array($info['reflection'])));}if(!$container->parameters['productionMode']&&$info['debugger']){$panel=$container->addDefinition($this->prefix("database.{$name}ConnectionPanel"))->setClass('NDatabasePanel')->setAutowired(FALSE)->addSetup('$explain',!empty($info['explain']))->addSetup('$name',$name)->addSetup('NDebugger::$bar->addPanel(?)',array('@self'));$connection->addSetup('$service->onQuery[] = ?',array(array($panel,'logQuery')));}}}function
afterCompile(NPhpClassType$class){$initialize=$class->methods['initialize'];$container=$this->getContainerBuilder();$config=$this->getConfig($this->defaults);foreach(array('email','editor','browser','strictMode','maxLen','maxDepth')as$key){if(isset($config['debugger'][$key])){$initialize->addBody('NDebugger::$? = ?;',array($key,$config['debugger'][$key]));}}if(!$container->parameters['productionMode']){if($config['container']['debugger']){$config['debugger']['bar'][]='NContainerPanel';}foreach((array)$config['debugger']['bar']as$item){$initialize->addBody($container->formatPhp('NDebugger::$bar->addPanel(?);',NConfigCompiler::filterArguments(array(is_string($item)?new
NDIStatement($item):$item))));}}foreach((array)$config['debugger']['blueScreen']as$item){$initialize->addBody($container->formatPhp('NDebugger::$blueScreen->addPanel(?);',NConfigCompiler::filterArguments(array($item))));}if(!empty($container->parameters['tempDir'])){$initialize->addBody($this->checkTempDir($container->expand('%tempDir%/cache')));}foreach((array)$config['forms']['messages']as$name=>$text){$initialize->addBody('NRules::$defaultMessages[NForm::?] = ?;',array($name,$text));}if($config['session']['autoStart']==='smart'){$initialize->addBody('$this->getService("session")->exists() && $this->getService("session")->start();');}elseif($config['session']['autoStart']){$initialize->addBody('$this->getService("session")->start();');}if(empty($config['xhtml'])){$initialize->addBody('NHtml::$xhtml = ?;',array((bool)$config['xhtml']));}if(isset($config['security']['frames'])&&$config['security']['frames']!==TRUE){$frames=$config['security']['frames'];if($frames===FALSE){$frames='DENY';}elseif(preg_match('#^https?:#',$frames)){$frames="ALLOW-FROM $frames";}$initialize->addBody('header(?);',array("X-Frame-Options: $frames"));}foreach($container->findByTag('run')as$name=>$on){if($on){$initialize->addBody('$this->getService(?);',array($name));}}}private
function
checkTempDir($dir){$uniq=uniqid('_',TRUE);if(!@mkdir("$dir/$uniq",0777)){throw
new
InvalidStateException("Unable to write to directory '$dir'. Make this directory writable.");}$useDirs=@file_put_contents("$dir/$uniq/_",'')!==FALSE;@unlink("$dir/$uniq/_");@rmdir("$dir/$uniq");return'NFileStorage::$useDirectories = '.($useDirs?'TRUE':'FALSE').";\n";}}class
NPhpExtension
extends
NConfigCompilerExtension{function
afterCompile(NPhpClassType$class){$initialize=$class->methods['initialize'];foreach($this->getConfig()as$name=>$value){if(!is_scalar($value)){throw
new
InvalidStateException("Configuration value for directive '$name' is not scalar.");}elseif($name==='include_path'){$initialize->addBody('set_include_path(?);',array(str_replace(';',PATH_SEPARATOR,$value)));}elseif($name==='ignore_user_abort'){$initialize->addBody('ignore_user_abort(?);',array($value));}elseif($name==='max_execution_time'){$initialize->addBody('set_time_limit(?);',array($value));}elseif($name==='date.timezone'){$initialize->addBody('date_default_timezone_set(?);',array($value));}elseif(function_exists('ini_set')){$initialize->addBody('ini_set(?, ?);',array($name,$value));}elseif(ini_get($name)!=$value){throw
new
NotSupportedException('Required function ini_set() is disabled.');}}}}class
NConfigHelpers{const
EXTENDS_KEY='_extends',OVERWRITE=TRUE;static
function
merge($left,$right){if(is_array($left)&&is_array($right)){foreach($left
as$key=>$val){if(is_int($key)){$right[]=$val;}else{if(is_array($val)&&isset($val[self::EXTENDS_KEY])){if($val[self::EXTENDS_KEY]===self::OVERWRITE){unset($val[self::EXTENDS_KEY]);}}elseif(isset($right[$key])){$val=self::merge($val,$right[$key]);}$right[$key]=$val;}}return$right;}elseif($left===NULL&&is_array($right)){return$right;}else{return$left;}}static
function
takeParent(&$data){if(is_array($data)&&isset($data[self::EXTENDS_KEY])){$parent=$data[self::EXTENDS_KEY];unset($data[self::EXTENDS_KEY]);return$parent;}}static
function
isOverwriting(&$data){return
is_array($data)&&isset($data[self::EXTENDS_KEY])&&$data[self::EXTENDS_KEY]===self::OVERWRITE;}static
function
isInheriting(&$data){return
is_array($data)&&isset($data[self::EXTENDS_KEY])&&$data[self::EXTENDS_KEY]!==self::OVERWRITE;}}class
NConfigLoader
extends
NObject{const
INCLUDES_KEY='includes';private$adapters=array('php'=>'NConfigPhpAdapter','ini'=>'NConfigIniAdapter','neon'=>'NConfigNeonAdapter');private$dependencies=array();function
load($file,$section=NULL){if(!is_file($file)||!is_readable($file)){throw
new
FileNotFoundException("File '$file' is missing or is not readable.");}$this->dependencies[]=$file=realpath($file);$data=$this->getAdapter($file)->load($file);if($section){if(isset($data[self::INCLUDES_KEY])){throw
new
InvalidStateException("Section 'includes' must be placed under some top section in file '$file'.");}$data=$this->getSection($data,$section,$file);}$merged=array();if(isset($data[self::INCLUDES_KEY])){NValidators::assert($data[self::INCLUDES_KEY],'list',"section 'includes' in file '$file'");foreach($data[self::INCLUDES_KEY]as$include){$merged=NConfigHelpers::merge($this->load(dirname($file).'/'.$include),$merged);}}unset($data[self::INCLUDES_KEY]);return
NConfigHelpers::merge($data,$merged);}function
save($data,$file){if(file_put_contents($file,$this->getAdapter($file)->dump($data))===FALSE){throw
new
IOException("Cannot write file '$file'.");}}function
getDependencies(){return
array_unique($this->dependencies);}function
addAdapter($extension,$adapter){$this->adapters[strtolower($extension)]=$adapter;return$this;}private
function
getAdapter($file){$extension=strtolower(pathinfo($file,PATHINFO_EXTENSION));if(!isset($this->adapters[$extension])){throw
new
InvalidArgumentException("Unknown file extension '$file'.");}return
is_object($this->adapters[$extension])?$this->adapters[$extension]:new$this->adapters[$extension];}private
function
getSection(array$data,$key,$file){NValidators::assertField($data,$key,'array|null',"section '%' in file '$file'");$item=$data[$key];if($parent=NConfigHelpers::takeParent($item)){$item=NConfigHelpers::merge($item,$this->getSection($data,$parent,$file));}return$item;}}if(class_exists('PDO')){class
NConnection
extends
PDO{private$dsn;private$driver;private$preprocessor;private$databaseReflection;private$cache;public$onQuery;function
__construct($dsn,$username=NULL,$password=NULL,array$options=NULL,$driverClass=NULL){parent::__construct($this->dsn=$dsn,$username,$password,$options);$this->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);$this->setAttribute(PDO::ATTR_STATEMENT_CLASS,array('NStatement',array($this)));$driverClass=($tmp=$driverClass)?$tmp:'N'.ucfirst(str_replace('sql','Sql',$this->getAttribute(PDO::ATTR_DRIVER_NAME))).'Driver';$this->driver=new$driverClass($this,(array)$options);$this->preprocessor=new
NSqlPreprocessor($this);}function
getDsn(){return$this->dsn;}function
getSupplementalDriver(){return$this->driver;}function
setDatabaseReflection(IReflection$databaseReflection){$databaseReflection->setConnection($this);$this->databaseReflection=$databaseReflection;return$this;}function
getDatabaseReflection(){if(!$this->databaseReflection){$this->setDatabaseReflection(new
NConventionalReflection);}return$this->databaseReflection;}function
setCacheStorage(ICacheStorage$storage=NULL){$this->cache=$storage?new
NCache($storage,'Nette.Database.'.md5($this->dsn)):NULL;return$this;}function
getCache(){return$this->cache;}function
query($statement){$args=func_get_args();return$this->queryArgs(array_shift($args),$args);}function
exec($statement){$args=func_get_args();return$this->queryArgs(array_shift($args),$args)->rowCount();}function
queryArgs($statement,$params){foreach($params
as$value){if(is_array($value)||is_object($value)){$need=TRUE;break;}}if(isset($need)&&$this->preprocessor!==NULL){list($statement,$params)=$this->preprocessor->process($statement,$params);}return$this->prepare($statement)->execute($params);}function
fetch($args){$args=func_get_args();return$this->queryArgs(array_shift($args),$args)->fetch();}function
fetchField($args){$args=func_get_args();return$this->queryArgs(array_shift($args),$args)->fetchField();}function
fetchColumn($args){$args=func_get_args();return$this->queryArgs(array_shift($args),$args)->fetchColumn();}function
fetchPairs($args){$args=func_get_args();return$this->queryArgs(array_shift($args),$args)->fetchPairs();}function
fetchAll($args){$args=func_get_args();return$this->queryArgs(array_shift($args),$args)->fetchAll();}function
table($table){return
new
NTableSelection($table,$this);}function
getReflection(){return
new
NClassReflection($this);}function
__call($name,$args){return
NObjectMixin::call($this,$name,$args);}function&__get($name){return
NObjectMixin::get($this,$name);}function
__set($name,$value){return
NObjectMixin::set($this,$name,$value);}function
__isset($name){return
NObjectMixin::has($this,$name);}function
__unset($name){NObjectMixin::remove($this,$name);}}}class
NDatabasePanel
extends
NObject
implements
IBarPanel{static
public$maxLength=1000;public$maxQueries=100;private$totalTime=0;private$count=0;private$queries=array();public$name;public$explain=TRUE;public$disabled=FALSE;function
logQuery(NStatement$result,array$params=NULL){if($this->disabled){return;}$source=NULL;foreach(PHP_VERSION_ID<50205?debug_backtrace():debug_backtrace(FALSE)as$row){if(isset($row['file'])&&is_file($row['file'])&&strpos($row['file'],NETTE_DIR.DIRECTORY_SEPARATOR)!==0){if(isset($row['function'])&&strpos($row['function'],'call_user_func')===0)continue;if(isset($row['class'])&&is_subclass_of($row['class'],'\\Nette\\Database\\Connection'))continue;$source=array($row['file'],(int)$row['line']);break;}}$this->count++;$this->totalTime+=$result->getTime();if($this->count<$this->maxQueries){$this->queries[]=array($result->queryString,$params,$result->getTime(),$result->rowCount(),$result->getConnection(),$source);}}static
function
renderException($e){if(!$e
instanceof
PDOException){return;}if(isset($e->queryString)){$sql=$e->queryString;}elseif($item=NDebugHelpers::findTrace($e->getTrace(),'PDO::prepare')){$sql=$item['args'][0];}return
isset($sql)?array('tab'=>'SQL','panel'=>NDatabaseHelpers::dumpSql($sql)):NULL;}function
getTab(){return'<span title="Nette\\Database '.htmlSpecialChars($this->name).'">'.'<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAEYSURBVBgZBcHPio5hGAfg6/2+R980k6wmJgsJ5U/ZOAqbSc2GnXOwUg7BESgLUeIQ1GSjLFnMwsKGGg1qxJRmPM97/1zXFAAAAEADdlfZzr26miup2svnelq7d2aYgt3rebl585wN6+K3I1/9fJe7O/uIePP2SypJkiRJ0vMhr55FLCA3zgIAOK9uQ4MS361ZOSX+OrTvkgINSjS/HIvhjxNNFGgQsbSmabohKDNoUGLohsls6BaiQIMSs2FYmnXdUsygQYmumy3Nhi6igwalDEOJEjPKP7CA2aFNK8Bkyy3fdNCg7r9/fW3jgpVJbDmy5+PB2IYp4MXFelQ7izPrhkPHB+P5/PjhD5gCgCenx+VR/dODEwD+A3T7nqbxwf1HAAAAAElFTkSuQmCC" />'.$this->count.' '.($this->count===1?'query':'queries').($this->totalTime?' / '.sprintf('%0.1f',$this->totalTime*1000).'ms':'').'</span>';}function
getPanel(){$this->disabled=TRUE;$s='';foreach($this->queries
as$query){list($sql,$params,$time,$rows,$connection,$source)=$query;$explain=NULL;if($this->explain&&preg_match('#\s*\(?\s*SELECT\s#iA',$sql)){try{$cmd=is_string($this->explain)?$this->explain:'EXPLAIN';$explain=$connection->queryArgs("$cmd $sql",$params)->fetchAll();}catch(PDOException$e){}}$s.='<tr><td>'.sprintf('%0.3f',$time*1000);if($explain){static$counter;$counter++;$s.="<br /><a href='#' class='nette-toggler' rel='#nette-DbConnectionPanel-row-$counter'>explain&nbsp;&#x25ba;</a>";}$s.='</td><td class="nette-DbConnectionPanel-sql">'.NDatabaseHelpers::dumpSql(self::$maxLength?NStrings::truncate($sql,self::$maxLength):$sql);if($explain){$s.="<table id='nette-DbConnectionPanel-row-$counter' class='nette-collapsed'><tr>";foreach($explain[0]as$col=>$foo){$s.='<th>'.htmlSpecialChars($col).'</th>';}$s.="</tr>";foreach($explain
as$row){$s.="<tr>";foreach($row
as$col){$s.='<td>'.htmlSpecialChars($col).'</td>';}$s.="</tr>";}$s.="</table>";}if($source){$s.=NDebugHelpers::editorLink($source[0],$source[1])->class('nette-DbConnectionPanel-source');}$s.='</td><td>';foreach($params
as$param){$s.=NDebugger::dump($param,TRUE);}$s.='</td><td>'.$rows.'</td></tr>';}return$this->count?'<style class="nette-debug"> #nette-debug td.nette-DbConnectionPanel-sql { background: white !important }
			#nette-debug .nette-DbConnectionPanel-source { color: #BBB !important } </style>
			<h1 title="'.htmlSpecialChars($connection->getDsn()).'">Queries: '.$this->count.($this->totalTime?', time: '.sprintf('%0.3f',$this->totalTime*1000).' ms':'').', '.htmlSpecialChars($this->name).'</h1>
			<div class="nette-inner nette-DbConnectionPanel">
			<table>
				<tr><th>Time&nbsp;ms</th><th>SQL Statement</th><th>Params</th><th>Rows</th></tr>'.$s.'
			</table>'.(count($this->queries)<$this->count?'<p>...and more</p>':'').'</div>':'';}}class
NMsSqlDriver
extends
NObject
implements
ISupplementalDriver{private$connection;function
__construct(NConnection$connection,array$options){$this->connection=$connection;}function
delimite($name){return'['.str_replace(array('[',']'),array('[[',']]'),$name).']';}function
formatBool($value){return$value?'1':'0';}function
formatDateTime(DateTime$value){return$value->format("'Y-m-d H:i:s'");}function
formatLike($value,$pos){$value=strtr($value,array("'"=>"''",'%'=>'[%]','_'=>'[_]','['=>'[[]'));return($pos<=0?"'%":"'").$value.($pos>=0?"%'":"'");}function
applyLimit(&$sql,$limit,$offset){if($limit>=0){$sql='SELECT TOP '.(int)$limit.' * FROM ('.$sql.') t';}if($offset){throw
new
NotImplementedException('Offset is not implemented.');}}function
normalizeRow($row,$statement){return$row;}function
getTables(){throw
new
NotImplementedException;}function
getColumns($table){throw
new
NotImplementedException;}function
getIndexes($table){throw
new
NotImplementedException;}function
getForeignKeys($table){throw
new
NotImplementedException;}function
isSupported($item){return$item===self::SUPPORT_COLUMNS_META;}}class
NMySqlDriver
extends
NObject
implements
ISupplementalDriver{const
ERROR_ACCESS_DENIED=1045;const
ERROR_DUPLICATE_ENTRY=1062;const
ERROR_DATA_TRUNCATED=1265;private$connection;function
__construct(NConnection$connection,array$options){$this->connection=$connection;$charset=isset($options['charset'])?$options['charset']:'utf8';if($charset){$connection->query("SET NAMES '$charset'");}if(isset($options['sqlmode'])){$connection->query("SET sql_mode='$options[sqlmode]'");}$connection->query("SET time_zone='".date('P')."'");}function
delimite($name){return'`'.str_replace('`','``',$name).'`';}function
formatBool($value){return$value?'1':'0';}function
formatDateTime(DateTime$value){return$value->format("'Y-m-d H:i:s'");}function
formatLike($value,$pos){$value=addcslashes(str_replace('\\','\\\\',$value),"\x00\n\r\\'%_");return($pos<=0?"'%":"'").$value.($pos>=0?"%'":"'");}function
applyLimit(&$sql,$limit,$offset){if($limit>=0||$offset>0){$sql.=' LIMIT '.($limit<0?'18446744073709551615':(int)$limit).($offset>0?' OFFSET '.(int)$offset:'');}}function
normalizeRow($row,$statement){return$row;}function
getTables(){$tables=array();foreach($this->connection->query('SHOW FULL TABLES')as$row){$tables[]=array('name'=>$row[0],'view'=>isset($row[1])&&$row[1]==='VIEW');}return$tables;}function
getColumns($table){$columns=array();foreach($this->connection->query('SHOW FULL COLUMNS FROM '.$this->delimite($table))as$row){$type=explode('(',$row['Type']);$columns[]=array('name'=>$row['Field'],'table'=>$table,'nativetype'=>strtoupper($type[0]),'size'=>isset($type[1])?(int)$type[1]:NULL,'unsigned'=>(bool)strstr($row['Type'],'unsigned'),'nullable'=>$row['Null']==='YES','default'=>$row['Default'],'autoincrement'=>$row['Extra']==='auto_increment','primary'=>$row['Key']==='PRI','vendor'=>(array)$row);}return$columns;}function
getIndexes($table){$indexes=array();foreach($this->connection->query('SHOW INDEX FROM '.$this->delimite($table))as$row){$indexes[$row['Key_name']]['name']=$row['Key_name'];$indexes[$row['Key_name']]['unique']=!$row['Non_unique'];$indexes[$row['Key_name']]['primary']=$row['Key_name']==='PRIMARY';$indexes[$row['Key_name']]['columns'][$row['Seq_in_index']-1]=$row['Column_name'];}return
array_values($indexes);}function
getForeignKeys($table){$keys=array();$query='SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE '.'WHERE TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL AND TABLE_NAME = '.$this->connection->quote($table);foreach($this->connection->query($query)as$id=>$row){$keys[$id]['name']=$row['CONSTRAINT_NAME'];$keys[$id]['local']=$row['COLUMN_NAME'];$keys[$id]['table']=$row['REFERENCED_TABLE_NAME'];$keys[$id]['foreign']=$row['REFERENCED_COLUMN_NAME'];}return
array_values($keys);}function
isSupported($item){return$item===self::SUPPORT_COLUMNS_META||$item===self::SUPPORT_SELECT_UNGROUPED_COLUMNS;}}class
NOciDriver
extends
NObject
implements
ISupplementalDriver{private$connection;private$fmtDateTime;function
__construct(NConnection$connection,array$options){$this->connection=$connection;$this->fmtDateTime=isset($options['formatDateTime'])?$options['formatDateTime']:'U';}function
delimite($name){return'"'.str_replace('"','""',$name).'"';}function
formatBool($value){return$value?'1':'0';}function
formatDateTime(DateTime$value){return$value->format($this->fmtDateTime);}function
formatLike($value,$pos){throw
new
NotImplementedException;}function
applyLimit(&$sql,$limit,$offset){if($offset>0){$sql='SELECT * FROM (SELECT t.*, ROWNUM AS "__rnum" FROM ('.$sql.') t '.($limit>=0?'WHERE ROWNUM <= '.((int)$offset+(int)$limit):'').') WHERE "__rnum" > '.(int)$offset;}elseif($limit>=0){$sql='SELECT * FROM ('.$sql.') WHERE ROWNUM <= '.(int)$limit;}}function
normalizeRow($row,$statement){return$row;}function
getTables(){$tables=array();foreach($this->connection->query('SELECT * FROM cat')as$row){if($row[1]==='TABLE'||$row[1]==='VIEW'){$tables[]=array('name'=>$row[0],'view'=>$row[1]==='VIEW');}}return$tables;}function
getColumns($table){throw
new
NotImplementedException;}function
getIndexes($table){throw
new
NotImplementedException;}function
getForeignKeys($table){throw
new
NotImplementedException;}function
isSupported($item){return$item===self::SUPPORT_COLUMNS_META||$item===self::SUPPORT_SEQUENCE;}}class
NOdbcDriver
extends
NObject
implements
ISupplementalDriver{private$connection;function
__construct(NConnection$connection,array$options){$this->connection=$connection;}function
delimite($name){return'['.str_replace(array('[',']'),array('[[',']]'),$name).']';}function
formatBool($value){return$value?'1':'0';}function
formatDateTime(DateTime$value){return$value->format('#m/d/Y H:i:s#');}function
formatLike($value,$pos){$value=strtr($value,array("'"=>"''",'%'=>'[%]','_'=>'[_]','['=>'[[]'));return($pos<=0?"'%":"'").$value.($pos>=0?"%'":"'");}function
applyLimit(&$sql,$limit,$offset){if($limit>=0){$sql='SELECT TOP '.(int)$limit.' * FROM ('.$sql.')';}if($offset){throw
new
InvalidArgumentException('Offset is not implemented in driver odbc.');}}function
normalizeRow($row,$statement){return$row;}function
getTables(){throw
new
NotImplementedException;}function
getColumns($table){throw
new
NotImplementedException;}function
getIndexes($table){throw
new
NotImplementedException;}function
getForeignKeys($table){throw
new
NotImplementedException;}function
isSupported($item){return$item===self::SUPPORT_COLUMNS_META;}}class
NPgSqlDriver
extends
NObject
implements
ISupplementalDriver{private$connection;function
__construct(NConnection$connection,array$options){$this->connection=$connection;}function
delimite($name){return'"'.str_replace('"','""',$name).'"';}function
formatBool($value){return$value?'TRUE':'FALSE';}function
formatDateTime(DateTime$value){return$value->format("'Y-m-d H:i:s'");}function
formatLike($value,$pos){$value=strtr($value,array("'"=>"''",'\\'=>'\\\\','%'=>'\\\\%','_'=>'\\\\_'));return($pos<=0?"'%":"'").$value.($pos>=0?"%'":"'");}function
applyLimit(&$sql,$limit,$offset){if($limit>=0){$sql.=' LIMIT '.(int)$limit;}if($offset>0){$sql.=' OFFSET '.(int)$offset;}}function
normalizeRow($row,$statement){return$row;}function
getTables(){$tables=array();foreach($this->connection->query("
			SELECT
				c.relname::varchar AS name,
				c.relkind = 'v' AS view
			FROM
				pg_catalog.pg_class AS c
				JOIN pg_catalog.pg_namespace AS n ON n.oid = c.relnamespace
			WHERE
				c.relkind IN ('r', 'v')
				AND ARRAY[n.nspname] <@ pg_catalog.current_schemas(FALSE)
			ORDER BY
				c.relname
		")as$row){$tables[]=(array)$row;}return$tables;}function
getColumns($table){$columns=array();foreach($this->connection->query("
			SELECT
				a.attname::varchar AS name,
				c.relname::varchar AS table,
				upper(t.typname) AS nativetype,
				NULL AS size,
				FALSE AS unsigned,
				NOT (a.attnotnull OR t.typtype = 'd' AND t.typnotnull) AS nullable,
				pg_catalog.pg_get_expr(ad.adbin, 'pg_catalog.pg_attrdef'::regclass)::varchar AS default,
				coalesce(co.contype = 'p' AND strpos(ad.adsrc, 'nextval') = 1, FALSE) AS autoincrement,
				coalesce(co.contype = 'p', FALSE) AS primary,
				substring(pg_catalog.pg_get_expr(ad.adbin, 'pg_catalog.pg_attrdef'::regclass) from 'nextval[(]''\"?([^''\"]+)') AS sequence
			FROM
				pg_catalog.pg_attribute AS a
				JOIN pg_catalog.pg_class AS c ON a.attrelid = c.oid
				JOIN pg_catalog.pg_namespace AS n ON n.oid = c.relnamespace
				JOIN pg_catalog.pg_type AS t ON a.atttypid = t.oid
				LEFT JOIN pg_catalog.pg_attrdef AS ad ON ad.adrelid = c.oid AND ad.adnum = a.attnum
				LEFT JOIN pg_catalog.pg_constraint AS co ON co.connamespace = n.oid AND contype = 'p' AND co.conrelid = c.oid AND a.attnum = ANY(co.conkey)
			WHERE
				c.relkind IN ('r', 'v')
				AND c.relname::varchar = {$this->connection->quote($table)}
				AND ARRAY[n.nspname] <@ pg_catalog.current_schemas(FALSE)
				AND a.attnum > 0
				AND NOT a.attisdropped
			ORDER BY
				a.attnum
		")as$row){$column=(array)$row;$column['vendor']=$column;unset($column['sequence']);$columns[]=$column;}return$columns;}function
getIndexes($table){$indexes=array();foreach($this->connection->query("
			SELECT
				c2.relname::varchar AS name,
				i.indisunique AS unique,
				i.indisprimary AS primary,
				a.attname::varchar AS column
			FROM
				pg_catalog.pg_class AS c1
				JOIN pg_catalog.pg_namespace AS n ON c1.relnamespace = n.oid
				JOIN pg_catalog.pg_index AS i ON c1.oid = i.indrelid
				JOIN pg_catalog.pg_class AS c2 ON i.indexrelid = c2.oid
				LEFT JOIN pg_catalog.pg_attribute AS a ON c1.oid = a.attrelid AND a.attnum = ANY(i.indkey)
			WHERE
				ARRAY[n.nspname] <@ pg_catalog.current_schemas(FALSE)
				AND c1.relkind = 'r'
				AND c1.relname = {$this->connection->quote($table)}
		")as$row){$indexes[$row['name']]['name']=$row['name'];$indexes[$row['name']]['unique']=$row['unique'];$indexes[$row['name']]['primary']=$row['primary'];$indexes[$row['name']]['columns'][]=$row['column'];}return
array_values($indexes);}function
getForeignKeys($table){return$this->connection->query("
			SELECT
				co.conname::varchar AS name,
				al.attname::varchar AS local,
				cf.relname::varchar AS table,
				af.attname::varchar AS foreign
			FROM
				pg_catalog.pg_constraint AS co
				JOIN pg_catalog.pg_namespace AS n ON co.connamespace = n.oid
				JOIN pg_catalog.pg_class AS cl ON co.conrelid = cl.oid
				JOIN pg_catalog.pg_class AS cf ON co.confrelid = cf.oid
				JOIN pg_catalog.pg_attribute AS al ON al.attrelid = cl.oid AND al.attnum = co.conkey[1]
				JOIN pg_catalog.pg_attribute AS af ON af.attrelid = cf.oid AND af.attnum = co.confkey[1]
			WHERE
				ARRAY[n.nspname] <@ pg_catalog.current_schemas(FALSE)
				AND co.contype = 'f'
				AND cl.relname = {$this->connection->quote($table)}
		")->fetchAll();}function
isSupported($item){return$item===self::SUPPORT_COLUMNS_META||$item===self::SUPPORT_SEQUENCE;}}class
NSqliteDriver
extends
NObject
implements
ISupplementalDriver{private$connection;private$fmtDateTime;function
__construct(NConnection$connection,array$options){$this->connection=$connection;$this->fmtDateTime=isset($options['formatDateTime'])?$options['formatDateTime']:'U';}function
delimite($name){return'['.strtr($name,'[]','  ').']';}function
formatBool($value){return$value?'1':'0';}function
formatDateTime(DateTime$value){return$value->format($this->fmtDateTime);}function
formatLike($value,$pos){$value=addcslashes(substr($this->connection->quote($value),1,-1),'%_\\');return($pos<=0?"'%":"'").$value.($pos>=0?"%'":"'")." ESCAPE '\\'";}function
applyLimit(&$sql,$limit,$offset){if($limit>=0||$offset>0){$sql.=' LIMIT '.$limit.($offset>0?' OFFSET '.(int)$offset:'');}}function
normalizeRow($row,$statement){foreach($row
as$key=>$value){unset($row[$key]);if($key[0]==='['||$key[0]==='"'){$key=substr($key,1,-1);}$row[$key]=$value;}return$row;}function
getTables(){$tables=array();foreach($this->connection->query("
			SELECT name, type = 'view' as view FROM sqlite_master WHERE type IN ('table', 'view') AND name NOT LIKE 'sqlite_%'
			UNION ALL
			SELECT name, type = 'view' as view FROM sqlite_temp_master WHERE type IN ('table', 'view') AND name NOT LIKE 'sqlite_%'
			ORDER BY name
		")as$row){$tables[]=array('name'=>$row->name,'view'=>(bool)$row->view);}return$tables;}function
getColumns($table){$meta=$this->connection->query("
			SELECT sql FROM sqlite_master WHERE type = 'table' AND name = {$this->connection->quote($table)}
			UNION ALL
			SELECT sql FROM sqlite_temp_master WHERE type = 'table' AND name = {$this->connection->quote($table)}
		")->fetch();$columns=array();foreach($this->connection->query("PRAGMA table_info({$this->delimite($table)})")as$row){$column=$row['name'];$pattern="/(\"$column\"|\[$column\]|$column)\\s+[^,]+\\s+PRIMARY\\s+KEY\\s+AUTOINCREMENT/Ui";$type=explode('(',$row['type']);$columns[]=array('name'=>$column,'table'=>$table,'nativetype'=>strtoupper($type[0]),'size'=>isset($type[1])?(int)$type[1]:NULL,'unsigned'=>FALSE,'nullable'=>$row['notnull']=='0','default'=>$row['dflt_value'],'autoincrement'=>(bool)preg_match($pattern,$meta['sql']),'primary'=>$row['pk']>0,'vendor'=>(array)$row);}return$columns;}function
getIndexes($table){$indexes=array();foreach($this->connection->query("PRAGMA index_list({$this->delimite($table)})")as$row){$indexes[$row['name']]['name']=$row['name'];$indexes[$row['name']]['unique']=(bool)$row['unique'];$indexes[$row['name']]['primary']=FALSE;}foreach($indexes
as$index=>$values){$res=$this->connection->query("PRAGMA index_info({$this->delimite($index)})");while($row=$res->fetch(TRUE)){$indexes[$index]['columns'][$row['seqno']]=$row['name'];}}$columns=$this->getColumns($table);foreach($indexes
as$index=>$values){$column=$indexes[$index]['columns'][0];foreach($columns
as$info){if($column==$info['name']){$indexes[$index]['primary']=(bool)$info['primary'];break;}}}if(!$indexes){foreach($columns
as$column){if($column['vendor']['pk']){$indexes[]=array('name'=>'ROWID','unique'=>TRUE,'primary'=>TRUE,'columns'=>array($column['name']));break;}}}return
array_values($indexes);}function
getForeignKeys($table){$keys=array();foreach($this->connection->query("PRAGMA foreign_key_list({$this->delimite($table)})")as$row){$keys[$row['id']]['name']=$row['id'];$keys[$row['id']]['local']=$row['from'];$keys[$row['id']]['table']=$row['table'];$keys[$row['id']]['foreign']=$row['to'];$keys[$row['id']]['onDelete']=$row['on_delete'];$keys[$row['id']]['onUpdate']=$row['on_update'];if($keys[$row['id']]['foreign'][0]==NULL){$keys[$row['id']]['foreign']=NULL;}}return
array_values($keys);}function
isSupported($item){return
FALSE;}}class
NSqlite2Driver
extends
NSqliteDriver{function
formatLike($value,$pos){throw
new
NotSupportedException;}function
getForeignKeys($table){throw
new
NotSupportedException;}}class
NDatabaseHelpers{public
static$typePatterns=array('^_'=>IReflection::FIELD_TEXT,'BYTEA|BLOB|BIN'=>IReflection::FIELD_BINARY,'TEXT|CHAR|POINT|INTERVAL'=>IReflection::FIELD_TEXT,'YEAR|BYTE|COUNTER|SERIAL|INT|LONG|SHORT|^TINY$'=>IReflection::FIELD_INTEGER,'CURRENCY|REAL|MONEY|FLOAT|DOUBLE|DECIMAL|NUMERIC|NUMBER'=>IReflection::FIELD_FLOAT,'^TIME$'=>IReflection::FIELD_TIME,'TIME'=>IReflection::FIELD_DATETIME,'DATE'=>IReflection::FIELD_DATE,'BOOL'=>IReflection::FIELD_BOOL);static
function
dumpResult(NStatement$statement){echo"\n<table class=\"dump\">\n<caption>".htmlSpecialChars($statement->queryString)."</caption>\n";if(!$statement->columnCount()){echo"\t<tr>\n\t\t<th>Affected rows:</th>\n\t\t<td>",$statement->rowCount(),"</td>\n\t</tr>\n</table>\n";return;}$i=0;foreach($statement
as$row){if($i===0){echo"<thead>\n\t<tr>\n\t\t<th>#row</th>\n";foreach($row
as$col=>$foo){echo"\t\t<th>".htmlSpecialChars($col)."</th>\n";}echo"\t</tr>\n</thead>\n<tbody>\n";}echo"\t<tr>\n\t\t<th>",$i,"</th>\n";foreach($row
as$col){echo"\t\t<td>",htmlSpecialChars($col),"</td>\n";}echo"\t</tr>\n";$i++;}if($i===0){echo"\t<tr>\n\t\t<td><em>empty result set</em></td>\n\t</tr>\n</table>\n";}else{echo"</tbody>\n</table>\n";}}static
function
dumpSql($sql){static$keywords1='SELECT|(?:ON\s+DUPLICATE\s+KEY)?UPDATE|INSERT(?:\s+INTO)?|REPLACE(?:\s+INTO)?|DELETE|CALL|UNION|FROM|WHERE|HAVING|GROUP\s+BY|ORDER\s+BY|LIMIT|OFFSET|SET|VALUES|LEFT\s+JOIN|INNER\s+JOIN|TRUNCATE';static$keywords2='ALL|DISTINCT|DISTINCTROW|IGNORE|AS|USING|ON|AND|OR|IN|IS|NOT|NULL|[RI]?LIKE|REGEXP|TRUE|FALSE';$sql=" $sql ";$sql=preg_replace("#(?<=[\\s,(])($keywords1)(?=[\\s,)])#i","\n\$1",$sql);$sql=preg_replace('#[ \t]{2,}#',' ',$sql);$sql=wordwrap($sql,100);$sql=preg_replace('#([ \t]*\r?\n){2,}#',"\n",$sql);$sql=htmlSpecialChars($sql);$sql=preg_replace_callback("#(/\\*.+?\\*/)|(\\*\\*.+?\\*\\*)|(?<=[\\s,(])($keywords1)(?=[\\s,)])|(?<=[\\s,(=])($keywords2)(?=[\\s,)=])#is",create_function('$matches','
			if (!empty($matches[1])) { // comment
				return \'<em style="color:gray">\' . $matches[1] . \'</em>\';

			} elseif (!empty($matches[2])) { // error
				return \'<strong style="color:red">\' . $matches[2] . \'</strong>\';

			} elseif (!empty($matches[3])) { // most important keywords
				return \'<strong style="color:blue">\' . $matches[3] . \'</strong>\';

			} elseif (!empty($matches[4])) { // other keywords
				return \'<strong style="color:green">\' . $matches[4] . \'</strong>\';
			}
		'),$sql);return'<pre class="dump">'.trim($sql)."</pre>\n";}static
function
detectType($type){static$cache;if(!isset($cache[$type])){$cache[$type]='string';foreach(self::$typePatterns
as$s=>$val){if(preg_match("#$s#i",$type)){return$cache[$type]=$val;}}}return$cache[$type];}static
function
loadFromFile(NConnection$connection,$file){@set_time_limit(0);$handle=@fopen($file,'r');if(!$handle){throw
new
FileNotFoundException("Cannot open file '$file'.");}$count=0;$sql='';while(!feof($handle)){$s=fgets($handle);$sql.=$s;if(substr(rtrim($s),-1)===';'){$connection->query($sql);$sql='';$count++;}}if(trim($sql)!==''){$connection->query($sql);$count++;}fclose($handle);return$count;}}class
NConventionalReflection
extends
NObject
implements
IReflection{protected$primary;protected$foreign;protected$table;function
__construct($primary='id',$foreign='%s_id',$table='%s'){$this->primary=$primary;$this->foreign=$foreign;$this->table=$table;}function
getPrimary($table){return
sprintf($this->primary,$this->getColumnFromTable($table));}function
getHasManyReference($table,$key){$table=$this->getColumnFromTable($table);return
array(sprintf($this->table,$key,$table),sprintf($this->foreign,$table,$key));}function
getBelongsToReference($table,$key){$table=$this->getColumnFromTable($table);return
array(sprintf($this->table,$key,$table),sprintf($this->foreign,$key,$table));}function
setConnection(NConnection$connection){}protected
function
getColumnFromTable($name){if($this->table!=='%s'&&preg_match('(^'.str_replace('%s','(.*)',preg_quote($this->table)).'\z)',$name,$match)){return$match[1];}return$name;}}class
NDiscoveredReflection
extends
NObject
implements
IReflection{protected$cache;protected$cacheStorage;protected$connection;protected$structure=array();protected$loadedStructure;function
__construct(ICacheStorage$storage=NULL){$this->cacheStorage=$storage;}function
setConnection(NConnection$connection){$this->connection=$connection;if($this->cacheStorage){$this->cache=new
NCache($this->cacheStorage,'Nette.Database.'.md5($connection->getDsn()));$this->structure=$this->loadedStructure=($tmp=$this->cache->load('structure'))?$tmp:$this->structure;}}function
__destruct(){if($this->cache&&$this->structure!==$this->loadedStructure){$this->cache->save('structure',$this->structure);}}function
getPrimary($table){$primary=&$this->structure['primary'][strtolower($table)];if(isset($primary)){return
empty($primary)?NULL:$primary;}$columns=$this->connection->getSupplementalDriver()->getColumns($table);$primary=array();foreach($columns
as$column){if($column['primary']){$primary[]=$column['name'];}}if(count($primary)===0){return
NULL;}elseif(count($primary)===1){$primary=reset($primary);}return$primary;}function
getHasManyReference($table,$key,$refresh=TRUE){if(isset($this->structure['hasMany'][strtolower($table)])){$candidates=$columnCandidates=array();foreach($this->structure['hasMany'][strtolower($table)]as$targetPair){list($targetColumn,$targetTable)=$targetPair;if(stripos($targetTable,$key)===FALSE){continue;}$candidates[]=array($targetTable,$targetColumn);if(stripos($targetColumn,$table)!==FALSE){$columnCandidates[]=$candidate=array($targetTable,$targetColumn);if(strtolower($targetTable)===strtolower($key)){return$candidate;}}}if(count($columnCandidates)===1){return
reset($columnCandidates);}elseif(count($candidates)===1){return
reset($candidates);}foreach($candidates
as$candidate){if(strtolower($candidate[0])===strtolower($key)){return$candidate;}}}if($refresh){$this->reloadAllForeignKeys();return$this->getHasManyReference($table,$key,FALSE);}if(empty($candidates)){throw
new
NMissingReferenceException("No reference found for \${$table}->related({$key}).");}else{throw
new
NAmbiguousReferenceKeyException('Ambiguous joining column in related call.');}}function
getBelongsToReference($table,$key,$refresh=TRUE){if(isset($this->structure['belongsTo'][strtolower($table)])){foreach($this->structure['belongsTo'][strtolower($table)]as$column=>$targetTable){if(stripos($column,$key)!==FALSE){return
array($targetTable,$column);}}}if($refresh){$this->reloadForeignKeys($table);return$this->getBelongsToReference($table,$key,FALSE);}throw
new
NMissingReferenceException("No reference found for \${$table}->{$key}.");}protected
function
reloadAllForeignKeys(){$this->structure['hasMany']=$this->structure['belongsTo']=array();foreach($this->connection->getSupplementalDriver()->getTables()as$table){if($table['view']==FALSE){$this->reloadForeignKeys($table['name']);}}foreach($this->structure['hasMany']as&$table){uksort($table,create_function('$a, $b','
				return strlen($a) - strlen($b);
			'));}}protected
function
reloadForeignKeys($table){foreach($this->connection->getSupplementalDriver()->getForeignKeys($table)as$row){$this->structure['belongsTo'][strtolower($table)][$row['local']]=$row['table'];$this->structure['hasMany'][strtolower($row['table'])][$row['local'].$table]=array($row['local'],$table);}if(isset($this->structure['belongsTo'][$table])){uksort($this->structure['belongsTo'][$table],create_function('$a, $b','
				return strlen($a) - strlen($b);
			'));}}}if(class_exists('PDO')){class
NMissingReferenceException
extends
PDOException{}class
NAmbiguousReferenceKeyException
extends
PDOException{}}class
NRow
extends
NArrayHash{function
__construct(NStatement$statement){$data=array();foreach((array)$this
as$key=>$value){$data[$key]=$value;unset($this->$key);}foreach($statement->normalizeRow($data)as$key=>$value){$this->$key=$value;}}function
offsetGet($key){if(is_int($key)){$arr=array_slice((array)$this,$key,1);if(!$arr){trigger_error('Undefined offset: '.__CLASS__."[$key]",E_USER_NOTICE);}return
current($arr);}return$this->$key;}function
offsetExists($key){if(is_int($key)){return(bool)array_slice((array)$this,$key,1);}return
parent::offsetExists($key);}}class
NSqlLiteral
extends
NObject{private$value='';function
__construct($value){$this->value=(string)$value;}function
__toString(){return$this->value;}}class
NSqlPreprocessor
extends
NObject{private$connection;private$driver;private$params;private$remaining;private$counter;private$arrayMode;function
__construct(NConnection$connection){$this->connection=$connection;$this->driver=$connection->getSupplementalDriver();}function
process($sql,$params){$this->params=$params;$this->counter=0;$this->remaining=array();$this->arrayMode='assoc';$sql=NStrings::replace($sql,'~\'.*?\'|".*?"|\?|\b(?:INSERT|REPLACE|UPDATE)\b|/\*.*?\*/|--[^\n]*~si',array($this,'callback'));while($this->counter<count($params)){$sql.=' '.$this->formatValue($params[$this->counter++]);}return
array($sql,$this->remaining);}function
callback($m){$m=$m[0];if($m[0]==="'"||$m[0]==='"'||$m[0]==='/'||$m[0]==='-'){return$m;}elseif($m==='?'){if($this->counter>=count($this->params)){throw
new
InvalidArgumentException('There are more placeholders than passed parameters.');}return$this->formatValue($this->params[$this->counter++]);}else{$this->arrayMode=strtoupper($m)==='UPDATE'?'assoc':'values';return$m;}}private
function
formatValue($value){if(is_string($value)){if(strlen($value)>20){$this->remaining[]=$value;return'?';}else{return$this->connection->quote($value);}}elseif(is_int($value)){return(string)$value;}elseif(is_float($value)){return
rtrim(rtrim(number_format($value,10,'.',''),'0'),'.');}elseif(is_bool($value)){return$this->driver->formatBool($value);}elseif($value===NULL){return'NULL';}elseif($value
instanceof
NTableRow){return$value->getPrimary();}elseif(is_array($value)||$value
instanceof
Traversable){$vx=$kx=array();if($value
instanceof
Traversable){$value=iterator_to_array($value);}if(isset($value[0])){foreach($value
as$v){$vx[]=$this->formatValue($v);}return
implode(', ',$vx);}elseif($this->arrayMode==='values'){$this->arrayMode='multi';foreach($value
as$k=>$v){$kx[]=$this->driver->delimite($k);$vx[]=$this->formatValue($v);}return'('.implode(', ',$kx).') VALUES ('.implode(', ',$vx).')';}elseif($this->arrayMode==='assoc'){foreach($value
as$k=>$v){$vx[]=$this->driver->delimite($k).'='.$this->formatValue($v);}return
implode(', ',$vx);}elseif($this->arrayMode==='multi'){foreach($value
as$v){$vx[]=$this->formatValue($v);}return'('.implode(', ',$vx).')';}}elseif($value
instanceof
DateTime||$value
instanceof
DateTimeInterface){return$this->driver->formatDateTime($value);}elseif($value
instanceof
NSqlLiteral){return$value->__toString();}else{$this->remaining[]=$value;return'?';}}}if(class_exists('PDO')){class
NStatement
extends
PDOStatement{private$connection;private$time;private$types;protected
function
__construct(NConnection$connection){$this->connection=$connection;$this->setFetchMode(PDO::FETCH_CLASS,'NRow',array($this));}function
getConnection(){return$this->connection;}function
getQueryString(){return$this->queryString;}function
getColumnCount(){return$this->columnCount();}function
getRowCount(){return$this->rowCount();}function
execute($params=array()){static$types=array('boolean'=>PDO::PARAM_BOOL,'integer'=>PDO::PARAM_INT,'resource'=>PDO::PARAM_LOB,'NULL'=>PDO::PARAM_NULL);foreach($params
as$key=>$value){$type=gettype($value);$this->bindValue(is_int($key)?$key+1:$key,$value,isset($types[$type])?$types[$type]:PDO::PARAM_STR);}$time=microtime(TRUE);try{parent::execute();}catch(PDOException$e){$e->queryString=$this->queryString;throw$e;}$this->time=microtime(TRUE)-$time;$this->connection->__call('onQuery',array($this,$params));return$this;}function
fetchPairs(){return$this->fetchAll(PDO::FETCH_KEY_PAIR);}function
fetchField($column=0){$row=$this->fetch();return$row?$row[$column]:FALSE;}function
normalizeRow($row){foreach($this->detectColumnTypes()as$key=>$type){$value=$row[$key];if($value===NULL||$value===FALSE||$type===IReflection::FIELD_TEXT){}elseif($type===IReflection::FIELD_INTEGER){$row[$key]=is_float($tmp=$value*1)?$value:$tmp;}elseif($type===IReflection::FIELD_FLOAT){$value=strpos($value,'.')===FALSE?$value:rtrim(rtrim($value,'0'),'.');$float=(float)$value;$row[$key]=(string)$float===$value?$float:$value;}elseif($type===IReflection::FIELD_BOOL){$row[$key]=((bool)$value)&&$value!=='f'&&$value!=='F';}elseif($type===IReflection::FIELD_DATETIME||$type===IReflection::FIELD_DATE||$type===IReflection::FIELD_TIME){$row[$key]=new
NDateTime53($value);}}return$this->connection->getSupplementalDriver()->normalizeRow($row,$this);}private
function
detectColumnTypes(){if($this->types===NULL){$this->types=array();if($this->connection->getSupplementalDriver()->isSupported(ISupplementalDriver::SUPPORT_COLUMNS_META)){$count=$this->columnCount();for($col=0;$col<$count;$col++){$meta=$this->getColumnMeta($col);if(isset($meta['native_type'])){$this->types[$meta['name']]=NDatabaseHelpers::detectType($meta['native_type']);}}}}return$this->types;}function
getTime(){return$this->time;}function
dump(){NDatabaseHelpers::dumpResult($this);}function
getReflection(){return
new
NClassReflection($this);}function
__call($name,$args){return
NObjectMixin::call($this,$name,$args);}function&__get($name){return
NObjectMixin::get($this,$name);}function
__set($name,$value){return
NObjectMixin::set($this,$name,$value);}function
__isset($name){return
NObjectMixin::has($this,$name);}function
__unset($name){NObjectMixin::remove($this,$name);}}}class
NTableRow
extends
NObject
implements
IteratorAggregate,ArrayAccess{private$table;private$data;private$dataRefreshed=FALSE;private$modified=array();function
__construct(array$data,NTableSelection$table){$this->data=$data;$this->table=$table;}function
setTable(NTableSelection$table){$this->table=$table;}function
getTable(){return$this->table;}function
__toString(){try{return(string)$this->getPrimary();}catch(Exception$e){if(func_num_args()){throw$e;}trigger_error("Exception in ".__METHOD__."(): {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}",E_USER_ERROR);}}function
toArray(){$this->accessColumn(NULL);return$this->data;}function
getPrimary($need=TRUE){$primary=$this->table->getPrimary($need);if($primary===NULL){return
NULL;}elseif(!is_array($primary)){if(isset($this->data[$primary])){return$this->data[$primary];}elseif($need){throw
new
InvalidStateException("Row does not contain primary $primary column data.");}else{return
NULL;}}else{$primaryVal=array();foreach($primary
as$key){if(!isset($this->data[$key])){if($need){throw
new
InvalidStateException("Row does not contain primary $key column data.");}else{return
NULL;}}$primaryVal[$key]=$this->data[$key];}return$primaryVal;}}function
getSignature($need=TRUE){return
implode('|',(array)$this->getPrimary($need));}function
ref($key,$throughColumn=NULL){if(!$throughColumn){list($key,$throughColumn)=$this->table->getConnection()->getDatabaseReflection()->getBelongsToReference($this->table->getName(),$key);}return$this->getReference($key,$throughColumn);}function
related($key,$throughColumn=NULL){if(strpos($key,'.')!==FALSE){list($key,$throughColumn)=explode('.',$key);}elseif(!$throughColumn){list($key,$throughColumn)=$this->table->getConnection()->getDatabaseReflection()->getHasManyReference($this->table->getName(),$key);}return$this->table->getReferencingTable($key,$throughColumn,$this[$this->table->getPrimary()]);}function
update($data=NULL){if($data
instanceof
Traversable){$data=iterator_to_array($data);}if($data===NULL){$data=$this->modified;}return$this->table->getConnection()->table($this->table->getName())->wherePrimary($this->getPrimary())->update($data);}function
delete(){$res=$this->table->getConnection()->table($this->table->getName())->wherePrimary($this->getPrimary())->delete();if($res>0&&($signature=$this->getSignature(FALSE))){unset($this->table[$signature]);}return$res;}function
getIterator(){$this->accessColumn(NULL);return
new
ArrayIterator($this->data);}function
offsetSet($key,$value){$this->__set($key,$value);}function
offsetGet($key){return$this->__get($key);}function
offsetExists($key){return$this->__isset($key);}function
offsetUnset($key){$this->__unset($key);}function
__set($key,$value){$this->data[$key]=$value;$this->modified[$key]=$value;}function&__get($key){$this->accessColumn($key);if(array_key_exists($key,$this->data)){return$this->data[$key];}try{list($table,$column)=$this->table->getConnection()->getDatabaseReflection()->getBelongsToReference($this->table->getName(),$key);$referenced=$this->getReference($table,$column);if($referenced!==FALSE){$this->accessColumn($key,FALSE);return$referenced;}}catch(NMissingReferenceException$e){}$this->removeAccessColumn($key);throw
new
MemberAccessException("Cannot read an undeclared column '$key'.");}function
__isset($key){$this->accessColumn($key);if(array_key_exists($key,$this->data)){return
isset($this->data[$key]);}$this->removeAccessColumn($key);return
FALSE;}function
__unset($key){unset($this->data[$key]);unset($this->modified[$key]);}protected
function
accessColumn($key,$selectColumn=TRUE){if(isset($this->modified[$key])){return;}$this->table->accessColumn($key,$selectColumn);if($this->table->getDataRefreshed()&&!$this->dataRefreshed){$this->data=$this->table[$this->getSignature()]->data;$this->dataRefreshed=TRUE;}}protected
function
removeAccessColumn($key){$this->table->removeAccessColumn($key);}protected
function
getReference($table,$column){$this->accessColumn($column);if(array_key_exists($column,$this->data)){$value=$this->data[$column];$value=$value
instanceof
NTableRow?$value->getPrimary():$value;$referenced=$this->table->getReferencedTable($table,$column,!empty($this->modified[$column]));$referenced=isset($referenced[$value])?$referenced[$value]:NULL;if(!empty($this->modified[$column])){$this->modified[$column]=0;}return$referenced;}return
FALSE;}}class
NTableSelection
extends
NObject
implements
Iterator,ArrayAccess,Countable{protected$connection;protected$cache;protected$sqlBuilder;protected$name;protected$primary;protected$primarySequence=FALSE;protected$rows;protected$data;protected$dataRefreshed=FALSE;protected$referenced=array();protected$referencing=array();protected$referencingPrototype=array();protected$aggregation=array();protected$accessedColumns;protected$previousAccessedColumns;protected$observeCache=FALSE;protected$checkReferenced=FALSE;protected$keys=array();function
__construct($table,NConnection$connection){$this->name=$table;$this->connection=$connection;$reflection=$connection->getDatabaseReflection();$this->primary=$reflection->getPrimary($table);$this->sqlBuilder=new
NSqlBuilder($table,$connection,$reflection);$this->cache=$connection->getCache();}function
__destruct(){$this->saveCacheState();}function
__clone(){$this->sqlBuilder=clone$this->sqlBuilder;}function
getConnection(){return$this->connection;}function
getName(){return$this->name;}function
getPrimary($need=TRUE){if($this->primary===NULL&&$need){throw
new
LogicException("Table '{$this->name}' does not have a primary key.");}return$this->primary;}function
getPrimarySequence(){if($this->primarySequence===FALSE){$this->primarySequence=NULL;$driver=$this->connection->getSupplementalDriver();if($driver->isSupported(ISupplementalDriver::SUPPORT_SEQUENCE)&&$this->primary!==NULL){foreach($driver->getColumns($this->name)as$column){if($column['name']===$this->primary){$this->primarySequence=$column['vendor']['sequence'];break;}}}}return$this->primarySequence;}function
setPrimarySequence($sequence){$this->primarySequence=$sequence;return$this;}function
getSql(){return$this->sqlBuilder->buildSelectQuery($this->getPreviousAccessedColumns());}function
getPreviousAccessedColumns(){if($this->cache&&$this->previousAccessedColumns===NULL){$this->accessedColumns=$this->previousAccessedColumns=$this->cache->load($this->getCacheKey());}return
array_keys(array_filter((array)$this->previousAccessedColumns));}function
getSqlBuilder(){return$this->sqlBuilder;}function
get($key){$clone=clone$this;return$clone->wherePrimary($key)->fetch();}function
fetch(){$this->execute();$return=current($this->data);next($this->data);return$return;}function
fetchPairs($key,$value=NULL){$return=array();foreach($this
as$row){$return[is_object($row[$key])?(string)$row[$key]:$row[$key]]=($value?$row[$value]:$row);}return$return;}function
select($columns){$this->emptyResultSet();$this->sqlBuilder->addSelect($columns);return$this;}function
find($key){return$this->wherePrimary($key);}function
wherePrimary($key){if(is_array($this->primary)&&NValidators::isList($key)){foreach($this->primary
as$i=>$primary){$this->where($primary,$key[$i]);}}elseif(is_array($key)){$this->where($key);}else{$this->where($this->getPrimary(),$key);}return$this;}function
where($condition,$parameters=array()){if(is_array($condition)){foreach($condition
as$key=>$val){if(is_int($key)){$this->where($val);}else{$this->where($key,$val);}}return$this;}$_args=func_get_args();if(call_user_func_array(array($this->sqlBuilder,'addWhere'),$_args)){$this->emptyResultSet();}return$this;}function
order($columns){$this->emptyResultSet();$this->sqlBuilder->addOrder($columns);return$this;}function
limit($limit,$offset=NULL){$this->emptyResultSet();$this->sqlBuilder->setLimit($limit,$offset);return$this;}function
page($page,$itemsPerPage){return$this->limit($itemsPerPage,($page-1)*$itemsPerPage);}function
group($columns,$having=NULL){$this->emptyResultSet();$this->sqlBuilder->setGroup($columns,$having);return$this;}function
aggregation($function){$selection=$this->createSelectionInstance();$selection->getSqlBuilder()->importConditions($this->getSqlBuilder());$selection->select($function);foreach($selection->fetch()as$val){return$val;}}function
count($column=NULL){if(!$column){$this->execute();return
count($this->data);}return$this->aggregation("COUNT($column)");}function
min($column){return$this->aggregation("MIN($column)");}function
max($column){return$this->aggregation("MAX($column)");}function
sum($column){return$this->aggregation("SUM($column)");}protected
function
execute(){if($this->rows!==NULL){return;}$this->observeCache=$this;try{$result=$this->query($this->getSql());}catch(PDOException$exception){if(!$this->sqlBuilder->getSelect()&&$this->previousAccessedColumns){$this->previousAccessedColumns=FALSE;$this->accessedColumns=array();$result=$this->query($this->getSql());}else{throw$exception;}}$this->rows=array();$usedPrimary=TRUE;$result->setFetchMode(PDO::FETCH_ASSOC);foreach($result
as$key=>$row){$row=$this->createRow($result->normalizeRow($row));$primary=$row->getSignature(FALSE);$usedPrimary=$usedPrimary&&$primary;$this->rows[($tmp=$primary)?$tmp:$key]=$row;}$this->data=$this->rows;if($usedPrimary&&$this->accessedColumns!==FALSE){foreach((array)$this->primary
as$primary){$this->accessedColumns[$primary]=TRUE;}}}protected
function
createRow(array$row){return
new
NTableRow($row,$this);}protected
function
createSelectionInstance($table=NULL){return
new
NTableSelection(($tmp=$table)?$tmp:$this->name,$this->connection);}protected
function
createGroupedSelectionInstance($table,$column){return
new
NGroupedTableSelection($this,$table,$column);}protected
function
query($query){return$this->connection->queryArgs($query,$this->sqlBuilder->getParameters());}protected
function
emptyResultSet(){$this->rows=NULL;}protected
function
saveCacheState(){if($this->observeCache===$this&&$this->cache&&!$this->sqlBuilder->getSelect()&&$this->accessedColumns!=$this->previousAccessedColumns){$this->cache->save($this->getCacheKey(),$this->accessedColumns);}}protected
function
getRefTable(&$refPath){return$this;}protected
function
getCacheKey(){return
md5(serialize(array(__CLASS__,$this->name,$this->sqlBuilder->getConditions())));}function
accessColumn($key,$selectColumn=TRUE){if(!$this->cache){return;}if($key===NULL){$this->accessedColumns=FALSE;$currentKey=key((array)$this->data);}elseif($this->accessedColumns!==FALSE){$this->accessedColumns[$key]=$selectColumn;}if($selectColumn&&!$this->sqlBuilder->getSelect()&&$this->previousAccessedColumns&&($key===NULL||!isset($this->previousAccessedColumns[$key]))){$this->previousAccessedColumns=FALSE;$this->emptyResultSet();$this->dataRefreshed=TRUE;if($key===NULL){$this->execute();while(key($this->data)!==$currentKey){next($this->data);}}}}function
removeAccessColumn($key){if($this->cache&&is_array($this->accessedColumns)){$this->accessedColumns[$key]=FALSE;}}function
getDataRefreshed(){return$this->dataRefreshed;}function
insert($data){if($data
instanceof
NTableSelection){$data=$data->getSql();}elseif($data
instanceof
Traversable){$data=iterator_to_array($data);}$return=$this->connection->query($this->sqlBuilder->buildInsertQuery(),$data);$this->checkReferenced=TRUE;if(!is_array($data)){return$return->rowCount();}if(!is_array($this->primary)&&!isset($data[$this->primary])&&($id=$this->connection->lastInsertId($this->getPrimarySequence()))){$data[$this->primary]=$id;}$row=$this->createRow($data);if($signature=$row->getSignature(FALSE)){$this->rows[$signature]=$row;}return$row;}function
update($data){if($data
instanceof
Traversable){$data=iterator_to_array($data);}elseif(!is_array($data)){throw
new
InvalidArgumentException;}if(!$data){return
0;}return$this->connection->queryArgs($this->sqlBuilder->buildUpdateQuery(),array_merge(array($data),$this->sqlBuilder->getParameters()))->rowCount();}function
delete(){return$this->query($this->sqlBuilder->buildDeleteQuery())->rowCount();}function
getReferencedTable($table,$column,$checkReferenced=FALSE){$referenced=&$this->getRefTable($refPath)->referenced[$refPath."$table.$column"];if($referenced===NULL||$checkReferenced||$this->checkReferenced){$this->execute();$this->checkReferenced=FALSE;$keys=array();foreach($this->rows
as$row){if($row[$column]===NULL){continue;}$key=$row[$column]instanceof
NTableRow?$row[$column]->getPrimary():$row[$column];$keys[$key]=TRUE;}if($referenced!==NULL){$a=array_keys($keys);$b=array_keys($referenced->rows);sort($a);sort($b);if($a===$b){return$referenced;}}if($keys){$referenced=$this->createSelectionInstance($table);$referenced->where($referenced->getPrimary(),array_keys($keys));}else{$referenced=array();}}return$referenced;}function
getReferencingTable($table,$column,$active=NULL){$prototype=&$this->getRefTable($refPath)->referencingPrototype[$refPath."$table.$column"];if(!$prototype){$prototype=$this->createGroupedSelectionInstance($table,$column);$prototype->where("$table.$column",array_keys((array)$this->rows));}$clone=clone$prototype;$clone->setActive($active);return$clone;}function
rewind(){$this->execute();$this->keys=array_keys($this->data);reset($this->keys);}function
current(){if(($key=current($this->keys))!==FALSE){return$this->data[$key];}else{return
FALSE;}}function
key(){return
current($this->keys);}function
next(){next($this->keys);}function
valid(){return
current($this->keys)!==FALSE;}function
offsetSet($key,$value){$this->execute();$this->rows[$key]=$value;}function
offsetGet($key){$this->execute();return$this->rows[$key];}function
offsetExists($key){$this->execute();return
isset($this->rows[$key]);}function
offsetUnset($key){$this->execute();unset($this->rows[$key],$this->data[$key]);}}class
NGroupedTableSelection
extends
NTableSelection{protected$refTable;protected$column;protected$active;function
__construct(NTableSelection$refTable,$table,$column){parent::__construct($table,$refTable->connection);$this->refTable=$refTable;$this->column=$column;}function
setActive($active){$this->active=$active;return$this;}function
through($column){trigger_error(__METHOD__.'() is deprecated; use '.__CLASS__.'::related("'.$this->name.'", "'.$column.'") instead.',E_USER_WARNING);$this->column=$column;$this->delimitedColumn=$this->refTable->connection->getSupplementalDriver()->delimite($this->column);return$this;}function
select($columns){if(!$this->sqlBuilder->getSelect()){$this->sqlBuilder->addSelect("$this->name.$this->column");}return
parent::select($columns);}function
order($columns){if(!$this->sqlBuilder->getOrder()){$this->sqlBuilder->addOrder("$this->name.$this->column".(preg_match('~\bDESC\z~i',$columns)?' DESC':''));}return
parent::order($columns);}function
aggregation($function){$aggregation=&$this->getRefTable($refPath)->aggregation[$refPath.$function.$this->getSql().json_encode($this->sqlBuilder->getParameters())];if($aggregation===NULL){$aggregation=array();$selection=$this->createSelectionInstance();$selection->getSqlBuilder()->importConditions($this->getSqlBuilder());$selection->select($function);$selection->select("$this->name.$this->column");$selection->group("$this->name.$this->column");foreach($selection
as$row){$aggregation[$row[$this->column]]=$row;}}if(isset($aggregation[$this->active])){foreach($aggregation[$this->active]as$val){return$val;}}}function
count($column=NULL){$return=parent::count($column);return
isset($return)?$return:0;}protected
function
execute(){if($this->rows!==NULL){$this->observeCache=$this;return;}$hash=md5($this->getSql().json_encode($this->sqlBuilder->getParameters()));$accessedColumns=$this->accessedColumns;$referencingBase=&$this->getRefTable($refPath)->referencing[$this->getCacheKey()];$referencing=&$referencingBase[$refPath.$hash];$this->rows=&$referencing['rows'];$this->referenced=&$referencing['refs'];$this->accessedColumns=&$referencing['accessed'];$this->observeCache=&$referencingBase['observeCache'];$refData=&$referencing['data'];if($refData===NULL){$this->accessedColumns=$accessedColumns;$limit=$this->sqlBuilder->getLimit();$rows=count($this->refTable->rows);if($limit&&$rows>1){$this->sqlBuilder->setLimit(NULL,NULL);}parent::execute();$this->sqlBuilder->setLimit($limit,NULL);$refData=array();$offset=array();$this->accessColumn($this->column);foreach((array)$this->rows
as$key=>$row){$ref=&$refData[$row[$this->column]];$skip=&$offset[$row[$this->column]];if($limit===NULL||$rows<=1||(count($ref)<$limit&&$skip>=$this->sqlBuilder->getOffset())){$ref[$key]=$row;}else{unset($this->rows[$key]);}$skip++;unset($ref,$skip);}}$this->observeCache=$this;$this->data=&$refData[$this->active];if($this->data===NULL){$this->data=array();}else{foreach($this->data
as$row){$row->setTable($this);}reset($this->data);$this->checkReferenced=TRUE;}}protected
function
getRefTable(&$refPath){$refObj=$this->refTable;$refPath=$this->name.'.';while($refObj
instanceof
NGroupedTableSelection){$refPath.=$refObj->name.'.';$refObj=$refObj->refTable;}return$refObj;}function
insert($data){if($data
instanceof
Traversable&&!$data
instanceof
NTableSelection){$data=iterator_to_array($data);}if(NValidators::isList($data)){foreach(array_keys($data)as$key){$data[$key][$this->column]=$this->active;}}else{$data[$this->column]=$this->active;}return
parent::insert($data);}function
update($data){$builder=$this->sqlBuilder;$this->sqlBuilder=clone$this->sqlBuilder;$this->where($this->column,$this->active);$return=parent::update($data);$this->sqlBuilder=$builder;return$return;}function
delete(){$builder=$this->sqlBuilder;$this->sqlBuilder=clone$this->sqlBuilder;$this->where($this->column,$this->active);$return=parent::delete();$this->sqlBuilder=$builder;return$return;}}class
NSqlBuilder
extends
NObject{private$driver;private$driverName;protected$tableName;protected$databaseReflection;protected$delimitedTable;protected$select=array();protected$where=array();protected$conditions=array();protected$parameters=array();protected$order=array();protected$limit=NULL;protected$offset=NULL;protected$group='';protected$having='';function
__construct($tableName,NConnection$connection,IReflection$reflection){$this->tableName=$tableName;$this->databaseReflection=$reflection;$this->driver=$connection->getSupplementalDriver();$this->driverName=$connection->getAttribute(PDO::ATTR_DRIVER_NAME);$this->delimitedTable=$this->tryDelimite($tableName);}function
buildInsertQuery(){return"INSERT INTO {$this->delimitedTable}";}function
buildUpdateQuery(){return"UPDATE{$this->buildTopClause()} {$this->delimitedTable} SET ?".$this->buildConditions();}function
buildDeleteQuery(){return"DELETE{$this->buildTopClause()} FROM {$this->delimitedTable}".$this->buildConditions();}function
importConditions(NSqlBuilder$builder){$this->where=$builder->where;$this->parameters=$builder->parameters;$this->conditions=$builder->conditions;}function
addSelect($columns){if(is_array($columns)){throw
new
InvalidArgumentException('Select column must be a string.');}$this->select[]=$columns;}function
getSelect(){return$this->select;}function
addWhere($condition,$parameters=array()){$args=func_get_args();$hash=md5(json_encode($args));if(isset($this->conditions[$hash])){return
FALSE;}$this->conditions[$hash]=$condition;$condition=$this->removeExtraTables($condition);$condition=$this->tryDelimite($condition);$placeholderCount=substr_count($condition,'?');if($placeholderCount>1&&count($args)===2&&is_array($parameters)){$args=$parameters;}else{array_shift($args);}$condition=trim($condition);if($placeholderCount===0&&count($args)===1){$condition.=' ?';}elseif($placeholderCount!==count($args)){throw
new
InvalidArgumentException('Argument count does not match placeholder count.');}$replace=NULL;$placeholderNum=0;foreach($args
as$arg){preg_match('#(?:.*?\?.*?){'.$placeholderNum.'}(((?:&|\||^|~|\+|-|\*|/|%|\(|,|<|>|=|(?<=\W|^)(?:REGEXP|ALL|AND|ANY|BETWEEN|EXISTS|IN|[IR]?LIKE|OR|NOT|SOME|INTERVAL))\s*)?(?:\(\?\)|\?))#s',$condition,$match,PREG_OFFSET_CAPTURE);$hasOperator=($match[1][0]==='?'&&$match[1][1]===0)?TRUE:!empty($match[2][0]);if($arg===NULL){if($hasOperator){throw
new
InvalidArgumentException('Column operator does not accept NULL argument.');}$replace='IS NULL';}elseif($arg
instanceof
NTableSelection){$clone=clone$arg;if(!$clone->getSqlBuilder()->select){try{$clone->select($clone->getPrimary());}catch(LogicException$e){throw
new
InvalidArgumentException('Selection argument must have defined a select column.',0,$e);}}if($this->driverName!=='mysql'){$replace='IN ('.$clone->getSql().')';$this->parameters=array_merge($this->parameters,$clone->getSqlBuilder()->getParameters());}else{$parameter=array();foreach($clone
as$row){$parameter[]=array_values(iterator_to_array($row));}if(!$parameter){$replace='IN (NULL)';}else{$replace='IN (?)';$this->parameters[]=$parameter;}}}elseif($arg
instanceof
NSqlLiteral){$this->parameters[]=$arg;}elseif(is_array($arg)){if($hasOperator){if(trim($match[2][0])!=='IN'){throw
new
InvalidArgumentException('Column operator does not accept array argument.');}}else{$match[2][0]='IN ';}if(!$arg){$replace=$match[2][0].'(NULL)';}else{$replace=$match[2][0].'(?)';$this->parameters[]=array_values($arg);}}else{if(!$hasOperator){$replace='= ?';}$this->parameters[]=$arg;}if($replace){$condition=substr_replace($condition,$replace,$match[1][1],strlen($match[1][0]));$replace=NULL;}if($arg!==NULL){$placeholderNum++;}}$this->where[]=$condition;return
TRUE;}function
getConditions(){return
array_values($this->conditions);}function
addOrder($columns){$this->order[]=$columns;}function
getOrder(){return$this->order;}function
setLimit($limit,$offset){$this->limit=$limit;$this->offset=$offset;}function
getLimit(){return$this->limit;}function
getOffset(){return$this->offset;}function
setGroup($columns,$having){$this->group=$columns;$this->having=$having;}function
getGroup(){return$this->group;}function
getHaving(){return$this->having;}function
buildSelectQuery($columns=NULL){$join=$this->buildJoins(implode(',',$this->conditions),TRUE);$join+=$this->buildJoins(implode(',',$this->select).",{$this->group},{$this->having},".implode(',',$this->order));$prefix=$join?"{$this->delimitedTable}.":'';if($this->select){$cols=$this->tryDelimite($this->removeExtraTables(implode(', ',$this->select)));}elseif($columns){$cols=array_map(array($this->driver,'delimite'),$columns);$cols=$prefix.implode(', '.$prefix,$cols);}elseif($this->group&&!$this->driver->isSupported(ISupplementalDriver::SUPPORT_SELECT_UNGROUPED_COLUMNS)){$cols=$this->tryDelimite($this->removeExtraTables($this->group));}else{$cols=$prefix.'*';}return"SELECT{$this->buildTopClause()} {$cols} FROM {$this->delimitedTable}".implode($join).$this->buildConditions();}function
getParameters(){return$this->parameters;}protected
function
buildJoins($val,$inner=FALSE){$joins=array();preg_match_all('~\\b([a-z][\\w.:]*[.:])([a-z]\\w*|\*)(\\s+IS\\b|\\s*<=>)?~i',$val,$matches);foreach($matches[1]as$names){$parent=$parentAlias=$this->tableName;if($names!=="$parent."){preg_match_all('~\\b([a-z][\\w]*|\*)([.:])~i',$names,$matches,PREG_SET_ORDER);foreach($matches
as$match){list(,$name,$delimiter)=$match;if($delimiter===':'){list($table,$primary)=$this->databaseReflection->getHasManyReference($parent,$name);$column=$this->databaseReflection->getPrimary($parent);}else{list($table,$column)=$this->databaseReflection->getBelongsToReference($parent,$name);$primary=$this->databaseReflection->getPrimary($table);}$joins[$name]=' '.(!isset($joins[$name])&&$inner&&!isset($match[3])?'INNER':'LEFT').' JOIN '.$this->driver->delimite($table).($table!==$name?' AS '.$this->driver->delimite($name):'').' ON '.$this->driver->delimite($parentAlias).'.'.$this->driver->delimite($column).' = '.$this->driver->delimite($name).'.'.$this->driver->delimite($primary);$parent=$table;$parentAlias=$name;}}}return$joins;}protected
function
buildConditions(){$return='';$where=$this->where;if($this->limit!==NULL&&$this->driverName==='oci'){$where[]=($this->offset?"rownum > $this->offset AND ":'').'rownum <= '.($this->limit+$this->offset);}if($where){$return.=' WHERE ('.implode(') AND (',$where).')';}if($this->group){$return.=' GROUP BY '.$this->tryDelimite($this->removeExtraTables($this->group));}if($this->having){$return.=' HAVING '.$this->tryDelimite($this->removeExtraTables($this->having));}if($this->order){$return.=' ORDER BY '.$this->tryDelimite($this->removeExtraTables(implode(', ',$this->order)));}if($this->limit!==NULL&&$this->driverName!=='oci'&&$this->driverName!=='dblib'){$return.=" LIMIT $this->limit";if($this->offset!==NULL){$return.=" OFFSET $this->offset";}}return$return;}protected
function
buildTopClause(){if($this->limit!==NULL&&$this->driverName==='dblib'){return" TOP ($this->limit)";}return'';}protected
function
tryDelimite($s){$driver=$this->driver;return
preg_replace_callback('#(?<=[^\w`"\[]|^)[a-z_][a-z0-9_]*(?=[^\w`"(\]]|\z)#i',create_function('$m','extract($GLOBALS[0]['.array_push($GLOBALS[0],array('driver'=>$driver)).'-1], EXTR_REFS);
			return strtoupper($m[0]) === $m[0] ? $m[0] : $driver->delimite($m[0]);
		'),$s);}protected
function
removeExtraTables($expression){return
preg_replace('~(?:\\b[a-z_][a-z0-9_.:]*[.:])?([a-z_][a-z0-9_]*)[.:]([a-z_*])~i','\\1.\\2',$expression);}}class
NDIContainer
extends
NFreezableObject
implements
IDIContainer{const
TAGS='tags';public$parameters=array();public$params=array();public$classes=array();private$registry=array();private$factories=array();public$meta=array();private$creating;function
__construct(array$params=array()){$this->parameters=$params+$this->parameters;$this->params=&$this->parameters;}function
getParameters(){return$this->parameters;}function
addService($name,$service,array$meta=NULL){$this->updating();if(!is_string($name)||!$name){throw
new
InvalidArgumentException("Service name must be a non-empty string, ".gettype($name)." given.");}if(isset($this->registry[$name])){throw
new
InvalidStateException("Service '$name' has already been registered.");}if(is_object($service)&&!$service
instanceof
Closure&&!$service
instanceof
NCallback){$this->registry[$name]=$service;$this->meta[$name]=$meta;return$this;}elseif(!is_string($service)||strpos($service,':')!==FALSE||$service[0]==="\0"){$service=new
NCallback($service);}$this->factories[$name]=array($service);$this->registry[$name]=&$this->factories[$name][1];$this->meta[$name]=$meta;return$this;}function
removeService($name){$this->updating();unset($this->registry[$name],$this->factories[$name],$this->meta[$name]);}function
getService($name){if(isset($this->registry[$name])){return$this->registry[$name];}elseif(isset($this->creating[$name])){throw
new
InvalidStateException("Circular reference detected for services: ".implode(', ',array_keys($this->creating)).".");}if(isset($this->factories[$name])){list($factory)=$this->factories[$name];if(is_string($factory)){if(!class_exists($factory)){throw
new
InvalidStateException("Cannot instantiate service, class '$factory' not found.");}try{$this->creating[$name]=TRUE;$service=new$factory;}catch(Exception$e){}}elseif(!$factory->isCallable()){throw
new
InvalidStateException("Unable to create service '$name', factory '$factory' is not callable.");}else{$this->creating[$name]=TRUE;try{$service=$factory->invoke($this);}catch(Exception$e){}}}elseif(method_exists($this,$factory=NDIContainer::getMethodName($name))&&$this->getReflection()->getMethod($factory)->getName()===$factory){$this->creating[$name]=TRUE;try{$service=$this->$factory();}catch(Exception$e){}}else{throw
new
NMissingServiceException("Service '$name' not found.");}unset($this->creating[$name]);if(isset($e)){throw$e;}elseif(!is_object($service)){throw
new
UnexpectedValueException("Unable to create service '$name', value returned by factory '$factory' is not object.");}return$this->registry[$name]=$service;}function
hasService($name){return
isset($this->registry[$name])||isset($this->factories[$name])||method_exists($this,$method=NDIContainer::getMethodName($name))&&$this->getReflection()->getMethod($method)->getName()===$method;}function
isCreated($name){if(!$this->hasService($name)){throw
new
NMissingServiceException("Service '$name' not found.");}return
isset($this->registry[$name]);}function
getByType($class,$need=TRUE){$lower=ltrim(strtolower($class),'\\');if(!isset($this->classes[$lower])){if($need){throw
new
NMissingServiceException("Service of type $class not found.");}}elseif($this->classes[$lower]===FALSE){throw
new
NMissingServiceException("Multiple services of type $class found.");}else{return$this->getService($this->classes[$lower]);}}function
findByTag($tag){$found=array();foreach($this->meta
as$name=>$meta){if(isset($meta[self::TAGS][$tag])){$found[$name]=$meta[self::TAGS][$tag];}}return$found;}function
createInstance($class,array$args=array()){$rc=NClassReflection::from($class);if(!$rc->isInstantiable()){throw
new
NServiceCreationException("Class $class is not instantiable.");}elseif($constructor=$rc->getConstructor()){return$rc->newInstanceArgs(NDIHelpers::autowireArguments($constructor,$args,$this));}elseif($args){throw
new
NServiceCreationException("Unable to pass arguments, class $class has no constructor.");}return
new$class;}function
callMethod($function,array$args=array()){$callback=new
NCallback($function);return$callback->invokeArgs(NDIHelpers::autowireArguments($callback->toReflection(),$args,$this));}function
expand($s){return
NDIHelpers::expand($s,$this->parameters);}function&__get($name){if(!isset($this->registry[$name])){$this->getService($name);}return$this->registry[$name];}function
__set($name,$service){$this->updating();if(!is_string($name)||$name===''){throw
new
InvalidArgumentException("Service name must be a non-empty string, ".gettype($name)." given.");}elseif(isset($this->registry[$name])){throw
new
InvalidStateException("Service '$name' has already been registered.");}elseif(!is_object($service)){throw
new
InvalidArgumentException("Service must be a object, ".gettype($service)." given.");}$this->registry[$name]=$service;}function
__isset($name){return$this->hasService($name);}function
__unset($name){$this->removeService($name);}static
function
getMethodName($name,$isService=TRUE){$uname=ucfirst($name);return($isService?'createService':'create').((string)$name===$uname?'__':'').str_replace('.','__',$uname);}}class
NDIContainerBuilder
extends
NObject{const
CREATED_SERVICE='self',THIS_CONTAINER='container';public$parameters=array();private$definitions=array();private$classes;private$dependencies=array();function
addDefinition($name){if(!is_string($name)||!$name){throw
new
InvalidArgumentException("Service name must be a non-empty string, ".gettype($name)." given.");}elseif(isset($this->definitions[$name])){throw
new
InvalidStateException("Service '$name' has already been added.");}return$this->definitions[$name]=new
NDIServiceDefinition;}function
removeDefinition($name){unset($this->definitions[$name]);}function
getDefinition($name){if(!isset($this->definitions[$name])){throw
new
NMissingServiceException("Service '$name' not found.");}return$this->definitions[$name];}function
getDefinitions(){return$this->definitions;}function
hasDefinition($name){return
isset($this->definitions[$name]);}function
getByType($class){$lower=ltrim(strtolower($class),'\\');if(!isset($this->classes[$lower])){return;}elseif(count($this->classes[$lower])===1){return$this->classes[$lower][0];}else{throw
new
NServiceCreationException("Multiple services of type $class found: ".implode(', ',$this->classes[$lower]));}}function
findByTag($tag){$found=array();foreach($this->definitions
as$name=>$def){if(isset($def->tags[$tag])&&$def->shared){$found[$name]=$def->tags[$tag];}}return$found;}function
autowireArguments($class,$method,array$arguments){$rc=NClassReflection::from($class);if(!$rc->hasMethod($method)){if(!NValidators::isList($arguments)){throw
new
NServiceCreationException("Unable to pass specified arguments to $class::$method().");}return$arguments;}$rm=$rc->getMethod($method);if($rm->isAbstract()||!$rm->isPublic()){throw
new
NServiceCreationException("$rm is not callable.");}$this->addDependency($rm->getFileName());return
NDIHelpers::autowireArguments($rm,$arguments,$this);}function
prepareClassList(){foreach($this->definitions
as$name=>$def){if($def->class===self::CREATED_SERVICE||($def->factory&&$def->factory->entity===self::CREATED_SERVICE)){$def->class=$name;$def->internal=TRUE;if($def->factory&&$def->factory->entity===self::CREATED_SERVICE){$def->factory->entity=$def->class;}unset($this->definitions[$name]);$this->definitions['_anonymous_'.str_replace('\\','_',strtolower(trim($name,'\\')))]=$def;}if($def->class){$def->class=$this->expand($def->class);if(!$def->factory){$def->factory=new
NDIStatement($def->class);}}elseif(!$def->factory){throw
new
NServiceCreationException("Class and factory are missing in service '$name' definition.");}}foreach($this->definitions
as$name=>$def){$factory=$this->normalizeEntity($this->expand($def->factory->entity));if(is_string($factory)&&preg_match('#^[\w\\\\]+\z#',$factory)&&$factory!==self::CREATED_SERVICE){if(!class_exists($factory)||!NClassReflection::from($factory)->isInstantiable()){throw
new
InvalidStateException("Class $factory used in service '$name' has not been found or is not instantiable.");}}}$this->classes=FALSE;foreach($this->definitions
as$name=>$def){$this->resolveClass($name);}$this->classes=array();foreach($this->definitions
as$name=>$def){if(!$def->class){continue;}if(!class_exists($def->class)&&!interface_exists($def->class)){throw
new
InvalidStateException("Class $def->class has not been found.");}$def->class=NClassReflection::from($def->class)->getName();if($def->autowired){foreach(class_parents($def->class)+class_implements($def->class)+array($def->class)as$parent){$this->classes[strtolower($parent)][]=(string)$name;}}}foreach($this->classes
as$class=>$foo){$this->addDependency(NClassReflection::from($class)->getFileName());}}private
function
resolveClass($name,$recursive=array()){if(isset($recursive[$name])){throw
new
InvalidArgumentException('Circular reference detected for services: '.implode(', ',array_keys($recursive)).'.');}$recursive[$name]=TRUE;$def=$this->definitions[$name];$factory=$this->normalizeEntity($this->expand($def->factory->entity));if($def->class){return$def->class;}elseif(is_array($factory)){if($service=$this->getServiceName($factory[0])){if(NStrings::contains($service,'\\')){throw
new
NServiceCreationException("Unable resolve class name for service '$name'.");}$factory[0]=$this->resolveClass($service,$recursive);if(!$factory[0]){return;}}$factory=new
NCallback($factory);if(!$factory->isCallable()){throw
new
InvalidStateException("Factory '$factory' is not callable.");}try{$reflection=$factory->toReflection();}catch(ReflectionException$e){throw
new
InvalidStateException("Missing factory '$factory'.");}$def->class=preg_replace('#[|\s].*#','',$reflection->getAnnotation('return'));if($def->class&&!class_exists($def->class)&&$def->class[0]!=='\\'&&$reflection
instanceof
ReflectionMethod){}}elseif($service=$this->getServiceName($factory)){if(NStrings::contains($service,'\\')){$service=ltrim($service,'\\');$def->autowired=FALSE;return$def->class=$service;}if($this->definitions[$service]->shared){$def->autowired=FALSE;}return$def->class=$this->resolveClass($service,$recursive);}else{return$def->class=$factory;}}function
addDependency($file){$this->dependencies[$file]=TRUE;return$this;}function
getDependencies(){unset($this->dependencies[FALSE]);return
array_keys($this->dependencies);}function
generateClass($parentClass='NDIContainer'){unset($this->definitions[self::THIS_CONTAINER]);$this->addDefinition(self::THIS_CONTAINER)->setClass($parentClass);$this->prepareClassList();$class=new
NPhpClassType('Container');$class->addExtend($parentClass);$class->addMethod('__construct')->addBody('parent::__construct(?);',array($this->expand($this->parameters)));$classes=$class->addProperty('classes',array());foreach($this->classes
as$name=>$foo){try{$classes->value[$name]=$this->getByType($name);}catch(NServiceCreationException$e){$classes->value[$name]=new
NPhpLiteral('FALSE, //'.strstr($e->getMessage(),':'));}}$definitions=$this->definitions;ksort($definitions);$meta=$class->addProperty('meta',array());foreach($definitions
as$name=>$def){if($def->shared){foreach($this->expand($def->tags)as$tag=>$value){$meta->value[$name][NDIContainer::TAGS][$tag]=$value;}}}foreach($definitions
as$name=>$def){try{$name=(string)$name;$type=($tmp=$def->class)?$tmp:'object';$methodName=NDIContainer::getMethodName($name,$def->shared);if(!NPhpHelpers::isIdentifier($methodName)){throw
new
NServiceCreationException('Name contains invalid characters.');}if($def->shared&&!$def->internal&&NPhpHelpers::isIdentifier($name)){$class->addDocument("@property $type \$$name");}$method=$class->addMethod($methodName)->addDocument("@return $type")->setVisibility($def->shared||$def->internal?'protected':'public')->setBody($name===self::THIS_CONTAINER?'return $this;':$this->generateService($name));foreach($this->expand($def->parameters)as$k=>$v){$tmp=explode(' ',is_int($k)?$v:$k);$param=is_int($k)?$method->addParameter(end($tmp)):$method->addParameter(end($tmp),$v);if(isset($tmp[1])){$param->setTypeHint($tmp[0]);}}}catch(Exception$e){throw
new
NServiceCreationException("Service '$name': ".$e->getMessage(),NULL,$e);}}return$class;}private
function
generateService($name){$def=$this->definitions[$name];$parameters=$this->parameters;foreach($this->expand($def->parameters)as$k=>$v){$v=explode(' ',is_int($k)?$v:$k);$parameters[end($v)]=new
NPhpLiteral('$'.end($v));}$code='$service = '.$this->formatStatement(NDIHelpers::expand($def->factory,$parameters,TRUE)).";\n";$entity=$this->normalizeEntity($def->factory->entity);if($def->class&&$def->class!==$entity&&!$this->getServiceName($entity)){$code.=NPhpHelpers::formatArgs("if (!\$service instanceof $def->class) {\n"."\tthrow new UnexpectedValueException(?);\n}\n",array("Unable to create service '$name', value returned by factory is not $def->class type."));}foreach((array)$def->setup
as$setup){$setup=NDIHelpers::expand($setup,$parameters,TRUE);if(is_string($setup->entity)&&strpbrk($setup->entity,':@?')===FALSE){$setup->entity=array("@$name",$setup->entity);}$code.=$this->formatStatement($setup,$name).";\n";}return$code.='return $service;';}function
formatStatement(NDIStatement$statement,$self=NULL){$entity=$this->normalizeEntity($statement->entity);$arguments=$statement->arguments;if(is_string($entity)&&NStrings::contains($entity,'?')){return$this->formatPhp($entity,$arguments,$self);}elseif($service=$this->getServiceName($entity)){if($this->definitions[$service]->shared){if($arguments){throw
new
NServiceCreationException("Unable to call service '$entity'.");}return$this->formatPhp('$this->getService(?)',array($service));}$params=array();foreach($this->definitions[$service]->parameters
as$k=>$v){$params[]=preg_replace('#\w+\z#','\$$0',(is_int($k)?$v:$k)).(is_int($k)?'':' = '.NPhpHelpers::dump($v));}$rm=new
NFunctionReflection(create_function(implode(', ',$params),''));$arguments=NDIHelpers::autowireArguments($rm,$arguments,$this);return$this->formatPhp('$this->?(?*)',array(NDIContainer::getMethodName($service,FALSE),$arguments),$self);}elseif($entity==='not'){return$this->formatPhp('!?',array($arguments[0]));}elseif(is_string($entity)){if($constructor=NClassReflection::from($entity)->getConstructor()){$this->addDependency($constructor->getFileName());$arguments=NDIHelpers::autowireArguments($constructor,$arguments,$this);}elseif($arguments){throw
new
NServiceCreationException("Unable to pass arguments, class $entity has no constructor.");}return$this->formatPhp("new $entity".($arguments?'(?*)':''),array($arguments),$self);}elseif(!NValidators::isList($entity)||count($entity)!==2){throw
new
InvalidStateException("Expected class, method or property, ".NPhpHelpers::dump($entity)." given.");}elseif($entity[0]===''){return$this->formatPhp("$entity[1](?*)",array($arguments),$self);}elseif(NStrings::contains($entity[1],'$')){NValidators::assert($arguments,'list:1',"setup arguments for '".NCallback::create($entity)."'");if($this->getServiceName($entity[0],$self)){return$this->formatPhp('?->? = ?',array($entity[0],substr($entity[1],1),$arguments[0]),$self);}else{return$this->formatPhp($entity[0].'::$? = ?',array(substr($entity[1],1),$arguments[0]),$self);}}elseif($service=$this->getServiceName($entity[0],$self)){if($this->definitions[$service]->class){$arguments=$this->autowireArguments($this->definitions[$service]->class,$entity[1],$arguments);}return$this->formatPhp('?->?(?*)',array($entity[0],$entity[1],$arguments),$self);}else{$arguments=$this->autowireArguments($entity[0],$entity[1],$arguments);return$this->formatPhp("$entity[0]::$entity[1](?*)",array($arguments),$self);}}function
formatPhp($statement,$args,$self=NULL){$that=$this;array_walk_recursive($args,create_function('& $val','extract($GLOBALS[0]['.array_push($GLOBALS[0],array('self'=>$self,'that'=>$that)).'-1], EXTR_REFS);
			list($val) = $that->normalizeEntity(array($val));

			if ($val instanceof NDIStatement) {
				$val = new NPhpLiteral($that->formatStatement($val, $self));

			} elseif ($val === \'@\' . NDIContainerBuilder::THIS_CONTAINER) {
				$val = new NPhpLiteral(\'$this\');

			} elseif ($service = $that->getServiceName($val, $self)) {
				$val = $service === $self ? \'$service\' : $that->formatStatement(new NDIStatement($val));
				$val = new NPhpLiteral($val);
			}
		'));return
NPhpHelpers::formatArgs($statement,$args);}function
expand($value){return
NDIHelpers::expand($value,$this->parameters,TRUE);}function
normalizeEntity($entity){if(is_string($entity)&&NStrings::contains($entity,'::')&&!NStrings::contains($entity,'?')){$entity=explode('::',$entity);}if(is_array($entity)&&$entity[0]instanceof
NDIServiceDefinition){$tmp=array_keys($this->definitions,$entity[0],TRUE);$entity[0]="@$tmp[0]";}elseif($entity
instanceof
NDIServiceDefinition){$tmp=array_keys($this->definitions,$entity,TRUE);$entity="@$tmp[0]";}elseif(is_array($entity)&&$entity[0]===$this){$entity[0]='@'.NDIContainerBuilder::THIS_CONTAINER;}return$entity;}/**
	 * Converts @service or @Class -> service name and checks its existence.
	 * @return string  of FALSE, if argument is not service name
	 */function
getServiceName($arg,$self=NULL){if(!is_string($arg)||!preg_match('#^@[\w\\\\.].*\z#',$arg)){return
FALSE;}$service=substr($arg,1);if($service===self::CREATED_SERVICE){$service=$self;}if(NStrings::contains($service,'\\')){if($this->classes===FALSE){return$service;}$res=$this->getByType($service);if(!$res){throw
new
NServiceCreationException("Reference to missing service of type $service.");}return$res;}if(!isset($this->definitions[$service])){throw
new
NServiceCreationException("Reference to missing service '$service'.");}return$service;}}class
NContainerPanel
extends
NObject
implements
IBarPanel{private$container;function
__construct(NDIContainer$container){if(PHP_VERSION_ID<50300){throw
new
NotSupportedException(__CLASS__.' requires PHP 5.3 or newer.');}$this->container=$container;}function
getTab(){ob_start();?>
<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAGSSURBVCjPVVFNSwJhEF78Ad79Cf6PvXQRsotUlzKICosuRYmR2RJR0KE6lBFFZVEbpFBSqKu2rum6llFS9HHI4iUhT153n6ZtIWMOM+/MM88z7wwH7s9Ub16SJcnbmrNcxVm2q7Z8/QPvEOtntpj92NkCqITLepEpjix7xQtiLOoQ2b6+E7YAN/5nfOEJ2WbKqOIOJ4bYVMEQx4LfBBQDsvFMhUcCVU1/CxVXmDBGA5ZETrhDCQVcYAPbyEJBhvrnBVPiSpNr6cYDNCQwo4zzU/ySckkgDYuNuVpI42T9k4gLKGMPs/xPzzovQiY2hQYe0jlJfyNNhTqiWDYBq/wBMcSRpnyPzu1oS7WtxjVBSthU1vgVksiQ3Dn6Gp5ah2YOKQo5GiuHPA6xT1EKpxQNCNYejgIR457KKio0S56YckjSa9jo//3mrj+BV0QQagqGTOo+Y7gZIf1puP3WHoLhEb2PjTlCTCWGXtbp8DCX3hZuOdaIc9A+aQvWk4ihq95p67a7nP+u+Ws+r0dql9z/zv0NCYhdCPKZ7oYAAAAASUVORK5CYII="
/>&nbsp;
<?php
return
ob_get_clean();}function
getPanel(){$services=$this->getContainerProperty('factories');$factories=array();foreach(NClassReflection::from($this->container)->getMethods()as$method){if(preg_match('#^create(Service)?_*(.+)\z#',$method->getName(),$m)){$name=str_replace('__','.',strtolower(substr($m[2],0,1)).substr($m[2],1));if($m[1]){$services[$name]=$method->getAnnotation('return');}elseif($method->isPublic()){$a=strrpos(".$name",'.');$factories[substr($name,0,$a).'create'.ucfirst(substr($name,$a))]=$method->getAnnotation('return');}}}ksort($services);ksort($factories);$container=$this->container;$registry=$this->getContainerProperty('registry');ob_start();?>
<style class="nette-debug">#nette-debug .nette-ContainerPanel .nette-inner{width:700px}#nette-debug .nette-ContainerPanel table{width:100%;white-space:nowrap}#nette-debug .nette-ContainerPanel-parameters pre{background:#FDF5CE;padding:.4em .7em;border:1px dotted silver;overflow:auto}#nette-debug .nette-ContainerPanel .created{font-weight:bold}#nette-debug .nette-ContainerPanel .yes{color:green;font-weight:bold}</style>

<div class="nette-ContainerPanel">
<h1><?php echo
get_class($this->container)?></h1>

<div class="nette-inner">
	<h2>Parameters</h2>

	<div class="nette-ContainerPanel-parameters">
		<?php echo
NDebugHelpers::clickableDump($this->container->parameters);?>
	</div>

	<h2>Services</h2>

	<table>
		<thead>
		<tr>
			<th>Name</th>
			<th>Autowired</th>
			<th>Service</th>
			<th>Meta</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach($services
as$name=>$class):?>
		<?php $autowired=array_keys($container->classes,$name);?>
		<tr>
			<td class="<?php echo
isset($registry[$name])?'created':''?>"><?php echo
htmlSpecialChars(str_replace('.','->',$name))?></td>
			<td title="<?php echo
htmlSpecialChars(implode(" \n",$autowired))?>" class="<?php echo$autowired?'yes':''?>"><?php echo$autowired?'yes':'no'?></td>
			<td>
				<?php if(isset($registry[$name])&&!$registry[$name]instanceof
NDIContainer&&!$registry[$name]instanceof
NDINestedAccessor):?>
					<?php echo
NDebugHelpers::clickableDump($registry[$name],TRUE);?>
				<?php elseif(isset($registry[$name])):?>
					<code><?php echo
get_class($registry[$name])?></code>
				<?php elseif(is_string($class)):?>
					<code><?php echo
htmlSpecialChars($class)?></code>
				<?php endif?>
			</td>
			<td><?php if(isset($container->meta[$name])){echo
NDebugHelpers::clickableDump($container->meta[$name],TRUE);}?></td>
		</tr>
		<?php endforeach?>
		</tbody>
	</table>

	<h2>Factories</h2>

	<table>
		<thead>
		<tr>
			<th>Method</th>
			<th>Returns</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach($factories
as$name=>$class):?>
		<tr>
			<td><?php echo
htmlSpecialChars(str_replace('.','->',$name))?>()</td>
			<td><code><?php echo
htmlSpecialChars($class)?><code></td>
		</tr>
		<?php endforeach?>
		</tbody>
	</table>
</div>
</div>
<?php
return
ob_get_clean();}private
function
getContainerProperty($name){$prop=NClassReflection::from('NDIContainer')->getProperty($name);$prop->setAccessible(TRUE);return$prop->getValue($this->container);}}class
NMissingServiceException
extends
InvalidStateException{}class
NServiceCreationException
extends
InvalidStateException{}class
NDIHelpers{static
function
expand($var,array$params,$recursive=FALSE){if(is_array($var)){$res=array();foreach($var
as$key=>$val){$res[$key]=self::expand($val,$params,$recursive);}return$res;}elseif($var
instanceof
NDIStatement){return
new
NDIStatement(self::expand($var->entity,$params,$recursive),self::expand($var->arguments,$params,$recursive));}elseif(!is_string($var)){return$var;}$parts=preg_split('#%([\w.-]*)%#i',$var,-1,PREG_SPLIT_DELIM_CAPTURE);$res='';foreach($parts
as$n=>$part){if($n
%
2===0){$res.=$part;}elseif($part===''){$res.='%';}elseif(isset($recursive[$part])){throw
new
InvalidArgumentException(sprintf('Circular reference detected for variables: %s.',implode(', ',array_keys($recursive))));}else{$val=NArrays::get($params,explode('.',$part));if($recursive){$val=self::expand($val,$params,(is_array($recursive)?$recursive:array())+array($part=>1));}if(strlen($part)+2===strlen($var)){return$val;}if(!is_scalar($val)){throw
new
InvalidArgumentException("Unable to concatenate non-scalar parameter '$part' into '$var'.");}$res.=$val;}}return$res;}static
function
escape($value){if(is_array($value)){array_walk_recursive($value,create_function('&$val','
				$val = is_string($val) ? str_replace(\'%\', \'%%\', $val) : $val;
			'));}elseif(is_string($value)){$value=str_replace('%','%%',$value);}return$value;}static
function
autowireArguments(ReflectionFunctionAbstract$method,array$arguments,$container){$optCount=0;$num=-1;$res=array();foreach($method->getParameters()as$num=>$parameter){if(array_key_exists($num,$arguments)){$res[$num]=$arguments[$num];unset($arguments[$num]);$optCount=0;}elseif(array_key_exists($parameter->getName(),$arguments)){$res[$num]=$arguments[$parameter->getName()];unset($arguments[$parameter->getName()]);$optCount=0;}elseif($class=$parameter->getClassName()){$res[$num]=$container->getByType($class,FALSE);if($res[$num]===NULL){if($parameter->allowsNull()){$optCount++;}else{throw
new
NServiceCreationException("No service of type {$class} found. Make sure the type hint in $method is written correctly and service of this type is registered.");}}else{if($container
instanceof
NDIContainerBuilder){$res[$num]='@'.$res[$num];}$optCount=0;}}elseif($parameter->isOptional()){$res[$num]=$parameter->isDefaultValueAvailable()?$parameter->getDefaultValue():NULL;$optCount++;}else{throw
new
NServiceCreationException("$parameter has no type hint, so its value must be specified.");}}while(array_key_exists(++$num,$arguments)){$res[$num]=$arguments[$num];unset($arguments[$num]);$optCount=0;}if($arguments){throw
new
NServiceCreationException("Unable to pass specified arguments to $method.");}return$optCount?array_slice($res,0,-$optCount):$res;}}class
NDINestedAccessor
extends
NObject{public$parameters;private$container;private$namespace;function
__construct(NDIContainer$container,$namespace){$this->container=$container;$this->namespace=$namespace.'.';$this->parameters=&$container->parameters[$namespace];}function
__call($name,$args){if(substr($name,0,6)==='create'){return
call_user_func_array(array($this->container,NDIContainer::getMethodName($this->namespace.substr($name,6),FALSE)),$args);}throw
new
NotSupportedException;}function&__get($name){$service=$this->container->getService($this->namespace.$name);return$service;}function
__set($name,$service){throw
new
NotSupportedException;}function
__isset($name){return$this->container->hasService($this->namespace.$name);}function
__unset($name){throw
new
NotSupportedException;}}class
NDIServiceDefinition
extends
NObject{public$class;public$factory;public$setup=array();public$parameters=array();public$tags=array();public$autowired=TRUE;public$shared=TRUE;public$internal=FALSE;function
setClass($class,array$args=array()){$this->class=$class;if($args){$this->setFactory($class,$args);}return$this;}function
setFactory($factory,array$args=array()){$this->factory=new
NDIStatement($factory,$args);return$this;}function
setArguments(array$args=array()){if($this->factory){$this->factory->arguments=$args;}else{$this->setClass($this->class,$args);}return$this;}function
addSetup($target,$args=NULL){$_args=func_get_args();$this->setup[]=new
NDIStatement($target,is_array($args)?$args:array_slice($_args,1));return$this;}function
setParameters(array$params){$this->shared=$this->autowired=FALSE;$this->parameters=$params;return$this;}function
addTag($tag,$attrs=TRUE){$this->tags[$tag]=$attrs;return$this;}function
setAutowired($on){$this->autowired=$on;return$this;}function
setShared($on){$this->shared=(bool)$on;$this->autowired=$this->shared?$this->autowired:FALSE;return$this;}function
setInternal($on){$this->internal=(bool)$on;return$this;}}class
NDIStatement
extends
NObject{public$entity;public$arguments;function
__construct($entity,array$arguments=array()){$this->entity=$entity;$this->arguments=$arguments;}}class
NFormGroup
extends
NObject{protected$controls;private$options=array();function
__construct(){$this->controls=new
SplObjectStorage;}function
add(){foreach(func_get_args()as$num=>$item){if($item
instanceof
IFormControl){$this->controls->attach($item);}elseif($item
instanceof
Traversable||is_array($item)){foreach($item
as$control){$this->controls->attach($control);}}else{throw
new
InvalidArgumentException("Only IFormControl items are allowed, the #$num parameter is invalid.");}}return$this;}function
getControls(){return
iterator_to_array($this->controls);}function
setOption($key,$value){if($value===NULL){unset($this->options[$key]);}else{$this->options[$key]=$value;}return$this;}function
getOption($key,$default=NULL){return
isset($this->options[$key])?$this->options[$key]:$default;}function
getOptions(){return$this->options;}}abstract
class
NFormControl
extends
NComponent
implements
IFormControl{public
static$idMask='frm%s-%s';public$caption;protected$value;protected$control;protected$label;private$errors=array();private$disabled=FALSE;private$htmlId;private$htmlName;private$rules;private$translator=TRUE;private$options=array();function
__construct($caption=NULL){$this->monitor('NForm');parent::__construct();$this->control=NHtml::el('input');$this->label=NHtml::el('label');$this->caption=$caption;$this->rules=new
NRules($this);}protected
function
attached($form){if(!$this->disabled&&$form
instanceof
NForm&&$form->isAnchored()&&$form->isSubmitted()){$this->htmlName=NULL;$this->loadHttpData();}}function
getForm($need=TRUE){return$this->lookup('NForm',$need);}function
loadHttpData(){$path=explode('[',strtr(str_replace(array('[]',']'),'',$this->getHtmlName()),'.','_'));$this->setValue(NArrays::get($this->getForm()->getHttpData(),$path,NULL));}function
getHtmlName(){if($this->htmlName===NULL){$name=str_replace(self::NAME_SEPARATOR,'][',$this->lookupPath('NForm'),$count);if($count){$name=substr_replace($name,'',strpos($name,']'),1).']';}if(is_numeric($name)||in_array($name,array('attributes','children','elements','focus','length','reset','style','submit','onsubmit'))){$name.='_';}$this->htmlName=$name;}return$this->htmlName;}function
setValue($value){$this->value=$value;return$this;}function
getValue(){return$this->value;}function
isFilled(){return(string)$this->getValue()!=='';}function
setDefaultValue($value){$form=$this->getForm(FALSE);if(!$form||!$form->isAnchored()||!$form->isSubmitted()){$this->setValue($value);}return$this;}function
setDisabled($value=TRUE){$this->disabled=(bool)$value;return$this;}function
isDisabled(){return$this->disabled;}function
getControl(){$this->setOption('rendered',TRUE);$control=clone$this->control;$control->name=$this->getHtmlName();$control->disabled=$this->disabled;$control->id=$this->getHtmlId();$control->required=$this->isRequired();$rules=self::exportRules($this->rules);$rules=substr(PHP_VERSION_ID>=50400?json_encode($rules,JSON_UNESCAPED_UNICODE):json_encode($rules),1,-1);$rules=preg_replace('#"([a-z0-9_]+)":#i','$1:',$rules);$rules=preg_replace('#(?<!\\\\)"(?!:[^a-z])([^\\\\\',]*)"#i',"'$1'",$rules);$control->data('nette-rules',$rules?$rules:NULL);return$control;}function
getLabel($caption=NULL){$label=clone$this->label;$label->for=$this->getHtmlId();if($caption!==NULL){$label->setText($this->translate($caption));}elseif($this->caption
instanceof
NHtml){$label->add($this->caption);}else{$label->setText($this->translate($this->caption));}return$label;}function
getControlPrototype(){return$this->control;}function
getLabelPrototype(){return$this->label;}function
setHtmlId($id){$this->htmlId=$id;return$this;}function
getHtmlId(){if($this->htmlId===FALSE){return
NULL;}elseif($this->htmlId===NULL){$this->htmlId=sprintf(self::$idMask,$this->getForm()->getName(),$this->lookupPath('NForm'));}return$this->htmlId;}function
setAttribute($name,$value=TRUE){$this->control->$name=$value;return$this;}function
setTranslator(ITranslator$translator=NULL){$this->translator=$translator;return$this;}function
getTranslator(){if($this->translator===TRUE){return$this->getForm(FALSE)?$this->getForm()->getTranslator():NULL;}return$this->translator;}function
translate($s,$count=NULL){$translator=$this->getTranslator();return$translator===NULL||$s==NULL?$s:$translator->translate($s,$count);}function
addRule($operation,$message=NULL,$arg=NULL){$this->rules->addRule($operation,$message,$arg);return$this;}function
addCondition($operation,$value=NULL){return$this->rules->addCondition($operation,$value);}function
addConditionOn(IFormControl$control,$operation,$value=NULL){return$this->rules->addConditionOn($control,$operation,$value);}function
getRules(){return$this->rules;}function
setRequired($message=NULL){return$this->addRule(NForm::FILLED,$message);}function
isRequired(){foreach($this->rules
as$rule){if($rule->type===NRule::VALIDATOR&&!$rule->isNegative&&$rule->operation===NForm::FILLED){return
TRUE;}}return
FALSE;}function
addError($message){if(!in_array($message,$this->errors,TRUE)){$this->errors[]=$message;}$this->getForm()->addError($message);}function
getErrors(){return$this->errors;}function
hasErrors(){return(bool)$this->errors;}function
cleanErrors(){$this->errors=array();}protected
static
function
exportRules($rules){$payload=array();foreach($rules
as$rule){if(!is_string($op=$rule->operation)){$op=new
NCallback($op);if(!$op->isStatic()){continue;}}if($rule->type===NRule::VALIDATOR){$item=array('op'=>($rule->isNegative?'~':'').$op,'msg'=>$rules->formatMessage($rule,FALSE));}elseif($rule->type===NRule::CONDITION){$item=array('op'=>($rule->isNegative?'~':'').$op,'rules'=>self::exportRules($rule->subRules),'control'=>$rule->control->getHtmlName());if($rule->subRules->getToggles()){$item['toggle']=$rule->subRules->getToggles();}}if(is_array($rule->arg)){foreach($rule->arg
as$key=>$value){$item['arg'][$key]=$value
instanceof
IFormControl?(object)array('control'=>$value->getHtmlName()):$value;}}elseif($rule->arg!==NULL){$item['arg']=$rule->arg
instanceof
IFormControl?(object)array('control'=>$rule->arg->getHtmlName()):$rule->arg;}$payload[]=$item;}return$payload;}static
function
validateEqual(IFormControl$control,$arg){$value=$control->getValue();foreach((is_array($value)?$value:array($value))as$val){foreach((is_array($arg)?$arg:array($arg))as$item){if((string)$val===(string)($item
instanceof
IFormControl?$item->value:$item)){return
TRUE;}}}return
FALSE;}static
function
validateNotEqual(IFormControl$control,$arg){return!self::validateEqual($control,$arg);}static
function
validateFilled(IFormControl$control){return$control->isFilled();}static
function
validateBlank(IFormControl$control){return!$control->isFilled();}static
function
validateValid(IFormControl$control){return$control->rules->validate(TRUE);}function
setOption($key,$value){if($value===NULL){unset($this->options[$key]);}else{$this->options[$key]=$value;}return$this;}function
getOption($key,$default=NULL){return
isset($this->options[$key])?$this->options[$key]:$default;}function
getOptions(){return$this->options;}}class
NButton
extends
NFormControl{function
__construct($caption=NULL){parent::__construct($caption);$this->control->type='button';}function
getLabel($caption=NULL){return
NULL;}function
getControl($caption=NULL){$control=parent::getControl();$control->value=$this->translate($caption===NULL?$this->caption:$caption);return$control;}}class
NCheckbox
extends
NFormControl{function
__construct($label=NULL){parent::__construct($label);$this->control->type='checkbox';$this->value=FALSE;}function
setValue($value){$this->value=is_scalar($value)?(bool)$value:FALSE;return$this;}function
getControl(){return
parent::getControl()->checked($this->value);}}class
NHiddenField
extends
NFormControl{private$forcedValue;function
__construct($forcedValue=NULL){parent::__construct();$this->control->type='hidden';$this->value=(string)$forcedValue;$this->forcedValue=$forcedValue;}function
setValue($value){$this->value=is_scalar($value)?(string)$value:'';return$this;}function
getControl(){return
parent::getControl()->value($this->forcedValue===NULL?$this->value:$this->forcedValue)->data('nette-rules',NULL);}function
getLabel($caption=NULL){return
NULL;}}class
NSubmitButton
extends
NButton
implements
ISubmitterControl{public$onClick;public$onInvalidClick;private$validationScope=TRUE;function
__construct($caption=NULL){parent::__construct($caption);$this->control->type='submit';}function
setValue($value){if($this->value=$value!==NULL){$this->getForm()->setSubmittedBy($this);}return$this;}function
isSubmittedBy(){return$this->getForm()->isSubmitted()===$this;}function
setValidationScope($scope){$this->validationScope=(bool)$scope;$this->control->formnovalidate=!$this->validationScope;return$this;}function
getValidationScope(){return$this->validationScope;}function
click(){$this->onClick($this);}static
function
validateSubmitted(ISubmitterControl$control){return$control->isSubmittedBy();}}class
NImageButton
extends
NSubmitButton{function
__construct($src=NULL,$alt=NULL){parent::__construct();$this->control->type='image';$this->control->src=$src;$this->control->alt=$alt;}function
getHtmlName(){$name=parent::getHtmlName();return
strpos($name,'[')===FALSE?$name:$name.'[]';}function
loadHttpData(){$path=$this->getHtmlName();$path=explode('[',strtr(str_replace(']','',strpos($path,'[')===FALSE?$path.'.x':substr($path,0,-2)),'.','_'));$this->setValue(NArrays::get($this->getForm()->getHttpData(),$path,NULL));}}class
NSelectBox
extends
NFormControl{private$items=array();protected$allowed=array();private$prompt=FALSE;private$useKeys=TRUE;function
__construct($label=NULL,array$items=NULL,$size=NULL){parent::__construct($label);$this->control->setName('select');$this->control->size=$size>1?(int)$size:NULL;if($items!==NULL){$this->setItems($items);}}function
getValue(){return
is_scalar($this->value)&&isset($this->allowed[$this->value])?$this->value:NULL;}function
getRawValue(){return
is_scalar($this->value)?$this->value:NULL;}function
isFilled(){$value=$this->getValue();return
is_array($value)?count($value)>0:$value!==NULL;}function
setPrompt($prompt){if($prompt===TRUE){$prompt=reset($this->items);unset($this->allowed[key($this->items)],$this->items[key($this->items)]);}$this->prompt=$prompt;return$this;}function
skipFirst($v=NULL){trigger_error(__METHOD__.'() is deprecated; use setPrompt() instead.',E_USER_WARNING);return$this->setPrompt($v);}function
getPrompt(){return$this->prompt;}function
areKeysUsed(){return$this->useKeys;}function
setItems(array$items,$useKeys=TRUE){$allowed=array();foreach($items
as$k=>$v){foreach((is_array($v)?$v:array($k=>$v))as$key=>$value){if(!$useKeys){if(!is_scalar($value)){throw
new
InvalidArgumentException("All items must be scalar.");}$key=$value;}if(isset($allowed[$key])){throw
new
InvalidArgumentException("Items contain duplication for key '$key'.");}$allowed[$key]=$value;}}$this->items=$items;$this->allowed=$allowed;$this->useKeys=(bool)$useKeys;return$this;}function
getItems(){return$this->items;}function
getSelectedItem(){$value=$this->getValue();return($this->useKeys&&$value!==NULL)?$this->allowed[$value]:$value;}function
getControl(){$selected=$this->getValue();$selected=is_array($selected)?array_flip($selected):array($selected=>TRUE);$control=parent::getControl();$option=NHtml::el('option');if($this->prompt!==FALSE){$control->add($this->prompt
instanceof
NHtml?$this->prompt->value(''):(string)$option->value('')->setText($this->translate((string)$this->prompt)));}foreach($this->items
as$key=>$value){if(!is_array($value)){$value=array($key=>$value);$dest=$control;}else{$dest=$control->create('optgroup')->label($this->translate($key));}foreach($value
as$key2=>$value2){if($value2
instanceof
NHtml){$dest->add((string)$value2->value($key2)->selected(isset($selected[$key2])));}else{$key2=$this->useKeys?$key2:$value2;$dest->add((string)$option->value($key2)->selected(isset($selected[$key2]))->setText($this->translate((string)$value2)));}}}return$control;}}class
NMultiSelectBox
extends
NSelectBox{function
getValue(){return
array_intersect($this->getRawValue(),array_keys($this->allowed));}function
getRawValue(){if(is_scalar($this->value)){return
array($this->value);}else{$res=array();foreach((array)$this->value
as$val){if(is_scalar($val)){$res[]=$val;}}return$res;}}function
getSelectedItem(){return$this->areKeysUsed()?array_intersect_key($this->allowed,array_flip($this->getValue())):$this->getValue();}function
getHtmlName(){return
parent::getHtmlName().'[]';}function
getControl(){return
parent::getControl()->multiple(TRUE);}static
function
validateLength(NMultiSelectBox$control,$range){if(!is_array($range)){$range=array($range,$range);}$count=count($control->getSelectedItem());return($range[0]===NULL||$count>=$range[0])&&($range[1]===NULL||$count<=$range[1]);}}class
NRadioList
extends
NFormControl{protected$separator;protected$container;protected$items=array();function
__construct($label=NULL,array$items=NULL){parent::__construct($label);$this->control->type='radio';$this->container=NHtml::el();$this->separator=NHtml::el('br');if($items!==NULL){$this->setItems($items);}}function
getValue($raw=FALSE){return
is_scalar($this->value)&&($raw||isset($this->items[$this->value]))?$this->value:NULL;}function
isFilled(){return$this->getValue()!==NULL;}function
setItems(array$items){$this->items=$items;return$this;}function
getItems(){return$this->items;}function
getSeparatorPrototype(){return$this->separator;}function
getContainerPrototype(){return$this->container;}function
getControl($key=NULL){if($key===NULL){$container=clone$this->container;$separator=(string)$this->separator;}elseif(!isset($this->items[$key])){return
NULL;}$control=parent::getControl();$id=$control->id;$counter=-1;$value=$this->value===NULL?NULL:(string)$this->getValue();$label=NHtml::el('label');foreach($this->items
as$k=>$val){$counter++;if($key!==NULL&&(string)$key!==(string)$k){continue;}$control->id=$label->for=$id.'-'.$counter;$control->checked=(string)$k===$value;$control->value=$k;if($val
instanceof
NHtml){$label->setHtml($val);}else{$label->setText($this->translate((string)$val));}if($key!==NULL){return
NHtml::el()->add($control)->add($label);}$container->add((string)$control.(string)$label.$separator);$control->data('nette-rules',NULL);}return$container;}function
getLabel($caption=NULL){$label=parent::getLabel($caption);$label->for=NULL;return$label;}}abstract
class
NTextBase
extends
NFormControl{protected$emptyValue='';protected$filters=array();function
__construct($label=NULL){parent::__construct($label);$this->addFilter($this->sanitize);}function
setValue($value){$this->value=is_array($value)?'':str_replace("\r\n","\n",$value);return$this;}function
getValue(){$value=$this->value;foreach($this->filters
as$filter){$value=(string)$filter->invoke($value);}return$value===$this->translate($this->emptyValue)?'':$value;}function
setEmptyValue($value){$this->emptyValue=(string)$value;return$this;}function
getEmptyValue(){return$this->emptyValue;}function
addFilter($filter){$this->filters[]=new
NCallback($filter);return$this;}function
sanitize($value){if($this->control->maxlength){$value=NStrings::substring($value,0,$this->control->maxlength);}if(strcasecmp($this->control->getName(),'input')===0){$value=NStrings::trim(strtr($value,"\r\n",'  '));}return$value;}function
getControl(){$control=parent::getControl();foreach($this->getRules()as$rule){if($rule->type===NRule::VALIDATOR&&!$rule->isNegative&&($rule->operation===NForm::LENGTH||$rule->operation===NForm::MAX_LENGTH)){$control->maxlength=is_array($rule->arg)?$rule->arg[1]:$rule->arg;}}if($this->emptyValue!==''){$control->data('nette-empty-value',$this->translate($this->emptyValue));}return$control;}function
addRule($operation,$message=NULL,$arg=NULL){if($operation===NForm::FLOAT){$this->addFilter(array(__CLASS__,'filterFloat'));}elseif($operation===NForm::LENGTH||$operation===NForm::MAX_LENGTH){$tmp=is_array($arg)?$arg[1]:$arg;$this->control->maxlength=is_scalar($tmp)?$tmp:NULL;}return
parent::addRule($operation,$message,$arg);}static
function
validateMinLength(NTextBase$control,$length){return
NStrings::length($control->getValue())>=$length;}static
function
validateMaxLength(NTextBase$control,$length){return
NStrings::length($control->getValue())<=$length;}static
function
validateLength(NTextBase$control,$range){if(!is_array($range)){$range=array($range,$range);}return
NValidators::isInRange(NStrings::length($control->getValue()),$range);}static
function
validateEmail(NTextBase$control){return
NValidators::isEmail($control->getValue());}static
function
validateUrl(NTextBase$control){return
NValidators::isUrl($control->getValue())||NValidators::isUrl('http://'.$control->getValue());}static
function
validateRegexp(NTextBase$control,$regexp){return(bool)NStrings::match($control->getValue(),$regexp);}static
function
validatePattern(NTextBase$control,$pattern){return(bool)NStrings::match($control->getValue(),"\x01^($pattern)\\z\x01u");}static
function
validateInteger(NTextBase$control){return
NValidators::isNumericInt($control->getValue());}static
function
validateFloat(NTextBase$control){return
NValidators::isNumeric(self::filterFloat($control->getValue()));}static
function
validateRange(NTextBase$control,$range){return
NValidators::isInRange($control->getValue(),$range);}static
function
filterFloat($s){return
str_replace(array(' ',','),array('','.'),$s);}}class
NTextArea
extends
NTextBase{function
__construct($label=NULL,$cols=NULL,$rows=NULL){parent::__construct($label);$this->control->setName('textarea');$this->control->cols=$cols;$this->control->rows=$rows;}function
getControl(){$control=parent::getControl();$control->setText($this->getValue()===''?$this->translate($this->emptyValue):$this->value);return$control;}}class
NTextInput
extends
NTextBase{function
__construct($label=NULL,$cols=NULL,$maxLength=NULL){parent::__construct($label);$this->control->type='text';$this->control->size=$cols;$this->control->maxlength=$maxLength;}function
setType($type){$this->control->type=$type;return$this;}function
setPasswordMode($mode=TRUE){$this->control->type=$mode?'password':'text';return$this;}function
getControl(){$control=parent::getControl();foreach($this->getRules()as$rule){if($rule->isNegative||$rule->type!==NRule::VALIDATOR){}elseif($rule->operation===NForm::RANGE&&$control->type!=='text'){list($control->min,$control->max)=$rule->arg;}elseif($rule->operation===NForm::PATTERN){$control->pattern=$rule->arg;}}if($control->type!=='password'){$control->value=$this->getValue()===''?$this->translate($this->emptyValue):$this->value;}return$control;}}class
NUploadControl
extends
NFormControl{function
__construct($label=NULL){parent::__construct($label);$this->control->type='file';}protected
function
attached($form){if($form
instanceof
NForm){if($form->getMethod()!==NForm::POST){throw
new
InvalidStateException('File upload requires method POST.');}$form->getElementPrototype()->enctype='multipart/form-data';}parent::attached($form);}function
setValue($value){if(is_array($value)){$this->value=new
NHttpUploadedFile($value);}elseif($value
instanceof
NHttpUploadedFile){$this->value=$value;}else{$this->value=new
NHttpUploadedFile(NULL);}return$this;}function
isFilled(){return$this->value
instanceof
NHttpUploadedFile&&$this->value->isOK();}static
function
validateFileSize(NUploadControl$control,$limit){$file=$control->getValue();return$file
instanceof
NHttpUploadedFile&&$file->getSize()<=$limit;}static
function
validateMimeType(NUploadControl$control,$mimeType){$file=$control->getValue();if($file
instanceof
NHttpUploadedFile){$type=strtolower($file->getContentType());$mimeTypes=is_array($mimeType)?$mimeType:explode(',',$mimeType);if(in_array($type,$mimeTypes,TRUE)){return
TRUE;}if(in_array(preg_replace('#/.*#','/*',$type),$mimeTypes,TRUE)){return
TRUE;}}return
FALSE;}static
function
validateImage(NUploadControl$control){$file=$control->getValue();return$file
instanceof
NHttpUploadedFile&&$file->isImage();}}class
NDefaultFormRenderer
extends
NObject
implements
IFormRenderer{public$wrappers=array('form'=>array('container'=>NULL,'errors'=>TRUE),'error'=>array('container'=>'ul class=error','item'=>'li'),'group'=>array('container'=>'fieldset','label'=>'legend','description'=>'p'),'controls'=>array('container'=>'table'),'pair'=>array('container'=>'tr','.required'=>'required','.optional'=>NULL,'.odd'=>NULL),'control'=>array('container'=>'td','.odd'=>NULL,'errors'=>FALSE,'description'=>'small','requiredsuffix'=>'','.required'=>'required','.text'=>'text','.password'=>'text','.file'=>'text','.submit'=>'button','.image'=>'imagebutton','.button'=>'button'),'label'=>array('container'=>'th','suffix'=>NULL,'requiredsuffix'=>''),'hidden'=>array('container'=>'div'));protected$form;protected$counter;function
render(NForm$form,$mode=NULL){if($this->form!==$form){$this->form=$form;$this->init();}$s='';if(!$mode||$mode==='begin'){$s.=$this->renderBegin();}if((!$mode&&$this->getValue('form errors'))||$mode==='errors'){$s.=$this->renderErrors();}if(!$mode||$mode==='body'){$s.=$this->renderBody();}if(!$mode||$mode==='end'){$s.=$this->renderEnd();}return$s;}function
setClientScript(){trigger_error(__METHOD__.'() is deprecated; use unobstructive JavaScript instead.',E_USER_WARNING);return$this;}protected
function
init(){$wrapper=&$this->wrappers['control'];foreach($this->form->getControls()as$control){if($control->isRequired()&&isset($wrapper['.required'])){$control->getLabelPrototype()->class($wrapper['.required'],TRUE);}$el=$control->getControlPrototype();if($el->getName()==='input'&&isset($wrapper['.'.$el->type])){$el->class($wrapper['.'.$el->type],TRUE);}}}function
renderBegin(){$this->counter=0;foreach($this->form->getControls()as$control){$control->setOption('rendered',FALSE);}if(strcasecmp($this->form->getMethod(),'get')===0){$el=clone$this->form->getElementPrototype();$url=explode('?',(string)$el->action,2);$el->action=$url[0];$s='';if(isset($url[1])){foreach(preg_split('#[;&]#',$url[1])as$param){$parts=explode('=',$param,2);$name=urldecode($parts[0]);if(!isset($this->form[$name])){$s.=NHtml::el('input',array('type'=>'hidden','name'=>$name,'value'=>urldecode($parts[1])));}}$s="\n\t".$this->getWrapper('hidden container')->setHtml($s);}return$el->startTag().$s;}else{return$this->form->getElementPrototype()->startTag();}}function
renderEnd(){$s='';foreach($this->form->getControls()as$control){if($control
instanceof
NHiddenField&&!$control->getOption('rendered')){$s.=(string)$control->getControl();}}if(iterator_count($this->form->getComponents(TRUE,'NTextInput'))<2){$s.='<!--[if IE]><input type=IEbug disabled style="display:none"><![endif]-->';}if($s){$s=$this->getWrapper('hidden container')->setHtml($s)."\n";}return$s.$this->form->getElementPrototype()->endTag()."\n";}function
renderErrors(IFormControl$control=NULL){$errors=$control===NULL?$this->form->getErrors():$control->getErrors();if(count($errors)){$ul=$this->getWrapper('error container');$li=$this->getWrapper('error item');foreach($errors
as$error){$item=clone$li;if($error
instanceof
NHtml){$item->add($error);}else{$item->setText($error);}$ul->add($item);}return"\n".$ul->render(0);}}function
renderBody(){$s=$remains='';$defaultContainer=$this->getWrapper('group container');$translator=$this->form->getTranslator();foreach($this->form->getGroups()as$group){if(!$group->getControls()||!$group->getOption('visual')){continue;}$container=$group->getOption('container',$defaultContainer);$container=$container
instanceof
NHtml?clone$container:NHtml::el($container);$s.="\n".$container->startTag();$text=$group->getOption('label');if($text
instanceof
NHtml){$s.=$text;}elseif(is_string($text)){if($translator!==NULL){$text=$translator->translate($text);}$s.="\n".$this->getWrapper('group label')->setText($text)."\n";}$text=$group->getOption('description');if($text
instanceof
NHtml){$s.=$text;}elseif(is_string($text)){if($translator!==NULL){$text=$translator->translate($text);}$s.=$this->getWrapper('group description')->setText($text)."\n";}$s.=$this->renderControls($group);$remains=$container->endTag()."\n".$remains;if(!$group->getOption('embedNext')){$s.=$remains;$remains='';}}$s.=$remains.$this->renderControls($this->form);$container=$this->getWrapper('form container');$container->setHtml($s);return$container->render(0);}function
renderControls($parent){if(!($parent
instanceof
NFormContainer||$parent
instanceof
NFormGroup)){throw
new
InvalidArgumentException('Argument must be FormContainer or FormGroup instance.');}$container=$this->getWrapper('controls container');$buttons=NULL;foreach($parent->getControls()as$control){if($control->getOption('rendered')||$control
instanceof
NHiddenField||$control->getForm(FALSE)!==$this->form){}elseif($control
instanceof
NButton){$buttons[]=$control;}else{if($buttons){$container->add($this->renderPairMulti($buttons));$buttons=NULL;}$container->add($this->renderPair($control));}}if($buttons){$container->add($this->renderPairMulti($buttons));}$s='';if(count($container)){$s.="\n".$container."\n";}return$s;}function
renderPair(IFormControl$control){$pair=$this->getWrapper('pair container');$pair->add($this->renderLabel($control));$pair->add($this->renderControl($control));$pair->class($this->getValue($control->isRequired()?'pair .required':'pair .optional'),TRUE);$pair->class($control->getOption('class'),TRUE);if(++$this->counter
%
2){$pair->class($this->getValue('pair .odd'),TRUE);}$pair->id=$control->getOption('id');return$pair->render(0);}function
renderPairMulti(array$controls){$s=array();foreach($controls
as$control){if(!$control
instanceof
IFormControl){throw
new
InvalidArgumentException('Argument must be array of IFormControl instances.');}$s[]=(string)$control->getControl();}$pair=$this->getWrapper('pair container');$pair->add($this->renderLabel($control));$pair->add($this->getWrapper('control container')->setHtml(implode(' ',$s)));return$pair->render(0);}function
renderLabel(IFormControl$control){$head=$this->getWrapper('label container');if($control
instanceof
NCheckbox||$control
instanceof
NButton){return$head->setHtml(($head->getName()==='td'||$head->getName()==='th')?'&nbsp;':'');}else{$label=$control->getLabel();$suffix=$this->getValue('label suffix').($control->isRequired()?$this->getValue('label requiredsuffix'):'');if($label
instanceof
NHtml){$label->setHtml($label->getHtml().$suffix);$suffix='';}return$head->setHtml((string)$label.$suffix);}}function
renderControl(IFormControl$control){$body=$this->getWrapper('control container');if($this->counter
%
2){$body->class($this->getValue('control .odd'),TRUE);}$description=$control->getOption('description');if($description
instanceof
NHtml){$description=' '.$control->getOption('description');}elseif(is_string($description)){$description=' '.$this->getWrapper('control description')->setText($control->translate($description));}else{$description='';}if($control->isRequired()){$description=$this->getValue('control requiredsuffix').$description;}if($this->getValue('control errors')){$description.=$this->renderErrors($control);}if($control
instanceof
NCheckbox||$control
instanceof
NButton){return$body->setHtml((string)$control->getControl().(string)$control->getLabel().$description);}else{return$body->setHtml((string)$control->getControl().$description);}}protected
function
getWrapper($name){$data=$this->getValue($name);return$data
instanceof
NHtml?clone$data:NHtml::el($data);}protected
function
getValue($name){$name=explode(' ',$name);$data=&$this->wrappers[$name[0]][$name[1]];return$data;}}class
NRule
extends
NObject{const
CONDITION=1;const
VALIDATOR=2;const
FILTER=3;public$control;public$operation;public$arg;public$type;public$isNegative=FALSE;public$message;public$subRules;}class
NRules
extends
NObject
implements
IteratorAggregate{const
VALIDATE_PREFIX='validate';public
static$defaultMessages=array(NForm::PROTECTION=>'Please submit this form again (security token has expired).',NForm::EQUAL=>'Please enter %s.',NForm::FILLED=>'Please complete mandatory field.',NForm::MIN_LENGTH=>'Please enter a value of at least %d characters.',NForm::MAX_LENGTH=>'Please enter a value no longer than %d characters.',NForm::LENGTH=>'Please enter a value between %d and %d characters long.',NForm::EMAIL=>'Please enter a valid email address.',NForm::URL=>'Please enter a valid URL.',NForm::INTEGER=>'Please enter a numeric value.',NForm::FLOAT=>'Please enter a numeric value.',NForm::RANGE=>'Please enter a value between %d and %d.',NForm::MAX_FILE_SIZE=>'The size of the uploaded file can be up to %d bytes.',NForm::IMAGE=>'The uploaded file must be image in format JPEG, GIF or PNG.',NForm::MIME_TYPE=>'The uploaded file is not in the expected format.');private$rules=array();private$parent;private$toggles=array();private$control;function
__construct(IFormControl$control){$this->control=$control;}function
addRule($operation,$message=NULL,$arg=NULL){$rule=new
NRule;$rule->control=$this->control;$rule->operation=$operation;$this->adjustOperation($rule);$rule->arg=$arg;$rule->type=NRule::VALIDATOR;if($message===NULL&&is_string($rule->operation)&&isset(self::$defaultMessages[$rule->operation])){$rule->message=self::$defaultMessages[$rule->operation];}else{$rule->message=$message;}$this->rules[]=$rule;return$this;}function
addCondition($operation,$arg=NULL){return$this->addConditionOn($this->control,$operation,$arg);}function
addConditionOn(IFormControl$control,$operation,$arg=NULL){$rule=new
NRule;$rule->control=$control;$rule->operation=$operation;$this->adjustOperation($rule);$rule->arg=$arg;$rule->type=NRule::CONDITION;$rule->subRules=new
self($this->control);$rule->subRules->parent=$this;$this->rules[]=$rule;return$rule->subRules;}function
elseCondition(){$rule=clone
end($this->parent->rules);$rule->isNegative=!$rule->isNegative;$rule->subRules=new
self($this->parent->control);$rule->subRules->parent=$this->parent;$this->parent->rules[]=$rule;return$rule->subRules;}function
endCondition(){return$this->parent;}function
toggle($id,$hide=TRUE){$this->toggles[$id]=$hide;return$this;}function
validate($onlyCheck=FALSE){foreach($this->rules
as$rule){if($rule->control->isDisabled()){continue;}$success=($rule->isNegative
xor$this->getCallback($rule)->invoke($rule->control,$rule->arg));if($rule->type===NRule::CONDITION&&$success){if(!$rule->subRules->validate($onlyCheck)){return
FALSE;}}elseif($rule->type===NRule::VALIDATOR&&!$success){if(!$onlyCheck){$rule->control->addError(self::formatMessage($rule,TRUE));}return
FALSE;}}return
TRUE;}function
getIterator(){return
new
ArrayIterator($this->rules);}function
getToggles(){return$this->toggles;}private
function
adjustOperation($rule){if(is_string($rule->operation)&&ord($rule->operation[0])>127){$rule->isNegative=TRUE;$rule->operation=~$rule->operation;}if(!$this->getCallback($rule)->isCallable()){$operation=is_scalar($rule->operation)?" '$rule->operation'":'';throw
new
InvalidArgumentException("Unknown operation$operation for control '{$rule->control->getName()}'.");}}private
function
getCallback($rule){$op=$rule->operation;if(is_string($op)&&strncmp($op,':',1)===0){return
new
NCallback(get_class($rule->control),self::VALIDATE_PREFIX.ltrim($op,':'));}else{return
new
NCallback($op);}}static
function
formatMessage($rule,$withValue){$message=$rule->message;if($message
instanceof
NHtml){return$message;}if($message==NULL){trigger_error("Missing validation message for control '{$rule->control->getName()}'.",E_USER_WARNING);}if($translator=$rule->control->getForm()->getTranslator()){$message=$translator->translate($message,is_int($rule->arg)?$rule->arg:NULL);}$message=vsprintf(preg_replace('#%(name|label|value)#','%$0',$message),(array)$rule->arg);$message=str_replace('%name',$rule->control->getName(),$message);$message=str_replace('%label',$rule->control->translate($rule->control->caption),$message);if($withValue&&strpos($message,'%value')!==FALSE){$message=str_replace('%value',$rule->control->getValue(),$message);}return$message;}}class
NHttpContext
extends
NObject{private$request;private$response;function
__construct(IHttpRequest$request,IHttpResponse$response){$this->request=$request;$this->response=$response;}function
isModified($lastModified=NULL,$etag=NULL){if($lastModified){$this->response->setHeader('Last-Modified',$this->response->date($lastModified));}if($etag){$this->response->setHeader('ETag','"'.addslashes($etag).'"');}$ifNoneMatch=$this->request->getHeader('If-None-Match');if($ifNoneMatch==='*'){$match=TRUE;}elseif($ifNoneMatch!==NULL){$etag=$this->response->getHeader('ETag');if($etag==NULL||strpos(' '.strtr($ifNoneMatch,",\t",'  '),' '.$etag)===FALSE){return
TRUE;}else{$match=TRUE;}}$ifModifiedSince=$this->request->getHeader('If-Modified-Since');if($ifModifiedSince!==NULL){$lastModified=$this->response->getHeader('Last-Modified');if($lastModified!=NULL&&strtotime($lastModified)<=strtotime($ifModifiedSince)){$match=TRUE;}else{return
TRUE;}}if(empty($match)){return
TRUE;}$this->response->setCode(IHttpResponse::S304_NOT_MODIFIED);return
FALSE;}function
getRequest(){return$this->request;}function
getResponse(){return$this->response;}}class
NHttpUploadedFile
extends
NObject{private$name;private$type;private$size;private$tmpName;private$error;function
__construct($value){foreach(array('name','type','size','tmp_name','error')as$key){if(!isset($value[$key])||!is_scalar($value[$key])){$this->error=UPLOAD_ERR_NO_FILE;return;}}$this->name=$value['name'];$this->size=$value['size'];$this->tmpName=$value['tmp_name'];$this->error=$value['error'];}function
getName(){return$this->name;}function
getSanitizedName(){return
trim(NStrings::webalize($this->name,'.',FALSE),'.-');}function
getContentType(){if($this->isOk()&&$this->type===NULL){$this->type=NMimeTypeDetector::fromFile($this->tmpName);}return$this->type;}function
getSize(){return$this->size;}function
getTemporaryFile(){return$this->tmpName;}function
__toString(){return(string)$this->tmpName;}function
getError(){return$this->error;}function
isOk(){return$this->error===UPLOAD_ERR_OK;}function
move($dest){@mkdir(dirname($dest),0777,TRUE);@unlink($dest);if(!call_user_func(is_uploaded_file($this->tmpName)?'move_uploaded_file':'rename',$this->tmpName,$dest)){throw
new
InvalidStateException("Unable to move uploaded file '$this->tmpName' to '$dest'.");}chmod($dest,0666);$this->tmpName=$dest;return$this;}function
isImage(){return
in_array($this->getContentType(),array('image/gif','image/png','image/jpeg'),TRUE);}function
toImage(){return
NImage::fromFile($this->tmpName);}function
getImageSize(){return$this->isOk()?@getimagesize($this->tmpName):NULL;}function
getContents(){return$this->isOk()?file_get_contents($this->tmpName):NULL;}}class
NHttpRequest
extends
NObject
implements
IHttpRequest{private$method;private$url;private$query;private$post;private$files;private$cookies;private$headers;private$remoteAddress;private$remoteHost;function
__construct(NUrlScript$url,$query=NULL,$post=NULL,$files=NULL,$cookies=NULL,$headers=NULL,$method=NULL,$remoteAddress=NULL,$remoteHost=NULL){$this->url=$url;$this->url->freeze();if($query===NULL){parse_str($url->query,$this->query);}else{$this->query=(array)$query;}$this->post=(array)$post;$this->files=(array)$files;$this->cookies=(array)$cookies;$this->headers=(array)$headers;$this->method=$method;$this->remoteAddress=$remoteAddress;$this->remoteHost=$remoteHost;}function
getUrl(){return$this->url;}function
getUri(){trigger_error(__METHOD__.'() is deprecated; use '.__CLASS__.'::getUrl() instead.',E_USER_WARNING);return$this->getUrl();}function
getQuery($key=NULL,$default=NULL){if(func_num_args()===0){return$this->query;}elseif(isset($this->query[$key])){return$this->query[$key];}else{return$default;}}function
getPost($key=NULL,$default=NULL){if(func_num_args()===0){return$this->post;}elseif(isset($this->post[$key])){return$this->post[$key];}else{return$default;}}function
getFile($key){$_args=func_get_args();return
NArrays::get($this->files,$_args,NULL);}function
getFiles(){return$this->files;}function
getCookie($key,$default=NULL){if(func_num_args()===0){return$this->cookies;}elseif(isset($this->cookies[$key])){return$this->cookies[$key];}else{return$default;}}function
getCookies(){return$this->cookies;}function
getMethod(){return$this->method;}function
isMethod($method){return
strcasecmp($this->method,$method)===0;}function
isPost(){return$this->isMethod('POST');}function
getHeader($header,$default=NULL){$header=strtolower($header);if(isset($this->headers[$header])){return$this->headers[$header];}else{return$default;}}function
getHeaders(){return$this->headers;}function
getReferer(){return
isset($this->headers['referer'])?new
NUrl($this->headers['referer']):NULL;}function
isSecured(){return$this->url->scheme==='https';}function
isAjax(){return$this->getHeader('X-Requested-With')==='XMLHttpRequest';}function
getRemoteAddress(){return$this->remoteAddress;}function
getRemoteHost(){if(!$this->remoteHost){$this->remoteHost=$this->remoteAddress?getHostByAddr($this->remoteAddress):NULL;}return$this->remoteHost;}function
detectLanguage(array$langs){$header=$this->getHeader('Accept-Language');if(!$header){return
NULL;}$s=strtolower($header);$s=strtr($s,'_','-');rsort($langs);preg_match_all('#('.implode('|',$langs).')(?:-[^\s,;=]+)?\s*(?:;\s*q=([0-9.]+))?#',$s,$matches);if(!$matches[0]){return
NULL;}$max=0;$lang=NULL;foreach($matches[1]as$key=>$value){$q=$matches[2][$key]===''?1.0:(float)$matches[2][$key];if($q>$max){$max=$q;$lang=$value;}}return$lang;}}class
NHttpRequestFactory
extends
NObject{const
NONCHARS='#[^\x09\x0A\x0D\x20-\x7E\xA0-\x{10FFFF}]#u';public$urlFilters=array('path'=>array('#/{2,}#'=>'/'),'url'=>array());private$encoding;function
setEncoding($encoding){$this->encoding=$encoding;return$this;}function
createHttpRequest(){$url=new
NUrlScript;$url->scheme=!empty($_SERVER['HTTPS'])&&strcasecmp($_SERVER['HTTPS'],'off')?'https':'http';$url->user=isset($_SERVER['PHP_AUTH_USER'])?$_SERVER['PHP_AUTH_USER']:'';$url->password=isset($_SERVER['PHP_AUTH_PW'])?$_SERVER['PHP_AUTH_PW']:'';if((isset($_SERVER[$tmp='HTTP_HOST'])||isset($_SERVER[$tmp='SERVER_NAME']))&&preg_match('#^([a-z0-9_.-]+|\[[a-fA-F0-9:]+\])(:\d+)?\z#',$_SERVER[$tmp],$pair)){$url->host=strtolower($pair[1]);if(isset($pair[2])){$url->port=(int)substr($pair[2],1);}elseif(isset($_SERVER['SERVER_PORT'])){$url->port=(int)$_SERVER['SERVER_PORT'];}}if(isset($_SERVER['REQUEST_URI'])){$requestUrl=$_SERVER['REQUEST_URI'];}elseif(isset($_SERVER['ORIG_PATH_INFO'])){$requestUrl=$_SERVER['ORIG_PATH_INFO'];if(isset($_SERVER['QUERY_STRING'])&&$_SERVER['QUERY_STRING']!=''){$requestUrl.='?'.$_SERVER['QUERY_STRING'];}}else{$requestUrl='';}$requestUrl=NStrings::replace($requestUrl,$this->urlFilters['url']);$tmp=explode('?',$requestUrl,2);$url->path=NStrings::replace($tmp[0],$this->urlFilters['path']);$url->query=isset($tmp[1])?$tmp[1]:'';$url->canonicalize();$url->path=NStrings::fixEncoding($url->path);if(isset($_SERVER['SCRIPT_NAME'])){$script=$_SERVER['SCRIPT_NAME'];}elseif(isset($_SERVER['DOCUMENT_ROOT'],$_SERVER['SCRIPT_FILENAME'])&&strncmp($_SERVER['DOCUMENT_ROOT'],$_SERVER['SCRIPT_FILENAME'],strlen($_SERVER['DOCUMENT_ROOT']))===0){$script='/'.ltrim(strtr(substr($_SERVER['SCRIPT_FILENAME'],strlen($_SERVER['DOCUMENT_ROOT'])),'\\','/'),'/');}else{$script='/';}$path=strtolower($url->path).'/';$script=strtolower($script).'/';$max=min(strlen($path),strlen($script));for($i=0;$i<$max;$i++){if($path[$i]!==$script[$i]){break;}elseif($path[$i]==='/'){$url->scriptPath=substr($url->path,0,$i+1);}}$useFilter=(!in_array(ini_get('filter.default'),array('','unsafe_raw'))||ini_get('filter.default_flags'));parse_str($url->query,$query);if(!$query){$query=$useFilter?filter_input_array(INPUT_GET,FILTER_UNSAFE_RAW):(empty($_GET)?array():$_GET);}$post=$useFilter?filter_input_array(INPUT_POST,FILTER_UNSAFE_RAW):(empty($_POST)?array():$_POST);$cookies=$useFilter?filter_input_array(INPUT_COOKIE,FILTER_UNSAFE_RAW):(empty($_COOKIE)?array():$_COOKIE);$gpc=(bool)get_magic_quotes_gpc();$old=error_reporting(error_reporting()^E_NOTICE);if($gpc||$this->encoding){$utf=strcasecmp($this->encoding,'UTF-8')===0;$list=array(&$query,&$post,&$cookies);while(list($key,$val)=each($list)){foreach($val
as$k=>$v){unset($list[$key][$k]);if($gpc){$k=stripslashes($k);}if($this->encoding&&is_string($k)&&(preg_match(self::NONCHARS,$k)||preg_last_error())){}elseif(is_array($v)){$list[$key][$k]=$v;$list[]=&$list[$key][$k];}else{if($gpc&&!$useFilter){$v=stripSlashes($v);}if($this->encoding){if($utf){$v=NStrings::fixEncoding($v);}else{if(!NStrings::checkEncoding($v)){$v=iconv($this->encoding,'UTF-8//IGNORE',$v);}$v=html_entity_decode($v,ENT_QUOTES,'UTF-8');}$v=preg_replace(self::NONCHARS,'',$v);}$list[$key][$k]=$v;}}}unset($list,$key,$val,$k,$v);}$files=array();$list=array();if(!empty($_FILES)){foreach($_FILES
as$k=>$v){if($this->encoding&&is_string($k)&&(preg_match(self::NONCHARS,$k)||preg_last_error())){continue;}$v['@']=&$files[$k];$list[]=$v;}}while(list(,$v)=each($list)){if(!isset($v['name'])){continue;}elseif(!is_array($v['name'])){if($gpc){$v['name']=stripSlashes($v['name']);}if($this->encoding){$v['name']=preg_replace(self::NONCHARS,'',NStrings::fixEncoding($v['name']));}$v['@']=new
NHttpUploadedFile($v);continue;}foreach($v['name']as$k=>$foo){if($this->encoding&&is_string($k)&&(preg_match(self::NONCHARS,$k)||preg_last_error())){continue;}$list[]=array('name'=>$v['name'][$k],'type'=>$v['type'][$k],'size'=>$v['size'][$k],'tmp_name'=>$v['tmp_name'][$k],'error'=>$v['error'][$k],'@'=>&$v['@'][$k]);}}error_reporting($old);if(function_exists('apache_request_headers')){$headers=array_change_key_case(apache_request_headers(),CASE_LOWER);}else{$headers=array();foreach($_SERVER
as$k=>$v){if(strncmp($k,'HTTP_',5)==0){$k=substr($k,5);}elseif(strncmp($k,'CONTENT_',8)){continue;}$headers[strtr(strtolower($k),'_','-')]=$v;}}return
new
NHttpRequest($url,$query,$post,$files,$cookies,$headers,isset($_SERVER['REQUEST_METHOD'])?$_SERVER['REQUEST_METHOD']:NULL,isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:NULL,isset($_SERVER['REMOTE_HOST'])?$_SERVER['REMOTE_HOST']:NULL);}}class
NHttpResponse
extends
NObject
implements
IHttpResponse{private
static$fixIE=TRUE;public$cookieDomain='';public$cookiePath='/';public$cookieSecure=FALSE;public$cookieHttpOnly=TRUE;private$code=self::S200_OK;function
setCode($code){$code=(int)$code;if($code<100||$code>599){throw
new
InvalidArgumentException("Bad HTTP response '$code'.");}elseif(headers_sent($file,$line)){throw
new
InvalidStateException("Cannot set HTTP code after HTTP headers have been sent".($file?" (output started at $file:$line).":"."));}else{$this->code=$code;$protocol=isset($_SERVER['SERVER_PROTOCOL'])?$_SERVER['SERVER_PROTOCOL']:'HTTP/1.1';header($protocol.' '.$code,TRUE,$code);}return$this;}function
getCode(){return$this->code;}function
setHeader($name,$value){if(headers_sent($file,$line)){throw
new
InvalidStateException("Cannot send header after HTTP headers have been sent".($file?" (output started at $file:$line).":"."));}if($value===NULL&&function_exists('header_remove')){header_remove($name);}elseif(strcasecmp($name,'Content-Length')===0&&ini_get('zlib.output_compression')){}else{header($name.': '.$value,TRUE,$this->code);}return$this;}function
addHeader($name,$value){if(headers_sent($file,$line)){throw
new
InvalidStateException("Cannot send header after HTTP headers have been sent".($file?" (output started at $file:$line).":"."));}header($name.': '.$value,FALSE,$this->code);return$this;}function
setContentType($type,$charset=NULL){$this->setHeader('Content-Type',$type.($charset?'; charset='.$charset:''));return$this;}function
redirect($url,$code=self::S302_FOUND){if(isset($_SERVER['SERVER_SOFTWARE'])&&preg_match('#^Microsoft-IIS/[1-5]#',$_SERVER['SERVER_SOFTWARE'])&&$this->getHeader('Set-Cookie')!==NULL){$this->setHeader('Refresh',"0;url=$url");return;}$this->setCode($code);$this->setHeader('Location',$url);echo"<h1>Redirect</h1>\n\n<p><a href=\"".htmlSpecialChars($url)."\">Please click here to continue</a>.</p>";}function
setExpiration($time){if(!$time){$this->setHeader('Cache-Control','s-maxage=0, max-age=0, must-revalidate');$this->setHeader('Expires','Mon, 23 Jan 1978 10:00:00 GMT');return$this;}$time=NDateTime53::from($time);$this->setHeader('Cache-Control','max-age='.($time->format('U')-time()));$this->setHeader('Expires',self::date($time));return$this;}function
isSent(){return
headers_sent();}function
getHeader($header,$default=NULL){$header.=':';$len=strlen($header);foreach(headers_list()as$item){if(strncasecmp($item,$header,$len)===0){return
ltrim(substr($item,$len));}}return$default;}function
getHeaders(){$headers=array();foreach(headers_list()as$header){$a=strpos($header,':');$headers[substr($header,0,$a)]=(string)substr($header,$a+2);}return$headers;}static
function
date($time=NULL){$time=NDateTime53::from($time);$time->setTimezone(new
DateTimeZone('GMT'));return$time->format('D, d M Y H:i:s \G\M\T');}function
__destruct(){if(self::$fixIE&&isset($_SERVER['HTTP_USER_AGENT'])&&strpos($_SERVER['HTTP_USER_AGENT'],'MSIE ')!==FALSE&&in_array($this->code,array(400,403,404,405,406,408,409,410,500,501,505),TRUE)&&$this->getHeader('Content-Type','text/html')==='text/html'){echo
NStrings::random(2e3," \t\r\n");self::$fixIE=FALSE;}}function
setCookie($name,$value,$time,$path=NULL,$domain=NULL,$secure=NULL,$httpOnly=NULL){if(headers_sent($file,$line)){throw
new
InvalidStateException("Cannot set cookie after HTTP headers have been sent".($file?" (output started at $file:$line).":"."));}setcookie($name,$value,$time?NDateTime53::from($time)->format('U'):0,$path===NULL?$this->cookiePath:(string)$path,$domain===NULL?$this->cookieDomain:(string)$domain,$secure===NULL?$this->cookieSecure:(bool)$secure,$httpOnly===NULL?$this->cookieHttpOnly:(bool)$httpOnly);$this->removeDuplicateCookies();return$this;}function
removeDuplicateCookies(){if(headers_sent($file,$line)||ini_get('suhosin.cookie.encrypt')){return;}$flatten=array();foreach(headers_list()as$header){if(preg_match('#^Set-Cookie: .+?=#',$header,$m)){$flatten[$m[0]]=$header;if(PHP_VERSION_ID<50300){header('Set-Cookie:');}else{header_remove('Set-Cookie');}}}foreach(array_values($flatten)as$key=>$header){header($header,$key===0);}}function
deleteCookie($name,$path=NULL,$domain=NULL,$secure=NULL){$this->setCookie($name,FALSE,0,$path,$domain,$secure);}}class
NSession
extends
NObject{const
DEFAULT_FILE_LIFETIME=10800;private$regenerated;private
static$started;private$options=array('referer_check'=>'','use_cookies'=>1,'use_only_cookies'=>1,'use_trans_sid'=>0,'cookie_lifetime'=>0,'cookie_path'=>'/','cookie_domain'=>'','cookie_secure'=>FALSE,'cookie_httponly'=>TRUE,'gc_maxlifetime'=>self::DEFAULT_FILE_LIFETIME,'cache_limiter'=>NULL,'cache_expire'=>NULL,'hash_function'=>NULL,'hash_bits_per_character'=>NULL);private$request;private$response;function
__construct(IHttpRequest$request,IHttpResponse$response){$this->request=$request;$this->response=$response;}function
start(){if(self::$started){return;}$this->configure($this->options);$id=&$_COOKIE[session_name()];if(!is_string($id)||!preg_match('#^[0-9a-zA-Z,-]{22,128}\z#i',$id)){unset($_COOKIE[session_name()]);}set_error_handler(create_function('$severity, $message','extract($GLOBALS[0]['.array_push($GLOBALS[0],array('error'=>&$error)).'-1], EXTR_REFS); // session_start returns FALSE on failure since PHP 5.3.0.
			if (($severity & error_reporting()) === $severity) {
				$error = $message;
				restore_error_handler();
			}
		'));session_start();if(!$error){restore_error_handler();}$this->response->removeDuplicateCookies();if($error&&!session_id()){@session_write_close();throw
new
InvalidStateException("session_start(): $error");}self::$started=TRUE;unset($_SESSION['__NT'],$_SESSION['__NS'],$_SESSION['__NM']);$nf=&$_SESSION['__NF'];@$nf['C']++;if(empty($nf['Time'])){$nf['Time']=time();$this->regenerated=TRUE;}$browserKey=$this->request->getCookie('nette-browser');if(!$browserKey){$browserKey=NStrings::random();}$browserClosed=!isset($nf['B'])||$nf['B']!==$browserKey;$nf['B']=$browserKey;$this->sendCookie();if(isset($nf['META'])){$now=time();foreach($nf['META']as$section=>$metadata){if(is_array($metadata)){foreach($metadata
as$variable=>$value){if((!empty($value['B'])&&$browserClosed)||(!empty($value['T'])&&$now>$value['T'])||(isset($nf['DATA'][$section][$variable])&&is_object($nf['DATA'][$section][$variable])&&(isset($value['V'])?$value['V']:NULL)!=NClassReflection::from($nf['DATA'][$section][$variable])->getAnnotation('serializationVersion'))){if($variable===''){unset($nf['META'][$section],$nf['DATA'][$section]);continue
2;}unset($nf['META'][$section][$variable],$nf['DATA'][$section][$variable]);}}}}}if($this->regenerated){$this->regenerated=FALSE;$this->regenerateId();}register_shutdown_function(array($this,'clean'));}function
isStarted(){return(bool)self::$started;}function
close(){if(self::$started){$this->clean();session_write_close();self::$started=FALSE;}}function
destroy(){if(!self::$started){throw
new
InvalidStateException('Session is not started.');}session_destroy();$_SESSION=NULL;self::$started=FALSE;if(!$this->response->isSent()){$params=session_get_cookie_params();$this->response->deleteCookie(session_name(),$params['path'],$params['domain'],$params['secure']);}}function
exists(){return
self::$started||$this->request->getCookie($this->getName())!==NULL;}function
regenerateId(){if(self::$started&&!$this->regenerated){if(headers_sent($file,$line)){throw
new
InvalidStateException("Cannot regenerate session ID after HTTP headers have been sent".($file?" (output started at $file:$line).":"."));}session_regenerate_id(TRUE);session_write_close();$backup=$_SESSION;session_start();$_SESSION=$backup;$this->response->removeDuplicateCookies();}$this->regenerated=TRUE;}function
getId(){return
session_id();}function
setName($name){if(!is_string($name)||!preg_match('#[^0-9.][^.]*\z#A',$name)){throw
new
InvalidArgumentException('Session name must be a string and cannot contain dot.');}session_name($name);return$this->setOptions(array('name'=>$name));}function
getName(){return
isset($this->options['name'])?$this->options['name']:session_name();}function
getSection($section,$class='NSessionSection'){return
new$class($this,$section);}function
getNamespace($section){trigger_error(__METHOD__.'() is deprecated; use getSection() instead.',E_USER_WARNING);return$this->getSection($section);}function
hasSection($section){if($this->exists()&&!self::$started){$this->start();}return!empty($_SESSION['__NF']['DATA'][$section]);}function
getIterator(){if($this->exists()&&!self::$started){$this->start();}if(isset($_SESSION['__NF']['DATA'])){return
new
ArrayIterator(array_keys($_SESSION['__NF']['DATA']));}else{return
new
ArrayIterator;}}function
clean(){if(!self::$started||empty($_SESSION)){return;}$nf=&$_SESSION['__NF'];if(isset($nf['META'])&&is_array($nf['META'])){foreach($nf['META']as$name=>$foo){if(empty($nf['META'][$name])){unset($nf['META'][$name]);}}}if(empty($nf['META'])){unset($nf['META']);}if(empty($nf['DATA'])){unset($nf['DATA']);}}function
setOptions(array$options){if(self::$started){$this->configure($options);}$this->options=$options+$this->options;if(!empty($options['auto_start'])){$this->start();}return$this;}function
getOptions(){return$this->options;}private
function
configure(array$config){$special=array('cache_expire'=>1,'cache_limiter'=>1,'save_path'=>1,'name'=>1);foreach($config
as$key=>$value){if(!strncmp($key,'session.',8)){$key=substr($key,8);}$key=strtolower(preg_replace('#(.)(?=[A-Z])#','$1_',$key));if($value===NULL||ini_get("session.$key")==$value){continue;}elseif(strncmp($key,'cookie_',7)===0){if(!isset($cookie)){$cookie=session_get_cookie_params();}$cookie[substr($key,7)]=$value;}else{if(defined('SID')){throw
new
InvalidStateException("Unable to set 'session.$key' to value '$value' when session has been started".($this->started?".":" by session.auto_start or session_start()."));}if(isset($special[$key])){$key="session_$key";$key($value);}elseif(function_exists('ini_set')){ini_set("session.$key",$value);}elseif(!NFramework::$iAmUsingBadHost){throw
new
NotSupportedException('Required function ini_set() is disabled.');}}}if(isset($cookie)){session_set_cookie_params($cookie['lifetime'],$cookie['path'],$cookie['domain'],$cookie['secure'],$cookie['httponly']);if(self::$started){$this->sendCookie();}}}function
setExpiration($time){if(empty($time)){return$this->setOptions(array('gc_maxlifetime'=>self::DEFAULT_FILE_LIFETIME,'cookie_lifetime'=>0));}else{$time=NDateTime53::from($time)->format('U')-time();return$this->setOptions(array('gc_maxlifetime'=>$time,'cookie_lifetime'=>$time));}}function
setCookieParameters($path,$domain=NULL,$secure=NULL){return$this->setOptions(array('cookie_path'=>$path,'cookie_domain'=>$domain,'cookie_secure'=>$secure));}function
getCookieParameters(){return
session_get_cookie_params();}function
setCookieParams($path,$domain=NULL,$secure=NULL){trigger_error(__METHOD__.'() is deprecated; use setCookieParameters() instead.',E_USER_WARNING);return$this->setCookieParameters($path,$domain,$secure);}function
setSavePath($path){return$this->setOptions(array('save_path'=>$path));}function
setStorage(ISessionStorage$storage){if(self::$started){throw
new
InvalidStateException("Unable to set storage when session has been started.");}session_set_save_handler(array($storage,'open'),array($storage,'close'),array($storage,'read'),array($storage,'write'),array($storage,'remove'),array($storage,'clean'));}private
function
sendCookie(){$cookie=$this->getCookieParameters();$this->response->setCookie(session_name(),session_id(),$cookie['lifetime']?$cookie['lifetime']+time():0,$cookie['path'],$cookie['domain'],$cookie['secure'],$cookie['httponly'])->setCookie('nette-browser',$_SESSION['__NF']['B'],NHttpResponse::BROWSER,$cookie['path'],$cookie['domain']);}}class
NSessionSection
extends
NObject
implements
IteratorAggregate,ArrayAccess{private$session;private$name;private$data;private$meta=FALSE;public$warnOnUndefined=FALSE;function
__construct(NSession$session,$name){if(!is_string($name)){throw
new
InvalidArgumentException("Session namespace must be a string, ".gettype($name)." given.");}$this->session=$session;$this->name=$name;}private
function
start(){if($this->meta===FALSE){$this->session->start();$this->data=&$_SESSION['__NF']['DATA'][$this->name];$this->meta=&$_SESSION['__NF']['META'][$this->name];}}function
getIterator(){$this->start();if(isset($this->data)){return
new
ArrayIterator($this->data);}else{return
new
ArrayIterator;}}function
__set($name,$value){$this->start();$this->data[$name]=$value;if(is_object($value)){$this->meta[$name]['V']=NClassReflection::from($value)->getAnnotation('serializationVersion');}}function&__get($name){$this->start();if($this->warnOnUndefined&&!array_key_exists($name,$this->data)){trigger_error("The variable '$name' does not exist in session section",E_USER_NOTICE);}return$this->data[$name];}function
__isset($name){if($this->session->exists()){$this->start();}return
isset($this->data[$name]);}function
__unset($name){$this->start();unset($this->data[$name],$this->meta[$name]);}function
offsetSet($name,$value){$this->__set($name,$value);}function
offsetGet($name){return$this->__get($name);}function
offsetExists($name){return$this->__isset($name);}function
offsetUnset($name){$this->__unset($name);}function
setExpiration($time,$variables=NULL){$this->start();if(empty($time)){$time=NULL;$whenBrowserIsClosed=TRUE;}else{$time=NDateTime53::from($time)->format('U');$max=ini_get('session.gc_maxlifetime');if($time-time()>$max+3){trigger_error("The expiration time is greater than the session expiration $max seconds",E_USER_NOTICE);}$whenBrowserIsClosed=FALSE;}if($variables===NULL){$this->meta['']['T']=$time;$this->meta['']['B']=$whenBrowserIsClosed;}elseif(is_array($variables)){foreach($variables
as$variable){$this->meta[$variable]['T']=$time;$this->meta[$variable]['B']=$whenBrowserIsClosed;}}else{$this->meta[$variables]['T']=$time;$this->meta[$variables]['B']=$whenBrowserIsClosed;}return$this;}function
removeExpiration($variables=NULL){$this->start();if($variables===NULL){unset($this->meta['']['T'],$this->meta['']['B']);}elseif(is_array($variables)){foreach($variables
as$variable){unset($this->meta[$variable]['T'],$this->meta[$variable]['B']);}}else{unset($this->meta[$variables]['T'],$this->meta[$variables]['B']);}}function
remove(){$this->start();$this->data=NULL;$this->meta=NULL;}}class
NUrl
extends
NFreezableObject{public
static$defaultPorts=array('http'=>80,'https'=>443,'ftp'=>21,'news'=>119,'nntp'=>119);private$scheme='';private$user='';private$pass='';private$host='';private$port=NULL;private$path='';private$query='';private$fragment='';function
__construct($url=NULL){if(is_string($url)){$parts=@parse_url($url);if($parts===FALSE){throw
new
InvalidArgumentException("Malformed or unsupported URI '$url'.");}foreach($parts
as$key=>$val){$this->$key=$val;}if(!$this->port&&isset(self::$defaultPorts[$this->scheme])){$this->port=self::$defaultPorts[$this->scheme];}if($this->path===''&&($this->scheme==='http'||$this->scheme==='https')){$this->path='/';}}elseif($url
instanceof
self){foreach($this
as$key=>$val){$this->$key=$url->$key;}}}function
setScheme($value){$this->updating();$this->scheme=(string)$value;return$this;}function
getScheme(){return$this->scheme;}function
setUser($value){$this->updating();$this->user=(string)$value;return$this;}function
getUser(){return$this->user;}function
setPassword($value){$this->updating();$this->pass=(string)$value;return$this;}function
getPassword(){return$this->pass;}function
setHost($value){$this->updating();$this->host=(string)$value;return$this;}function
getHost(){return$this->host;}function
setPort($value){$this->updating();$this->port=(int)$value;return$this;}function
getPort(){return$this->port;}function
setPath($value){$this->updating();$this->path=(string)$value;return$this;}function
getPath(){return$this->path;}function
setQuery($value){$this->updating();$this->query=(string)(is_array($value)?http_build_query($value,'','&'):$value);return$this;}function
appendQuery($value){$this->updating();$value=(string)(is_array($value)?http_build_query($value,'','&'):$value);$this->query.=($this->query===''||$value==='')?$value:'&'.$value;}function
getQuery(){return$this->query;}function
setFragment($value){$this->updating();$this->fragment=(string)$value;return$this;}function
getFragment(){return$this->fragment;}function
getAbsoluteUrl(){return$this->getHostUrl().$this->path.($this->query===''?'':'?'.$this->query).($this->fragment===''?'':'#'.$this->fragment);}function
getAuthority(){$authority=$this->host;if($this->port&&(!isset(self::$defaultPorts[$this->scheme])||$this->port!==self::$defaultPorts[$this->scheme])){$authority.=':'.$this->port;}if($this->user!==''&&$this->scheme!=='http'&&$this->scheme!=='https'){$authority=$this->user.($this->pass===''?'':':'.$this->pass).'@'.$authority;}return$authority;}function
getHostUrl(){return($this->scheme?$this->scheme.':':'').'//'.$this->getAuthority();}function
getBasePath(){$pos=strrpos($this->path,'/');return$pos===FALSE?'':substr($this->path,0,$pos+1);}function
getBaseUrl(){return$this->getHostUrl().$this->getBasePath();}function
getRelativeUrl(){return(string)substr($this->getAbsoluteUrl(),strlen($this->getBaseUrl()));}function
isEqual($url){$part=self::unescape(strtok($url,'?#'),'%/');if(strncmp($part,'//',2)===0){if($part!=='//'.$this->getAuthority().$this->path){return
FALSE;}}elseif(strncmp($part,'/',1)===0){if($part!==$this->path){return
FALSE;}}else{if($part!==$this->getHostUrl().$this->path){return
FALSE;}}$part=preg_split('#[&;]#',self::unescape(strtr((string)strtok('?#'),'+',' '),'%&;=+'));sort($part);$query=preg_split('#[&;]#',$this->query);sort($query);return$part===$query;}function
canonicalize(){$this->updating();$this->path=$this->path===''?'/':self::unescape($this->path,'%/');$this->host=strtolower(rawurldecode($this->host));$this->query=self::unescape(strtr($this->query,'+',' '),'%&;=+');}function
__toString(){return$this->getAbsoluteUrl();}static
function
unescape($s,$reserved='%;/?:@&=+$,'){preg_match_all('#(?<=%)[a-f0-9][a-f0-9]#i',$s,$matches,PREG_OFFSET_CAPTURE|PREG_SET_ORDER);foreach(array_reverse($matches)as$match){$ch=chr(hexdec($match[0][0]));if(strpos($reserved,$ch)===FALSE){$s=substr_replace($s,$ch,$match[0][1]-1,3);}}return$s;}function
getRelativeUri(){trigger_error(__METHOD__.'() is deprecated; use '.__CLASS__.'::getRelativeUrl() instead.',E_USER_WARNING);return$this->getRelativeUrl();}function
getAbsoluteUri(){trigger_error(__METHOD__.'() is deprecated; use '.__CLASS__.'::getAbsoluteUrl() instead.',E_USER_WARNING);return$this->getAbsoluteUrl();}function
getHostUri(){trigger_error(__METHOD__.'() is deprecated; use '.__CLASS__.'::getHostUrl() instead.',E_USER_WARNING);return$this->getHostUrl();}function
getBaseUri(){trigger_error(__METHOD__.'() is deprecated; use '.__CLASS__.'::getBaseUrl() instead.',E_USER_WARNING);return$this->getBaseUrl();}}class
NUrlScript
extends
NUrl{private$scriptPath='/';function
setScriptPath($value){$this->updating();$this->scriptPath=(string)$value;return$this;}function
getScriptPath(){return$this->scriptPath;}function
getBasePath(){$pos=strrpos($this->scriptPath,'/');return$pos===FALSE?'':substr($this->path,0,$pos+1);}function
getPathInfo(){return(string)substr($this->path,strlen($this->scriptPath));}}class
NUserStorage
extends
NObject
implements
IUserStorage{private$namespace='';private$sessionHandler;private$sessionSection;function
__construct(NSession$sessionHandler){$this->sessionHandler=$sessionHandler;}function
setAuthenticated($state){$section=$this->getSessionSection(TRUE);$section->authenticated=(bool)$state;$this->sessionHandler->regenerateId();if($state){$section->reason=NULL;$section->authTime=time();}else{$section->reason=self::MANUAL;$section->authTime=NULL;}return$this;}function
isAuthenticated(){$session=$this->getSessionSection(FALSE);return$session&&$session->authenticated;}function
setIdentity(IIdentity$identity=NULL){$this->getSessionSection(TRUE)->identity=$identity;return$this;}function
getIdentity(){$session=$this->getSessionSection(FALSE);return$session?$session->identity:NULL;}function
setNamespace($namespace){if($this->namespace!==$namespace){$this->namespace=(string)$namespace;$this->sessionSection=NULL;}return$this;}function
getNamespace(){return$this->namespace;}function
setExpiration($time,$flags=0){$section=$this->getSessionSection(TRUE);if($time){$time=NDateTime53::from($time)->format('U');$section->expireTime=$time;$section->expireDelta=$time-time();}else{unset($section->expireTime,$section->expireDelta);}$section->expireIdentity=(bool)($flags&self::CLEAR_IDENTITY);$section->expireBrowser=(bool)($flags&self::BROWSER_CLOSED);$section->browserCheck=TRUE;$section->setExpiration(0,'browserCheck');$section->setExpiration($time,'foo');return$this;}function
getLogoutReason(){$session=$this->getSessionSection(FALSE);return$session?$session->reason:NULL;}protected
function
getSessionSection($need){if($this->sessionSection!==NULL){return$this->sessionSection;}if(!$need&&!$this->sessionHandler->exists()){return
NULL;}$this->sessionSection=$section=$this->sessionHandler->getSection('Nette.Http.UserStorage/'.$this->namespace);if(!$section->identity
instanceof
IIdentity||!is_bool($section->authenticated)){$section->remove();}if($section->authenticated&&$section->expireBrowser&&!$section->browserCheck){$section->reason=self::BROWSER_CLOSED;$section->authenticated=FALSE;if($section->expireIdentity){unset($section->identity);}}if($section->authenticated&&$section->expireDelta>0){if($section->expireTime<time()){$section->reason=self::INACTIVITY;$section->authenticated=FALSE;if($section->expireIdentity){unset($section->identity);}}$section->expireTime=time()+$section->expireDelta;}if(!$section->authenticated){unset($section->expireTime,$section->expireDelta,$section->expireIdentity,$section->expireBrowser,$section->browserCheck,$section->authTime);}return$this->sessionSection;}}class
NSmartCachingIterator
extends
CachingIterator
implements
Countable{private$counter=0;function
__construct($iterator){if(is_array($iterator)||$iterator
instanceof
stdClass){$iterator=new
ArrayIterator($iterator);}elseif($iterator
instanceof
Traversable){if($iterator
instanceof
IteratorAggregate){$iterator=$iterator->getIterator();}elseif(!$iterator
instanceof
Iterator){$iterator=new
IteratorIterator($iterator);}}else{throw
new
InvalidArgumentException(sprintf('Invalid argument passed to foreach resp. %s; array or Traversable expected, %s given.',__CLASS__,is_object($iterator)?get_class($iterator):gettype($iterator)));}parent::__construct($iterator,0);}function
isFirst($width=NULL){return$this->counter===1||($width&&$this->counter!==0&&(($this->counter-1)%$width)===0);}function
isLast($width=NULL){return!$this->hasNext()||($width&&($this->counter
%$width)===0);}function
isEmpty(){return$this->counter===0;}function
isOdd(){return$this->counter
%
2===1;}function
isEven(){return$this->counter
%
2===0;}function
getCounter(){return$this->counter;}function
count(){$inner=$this->getInnerIterator();if($inner
instanceof
Countable){return$inner->count();}else{throw
new
NotSupportedException('Iterator is not countable.');}}function
next(){parent::next();if(parent::valid()){$this->counter++;}}function
rewind(){parent::rewind();$this->counter=parent::valid()?1:0;}function
getNextKey(){return$this->getInnerIterator()->key();}function
getNextValue(){return$this->getInnerIterator()->current();}function
__call($name,$args){return
NObjectMixin::call($this,$name,$args);}function&__get($name){return
NObjectMixin::get($this,$name);}function
__set($name,$value){NObjectMixin::set($this,$name,$value);}function
__isset($name){return
NObjectMixin::has($this,$name);}function
__unset($name){NObjectMixin::remove($this,$name);}}class
NNCallbackFilterIterator
extends
FilterIterator{private$callback;function
__construct(Iterator$iterator,$callback){parent::__construct($iterator);$this->callback=new
NCallback($callback);}function
accept(){return$this->callback->invoke($this);}}class
NInstanceFilterIterator
extends
FilterIterator
implements
Countable{private$type;function
__construct(Iterator$iterator,$type){$this->type=$type;parent::__construct($iterator);}function
accept(){return$this->current()instanceof$this->type;}function
count(){return
iterator_count($this);}}class
NMapIterator
extends
IteratorIterator{private$callback;function
__construct(Traversable$iterator,$callback){parent::__construct($iterator);$this->callback=new
NCallback($callback);}function
current(){return$this->callback->invoke(parent::current(),parent::key());}}class
NNRecursiveCallbackFilterIterator
extends
FilterIterator
implements
RecursiveIterator{private$callback;private$childrenCallback;function
__construct(RecursiveIterator$iterator,$callback,$childrenCallback=NULL){parent::__construct($iterator);$this->callback=$callback===NULL?NULL:new
NCallback($callback);$this->childrenCallback=$childrenCallback===NULL?NULL:new
NCallback($childrenCallback);}function
accept(){return$this->callback===NULL||$this->callback->invoke($this);}function
hasChildren(){return$this->getInnerIterator()->hasChildren()&&($this->childrenCallback===NULL||$this->childrenCallback->invoke($this));}function
getChildren(){return
new
self($this->getInnerIterator()->getChildren(),$this->callback,$this->childrenCallback);}}class
NGenericRecursiveIterator
extends
IteratorIterator
implements
RecursiveIterator,Countable{function
hasChildren(){$obj=$this->current();return($obj
instanceof
IteratorAggregate&&$obj->getIterator()instanceof
RecursiveIterator)||$obj
instanceof
RecursiveIterator;}function
getChildren(){$obj=$this->current();return$obj
instanceof
IteratorAggregate?$obj->getIterator():$obj;}function
count(){return
iterator_count($this);}}class
NLatteCompiler
extends
NObject{public$defaultContentType=self::CONTENT_XHTML;private$tokens;private$output;private$position;private$macros;private$macroHandlers;private$htmlNodes=array();private$macroNodes=array();private$attrCodes=array();private$contentType;private$context;private$templateId;const
CONTENT_HTML='html',CONTENT_XHTML='xhtml',CONTENT_XML='xml',CONTENT_JS='js',CONTENT_CSS='css',CONTENT_ICAL='ical',CONTENT_TEXT='text';const
CONTEXT_COMMENT='comment',CONTEXT_SINGLE_QUOTED="'",CONTEXT_DOUBLE_QUOTED='"';function
__construct(){$this->macroHandlers=new
SplObjectStorage;}function
addMacro($name,IMacro$macro){$this->macros[$name][]=$macro;$this->macroHandlers->attach($macro);return$this;}function
compile(array$tokens){$this->templateId=NStrings::random();$this->tokens=$tokens;$output='';$this->output=&$output;$this->htmlNodes=$this->macroNodes=array();$this->setContentType($this->defaultContentType);foreach($this->macroHandlers
as$handler){$handler->initialize($this);}try{foreach($tokens
as$this->position=>$token){if($token->type===NLatteToken::TEXT){$this->output.=$token->text;}elseif($token->type===NLatteToken::MACRO_TAG){$isRightmost=!isset($tokens[$this->position+1])||substr($tokens[$this->position+1]->text,0,1)==="\n";$this->writeMacro($token->name,$token->value,$token->modifiers,$isRightmost);}elseif($token->type===NLatteToken::HTML_TAG_BEGIN){$this->processHtmlTagBegin($token);}elseif($token->type===NLatteToken::HTML_TAG_END){$this->processHtmlTagEnd($token);}elseif($token->type===NLatteToken::HTML_ATTRIBUTE){$this->processHtmlAttribute($token);}elseif($token->type===NLatteToken::COMMENT){$this->processComment($token);}}}catch(NCompileException$e){$e->sourceLine=$token->line;throw$e;}foreach($this->htmlNodes
as$htmlNode){if(!empty($htmlNode->macroAttrs)){throw
new
NCompileException("Missing end tag </$htmlNode->name> for macro-attribute ".NParser::N_PREFIX.implode(' and '.NParser::N_PREFIX,array_keys($htmlNode->macroAttrs)).".",0,$token->line);}}$prologs=$epilogs='';foreach($this->macroHandlers
as$handler){$res=$handler->finalize();$handlerName=get_class($handler);$prologs.=empty($res[0])?'':"<?php\n// prolog $handlerName\n$res[0]\n?>";$epilogs=(empty($res[1])?'':"<?php\n// epilog $handlerName\n$res[1]\n?>").$epilogs;}$output=($prologs?$prologs."<?php\n//\n// main template\n//\n?>\n":'').$output.$epilogs;if($this->macroNodes){throw
new
NCompileException("There are unclosed macros.",0,$token->line);}$output=$this->expandTokens($output);return$output;}function
setContentType($type){$this->contentType=$type;$this->context=NULL;return$this;}function
getContentType(){return$this->contentType;}function
setContext($context,$sub=NULL){$this->context=array($context,$sub);return$this;}function
getContext(){return$this->context;}function
getTemplateId(){return$this->templateId;}function
getLine(){return$this->tokens?$this->tokens[$this->position]->line:NULL;}function
expandTokens($s){return
strtr($s,$this->attrCodes);}private
function
processHtmlTagBegin(NLatteToken$token){if($token->closing){do{$htmlNode=array_pop($this->htmlNodes);if(!$htmlNode){$htmlNode=new
NHtmlNode($token->name);}if(strcasecmp($htmlNode->name,$token->name)===0){break;}if($htmlNode->macroAttrs){throw
new
NCompileException("Unexpected </$token->name>.",0,$token->line);}}while(TRUE);$this->htmlNodes[]=$htmlNode;$htmlNode->closing=TRUE;$htmlNode->offset=strlen($this->output);$this->setContext(NULL);}elseif($token->text==='<!--'){$this->setContext(self::CONTEXT_COMMENT);}else{$this->htmlNodes[]=$htmlNode=new
NHtmlNode($token->name);$htmlNode->isEmpty=in_array($this->contentType,array(self::CONTENT_HTML,self::CONTENT_XHTML))&&isset(NHtml::$emptyElements[strtolower($token->name)]);$htmlNode->offset=strlen($this->output);$this->setContext(NULL);}$this->output.=$token->text;}private
function
processHtmlTagEnd(NLatteToken$token){if($token->text==='-->'){$this->output.=$token->text;$this->setContext(NULL);return;}$htmlNode=end($this->htmlNodes);$isEmpty=!$htmlNode->closing&&(NStrings::contains($token->text,'/')||$htmlNode->isEmpty);if($isEmpty&&in_array($this->contentType,array(self::CONTENT_HTML,self::CONTENT_XHTML))){$token->text=preg_replace('#^.*>#',$this->contentType===self::CONTENT_XHTML?' />':'>',$token->text);}if(empty($htmlNode->macroAttrs)){$this->output.=$token->text;}else{$code=substr($this->output,$htmlNode->offset).$token->text;$this->output=substr($this->output,0,$htmlNode->offset);$this->writeAttrsMacro($code,$htmlNode);if($isEmpty){$htmlNode->closing=TRUE;$this->writeAttrsMacro('',$htmlNode);}}if($isEmpty){$htmlNode->closing=TRUE;}$lower=strtolower($htmlNode->name);if(!$htmlNode->closing&&($lower==='script'||$lower==='style')){$this->setContext($lower==='script'?self::CONTENT_JS:self::CONTENT_CSS);}else{$this->setContext(NULL);if($htmlNode->closing){array_pop($this->htmlNodes);}}}private
function
processHtmlAttribute(NLatteToken$token){$htmlNode=end($this->htmlNodes);if(NStrings::startsWith($token->name,NParser::N_PREFIX)){$name=substr($token->name,strlen(NParser::N_PREFIX));if(isset($htmlNode->macroAttrs[$name])){throw
new
NCompileException("Found multiple macro-attributes $token->name.",0,$token->line);}$htmlNode->macroAttrs[$name]=$token->value;return;}$htmlNode->attrs[$token->name]=TRUE;$this->output.=$token->text;$context=NULL;if($token->value&&in_array($this->contentType,array(self::CONTENT_HTML,self::CONTENT_XHTML))){$lower=strtolower($token->name);if(substr($lower,0,2)==='on'){$context=self::CONTENT_JS;}elseif($lower==='style'){$context=self::CONTENT_CSS;}}$this->setContext($token->value,$context);}private
function
processComment(NLatteToken$token){$isLeftmost=trim(substr($this->output,strrpos("\n$this->output","\n")))==='';if(!$isLeftmost){$this->output.=substr($token->text,strlen(rtrim($token->text,"\n")));}}function
writeMacro($name,$args=NULL,$modifiers=NULL,$isRightmost=FALSE,NHtmlNode$htmlNode=NULL,$prefix=NULL){if($name[0]==='/'){$node=end($this->macroNodes);if(!$node||("/$node->name"!==$name&&'/'!==$name)||$modifiers||($args&&$node->args&&!NStrings::startsWith("$node->args ","$args "))){$name.=$args?' ':'';throw
new
NCompileException("Unexpected macro {{$name}{$args}{$modifiers}}".($node?", expecting {/$node->name}".($args&&$node->args?" or eventually {/$node->name $node->args}":''):''));}array_pop($this->macroNodes);if(!$node->args){$node->setArgs($args);}$isLeftmost=$node->content?trim(substr($this->output,strrpos("\n$this->output","\n")))==='':FALSE;$node->closing=TRUE;$node->macro->nodeClosed($node);$this->output=&$node->saved[0];$this->writeCode($node->openingCode,$this->output,$node->saved[1]);$this->writeCode($node->closingCode,$node->content,$isRightmost,$isLeftmost);$this->output.=$node->content;}else{$node=$this->expandMacro($name,$args,$modifiers,$htmlNode,$prefix);if($node->isEmpty){$this->writeCode($node->openingCode,$this->output,$isRightmost);}else{$this->macroNodes[]=$node;$node->saved=array(&$this->output,$isRightmost);$this->output=&$node->content;}}return$node;}private
function
writeCode($code,&$output,$isRightmost,$isLeftmost=NULL){if($isRightmost){$leftOfs=strrpos("\n$output","\n");$isLeftmost=$isLeftmost===NULL?trim(substr($output,$leftOfs))==='':$isLeftmost;if($isLeftmost&&substr($code,0,11)!=='<?php echo '){$output=substr($output,0,$leftOfs);}elseif(substr($code,-2)==='?>'){$code.="\n";}}$output.=$code;}function
writeAttrsMacro($code,NHtmlNode$htmlNode){$attrs=$htmlNode->macroAttrs;$left=$right=array();foreach($this->macros
as$name=>$foo){$attrName=NMacroNode::PREFIX_INNER."-$name";if(isset($attrs[$attrName])){if($htmlNode->closing){$left[]=array("/$name",'',NMacroNode::PREFIX_INNER);}else{array_unshift($right,array($name,$attrs[$attrName],NMacroNode::PREFIX_INNER));}unset($attrs[$attrName]);}}foreach(array_reverse($this->macros)as$name=>$foo){$attrName=NMacroNode::PREFIX_TAG."-$name";if(isset($attrs[$attrName])){$left[]=array($name,$attrs[$attrName],NMacroNode::PREFIX_TAG);array_unshift($right,array("/$name",'',NMacroNode::PREFIX_TAG));unset($attrs[$attrName]);}}foreach($this->macros
as$name=>$foo){if(isset($attrs[$name])){if($htmlNode->closing){$right[]=array("/$name",'',NULL);}else{array_unshift($left,array($name,$attrs[$name],NULL));}unset($attrs[$name]);}}if($attrs){throw
new
NCompileException("Unknown macro-attribute ".NParser::N_PREFIX.implode(' and '.NParser::N_PREFIX,array_keys($attrs)));}if(!$htmlNode->closing){$htmlNode->attrCode=&$this->attrCodes[$uniq=' n:'.NStrings::random()];$code=substr_replace($code,$uniq,($tmp=strrpos($code,'/>'))?$tmp:strrpos($code,'>'),0);}foreach($left
as$item){$node=$this->writeMacro($item[0],$item[1],NULL,NULL,$htmlNode,$item[2]);if($node->closing||$node->isEmpty){$htmlNode->attrCode.=$node->attrCode;if($node->isEmpty){unset($htmlNode->macroAttrs[$node->name]);}}}$this->output.=$code;foreach($right
as$item){$node=$this->writeMacro($item[0],$item[1],NULL,NULL,$htmlNode);if($node->closing){$htmlNode->attrCode.=$node->attrCode;}}if($right&&substr($this->output,-2)==='?>'){$this->output.="\n";}}function
expandMacro($name,$args,$modifiers=NULL,NHtmlNode$htmlNode=NULL,$prefix=NULL){if(empty($this->macros[$name])){$cdata=$this->htmlNodes&&in_array(strtolower(end($this->htmlNodes)->name),array('script','style'));throw
new
NCompileException("Unknown macro {{$name}}".($cdata?" (in JavaScript or CSS, try to put a space after bracket.)":''));}foreach(array_reverse($this->macros[$name])as$macro){$node=new
NMacroNode($macro,$name,$args,$modifiers,$this->macroNodes?end($this->macroNodes):NULL,$htmlNode,$prefix);if($macro->nodeOpened($node)!==FALSE){return$node;}}throw
new
NCompileException("Unhandled macro {{$name}}");}}class
NLatteFilter
extends
NObject{private$parser;private$compiler;function
__construct(){$this->parser=new
NParser;$this->compiler=new
NLatteCompiler;$this->compiler->defaultContentType=NLatteCompiler::CONTENT_XHTML;NCoreMacros::install($this->compiler);$this->compiler->addMacro('cache',new
NCacheMacro($this->compiler));NUIMacros::install($this->compiler);NFormMacros::install($this->compiler);}function
__invoke($s){return$this->compiler->compile($this->parser->parse($s));}function
getParser(){return$this->parser;}function
getCompiler(){return$this->compiler;}}class
NTemplateException
extends
InvalidStateException{public$sourceFile;public$sourceLine;function
__construct($message,$code=0,$sourceLine=0){$this->sourceLine=(int)$sourceLine;parent::__construct($message,$code);}function
setSourceFile($file){$this->sourceFile=(string)$file;$this->message=rtrim($this->message,'.')." in ".str_replace(dirname(dirname($file)),'...',$file).($this->sourceLine?":$this->sourceLine":'');}}class
NCompileException
extends
NTemplateException{}class
NHtmlNode
extends
NObject{public$name;public$isEmpty=FALSE;public$attrs=array();public$macroAttrs=array();public$closing=FALSE;public$attrCode;public$offset;function
__construct($name){$this->name=$name;}}class
NMacroNode
extends
NObject{const
PREFIX_INNER='inner',PREFIX_TAG='tag';public$macro;public$name;public$isEmpty=FALSE;public$args;public$modifiers;public$closing=FALSE;public$tokenizer;public$parentNode;public$openingCode;public$closingCode;public$attrCode;public$content;public$data;public$htmlNode;public$prefix;public$saved;function
__construct(IMacro$macro,$name,$args=NULL,$modifiers=NULL,NMacroNode$parentNode=NULL,NHtmlNode$htmlNode=NULL,$prefix=NULL){$this->macro=$macro;$this->name=(string)$name;$this->modifiers=(string)$modifiers;$this->parentNode=$parentNode;$this->htmlNode=$htmlNode;$this->prefix=$prefix;$this->tokenizer=new
NMacroTokenizer($this->args);$this->data=new
stdClass;$this->setArgs($args);}function
setArgs($args){$this->args=(string)$args;$this->tokenizer->tokenize($this->args);}}class
NCacheMacro
extends
NObject
implements
IMacro{private$used;function
initialize(){$this->used=FALSE;}function
finalize(){if($this->used){return
array('NCacheMacro::initRuntime($template, $_g);');}}function
nodeOpened(NMacroNode$node){$this->used=TRUE;$node->isEmpty=FALSE;$node->openingCode=NPhpWriter::using($node)->write('<?php if (NCacheMacro::createCache($netteCacheStorage, %var, $_g->caches, %node.array?)) { ?>',NStrings::random());}function
nodeClosed(NMacroNode$node){$node->closingCode='<?php $_l->tmp = array_pop($_g->caches); if (!$_l->tmp instanceof stdClass) $_l->tmp->end(); } ?>';}static
function
initRuntime(NFileTemplate$template,stdClass$global){if(!empty($global->caches)){end($global->caches)->dependencies[NCache::FILES][]=$template->getFile();}}static
function
createCache(ICacheStorage$cacheStorage,$key,&$parents,array$args=NULL){if($args){if(array_key_exists('if',$args)&&!$args['if']){return$parents[]=new
stdClass;}$key=array_merge(array($key),array_intersect_key($args,range(0,count($args))));}if($parents){end($parents)->dependencies[NCache::ITEMS][]=$key;}$cache=new
NCache($cacheStorage,'Nette.Templating.Cache');if($helper=$cache->start($key)){if(isset($args['expire'])){$args['expiration']=$args['expire'];}$helper->dependencies=array(NCache::TAGS=>isset($args['tags'])?$args['tags']:NULL,NCache::EXPIRATION=>isset($args['expiration'])?$args['expiration']:'+ 7 days');$parents[]=$helper;}return$helper;}}class
NMacroSet
extends
NObject
implements
IMacro{private$compiler;private$macros;function
__construct(NLatteCompiler$compiler){$this->compiler=$compiler;}function
addMacro($name,$begin,$end=NULL,$attr=NULL){$this->macros[$name]=array($begin,$end,$attr);$this->compiler->addMacro($name,$this);return$this;}static
function
install(NLatteCompiler$compiler){return
new
self($compiler);}function
initialize(){}function
finalize(){}function
nodeOpened(NMacroNode$node){if($this->macros[$node->name][2]&&$node->htmlNode){$node->isEmpty=TRUE;$this->compiler->setContext(NLatteCompiler::CONTEXT_DOUBLE_QUOTED);$res=$this->compile($node,$this->macros[$node->name][2]);$this->compiler->setContext(NULL);if(!$node->attrCode){$node->attrCode="<?php $res ?>";}}else{$node->isEmpty=!isset($this->macros[$node->name][1]);$res=$this->compile($node,$this->macros[$node->name][0]);if(!$node->openingCode){$node->openingCode="<?php $res ?>";}}return$res!==FALSE;}function
nodeClosed(NMacroNode$node){$res=$this->compile($node,$this->macros[$node->name][1]);if(!$node->closingCode){$node->closingCode="<?php $res ?>";}}private
function
compile(NMacroNode$node,$def){$node->tokenizer->reset();$writer=NPhpWriter::using($node,$this->compiler);if(is_string($def)&&substr($def,0,1)!=="\0"){return$writer->write($def);}else{return
NCallback::create($def)->invoke($node,$writer);}}function
getCompiler(){return$this->compiler;}}class
NCoreMacros
extends
NMacroSet{static
function
install(NLatteCompiler$compiler){$me=new
self($compiler);$me->addMacro('if',array($me,'macroIf'),array($me,'macroEndIf'));$me->addMacro('elseif','} elseif (%node.args) {');$me->addMacro('else',array($me,'macroElse'));$me->addMacro('ifset','if (isset(%node.args)) {','}');$me->addMacro('elseifset','} elseif (isset(%node.args)) {');$me->addMacro('foreach','',array($me,'macroEndForeach'));$me->addMacro('for','for (%node.args) {','}');$me->addMacro('while','while (%node.args) {','}');$me->addMacro('continueIf','if (%node.args) continue');$me->addMacro('breakIf','if (%node.args) break');$me->addMacro('first','if ($iterator->isFirst(%node.args)) {','}');$me->addMacro('last','if ($iterator->isLast(%node.args)) {','}');$me->addMacro('sep','if (!$iterator->isLast(%node.args)) {','}');$me->addMacro('var',array($me,'macroVar'));$me->addMacro('assign',array($me,'macroVar'));$me->addMacro('default',array($me,'macroVar'));$me->addMacro('dump',array($me,'macroDump'));$me->addMacro('debugbreak',array($me,'macroDebugbreak'));$me->addMacro('l','?>{<?php');$me->addMacro('r','?>}<?php');$me->addMacro('_',array($me,'macroTranslate'),array($me,'macroTranslate'));$me->addMacro('=',array($me,'macroExpr'));$me->addMacro('?',array($me,'macroExpr'));$me->addMacro('capture',array($me,'macroCapture'),array($me,'macroCaptureEnd'));$me->addMacro('include',array($me,'macroInclude'));$me->addMacro('use',array($me,'macroUse'));$me->addMacro('class',NULL,NULL,array($me,'macroClass'));$me->addMacro('attr',array($me,'macroOldAttr'),'',array($me,'macroAttr'));$me->addMacro('href',NULL);}function
finalize(){return
array('list($_l, $_g) = NCoreMacros::initRuntime($template, '.var_export($this->getCompiler()->getTemplateId(),TRUE).')');}function
macroIf(NMacroNode$node,NPhpWriter$writer){if($node->data->capture=($node->args==='')){return'ob_start()';}if($node->prefix===NMacroNode::PREFIX_TAG){return$writer->write($node->htmlNode->closing?'if (array_pop($_l->ifs)) {':'if ($_l->ifs[] = (%node.args)) {');}return$writer->write('if (%node.args) {');}function
macroEndIf(NMacroNode$node,NPhpWriter$writer){if($node->data->capture){if($node->args===''){throw
new
NCompileException('Missing condition in {if} macro.');}return$writer->write('if (%node.args) '.(isset($node->data->else)?'{ ob_end_clean(); ob_end_flush(); }':'ob_end_flush();').' else '.(isset($node->data->else)?'{ $_else = ob_get_contents(); ob_end_clean(); ob_end_clean(); echo $_else; }':'ob_end_clean();'));}return'}';}function
macroElse(NMacroNode$node,NPhpWriter$writer){$ifNode=$node->parentNode;if($ifNode&&$ifNode->name==='if'&&$ifNode->data->capture){if(isset($ifNode->data->else)){throw
new
NCompileException('Macro {if} supports only one {else}.');}$ifNode->data->else=TRUE;return'ob_start()';}return'} else {';}function
macroTranslate(NMacroNode$node,NPhpWriter$writer){if($node->closing){return$writer->write('echo %modify($template->translate(ob_get_clean()))');}elseif($node->isEmpty=($node->args!=='')){return$writer->write('echo %modify($template->translate(%node.args))');}else{return'ob_start()';}}function
macroInclude(NMacroNode$node,NPhpWriter$writer){$code=$writer->write('NCoreMacros::includeTemplate(%node.word, %node.array? + $template->getParameters(), $_l->templates[%var])',$this->getCompiler()->getTemplateId());if($node->modifiers){return$writer->write('echo %modify(%raw->__toString(TRUE))',$code);}else{return$code.'->render()';}}function
macroUse(NMacroNode$node,NPhpWriter$writer){NCallback::create($node->tokenizer->fetchWord(),'install')->invoke($this->getCompiler())->initialize();}function
macroCapture(NMacroNode$node,NPhpWriter$writer){$variable=$node->tokenizer->fetchWord();if(substr($variable,0,1)!=='$'){throw
new
NCompileException("Invalid capture block variable '$variable'");}$node->data->variable=$variable;return'ob_start()';}function
macroCaptureEnd(NMacroNode$node,NPhpWriter$writer){return$node->data->variable.$writer->write(' = %modify(ob_get_clean())');}function
macroEndForeach(NMacroNode$node,NPhpWriter$writer){if($node->modifiers!=='|noiterator'&&preg_match('#\W(\$iterator|include|require|get_defined_vars)\W#',$this->getCompiler()->expandTokens($node->content))){$node->openingCode='<?php $iterations = 0; foreach ($iterator = $_l->its[] = new NSmartCachingIterator('.preg_replace('#(.*)\s+as\s+#i','$1) as ',$writer->formatArgs(),1).') { ?>';$node->closingCode='<?php $iterations++; } array_pop($_l->its); $iterator = end($_l->its) ?>';}else{$node->openingCode='<?php $iterations = 0; foreach ('.$writer->formatArgs().') { ?>';$node->closingCode='<?php $iterations++; } ?>';}}function
macroClass(NMacroNode$node,NPhpWriter$writer){return$writer->write('if ($_l->tmp = array_filter(%node.array)) echo \' class="\' . %escape(implode(" ", array_unique($_l->tmp))) . \'"\'');}function
macroAttr(NMacroNode$node,NPhpWriter$writer){return$writer->write('echo NHtml::el(NULL, %node.array)->attributes()');}function
macroOldAttr(NMacroNode$node){return
NStrings::replace($node->args.' ','#\)\s+#',')->');}function
macroDump(NMacroNode$node,NPhpWriter$writer){$args=$writer->formatArgs();return'NDebugger::barDump('.($node->args?"array(".$writer->write('%var',$args)." => $args)":'get_defined_vars()').', "Template " . str_replace(dirname(dirname($template->getFile())), "\xE2\x80\xA6", $template->getFile()))';}function
macroDebugbreak(NMacroNode$node,NPhpWriter$writer){return$writer->write(($node->args==NULL?'':'if (!(%node.args)); else').'if (function_exists("debugbreak")) debugbreak(); elseif (function_exists("xdebug_break")) xdebug_break()');}function
macroVar(NMacroNode$node,NPhpWriter$writer){$out='';$var=TRUE;$tokenizer=$writer->preprocess();while($token=$tokenizer->fetchToken()){if($var&&($token['type']===NMacroTokenizer::T_SYMBOL||$token['type']===NMacroTokenizer::T_VARIABLE)){if($node->name==='default'){$out.="'".ltrim($token['value'],"$")."'";}else{$out.='$'.ltrim($token['value'],"$");}$var=NULL;}elseif(($token['value']==='='||$token['value']==='=>')&&$token['depth']===0){$out.=$node->name==='default'?'=>':'=';$var=FALSE;}elseif($token['value']===','&&$token['depth']===0){$out.=$node->name==='default'?',':';';$var=TRUE;}elseif($var===NULL&&$node->name==='default'&&$token['type']!==NMacroTokenizer::T_WHITESPACE){throw
new
NCompileException("Unexpected '$token[value]' in {default $node->args}");}else{$out.=$writer->canQuote($tokenizer)?"'$token[value]'":$token['value'];}}return$node->name==='default'?"extract(array($out), EXTR_SKIP)":$out;}function
macroExpr(NMacroNode$node,NPhpWriter$writer){return$writer->write(($node->name==='?'?'':'echo ').'%modify(%node.args)');}static
function
includeTemplate($destination,array$params,ITemplate$template){if($destination
instanceof
ITemplate){$tpl=$destination;}elseif($destination==NULL){throw
new
InvalidArgumentException("Template file name was not specified.");}elseif($template
instanceof
IFileTemplate){if(substr($destination,0,1)!=='/'&&substr($destination,1,1)!==':'){$destination=dirname($template->getFile()).'/'.$destination;}$tpl=clone$template;$tpl->setFile($destination);}else{throw
new
NotSupportedException('Macro {include "filename"} is supported only with IFileTemplate.');}$tpl->setParameters($params);return$tpl;}static
function
initRuntime(ITemplate$template,$templateId){if(isset($template->_l)){$local=$template->_l;unset($template->_l);}else{$local=new
stdClass;}$local->templates[$templateId]=$template;if(!isset($template->_g)){$template->_g=new
stdClass;}return
array($local,$template->_g);}}class
NFormMacros
extends
NMacroSet{static
function
install(NLatteCompiler$compiler){$me=new
self($compiler);$me->addMacro('form','NFormMacros::renderFormBegin($form = $_form = (is_object(%node.word) ? %node.word : $_control[%node.word]), %node.array)','NFormMacros::renderFormEnd($_form)');$me->addMacro('label',array($me,'macroLabel'),'if ($_label) echo $_label->endTag()');$me->addMacro('input','$_input = (is_object(%node.word) ? %node.word : $_form[%node.word]); echo $_input->getControl()->addAttributes(%node.array)',NULL,array($me,'macroAttrName'));$me->addMacro('formContainer','$_formStack[] = $_form; $formContainer = $_form = (is_object(%node.word) ? %node.word : $_form[%node.word])','$_form = array_pop($_formStack)');$me->addMacro('name',NULL,NULL,array($me,'macroAttrName'));}function
macroLabel(NMacroNode$node,NPhpWriter$writer){$cmd='$_input = is_object(%node.word) ? %node.word : $_form[%node.word]; if ($_label = $_input->getLabel()) echo $_label->addAttributes(%node.array)';if($node->isEmpty=(substr($node->args,-1)==='/')){$node->setArgs(substr($node->args,0,-1));return$writer->write($cmd);}else{return$writer->write($cmd.'->startTag()');}}function
macroAttrName(NMacroNode$node,NPhpWriter$writer){if($node->htmlNode->attrs){$reset=array_fill_keys(array_keys($node->htmlNode->attrs),NULL);return$writer->write('$_input = (is_object(%node.word) ? %node.word : $_form[%node.word]); echo $_input->getControl()->addAttributes(%var)->attributes()',$reset);}return$writer->write('$_input = (is_object(%node.word) ? %node.word : $_form[%node.word]); echo $_input->getControl()->attributes()');}static
function
renderFormBegin(NForm$form,array$attrs){$el=$form->getElementPrototype();$el->action=$action=(string)$el->action;$el=clone$el;if(strcasecmp($form->getMethod(),'get')===0){list($el->action)=explode('?',$action,2);if(($i=strpos($action,'#'))!==FALSE){$el->action.=substr($action,$i);}}echo$el->addAttributes($attrs)->startTag();}static
function
renderFormEnd(NForm$form){$s='';if(strcasecmp($form->getMethod(),'get')===0){$url=explode('?',$form->getElementPrototype()->action,2);if(isset($url[1])){list($url[1])=explode('#',$url[1],2);foreach(preg_split('#[;&]#',$url[1])as$param){$parts=explode('=',$param,2);$name=urldecode($parts[0]);if(!isset($form[$name])){$s.=NHtml::el('input',array('type'=>'hidden','name'=>$name,'value'=>urldecode($parts[1])));}}}}foreach($form->getComponents(TRUE,'NHiddenField')as$control){if(!$control->getOption('rendered')){$s.=$control->getControl();}}if(iterator_count($form->getComponents(TRUE,'NTextInput'))<2){$s.='<!--[if IE]><input type=IEbug disabled style="display:none"><![endif]-->';}echo($s?"<div>$s</div>\n":'').$form->getElementPrototype()->endTag()."\n";}}class
NUIMacros
extends
NMacroSet{private$namedBlocks=array();private$extends;static
function
install(NLatteCompiler$compiler){$me=new
self($compiler);$me->addMacro('include',array($me,'macroInclude'));$me->addMacro('includeblock',array($me,'macroIncludeBlock'));$me->addMacro('extends',array($me,'macroExtends'));$me->addMacro('layout',array($me,'macroExtends'));$me->addMacro('block',array($me,'macroBlock'),array($me,'macroBlockEnd'));$me->addMacro('define',array($me,'macroBlock'),array($me,'macroBlockEnd'));$me->addMacro('snippet',array($me,'macroBlock'),array($me,'macroBlockEnd'));$me->addMacro('ifset',array($me,'macroIfset'),'}');$me->addMacro('widget',array($me,'macroControl'));$me->addMacro('control',array($me,'macroControl'));$me->addMacro('href',NULL,NULL,create_function('NMacroNode $node, NPhpWriter $writer','extract($GLOBALS[0]['.array_push($GLOBALS[0],array('me'=>$me)).'-1], EXTR_REFS);
			return \' ?> href="<?php \' . $me->macroLink($node, $writer) . \' ?>"<?php \';
		'));$me->addMacro('plink',array($me,'macroLink'));$me->addMacro('link',array($me,'macroLink'));$me->addMacro('ifCurrent',array($me,'macroIfCurrent'),'}');$me->addMacro('contentType',array($me,'macroContentType'));$me->addMacro('status',array($me,'macroStatus'));}function
initialize(){$this->namedBlocks=array();$this->extends=NULL;}function
finalize(){try{$this->getCompiler()->writeMacro('/block');}catch(NCompileException$e){}$epilog=$prolog=array();if($this->namedBlocks){foreach($this->namedBlocks
as$name=>$code){$func='_lb'.substr(md5($this->getCompiler()->getTemplateId().$name),0,10).'_'.preg_replace('#[^a-z0-9_]#i','_',$name);$snippet=$name[0]==='_';$prolog[]="//\n// block $name\n//\n"."if (!function_exists(\$_l->blocks[".var_export($name,TRUE)."][] = '$func')) { "."function $func(\$_l, \$_args) { foreach (\$_args as \$__k => \$__v) \$\$__k = \$__v".($snippet?'; $_control->validateControl('.var_export(substr($name,1),TRUE).')':'')."\n?>$code<?php\n}}";}$prolog[]="//\n// end of blocks\n//";}if($this->namedBlocks||$this->extends){$prolog[]="// template extending and snippets support";$prolog[]='$_l->extends = '.($this->extends?$this->extends:'empty($template->_extended) && isset($_control) && $_control instanceof NPresenter ? $_control->findLayoutTemplateFile() : NULL').'; $template->_extended = $_extended = TRUE;';$prolog[]='
if ($_l->extends) {
	'.($this->namedBlocks?'ob_start();':'return NCoreMacros::includeTemplate($_l->extends, get_defined_vars(), $template)->render();').'

} elseif (!empty($_control->snippetMode)) {
	return NUIMacros::renderSnippets($_control, $_l, get_defined_vars());
}';}else{$prolog[]='
// snippets support
if (!empty($_control->snippetMode)) {
	return NUIMacros::renderSnippets($_control, $_l, get_defined_vars());
}';}return
array(implode("\n\n",$prolog),implode("\n",$epilog));}function
macroInclude(NMacroNode$node,NPhpWriter$writer){$destination=$node->tokenizer->fetchWord();if(substr($destination,0,1)!=='#'){return
FALSE;}$destination=ltrim($destination,'#');$parent=$destination==='parent';if($destination==='parent'||$destination==='this'){for($item=$node->parentNode;$item&&$item->name!=='block'&&!isset($item->data->name);$item=$item->parentNode);if(!$item){throw
new
NCompileException("Cannot include $destination block outside of any block.");}$destination=$item->data->name;}$name=NStrings::contains($destination,'$')?$destination:var_export($destination,TRUE);if(isset($this->namedBlocks[$destination])&&!$parent){$cmd="call_user_func(reset(\$_l->blocks[$name]), \$_l, %node.array? + get_defined_vars())";}else{$cmd='NUIMacros::callBlock'.($parent?'Parent':'')."(\$_l, $name, %node.array? + ".($parent?'get_defined_vars':'$template->getParameters').'())';}if($node->modifiers){return$writer->write("ob_start(); $cmd; echo %modify(ob_get_clean())");}else{return$writer->write($cmd);}}function
macroIncludeBlock(NMacroNode$node,NPhpWriter$writer){return$writer->write('NCoreMacros::includeTemplate(%node.word, %node.array? + get_defined_vars(), $_l->templates[%var])->render()',$this->getCompiler()->getTemplateId());}function
macroExtends(NMacroNode$node,NPhpWriter$writer){if(!$node->args){throw
new
NCompileException('Missing destination in {'.$node->name.'}');}if(!empty($node->parentNode)){throw
new
NCompileException('{'.$node->name.'} must be placed outside any macro.');}if($this->extends!==NULL){throw
new
NCompileException('Multiple {'.$node->name.'} declarations are not allowed.');}if($node->args==='none'){$this->extends='FALSE';}elseif($node->args==='auto'){$this->extends='$_presenter->findLayoutTemplateFile()';}else{$this->extends=$writer->write('%node.word%node.args');}return;}function
macroBlock(NMacroNode$node,NPhpWriter$writer){$name=$node->tokenizer->fetchWord();if($node->name==='block'&&$name===FALSE){return$node->modifiers===''?'':'ob_start()';}$node->data->name=$name=ltrim($name,'#');if($name==NULL){if($node->name!=='snippet'){throw
new
NCompileException("Missing block name.");}}elseif(NStrings::contains($name,'$')){if($node->name==='snippet'){for($parent=$node->parentNode;$parent&&$parent->name!=='snippet';$parent=$parent->parentNode);if(!$parent){throw
new
NCompileException("Dynamic snippets are allowed only inside static snippet.");}$parent->data->dynamic=TRUE;$node->data->leave=TRUE;$node->closingCode="<?php \$_dynSnippets[\$_dynSnippetId] = ob_get_flush() ?>";if($node->htmlNode){$node->attrCode=$writer->write("<?php echo ' id=\"' . (\$_dynSnippetId = \$_control->getSnippetId({$writer->formatWord($name)})) . '\"' ?>");return$writer->write('ob_start()');}$tag=trim($node->tokenizer->fetchWord(),'<>');$tag=$tag?$tag:'div';$node->closingCode.="\n</$tag>";return$writer->write("?>\n<$tag id=\"<?php echo \$_dynSnippetId = \$_control->getSnippetId({$writer->formatWord($name)}) ?>\"><?php ob_start()");}else{$node->data->leave=TRUE;$fname=$writer->formatWord($name);$node->closingCode="<?php }} ".($node->name==='define'?'':"call_user_func(reset(\$_l->blocks[$fname]), \$_l, get_defined_vars())")." ?>";$func='_lb'.substr(md5($this->getCompiler()->getTemplateId().$name),0,10).'_'.preg_replace('#[^a-z0-9_]#i','_',$name);return"\n\n//\n// block $name\n//\n"."if (!function_exists(\$_l->blocks[$fname]['{$this->getCompiler()->getTemplateId()}'] = '$func')) { "."function $func(\$_l, \$_args) { foreach (\$_args as \$__k => \$__v) \$\$__k = \$__v";}}if($node->name==='snippet'){$node->data->name=$name='_'.$name;}if(isset($this->namedBlocks[$name])){throw
new
NCompileException("Cannot redeclare static block '$name'");}$prolog=$this->namedBlocks?'':"if (\$_l->extends) { ob_end_clean(); return NCoreMacros::includeTemplate(\$_l->extends, get_defined_vars(), \$template)->render(); }\n";$this->namedBlocks[$name]=TRUE;$include='call_user_func(reset($_l->blocks[%var]), $_l, '.($node->name==='snippet'?'$template->getParameters()':'get_defined_vars()').')';if($node->modifiers){$include="ob_start(); $include; echo %modify(ob_get_clean())";}if($node->name==='snippet'){if($node->htmlNode){$node->attrCode=$writer->write('<?php echo \' id="\' . $_control->getSnippetId(%var) . \'"\' ?>',(string)substr($name,1));return$writer->write($prolog.$include,$name);}$tag=trim($node->tokenizer->fetchWord(),'<>');$tag=$tag?$tag:'div';return$writer->write("$prolog ?>\n<$tag id=\"<?php echo \$_control->getSnippetId(%var) ?>\"><?php $include ?>\n</$tag><?php ",(string)substr($name,1),$name);}elseif($node->name==='define'){return$prolog;}else{return$writer->write($prolog.$include,$name);}}function
macroBlockEnd(NMacroNode$node,NPhpWriter$writer){if(isset($node->data->name)){if($node->name==='snippet'&&$node->htmlNode&&!$node->prefix&&preg_match('#^.*? n:\w+>\n?#s',$node->content,$m1)&&preg_match('#[ \t]*<[^<]+\z#s',$node->content,$m2)){$node->openingCode=$m1[0].$node->openingCode;$node->content=substr($node->content,strlen($m1[0]),-strlen($m2[0]));$node->closingCode.=$m2[0];}if(empty($node->data->leave)){if(!empty($node->data->dynamic)){$node->content.='<?php if (isset($_dynSnippets)) return $_dynSnippets; ?>';}$this->namedBlocks[$node->data->name]=$tmp=rtrim(ltrim($node->content,"\n")," \t");$node->content=substr_replace($node->content,$node->openingCode."\n",strspn($node->content,"\n"),strlen($tmp));$node->openingCode="<?php ?>";}}elseif($node->modifiers){return$writer->write('echo %modify(ob_get_clean())');}}function
macroIfset(NMacroNode$node,NPhpWriter$writer){if(!NStrings::contains($node->args,'#')){return
FALSE;}$list=array();while(($name=$node->tokenizer->fetchWord())!==FALSE){$list[]=$name[0]==='#'?'$_l->blocks["'.substr($name,1).'"]':$name;}return'if (isset('.implode(', ',$list).')) {';}function
macroControl(NMacroNode$node,NPhpWriter$writer){$pair=$node->tokenizer->fetchWord();if($pair===FALSE){throw
new
NCompileException("Missing control name in {control}");}$pair=explode(':',$pair,2);$name=$writer->formatWord($pair[0]);$method=isset($pair[1])?ucfirst($pair[1]):'';$method=NStrings::match($method,'#^\w*\z#')?"render$method":"{\"render$method\"}";$param=$writer->formatArray();if(!NStrings::contains($node->args,'=>')){$param=substr($param,6,-1);}return($name[0]==='$'?"if (is_object($name)) \$_ctrl = $name; else ":'').'$_ctrl = $_control->getComponent('.$name.'); '.'if ($_ctrl instanceof IRenderable) $_ctrl->validateControl(); '."\$_ctrl->$method($param)";}function
macroLink(NMacroNode$node,NPhpWriter$writer){return$writer->write('echo %escape(%modify('.($node->name==='plink'?'$_presenter':'$_control').'->link(%node.word, %node.array?)))');}function
macroIfCurrent(NMacroNode$node,NPhpWriter$writer){return$writer->write(($node->args?'try { $_presenter->link(%node.word, %node.array?); } catch (NInvalidLinkException $e) {}':'').'; if ($_presenter->getLastCreatedRequestFlag("current")) {');}function
macroContentType(NMacroNode$node,NPhpWriter$writer){if(NStrings::contains($node->args,'xhtml')){$this->getCompiler()->setContentType(NLatteCompiler::CONTENT_XHTML);}elseif(NStrings::contains($node->args,'html')){$this->getCompiler()->setContentType(NLatteCompiler::CONTENT_HTML);}elseif(NStrings::contains($node->args,'xml')){$this->getCompiler()->setContentType(NLatteCompiler::CONTENT_XML);}elseif(NStrings::contains($node->args,'javascript')){$this->getCompiler()->setContentType(NLatteCompiler::CONTENT_JS);}elseif(NStrings::contains($node->args,'css')){$this->getCompiler()->setContentType(NLatteCompiler::CONTENT_CSS);}elseif(NStrings::contains($node->args,'calendar')){$this->getCompiler()->setContentType(NLatteCompiler::CONTENT_ICAL);}else{$this->getCompiler()->setContentType(NLatteCompiler::CONTENT_TEXT);}if(NStrings::contains($node->args,'/')){return$writer->write('$netteHttpResponse->setHeader("Content-Type", %var)',$node->args);}}function
macroStatus(NMacroNode$node,NPhpWriter$writer){return$writer->write((substr($node->args,-1)==='?'?'if (!$netteHttpResponse->isSent()) ':'').'$netteHttpResponse->setCode(%var)',(int)$node->args);}static
function
callBlock(stdClass$context,$name,array$params){if(empty($context->blocks[$name])){throw
new
InvalidStateException("Cannot include undefined block '$name'.");}$block=reset($context->blocks[$name]);$block($context,$params);}static
function
callBlockParent(stdClass$context,$name,array$params){if(empty($context->blocks[$name])||($block=next($context->blocks[$name]))===FALSE){throw
new
InvalidStateException("Cannot include undefined parent block '$name'.");}$block($context,$params);prev($context->blocks[$name]);}static
function
renderSnippets(NControl$control,stdClass$local,array$params){$control->snippetMode=FALSE;$payload=$control->getPresenter()->getPayload();if(isset($local->blocks)){foreach($local->blocks
as$name=>$function){if($name[0]!=='_'||!$control->isControlInvalid(substr($name,1))){continue;}ob_start();$function=reset($function);$snippets=$function($local,$params);$payload->snippets[$id=$control->getSnippetId(substr($name,1))]=ob_get_clean();if($snippets){$payload->snippets+=$snippets;unset($payload->snippets[$id]);}}}$control->snippetMode=TRUE;if($control
instanceof
IRenderable){$queue=array($control);do{foreach(array_shift($queue)->getComponents()as$child){if($child
instanceof
IRenderable){if($child->isControlInvalid()){$child->snippetMode=TRUE;$child->render();$child->snippetMode=FALSE;}}elseif($child
instanceof
IComponentContainer){$queue[]=$child;}}}while($queue);}}}class
NTokenizer
extends
NObject{public$tokens;public$position=0;public$ignored=array();private$input;private$re;private$types;public$current;function
__construct(array$patterns,$flags=''){$this->re='~('.implode(')|(',$patterns).')~A'.$flags;$keys=array_keys($patterns);$this->types=$keys===range(0,count($patterns)-1)?FALSE:$keys;}function
tokenize($input){$this->input=$input;if($this->types){$this->tokens=NStrings::matchAll($input,$this->re);$len=0;$count=count($this->types);$line=1;foreach($this->tokens
as&$match){$type=NULL;for($i=1;$i<=$count;$i++){if(!isset($match[$i])){break;}elseif($match[$i]!=NULL){$type=$this->types[$i-1];break;}}$match=self::createToken($match[0],$type,$line);$len+=strlen($match['value']);$line+=substr_count($match['value'],"\n");}if($len!==strlen($input)){$errorOffset=$len;}}else{$this->tokens=NStrings::split($input,$this->re,PREG_SPLIT_NO_EMPTY);if($this->tokens&&!NStrings::match(end($this->tokens),$this->re)){$tmp=NStrings::split($this->input,$this->re,PREG_SPLIT_NO_EMPTY|PREG_SPLIT_OFFSET_CAPTURE);list(,$errorOffset)=end($tmp);}}if(isset($errorOffset)){$line=$errorOffset?substr_count($this->input,"\n",0,$errorOffset)+1:1;$col=$errorOffset-strrpos(substr($this->input,0,$errorOffset),"\n")+1;$token=str_replace("\n",'\n',substr($input,$errorOffset,10));throw
new
NTokenizerException("Unexpected '$token' on line $line, column $col.");}return$this->tokens;}static
function
createToken($value,$type=NULL,$line=NULL){return
array('value'=>$value,'type'=>$type,'line'=>$line);}function
getOffset($i){$tokens=NStrings::split($this->input,$this->re,PREG_SPLIT_NO_EMPTY|PREG_SPLIT_OFFSET_CAPTURE);$offset=isset($tokens[$i])?$tokens[$i][1]:strlen($this->input);return
array($offset,($offset?substr_count($this->input,"\n",0,$offset)+1:1),$offset-strrpos(substr($this->input,0,$offset),"\n"));}function
fetch(){return$this->scan(func_get_args(),TRUE);}function
fetchToken(){return$this->scan(func_get_args(),TRUE)===FALSE?FALSE:$this->current;}function
fetchAll(){return$this->scan(func_get_args(),FALSE);}function
fetchUntil($arg){return$this->scan(func_get_args(),FALSE,TRUE,TRUE);}function
isNext($arg){return(bool)$this->scan(func_get_args(),TRUE,FALSE);}function
isPrev($arg){return(bool)$this->scan(func_get_args(),TRUE,FALSE,FALSE,TRUE);}function
hasNext(){return
isset($this->tokens[$this->position]);}function
hasPrev(){return$this->position>1;}function
isCurrent($arg){$args=func_get_args();if(is_array($this->current)){return
in_array($this->current['value'],$args,TRUE)||in_array($this->current['type'],$args,TRUE);}else{return
in_array($this->current,$args,TRUE);}}function
reset(){$this->position=0;$this->current=NULL;}private
function
scan($wanted,$first,$advance=TRUE,$neg=FALSE,$prev=FALSE){$res=FALSE;$pos=$this->position+($prev?-2:0);while(isset($this->tokens[$pos])){$token=$this->tokens[$pos];$pos+=$prev?-1:1;$value=is_array($token)?$token['value']:$token;$type=is_array($token)?$token['type']:$token;if(!$wanted||(in_array($value,$wanted,TRUE)||in_array($type,$wanted,TRUE))^$neg){if($advance){$this->position=$pos;$this->current=$token;}$res.=$value;if($first){break;}}elseif($neg||!in_array($type,$this->ignored,TRUE)){break;}}return$res;}}class
NTokenizerException
extends
Exception{}class
NMacroTokenizer
extends
NTokenizer{const
T_WHITESPACE=1,T_COMMENT=2,T_SYMBOL=3,T_NUMBER=4,T_VARIABLE=5,T_STRING=6,T_CAST=7,T_KEYWORD=8,T_CHAR=9;function
__construct($input){parent::__construct(array(self::T_WHITESPACE=>'\s+',self::T_COMMENT=>'(?s)/\*.*?\*/',self::T_STRING=>NParser::RE_STRING,self::T_KEYWORD=>'(?:true|false|null|and|or|xor|clone|new|instanceof|return|continue|break|endif|endforeach|endwhile|endswitch|[A-Z_][A-Z0-9_]{2,})(?![\w\pL_])',self::T_CAST=>'\((?:expand|string|array|int|integer|float|bool|boolean|object)\)',self::T_VARIABLE=>'\$[\w\pL_]+',self::T_NUMBER=>'[+-]?[0-9]+(?:\.[0-9]+)?(?:e[0-9]+)?',self::T_SYMBOL=>'[\w\pL_]+(?:-[\w\pL_]+)*',self::T_CHAR=>'::|=>|->|\+\+|--|<<|>>|<=|>=|===|!==|==|!=|<>|&&|\|\||[^"\']'),'u');$this->ignored=array(self::T_COMMENT,self::T_WHITESPACE);$this->tokenize($input);}function
fetchWord(){$word=$this->fetchUntil(self::T_WHITESPACE,',');$this->fetch(',');$this->fetchAll(self::T_WHITESPACE,self::T_COMMENT);return$word;}}class
NParser
extends
NObject{const
RE_STRING='\'(?:\\\\.|[^\'\\\\])*\'|"(?:\\\\.|[^"\\\\])*"';const
N_PREFIX='n:';public$defaultSyntax='latte';public$syntaxes=array('latte'=>array('\\{(?![\\s\'"{}])','\\}'),'double'=>array('\\{\\{(?![\\s\'"{}])','\\}\\}'),'asp'=>array('<%\s*','\s*%>'),'python'=>array('\\{[{%]\s*','\s*[%}]\\}'),'off'=>array('[^\x00-\xFF]',''));private$macroRe;private$input;private$output;private$offset;private$context;private$lastHtmlTag;private$syntaxEndTag;private$xmlMode;const
CONTEXT_TEXT='text',CONTEXT_CDATA='cdata',CONTEXT_TAG='tag',CONTEXT_ATTRIBUTE='attribute',CONTEXT_NONE='none',CONTEXT_COMMENT='comment';function
parse($input){if(substr($input,0,3)==="\xEF\xBB\xBF"){$input=substr($input,3);}if(!NStrings::checkEncoding($input)){throw
new
InvalidArgumentException('Template is not valid UTF-8 stream.');}$input=str_replace("\r\n","\n",$input);$this->input=$input;$this->output=array();$this->offset=0;$this->setSyntax($this->defaultSyntax);$this->setContext(self::CONTEXT_TEXT);$this->lastHtmlTag=$this->syntaxEndTag=NULL;while($this->offset<strlen($input)){$matches=$this->{"context".$this->context[0]}();if(!$matches){break;}elseif(!empty($matches['comment'])){$this->addToken(NLatteToken::COMMENT,$matches[0]);}elseif(!empty($matches['macro'])){$token=$this->addToken(NLatteToken::MACRO_TAG,$matches[0]);list($token->name,$token->value,$token->modifiers)=$this->parseMacroTag($matches['macro']);}$this->filter();}if($this->offset<strlen($input)){$this->addToken(NLatteToken::TEXT,substr($this->input,$this->offset));}return$this->output;}private
function
contextText(){$matches=$this->match('~
			(?:(?<=\n|^)[ \t]*)?<(?P<closing>/?)(?P<tag>[a-z0-9:]+)|  ##  begin of HTML tag <tag </tag - ignores <!DOCTYPE
			<(?P<htmlcomment>!--(?!>))|     ##  begin of HTML comment <!--, but not <!-->
			'.$this->macroRe.'              ##  macro tag
		~xsi');if(!empty($matches['htmlcomment'])){$this->addToken(NLatteToken::HTML_TAG_BEGIN,$matches[0]);$this->setContext(self::CONTEXT_COMMENT);}elseif(!empty($matches['tag'])){$token=$this->addToken(NLatteToken::HTML_TAG_BEGIN,$matches[0]);$token->name=$matches['tag'];$token->closing=(bool)$matches['closing'];$this->lastHtmlTag=$matches['closing'].strtolower($matches['tag']);$this->setContext(self::CONTEXT_TAG);}return$matches;}private
function
contextCData(){$matches=$this->match('~
			</(?P<tag>'.$this->lastHtmlTag.')(?![a-z0-9:])| ##  end HTML tag </tag
			'.$this->macroRe.'              ##  macro tag
		~xsi');if(!empty($matches['tag'])){$token=$this->addToken(NLatteToken::HTML_TAG_BEGIN,$matches[0]);$token->name=$this->lastHtmlTag;$token->closing=TRUE;$this->lastHtmlTag='/'.$this->lastHtmlTag;$this->setContext(self::CONTEXT_TAG);}return$matches;}private
function
contextTag(){$matches=$this->match('~
			(?P<end>\ ?/?>)([ \t]*\n)?|  ##  end of HTML tag
			'.$this->macroRe.'|          ##  macro tag
			\s*(?P<attr>[^\s/>={]+)(?:\s*=\s*(?P<value>["\']|[^\s/>{]+))? ## begin of HTML attribute
		~xsi');if(!empty($matches['end'])){$this->addToken(NLatteToken::HTML_TAG_END,$matches[0]);$this->setContext(!$this->xmlMode&&in_array($this->lastHtmlTag,array('script','style'))?self::CONTEXT_CDATA:self::CONTEXT_TEXT);}elseif(isset($matches['attr'])&&$matches['attr']!==''){$token=$this->addToken(NLatteToken::HTML_ATTRIBUTE,$matches[0]);$token->name=$matches['attr'];$token->value=isset($matches['value'])?$matches['value']:'';if($token->value==='"'||$token->value==="'"){if(NStrings::startsWith($token->name,self::N_PREFIX)){$token->value='';if($m=$this->match('~(.*?)'.$matches['value'].'~xsi')){$token->value=$m[1];$token->text.=$m[0];}}else{$this->setContext(self::CONTEXT_ATTRIBUTE,$matches['value']);}}}return$matches;}private
function
contextAttribute(){$matches=$this->match('~
			(?P<quote>'.$this->context[1].')|  ##  end of HTML attribute
			'.$this->macroRe.'                 ##  macro tag
		~xsi');if(!empty($matches['quote'])){$this->addToken(NLatteToken::TEXT,$matches[0]);$this->setContext(self::CONTEXT_TAG);}return$matches;}private
function
contextComment(){$matches=$this->match('~
			(?P<htmlcomment>-->)|   ##  end of HTML comment
			'.$this->macroRe.'      ##  macro tag
		~xsi');if(!empty($matches['htmlcomment'])){$this->addToken(NLatteToken::HTML_TAG_END,$matches[0]);$this->setContext(self::CONTEXT_TEXT);}return$matches;}private
function
contextNone(){$matches=$this->match('~
			'.$this->macroRe.'     ##  macro tag
		~xsi');return$matches;}private
function
match($re){if($matches=NStrings::match($this->input,$re,PREG_OFFSET_CAPTURE,$this->offset)){$value=substr($this->input,$this->offset,$matches[0][1]-$this->offset);if($value!==''){$this->addToken(NLatteToken::TEXT,$value);}$this->offset=$matches[0][1]+strlen($matches[0][0]);foreach($matches
as$k=>$v)$matches[$k]=$v[0];}return$matches;}function
setContext($context,$quote=NULL){$this->context=array($context,$quote);return$this;}function
setSyntax($type){$type=($tmp=$type)?$tmp:$this->defaultSyntax;if(isset($this->syntaxes[$type])){$this->setDelimiters($this->syntaxes[$type][0],$this->syntaxes[$type][1]);}else{throw
new
InvalidArgumentException("Unknown syntax '$type'");}return$this;}function
setDelimiters($left,$right){$this->macroRe='
			(?P<comment>'.$left.'\\*.*?\\*'.$right.'\n{0,2})|
			'.$left.'
				(?P<macro>(?:'.self::RE_STRING.'|\{
						(?P<inner>'.self::RE_STRING.'|\{(?P>inner)\}|[^\'"{}])*+
				\}|[^\'"{}])+?)
			'.$right.'
			(?P<rmargin>[ \t]*(?=\n))?
		';return$this;}function
parseMacroTag($tag){$match=NStrings::match($tag,'~^
			(
				(?P<name>\?|/?[a-z]\w*+(?:[.:]\w+)*+(?!::|\(|\\\\))|   ## ?, name, /name, but not function( or class:: or namespace\
				(?P<noescape>!?)(?P<shortname>/?[=\~#%^&_]?)      ## !expression, !=expression, ...
			)(?P<args>.*?)
			(?P<modifiers>\|[a-z](?:'.NParser::RE_STRING.'|[^\'"])*)?
		()\z~isx');if(!$match){return
FALSE;}$modifiers=preg_replace('#\|noescape\s?(?=\||\z)#i','',$match['modifiers'],-1,$noescape);if($match['name']===''){$match['name']=($tmp=$match['shortname'])?$tmp:'=';if(!$noescape&&!$match['noescape']&&substr($match['shortname'],0,1)!=='/'){$modifiers.='|escape';}}return
array($match['name'],trim($match['args']),$modifiers);}private
function
addToken($type,$text){$this->output[]=$token=new
NLatteToken;$token->type=$type;$token->text=$text;$token->line=substr_count($this->input,"\n",0,max(1,$this->offset-1))+1;return$token;}protected
function
filter(){$token=end($this->output);if($token->type===NLatteToken::MACRO_TAG&&$token->name==='/syntax'){$this->setSyntax($this->defaultSyntax);$token->type=NLatteToken::COMMENT;}elseif($token->type===NLatteToken::MACRO_TAG&&$token->name==='syntax'){$this->setSyntax($token->value);$token->type=NLatteToken::COMMENT;}elseif($token->type===NLatteToken::HTML_ATTRIBUTE&&$token->name==='n:syntax'){$this->setSyntax($token->value);$this->syntaxEndTag='/'.$this->lastHtmlTag;$token->type=NLatteToken::COMMENT;}elseif($token->type===NLatteToken::HTML_TAG_END&&$this->lastHtmlTag===$this->syntaxEndTag){$this->setSyntax($this->defaultSyntax);}elseif($token->type===NLatteToken::MACRO_TAG&&$token->name==='contentType'){if(preg_match('#html|xml#',$token->value,$m)){$this->xmlMode=$m[0]==='xml';$this->setContext(self::CONTEXT_TEXT);}else{$this->setContext(self::CONTEXT_NONE);}}}}class
NPhpWriter
extends
NObject{private$argsTokenizer;private$modifiers;private$compiler;static
function
using(NMacroNode$node,NLatteCompiler$compiler=NULL){return
new
self($node->tokenizer,$node->modifiers,$compiler);}function
__construct(NMacroTokenizer$argsTokenizer,$modifiers=NULL,NLatteCompiler$compiler=NULL){$this->argsTokenizer=$argsTokenizer;$this->modifiers=$modifiers;$this->compiler=$compiler;}function
write($mask){$args=func_get_args();array_shift($args);$word=strpos($mask,'%node.word')===FALSE?NULL:$this->argsTokenizer->fetchWord();$me=$this;$mask=NStrings::replace($mask,'#%escape(\(([^()]*+|(?1))+\))#',create_function('$m','extract($GLOBALS[0]['.array_push($GLOBALS[0],array('me'=>$me)).'-1], EXTR_REFS);
			return $me->escape(substr($m[1], 1, -1));
		'));$mask=NStrings::replace($mask,'#%modify(\(([^()]*+|(?1))+\))#',create_function('$m','extract($GLOBALS[0]['.array_push($GLOBALS[0],array('me'=>$me)).'-1], EXTR_REFS);
			return $me->formatModifiers(substr($m[1], 1, -1));
		'));return
NStrings::replace($mask,'#([,+]\s*)?%(node\.word|node\.array|node\.args|var|raw)(\?)?(\s*\+\s*)?()#',create_function('$m','extract($GLOBALS[0]['.array_push($GLOBALS[0],array('me'=>$me,'word'=>$word,'args'=>&$args)).'-1], EXTR_REFS);
			list(, $l, $macro, $cond, $r) = $m;

			switch ($macro) {
				case \'node.word\':
					$code = $me->formatWord($word); break;
				case \'node.args\':
					$code = $me->formatArgs(); break;
				case \'node.array\':
					$code = $me->formatArray();
					$code = $cond && $code === \'array()\' ? \'\' : $code; break;
				case \'var\':
					$code = var_export(array_shift($args), TRUE); break;
				case \'raw\':
					$code = (string) array_shift($args); break;
			}

			if ($cond && $code === \'\') {
				return $r ? $l : $r;
			} else {
				return $l . $code . $r;
			}
		'));}function
formatModifiers($var){$modifiers=ltrim($this->modifiers,'|');if(!$modifiers){return$var;}$tokenizer=$this->preprocess(new
NMacroTokenizer($modifiers));$inside=FALSE;while($token=$tokenizer->fetchToken()){if($token['type']===NMacroTokenizer::T_WHITESPACE){$var=rtrim($var).' ';}elseif(!$inside){if($token['type']===NMacroTokenizer::T_SYMBOL){if($this->compiler&&$token['value']==='escape'){$var=$this->escape($var);$tokenizer->fetch('|');}else{$var="\$template->".$token['value']."($var";$inside=TRUE;}}else{throw
new
NCompileException("Modifier name must be alphanumeric string, '$token[value]' given.");}}else{if($token['value']===':'||$token['value']===','){$var=$var.', ';}elseif($token['value']==='|'){$var=$var.')';$inside=FALSE;}else{$var.=$this->canQuote($tokenizer)?"'$token[value]'":$token['value'];}}}return$inside?"$var)":$var;}function
formatArgs(){$out='';$tokenizer=$this->preprocess();while($token=$tokenizer->fetchToken()){$out.=$this->canQuote($tokenizer)?"'$token[value]'":$token['value'];}return$out;}function
formatArray(){$out='';$expand=NULL;$tokenizer=$this->preprocess();while($token=$tokenizer->fetchToken()){if($token['value']==='(expand)'&&$token['depth']===0){$expand=TRUE;$out.='),';}elseif($expand&&($token['value']===',')&&!$token['depth']){$expand=FALSE;$out.=', array(';}else{$out.=$this->canQuote($tokenizer)?"'$token[value]'":$token['value'];}}if($expand===NULL){return"array($out)";}else{return"array_merge(array($out".($expand?', array(':'')."))";}}function
formatWord($s){return(is_numeric($s)||preg_match('#^\$|[\'"]|^true\z|^false\z|^null\z#i',$s))?$s:'"'.$s.'"';}function
canQuote(NMacroTokenizer$tokenizer){return$tokenizer->isCurrent(NMacroTokenizer::T_SYMBOL)&&(!$tokenizer->hasPrev()||$tokenizer->isPrev(',','(','[','=>',':','?','.','<','>','<=','>=','===','!==','==','!=','<>','&&','||','=','and','or','xor'))&&(!$tokenizer->hasNext()||$tokenizer->isNext(',',';',')',']','=>',':','?','.','<','>','<=','>=','===','!==','==','!=','<>','&&','||','and','or','xor'));}function
preprocess(NMacroTokenizer$tokenizer=NULL){$tokenizer=$tokenizer===NULL?$this->argsTokenizer:$tokenizer;$inTernary=$prev=NULL;$tokens=$arrays=array();while($token=$tokenizer->fetchToken()){$token['depth']=$depth=count($arrays);if($token['type']===NMacroTokenizer::T_COMMENT){continue;}elseif($token['type']===NMacroTokenizer::T_WHITESPACE){$tokens[]=$token;continue;}if($token['value']==='?'){$inTernary=$depth;}elseif($token['value']===':'){$inTernary=NULL;}elseif($inTernary===$depth&&($token['value']===','||$token['value']===')'||$token['value']===']')){$tokens[]=NMacroTokenizer::createToken(':')+array('depth'=>$depth);$tokens[]=NMacroTokenizer::createToken('null')+array('depth'=>$depth);$inTernary=NULL;}if($token['value']==='['){if($arrays[]=$prev['value']!==']'&&$prev['value']!==')'&&$prev['type']!==NMacroTokenizer::T_SYMBOL&&$prev['type']!==NMacroTokenizer::T_VARIABLE&&$prev['type']!==NMacroTokenizer::T_KEYWORD){$tokens[]=NMacroTokenizer::createToken('array')+array('depth'=>$depth);$token=NMacroTokenizer::createToken('(');}}elseif($token['value']===']'){if(array_pop($arrays)===TRUE){$token=NMacroTokenizer::createToken(')');}}elseif($token['value']==='('){$arrays[]='(';}elseif($token['value']===')'){array_pop($arrays);}$tokens[]=$prev=$token;}if($inTernary!==NULL){$tokens[]=NMacroTokenizer::createToken(':')+array('depth'=>count($arrays));$tokens[]=NMacroTokenizer::createToken('null')+array('depth'=>count($arrays));}$tokenizer=clone$tokenizer;$tokenizer->reset();$tokenizer->tokens=$tokens;return$tokenizer;}function
escape($s){switch($this->compiler->getContentType()){case
NLatteCompiler::CONTENT_XHTML:case
NLatteCompiler::CONTENT_HTML:$context=$this->compiler->getContext();switch($context[0]){case
NLatteCompiler::CONTEXT_SINGLE_QUOTED:case
NLatteCompiler::CONTEXT_DOUBLE_QUOTED:if($context[1]===NLatteCompiler::CONTENT_JS){$s="NTemplateHelpers::escapeJs($s)";}elseif($context[1]===NLatteCompiler::CONTENT_CSS){$s="NTemplateHelpers::escapeCss($s)";}$quote=$context[0]===NLatteCompiler::CONTEXT_DOUBLE_QUOTED?', ENT_COMPAT':', ENT_QUOTES';return"NTemplateHelpers::escapeHtml($s$quote)";case
NLatteCompiler::CONTEXT_COMMENT:return"NTemplateHelpers::escapeHtmlComment($s)";case
NLatteCompiler::CONTENT_JS:case
NLatteCompiler::CONTENT_CSS:return'NTemplateHelpers::escape'.ucfirst($context[0])."($s)";default:return"NTemplateHelpers::escapeHtml($s, ENT_NOQUOTES)";}case
NLatteCompiler::CONTENT_XML:$context=$this->compiler->getContext();if($context[0]===NLatteCompiler::CONTEXT_COMMENT){return"NTemplateHelpers::escapeHtmlComment($s)";}case
NLatteCompiler::CONTENT_JS:case
NLatteCompiler::CONTENT_CSS:case
NLatteCompiler::CONTENT_ICAL:return'NTemplateHelpers::escape'.ucfirst($this->compiler->getContentType())."($s)";case
NLatteCompiler::CONTENT_TEXT:return$s;default:return"\$template->escape($s)";}}}class
NLatteToken
extends
NObject{const
TEXT='text',MACRO_TAG='macroTag',HTML_TAG_BEGIN='htmlTagBegin',HTML_TAG_END='htmlTagEnd',HTML_ATTRIBUTE='htmlAttribute',COMMENT='comment';public$type;public$text;public$line;public$name;public$value;public$modifiers;public$closing;}class
NRobotLoader
extends
NAutoLoader{const
RETRY_LIMIT=3;public$scanDirs=array();public$ignoreDirs='.*, *.old, *.bak, *.tmp, temp';public$acceptFiles='*.php, *.php5';public$autoRebuild=TRUE;private$classes=array();private$rebuilt=FALSE;private$missing=array();private$cacheStorage;function
__construct(){if(!extension_loaded('tokenizer')){throw
new
NotSupportedException("PHP extension Tokenizer is not loaded.");}}function
register(){$this->classes=$this->getCache()->load($this->getKey(),new
NCallback($this,'_rebuildCallback'));parent::register();return$this;}function
tryLoad($type){$type=ltrim(strtolower($type),'\\');$info=&$this->classes[$type];if(isset($this->missing[$type])||(is_int($info)&&$info>=self::RETRY_LIMIT)){return;}if($this->autoRebuild){if(!is_array($info)||!is_file($info['file'])){$info=is_int($info)?$info+1:0;if($this->rebuilt){$this->getCache()->save($this->getKey(),$this->classes,array(NCache::CONSTS=>'NFramework::REVISION'));}else{$this->rebuild();}}elseif(!$this->rebuilt&&filemtime($info['file'])!==$info['time']){$this->updateFile($info['file']);if(!isset($this->classes[$type])){$this->classes[$type]=0;}$this->getCache()->save($this->getKey(),$this->classes,array(NCache::CONSTS=>'NFramework::REVISION'));}}if(isset($this->classes[$type]['file'])){NLimitedScope::load($this->classes[$type]['file'],TRUE);self::$count++;}else{$this->missing[$type]=TRUE;}}function
addDirectory($path){foreach((array)$path
as$val){$real=realpath($val);if($real===FALSE){throw
new
DirectoryNotFoundException("Directory '$val' not found.");}$this->scanDirs[]=$real;}return$this;}function
getIndexedClasses(){$res=array();foreach($this->classes
as$info){if(is_array($info)){$res[$info['orig']]=$info['file'];}}return$res;}function
rebuild(){$this->rebuilt=TRUE;$this->getCache()->save($this->getKey(),new
NCallback($this,'_rebuildCallback'));}function
_rebuildCallback(&$dp){$files=$missing=array();foreach($this->classes
as$class=>$info){if(is_array($info)){$files[$info['file']]['time']=$info['time'];$files[$info['file']]['classes'][]=$info['orig'];}else{$missing[$class]=$info;}}$this->classes=array();foreach(array_unique($this->scanDirs)as$dir){foreach($this->createFileIterator($dir)as$file){$file=$file->getPathname();if(isset($files[$file])&&$files[$file]['time']==filemtime($file)){$classes=$files[$file]['classes'];}else{$classes=$this->scanPhp(file_get_contents($file));}foreach($classes
as$class){$info=&$this->classes[strtolower($class)];if(isset($info['file'])){$e=new
InvalidStateException("Ambiguous class $class resolution; defined in {$info['file']} and in $file.");if(PHP_VERSION_ID<50300){NDebugger::_exceptionHandler($e);exit;}else{throw$e;}}$info=array('file'=>$file,'time'=>filemtime($file),'orig'=>$class);}}}$dp=array(NCache::CONSTS=>'NFramework::REVISION');$this->classes+=$missing;return$this->classes;}private
function
createFileIterator($dir){if(!is_dir($dir)){return
new
ArrayIterator(array(new
SplFileInfo($dir)));}$ignoreDirs=is_array($this->ignoreDirs)?$this->ignoreDirs:preg_split('#[,\s]+#',$this->ignoreDirs);$disallow=array();foreach($ignoreDirs
as$item){if($item=realpath($item)){$disallow[$item]=TRUE;}}$iterator=NFinder::findFiles(is_array($this->acceptFiles)?$this->acceptFiles:preg_split('#[,\s]+#',$this->acceptFiles))->filter(create_function('$file','extract($GLOBALS[0]['.array_push($GLOBALS[0],array('disallow'=>&$disallow)).'-1], EXTR_REFS);
				return !isset($disallow[$file->getPathname()]);
			'))->from($dir)->exclude($ignoreDirs)->filter($filter=create_function('$dir','extract($GLOBALS[0]['.array_push($GLOBALS[0],array('disallow'=>&$disallow)).'-1], EXTR_REFS);
				$path = $dir->getPathname();
				if (is_file("$path/netterobots.txt")) {
					foreach (file("$path/netterobots.txt") as $s) {
					if (preg_match(\'#^(?:disallow\\\\s*:)?\\\\s*(\\\\S+)#i\', $s, $matches)) {
							$disallow[$path . str_replace(\'/\', DIRECTORY_SEPARATOR, rtrim(\'/\' . ltrim($matches[1], \'/\'), \'/\'))] = TRUE;
						}
					}
				}
				return !isset($disallow[$path]);
			'));$filter(new
SplFileInfo($dir));return$iterator;}private
function
updateFile($file){foreach($this->classes
as$class=>$info){if(isset($info['file'])&&$info['file']===$file){unset($this->classes[$class]);}}if(is_file($file)){foreach($this->scanPhp(file_get_contents($file))as$class){$info=&$this->classes[strtolower($class)];if(isset($info['file'])&&@filemtime($info['file'])!==$info['time']){$this->updateFile($info['file']);$info=&$this->classes[strtolower($class)];}if(isset($info['file'])){$e=new
InvalidStateException("Ambiguous class $class resolution; defined in {$info['file']} and in $file.");if(PHP_VERSION_ID<50300){NDebugger::_exceptionHandler($e);exit;}else{throw$e;}}$info=array('file'=>$file,'time'=>filemtime($file),'orig'=>$class);}}}private
function
scanPhp($code){$T_NAMESPACE=PHP_VERSION_ID<50300?-1:T_NAMESPACE;$T_NS_SEPARATOR=PHP_VERSION_ID<50300?-1:T_NS_SEPARATOR;$T_TRAIT=PHP_VERSION_ID<50400?-1:T_TRAIT;$expected=FALSE;$namespace='';$level=$minLevel=0;$classes=array();if(preg_match('#//nette'.'loader=(\S*)#',$code,$matches)){foreach(explode(',',$matches[1])as$name){$classes[]=$name;}return$classes;}foreach(@token_get_all($code)as$token){if(is_array($token)){switch($token[0]){case
T_COMMENT:case
T_DOC_COMMENT:case
T_WHITESPACE:continue
2;case$T_NS_SEPARATOR:case
T_STRING:if($expected){$name.=$token[1];}continue
2;case$T_NAMESPACE:case
T_CLASS:case
T_INTERFACE:case$T_TRAIT:$expected=$token[0];$name='';continue
2;case
T_CURLY_OPEN:case
T_DOLLAR_OPEN_CURLY_BRACES:$level++;}}if($expected){switch($expected){case
T_CLASS:case
T_INTERFACE:case$T_TRAIT:if($name&&$level===$minLevel){$classes[]=$namespace.$name;}break;case$T_NAMESPACE:$namespace=$name?$name.'\\':'';$minLevel=$token==='{'?1:0;}$expected=NULL;}if($token==='{'){$level++;}elseif($token==='}'){$level--;}}return$classes;}function
setCacheStorage(ICacheStorage$storage){$this->cacheStorage=$storage;return$this;}function
getCacheStorage(){return$this->cacheStorage;}protected
function
getCache(){if(!$this->cacheStorage){trigger_error('Missing cache storage.',E_USER_WARNING);$this->cacheStorage=new
NDevNullStorage;}return
new
NCache($this->cacheStorage,'Nette.RobotLoader');}protected
function
getKey(){return
array($this->ignoreDirs,$this->acceptFiles,$this->scanDirs);}}class
NMailMimePart
extends
NObject{const
ENCODING_BASE64='base64',ENCODING_7BIT='7bit',ENCODING_8BIT='8bit',ENCODING_QUOTED_PRINTABLE='quoted-printable';const
EOL="\r\n";const
LINE_LENGTH=76;private$headers=array();private$parts=array();private$body;function
setHeader($name,$value,$append=FALSE){if(!$name||preg_match('#[^a-z0-9-]#i',$name)){throw
new
InvalidArgumentException("Header name must be non-empty alphanumeric string, '$name' given.");}if($value==NULL){if(!$append){unset($this->headers[$name]);}}elseif(is_array($value)){$tmp=&$this->headers[$name];if(!$append||!is_array($tmp)){$tmp=array();}foreach($value
as$email=>$recipient){if($recipient!==NULL&&!NStrings::checkEncoding($recipient)){NValidators::assert($recipient,'unicode',"header '$name'");}if(preg_match('#[\r\n]#',$recipient)){throw
new
InvalidArgumentException('Name must not contain line separator.');}NValidators::assert($email,'email',"header '$name'");$tmp[$email]=$recipient;}}else{$value=(string)$value;if(!NStrings::checkEncoding($value)){throw
new
InvalidArgumentException('Header is not valid UTF-8 string.');}$this->headers[$name]=preg_replace('#[\r\n]+#',' ',$value);}return$this;}function
getHeader($name){return
isset($this->headers[$name])?$this->headers[$name]:NULL;}function
clearHeader($name){unset($this->headers[$name]);return$this;}function
getEncodedHeader($name){$offset=strlen($name)+2;if(!isset($this->headers[$name])){return
NULL;}elseif(is_array($this->headers[$name])){$s='';foreach($this->headers[$name]as$email=>$name){if($name!=NULL){$s.=self::encodeHeader($name,$offset,strpbrk($name,'.,;<@>()[]"=?'));$email=" <$email>";}$email.=',';if($s!==''&&$offset+strlen($email)>self::LINE_LENGTH){$s.=self::EOL."\t";$offset=1;}$s.=$email;$offset+=strlen($email);}return
substr($s,0,-1);}elseif(preg_match('#^(\S+; (?:file)?name=)"(.*)"\z#',$this->headers[$name],$m)){$offset+=strlen($m[1]);return$m[1].'"'.self::encodeHeader($m[2],$offset).'"';}else{return
self::encodeHeader($this->headers[$name],$offset);}}function
getHeaders(){return$this->headers;}function
setContentType($contentType,$charset=NULL){$this->setHeader('Content-Type',$contentType.($charset?"; charset=$charset":''));return$this;}function
setEncoding($encoding){$this->setHeader('Content-Transfer-Encoding',$encoding);return$this;}function
getEncoding(){return$this->getHeader('Content-Transfer-Encoding');}function
addPart(NMailMimePart$part=NULL){return$this->parts[]=$part===NULL?new
self:$part;}function
setBody($body){$this->body=$body;return$this;}function
getBody(){return$this->body;}function
generateMessage(){$output='';$boundary='--------'.NStrings::random();foreach($this->headers
as$name=>$value){$output.=$name.': '.$this->getEncodedHeader($name);if($this->parts&&$name==='Content-Type'){$output.=';'.self::EOL."\tboundary=\"$boundary\"";}$output.=self::EOL;}$output.=self::EOL;$body=(string)$this->body;if($body!==''){switch($this->getEncoding()){case
self::ENCODING_QUOTED_PRINTABLE:$output.=function_exists('quoted_printable_encode')?quoted_printable_encode($body):self::encodeQuotedPrintable($body);break;case
self::ENCODING_BASE64:$output.=rtrim(chunk_split(base64_encode($body),self::LINE_LENGTH,self::EOL));break;case
self::ENCODING_7BIT:$body=preg_replace('#[\x80-\xFF]+#','',$body);case
self::ENCODING_8BIT:$body=str_replace(array("\x00","\r"),'',$body);$body=str_replace("\n",self::EOL,$body);$output.=$body;break;default:throw
new
InvalidStateException('Unknown encoding.');}}if($this->parts){if(substr($output,-strlen(self::EOL))!==self::EOL){$output.=self::EOL;}foreach($this->parts
as$part){$output.='--'.$boundary.self::EOL.$part->generateMessage().self::EOL;}$output.='--'.$boundary.'--';}return$output;}private
static
function
encodeHeader($s,&$offset=0,$force=FALSE){$o='';if($offset>=55){$o=self::EOL."\t";$offset=1;}if(!$force&&strspn($s,"!\"#$%&\'()*+,-./0123456789:;<>@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^`abcdefghijklmnopqrstuvwxyz{|}=? _\r\n\t")===strlen($s)&&($offset+strlen($s)<=self::LINE_LENGTH)){$offset+=strlen($s);return$o.$s;}$o.=str_replace("\n ","\n\t",substr(iconv_mime_encode(str_repeat(' ',$offset),$s,array('scheme'=>'B','input-charset'=>'UTF-8','output-charset'=>'UTF-8')),$offset+2));$offset=strlen($o)-strrpos($o,"\n");return$o;}static
function
encodeQuotedPrintable($s){$range='!"#$%&\'()*+,-./0123456789:;<>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}';$pos=0;$len=0;$o='';$size=strlen($s);while($pos<$size){if($l=strspn($s,$range,$pos)){while($len+$l>self::LINE_LENGTH-1){$lx=self::LINE_LENGTH-$len-1;$o.=substr($s,$pos,$lx).'='.self::EOL;$pos+=$lx;$l-=$lx;$len=0;}$o.=substr($s,$pos,$l);$len+=$l;$pos+=$l;}else{$len+=3;if($len>self::LINE_LENGTH-1){$o.='='.self::EOL;$len=3;}$o.='='.strtoupper(bin2hex($s[$pos]));$pos++;}}return
rtrim($o,'='.self::EOL);}}class
NMail
extends
NMailMimePart{const
HIGH=1,NORMAL=3,LOW=5;public
static$defaultMailer='NSendmailMailer';public
static$defaultHeaders=array('MIME-Version'=>'1.0','X-Mailer'=>'Nette Framework');private$mailer;private$attachments=array();private$inlines=array();private$html;private$basePath;function
__construct(){foreach(self::$defaultHeaders
as$name=>$value){$this->setHeader($name,$value);}$this->setHeader('Date',date('r'));}function
setFrom($email,$name=NULL){$this->setHeader('From',$this->formatEmail($email,$name));return$this;}function
getFrom(){return$this->getHeader('From');}function
addReplyTo($email,$name=NULL){$this->setHeader('Reply-To',$this->formatEmail($email,$name),TRUE);return$this;}function
setSubject($subject){$this->setHeader('Subject',$subject);return$this;}function
getSubject(){return$this->getHeader('Subject');}function
addTo($email,$name=NULL){$this->setHeader('To',$this->formatEmail($email,$name),TRUE);return$this;}function
addCc($email,$name=NULL){$this->setHeader('Cc',$this->formatEmail($email,$name),TRUE);return$this;}function
addBcc($email,$name=NULL){$this->setHeader('Bcc',$this->formatEmail($email,$name),TRUE);return$this;}private
function
formatEmail($email,$name){if(!$name&&preg_match('#^(.+) +<(.*)>\z#',$email,$matches)){return
array($matches[2]=>$matches[1]);}else{return
array($email=>$name);}}function
setReturnPath($email){$this->setHeader('Return-Path',$email);return$this;}function
getReturnPath(){return$this->getHeader('Return-Path');}function
setPriority($priority){$this->setHeader('X-Priority',(int)$priority);return$this;}function
getPriority(){return$this->getHeader('X-Priority');}function
setHtmlBody($html,$basePath=NULL){$this->html=$html;$this->basePath=$basePath;return$this;}function
getHtmlBody(){return$this->html;}function
addEmbeddedFile($file,$content=NULL,$contentType=NULL){return$this->inlines[$file]=$this->createAttachment($file,$content,$contentType,'inline')->setHeader('Content-ID',$this->getRandomId());}function
addAttachment($file,$content=NULL,$contentType=NULL){return$this->attachments[]=$this->createAttachment($file,$content,$contentType,'attachment');}private
function
createAttachment($file,$content,$contentType,$disposition){$part=new
NMailMimePart;if($content===NULL){$content=@file_get_contents($file);if($content===FALSE){throw
new
FileNotFoundException("Unable to read file '$file'.");}}else{$content=(string)$content;}$part->setBody($content);$part->setContentType($contentType?$contentType:NMimeTypeDetector::fromString($content));$part->setEncoding(preg_match('#(multipart|message)/#A',$contentType)?self::ENCODING_8BIT:self::ENCODING_BASE64);$part->setHeader('Content-Disposition',$disposition.'; filename="'.NStrings::fixEncoding(basename($file)).'"');return$part;}function
send(){$this->getMailer()->send($this->build());}function
setMailer(IMailer$mailer){$this->mailer=$mailer;return$this;}function
getMailer(){if($this->mailer===NULL){$this->mailer=is_object(self::$defaultMailer)?self::$defaultMailer:new
self::$defaultMailer;}return$this->mailer;}function
generateMessage(){if($this->getHeader('Message-ID')){return
parent::generateMessage();}else{return$this->build()->generateMessage();}}protected
function
build(){$mail=clone$this;$mail->setHeader('Message-ID',$this->getRandomId());$mail->buildHtml();$mail->buildText();$cursor=$mail;if($mail->attachments){$tmp=$cursor->setContentType('multipart/mixed');$cursor=$cursor->addPart();foreach($mail->attachments
as$value){$tmp->addPart($value);}}if($mail->html!=NULL){$tmp=$cursor->setContentType('multipart/alternative');$cursor=$cursor->addPart();$alt=$tmp->addPart();if($mail->inlines){$tmp=$alt->setContentType('multipart/related');$alt=$alt->addPart();foreach($mail->inlines
as$value){$tmp->addPart($value);}}$alt->setContentType('text/html','UTF-8')->setEncoding(preg_match('#\S{990}#',$mail->html)?self::ENCODING_QUOTED_PRINTABLE:(preg_match('#[\x80-\xFF]#',$mail->html)?self::ENCODING_8BIT:self::ENCODING_7BIT))->setBody($mail->html);}$text=$mail->getBody();$mail->setBody(NULL);$cursor->setContentType('text/plain','UTF-8')->setEncoding(preg_match('#\S{990}#',$text)?self::ENCODING_QUOTED_PRINTABLE:(preg_match('#[\x80-\xFF]#',$text)?self::ENCODING_8BIT:self::ENCODING_7BIT))->setBody($text);return$mail;}protected
function
buildHtml(){if($this->html
instanceof
ITemplate){$this->html->mail=$this;if($this->basePath===NULL&&$this->html
instanceof
IFileTemplate){$this->basePath=dirname($this->html->getFile());}$this->html=$this->html->__toString(TRUE);}if($this->basePath!==FALSE){$cids=array();$matches=NStrings::matchAll($this->html,'#(src\s*=\s*|background\s*=\s*|url\()(["\'])(?![a-z]+:|[/\\#])(.+?)\\2#i',PREG_OFFSET_CAPTURE);foreach(array_reverse($matches)as$m){$file=rtrim($this->basePath,'/\\').'/'.$m[3][0];if(!isset($cids[$file])){$cids[$file]=substr($this->addEmbeddedFile($file)->getHeader("Content-ID"),1,-1);}$this->html=substr_replace($this->html,"{$m[1][0]}{$m[2][0]}cid:{$cids[$file]}{$m[2][0]}",$m[0][1],strlen($m[0][0]));}}if(!$this->getSubject()&&$matches=NStrings::match($this->html,'#<title>(.+?)</title>#is')){$this->setSubject(html_entity_decode($matches[1],ENT_QUOTES,'UTF-8'));}}protected
function
buildText(){$text=$this->getBody();if($text
instanceof
ITemplate){$text->mail=$this;$this->setBody($text->__toString(TRUE));}elseif($text==NULL&&$this->html!=NULL){$text=NStrings::replace($this->html,array('#<(style|script|head).*</\\1>#Uis'=>'','#<t[dh][ >]#i'=>" $0",'#[\r\n]+#'=>' ','#<(/?p|/?h\d|li|br|/tr)[ >/]#i'=>"\n$0"));$text=html_entity_decode(strip_tags($text),ENT_QUOTES,'UTF-8');$text=NStrings::replace($text,'#[ \t]+#',' ');$this->setBody(trim($text));}}private
function
getRandomId(){return'<'.NStrings::random().'@'.preg_replace('#[^\w.-]+#','',isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:php_uname('n')).'>';}}class
NSendmailMailer
extends
NObject
implements
IMailer{public$commandArgs;function
send(NMail$mail){$tmp=clone$mail;$tmp->setHeader('Subject',NULL);$tmp->setHeader('To',NULL);$parts=explode(NMail::EOL.NMail::EOL,$tmp->generateMessage(),2);$args=array(str_replace(NMail::EOL,PHP_EOL,$mail->getEncodedHeader('To')),str_replace(NMail::EOL,PHP_EOL,$mail->getEncodedHeader('Subject')),str_replace(NMail::EOL,PHP_EOL,$parts[1]),str_replace(NMail::EOL,PHP_EOL,$parts[0]));if($this->commandArgs){$args[]=(string)$this->commandArgs;}if(call_user_func_array('mail',$args)===FALSE){$error=error_get_last();throw
new
InvalidStateException("Unable to send email: $error[message].");}}}class
NSmtpMailer
extends
NObject
implements
IMailer{private$connection;private$host;private$port;private$username;private$password;private$secure;private$timeout;function
__construct(array$options=array()){if(isset($options['host'])){$this->host=$options['host'];$this->port=isset($options['port'])?(int)$options['port']:NULL;}else{$this->host=ini_get('SMTP');$this->port=(int)ini_get('smtp_port');}$this->username=isset($options['username'])?$options['username']:'';$this->password=isset($options['password'])?$options['password']:'';$this->secure=isset($options['secure'])?$options['secure']:'';$this->timeout=isset($options['timeout'])?(int)$options['timeout']:20;if(!$this->port){$this->port=$this->secure==='ssl'?465:25;}}function
send(NMail$mail){$mail=clone$mail;$this->connect();if(($from=$mail->getHeader('Return-Path'))||($from=key($mail->getHeader('From')))){$this->write("MAIL FROM:<$from>",250);}foreach(array_merge((array)$mail->getHeader('To'),(array)$mail->getHeader('Cc'),(array)$mail->getHeader('Bcc'))as$email=>$name){$this->write("RCPT TO:<$email>",array(250,251));}$mail->setHeader('Bcc',NULL);$data=$mail->generateMessage();$this->write('DATA',354);$data=preg_replace('#^\.#m','..',$data);$this->write($data);$this->write('.',250);$this->write('QUIT',221);$this->disconnect();}private
function
connect(){$this->connection=@fsockopen(($this->secure==='ssl'?'ssl://':'').$this->host,$this->port,$errno,$error,$this->timeout);if(!$this->connection){throw
new
NSmtpException($error,$errno);}stream_set_timeout($this->connection,$this->timeout,0);$this->read();$self=isset($_SERVER['HTTP_HOST'])&&preg_match('#^[\w.-]+\z#',$_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'localhost';$this->write("EHLO $self");if((int)$this->read()!==250){$this->write("HELO $self",250);}if($this->secure==='tls'){$this->write('STARTTLS',220);if(!stream_socket_enable_crypto($this->connection,TRUE,STREAM_CRYPTO_METHOD_TLS_CLIENT)){throw
new
NSmtpException('Unable to connect via TLS.');}$this->write("EHLO $self",250);}if($this->username!=NULL&&$this->password!=NULL){$this->write('AUTH LOGIN',334);$this->write(base64_encode($this->username),334,'username');$this->write(base64_encode($this->password),235,'password');}}private
function
disconnect(){fclose($this->connection);$this->connection=NULL;}private
function
write($line,$expectedCode=NULL,$message=NULL){fwrite($this->connection,$line.NMail::EOL);if($expectedCode&&!in_array((int)$this->read(),(array)$expectedCode)){throw
new
NSmtpException('SMTP server did not accept '.($message?$message:$line));}}private
function
read(){$s='';while(($line=fgets($this->connection,1e3))!=NULL){$s.=$line;if(substr($line,3,1)===' '){break;}}return$s;}}class
NSmtpException
extends
Exception{}class
NAnnotation
extends
NObject
implements
IAnnotation{function
__construct(array$values){foreach($values
as$k=>$v){$this->$k=$v;}}function
__toString(){return$this->value;}}/**
 * Annotations support for PHP.
 *
 * @author     David Grudl
 * @Annotation
 * @package Nette\Reflection
 */class
NAnnotationsParser{const
RE_STRING='\'(?:\\\\.|[^\'\\\\])*\'|"(?:\\\\.|[^"\\\\])*"';const
RE_IDENTIFIER='[_a-zA-Z\x7F-\xFF][_a-zA-Z0-9\x7F-\xFF-\\\]*';public
static$useReflection;public
static$inherited=array('description','param','return');private
static$cache;private
static$timestamps;private
static$cacheStorage;final
function
__construct(){throw
new
NStaticClassException;}static
function
getAll(Reflector$r){if($r
instanceof
ReflectionClass){$type=$r->getName();$member='';}elseif($r
instanceof
ReflectionMethod){$type=$r->getDeclaringClass()->getName();$member=$r->getName();}else{$type=$r->getDeclaringClass()->getName();$member='$'.$r->getName();}if(!self::$useReflection){$file=$r
instanceof
ReflectionClass?$r->getFileName():$r->getDeclaringClass()->getFileName();if($file&&isset(self::$timestamps[$file])&&self::$timestamps[$file]!==filemtime($file)){unset(self::$cache[$type]);}unset(self::$timestamps[$file]);}if(isset(self::$cache[$type][$member])){return
self::$cache[$type][$member];}if(self::$useReflection===NULL){self::$useReflection=(bool)NClassReflection::from(__CLASS__)->getDocComment();}if(self::$useReflection){$annotations=self::parseComment($r->getDocComment());}else{if(!self::$cacheStorage){self::$cacheStorage=new
NDevNullStorage;}$outerCache=new
NCache(self::$cacheStorage,'Nette.Reflection.Annotations');if(self::$cache===NULL){self::$cache=(array)$outerCache->offsetGet('list');self::$timestamps=isset(self::$cache['*'])?self::$cache['*']:array();}if(!isset(self::$cache[$type])&&$file){self::$cache['*'][$file]=filemtime($file);self::parseScript($file);$outerCache->save('list',self::$cache);}if(isset(self::$cache[$type][$member])){$annotations=self::$cache[$type][$member];}else{$annotations=array();}}if($r
instanceof
ReflectionMethod&&!$r->isPrivate()&&(!$r->isConstructor()||!empty($annotations['inheritdoc'][0]))){try{$inherited=self::getAll(new
ReflectionMethod(get_parent_class($type),$member));}catch(ReflectionException$e){try{$inherited=self::getAll($r->getPrototype());}catch(ReflectionException$e){$inherited=array();}}$annotations+=array_intersect_key($inherited,array_flip(self::$inherited));}return
self::$cache[$type][$member]=$annotations;}private
static
function
parseComment($comment){static$tokens=array('true'=>TRUE,'false'=>FALSE,'null'=>NULL,''=>TRUE);$res=array();$comment=preg_replace('#^\s*\*\s?#ms','',trim($comment,'/*'));$parts=preg_split('#^\s*(?=@'.self::RE_IDENTIFIER.')#m',$comment,2);$description=trim($parts[0]);if($description!==''){$res['description']=array($description);}$matches=NStrings::matchAll(isset($parts[1])?$parts[1]:'','~
				(?<=\s|^)@('.self::RE_IDENTIFIER.')[ \t]*      ##  annotation
				(
					\((?>'.self::RE_STRING.'|[^\'")@]+)+\)|  ##  (value)
					[^(@\r\n][^@\r\n]*|)                     ##  value
			~xi');foreach($matches
as$match){list(,$name,$value)=$match;if(substr($value,0,1)==='('){$items=array();$key='';$val=TRUE;$value[0]=',';while($m=NStrings::match($value,'#\s*,\s*(?>('.self::RE_IDENTIFIER.')\s*=\s*)?('.self::RE_STRING.'|[^\'"),\s][^\'"),]*)#A')){$value=substr($value,strlen($m[0]));list(,$key,$val)=$m;$val=rtrim($val);if($val[0]==="'"||$val[0]==='"'){$val=substr($val,1,-1);}elseif(is_numeric($val)){$val=1*$val;}else{$lval=strtolower($val);$val=array_key_exists($lval,$tokens)?$tokens[$lval]:$val;}if($key===''){$items[]=$val;}else{$items[$key]=$val;}}$value=count($items)<2&&$key===''?$val:$items;}else{$value=trim($value);if(is_numeric($value)){$value=1*$value;}else{$lval=strtolower($value);$value=array_key_exists($lval,$tokens)?$tokens[$lval]:$value;}}$class=$name.'Annotation';if(class_exists($class)){$res[$name][]=new$class(is_array($value)?$value:array('value'=>$value));}else{$res[$name][]=is_array($value)?new
ArrayObject($value,ArrayObject::ARRAY_AS_PROPS):$value;}}return$res;}private
static
function
parseScript($file){$T_NAMESPACE=PHP_VERSION_ID<50300?-1:T_NAMESPACE;$T_NS_SEPARATOR=PHP_VERSION_ID<50300?-1:T_NS_SEPARATOR;$s=file_get_contents($file);if(NStrings::match($s,'#//nette'.'loader=(\S*)#')){return;}$expected=$namespace=$class=$docComment=NULL;$level=$classLevel=0;foreach(token_get_all($s)as$token){if(is_array($token)){switch($token[0]){case
T_DOC_COMMENT:$docComment=$token[1];case
T_WHITESPACE:case
T_COMMENT:continue
2;case
T_STRING:case$T_NS_SEPARATOR:case
T_VARIABLE:if($expected){$name.=$token[1];}continue
2;case
T_FUNCTION:case
T_VAR:case
T_PUBLIC:case
T_PROTECTED:case$T_NAMESPACE:case
T_CLASS:case
T_INTERFACE:$expected=$token[0];$name=NULL;continue
2;case
T_STATIC:case
T_ABSTRACT:case
T_FINAL:continue
2;case
T_CURLY_OPEN:case
T_DOLLAR_OPEN_CURLY_BRACES:$level++;}}if($expected){switch($expected){case
T_CLASS:case
T_INTERFACE:$class=$namespace.$name;$classLevel=$level;$name='';case
T_FUNCTION:if($token==='&'){continue
2;}case
T_VAR:case
T_PUBLIC:case
T_PROTECTED:if($class&&$name!==NULL&&$docComment){self::$cache[$class][$name]=self::parseComment($docComment);}break;case$T_NAMESPACE:$namespace=$name.'\\';}$expected=$docComment=NULL;}if($token===';'){$docComment=NULL;}elseif($token==='{'){$docComment=NULL;$level++;}elseif($token==='}'){$level--;if($level===$classLevel){$class=NULL;}}}}static
function
setCacheStorage(ICacheStorage$storage){self::$cacheStorage=$storage;}static
function
getCacheStorage(){return
self::$cacheStorage;}}class
NExtensionReflection
extends
ReflectionExtension{function
__toString(){return'Extension '.$this->getName();}function
getClasses(){$res=array();foreach(parent::getClassNames()as$val){$res[$val]=new
NClassReflection($val);}return$res;}function
getFunctions(){foreach($res=parent::getFunctions()as$key=>$val){$res[$key]=new
NFunctionReflection($key);}return$res;}function
getReflection(){return
new
NClassReflection($this);}function
__call($name,$args){return
NObjectMixin::call($this,$name,$args);}function&__get($name){return
NObjectMixin::get($this,$name);}function
__set($name,$value){NObjectMixin::set($this,$name,$value);}function
__isset($name){return
NObjectMixin::has($this,$name);}function
__unset($name){NObjectMixin::remove($this,$name);}}class
NFunctionReflection
extends
ReflectionFunction{private$value;function
__construct($name){parent::__construct($this->value=$name);}function
toCallback(){return
new
NCallback($this->value);}function
__toString(){return'Function '.$this->getName().'()';}function
getClosure(){return$this->isClosure()?$this->value:NULL;}function
getExtension(){return($name=$this->getExtensionName())?new
NExtensionReflection($name):NULL;}function
getParameters(){foreach($res=parent::getParameters()as$key=>$val){$res[$key]=new
NParameterReflection($this->value,$val->getName());}return$res;}function
getReflection(){return
new
NClassReflection($this);}function
__call($name,$args){return
NObjectMixin::call($this,$name,$args);}function&__get($name){return
NObjectMixin::get($this,$name);}function
__set($name,$value){NObjectMixin::set($this,$name,$value);}function
__isset($name){return
NObjectMixin::has($this,$name);}function
__unset($name){NObjectMixin::remove($this,$name);}}class
NMethodReflection
extends
ReflectionMethod{static
function
from($class,$method){return
new
self(is_object($class)?get_class($class):$class,$method);}function
toCallback(){return
new
NCallback(parent::getDeclaringClass()->getName(),$this->getName());}function
__toString(){return'Method '.parent::getDeclaringClass()->getName().'::'.$this->getName().'()';}function
getDeclaringClass(){return
new
NClassReflection(parent::getDeclaringClass()->getName());}function
getPrototype(){$prototype=parent::getPrototype();return
new
NMethodReflection($prototype->getDeclaringClass()->getName(),$prototype->getName());}function
getExtension(){return($name=$this->getExtensionName())?new
NExtensionReflection($name):NULL;}function
getParameters(){$me=array(parent::getDeclaringClass()->getName(),$this->getName());foreach($res=parent::getParameters()as$key=>$val){$res[$key]=new
NParameterReflection($me,$val->getName());}return$res;}function
hasAnnotation($name){$res=NAnnotationsParser::getAll($this);return!empty($res[$name]);}function
getAnnotation($name){$res=NAnnotationsParser::getAll($this);return
isset($res[$name])?end($res[$name]):NULL;}function
getAnnotations(){return
NAnnotationsParser::getAll($this);}function
getDescription(){return$this->getAnnotation('description');}function
getReflection(){return
new
NClassReflection($this);}function
__call($name,$args){return
NObjectMixin::call($this,$name,$args);}function&__get($name){return
NObjectMixin::get($this,$name);}function
__set($name,$value){NObjectMixin::set($this,$name,$value);}function
__isset($name){return
NObjectMixin::has($this,$name);}function
__unset($name){NObjectMixin::remove($this,$name);}}class
NParameterReflection
extends
ReflectionParameter{private$function;function
__construct($function,$parameter){parent::__construct($this->function=$function,$parameter);}function
getClass(){return($ref=parent::getClass())?new
NClassReflection($ref->getName()):NULL;}function
getClassName(){try{return($ref=parent::getClass())?$ref->getName():NULL;}catch(ReflectionException$e){if(preg_match('#Class (.+) does not exist#',$e->getMessage(),$m)){return$m[1];}throw$e;}}function
getDeclaringClass(){return($ref=parent::getDeclaringClass())?new
NClassReflection($ref->getName()):NULL;}function
getDeclaringFunction(){return
is_array($this->function)?new
NMethodReflection($this->function[0],$this->function[1]):new
NFunctionReflection($this->function);}function
isDefaultValueAvailable(){if(PHP_VERSION_ID===50316){try{$this->getDefaultValue();return
TRUE;}catch(ReflectionException$e){return
FALSE;}}return
parent::isDefaultValueAvailable();}function
__toString(){return'Parameter $'.parent::getName().' in '.$this->getDeclaringFunction();}function
getReflection(){return
new
NClassReflection($this);}function
__call($name,$args){return
NObjectMixin::call($this,$name,$args);}function&__get($name){return
NObjectMixin::get($this,$name);}function
__set($name,$value){NObjectMixin::set($this,$name,$value);}function
__isset($name){return
NObjectMixin::has($this,$name);}function
__unset($name){NObjectMixin::remove($this,$name);}}class
NPropertyReflection
extends
ReflectionProperty{function
__toString(){return'Property '.parent::getDeclaringClass()->getName().'::$'.$this->getName();}function
getDeclaringClass(){return
new
NClassReflection(parent::getDeclaringClass()->getName());}function
hasAnnotation($name){$res=NAnnotationsParser::getAll($this);return!empty($res[$name]);}function
getAnnotation($name){$res=NAnnotationsParser::getAll($this);return
isset($res[$name])?end($res[$name]):NULL;}function
getAnnotations(){return
NAnnotationsParser::getAll($this);}function
getDescription(){return$this->getAnnotation('description');}function
getReflection(){return
new
NClassReflection($this);}function
__call($name,$args){return
NObjectMixin::call($this,$name,$args);}function&__get($name){return
NObjectMixin::get($this,$name);}function
__set($name,$value){NObjectMixin::set($this,$name,$value);}function
__isset($name){return
NObjectMixin::has($this,$name);}function
__unset($name){NObjectMixin::remove($this,$name);}}class
NAuthenticationException
extends
Exception{}class
NUserPanel
extends
NObject
implements
IBarPanel{private$user;function
__construct(NUser$user){$this->user=$user;}function
getTab(){ob_start();?>
<?php if($this->user->isLoggedIn()):?>
<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAJkSURBVDjLhVLPSxRhGH5mf8yOs9O6aa2b6BJhsW3RilAXDSW65clDdgwkEBH/gIiI6FC3uoRBQYeooP4Aw9isQ2xG5YZEVFrINmnFto67s7sz33xf76wedEfwgxdm4H1+vO/zSkIINL7Bax/PpxLRkXhUTVuMY/7Hci4z++2e/njofmNvYDvwqe726/2pcJsa9MMhgd7D4T5NUQ8GBibBZka3kPgaCZKk7IKbVT8qNodpcUToe6g33tadOjCyo4NYREkrpGyYHLYDMEfArHFoioTE/o70jgRVC3AIZDMqLogA9fKR12qVefblGWHui54rmDZCsoSaLVClUkMSVlYZZl7P53YkyGQ/T9+dWqoaFY6K5ZaDEo1w42GOVWaz7xv7pc0x9kxkh/uOxa6c6JSSnDz/MgJgFGM0ZCLALTzKrhZePnh1S+gXr3p2cHQ0kx7oSVwePtmWbNUCKFsCKb6+i3K1GXKQY2JfrCW/XJqQfGNvBL/9bMsILRF1/MzxWGo3RfbHoK3VjUkgDlhEsqDXEKJ0Lgx2tSJ56JJnB13tLf3NYR9+F20CCwJSuSnw9W8hJHxdMtHeqiAYix/xEGia0ilLPuRXKnVVx41vYwRG6XEOGGsMst8PWVF3eXZgWUyixChvCc6GMiNwja7RJjR3x3GLRFwyj4PFvPFzQTehNUn1f4e6LIfXCdxDovGR2BvEh+9lVArFNQ/BdCY/Pjq5eGfqbQGC1IPkpEkGwnREMvl09/DkxQpuPs0beDd3ets7cF/HuefL8ViU7YnIYbpcTS+Y0P9apXLe+IeSWRSfzvZs7v8PV6U0ly704DwAAAAASUVORK5CYII=" style="margin-right:0" />&nbsp;
<?php else:?>
<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAE4SURBVCjPZdBLSwIBGIXh/lHQb4guyza1CEIqpNoIQdHKXEQQrkS6IUSLFhYFtpCIwUAG07IstTTnqjNTjnSRZmPg4m3lpYZvd84DB74BBjq36zkXk07CORB9nl7aVydtkwZ1NKL2tMcFYqLJOxYGb1QIiC5w5dhYGOgo6EQcFxCcOjV0VCRUdtxgX1R4RaZClTzz7okF/2FLo0SRChvtkdA/sDl1Wk6RQuASAYHg54S/D6wPnjzrNLAwqVJBJsfax/BoFwQjZWw0LEx0SmQocsGk2AVHko6MhoGByhMZEqSZ++qCs5bBLSo1qkgUSBMny1K7C45/qtwho6NQ4oFr4mRZ7IGwmqWAjMILee65IUWMmd6Ed3xlL4qEjkqZR9KE8X2PDf151Kq9ZW03Q+1Ae7np1WZznfwXGfNkzblrzUIAAAAASUVORK5CYII=" style="margin-right:0" />&nbsp;
<?php endif?>
<?php
return
ob_get_clean();}function
getPanel(){ob_start();?>
<style class="nette-debug">#nette-debug .nette-UserPanel pre{background:#FDF5CE;padding:.4em .7em;border:1px dotted silver;overflow:auto}</style>

<div class="nette-UserPanel">
	<h1><?php if($this->user->isLoggedIn()):?>Logged in<?php else:?>Unlogged<?php endif?></h1>

	<?php if($this->user->getIdentity()):echo
NDebugHelpers::clickableDump($this->user->getIdentity());else:?><p>no identity</p><?php endif?>
</div>
<?php
return
ob_get_clean();}}class
NIdentity
extends
NFreezableObject
implements
IIdentity{private$id;private$roles;private$data;function
__construct($id,$roles=NULL,$data=NULL){$this->setId($id);$this->setRoles((array)$roles);$this->data=$data
instanceof
Traversable?iterator_to_array($data):(array)$data;}function
setId($id){$this->updating();$this->id=is_numeric($id)?1*$id:$id;return$this;}function
getId(){return$this->id;}function
setRoles(array$roles){$this->updating();$this->roles=$roles;return$this;}function
getRoles(){return$this->roles;}function
getData(){return$this->data;}function
__set($key,$value){$this->updating();if(parent::__isset($key)){parent::__set($key,$value);}else{$this->data[$key]=$value;}}function&__get($key){if(parent::__isset($key)){return
parent::__get($key);}else{return$this->data[$key];}}function
__isset($key){return
isset($this->data[$key])||parent::__isset($key);}function
__unset($name){NObjectMixin::remove($this,$name);}}class
NPermission
extends
NObject
implements
IAuthorizator{private$roles=array();private$resources=array();private$rules=array('allResources'=>array('allRoles'=>array('allPrivileges'=>array('type'=>self::DENY,'assert'=>NULL),'byPrivilege'=>array()),'byRole'=>array()),'byResource'=>array());private$queriedRole,$queriedResource;function
addRole($role,$parents=NULL){$this->checkRole($role,FALSE);if(isset($this->roles[$role])){throw
new
InvalidStateException("Role '$role' already exists in the list.");}$roleParents=array();if($parents!==NULL){if(!is_array($parents)){$parents=array($parents);}foreach($parents
as$parent){$this->checkRole($parent);$roleParents[$parent]=TRUE;$this->roles[$parent]['children'][$role]=TRUE;}}$this->roles[$role]=array('parents'=>$roleParents,'children'=>array());return$this;}function
hasRole($role){$this->checkRole($role,FALSE);return
isset($this->roles[$role]);}private
function
checkRole($role,$need=TRUE){if(!is_string($role)||$role===''){throw
new
InvalidArgumentException('Role must be a non-empty string.');}elseif($need&&!isset($this->roles[$role])){throw
new
InvalidStateException("Role '$role' does not exist.");}}function
getRoles(){return
array_keys($this->roles);}function
getRoleParents($role){$this->checkRole($role);return
array_keys($this->roles[$role]['parents']);}function
roleInheritsFrom($role,$inherit,$onlyParents=FALSE){$this->checkRole($role);$this->checkRole($inherit);$inherits=isset($this->roles[$role]['parents'][$inherit]);if($inherits||$onlyParents){return$inherits;}foreach($this->roles[$role]['parents']as$parent=>$foo){if($this->roleInheritsFrom($parent,$inherit)){return
TRUE;}}return
FALSE;}function
removeRole($role){$this->checkRole($role);foreach($this->roles[$role]['children']as$child=>$foo){unset($this->roles[$child]['parents'][$role]);}foreach($this->roles[$role]['parents']as$parent=>$foo){unset($this->roles[$parent]['children'][$role]);}unset($this->roles[$role]);foreach($this->rules['allResources']['byRole']as$roleCurrent=>$rules){if($role===$roleCurrent){unset($this->rules['allResources']['byRole'][$roleCurrent]);}}foreach($this->rules['byResource']as$resourceCurrent=>$visitor){if(isset($visitor['byRole'])){foreach($visitor['byRole']as$roleCurrent=>$rules){if($role===$roleCurrent){unset($this->rules['byResource'][$resourceCurrent]['byRole'][$roleCurrent]);}}}}return$this;}function
removeAllRoles(){$this->roles=array();foreach($this->rules['allResources']['byRole']as$roleCurrent=>$rules){unset($this->rules['allResources']['byRole'][$roleCurrent]);}foreach($this->rules['byResource']as$resourceCurrent=>$visitor){foreach($visitor['byRole']as$roleCurrent=>$rules){unset($this->rules['byResource'][$resourceCurrent]['byRole'][$roleCurrent]);}}return$this;}function
addResource($resource,$parent=NULL){$this->checkResource($resource,FALSE);if(isset($this->resources[$resource])){throw
new
InvalidStateException("Resource '$resource' already exists in the list.");}if($parent!==NULL){$this->checkResource($parent);$this->resources[$parent]['children'][$resource]=TRUE;}$this->resources[$resource]=array('parent'=>$parent,'children'=>array());return$this;}function
hasResource($resource){$this->checkResource($resource,FALSE);return
isset($this->resources[$resource]);}private
function
checkResource($resource,$need=TRUE){if(!is_string($resource)||$resource===''){throw
new
InvalidArgumentException('Resource must be a non-empty string.');}elseif($need&&!isset($this->resources[$resource])){throw
new
InvalidStateException("Resource '$resource' does not exist.");}}function
getResources(){return
array_keys($this->resources);}function
resourceInheritsFrom($resource,$inherit,$onlyParent=FALSE){$this->checkResource($resource);$this->checkResource($inherit);if($this->resources[$resource]['parent']===NULL){return
FALSE;}$parent=$this->resources[$resource]['parent'];if($inherit===$parent){return
TRUE;}elseif($onlyParent){return
FALSE;}while($this->resources[$parent]['parent']!==NULL){$parent=$this->resources[$parent]['parent'];if($inherit===$parent){return
TRUE;}}return
FALSE;}function
removeResource($resource){$this->checkResource($resource);$parent=$this->resources[$resource]['parent'];if($parent!==NULL){unset($this->resources[$parent]['children'][$resource]);}$removed=array($resource);foreach($this->resources[$resource]['children']as$child=>$foo){$this->removeResource($child);$removed[]=$child;}foreach($removed
as$resourceRemoved){foreach($this->rules['byResource']as$resourceCurrent=>$rules){if($resourceRemoved===$resourceCurrent){unset($this->rules['byResource'][$resourceCurrent]);}}}unset($this->resources[$resource]);return$this;}function
removeAllResources(){foreach($this->resources
as$resource=>$foo){foreach($this->rules['byResource']as$resourceCurrent=>$rules){if($resource===$resourceCurrent){unset($this->rules['byResource'][$resourceCurrent]);}}}$this->resources=array();return$this;}function
allow($roles=self::ALL,$resources=self::ALL,$privileges=self::ALL,$assertion=NULL){$this->setRule(TRUE,self::ALLOW,$roles,$resources,$privileges,$assertion);return$this;}function
deny($roles=self::ALL,$resources=self::ALL,$privileges=self::ALL,$assertion=NULL){$this->setRule(TRUE,self::DENY,$roles,$resources,$privileges,$assertion);return$this;}function
removeAllow($roles=self::ALL,$resources=self::ALL,$privileges=self::ALL){$this->setRule(FALSE,self::ALLOW,$roles,$resources,$privileges);return$this;}function
removeDeny($roles=self::ALL,$resources=self::ALL,$privileges=self::ALL){$this->setRule(FALSE,self::DENY,$roles,$resources,$privileges);return$this;}protected
function
setRule($toAdd,$type,$roles,$resources,$privileges,$assertion=NULL){if($roles===self::ALL){$roles=array(self::ALL);}else{if(!is_array($roles)){$roles=array($roles);}foreach($roles
as$role){$this->checkRole($role);}}if($resources===self::ALL){$resources=array(self::ALL);}else{if(!is_array($resources)){$resources=array($resources);}foreach($resources
as$resource){$this->checkResource($resource);}}if($privileges===self::ALL){$privileges=array();}elseif(!is_array($privileges)){$privileges=array($privileges);}$assertion=$assertion?new
NCallback($assertion):NULL;if($toAdd){foreach($resources
as$resource){foreach($roles
as$role){$rules=&$this->getRules($resource,$role,TRUE);if(count($privileges)===0){$rules['allPrivileges']['type']=$type;$rules['allPrivileges']['assert']=$assertion;if(!isset($rules['byPrivilege'])){$rules['byPrivilege']=array();}}else{foreach($privileges
as$privilege){$rules['byPrivilege'][$privilege]['type']=$type;$rules['byPrivilege'][$privilege]['assert']=$assertion;}}}}}else{foreach($resources
as$resource){foreach($roles
as$role){$rules=&$this->getRules($resource,$role);if($rules===NULL){continue;}if(count($privileges)===0){if($resource===self::ALL&&$role===self::ALL){if($type===$rules['allPrivileges']['type']){$rules=array('allPrivileges'=>array('type'=>self::DENY,'assert'=>NULL),'byPrivilege'=>array());}continue;}if($type===$rules['allPrivileges']['type']){unset($rules['allPrivileges']);}}else{foreach($privileges
as$privilege){if(isset($rules['byPrivilege'][$privilege])&&$type===$rules['byPrivilege'][$privilege]['type']){unset($rules['byPrivilege'][$privilege]);}}}}}}return$this;}function
isAllowed($role=self::ALL,$resource=self::ALL,$privilege=self::ALL){$this->queriedRole=$role;if($role!==self::ALL){if($role
instanceof
IRole){$role=$role->getRoleId();}$this->checkRole($role);}$this->queriedResource=$resource;if($resource!==self::ALL){if($resource
instanceof
IResource){$resource=$resource->getResourceId();}$this->checkResource($resource);}do{if($role!==NULL&&NULL!==($result=$this->searchRolePrivileges($privilege===self::ALL,$role,$resource,$privilege))){break;}if($privilege===self::ALL){if($rules=$this->getRules($resource,self::ALL)){foreach($rules['byPrivilege']as$privilege=>$rule){if(self::DENY===($result=$this->getRuleType($resource,NULL,$privilege))){break
2;}}if(NULL!==($result=$this->getRuleType($resource,NULL,NULL))){break;}}}else{if(NULL!==($result=$this->getRuleType($resource,NULL,$privilege))){break;}elseif(NULL!==($result=$this->getRuleType($resource,NULL,NULL))){break;}}$resource=$this->resources[$resource]['parent'];}while(TRUE);$this->queriedRole=$this->queriedResource=NULL;return$result;}function
getQueriedRole(){return$this->queriedRole;}function
getQueriedResource(){return$this->queriedResource;}private
function
searchRolePrivileges($all,$role,$resource,$privilege){$dfs=array('visited'=>array(),'stack'=>array($role));while(NULL!==($role=array_pop($dfs['stack']))){if(isset($dfs['visited'][$role])){continue;}if($all){if($rules=$this->getRules($resource,$role)){foreach($rules['byPrivilege']as$privilege2=>$rule){if(self::DENY===$this->getRuleType($resource,$role,$privilege2)){return
self::DENY;}}if(NULL!==($type=$this->getRuleType($resource,$role,NULL))){return$type;}}}else{if(NULL!==($type=$this->getRuleType($resource,$role,$privilege))){return$type;}elseif(NULL!==($type=$this->getRuleType($resource,$role,NULL))){return$type;}}$dfs['visited'][$role]=TRUE;foreach($this->roles[$role]['parents']as$roleParent=>$foo){$dfs['stack'][]=$roleParent;}}return
NULL;}private
function
getRuleType($resource,$role,$privilege){if(!$rules=$this->getRules($resource,$role)){return
NULL;}if($privilege===self::ALL){if(isset($rules['allPrivileges'])){$rule=$rules['allPrivileges'];}else{return
NULL;}}elseif(!isset($rules['byPrivilege'][$privilege])){return
NULL;}else{$rule=$rules['byPrivilege'][$privilege];}if($rule['assert']===NULL||$rule['assert']->__invoke($this,$role,$resource,$privilege)){return$rule['type'];}elseif($resource!==self::ALL||$role!==self::ALL||$privilege!==self::ALL){return
NULL;}elseif(self::ALLOW===$rule['type']){return
self::DENY;}else{return
self::ALLOW;}}private
function&getRules($resource,$role,$create=FALSE){$null=NULL;if($resource===self::ALL){$visitor=&$this->rules['allResources'];}else{if(!isset($this->rules['byResource'][$resource])){if(!$create){return$null;}$this->rules['byResource'][$resource]=array();}$visitor=&$this->rules['byResource'][$resource];}if($role===self::ALL){if(!isset($visitor['allRoles'])){if(!$create){return$null;}$visitor['allRoles']['byPrivilege']=array();}return$visitor['allRoles'];}if(!isset($visitor['byRole'][$role])){if(!$create){return$null;}$visitor['byRole'][$role]['byPrivilege']=array();}return$visitor['byRole'][$role];}}class
NSimpleAuthenticator
extends
NObject
implements
IAuthenticator{private$userlist;function
__construct(array$userlist){$this->userlist=$userlist;}function
authenticate(array$credentials){list($username,$password)=$credentials;foreach($this->userlist
as$name=>$pass){if(strcasecmp($name,$username)===0){if((string)$pass===(string)$password){return
new
NIdentity($name);}else{throw
new
NAuthenticationException('Invalid password.',self::INVALID_CREDENTIAL);}}}throw
new
NAuthenticationException("User '$username' not found.",self::IDENTITY_NOT_FOUND);}}class
NUser
extends
NObject{public$guestRole='guest';public$authenticatedRole='authenticated';public$onLoggedIn;public$onLoggedOut;private$storage;private$authenticator;private$authorizator;private$context;function
__construct(IUserStorage$storage,NDIContainer$context){$this->storage=$storage;$this->context=$context;}function
getStorage(){return$this->storage;}function
login($id=NULL,$password=NULL){$this->logout(TRUE);if(!$id
instanceof
IIdentity){$id=$this->getAuthenticator()->authenticate(func_get_args());}$this->storage->setIdentity($id);$this->storage->setAuthenticated(TRUE);$this->onLoggedIn($this);}function
logout($clearIdentity=FALSE){if($this->isLoggedIn()){$this->onLoggedOut($this);$this->storage->setAuthenticated(FALSE);}if($clearIdentity){$this->storage->setIdentity(NULL);}}function
isLoggedIn(){return$this->storage->isAuthenticated();}function
getIdentity(){return$this->storage->getIdentity();}function
getId(){$identity=$this->getIdentity();return$identity?$identity->getId():NULL;}function
setAuthenticator(IAuthenticator$handler){$this->authenticator=$handler;return$this;}function
getAuthenticator(){return($tmp=$this->authenticator)?$tmp:$this->context->getByType('IAuthenticator');}function
setExpiration($time,$whenBrowserIsClosed=TRUE,$clearIdentity=FALSE){$flags=($whenBrowserIsClosed?IUserStorage::BROWSER_CLOSED:0)|($clearIdentity?IUserStorage::CLEAR_IDENTITY:0);$this->storage->setExpiration($time,$flags);return$this;}function
getLogoutReason(){return$this->storage->getLogoutReason();}function
getRoles(){if(!$this->isLoggedIn()){return
array($this->guestRole);}$identity=$this->getIdentity();return$identity&&$identity->getRoles()?$identity->getRoles():array($this->authenticatedRole);}function
isInRole($role){return
in_array($role,$this->getRoles(),TRUE);}function
isAllowed($resource=IAuthorizator::ALL,$privilege=IAuthorizator::ALL){$authorizator=$this->getAuthorizator();foreach($this->getRoles()as$role){if($authorizator->isAllowed($role,$resource,$privilege)){return
TRUE;}}return
FALSE;}function
setAuthorizator(IAuthorizator$handler){$this->authorizator=$handler;return$this;}function
getAuthorizator(){return($tmp=$this->authorizator)?$tmp:$this->context->getByType('IAuthorizator');}function
setNamespace($namespace){trigger_error(__METHOD__.'() is deprecated; use getStorage()->setNamespace() instead.',E_USER_WARNING);$this->storage->setNamespace($namespace);return$this;}function
getNamespace(){trigger_error(__METHOD__.'() is deprecated; use getStorage()->getNamespace() instead.',E_USER_WARNING);return$this->storage->getNamespace();}function
setAuthenticationHandler($v){trigger_error(__METHOD__.'() is deprecated; use setAuthenticator() instead.',E_USER_WARNING);return$this->setAuthenticator($v);}function
setAuthorizationHandler($v){trigger_error(__METHOD__.'() is deprecated; use setAuthorizator() instead.',E_USER_WARNING);return$this->setAuthorizator($v);}}class
NTemplate
extends
NObject
implements
ITemplate{public$onPrepareFilters=array();private$source;private$params=array();private$filters=array();private$helpers=array();private$helperLoaders=array();private$cacheStorage;function
setSource($source){$this->source=$source;return$this;}function
getSource(){return$this->source;}function
render(){$cache=new
NCache($storage=$this->getCacheStorage(),'Nette.Template');$cached=$compiled=$cache->load($this->source);if($compiled===NULL){$compiled=$this->compile();$cache->save($this->source,$compiled,array(NCache::CONSTS=>'NFramework::REVISION'));$cached=$cache->load($this->source);}if($cached!==NULL&&$storage
instanceof
NPhpFileStorage){NLimitedScope::load($cached['file'],$this->getParameters());}else{NLimitedScope::evaluate($compiled,$this->getParameters());}}function
save($file){if(file_put_contents($file,$this->__toString(TRUE))===FALSE){throw
new
IOException("Unable to save file '$file'.");}}function
__toString(){ob_start();try{$this->render();return
ob_get_clean();}catch(Exception$e){ob_end_clean();if(func_num_args()){throw$e;}trigger_error("Exception in ".__METHOD__."(): {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}",E_USER_ERROR);}}function
compile(){if(!$this->filters){$this->onPrepareFilters($this);}$code=$this->getSource();foreach($this->filters
as$filter){$code=self::extractPhp($code,$blocks);$code=$filter->invoke($code);$code=strtr($code,$blocks);}return
NTemplateHelpers::optimizePhp($code);}function
registerFilter($callback){$callback=new
NCallback($callback);if(in_array($callback,$this->filters)){throw
new
InvalidStateException("Filter '$callback' was registered twice.");}$this->filters[]=$callback;return$this;}function
getFilters(){return$this->filters;}function
registerHelper($name,$callback){$this->helpers[strtolower($name)]=new
NCallback($callback);return$this;}function
registerHelperLoader($callback){$this->helperLoaders[]=new
NCallback($callback);return$this;}function
getHelpers(){return$this->helpers;}function
getHelperLoaders(){return$this->helperLoaders;}function
__call($name,$args){$lname=strtolower($name);if(!isset($this->helpers[$lname])){foreach($this->helperLoaders
as$loader){$helper=$loader->invoke($lname);if($helper){$this->registerHelper($lname,$helper);return$this->helpers[$lname]->invokeArgs($args);}}return
parent::__call($name,$args);}return$this->helpers[$lname]->invokeArgs($args);}function
setTranslator(ITranslator$translator=NULL){$this->registerHelper('translate',$translator===NULL?NULL:array($translator,'translate'));return$this;}function
add($name,$value){if(array_key_exists($name,$this->params)){throw
new
InvalidStateException("The variable '$name' already exists.");}$this->params[$name]=$value;return$this;}function
setParameters(array$params){$this->params=$params+$this->params;return$this;}function
getParameters(){$this->params['template']=$this;return$this->params;}function
setParams(array$params){trigger_error(__METHOD__.'() is deprecated; use setParameters() instead.',E_USER_WARNING);return$this->setParameters($params);}function
getParams(){trigger_error(__METHOD__.'() is deprecated; use getParameters() instead.',E_USER_WARNING);return$this->getParameters();}function
__set($name,$value){$this->params[$name]=$value;}function&__get($name){if(!array_key_exists($name,$this->params)){trigger_error("The variable '$name' does not exist in template.",E_USER_NOTICE);}return$this->params[$name];}function
__isset($name){return
isset($this->params[$name]);}function
__unset($name){unset($this->params[$name]);}function
setCacheStorage(ICacheStorage$storage){$this->cacheStorage=$storage;return$this;}function
getCacheStorage(){if($this->cacheStorage===NULL){return
new
NDevNullStorage;}return$this->cacheStorage;}private
static
function
extractPhp($source,&$blocks){$res='';$blocks=array();$tokens=token_get_all($source);foreach($tokens
as$n=>$token){if(is_array($token)){if($token[0]===T_INLINE_HTML){$res.=$token[1];continue;}elseif($token[0]===T_CLOSE_TAG){if($php!==$res){$res.=str_repeat("\n",substr_count($php,"\n"));}$res.=$token[1];continue;}elseif($token[0]===T_OPEN_TAG&&$token[1]==='<?'&&isset($tokens[$n+1][1])&&$tokens[$n+1][1]==='xml'){$php=&$res;$token[1]='<<?php ?>?';}elseif($token[0]===T_OPEN_TAG||$token[0]===T_OPEN_TAG_WITH_ECHO){$res.=$id="<?php \x01@php:p".count($blocks)."@\x02";$php=&$blocks[$id];}$php.=$token[1];}else{$php.=$token;}}return$res;}}class
NFileTemplate
extends
NTemplate
implements
IFileTemplate{private$file;function
__construct($file=NULL){if($file!==NULL){$this->setFile($file);}}function
setFile($file){$this->file=realpath($file);if(!$this->file){throw
new
FileNotFoundException("Missing template file '$file'.");}return$this;}function
getFile(){return$this->file;}function
getSource(){return
file_get_contents($this->file);}function
render(){if($this->file==NULL){throw
new
InvalidStateException("Template file name was not specified.");}$cache=new
NCache($storage=$this->getCacheStorage(),'Nette.FileTemplate');if($storage
instanceof
NPhpFileStorage){$storage->hint=str_replace(dirname(dirname($this->file)),'',$this->file);}$cached=$compiled=$cache->load($this->file);if($compiled===NULL){try{$compiled="<?php\n\n// source file: $this->file\n\n?>".$this->compile();}catch(NTemplateException$e){$e->setSourceFile($this->file);throw$e;}$cache->save($this->file,$compiled,array(NCache::FILES=>$this->file,NCache::CONSTS=>'NFramework::REVISION'));$cached=$cache->load($this->file);}if($cached!==NULL&&$storage
instanceof
NPhpFileStorage){NLimitedScope::load($cached['file'],$this->getParameters());}else{NLimitedScope::evaluate($compiled,$this->getParameters());}}}class
NTemplateHelpers{private
static$helpers=array('normalize'=>'NStrings::normalize','toascii'=>'NStrings::toAscii','webalize'=>'NStrings::webalize','truncate'=>'NStrings::truncate','lower'=>'NStrings::lower','upper'=>'NStrings::upper','firstupper'=>'NStrings::firstUpper','capitalize'=>'NStrings::capitalize','trim'=>'NStrings::trim','padleft'=>'NStrings::padLeft','padright'=>'NStrings::padRight','reverse'=>'NStrings::reverse','replacere'=>'NStrings::replace','url'=>'rawurlencode','striptags'=>'strip_tags','substr'=>'NStrings::substring','repeat'=>'str_repeat','implode'=>'implode','number'=>'number_format');public
static$dateFormat='%x';static
function
loader($helper){if(method_exists(__CLASS__,$helper)){return
new
NCallback(__CLASS__,$helper);}elseif(isset(self::$helpers[$helper])){return
self::$helpers[$helper];}}static
function
escapeHtml($s,$quotes=ENT_QUOTES){if($quotes===ENT_NOQUOTES&&($s
instanceof
ITemplate||$s
instanceof
NHtml||$s
instanceof
NForm)){return$s->__toString(TRUE);}$s=(string)$s;if($quotes!==ENT_NOQUOTES&&strpos($s,'`')!==FALSE&&strpbrk($s,' <>"\'')===FALSE){$s.=' ';}return
htmlSpecialChars($s,$quotes);}static
function
escapeHtmlComment($s){$s=(string)$s;if($s&&($s[0]==='-'||$s[0]==='>'||$s[0]==='!')){$s=' '.$s;}return
str_replace('-','- ',$s);}static
function
escapeXML($s){return
htmlSpecialChars(preg_replace('#[\x00-\x08\x0B\x0C\x0E-\x1F]+#','',$s),ENT_QUOTES);}static
function
escapeCss($s){return
addcslashes($s,"\x00..\x1F!\"#$%&'()*+,./:;<=>?@[\\]^`{|}~");}static
function
escapeJs($s){if(is_object($s)&&($s
instanceof
ITemplate||$s
instanceof
NHtml||$s
instanceof
NForm)){$s=$s->__toString(TRUE);}return
str_replace(array(']]>','<!'),array(']]\x3E','\x3C!'),NJson::encode($s));}static
function
escapeICal($s){return
addcslashes(preg_replace('#[\x00-\x08\x0B\x0C-\x1F]+#','',$s),"\";\\,:\n");}static
function
safeUrl($s){return
preg_match('~^(?:(?:https?|ftp)://[^@]+(?:/.*)?|mailto:.+|[/?#].*|[^:]+)\z~i',$s)?$s:'';}static
function
strip($s){return
NStrings::replace($s,'#(</textarea|</pre|</script|^).*?(?=<textarea|<pre|<script|\z)#si',create_function('$m','
				return trim(preg_replace(\'#[ \\t\\r\\n]+#\', " ", $m[0]));
			'));}static
function
indent($s,$level=1,$chars="\t"){if($level>=1){$s=NStrings::replace($s,'#<(textarea|pre).*?</\\1#si',create_function('$m','
				return strtr($m[0], " \\t\\r\\n", "\\x1F\\x1E\\x1D\\x1A");
			'));$s=NStrings::indent($s,$level,$chars);$s=strtr($s,"\x1F\x1E\x1D\x1A"," \t\r\n");}return$s;}static
function
date($time,$format=NULL){if($time==NULL){return
NULL;}if(!isset($format)){$format=self::$dateFormat;}$time=NDateTime53::from($time);return
NStrings::contains($format,'%')?strftime($format,$time->format('U')):$time->format($format);}static
function
bytes($bytes,$precision=2){$bytes=round($bytes);$units=array('B','kB','MB','GB','TB','PB');foreach($units
as$unit){if(abs($bytes)<1024||$unit===end($units)){break;}$bytes=$bytes/1024;}return
round($bytes,$precision).' '.$unit;}static
function
length($var){return
is_string($var)?NStrings::length($var):count($var);}static
function
replace($subject,$search,$replacement=''){return
str_replace($search,$replacement,$subject);}static
function
dataStream($data,$type=NULL){if($type===NULL){$type=NMimeTypeDetector::fromString($data);}return'data:'.($type?"$type;":'').'base64,'.base64_encode($data);}static
function
null(){return'';}static
function
nl2br($value){return
nl2br($value,NHtml::$xhtml);}static
function
optimizePhp($source,$lineLength=80,$existenceOfThisParameterSolvesDamnBugInPHP535=NULL){$res=$php='';$lastChar=';';$tokens=new
ArrayIterator(token_get_all($source));foreach($tokens
as$key=>$token){if(is_array($token)){if($token[0]===T_INLINE_HTML){$lastChar='';$res.=$token[1];}elseif($token[0]===T_CLOSE_TAG){$next=isset($tokens[$key+1])?$tokens[$key+1]:NULL;if(substr($res,-1)!=='<'&&preg_match('#^<\?php\s*\z#',$php)){$php='';}elseif(is_array($next)&&$next[0]===T_OPEN_TAG){if(!strspn($lastChar,';{}:/')){$php.=$lastChar=';';}if(substr($next[1],-1)==="\n"){$php.="\n";}$tokens->next();}elseif($next){$res.=preg_replace('#;?(\s)*\z#','$1',$php).$token[1];if(strlen($res)-strrpos($res,"\n")>$lineLength&&(!is_array($next)||strpos($next[1],"\n")===FALSE)){$res.="\n";}$php='';}else{if(!strspn($lastChar,'};')){$php.=';';}}}elseif($token[0]===T_ELSE||$token[0]===T_ELSEIF){if($tokens[$key+1]===':'&&$lastChar==='}'){$php.=';';}$lastChar='';$php.=$token[1];}else{if(!in_array($token[0],array(T_WHITESPACE,T_COMMENT,T_DOC_COMMENT,T_OPEN_TAG))){$lastChar='';}$php.=$token[1];}}else{$php.=$lastChar=$token;}}return$res.$php;}}class
NArrays{final
function
__construct(){throw
new
NStaticClassException;}static
function
get(array$arr,$key,$default=NULL){foreach(is_array($key)?$key:array($key)as$k){if(is_array($arr)&&array_key_exists($k,$arr)){$arr=$arr[$k];}else{if(func_num_args()<3){throw
new
InvalidArgumentException("Missing item '$k'.");}return$default;}}return$arr;}static
function&getRef(&$arr,$key){foreach(is_array($key)?$key:array($key)as$k){if(is_array($arr)||$arr===NULL){$arr=&$arr[$k];}else{throw
new
InvalidArgumentException('Traversed item is not an array.');}}return$arr;}static
function
mergeTree($arr1,$arr2){$res=$arr1+$arr2;foreach(array_intersect_key($arr1,$arr2)as$k=>$v){if(is_array($v)&&is_array($arr2[$k])){$res[$k]=self::mergeTree($v,$arr2[$k]);}}return$res;}static
function
searchKey($arr,$key){$foo=array($key=>NULL);return
array_search(key($foo),array_keys($arr),TRUE);}static
function
insertBefore(array&$arr,$key,array$inserted){$offset=self::searchKey($arr,$key);$arr=array_slice($arr,0,$offset,TRUE)+$inserted+array_slice($arr,$offset,count($arr),TRUE);}static
function
insertAfter(array&$arr,$key,array$inserted){$offset=self::searchKey($arr,$key);$offset=$offset===FALSE?count($arr):$offset+1;$arr=array_slice($arr,0,$offset,TRUE)+$inserted+array_slice($arr,$offset,count($arr),TRUE);}static
function
renameKey(array&$arr,$oldKey,$newKey){$offset=self::searchKey($arr,$oldKey);if($offset!==FALSE){$keys=array_keys($arr);$keys[$offset]=$newKey;$arr=array_combine($keys,$arr);}}static
function
grep(array$arr,$pattern,$flags=0){set_error_handler(create_function('$severity, $message','extract($GLOBALS[0]['.array_push($GLOBALS[0],array('pattern'=>$pattern)).'-1], EXTR_REFS); // preg_last_error does not return compile errors
			restore_error_handler();
			throw new NRegexpException("$message in pattern: $pattern");
		'));$res=preg_grep($pattern,$arr,$flags);restore_error_handler();if(preg_last_error()){throw
new
NRegexpException(NULL,preg_last_error(),$pattern);}return$res;}static
function
flatten(array$arr){$res=array();array_walk_recursive($arr,create_function('$a','extract($GLOBALS[0]['.array_push($GLOBALS[0],array('res'=>&$res)).'-1], EXTR_REFS); $res[] = $a; '));return$res;}}class
NFinder
extends
NObject
implements
IteratorAggregate{private$paths=array();private$groups;private$exclude=array();private$order=RecursiveIteratorIterator::SELF_FIRST;private$maxDepth=-1;private$cursor;static
function
find($mask){if(!is_array($mask)){$mask=func_get_args();}$finder=new
self;return$finder->select(array(),'isDir')->select($mask,'isFile');}static
function
findFiles($mask){if(!is_array($mask)){$mask=func_get_args();}$finder=new
self;return$finder->select($mask,'isFile');}static
function
findDirectories($mask){if(!is_array($mask)){$mask=func_get_args();}$finder=new
self;return$finder->select($mask,'isDir');}private
function
select($masks,$type){$this->cursor=&$this->groups[];$pattern=self::buildPattern($masks);if($type||$pattern){$this->filter(create_function('$file','extract($GLOBALS[0]['.array_push($GLOBALS[0],array('type'=>$type,'pattern'=>$pattern)).'-1], EXTR_REFS);
				return !$file->isDot()
					&& (!$type || $file->$type())
					&& (!$pattern || preg_match($pattern, \'/\' . strtr($file->getSubPathName(), \'\\\\\', \'/\')));
			'));}return$this;}function
in($path){if(!is_array($path)){$path=func_get_args();}$this->maxDepth=0;return$this->from($path);}function
from($path){if($this->paths){throw
new
InvalidStateException('Directory to search has already been specified.');}if(!is_array($path)){$path=func_get_args();}$this->paths=$path;$this->cursor=&$this->exclude;return$this;}function
childFirst(){$this->order=RecursiveIteratorIterator::CHILD_FIRST;return$this;}private
static
function
buildPattern($masks){$pattern=array();foreach($masks
as$mask){$mask=rtrim(strtr($mask,'\\','/'),'/');$prefix='';if($mask===''){continue;}elseif($mask==='*'){return
NULL;}elseif($mask[0]==='/'){$mask=ltrim($mask,'/');$prefix='(?<=^/)';}$pattern[]=$prefix.strtr(preg_quote($mask,'#'),array('\*\*'=>'.*','\*'=>'[^/]*','\?'=>'[^/]','\[\!'=>'[^','\['=>'[','\]'=>']','\-'=>'-'));}return$pattern?'#/('.implode('|',$pattern).')\z#i':NULL;}function
getIterator(){if(!$this->paths){throw
new
InvalidStateException('Call in() or from() to specify directory to search.');}elseif(count($this->paths)===1){return$this->buildIterator($this->paths[0]);}else{$iterator=new
AppendIterator();$iterator->append($workaround=new
ArrayIterator(array('workaround PHP bugs #49104, #63077')));foreach($this->paths
as$path){$iterator->append($this->buildIterator($path));}unset($workaround[0]);return$iterator;}}private
function
buildIterator($path){if(PHP_VERSION_ID<50301){$iterator=new
NRecursiveDirectoryIteratorFixed($path);}else{$iterator=new
RecursiveDirectoryIterator($path,RecursiveDirectoryIterator::FOLLOW_SYMLINKS);}if($this->exclude){$filters=$this->exclude;$iterator=new
NNRecursiveCallbackFilterIterator($iterator,create_function('$file','extract($GLOBALS[0]['.array_push($GLOBALS[0],array('filters'=>$filters)).'-1], EXTR_REFS);
				if (!$file->isDot() && !$file->isFile()) {
					foreach ($filters as $filter) {
						if (!call_user_func($filter, $file)) {
							return FALSE;
						}
					}
				}
				return TRUE;
			'));}if($this->maxDepth!==0){$iterator=new
RecursiveIteratorIterator($iterator,$this->order);$iterator->setMaxDepth($this->maxDepth);}if($this->groups){$groups=$this->groups;$iterator=new
NNCallbackFilterIterator($iterator,create_function('$file','extract($GLOBALS[0]['.array_push($GLOBALS[0],array('groups'=>$groups)).'-1], EXTR_REFS);
				foreach ($groups as $filters) {
					foreach ($filters as $filter) {
						if (!call_user_func($filter, $file)) {
							continue 2;
						}
					}
					return TRUE;
				}
				return FALSE;
			'));}return$iterator;}function
exclude($masks){if(!is_array($masks)){$masks=func_get_args();}$pattern=self::buildPattern($masks);if($pattern){$this->filter(create_function('$file','extract($GLOBALS[0]['.array_push($GLOBALS[0],array('pattern'=>$pattern)).'-1], EXTR_REFS);
				return !preg_match($pattern, \'/\' . strtr($file->getSubPathName(), \'\\\\\', \'/\'));
			'));}return$this;}function
filter($callback){$this->cursor[]=$callback;return$this;}function
limitDepth($depth){$this->maxDepth=$depth;return$this;}function
size($operator,$size=NULL){if(func_num_args()===1){if(!preg_match('#^(?:([=<>!]=?|<>)\s*)?((?:\d*\.)?\d+)\s*(K|M|G|)B?\z#i',$operator,$matches)){throw
new
InvalidArgumentException('Invalid size predicate format.');}list(,$operator,$size,$unit)=$matches;static$units=array(''=>1,'k'=>1e3,'m'=>1e6,'g'=>1e9);$size*=$units[strtolower($unit)];$operator=$operator?$operator:'=';}return$this->filter(create_function('$file','extract($GLOBALS[0]['.array_push($GLOBALS[0],array('operator'=>$operator,'size'=>$size)).'-1], EXTR_REFS);
			return NFinder::compare($file->getSize(), $operator, $size);
		'));}function
date($operator,$date=NULL){if(func_num_args()===1){if(!preg_match('#^(?:([=<>!]=?|<>)\s*)?(.+)\z#i',$operator,$matches)){throw
new
InvalidArgumentException('Invalid date predicate format.');}list(,$operator,$date)=$matches;$operator=$operator?$operator:'=';}$date=NDateTime53::from($date)->format('U');return$this->filter(create_function('$file','extract($GLOBALS[0]['.array_push($GLOBALS[0],array('operator'=>$operator,'date'=>$date)).'-1], EXTR_REFS);
			return NFinder::compare($file->getMTime(), $operator, $date);
		'));}static
function
compare($l,$operator,$r){switch($operator){case'>':return$l>$r;case'>=':return$l>=$r;case'<':return$l<$r;case'<=':return$l<=$r;case'=':case'==':return$l==$r;case'!':case'!=':case'<>':return$l!=$r;default:throw
new
InvalidArgumentException("Unknown operator $operator.");}}}if(PHP_VERSION_ID<50301){class
NRecursiveDirectoryIteratorFixed
extends
RecursiveDirectoryIterator{function
hasChildren(){return
parent::hasChildren(TRUE);}}}class
NJson{const
FORCE_ARRAY=1;private
static$messages=array(JSON_ERROR_DEPTH=>'The maximum stack depth has been exceeded',JSON_ERROR_STATE_MISMATCH=>'Syntax error, malformed JSON',JSON_ERROR_CTRL_CHAR=>'Unexpected control character found',JSON_ERROR_SYNTAX=>'Syntax error, malformed JSON',5=>'Invalid UTF-8 sequence',6=>'Recursion detected',7=>'Inf and NaN cannot be JSON encoded',8=>'Type is not supported');final
function
__construct(){throw
new
NStaticClassException;}static
function
encode($value){if(function_exists('ini_set')){$old=ini_set('display_errors',0);}set_error_handler(create_function('$severity, $message',' // needed to receive \'recursion detected\' error
			restore_error_handler();
			throw new NJsonException($message);
		'));$json=json_encode($value);restore_error_handler();if(isset($old)){ini_set('display_errors',$old);}if(PHP_VERSION_ID>=50300&&($error=json_last_error())){throw
new
NJsonException(isset(self::$messages[$error])?self::$messages[$error]:'Unknown error',$error);}$json=str_replace(array("\xe2\x80\xa8","\xe2\x80\xa9"),array('\u2028','\u2029'),$json);return$json;}static
function
decode($json,$options=0){$json=(string)$json;$value=json_decode($json,(bool)($options&self::FORCE_ARRAY));if($value===NULL&&$json!==''&&strcasecmp($json,'null')){$error=PHP_VERSION_ID>=50300?json_last_error():0;throw
new
NJsonException(isset(self::$messages[$error])?self::$messages[$error]:'Unknown error',$error);}return$value;}}class
NJsonException
extends
Exception{}class
NMimeTypeDetector{final
function
__construct(){throw
new
NStaticClassException;}static
function
fromFile($file){if(!is_file($file)){throw
new
FileNotFoundException("File '$file' not found.");}$info=@getimagesize($file);if(isset($info['mime'])){return$info['mime'];}elseif(extension_loaded('fileinfo')){$type=preg_replace('#[\s;].*\z#','',finfo_file(finfo_open(FILEINFO_MIME),$file));}elseif(function_exists('mime_content_type')){$type=mime_content_type($file);}return
isset($type)&&preg_match('#^\S+/\S+\z#',$type)?$type:'application/octet-stream';}static
function
fromString($data){if(extension_loaded('fileinfo')&&preg_match('#^(\S+/[^\s;]+)#',finfo_buffer(finfo_open(FILEINFO_MIME),$data),$m)){return$m[1];}elseif(strncmp($data,"\xff\xd8",2)===0){return'image/jpeg';}elseif(strncmp($data,"\x89PNG",4)===0){return'image/png';}elseif(strncmp($data,"GIF",3)===0){return'image/gif';}else{return'application/octet-stream';}}}class
NNeon
extends
NObject{const
BLOCK=1;private
static$patterns=array('
			\'[^\'\n]*\' |
			"(?: \\\\. | [^"\\\\\n] )*"
		','
			(?: [^#"\',:=[\]{}()\x00-\x20!`-] | [:-][^"\',\]})\s] )
			(?:
				[^,:=\]})(\x00-\x20]+ |
				:(?! [\s,\]})] | $ ) |
				[\ \t]+ [^#,:=\]})(\x00-\x20]
			)*
		','
			[,:=[\]{}()-]
		','?:\#.*','\n[\t\ ]*','?:[\t\ ]+');private
static$tokenizer;private
static$brackets=array('['=>']','{'=>'}','('=>')');private$n=0;private$indentTabs;static
function
encode($var,$options=NULL){if($var
instanceof
DateTime){return$var->format('Y-m-d H:i:s O');}elseif($var
instanceof
NNeonEntity){return
self::encode($var->value).'('.substr(self::encode($var->attributes),1,-1).')';}if(is_object($var)){$obj=$var;$var=array();foreach($obj
as$k=>$v){$var[$k]=$v;}}if(is_array($var)){$isList=NValidators::isList($var);$s='';if($options&self::BLOCK){if(count($var)===0){return"[]";}foreach($var
as$k=>$v){$v=self::encode($v,self::BLOCK);$s.=($isList?'-':self::encode($k).':').(NStrings::contains($v,"\n")?"\n\t".str_replace("\n","\n\t",$v):' '.$v)."\n";continue;}return$s;}else{foreach($var
as$k=>$v){$s.=($isList?'':self::encode($k).': ').self::encode($v).', ';}return($isList?'[':'{').substr($s,0,-2).($isList?']':'}');}}elseif(is_string($var)&&!is_numeric($var)&&!preg_match('~[\x00-\x1F]|^\d{4}|^(true|false|yes|no|on|off|null)\z~i',$var)&&preg_match('~^'.self::$patterns[1].'\z~x',$var)){return$var;}elseif(is_float($var)){$var=json_encode($var);return
NStrings::contains($var,'.')?$var:$var.'.0';}else{return
json_encode($var);}}static
function
decode($input){if(!is_string($input)){throw
new
InvalidArgumentException("Argument must be a string, ".gettype($input)." given.");}if(!self::$tokenizer){self::$tokenizer=new
NTokenizer(self::$patterns,'mix');}if(substr($input,0,3)==="\xEF\xBB\xBF"){$input=substr($input,3);}$input=str_replace("\r",'',$input);self::$tokenizer->tokenize($input);$parser=new
self;$res=$parser->parse(0);while(isset(self::$tokenizer->tokens[$parser->n])){if(self::$tokenizer->tokens[$parser->n][0]==="\n"){$parser->n++;}else{$parser->error();}}return$res;}private
function
parse($indent=NULL,$result=NULL){$inlineParser=$indent===NULL;$value=$key=$object=NULL;$hasValue=$hasKey=FALSE;$tokens=self::$tokenizer->tokens;$n=&$this->n;$count=count($tokens);for(;$n<$count;$n++){$t=$tokens[$n];if($t===','){if((!$hasKey&&!$hasValue)||!$inlineParser){$this->error();}$this->addValue($result,$hasKey,$key,$hasValue?$value:NULL);$hasKey=$hasValue=FALSE;}elseif($t===':'||$t==='='){if($hasKey||!$hasValue){$this->error();}if(is_array($value)||is_object($value)){$this->error('Unacceptable key');}$key=(string)$value;$hasKey=TRUE;$hasValue=FALSE;}elseif($t==='-'){if($hasKey||$hasValue||$inlineParser){$this->error();}$key=NULL;$hasKey=TRUE;}elseif(isset(self::$brackets[$t])){if($hasValue){if($t!=='('){$this->error();}$n++;$entity=new
NNeonEntity;$entity->value=$value;$entity->attributes=$this->parse(NULL,array());$value=$entity;}else{$n++;$value=$this->parse(NULL,array());}$hasValue=TRUE;if(!isset($tokens[$n])||$tokens[$n]!==self::$brackets[$t]){$this->error();}}elseif($t===']'||$t==='}'||$t===')'){if(!$inlineParser){$this->error();}break;}elseif($t[0]==="\n"){if($inlineParser){if($hasKey||$hasValue){$this->addValue($result,$hasKey,$key,$hasValue?$value:NULL);$hasKey=$hasValue=FALSE;}}else{while(isset($tokens[$n+1])&&$tokens[$n+1][0]==="\n")$n++;if(!isset($tokens[$n+1])){break;}$newIndent=strlen($tokens[$n])-1;if($indent===NULL){$indent=$newIndent;}if($newIndent){if($this->indentTabs===NULL){$this->indentTabs=$tokens[$n][1]==="\t";}if(strpos($tokens[$n],$this->indentTabs?' ':"\t")){$n++;$this->error('Either tabs or spaces may be used as indenting chars, but not both.');}}if($newIndent>$indent){if($hasValue||!$hasKey){$n++;$this->error('Unexpected indentation.');}else{$this->addValue($result,$key!==NULL,$key,$this->parse($newIndent));}$newIndent=isset($tokens[$n])?strlen($tokens[$n])-1:0;$hasKey=FALSE;}else{if($hasValue&&!$hasKey){break;}elseif($hasKey){$this->addValue($result,$key!==NULL,$key,$hasValue?$value:NULL);$hasKey=$hasValue=FALSE;}}if($newIndent<$indent){return$result;}}}else{if($hasValue){$this->error();}static$consts=array('true'=>TRUE,'True'=>TRUE,'TRUE'=>TRUE,'yes'=>TRUE,'Yes'=>TRUE,'YES'=>TRUE,'on'=>TRUE,'On'=>TRUE,'ON'=>TRUE,'false'=>FALSE,'False'=>FALSE,'FALSE'=>FALSE,'no'=>FALSE,'No'=>FALSE,'NO'=>FALSE,'off'=>FALSE,'Off'=>FALSE,'OFF'=>FALSE);if($t[0]==='"'){$value=preg_replace_callback('#\\\\(?:u[0-9a-f]{4}|x[0-9a-f]{2}|.)#i',array($this,'cbString'),substr($t,1,-1));}elseif($t[0]==="'"){$value=substr($t,1,-1);}elseif(isset($consts[$t])){$value=$consts[$t];}elseif($t==='null'||$t==='Null'||$t==='NULL'){$value=NULL;}elseif(is_numeric($t)){$value=$t*1;}elseif(preg_match('#\d\d\d\d-\d\d?-\d\d?(?:(?:[Tt]| +)\d\d?:\d\d:\d\d(?:\.\d*)? *(?:Z|[-+]\d\d?(?::\d\d)?)?)?\z#A',$t)){$value=new
NDateTime53($t);}else{$value=$t;}$hasValue=TRUE;}}if($inlineParser){if($hasKey||$hasValue){$this->addValue($result,$hasKey,$key,$hasValue?$value:NULL);}}else{if($hasValue&&!$hasKey){if($result===NULL){$result=$value;}else{$this->error();}}elseif($hasKey){$this->addValue($result,$key!==NULL,$key,$hasValue?$value:NULL);}}return$result;}private
function
addValue(&$result,$hasKey,$key,$value){if($hasKey){if($result&&array_key_exists($key,$result)){$this->error("Duplicated key '$key'");}$result[$key]=$value;}else{$result[]=$value;}}private
function
cbString($m){static$mapping=array('t'=>"\t",'n'=>"\n",'r'=>"\r",'f'=>"\x0C",'b'=>"\x08",'"'=>'"','\\'=>'\\','/'=>'/','_'=>"\xc2\xa0");$sq=$m[0];if(isset($mapping[$sq[1]])){return$mapping[$sq[1]];}elseif($sq[1]==='u'&&strlen($sq)===6){return
NStrings::chr(hexdec(substr($sq,2)));}elseif($sq[1]==='x'&&strlen($sq)===4){return
chr(hexdec(substr($sq,2)));}else{$this->error("Invalid escaping sequence $sq");}}private
function
error($message="Unexpected '%s'"){list(,$line,$col)=self::$tokenizer->getOffset($this->n);$token=isset(self::$tokenizer->tokens[$this->n])?str_replace("\n",'<new line>',NStrings::truncate(self::$tokenizer->tokens[$this->n],40)):'end';throw
new
NNeonException(str_replace('%s',$token,$message)." on line $line, column $col.");}}class
NNeonEntity
extends
stdClass{public$value;public$attributes;}class
NNeonException
extends
Exception{}class
NPaginator
extends
NObject{private$base=1;private$itemsPerPage=1;private$page;private$itemCount;function
setPage($page){$this->page=(int)$page;return$this;}function
getPage(){return$this->base+$this->getPageIndex();}function
getFirstPage(){return$this->base;}function
getLastPage(){return$this->itemCount===NULL?NULL:$this->base+max(0,$this->getPageCount()-1);}function
setBase($base){$this->base=(int)$base;return$this;}function
getBase(){return$this->base;}protected
function
getPageIndex(){$index=max(0,$this->page-$this->base);return$this->itemCount===NULL?$index:min($index,max(0,$this->getPageCount()-1));}function
isFirst(){return$this->getPageIndex()===0;}function
isLast(){return$this->itemCount===NULL?FALSE:$this->getPageIndex()>=$this->getPageCount()-1;}function
getPageCount(){return$this->itemCount===NULL?NULL:(int)ceil($this->itemCount/$this->itemsPerPage);}function
setItemsPerPage($itemsPerPage){$this->itemsPerPage=max(1,(int)$itemsPerPage);return$this;}function
getItemsPerPage(){return$this->itemsPerPage;}function
setItemCount($itemCount){$this->itemCount=($itemCount===FALSE||$itemCount===NULL)?NULL:max(0,(int)$itemCount);return$this;}function
getItemCount(){return$this->itemCount;}function
getOffset(){return$this->getPageIndex()*$this->itemsPerPage;}function
getCountdownOffset(){return$this->itemCount===NULL?NULL:max(0,$this->itemCount-($this->getPageIndex()+1)*$this->itemsPerPage);}function
getLength(){return$this->itemCount===NULL?$this->itemsPerPage:min($this->itemsPerPage,$this->itemCount-$this->getPageIndex()*$this->itemsPerPage);}}class
NPhpClassType
extends
NObject{public$name;public$type='class';public$final;public$abstract;public$extends=array();public$implements=array();public$traits=array();public$documents=array();public$consts=array();public$properties=array();public$methods=array();function
__construct($name=NULL){$this->name=$name;}function
addConst($name,$value){$this->consts[$name]=$value;return$this;}function
addProperty($name,$value=NULL){$property=new
NPhpProperty;return$this->properties[$name]=$property->setName($name)->setValue($value);}function
addMethod($name){$method=new
NPhpMethod;if($this->type==='interface'){$method->setVisibility('')->setBody(FALSE);}else{$method->setVisibility('public');}return$this->methods[$name]=$method->setName($name);}function
__call($name,$args){return
NObjectMixin::callProperty($this,$name,$args);}function
__toString(){$consts=array();foreach($this->consts
as$name=>$value){$consts[]="const $name = ".NPhpHelpers::dump($value).";\n";}$properties=array();foreach($this->properties
as$property){$properties[]=($property->documents?str_replace("\n","\n * ","/**\n".implode("\n",(array)$property->documents))."\n */\n":'').$property->visibility.($property->static?' static':'').' $'.$property->name.($property->value===NULL?'':' = '.NPhpHelpers::dump($property->value)).";\n";}return
NStrings::normalize(($this->documents?str_replace("\n","\n * ","/**\n".implode("\n",(array)$this->documents))."\n */\n":'').($this->abstract?'abstract ':'').($this->final?'final ':'').$this->type.' '.$this->name.' '.($this->extends?'extends '.implode(', ',(array)$this->extends).' ':'').($this->implements?'implements '.implode(', ',(array)$this->implements).' ':'')."\n{\n\n".NStrings::indent(($this->traits?"use ".implode(', ',(array)$this->traits).";\n\n":'').($this->consts?implode('',$consts)."\n\n":'').($this->properties?implode("\n",$properties)."\n\n":'').implode("\n\n\n",$this->methods),1)."\n\n}")."\n";}}class
NPhpHelpers{const
PHP_IDENT='[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*';static
function
dump($var){return
self::_dump($var);}private
static
function
_dump(&$var,$level=0){if($var
instanceof
NPhpLiteral){return$var->value;}elseif(is_float($var)){$var=var_export($var,TRUE);return
strpos($var,'.')===FALSE?$var.'.0':$var;}elseif(is_bool($var)){return$var?'TRUE':'FALSE';}elseif(is_string($var)&&(preg_match('#[^\x09\x20-\x7E\xA0-\x{10FFFF}]#u',$var)||preg_last_error())){static$table;if($table===NULL){foreach(range("\x00","\xFF")as$ch){$table[$ch]=ord($ch)<32||ord($ch)>=127?'\\x'.str_pad(dechex(ord($ch)),2,'0',STR_PAD_LEFT):$ch;}$table["\r"]='\r';$table["\n"]='\n';$table["\t"]='\t';$table['$']='\\$';$table['\\']='\\\\';$table['"']='\\"';}return'"'.strtr($var,$table).'"';}elseif(is_array($var)){$s='';$space=str_repeat("\t",$level);static$marker;if($marker===NULL){$marker=uniqid("\x00",TRUE);}if(empty($var)){}elseif($level>50||isset($var[$marker])){throw
new
InvalidArgumentException('Nesting level too deep or recursive dependency.');}else{$s.="\n";$var[$marker]=TRUE;$counter=0;foreach($var
as$k=>&$v){if($k!==$marker){$s.="$space\t".($k===$counter?'':self::_dump($k)." => ").self::_dump($v,$level+1).",\n";$counter=is_int($k)?max($k+1,$counter):$counter;}}unset($var[$marker]);$s.=$space;}return"array($s)";}elseif(is_object($var)){$arr=(array)$var;$s='';$space=str_repeat("\t",$level);static$list=array();if(empty($arr)){}elseif($level>50||in_array($var,$list,TRUE)){throw
new
InvalidArgumentException('Nesting level too deep or recursive dependency.');}else{$s.="\n";$list[]=$var;foreach($arr
as$k=>&$v){if($k[0]==="\x00"){$k=substr($k,strrpos($k,"\x00")+1);}$s.="$space\t".self::_dump($k)." => ".self::_dump($v,$level+1).",\n";}array_pop($list);$s.=$space;}return
get_class($var)==='stdClass'?"(object) array($s)":__CLASS__."::createObject('".get_class($var)."', array($s))";}else{return
var_export($var,TRUE);}}static
function
format($statement){$args=func_get_args();return
self::formatArgs(array_shift($args),$args);}static
function
formatArgs($statement,array$args){$a=strpos($statement,'?');while($a!==FALSE){if(!$args){throw
new
InvalidArgumentException('Insufficient number of arguments.');}$arg=array_shift($args);if(substr($statement,$a+1,1)==='*'){if(!is_array($arg)){throw
new
InvalidArgumentException('Argument must be an array.');}$arg=implode(', ',array_map(array(__CLASS__,'dump'),$arg));$statement=substr_replace($statement,$arg,$a,2);}else{$arg=substr($statement,$a-1,1)==='$'||in_array(substr($statement,$a-2,2),array('->','::'))?self::formatMember($arg):self::_dump($arg);$statement=substr_replace($statement,$arg,$a,1);}$a=strpos($statement,'?',$a+strlen($arg));}return$statement;}static
function
formatMember($name){return$name
instanceof
NPhpLiteral||!self::isIdentifier($name)?'{'.self::_dump($name).'}':$name;}static
function
isIdentifier($value){return
is_string($value)&&preg_match('#^'.self::PHP_IDENT.'\z#',$value);}static
function
createObject($class,array$props){return
unserialize('O'.substr(serialize((string)$class),1,-1).substr(serialize($props),1));}}class
NPhpMethod
extends
NObject{public$name;public$parameters=array();public$uses=array();public$body;public$static;public$visibility;public$final;public$abstract;public$returnReference;public$documents=array();function
addParameter($name,$defaultValue=NULL){$param=new
NPhpParameter;if(func_num_args()>1){$param->setOptional(TRUE)->setDefaultValue($defaultValue);}return$this->parameters[]=$param->setName($name);}function
addUse($name){$param=new
NPhpParameter;return$this->uses[]=$param->setName($name);}function
setBody($statement,array$args=NULL){$this->body=func_num_args()>1?NPhpHelpers::formatArgs($statement,$args):$statement;return$this;}function
addBody($statement,array$args=NULL){$this->body.=(func_num_args()>1?NPhpHelpers::formatArgs($statement,$args):$statement)."\n";return$this;}function
__call($name,$args){return
NObjectMixin::callProperty($this,$name,$args);}function
__toString(){$parameters=array();foreach($this->parameters
as$param){$parameters[]=($param->typeHint?$param->typeHint.' ':'').($param->reference?'&':'').'$'.$param->name.($param->optional?' = '.NPhpHelpers::dump($param->defaultValue):'');}$uses=array();foreach($this->uses
as$param){$uses[]=($param->reference?'&':'').'$'.$param->name;}return($this->documents?str_replace("\n","\n * ","/**\n".implode("\n",(array)$this->documents))."\n */\n":'').($this->abstract?'abstract ':'').($this->final?'final ':'').($this->visibility?$this->visibility.' ':'').($this->static?'static ':'').'function'.($this->returnReference?' &':'').($this->name?' '.$this->name:'').'('.implode(', ',$parameters).')'.($this->uses?' use ('.implode(', ',$uses).')':'').($this->abstract||$this->body===FALSE?';':($this->name?"\n":' ')."{\n".NStrings::indent(trim($this->body),1)."\n}");}}class
NPhpParameter
extends
NObject{public$name;public$reference;public$typeHint;public$optional;public$defaultValue;function
__call($name,$args){return
NObjectMixin::callProperty($this,$name,$args);}}class
NPhpLiteral{public$value='';function
__construct($value){$this->value=(string)$value;}}class
NPhpProperty
extends
NObject{public$name;public$value;public$static;public$visibility='public';public$documents=array();function
__call($name,$args){return
NObjectMixin::callProperty($this,$name,$args);}}class
NStrings{final
function
__construct(){throw
new
NStaticClassException;}static
function
checkEncoding($s,$encoding='UTF-8'){return$s===self::fixEncoding($s,$encoding);}static
function
fixEncoding($s,$encoding='UTF-8'){if(strcasecmp($encoding,'UTF-8')===0){$s=str_replace("\xEF\xBB\xBF",'',$s);}if(PHP_VERSION_ID>=50400){ini_set('mbstring.substitute_character','none');return
mb_convert_encoding($s,$encoding,$encoding);}return@iconv('UTF-16',$encoding.'//IGNORE',iconv($encoding,'UTF-16//IGNORE',$s));}static
function
chr($code,$encoding='UTF-8'){return
iconv('UTF-32BE',$encoding.'//IGNORE',pack('N',$code));}static
function
startsWith($haystack,$needle){return
strncmp($haystack,$needle,strlen($needle))===0;}static
function
endsWith($haystack,$needle){return
strlen($needle)===0||substr($haystack,-strlen($needle))===$needle;}static
function
contains($haystack,$needle){return
strpos($haystack,$needle)!==FALSE;}static
function
substring($s,$start,$length=NULL){if($length===NULL){$length=self::length($s);}return
function_exists('mb_substr')?mb_substr($s,$start,$length,'UTF-8'):iconv_substr($s,$start,$length,'UTF-8');}static
function
normalize($s){$s=str_replace("\r\n","\n",$s);$s=strtr($s,"\r","\n");$s=preg_replace('#[\x00-\x08\x0B-\x1F\x7F]+#','',$s);$s=preg_replace('#[\t ]+$#m','',$s);$s=trim($s,"\n");return$s;}static
function
toAscii($s){$s=preg_replace('#[^\x09\x0A\x0D\x20-\x7E\xA0-\x{2FF}\x{370}-\x{10FFFF}]#u','',$s);$s=strtr($s,'`\'"^~',"\x01\x02\x03\x04\x05");if(ICONV_IMPL==='glibc'){$s=@iconv('UTF-8','WINDOWS-1250//TRANSLIT',$s);$s=strtr($s,"\xa5\xa3\xbc\x8c\xa7\x8a\xaa\x8d\x8f\x8e\xaf\xb9\xb3\xbe\x9c\x9a\xba\x9d\x9f\x9e"."\xbf\xc0\xc1\xc2\xc3\xc4\xc5\xc6\xc7\xc8\xc9\xca\xcb\xcc\xcd\xce\xcf\xd0\xd1\xd2\xd3"."\xd4\xd5\xd6\xd7\xd8\xd9\xda\xdb\xdc\xdd\xde\xdf\xe0\xe1\xe2\xe3\xe4\xe5\xe6\xe7\xe8"."\xe9\xea\xeb\xec\xed\xee\xef\xf0\xf1\xf2\xf3\xf4\xf5\xf6\xf8\xf9\xfa\xfb\xfc\xfd\xfe\x96","ALLSSSSTZZZallssstzzzRAAAALCCCEEEEIIDDNNOOOOxRUUUUYTsraaaalccceeeeiiddnnooooruuuuyt-");}else{$s=@iconv('UTF-8','ASCII//TRANSLIT',$s);}$s=str_replace(array('`',"'",'"','^','~'),'',$s);return
strtr($s,"\x01\x02\x03\x04\x05",'`\'"^~');}static
function
webalize($s,$charlist=NULL,$lower=TRUE){$s=self::toAscii($s);if($lower){$s=strtolower($s);}$s=preg_replace('#[^a-z0-9'.preg_quote($charlist,'#').']+#i','-',$s);$s=trim($s,'-');return$s;}static
function
truncate($s,$maxLen,$append="\xE2\x80\xA6"){if(self::length($s)>$maxLen){$maxLen=$maxLen-self::length($append);if($maxLen<1){return$append;}elseif($matches=self::match($s,'#^.{1,'.$maxLen.'}(?=[\s\x00-/:-@\[-`{-~])#us')){return$matches[0].$append;}else{return
self::substring($s,0,$maxLen).$append;}}return$s;}static
function
indent($s,$level=1,$chars="\t"){return$level<1?$s:self::replace($s,'#(?:^|[\r\n]+)(?=[^\r\n])#','$0'.str_repeat($chars,$level));}static
function
lower($s){return
mb_strtolower($s,'UTF-8');}static
function
upper($s){return
mb_strtoupper($s,'UTF-8');}static
function
firstUpper($s){return
self::upper(self::substring($s,0,1)).self::substring($s,1);}static
function
capitalize($s){return
mb_convert_case($s,MB_CASE_TITLE,'UTF-8');}static
function
compare($left,$right,$len=NULL){if($len<0){$left=self::substring($left,$len,-$len);$right=self::substring($right,$len,-$len);}elseif($len!==NULL){$left=self::substring($left,0,$len);$right=self::substring($right,0,$len);}return
self::lower($left)===self::lower($right);}static
function
length($s){return
strlen(utf8_decode($s));}static
function
trim($s,$charlist=" \t\n\r\0\x0B\xC2\xA0"){$charlist=preg_quote($charlist,'#');return
self::replace($s,'#^['.$charlist.']+|['.$charlist.']+\z#u','');}static
function
padLeft($s,$length,$pad=' '){$length=max(0,$length-self::length($s));$padLen=self::length($pad);return
str_repeat($pad,$length/$padLen).self::substring($pad,0,$length
%$padLen).$s;}static
function
padRight($s,$length,$pad=' '){$length=max(0,$length-self::length($s));$padLen=self::length($pad);return$s.str_repeat($pad,$length/$padLen).self::substring($pad,0,$length
%$padLen);}static
function
reverse($s){return@iconv('UTF-32LE','UTF-8',strrev(@iconv('UTF-8','UTF-32BE',$s)));}static
function
random($length=10,$charlist='0-9a-z'){$charlist=str_shuffle(preg_replace_callback('#.-.#',create_function('$m','
			return implode(\'\', range($m[0][0], $m[0][2]));
		'),$charlist));$chLen=strlen($charlist);if(function_exists('openssl_random_pseudo_bytes')&&(PHP_VERSION_ID>=50400||!defined('PHP_WINDOWS_VERSION_BUILD'))){$rand3=openssl_random_pseudo_bytes($length);}if(empty($rand3)&&function_exists('mcrypt_create_iv')&&(PHP_VERSION_ID!==50303||!defined('PHP_WINDOWS_VERSION_BUILD'))){$rand3=mcrypt_create_iv($length,MCRYPT_DEV_URANDOM);}if(empty($rand3)&&@is_readable('/dev/urandom')){$rand3=file_get_contents('/dev/urandom',FALSE,NULL,-1,$length);}if(empty($rand3)){static$cache;$rand3=($tmp=$cache)?$tmp:$cache=md5(serialize($_SERVER),TRUE);}$s='';for($i=0;$i<$length;$i++){if($i
%
5===0){list($rand,$rand2)=explode(' ',microtime());$rand+=lcg_value();}$rand*=$chLen;$s.=$charlist[($rand+$rand2+ord($rand3[$i
%
strlen($rand3)]))%$chLen];$rand-=(int)$rand;}return$s;}static
function
split($subject,$pattern,$flags=0){set_error_handler(create_function('$severity, $message','extract($GLOBALS[0]['.array_push($GLOBALS[0],array('pattern'=>$pattern)).'-1], EXTR_REFS); // preg_last_error does not return compile errors
			restore_error_handler();
			throw new NRegexpException("$message in pattern: $pattern");
		'));$res=preg_split($pattern,$subject,-1,$flags|PREG_SPLIT_DELIM_CAPTURE);restore_error_handler();if(preg_last_error()){throw
new
NRegexpException(NULL,preg_last_error(),$pattern);}return$res;}static
function
match($subject,$pattern,$flags=0,$offset=0){if($offset>strlen($subject)){return
NULL;}set_error_handler(create_function('$severity, $message','extract($GLOBALS[0]['.array_push($GLOBALS[0],array('pattern'=>$pattern)).'-1], EXTR_REFS); // preg_last_error does not return compile errors
			restore_error_handler();
			throw new NRegexpException("$message in pattern: $pattern");
		'));$res=preg_match($pattern,$subject,$m,$flags,$offset);restore_error_handler();if(preg_last_error()){throw
new
NRegexpException(NULL,preg_last_error(),$pattern);}if($res){return$m;}}static
function
matchAll($subject,$pattern,$flags=0,$offset=0){if($offset>strlen($subject)){return
array();}set_error_handler(create_function('$severity, $message','extract($GLOBALS[0]['.array_push($GLOBALS[0],array('pattern'=>$pattern)).'-1], EXTR_REFS); // preg_last_error does not return compile errors
			restore_error_handler();
			throw new NRegexpException("$message in pattern: $pattern");
		'));preg_match_all($pattern,$subject,$m,($flags&PREG_PATTERN_ORDER)?$flags:($flags|PREG_SET_ORDER),$offset);restore_error_handler();if(preg_last_error()){throw
new
NRegexpException(NULL,preg_last_error(),$pattern);}return$m;}static
function
replace($subject,$pattern,$replacement=NULL,$limit=-1){if(is_object($replacement)||is_array($replacement)||preg_match('#^\x00lambda_\d+\z#',$replacement)){if($replacement
instanceof
NCallback){$replacement=$replacement->getNative();}if(!is_callable($replacement,FALSE,$textual)){throw
new
InvalidStateException("Callback '$textual' is not callable.");}set_error_handler(create_function('$severity, $message','extract($GLOBALS[0]['.array_push($GLOBALS[0],array('tmp'=>&$tmp)).'-1], EXTR_REFS); // preg_last_error does not return compile errors
				restore_error_handler();
				throw new NRegexpException("$message in pattern: $tmp");
			'));foreach((array)$pattern
as$tmp){preg_match($tmp,'');}restore_error_handler();$res=preg_replace_callback($pattern,$replacement,$subject,$limit);if($res===NULL&&preg_last_error()){throw
new
NRegexpException(NULL,preg_last_error(),$pattern);}return$res;}elseif($replacement===NULL&&is_array($pattern)){$replacement=array_values($pattern);$pattern=array_keys($pattern);}set_error_handler(create_function('$severity, $message','extract($GLOBALS[0]['.array_push($GLOBALS[0],array('pattern'=>$pattern)).'-1], EXTR_REFS); // preg_last_error does not return compile errors
			restore_error_handler();
			throw new NRegexpException("$message in pattern: " . implode(\' or \', (array) $pattern));
		'));$res=preg_replace($pattern,$replacement,$subject,$limit);restore_error_handler();if(preg_last_error()){throw
new
NRegexpException(NULL,preg_last_error(),implode(' or ',(array)$pattern));}return$res;}}class
NRegexpException
extends
Exception{static
public$messages=array(PREG_INTERNAL_ERROR=>'Internal error',PREG_BACKTRACK_LIMIT_ERROR=>'Backtrack limit was exhausted',PREG_RECURSION_LIMIT_ERROR=>'Recursion limit was exhausted',PREG_BAD_UTF8_ERROR=>'Malformed UTF-8 data',5=>'Offset didn\'t correspond to the begin of a valid UTF-8 code point');function
__construct($message,$code=NULL,$pattern=NULL){if(!$message){$message=(isset(self::$messages[$code])?self::$messages[$code]:'Unknown error').($pattern?" (pattern: $pattern)":'');}parent::__construct($message,$code);}}class
NValidators
extends
NObject{protected
static$validators=array('bool'=>'is_bool','boolean'=>'is_bool','int'=>'is_int','integer'=>'is_int','float'=>'is_float','number'=>NULL,'numeric'=>array(__CLASS__,'isNumeric'),'numericint'=>array(__CLASS__,'isNumericInt'),'string'=>'is_string','unicode'=>array(__CLASS__,'isUnicode'),'array'=>'is_array','list'=>array(__CLASS__,'isList'),'object'=>'is_object','resource'=>'is_resource','scalar'=>'is_scalar','callable'=>array(__CLASS__,'isCallable'),'null'=>'is_null','email'=>array(__CLASS__,'isEmail'),'url'=>array(__CLASS__,'isUrl'),'none'=>array(__CLASS__,'isNone'),'pattern'=>NULL,'alnum'=>'ctype_alnum','alpha'=>'ctype_alpha','digit'=>'ctype_digit','lower'=>'ctype_lower','upper'=>'ctype_upper','space'=>'ctype_space','xdigit'=>'ctype_xdigit');protected
static$counters=array('string'=>'strlen','unicode'=>array('NStrings','length'),'array'=>'count','list'=>'count','alnum'=>'strlen','alpha'=>'strlen','digit'=>'strlen','lower'=>'strlen','space'=>'strlen','upper'=>'strlen','xdigit'=>'strlen');static
function
assert($value,$expected,$label='variable'){if(!self::is($value,$expected)){$expected=str_replace(array('|',':'),array(' or ',' in range '),$expected);if(is_array($value)){$type='array('.count($value).')';}elseif(is_object($value)){$type='object '.get_class($value);}elseif(is_string($value)&&strlen($value)<40){$type="string '$value'";}else{$type=gettype($value);}throw
new
NAssertionException("The $label expects to be $expected, $type given.");}}static
function
assertField($arr,$field,$expected=NULL,$label="item '%' in array"){self::assert($arr,'array','first argument');if(!array_key_exists($field,$arr)){throw
new
NAssertionException('Missing '.str_replace('%',$field,$label).'.');}elseif($expected){self::assert($arr[$field],$expected,str_replace('%',$field,$label));}}static
function
is($value,$expected){foreach(explode('|',$expected)as$item){list($type)=$item=explode(':',$item,2);if(isset(self::$validators[$type])){if(!call_user_func(self::$validators[$type],$value)){continue;}}elseif($type==='number'){if(!is_int($value)&&!is_float($value)){continue;}}elseif($type==='pattern'){if(preg_match('|^'.(isset($item[1])?$item[1]:'').'\z|',$value)){return
TRUE;}continue;}elseif(!$value
instanceof$type){continue;}if(isset($item[1])){if(isset(self::$counters[$type])){$value=call_user_func(self::$counters[$type],$value);}$range=explode('..',$item[1]);if(!isset($range[1])){$range[1]=$range[0];}if(($range[0]!==''&&$value<$range[0])||($range[1]!==''&&$value>$range[1])){continue;}}return
TRUE;}return
FALSE;}static
function
isNumericInt($value){return
is_int($value)||is_string($value)&&preg_match('#^-?[0-9]+\z#',$value);}static
function
isNumeric($value){return
is_float($value)||is_int($value)||is_string($value)&&preg_match('#^-?[0-9]*[.]?[0-9]+\z#',$value);}static
function
isCallable($value){return$value&&is_callable($value,TRUE);}static
function
isUnicode($value){return
is_string($value)&&preg_match('##u',$value);}static
function
isNone($value){return$value==NULL;}static
function
isList($value){return
is_array($value)&&(!$value||array_keys($value)===range(0,count($value)-1));}static
function
isInRange($value,$range){return(!isset($range[0])||$value>=$range[0])&&(!isset($range[1])||$value<=$range[1]);}static
function
isEmail($value){$atom="[-a-z0-9!#$%&'*+/=?^_`{|}~]";$localPart="(?:\"(?:[ !\\x23-\\x5B\\x5D-\\x7E]*|\\\\[ -~])+\"|$atom+(?:\\.$atom+)*)";$alpha="a-z\x80-\xFF";$domain="[0-9$alpha](?:[-0-9$alpha]{0,61}[0-9$alpha])?";$topDomain="[$alpha][-0-9$alpha]{0,17}[$alpha]";return(bool)preg_match("(^$localPart@(?:$domain\\.)+$topDomain\\z)i",$value);}static
function
isUrl($value){$alpha="a-z\x80-\xFF";$domain="[0-9$alpha](?:[-0-9$alpha]{0,61}[0-9$alpha])?";$topDomain="[$alpha][-0-9$alpha]{0,17}[$alpha]";return(bool)preg_match("(^https?://(?:(?:$domain\\.)*$topDomain|\\d{1,3}\.\\d{1,3}\.\\d{1,3}\.\\d{1,3}|\[[0-9a-f:]{3,39}\])(:\\d{1,5})?(/\\S*)?\\z)i",$value);}}class
NAssertionException
extends
Exception{}NDebugger::_init();NSafeStream::register();function
callback($callback,$m=NULL){return
new
NCallback($callback,$m);}function
dump($var){foreach(func_get_args()as$arg){NDebugger::dump($arg);}return$var;}