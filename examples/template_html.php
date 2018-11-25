<?php

require_once '../src/templateEngine.php';

$engine = new \jthistlethwaite\templateEngine\templateEngine();

$vars = array(
    "username" => "Visitor",
    "date" => date("Y-m-d H:i:s")
);

$template = <<<EOT
<h1>Hello {var:username}</h1>
<p>
    Right now it's {var:date}
</p>
EOT;

$engine->templateOutput($template, $vars);

echo $template;