<?php include "conf.php"; /* load a local configuration */ ?>
<?php include "modulekit/loader.php"; /* loads all php-includes */ ?>
<?php
call_hooks("init"); /* Initializes all modules, also lang module */
Header("Content-Type: text/html; charset=UTF-8");
?>
<html>
<head>
  <?php print print_add_html_headers(); /* prints additional html headers */ ?>
  <?php print modulekit_to_javascript(); /* pass modulekit configuration to JavaScript */ ?>
  <?php print modulekit_include_js(); /* prints all js-includes */ ?>
  <?php print modulekit_include_css(); /* prints all css-includes */ ?>
</head>
<body>
<?php print lang("lang:current"); ?>
<script type='text/javascript'>
window.onload = function () {
  alert(lang("lang:current"));
}
</script>
</body>
