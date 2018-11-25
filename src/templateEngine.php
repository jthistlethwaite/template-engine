<?php
/**
 * @author Jason Thistlethwaite
 * @package jthistlethwaite/template-engine
 * @license BSD
 *
 * Copyright 2016 - 2018 Jason Thistlethwaite
 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the
 * following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
 * 3. Neither the name of the copyright holder nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */
namespace jthistlethwaite\templateEngine;

/**
 * Class for templating output
 *
 * Simple, light-weight class for templating HTML inside a PHP web application.
 *
 */
class templateEngine {

    /**
     * @var int Max recursion depth for templateOutputRecursive
     */
    public $maxRecursionDepth = 32;

    /**
     * @var int Tracks total number of calls to templateOutput
     */
    private $calls = 0;

    /**
     * @var string Local path where language files can be found
     */
    public $langStorage;

    /**
     * @var string Language to fall back on
     */
    public $fallBackLang = 'en';

    public function getLangStorange()
    {
        if ($this->langStorage == null) {
            $langStorage = dirname(__FILE__). DIRECTORY_SEPARATOR . '..'. 'langs';
        } else {
            $langStorage = $this->langStorage;
        }

        return $langStorage;
    }

    public function getLangs() {
        if ($this->langStorage == null) {
            $langStorage = dirname(__FILE__). DIRECTORY_SEPARATOR . 'langs';
        } else {
            $langStorage = $this->langStorage;
        }

        if (!is_dir($langStorage)) {
            throw new \Exception("langStorage folder doesn't exist '$langStorage''");
        }

        $langs = glob($langStorage. DIRECTORY_SEPARATOR. "*");



        $retLangs = array();

        foreach ($langs as $lang) {
            @$retLangs[] = array_pop(explode(".", basename($lang)));
        }

        return $retLangs;
    }

    /**
     * Include a file referenced inside a language file by @import
     *
     * Called from within translate when a string substition is formatted like:
     *
     *  general.string_name = @import somefile.html <options>
     *
     * In this case {string:general.string_name} is replaced with the contents of somefile.html
     *
     * @param $importName string Name of the file to import
     * @param $langStorage string|null Directory to search for $importName
     * @param $options array Array of options about how to handle the imported file
     *
     *  $options should be specified inside the language file as comma-delimited values after the name of the file
     *
     * @param $lang string Language we are using; same usage as translate()
     *
     * @return string Imported file contents OR "[ $importName not found ]" if the file doesn't exist
     * @throws \Exception On invalid options
     *
     * =======================================
     * Options list
     * =======================================
     *
     * Options are specified as a comma-delimited list of the following allowed values.
     *
     * Some options take an argument, which is passed with the =
     *
     * Example: callback=strip_tags
     *
     *
     * Options:
     *      - url       try to download $importName and use it as the string
     *
     *                  NOTE: This has potential security risks
     *
     *      - exec      call include on the file, capture any output as the string
     *
     *                  exec and url are mutually exclusive; they cannot be used together
     *
     *      - callback  Run the specified callback function on the string
     *
     *                  Example: callback=strip_tags will run strip_tags on the string before returning it
     *
     * =======================================
     * End options list
     * =======================================
     *
     *
     * BIG WARNING - BIG WARNING - BIG WARNING
     * =======================================
     *
     * This function assumes your language files are created by a trusted person. There are some security issues to
     * be aware of if untrusted individuals are able to edit your language files:
     *
     * DIRECTORY TRAVERSAL:
     *
     * This function DOES NOT attempt to prevent directory traversal. If your language files are not known-secure
     * input you should definitely make sure $importName is not a fully qualified file path.
     *
     * Example could be @import ../../../../../etc/passwd
     *
     *
     */
    private function importTranslation($importName, $langStorage = null, array $options = null, $lang)
    {

        // Full path to the file we want to import
        $importTarget = '';

        $importName = trim($importName);

        if (isset($options['url']) && isset($options['exec'])) {
            throw new \Exception("Options url and exec cannot be used together");
        }

        /*
         * If the url option is passed, we try to download the HTML contents found at the URL
         */
        if (isset($options['url'])) {
            $importTarget = $importName;
        } else {
            $importTarget = $langStorage. DIRECTORY_SEPARATOR. $importName;
        }

        if (isset($options['exec'])) {
            ob_start();
            include $importTarget;
            $string = ob_get_flush();
        } else {
            $string = file_get_contents($importTarget);
        }


        if ($string === false) {
            $string = "[ @import $importName unavailable ]";
        }

        /*
         * If a callback is specified, pass the string to it as the first option
         */
        if (isset($options['callback'])) {

            if (function_exists($options['callback'])) {
                $string = call_user_func($options['callback'], $string);
            }
        }

        return $string;
    }

    /**
     * @param string $input
     * @param string $lang Which language to use. File must exist inside of the strings folder
     *
     *  Using "raw" leaves all strings untranslated to get the raw text. This is for aiding in
     *  internationalization so the translator can easily see what text is which thing on the page.
     *
     * @param bool $followImports Allow imports / external file references inside language file; Default false
     *
     * @see importTranslation for details
     *
     * @return bool
     * @throws \Exception
     */
    public function translate(&$input, $lang, $followImports = false)
    {
        if ($lang == 'raw') {
            return true;
        }

        $lang = basename($lang);

        if ($this->langStorage == null) {
            $langStorage = dirname(__FILE__). DIRECTORY_SEPARATOR . 'langs';
        } else {
            $langStorage = $this->langStorage;
        }

        if (!is_dir($langStorage)) {
            throw new \Exception("langStorage folder doesn't exist '$langStorage''");
        }

        $langFile = $langStorage . DIRECTORY_SEPARATOR . 'main.'. $lang;

        if (is_file($langFile)) {
            $langArray = parse_ini_file($langFile);

            foreach ($langArray as $placeholder => $string) {
                $search = "{string:$placeholder}";

                /*
                 * If the translation string isn't in the template we move on to the next one
                 *
                 * For language files using imports or several thousand strings, this is a performance enhancement
                 *
                 */
                if ( strpos($input, $search) === false) {
                    continue;
                }

                /**
                 * @todo Set up something that makes it easy to visually tell which strings don't have a translation
                 */
                if ( strpos($string, '@import') === 0 ) {

                    if ($followImports !== true) {
                        $string = "[$string not included; imports disabled]";
                    } else {
                        $import = preg_split('/\s+/', $string);

                        $targetName = $import[1];

                        $options = array();
                        $realOptions = array();

                        if (isset($import[2])) {
                            $options = explode(',', $import[2]);

                            foreach ($options as $option) {
                                $realOption = explode("=", $option, 2);

                                $realOptions[$realOption[0]] = isset($realOption[1]) ? $realOption[1] : true;
                            }
                        }

                        $string = $this->importTranslation($targetName, $langStorage, $realOptions, $lang);
                    }


                }


                $input = str_replace($search, $string, $input);
            }
        }


        $failOver = new templateEngine();
        $failOver->langStorage = $this->langStorage;

        if ($lang != $this->fallBackLang) {
            $failOver->translate($input, $this->fallBackLang);
        }

    }

    /**
     * Applies transforms to the given template when passed an array of values
     *
     * @param string $template String to apply transforms on
     * @param array $pageVars Associative array of values for translation / templating
     * @param string $selector Template indicator
     *
     * By default, looks for any text formatted as {$selector:$key} where $key is a key in $pageVars
     *
     * If passed an array like:
     * array (
     *  "username" => "Bob"
     * )
     *
     * All instances of {var:username} inside of $template will be replaced with Bob
     *
     * This can be used in a multi-pass scenario, where say for example you have a template like this:
     *
     * ==== start template
     *
     * {string:hello} {var:username}!
     *
     * {string:current_time} {var:time}
     *
     * ==== end template
     *
     * $strings = array(
     *  "hello" => "Welcome",
     *  "current_time" => "It is now"
     * );
     *
     * $vars = array(
     *  "username" => "Bob",
     *  "time" => date("H:i")
     * );
     *
     * === Look for {var:*} and replace from $vars
     * templateOutput($template, $vars);
     *
     * === Look for {string:*} and replace with $strings
     * templateOutput($template, $strings, "string");
     *
     * === Multi-dimensional arrays ===
     * @see templateOutputRecursive
     *
     *
     * === Importing Other Files ===
     *
     * A reserved namespace {@import:<file_name>} is used to include other files inside of a template
     *
     * It works like this:
     *
     * {@import:importsFolder/somefile.php}
     *
     * templateEngine will follow the path specified and include the file if it exists.
     *
     *
     */
    public function templateOutput(&$template, $pageVars, $selector = 'var') {

        $searchArray = array();
        $replaceArray = array();

        while ( ($importStart = strpos($template, '{@import:') ) !== false) {
            $importStop = strpos($template, '}', $importStart);

            $import = substr($template, $importStart, $importStop - $importStart);

            $importName = explode(":", $import, 2)[1];

            $importValue = '';

            if (is_file($importName)) {
                ob_start();
                include $importName;
                $importValue = ob_get_contents();
                ob_end_clean();
            } else {
                $importValue = "[ Failed to include $importName ]";
            }

            $template = str_replace($import. "}", $importValue, $template);
        }

        if ($this->calls > $this->maxRecursionDepth) {
            die("templateOutput: maxRecursionDepth reached");
        }

        foreach ($pageVars as $pageVar => $value) {


            $searchArray[] = '{' . $selector . ':' . $pageVar . '}';
            $replaceArray[] = $value;
        }
        
        /* @ added to squelch the notice about array to string conversion.
         * ~ Jason Thistlethwaite
         * 
         * After some research it seems this is normal behavior. str_replace is supposed to work
         * that way.
         */               
        $template = @str_replace($searchArray, $replaceArray, $template);

        $this->calls++;
    }

    /**
     * Recursively perform transforms on a template using an associative array for substitutions
     *
     * @see ../examples/template_with_multidimensional_arrays.php
     *
     * @param $template string Template to be completed; same as with templateOutput
     * @param $pageVars array Multi-dimensional associative array of replacement values
     * @param string $selector Selector used to find replacements
     * @throws \Exception On maxRecursionDepth exceeded
     */
    public function templateOutputRecursive(&$template, $pageVars, $selector = 'var')
    {
        if ($this->calls > $this->maxRecursionDepth) {
            throw new \Exception("maxRecursionDepth ". $this->maxRecursionDepth. " reached");
        }

        $this->templateOutput($template, $pageVars, $selector);

        $deepData = array_filter($pageVars, function ($key) {
           if (is_array($key)) {
               return true;
           }

           return false;
        });

        foreach ($deepData as $selector => $vars) {
            $this->templateOutputRecursive($template, $vars, $selector);
        }

    }

    public function getWithinTags($baseTag, $string) {
        $startTag = "@$baseTag";
        $endTag = "$baseTag@";

        $sectionStart = strpos($string, $startTag);
        if ($sectionStart === false) {
            return $string;
        }

        $sectionEnd = strpos($string, $endTag, $sectionStart + strlen($startTag));
        if ($sectionEnd === false) {
            throw new \Exception("Missing closing tag for $baseTag");
        }

        $string = trim( substr($string, $sectionStart + strlen($startTag), ($sectionEnd - $sectionStart) - strlen($endTag)) );

        return $string;
    }


}
