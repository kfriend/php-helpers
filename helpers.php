<?php

// =============================================================================
// Array helpers
// =============================================================================

/**
 * Array splice, associatively
 *
 * Splices a value into an array by targeting a key, instead of an offset.
 *
 * @param  array $array        The array to splice into.
 * @param  string $key         The key replace with $replacement.
 * @param  array  $replacement The value you'd like to replace $key with.
 * @return array               The modified array, or $array, if $key not found.
 */
function array_splice_assoc(array $array, $key, array $replacement = array())
{
    // Get numeric offset of the key to remove
    $offset = array_search($key, array_keys($array));

    if (!is_int($offset)) {
        return $array;
    }

    unset($array[$key]);

    return array_slice($array, 0, $offset, true)
        + (array) $replacement
        + array_slice($array, $offset, null, true);

    return $array;
}

/**
 * Array random
 *
 * Randomly picks a value from an array. PHP's array_rand() only returns index
 * values, so this functions wraps that one, returning an array of random values.
 *
 * @param  array   $array  The array to draw from.
 * @param  integer $number The number or random elements to pick.
 * @return array           An array containing the randomly selected values.
 */
function array_random(array $array, $number = 1)
{
    $random = array();

    // array_rand() only return the indexes, not their index and value, so we'll
    // need to iterate over the random index, and extract our values after
    $selected = (array) array_rand($array, $number);

    foreach ($selected as $index) {
        $random[] = $array[$index];
    }

    return $random;
}

/**
 * Array random, associatively
 *
 * Returns an array comprised of randomly selected keys and their values from $array.
 *
 * @param  array   $array  The array to draw from.
 * @param  integer $number The number or random elements to pick.
 * @return array           An array containing the randomly selected values.
 */
function array_random_assoc(array $array, $num = 1)
{
    $keys = array_keys($array);
    shuffle($keys);

    $r = array();
    for ($i = 0; $i < $num; $i++) {
        $r[$keys[$i]] = $array[$keys[$i]];
    }

    return $r;
}

/**
 * Array random item
 *
 * Returns a random element from an array.
 *
 * @param  array $array The array to pick an element from.
 * @return array        The random element, or null if no elements exist.
 */
function array_item_rand(array $array)
{
    if (!$array) {
        return null;
    }

    return $array[array_rand($array)];
}

/**
 * Create an index from an array using the provided key.
 *
 * Produces an array where the keys are values pulled from sub arrays, creating
 * an index for easy lookup.
 *
 * Note: This does not account for duplicate index keys. The last key of a specific
 * value will be the final value for the index key. Use for situations where you're
 * confident there will only be single index value, such as numeric DB ids, usernames,
 * emails, etc.
 *
 * Note 2: If a sub array does not have the passed key, it will be excluded from the index
 *
 * @param  array  $array The array to create an index. Should be an array of arrays.
 * @param  string $key   The key to index by. E.g. username, id, etc. Something unique.
 * @return array        The indexed array (values aren't referenced).
 */
function array_index(array $array, $key)
{
    $index = array();
    $count = count($array);

    for ($i = 0; $i < $count; $i++) {
        if (!array_key_exists($key, $array[$i])) {
            continue;
        }

        $index[$array[$i][$key]] = $array[$i];
    }

    return $index;
}

/**
 * Array index with callback function
 *
 * Similar to the array_index() function, this function will create an array that
 * organizes values by a given key. However, unlike array_index(), this function
 * accepts a callable for the key, instead of a string. The callable will be passed
 * each sub array, and should return the value which the sub array should be indexed
 * under. This allows logic to be used to determine the index, including normalization,
 * modification, etc.
 *
 * @param  array  $array    The array to index.
 * @param  callable $callback The callable to handle index logic.
 * @return array           The indexed array
 */
function array_index_callback(array $array, $callback)
{
    $index = array();
    $count = count($array);

    for ($i = 0; $i < $count; $i++) {
        $indexKey = $callback($array[$i]);
        $index[$indexKey] = $array[$i];
    }

    return $index;
}

/**
 * Array group
 *
 * Creates a multidimensional index from an array.
 *
 * This will create an array of arrays of arrays. The sub arrays will contain
 * all the original array values that share the same value for $key.
 *
 * Example:
 *
 * array_group([
 *     ['name' => 'Kevin', 'lang' => 'php'],
 *     ['name' => 'Pete', 'lang' => 'ruby'],
 *     ['name' => 'Greg', 'lang' => 'php']
 * ], 'lang')
 *
 * Will return:
 *
 * [
 *     'php' => [
 *         ['name' => 'Kevin', 'lang' => 'php'],
 *         ['name' => 'Greg', 'lang' => 'php'],
 *     ],
 *     'ruby' => [
 *         ['name' => 'Pete', 'lang' => 'ruby'],
 *     ]
 * ]
 *
 * Note: arrays which do not contain the key will be excluded.
 *
 * @param  array  $array The array of arrays to group.
 * @param  string $key   The key to group by.
 * @return array        The grouped array.
 */
function array_group(array $array, $key)
{
    $index = array();
    $count = count($array);

    for ($i = 0; $i < $count; $i++) {
        if (!array_key_exists($key, $array[$i])) {
            continue;
        }

        // Does the group exist already?
        if (!isset($index[$key])) {
            // No, so create it
            $index[$key] = array();
        }

        $index[$key][] = $array[$i];
    }

    return $index;
}

/**
 * Array group with callback function
 *
 * Similar to the array_group() function, this function will create an array of
 * arrays where the first tier arrays contain arrays from $array, grouped by an
 * index. The index is generated from the passed the callable $callback, which is
 * passed each member from $array. The $callback return value will be used as the
 * grouping index for the passed $array item.
 *
 * @param  array  $array    The array to group.
 * @param  callable $callback The callable to handle index logic.
 * @return array           The grouped array
 */
function array_group_callback(array $array, $callback)
{
    $index = array();
    $count = count($array);

    for ($i = 0; $i < $count; $i++) {
        $key = $callback($array[$i]);

        // Does the group exist already?
        if (!isset($index[$key])) {
            // No, so create it
            $index[$key] = array();
        }

        $index[$key][] = $array[$i];
    }

    return $index;
}

/**
 * Array translate
 *
 * Returns a new array where $data's keys are renamed using the $lookup. Only
 * the keys found in $lookup will be returned.
 *
 * Example:
 *
 * array_trans(['foo' => 'bar'], ['foo' => 'foozle', 'wizzle' => 'wazzle'])
 *
 * Returns:
 *
 * [
 *     'bar' => 'foozle',
 * ]
 *
 * @param  array  $lookup   The key renaming lookup. Keys are the old name, values are the new name.
 * @param  array $data      An array containing the keys to be renamed, and values.
 * @return array            An array with renamed keys. Only keys found in $lookup will be returned.
 */
function array_trans(array $lookup, array $data)
{
    $translated = array();

    foreach ($lookup as $from => $to) {
        if (array_key_exists($from, $data)) {
            $translated[$to] = $data[$from];
        }
    }

    return $translated;
}

// =============================================================================
// Array and object helpers
// =============================================================================

/**
 * Flatten data structure
 *
 * Takes a multidimensional array and flattens it down to a single dimensional array.
 * Will handle sub values of string, numeric, array, and object.
 *
 * Example:
 *
 * [
 *     'foo' => ['foozle' => 'barzle', 'wizzle' => 'wuzzle'],
 *     'bar' => ['apple' => 'red', 'orange' => ['size' => 'medium', 'shape' => 'round']]
 * ]
 *
 * Will be returned as:
 *
 * [
 *     'foo.foozle' => 'barzle',
 *     'foo.wizzle' => 'wuzzle',
 *     'bar.apple' => 'red',
 *     'bar.orange.size' => 'medium',
 *     'bar.orange.shape' => 'round',
 * ]
 *
 * @param  array|object $record     The record to flatten.
 * @param  string $prepend          A key prefix to add to all sub values.
 * @param  string $delimiter        The concatenated key delimiter. Defaults to ".".
 * @return array                    The flattened $record.
 */
function flatten($record, $prepend = '', $delimiter = '.')
{
    $results = array();

    foreach ($record as $key => $value) {
        if (is_array($value) or is_object($value)) {
            $results = array_merge($results, flatten((array) $value, $prepend.$key.$delimiter));
        } else {
            $results[$prepend.$key] = $value;
        }
    }

    return $results;
}

// =============================================================================
// String helpers
// =============================================================================

/**
 * Replace last occurrence of a sub string
 * Credit: http://stackoverflow.com/a/3835653
 *
 * @param  string $search  Substring to search for
 * @param  string $replace Replacement substring
 * @param  string $subject String to search
 * @return string          String
 */
function str_replace_last($search, $replace, $subject)
{
    $pos = strrpos($subject, $search);

    if ($pos !== false) {
        $subject = substr_replace($subject, $replace, $pos, strlen($search));
    }

    return $subject;
}

/**
 * Last Segment
 *
 * Returns the last segment from a string, where the segments are delimited by $delim
 *
 * last_segment('foo.bar.baz', '.') => 'baz'
 *
 * @param string $string The string to find that last segment from
 * @param string $delim The segment delimiter
 * @return string
 */
function str_last_segment($string, $delim = '/')
{
    $lastPos = strrpos(trim($string, $delim), $delim);

    if ($lastPos === false) {
        return $string;
    }

    return substr($string, $lastPos + 1);
}

/**
 * Escape HTML entities
 *
 * Escapes HTML in a string, without double escaping.
 *
 * @param  string  $value
 * @return string
 */
function e($value)
{
    return htmlentities($value, ENT_QUOTES, 'UTF-8', false);
}


// =============================================================================
// Asset helpers
// =============================================================================

function asset($url, $revision = null, $useCdn = true)
{
    // Make use of global revision, if none provided
    if ($revision !== false && $revision === null) {
        $revision = $_ENV['APP_REVISION'];
    }

    // If we have a revision, prefix with '?'
    if ($revision !== false) {
        $revision = "?{$revision}";
    }

    $url = $_ENV['ASSETS_URI'].'/'.ltrim($url, '/');

    // If a CDN is provided, we'll use it, unless specifically instructed
    // not to do so.
    if ($useCdn !== false && !empty($_ENV['CDN_URL']) && $_ENV['APP_ENV'] === 'production') {
        $url = $_ENV['CDN_URL'].'/'.ltrim($url, '/');
    }

    return $url.$revision;
}

function app_asset($path, $revision = null)
{
    return asset('/app/'.ltrim($path, '/'), $revision);
}

function compiled_asset($file, $revision = null)
{
    static $manifestLoaded = false;
    static $manifest = array();

    if ($_ENV['APP_ENV'] === 'development') {
        return asset('/dev/'.$file, false);
    }

    // Lazy-load the compiled asset manifest
    if (!$manifestLoaded) {
        $manFile = "{$_ENV['PUBLIC_PATH']}/{$_ENV['ASSETS_URI']}/build/rev-manifest.json";

        if (file_exists($manFile)) {
            $manifest = json_decode((string) file_get_contents($manFile), true) ?: array();
        }

        $manifestLoaded = true;
    }

    if (isset($manifest[$file])) {
        $file = '/build/'.ltrim($manifest[$file], '/');
        $revision = false;
    } else {
        $fileParts = pathinfo($file) + [
            'dirname' => '/',
            'extension' => '',
        ];

        $file = "{$fileParts['dirname']}/{$fileParts['filename']}.min.{$fileParts['extension']}";
        $file = '/build/'.ltrim(str_replace('//', '/', $file), '/.');
    }

    return asset($file, $revision);
}

function vendor_asset($path, $revision = null)
{
    return asset('/vendor/'.ltrim($path, '/'), $revision);
}

function local_asset($path, $revision = null)
{
    return asset($path, $revision, false);
}

function app_icon($icon, $embed = true)
{
    static $cache = [];

    if ($embed) {
        if (isset($cache[$icon])) {
            return $cache[$icon];
        }

        $markup = file_get_contents($_ENV['PUBLIC_PATH'].asset("img/icons/{$icon}.svg", false, false));

        $cache[$icon] = $markup;

        return $markup;
    } else {
        return app_asset("img/icons/{$icon}.svg");
    }
}

// =============================================================================
// Debug helpers
// =============================================================================

function vd()
{
    call_user_func_array('var_dump', func_get_args());
}

function vdd()
{
    call_user_func_array('var_dump', func_get_args());
    die;
}

function debug()
{
    echo '<pre style="border: 2px solid red; background: #fff; padding: 1em;">';
    call_user_func_array('var_dump', func_get_args());
    echo '</pre>';
}

function alert_webmaster($subject, $message)
{
    mail($_ENV['EMAIL_ERROR'], $subject, $message, join("\n", array(
        "From: {$_ENV['EMAIL_ERROR']}",
    )));
}

// =============================================================================
// Misc helpers
// =============================================================================

/**
 * Gets the value of an environment variable. Supports boolean, empty and null.
 *
 * Original Source: https://github.com/laravel/framework/blob/5.2/src/Illuminate/Foundation/helpers.php
 * Edited to remove Str class usage.
 *
 * @param  string  $key
 * @param  mixed   $default
 * @return mixed
 */
function env($key, $default = null)
{
    $value = getenv($key);

    if ($value === false) {
        return value($default);
    }

    switch (strtolower($value)) {
        case 'true':
        case '(true)':
            return true;
        case 'false':
        case '(false)':
            return false;
        case 'empty':
        case '(empty)':
            return '';
        case 'null':
        case '(null)':
            return;
    }

    if (strlen($value) > 1 && substr($value, 0, 1) === '"' && substr($value, -1) === '"') {
        return substr($value, 1, -1);
    }

    return $value;
}

/**
 * Returner returner
 *
 * This function's purpose is to return a closure, which returns a value... Sounds useless, but
 * PHP lacks the ability to interpolate functions, static methods, constants, etc, within string
 * interpolation delimiters, which this function can help get around
 *
 * Since a closure can be assigned to a variable, you can use it within string interpolation. This
 * is especially helpful with heredocs.
 *
 * Contrived Example:
 *
 * $r = returner();
 * echo "The current PHP version is {$r(PHP_VERSION)}, which is amazing"
 *
 * @return Closure The anonymous function which returns a value. If the value is a Closure, it will execute it.
 */
function returner()
{
    static $returner;

    if (!$returner) {
        $returner = function($value) {
            if ($value instanceof Closure) {
                return $value();
            }

            return $value;
        };
    }

    return $returner;
}
