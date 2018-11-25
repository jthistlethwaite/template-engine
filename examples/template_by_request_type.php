<?php
/**
 * @author Jason Thistlethwaite
 * @package templateEngine
 *
 * This example shows how different output can be shown depending on the http request type
 */
require_once '../src/templateEngine.php';

$vars = array();

$template = <<<EOT
@POST
Your sent a POST request with the following information:
<pre>{var:data}</pre>
POST@
@GET
You sent a GET request. Click the button for a POST

<form method="POST">
    <textarea name="stuff" placeholder="Put stuff here"></textarea>
    <input type="submit">
</form>

GET@
EOT;

$section = $_SERVER['REQUEST_METHOD'];

$vars['data'] = print_r($_REQUEST, true);

$engine = new \jthistlethwaite\templateEngine\templateEngine();
$output = $engine->getWithinTags($section, $template);

$engine->templateOutputRecursive($output, $vars);

echo $output;



?>