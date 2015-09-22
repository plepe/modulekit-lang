<?php
define("F", 1);
define("M", 2);
define("N", 3);
$lang_genders=array(1=>"F", 2=>"M", 3=>"N");

function lang() {
  global $lang_str;
  $offset=1;

  $key=func_get_arg(0);
  if((sizeof(func_get_args())>1)&&is_numeric(func_get_arg(1))) {
    $offset++;
    $count=func_get_arg(1);
  }
  else
    $count=1;
  $params=array_slice(func_get_args(), $offset);

  // if 'key' is an array, translations are passed as array values, like:
  // array(
  //   'en'	=>"English text",
  //   'de'	=>"German text"
  // )
  //
  // optionally a prefix can be defined as second parameter, e.g.
  //
  // $x = array(
  //   'en'		=>"English text",
  //   'de'		=>"German text"
  //   'desc:en'	=>"English description",
  //   'desc:de'	=>"German description"
  // )
  // lang($x)            -> will return "English text" or "German text"
  // lang($x, 'desc:')   -> will return "English description" or "German description"
  //
  // if current language is not defined in the array the first language
  // will be used (in that case 'en').
  if(is_array($key)) {
    global $ui_lang;

    $prefix = "";
    if((sizeof(func_get_args()) > 1) && (is_string(func_get_arg(1)))) {
      $prefix = func_get_arg(1);
      if(sizeof(func_get_args())>2)
	$count = func_get_arg(2);
    }

    if(isset($key["{$prefix}{$ui_lang}"]))
      $l=$key["{$prefix}{$ui_lang}"];
    else {
      foreach($key as $k=>$v)
        if(substr($k, 0, strlen($prefix)) == $prefix) {
	  $l=$v;
	  break;
	}
    }
  }
  else {
    if(preg_match("/^(.*)\/(.*)$/", $key, $m)) {
      $key_exp=explode(";", $m[2]);
      if(sizeof($key_exp)>1) {
	foreach($key_exp as $key_index=>$key_value) {
	  $key_exp[$key_index]=lang("$m[1]/$key_value", $count);
	}
	$l=implode(", ", $key_exp);
      }
    }
    elseif(!isset($lang_str[$key])) {
      if((preg_match("/^tag:([^=]*)=(.*)$/", $key, $m))&&($k=$lang_str["tag:*={$m[2]}"])) {
	// Boolean values, see:
	// http://wiki.openstreetmap.org/wiki/Proposed_features/boolean_values
	$key=$k;
      }
      else if(preg_match("/^tag:([^><=!]*)(=|>|<|>=|<=|!=)([^><=!].*)$/", $key, $m)) {
	$key=$m[3];
      }
      elseif(preg_match("/^tag:([^><=!]*)$/", $key, $m)) {
	$key=$m[1];
      }


      return $key.(sizeof($params)?" ".implode(", ", $params):"");
    }
    else {
      $l=$lang_str[$key];
    }
  }

  if(!isset($l)) {
    return null;
  }
  elseif(is_array($l)&&(sizeof($l)==1)) {
    $l=$l[0];
  }
  elseif(is_array($l)) {
    if(($count===0)||($count!=1))
      $i=1;
    else
      $i=0;

    // if a Gender is defined, shift values
    if(is_integer($l[0]))
      $i++;

    $l=$l[$i];
  }

  return vsprintf($l, $params);
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
    if(!array_key_exists('q', $acc_lang))
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

function lang_load($lang, $loaded=array()) {
  global $lang_str;
  global $modulekit;

  $lang_str=array();

  @include(modulekit_file("modulekit-lang", "lang/base_{$lang}.php"));
  @include(modulekit_file("modulekit-lang", "lang/lang_{$lang}.php"));
  @include("lang/tags_{$lang}.php");
  foreach($modulekit['order'] as $module) {
    @include(modulekit_file($module, "lang_{$lang}.php"));
    @include(modulekit_file($module, "lang/{$lang}.php"));
  }
  $loaded[]=$lang;

  if(!isset($lang_str['base_language']))
    $lang_str['base_language']="en";
  if(in_array($lang_str['base_language'], $loaded))
    return;

  $save_lang_str=$lang_str;
  lang_load($lang_str['base_language'], $loaded);
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

  // list of available languages
  if((!isset($languages))||(!is_array($languages))) {
    $main_module=modulekit_loaded("");
    if(isset($main_module['languages']))
      $languages=$main_module['languages'];
    else
      $languages=array("en");
  }

  // Find language to use
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

  // Load language files
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

// Create twig 'lang' function
register_hook("twig_init", function() {
  global $twig;

  $twig->addFunction(new Twig_SimpleFunction('lang', function() {
    return call_user_func_array("lang", func_get_args());
  }));
});
