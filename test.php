<?php include "conf.php"; /* load a local configuration */ ?>
<?php include "modulekit/loader.php"; /* loads all php-includes */ ?>
<?php
$ui_lang = "de";
call_hooks("init"); /* Initializes all modules, also lang module */
Header("Content-Type: text/html; charset=UTF-8");
?>
<html>
<head>
  <?php print modulekit_to_javascript(); /* pass modulekit configuration to JavaScript */ ?>
  <?php print modulekit_include_js(); /* prints all js-includes */ ?>
  <?php print modulekit_include_css(); /* prints all css-includes */ ?>
  <?php print print_add_html_headers(); /* prints additional html headers */ ?>
</head>
<body>
<?php print lang("lang:current"); ?>
<script type='text/javascript'>
alert(lang("lang:current"));
alert(
  lang_enumerate([ 'foo' ]) + '\n' +
  lang_enumerate([ 'foo', 'bar' ]) + '\n' +
  lang_enumerate([ 'foo', 'bar', 'bla' ]) + '\n' +
  lang_enumerate([ 'foo', 'bar', 'bla', 'test' ]) + '\n' +
  lang_enumerate([ 'foo', 'bar', 'bla', 'test', 'xxx' ]) + '\n' +
  lang_enumerate([ 'foo', 'bar', 'bla', 'test', 'xxx', 'aaa' ]))
</script>
<br>
<?php
print lang_enumerate(array('foo')) . "<br>\n";
print lang_enumerate(array('foo', 'bar')) . "<br>\n";
print lang_enumerate(array('foo', 'bar', 'bla')) . "<br>\n";
print lang_enumerate(array('foo', 'bar', 'bla', 'test')) . "<br>\n";
print lang_enumerate(array('foo', 'bar', 'bla', 'test', 'xxx')) . "<br>\n";
?>
</body>
