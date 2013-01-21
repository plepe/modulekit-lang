<?
function lang_chooser_minimal() {
  global $language_list;
  global $lang;
  
  $ret.="<div id='lang_chooser'>\n";
  foreach($language_list as $code=>$name) {
    $r ="<a href='?ui_lang=$code'";
    if($ui_lang==$code)
      $r.=" class='chosen'";
    $r.="><img src='".modulekit_file("lang", "img/$code.png")."' alt='$name' title='$name'/></a>\n";
    $ret.=$r;
  }
  $ret.="</div>\n";

  return $ret;
}

