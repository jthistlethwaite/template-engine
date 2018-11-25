<?php

require_once '../src/templateEngine.php';
$engine = new \jthistlethwaite\templateEngine\templateEngine();

$template = <<<EOT
{var:name}
<hr>

Import me:
{@import:validImports/import_me2.php}

Something.html:
{@import:validImports/something.html}

EOT;

$vars = array(
    "name" => "Bob",
);


$engine->templateOutputRecursive($template, $vars);

echo $template;
