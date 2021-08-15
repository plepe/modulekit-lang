const fs = require('fs')

class ModulekitLang {
  constructor (lang) {
    this.language = lang
  }

  lang_shall_count_translations() {
    if (typeof lang_non_translated === 'undefined') {
      return false
    }

    if (lang_non_translated === null) {
      return false
    }

    if (typeof lang_non_translated !== 'object') {
      return false
    }

    if (Array.isArray(lang_non_translated)) {
      lang_non_translated = {}
    }

    return true
  }

  lang_element(str, options) {
    var l;
    var non_translated_counted = false

    if (this.lang_shall_count_translations() && str in lang_non_translated) {
      lang_non_translated[str]++
      non_translated_counted = true
    }


    if(l=this.lang_str[str]) {
      if(typeof(l)=="string")
        return l;

      var i;
      if(l.length && l.length>1) {
        if((options.count===0)||(options.count>1))
          i=1;
        else
          i=0;

        // if a Gender is defined, shift values
        if(typeof(l[0])=="number")
          i++;

        return l[i];
      }
      else if(l.length && l.length==1) {
        return l[0];
      }
      else if (typeof l === 'object') {
        if ('!=1' in l && (options.count === 0 || options.count > 1)) {
          return l['!=1']
        }

        if ('message' in l) {
          return l['message']
        }
      }
    }

    if(typeof debug=="function")
      debug(str, "language string missing");

    if (this.lang_shall_count_translations() && !non_translated_counted) {
      if (str in lang_non_translated) {
        lang_non_translated[str]++
      } else {
        lang_non_translated[str] = 1
      }
    }

    if (options.default) {
      return options.default
    }
    else if((l=str.match(/^tag:([^=]+)=(.*)$/))&&(l=this.lang_str["tag:*="+l[2]])) {
      // Boolean values, see:
      // http://wiki.openstreetmap.org/wiki/Proposed_features/boolean_values
      return l;
    }
    else if(l=str.match(/^tag:(.*)(=|>|<|>=|<=|!=)([^><=!]*)$/)) {
      return l[3];
    }
    else if(l=str.match(/^tag:([^><=!]*)$/)) {
      return l[1];
    }

    if(l=str.match(/^[^:]*:(.*)$/))
      return l[1];

    return str;
  }

  lang(str, options) {
    var el;

    // if 'key' is an array, translations are passed as array values, like:
    // {
    //   'en':	"English text",
    //   'de':	"German text"
    // }
    // optionally a prefix can be defined as second parameter, e.g.
    //
    // x = {
    //   'en':		"English text",
    //   'de':		"German text"
    //   'desc:en':	"English description",
    //   'desc:de':	"German description"
    // )
    // lang(x)            -> will return "English text" or "German text"
    // lang(x, 'desc:')   -> will return "English description" or "German description"
    //
    // if current language is not defined in the array the first language
    // will be used (in that case 'en').
    if (typeof options === 'number') {
      options = { count: options }
    }
    if (options === null || typeof options === 'undefined') {
      options = { count: null }
    }

    if(typeof str=="object") {
      let prefix = ""
      if((arguments.length>1) && (typeof arguments[1] == "string")) {
        prefix = arguments[1];
        options = arguments[2];
      }

      if (typeof options === 'number') {
        options = { count: options }
      }

      if(typeof str[prefix + this.language] !== "undefined") {
        el=str[prefix + this.language];
      }
      else if(typeof str[prefix + 'en'] !== "undefined") {
        el=str[prefix + 'en'];
      }
      else {
        for(var i in str) {
          if(i.substr(0, prefix.length) == prefix) {
            el=str[i];
            break;
          }
        }
      }
    }
    else
      el=this.lang_element(str, options);

    if(arguments.length<=2)
      return el;

    var vars=[];
    for(var i=2;i<arguments.length;i++) {
      vars.push(arguments[i]);
    }

    return vsprintf(el, vars);
  }

  enumerate (list) {
    if (list.length > 2) {
      let result = this.lang_str.enumerate_start.replace('{0}', list[0]).replace('{1}', list[1])

      for (let i = 2; i < list.length - 1; i++) {
        result = this.lang_str.enumerate_middle.replace('{0}', result).replace('{1}', list[i])
      }

      return this.lang_str.enumerate_end.replace('{0}', result).replace('{1}', list[list.length - 1])
    }
    else if (list.length == 2) {
      return this.lang_str.enumerate_2.replace('{0}', list[0]).replace('{1}', list[1])
    }
    else if (list.length > 0) {
      return list[0]
    }

    return ''
  }

  lang_dom(str, count) {
    var el=this.lang_element(str, count);
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

  load (callback) {
    this.lang_str = undefined

    this.language_list = require('../lang/list.json')
    if (!global.XMLHttpRequest) {
      return this.lang_init2(callback)
    }
    if (typeof global.languages === 'undefined') {
      this.languages = Object.keys(this.language_list)
    } else {
      this.languages = languages
    }

    this.lang_init2(callback)
  }

  lang_init2 (callback) {
    if (typeof this.lang_str === 'undefined') {
      if (typeof global.XMLHttpRequest === 'undefined') {

        fs.readFile('dist/lang_' + this.language + '.json',
          (err, body) => {
            if (err) {
              this.lang_str = {}
              return callback(err)
            }

            this.lang_str = JSON.parse(body)
            callback()
          }
        )

        return
      }

      var req = new XMLHttpRequest()
      req.addEventListener('load', () => {
        if (req.status === 200) {
          this.lang_str = JSON.parse(req.responseText)
        } else {
          this.lang_str = {}
          console.log('error occured when download translation', req)
        }

        callback()
      })

      var path = 'dist/'
      if (typeof modulekit_dist_path !== 'undefined') {
        path = modulekit_dist_path
      }

      req.open('GET', path + '/lang_' + this.language + '.json')
      req.send()
    } else {
      callback()
    }
  }
}

module.exports = ModulekitLang
