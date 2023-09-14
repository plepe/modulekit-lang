const ModulekitLang = require('./ModulekitLang')

const lang_detect_ui_lang = require('./lang_detect_ui_lang')

const loaded = {}
const callbacksSet = {}
let current = new ModulekitLang(null, {})
current.lang_str = {}

module.exports = {
  /**
   * @param {function} callback - callback which will be passed (err, list). List is a hash with iso codes as key and language name in the respective language.
   */
  languageList (callback) {
    callback(null, require('../lang/list.json'))
  },

  /**
   * @param {(string|null)} lang - iso code of language or null for autodetection
   * @param {object} [options] - additional options
   * @param {function} callback - callback which will be called when loading finished
   */
  set (lang, options, callback) {
    if (!lang) {
      lang = lang_detect_ui_lang()
    }

    if (lang in loaded) {
      current = loaded[lang]
      return callback(null)
    }

    if (lang in callbacksSet) {
      return callbacksSet[lang].push(callback)
    }

    callbacksSet[lang] = [ callback ]

    const newLang = new ModulekitLang(lang, options)
    newLang.load((err) => {
      loaded[lang] = newLang
      current = newLang

      callbacksSet[lang].forEach(cb => cb(err))
      delete callbacksSet[lang]
    })
  },

  /**
   * @returns {ModulekitLang} the current lanuage object
   */
  current () {
    return current
  },

  /**
   * @param {string|object} str - the key to translate (e.g. 'lang:de' or 'cancel').
   * @param {object} [options] - options
   * @returns {string} the translated string
   */
  lang () {
    return current.lang.apply(current, arguments)
  },

  /**
   * Enumerate a list of items (e.g. 'a, b and c')
   * @param {string[]} list - The list to enumerate
   * @returns {string} the enumerated list
   */
  enumerate () {
    return current.enumerate.apply(current, arguments)
  },

  /**
   * All defined strings in the current language
   * @returns {object} all defined strings
   */
  strings () {
    return current.lang_str
  }
}
