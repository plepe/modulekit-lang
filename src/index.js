const ModulekitLang = require('./ModulekitLang')

const lang_detect_ui_lang = require('./lang_detect_ui_lang')

const loaded = {}
let current = null

module.exports = {
  languageList (callback) {
    callback(null, require('../lang/list.json'))
  },

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

  current () {
    return current
  },

  lang () {
    return current.lang.apply(current, arguments)
  }
}
