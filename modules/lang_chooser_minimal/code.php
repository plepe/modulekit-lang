<?php
function lang_chooser_minimal() {
  global $language_list;
  global $languages;
  global $ui_lang;
  
  $ret.="<div id='lang_chooser_minimal'>\n";
  foreach($languages as $code) {
    $name=$language_list[$code];

    $r ="<a href='?ui_lang=$code'";
    if($ui_lang==$code)
      $r.=" class='chosen'";
    $r.="><img src='".modulekit_file("lang", "img/$code.png")."' alt='$name' title='$name'/></a>\n";
    $ret.=$r;
  }
  $ret.="</div>\n";

  return $ret;
}

