const ModulekitLang = require('./ModulekitLang')

const lang_detect_ui_lang = require('./lang_detect_ui_lang')

const loaded = {}
let current = null

module.exports = {
  /**
   * @param {function} callback - callback which will be passed (err, list). List is a hash with iso codes as key and language name in the respective language.
   */
  languageList (callback) {
    callback(null, require('../lang/list.json'))
  },

  /**
   * @param {(string|null)} lang - iso code of language or null for autodetection
   * @param {function} callback - callback which will be called when loading finished
   */
  set (lang, callback) {
    if (!lang) {
      lang = lang_detect_ui_lang()
    }

    if (lang in loaded) {
      current = loaded[lang]
      return callback(null)
    }

    const newLang = new ModulekitLang(lang)
    newLang.load((err) => {
      current = newLang
      callback(err)
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
  }
}
