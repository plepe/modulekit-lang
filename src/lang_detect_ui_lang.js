module.exports = function lang_detect_ui_lang () {
  if (global.ui_lang) {
    return global.ui_lang
  }

  if (!global.navigator) {
    return 'en'
  }

  if (!global.languages) {
    return 'en'
  }

  for (var i in navigator.languages) {
    if (languages.indexOf(navigator.languages[i]) !== -1) {
      return navigator.languages[i]
    }
  }

  return languages[0]
}
