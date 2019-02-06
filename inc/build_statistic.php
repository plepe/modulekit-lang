<?php
function build_statistic ($path) {
  $result = array();

  $template = array();
  if (file_exists("{$path}/template.json")) {
    $template = json_decode(file_get_contents("{$path}/template.json"), true);
  }
  elseif (file_exists("{$path}/en.json")) {
    $template = json_decode(file_get_contents("{$path}/en.json"), true);
  }
  $template_count = sizeof(array_keys($template));

  $result[''] = $template_count;

  $d = opendir($path);
  while ($f = readdir($d)) {
    if (!preg_match("/^(.*)\.json$/i", $f, $m) || substr($f, 0, 1) === '.' || $f === 'package.json' || $f === 'descriptions.json' || $f === 'template.json') {
      continue;
    }

    $lang = $m[1];
    $content = json_decode(file_get_contents("{$path}/{$f}"), true);
    $count = 0;
    foreach ($content as $k => $v) {
      if ($v === null || $v === '') {
        continue;
      }

      if (is_array($v) && (!array_key_exists('message', $v) || $v['message'] === '')) {
        continue;
      }

      if (!array_key_exists($k, $template)) {
        continue;
      }

      $count++;
    }

    $result[$lang] = $count;
  }

  return $result;
}
