# templateEngine
A simple, lightweight template engine for PHP web applications.



## Features

* Recursive string replacement using associative arrays of string matches
* Importing other documents, which are also parsed
* String translation for multi-language support

### Simple usage:

```php
<?php
$engine = new templateEngine();
$template = <<<EOT
<h1>Hello {var:username}!</h1>
<p>
    Welcome back! You last visited {var:last_visit}
</p>
EOT;

$vars = [
    "username" => "Eugene",
    "last_visit" => "3 days ago"
];

$engine->templateOutputRecursive($template, $vars);
echo $template;
?>
```

Output would be:

```html
<h1>Hello Eugene!</h1>
<p>
	Welcome back! You last visited 3 days ago
</p>
```

## Examples

The included examples folder show many examples of how templateEngine may be used.



## License

templateEngine is released under the following license:

Copyright 2016 - 2018 Jason Thistlethwaite

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the
following conditions are met:

1. Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
3. Neither the name of the copyright holder nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.