<?
$name="Language System";

$id="modulekit-lang";

$depend=array("hooks", "html", "sprintf");

$include=array(
  'php'=>array(
    "inc/lang.php",
    "inc/list.php",
  ),
  'mcp'=>array(
    "inc/lang.php",
    "inc/list.php",
  ),
  'js'=>array(
    "inc/lang.js",
  ),
);

$default_include=array(
  'php'=>array(
    'code.php',
  ),
  'js'=>array(
    'code.js',
  ),
  'css'=>array(
    'style.css',
  ),
);
