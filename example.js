const ModulekitLang = require('modulekit-lang')

ModulekitLang.set('pt-br', (err) => {
  if (err) {
    console.error(err)
  }

  console.log(ModulekitLang.lang('lang:de'))
})

ModulekitLang.languageList((err, list) => {
  if (err) {
    console.error(err)
  }

  console.log(list)
})
