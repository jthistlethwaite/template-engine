<?php
/**
 * @author Jason Thistlethwaite
 * @package templateEngine
 *
 * This example shows how to use templateEngine for multi-language support in an application
 *
 * Basic setup:
 *
 * 1) Define language files
 *     - You need to create a file called main.language for each language you wish to support
 *
 *     - put them together in a folder, and set this folder as $langStorage in templateEngine
 *
 * 2) Use in templates
 *
 *  The strings you want to translate should be formatted like {string:name_of_string} within your templates
 *
 *  name_of_string should be referenced within your language files
 *
 *  (see folder translationLangs for a very basic example)
 *
 * 3) Before rendering the template for your user, call templateEngine->translate($template, $lang)
 *
 *  - This will look for main.$lang inside of your $langStorage and attempt to translate all {string:name_of_string}
 *    instances in your template.
 *
 *  - A second pass is run using templateEngine->fallBackLang, which should catch any untranslated strings
 *    if they are defined within the main.$fallBackLang
 *
 *
 */
require_once '../src/templateEngine.php';

$engine = new \jthistlethwaite\templateEngine\templateEngine();
$engine->langStorage = dirname(__FILE__). DIRECTORY_SEPARATOR. 'translationLangs';

$vars = array(
    "name" => "Bob"
);

/*
 * Our template includes two strings we want to translate, as well as some variables we want to replace
 */
$template =<<<EOT
{string:hello} {var:name}, {string:general.current_time}

{string:big_html}

{string:current_time}

{string:http_example}
EOT;

/*
 * In this example, we translate the above template using en, fr, de, and a special language "raw"
 *
 * "raw" disables translation. It's intended for aiding in translation work by displaying the raw template
 */
foreach (["en", "fr", "de", "raw"] as $lang) {
    $copyTemplate = $template;

    $engine->translate($copyTemplate, $lang, true);
    $engine->templateOutputRecursive($copyTemplate, $vars);

    echo "===== $lang =====\n".
        $copyTemplate.
        "\n===== end $lang =====\n\n";
}





