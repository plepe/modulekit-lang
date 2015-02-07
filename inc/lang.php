<?php
define("F", 1);
define("M", 2);
define("N", 3);
$lang_genders=array(1=>"F", 2=>"M", 3=>"N");

function lang() {
  global $lang_str;
  $params = func_get_args();

  $count = null;
  if(is_numeric($params[0])) {
    $count = $params[0];
    $params = array_slice($params, 1);
  }

  $key = $params[0];
  $params = array_slice($params, 1);

  // if 'key' is an array, translations are passed as array values, like:
  // array(
  //   'en'	=>"English text",
  //   'de'	=>"German text"
  // )
  //
  // lang($x)            -> will return "English text" or "German text"
  //
  // if current language is not defined in the array the first language
  // will be used (in that case 'en').
  if(is_array($key)) {
    global $ui_lang;

    if(isset($key[$ui_lang]))
      $def = $key[$ui_lang];
    else {
      foreach($key as $k=>$v) {
        $def = $v;
        break;
      }
    }
  }
  else {
    if(!array_key_exists($key, $lang_str))
      return $key;

    $def = $lang_str[$key];
  }

  if(!$def)
    return $key;
  elseif(is_string($def)) {
    $str = $def;
  }
  else {
    if($count === null)
      $str = $def['message'];
    elseif(array_key_exists($count, $def))
      $str = $def[$count];
    elseif(($count != 1) && (array_key_exists("!=1", $def)))
      $str = $def['!=1'];
    else
      $str = $def['message'];
  }

  return vsprintf($str, $params);
}

// replace all {...} by their translations
function lang_parse($str, $count=0) {
  $ret=$str;

  while(preg_match("/^(.*)\{([^\}]+)\}(.*)$/", $ret, $m)) {
    $args=func_get_args();
    $args[0]=$m[2];

    $ret=$m[1].call_user_func_array("lang", $args).$m[3];
  }

  return $ret;
}

function lang_from_browser($avail_langs=null) {
  $max_q=-1;
  $chosen_lang="";
  $acc_langs=explode(",", $_SERVER['HTTP_ACCEPT_LANGUAGE']);

  foreach($acc_langs as $acc_lang) {
    $acc_lang=explode(";", $acc_lang);
    
    foreach($acc_lang as $acc_lang_part) {
      if(preg_match("/^(.*)=(.*)$/", $acc_lang_part, $m)) {
	$acc_lang[$m[1]]=$m[2];
      }
    }
    if(!$acc_lang['q'])
      $acc_lang['q']=1;

    if(((!$avail_langs)||(in_array($acc_lang[0], $avail_langs)))
       &&(!strpos($acc_lang[0], "-"))
       &&($acc_lang['q']>$max_q)) {
      $chosen_lang=$acc_lang[0];
      $max_q=$acc_lang['q'];
    }
  }

  return $chosen_lang;
}

if((!isset($languages))||(!is_array($languages))) {
  $main_module=modulekit_loaded("");
  if(isset($main_module['languages']))
    $languages=$main_module['languages'];
  else
    $languages=array("en");
}

if(isset($_REQUEST['ui_lang']))
  $ui_lang=$_REQUEST['ui_lang'];
if(!isset($ui_lang)&&
   array_key_exists('param', $_REQUEST)&&
   array_key_exists('ui_lang', $_REQUEST['param']))
  $ui_lang=$_REQUEST['param']['ui_lang'];
if(!isset($ui_lang)&&array_key_exists('ui_lang', $_COOKIE))
  $ui_lang=$_COOKIE['ui_lang'];
if(!isset($ui_lang))
  $ui_lang=lang_from_browser($languages);
if(!$ui_lang)
  $ui_lang=$languages[0];

function lang_file_load_json($file) {
  global $lang_str;

  if(!file_exists($file))
    return;

  $strs = json_decode(file_get_contents($file), true);
  if(json_last_error()) {
    trigger_error("modulekit-lang: JSON error when loading file '$file': " . json_last_error_msg() . "." , E_USER_WARNING);
    return;
  }

  foreach($strs as $k=>$v) {
    // if no 'message' => not translated, therefore ignore
    if(is_string($v) || (array_key_exists('message', $v))) {
      $lang_str[$k] = $v;
    }
  }
}

function lang_file_load_php($file) {
  @include($file);
  if(!isset($lang_str))
    return;

  $strs = $lang_str;
  global $lang_str;

  foreach($strs as $k=>$v) {
    if(is_array($v)) {
      if(in_array($v[0], array(M, F, N))) {
        $lang_str[$k] = array(
          'message'     => $v[1],
          '!=1'         => $v[2],
          'gender'      => array(M=>'male', F=>'female', N=>'neuter')[$v[0]],
        );
      }
      else {
        $lang_str[$k] = array(
          'message'     => $v[0],
          '!=1'         => $v[1],
        );
      }
    }
    else {
      $lang_str[$k] = $v;
    }
  }
}

function lang_load($lang, $loaded=array()) {
  global $lang_str;
  global $modulekit;

  $lang_str=array();

  lang_file_load_json(modulekit_file("modulekit-lang", "lang/base_{$lang}.json"));
  lang_file_load_json(modulekit_file("modulekit-lang", "lang/lang_{$lang}.json"));
  lang_file_load_php("lang/tags_{$lang}.php");
  foreach($modulekit['order'] as $module) {
    lang_file_load_json(modulekit_file($module, "lang_{$lang}.json"));
    lang_file_load_php(modulekit_file($module, "lang_{$lang}.php"));
    lang_file_load_json(modulekit_file($module, "lang/{$lang}.json"));
    lang_file_load_php(modulekit_file($module, "lang/{$lang}.php"));
  }
  $loaded[]=$lang;

  if(!isset($lang_str['base_language']))
    $lang_str['base_language']="en";
  if(in_array($lang_str['base_language'], $loaded))
    return;

  $base = $lang_str['base_language'];
  if(is_array($base))
    $base = $base['message'];

  $save_lang_str=$lang_str;
  lang_load($base, $loaded);
  $lang_str=array_merge($lang_str, $save_lang_str);
}

function lang_code_check($lang) {
  return preg_match("/^[a-z\-]+$/", $lang);
}

function lang_init() {
  global $lang_str;
  global $ui_lang;
  global $language_list;
  global $languages;
  global $design_hidden;
  global $lang_genders;
  global $version_string;
  global $modulekit;
  global $modulekit_cache_dir;

  @include modulekit_file("modulekit-lang", "lang/list.php");

  $cache_file="{$modulekit_cache_dir}lang_{$ui_lang}.data";
  $cache_file_js="{$modulekit_cache_dir}lang_{$ui_lang}.js";
  if(file_exists($cache_file)) {
    $lang_str=unserialize(file_get_contents($cache_file));
  }
  else {
    lang_load($ui_lang);

    // Define a language string for every language
    foreach($language_list as $abbr=>$lang) {
      $lang_str["lang_native:".$abbr]=$lang;
    }

    if(is_writeable($modulekit_cache_dir)) {
      file_put_contents($cache_file, serialize($lang_str));
      file_put_contents($cache_file_js, "var lang_str=".json_encode($lang_str).";\n");
    }
  }

  $vars=array("ui_lang"=>$ui_lang, "language_list"=>$language_list, "languages"=>$languages, "lang_genders"=>$lang_genders);
  if(file_exists($cache_file_js))
    add_html_header("<script type='text/javascript' src='{$cache_file_js}?{$modulekit['version']}'></script>");
  else
    $vars['lang_str']=$lang_str;

  html_export_var($vars);
  add_html_header("<meta http-equiv=\"content-language\" content=\"{$ui_lang}\">");
}

register_hook("init", "lang_init");
