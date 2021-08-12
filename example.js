const ModulekitLang = require('modulekit-lang')

ModulekitLang.set('en', (err) => {
  if (err) {
    console.log(err)
  }

  console.log(ModulekitLang.lang('lang:de'))
})
