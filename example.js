const ModulekitLang = require('modulekit-lang')

let currentLang = new ModulekitLang('en')
currentLang.load(
  (err) => {
    console.log(currentLang.lang('lang:de'))
  }
)
