module.exports = function copyLangStr (from, to) {
  for (let lang in from) {
    if (!(lang in to)) {
      to[lang] = {}
    }

    for (let k in from[lang]) {
      let str = from[lang][k]
      if (str === null || (typeof str === 'string' && str === '') ||
          (typeof str === 'object' && (!('message' in str) || str.message === ''))) {
        continue
      }

      to[lang][k] = str
    }
  }
}


