<?php

require_once '../src/templateEngine.php';

$engine = new \jthistlethwaite\templateEngine\templateEngine();

$vars = array(
    "username" => "Visitor",
    "date" => date("Y-m-d H:i:s"),
    "userData" => array(
        "email" => "bob@nowhere.com",
        "link" => "bob.com/bob"
    ),
    "siteData" => array(

        "version" => "0.0.1-beta",
        "build" => '1338'
    )
);

$template = <<<EOT
<h1>Hello {var:username}</h1>
<p>
    Right now it's {var:date}
</p>
<p>
    {userData:email}
</p>
<p>
    {userData:link}
</p>
<p>
    {siteData:version} - {siteData:build}
</p>
EOT;

$engine->templateOutputRecursive($template, $vars);

echo $template;