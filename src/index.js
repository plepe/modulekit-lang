const ModulekitLang = require('./ModulekitLang')

const loaded = {}
let current = null

module.exports = {
  set (lang, callback) {
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
