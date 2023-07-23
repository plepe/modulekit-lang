module.exports = function lang_detect_ui_lang () {
  if (global.ui_lang) {
    return global.ui_lang
  }

  if (!global.navigator) {
    return 'en'
  }

  const languages = global.languages ?? Object.keys(require('../lang/list.json'))

  for (var i in navigator.languages) {
    if (languages.indexOf(navigator.languages[i]) !== -1) {
      return navigator.languages[i]
    }
  }

  return (global.languages ?? ['en'])[0]
}
