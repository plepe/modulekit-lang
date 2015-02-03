var lang_str={};

function change_language() {
  var ob=document.getElementById("lang_select_form");
//  ob.action=get_permalink();
  ob.submit();
}

function lang() {
  // if 'key' is an array, translations are passed as array values, like:
  // {
  //   'en':	"English text",
  //   'de':	"German text"
  // }
  // optionally a prefix can be defined as second parameter, e.g.
  //
  // if current language is not defined in the array the first language
  // will be used (in that case 'en').
  var args = []
  for(var i=0; i<arguments.length; i++)
    args[i] = arguments[i];

  var count = null;
  if(typeof args[0] == "number") {
    count = args[0];
    args = args.slice(1);
  }

  var key = args[0];
  args = args.slice(1);

  var def;
  if(typeof key == "object") {
    if(ui_lang in key)
      def = key[ui_lang];
    else
      for(var k in key) {
        def = key[k];
        break;
      }
  }
  else {
    if(!key in lang_str)
      return key;

    def = lang_str[key];
  }

  var str;
  if(typeof def == "string")
    str = def;
  else {
    if(count === null)
      str = def.message;
    else if(count in def)
      str = def[count];
    else if((count != 1) && ('!=1' in def))
      str = def['!=1'];
    else
      str = def.message;
  }

  return vsprintf(str, args);
}

function lang_dom(str, count) {
  var el=lang_element(str, count);
  var ret="";

  if(arguments.length<=2) {
    ret=el;
  }
  else {
    var vars=[];
    for(var i=2;i<arguments.length;i++) {
      vars.push(arguments[i]);
    }

    ret=vsprintf(el, vars);
  }

  var dom=document.createElement("span");
  dom.setAttribute("lang_str", str);
  dom_create_append_text(dom, ret);
  
  return dom;
}

function t(str, count) {
  // TODO: write deprecation message to debug

  return lang(str, count);
}

// replace all {...} by their translations
function lang_parse(str, count) {
  var ret=str;
  var m;

  while(m=ret.match(/^(.*)\{([^\}]+)\}(.*)$/)) {
    var args=arguments;
    args[0]=m[2];

    ret=m[1]+lang.apply(this, args)+m[3];
  }

  return ret;
}

function lang_change(key, value) {
  // When the UI language changed, we have to reload
  if(key=="ui_lang") {
    if(value!=ui_lang) {
      // create new path
      var new_href=get_baseurl()+"#?"+hash_to_string(get_permalink());

      // Set new path and reload
      location.href=new_href;
      location.reload();
    }
  }

  // When the data language changed, we just remember
  // TODO: reload data
  if(key=="data_lang") {
    data_lang=value;
  }
}

function lang_code_check(lang) {
  return lang.match(/^[a-z\-]+$/);
}

register_hook("options_change", lang_change);
