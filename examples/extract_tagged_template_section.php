<?php
/**
 * @author Jason Thistlethwaite
 * @package templateEngine
 *
 * This example shows how to use section tags within a template
 *
 * The use case:
 *
 * This can be used to include several variations of a template inside one file. The following example
 * shows a template for both a "success" status and "error" status.
 *
 */

require_once '../src/templateEngine.php';

/*
 * Variables available within our template
 */
$vars = array(
    "username" => "Bob",
    "successMessage" => "You have done the thing!",
    "failureMessage" => "It didn't work."
);

$template = <<<EOT
@success
Congrats {var:username}!
<p>
    {var:successMessage}
</p>
success@
@error
Sorry {var:username}, our web monkeys are out to lunch.
<p>
    {var:failureMessage}
</p>
error@
EOT;

$engine = new \jthistlethwaite\templateEngine\templateEngine();

/*
 * Retrieve and process only the "success" section of the template
 */
$status = 'success';
$output = $engine->getWithinTags($status, $template);
$engine->templateOutput($output, $vars);

echo $output;

/*
 * Retrieve and process only the "error" section of the template
 */
$status = 'error';
$output = $engine->getWithinTags($status, $template);
$engine->templateOutput($output, $vars);

echo $output;