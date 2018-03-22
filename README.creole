== MODULEKIT-LANG ==
A translation library for use with Modulekit.

=== Howto ===
In your conf.php define an array $languages with a list of supported languages. First entry is the default language. E.g.:

conf.php: {{{
$languages=array("de", "en");
}}}

index.php: {{{
<?php include "conf.php"; /* load a local configuration */ ?>
<?php include "modulekit/loader.php"; /* loads all php-includes */ ?>
<?php
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
</script>
</body>
}}}

You may specify alternate language directories for a module, by setting the array $lang_dirs in modulekit.php .

=== Format description ===
A translation consists of a file in JSON format. It is based on [[https://developer.chrome.com/extensions/i18n-messages|i18n-messages as used in Chrome]]. Every translatable string is key to the JSON file with the translated string OR an embeded hash with a value "message" and possibly several further values. The most simple examples look like this (both are equal):

{{{
{
  "hello_world": "Hello World!"
}

{
  "hello_world": {
    "message": "Hello World!"
  }
}
}}}

The following values for a translatable string are possible:
* {{{description}}}: some text explaining this message.
* {{{gender}}}: if supported, the gender of the string (e.g. the german word "Restaurant" is neuter, therefore this value would be "neuter". May have the following values: "male", "female", "neuter".

It is possible to have the message for different counts (e.g. "1 cat", "5 cats"). Therefore the following keys may be supplied:
* {{{0}}}: special text for zero objects (e.g. "no cats").
* {{{1}}}: special text for one object.
* all other distinct counts: special text for any amount of objects.
* {{{!=1}}}: text for all amounts but 1.
* if no count matches, the {{{message}}} will be used.

If a string has not been translated (yet), the {{{message}}} value will be missing.
