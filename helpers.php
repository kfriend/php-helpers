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

    if (!is_int($offset)) return $array;

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

    foreach ($selected as $index)
    {
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
    for ($i = 0; $i < $num; $i++)
    {
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
    if (!$array)
    {
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

    for ($i = 0; $i < $count; $i++)
    {
        if (!array_key_exists($key, $array[$i]))
        {
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
 * each sub array, and should return the value which the sub array should be index
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

    for ($i = 0; $i < $count; $i++)
    {
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

    for ($i = 0; $i < $count; $i++)
    {
        if (!array_key_exists($key, $array[$i]))
        {
            continue;
        }

        // Does the group exist already?
        if (!isset($index[$key]))
        {
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

    for ($i = 0; $i < $count; $i++)
    {
        $key = $callback($array[$i]);

        // Does the group exist already?
        if (!isset($index[$key]))
        {
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

    foreach ($lookup as $from => $to)
    {
        if (array_key_exists($from, $data))
        {
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

    foreach ($record as $key => $value)
    {
        if (is_array($value) or is_object($value))
        {
            $results = array_merge($results, flatten((array) $value, $prepend.$key.$delimiter));
        }
        else
        {
            $results[$prepend.$key] = $value;
        }
    }

    return $results;
}

// =============================================================================
// String parsing and formatting helpers
// =============================================================================

/**
 * Parse currency
 *
 * Strips unnecessary values from a string presentation of a currency value, turning
 * it into a floating point value. Useful for parsing user-supplied currency values.
 *
 * @param  string $value The currency value to parse.
 * @return float         The parsed currency value.
 */
function parse_currency($value)
{
    // Strip out everything but digits and the final decimal
    $value = preg_replace('/([^\d\.]|\.(?=.*\.))/', '', $value);

    // Strip any extra 'left' zeros
    // This prevent the number from being interpreted as hex, octal, or binary numbers
    $value = ltrim($value, 0);

    return ($value) ? number_format($value, 2, '.', '') : 0;
}

/**
 * Format currency
 *
 * Formats a number into a US currency string.
 *
 * Note: This function make no attempt at internationalization.
 *
 * @param  mixed $value     The value to format.
 * @return [string]           The formatted currency.
 */
function format_currency($value)
{
    // Note: there is a money_format() function. I like this brute-force method better.
    return '$' . number_format(parse_number($value), 2, '.', ',');
}

/**
 * Parse number
 *
 * Parsed a number of any unnecessary characters. Useful for parsing user supplied
 * number of things like commas, extra decimals, etc.
 *
 * @param  [string] $value The number to parse.
 * @return mixed        The parsed number.
 */
function parse_number($value)
{
    // Strip out everything but digits and the final decimal
    $value = preg_replace('/([^\d\.]|\.(?=.*\.))/', '', $value);

    // Strip any extra 'left' zeros
    // This prevent the number from being interpreted as hex, octal, or binary numbers
    $value = ltrim($value, 0);

    return ($value) ? $value : 0;
}

/**
 * Format phone number
 *
 * Takes a provided integer and converts in into a US-style phone number, e.g.
 * (123) 456-7890.
 *
 * Note: This function makes no attempt at internationalization, and still needs
 * work in respect to handling irregular $number lengths.
 *
 * @param  string|int $number   The number to format
 * @return string               The formatted number
 */
function format_phone_number($number)
{
    $number = preg_replace('/[^\d x]/', '', $number);

    $length = strlen($number);

    if ($length === 10)
    {
        $area = substr($number, 0, 3);
        $prefix = substr($number, 3, 3);
        $line = substr($number, 6);
        return "({$area}) {$prefix}-{$line}";
    }

    // TODO
    // Fix this so the "country" segment can be any length
    elseif ($length === 11)
    {
        $country = substr($number, 0, 1);
        $area = substr($number, 1, 3);
        $prefix = substr($number, 4, 3);
        $line = substr($number, 7);
        return "+{$country} ({$area}) {$prefix}-{$line}";
    }
    // Just return the unformatted number
    return $number;
}

/**
 * Get first image
 *
 * Extracts out the first image's source within a string.
 *
 * Adapted from: http://css-tricks.com/snippets/wordpress/get-the-first-image-from-a-post/
 */
// function get_first_image($string)
// {
//     if (!$string) return null;

//     preg_match('/<img\s+([^>]*\s+)?src=([\'"])((?:(?!\2).)*)\2(\s+[^>]*)>/i', $string, $matches);
//     return !empty($matches[3]) ? $matches[3] : null;
// }

/**
 * Strip line endings
 *
 * Removes line ending from a string, turning it into a "one-liner".
 *
 * @param  string $value
 * @return string
 */
function strip_line_endings($value)
{
    return str_replace(array("\n", "\r"), '', $value);
}

/**
 * Normalize line endings
 *
 * Converts line endings to a single type.
 *
 * @param  string $value    The string to normalize.
 * @param  string $to       The line ending type to convert to.
 * @return string           The normalized string.
 */
function normalize_line_endings($value, $to = "\n")
{
    $opposite = ($to === "\n")
        ? "\r\n"
        : "\n";

    return str_replace($opposite, $to, $value);
}

/**
 * Last Segment
 *
 * Returns the last segment from a string, where the segments are delimited by
 * $delim
 *
 * last_segment('foo.bar.baz', '.') => 'baz'
 *
 * @param string $string The string to find that last segment from
 * @param string $delim The segment delimiter
 * @return string
 */
function last_segment($string, $delim = '/')
{
    $lastPos = strrpos(trim($string, $delim), $delim);

    if ($lastPos === false) {
        return $string;
    }

    return substr($string, $lastPos + 1);
};

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

/**
 * Escape
 *
 * Alias for the e() helper. Not sure why it exists?
 *
 */
function escape($string)
{
    return e($string);
}

/**
 * Escape and echo
 *
 * Convenient form of `echo e($value)`.
 *
 * @param  string $string   The value to escape.
 * @return string
 */
function ee($string)
{
    echo e($string);
}

/**
 * Strip slashes, escape, and echo
 *
 * Strips flashes from a value, then escape it, and echoes the value out. This
 * function is particularly handy with Wordpress, as it still relies on magic
 * quotes, and possibly always will.
 *
 * @param  string $string   The value to strip, escape, and echo out.
 * @return string
 */
function strip_ee($string)
{
    echo stripslashes(e($string));
}

function hyphenize($string)
{
    return str_replace(array(' ', '_'), '-', strtolower(trim($string)));
}

// =============================================================================
// Laravel 4 string helpers
// =============================================================================

// function studly_case($value)
// {
//     $value = ucwords(str_replace(array('-', '_'), ' ', $value));

//     return str_replace(' ', '', $value);
// }

// function camel_case($value)
// {
//     return lcfirst(studly_case($value));
// }

// function snake_case($value, $delimiter = '_')
// {
//     $replace = '$1'.$delimiter.'$2';

//     return ctype_lower($value) ? $value : strtolower(preg_replace('/(.)([A-Z])/', $replace, $value));
// }

// =============================================================================
// Request, query string, etc helpers
// =============================================================================

/**
 * Query string without
 *
 * Returns the a query string $query without the requests keys. Useful for things
 * like generating pagination URLs.
 *
 * @param string  $query    The query string in question.
 * @param  mixed  ...       Either a single array of keys to exclude, or variadic list of keys to exclude
 * @return string           A query string without the requested keys.
 */
// function query_without($query, $unset)
// {
//     // Decode, just in case something like EE, which passes some things encoded
//     $query = html_entity_decode($query);

//     if (!is_array($unset))
//     {
//         $unset = array_slice(func_get_args(), 1);
//     }

//     // If a 'foo=bar&bar=baz' format string was passed, prefix with '?' so parse_url() will correctly
//     // recognize as a query string, instead of a path
//     if (!preg_match('|^https?:\/\/|', $query)) $query = '?'.trim($query, '?');

//     // Parse the URL parts
//     $parts = parse_url($query);

//     // If no query provided, just return original value
//     if (!isset($parts['query'])) return $query;

//     // Trim excess amps
//     $parts['query'] = trim($parts['query'], '&');

//     // Convert query string to array
//     parse_str($parts['query'], $parsed);

//     foreach ($unset as $key)
//     {
//         unset($parsed[$key]);
//     }

//     return trim(preg_replace('/\?.*/', '?'.http_build_query($parsed), $query), '?');
// }

/**
 * Current query string without
 *
 * Returns the current query string (via $_GET) without the requests keys. Useful for things
 * like generating pagination URLs.
 *
 * @param  mixed  ...  Either a single array of keys to exclude, or variadic list of keys to exclude
 * @return string      A query string without the requested keys.
 */
// function current_query_without($unset)
// {
//     if (!is_array($unset))
//     {
//         $unset = func_get_args();
//     }

//     $query = call_user_func_array('query_without', array_merge(array($_SERVER['QUERY_STRING']), func_get_args()));

//     return trim($query, '?');
// }

/**
 * HTTP-ify
 *
 * Takes a string and prefixes with protocol. Useful for making sure use input is
 * a correct URL.
 *
 * @param  string $url  The URL in question
 * @param  string $type The type of protocol. See switch below for options.
 * @return string
 */
function httpify($url, $type = 'http')
{
    // Already a valid URL?
    if (filter_var((string) $url, FILTER_VALIDATE_URL))
    {
        // Yup, so just return it
        return $url;
    }

    switch ($type)
    {
        case 'https':
        case 'secure':
            $type = 'https://';
            break;
        case '//':
        case 'relative':
            $type = '//';
            break;

        case 'http':
        default:
            $type = 'http://';
            break;
    }

    return $type.$url;
}

/**
 * De-HTTP-ify
 *
 * Takes a URL and strips off "https?://" or "//" from the start.
 *
 * @param  string $url
 * @return string
 */
function dehttpify($url)
{
    return preg_replace('/^(https?:\/\/|\/\/)/i', '', $url);
}

/*
================================================================================
Asset helpers
================================================================================
*/

function asset_url($url, $revision = null, $useCdn = true)
{
    // Make use of global revision, if none provided
    if ($revision !== false && $revision === null)
    {
        $revision = $_ENV['APP_REVISION'];
    }

    // If we have a revision, prefix with '?'
    if ($revision !== false)
    {
        $revision = "?{$revision}";
    }

    $url = $_ENV['ASSET_URI'].'/'.ltrim($url, '/');

    // If a CDN is provided, we'll use it, unless specifically instructed
    // not to do so.
    if ($useCdn !== false && !empty($_ENV['CDN_URL']))
    {
        $url = $_ENV['CDN_URL'].'/'.ltrim($url, '/');
    }

    return $url.$revision;
}

function app_asset($path, $revision = null)
{
    return asset_url('/app/'.ltrim($path, '/'), $revision);
}

function compiled_asset($file, $revision = null)
{
    static $manifestLoaded = false;
    static $manifest = array();

    // Lazy-load the compiled asset manifest
    if (!$manifestLoaded)
    {
        $manFile = $_ENV['PUBLIC_PATH'].'/assets/build/rev-manifest.json';

        if (file_exists($manFile))
        {
            $manifest = json_decode((string) file_get_contents($manFile), true) ?: array();
        }

        $manifestLoaded = true;
    }

    if (isset($manifest[$file]))
    {
        $file = '/build/'.ltrim($manifest[$file], '/');
        $revision = false;
    }
    else
    {
        $file = '/compiled/'.ltrim($file, '/');
    }

    return asset_url($file, $revision);
}

function vendor_asset($path, $revision = null)
{
    return asset_url('/vendor/'.ltrim($path, '/'), $revision);
}

function local_asset($path, $revision = null)
{
    return asset_url($path, $revision, false);
}

/*
================================================================================
Error/Issue helpers
================================================================================
*/

function alert_webmaster($subject, $message)
{
    mail($_ENV['EMAIL_ERROR'], $subject, $message, join("\n", array(
        "From: {$_ENV['EMAIL_ERROR']}",
    )));
}
