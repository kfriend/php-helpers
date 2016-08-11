<?php

// =============================================================================
// Array helpers
// =============================================================================

/**
 * Takes an array and converts it to a CSV string
 *
 * Adapted from http://stackoverflow.com/a/16353448
 *
 * @param  array $array     The array to convert
 * @param  string $delimiter the CSV column delimiter
 * @param  string $enclosure String to use for enclosing column values
 * @return string            The CSV string
 */
function array_to_csv(array $array, $delimiter = ',', $enclosure = '"')
{
    $pointer = fopen('php://temp', 'r+');
    fputcsv($pointer, $array, $delimiter, $enclosure);
    rewind($pointer);
    $csv = fread($pointer, 1048576);
    fclose($pointer);

    return rtrim($csv);
}

// =============================================================================
// Strings helpers
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
function str_parse_currency($value)
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
 * @return string           The formatted currency.
 */
function str_format_currency($value)
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
 * @param  string $value The number to parse.
 * @return mixed        The parsed number.
 */
function str_parse_number($value)
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
function str_format_phone($number)
{
    $number = preg_replace('/[^\d x]/', '', $number);

    $length = strlen($number);

    if ($length === 10) {
        $area = substr($number, 0, 3);
        $prefix = substr($number, 3, 3);
        $line = substr($number, 6);
        return "({$area}) {$prefix}-{$line}";
    }

    // @todo
    // Fix this so the "country" segment can be any length
    elseif ($length === 11) {
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
 * HTTP-ify
 *
 * Takes a string and prefixes with protocol. Useful for making sure use input is
 * a correct URL.
 *
 * @param  string $url  The URL in question
 * @param  string $type The type of protocol. See switch below for options.
 * @return string
 */
function str_httpify($url, $type = 'http')
{
    // Already a valid URL?
    if (filter_var((string) $url, FILTER_VALIDATE_URL)) {
        // Yup, so just return it
        return $url;
    }

    switch ($type) {
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
function str_dehttpify($url)
{
    return preg_replace('/^(https?:\/\/|\/\/)/i', '', $url);
}

/**
 * Convert string to studly case.
 *
 * Original source: Laravel 4, adapted to remove Str dependency.
 * 
 * @param  string $value 
 * @return string
 */
function str_studly_case($value)
{
    $value = ucwords(str_replace(array('-', '_'), ' ', $value));

    return str_replace(' ', '', $value);
}

/**
 * Convert string to camel case.
 *
 * Original source: Laravel 4, adapted to remove Str dependency.
 * 
 * @param  string $value 
 * @return string
 */
function str_camel_case($value)
{
    return lcfirst(str_studly_case($value));
}

/**
 * Convert string to snake case.
 *
 * Original source: Laravel 4, adapted to remove Str dependency.
 * 
 * @param  string $value 
 * @return string
 */
function str_snake_case($value, $delimiter = '_')
{
    $replace = '$1'.$delimiter.'$2';

    return ctype_lower($value) ? $value : strtolower(preg_replace('/(.)([A-Z])/', $replace, $value));
}

/**
 * Convert string to kebab case.
 * 
 * @param  string $value 
 * @return string
 */
function str_kebab_case($value)
{
    return str_snake_case($value, '-');
}

/**
 * Strip line endings
 *
 * Removes line ending from a string, turning it into a "one-liner".
 *
 * @param  string $value
 * @return string
 */
function str_strip_endings($value)
{
    static $endings = array("\n", "\r");
    return str_replace($endings, '', $value);
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
function str_normalize_endings($value, $to = "\n")
{
    $opposite = ($to === "\n")
        ? "\r\n"
        : "\n";

    return str_replace($opposite, $to, $value);
}

/**
 * De-Widow
 *
 * Prevents widows by combining the last $minWords in a string with &nbsp;
 *
 * Original from http://davidwalsh.name/word-wrap-mootools-php. Changed for name and format
 *
 * @param  string  $text     The text to de-widow
 * @param  integer $minWords Number of words to accompany a widow
 * @return string            De-widowed text
 */
function str_dewidow($text, $minWords = 3)
{
    $words = explode(' ', $text);
    $wordCount = count($words);

    if ($wordCount >= $minWords) {
        $words[$wordCount - 2] .= '&nbsp;' . $words[$wordCount - 1];
        array_pop($words);
        $text = implode(' ', $words);
    }

    return $text;
}

/**
 * Get first image
 *
 * Extracts out the first image's source within a string.
 *
 * Adapted from: http://css-tricks.com/snippets/wordpress/get-the-first-image-from-a-post/
 */
function str_first_img($string)
{
    if (!$string) {
        return null;
    }

    preg_match('/<img\s+([^>]*\s+)?src=([\'"])((?:(?!\2).)*)\2(\s+[^>]*)>/i', $string, $matches);
    return !empty($matches[3]) ? $matches[3] : null;
}

/**
 * Get first image attributes
 *
 * Extracts out the first image's attributes within a string.
 */
function str_first_img_attrs($string) {
    if (!$string) {
        return null;
    }

    preg_match('/<img\s+([^>]*)>/i', $string, $matches);

    if (empty($matches[1]) or trim($matches[1]) === '') {
        return null;
    }

    // Libxml throws Exceptions AND spits out errors... need to handle both situations

    // Current setting
    $errorSetting = libxml_use_internal_errors();

    // Disable errors
    libxml_use_internal_errors(true);

    try {
        // Remove self-closing marker
        $attrs = preg_replace('#\s*\/\s*$#', '', $matches[1]);

        // Parse the attributes. We need to manually construct the <img> tag as self-closing,
        // or SimpleXMLElement will fail to parse it.
        $xml = new SimpleXMLElement("<img {$attrs} />");

        // Reset error setting
        libxml_use_internal_errors($errorSetting);

        if (libxml_get_errors())
        {
            return null;
        }

        // Need to use current() because SimpleXMlElement is horribly designed in this regard:
        // http://stackoverflow.com/questions/11439829/simplexml-attributes-to-array#answer-13677624
        return current($xml->attributes());
    } catch (Exception $exception) {
        // Reset error setting
        libxml_use_internal_errors($errorSetting);

        return null;
    }
}

// =============================================================================
// Request, query string, etc helpers
// =============================================================================

/**
 * Query string without
 *
 * Returns a query string $query without the requested keys. Useful for things
 * like generating pagination URLs.
 *
 * @param string  $query    The query string in question.
 * @param  mixed  ...       Either a single array of keys to exclude, or variadic list of keys to exclude
 * @return string           A query string without the requested keys.
 */
function req_query_without($query, $unset)
{
    // Decode, just in case something like ExpressionEngine, which passes some things encoded
    $query = html_entity_decode($query);

    if (is_array($unset) === false) {
        $unset = array_slice(func_get_args(), 1);
    }

    // If a 'foo=bar&bar=baz' format string was passed, prefix with '?' so parse_url() will correctly
    // recognize as a query string, instead of a path
    if (!preg_match('|^https?:\/\/|', $query)) {
        $query = '?'.trim($query, '?');
    }

    // Parse the URL parts
    $parts = parse_url($query);

    // If no query provided, just return original value
    if (!isset($parts['query'])) {
        return $query;
    }

    // Trim excess amps
    $parts['query'] = trim($parts['query'], '&');

    // Convert query string to array
    parse_str($parts['query'], $parsed);

    foreach ($unset as $key) {
        unset($parsed[$key]);
    }

    return trim(preg_replace('/\?.*/', '?'.http_build_query($parsed), $query), '?');
}

/**
 * Current query string without
 *
 * Returns the current query string (via $_GET) without the requests keys. Useful for things
 * like generating pagination URLs.
 *
 * @param  mixed  ...  Either a single array of keys to exclude, or variadic list of keys to exclude
 * @return string      A query string without the requested keys.
 */
function req_current_query_without($unset)
{
    if (is_array($unset) === false) {
        $unset = func_get_args();
    }

    $query = call_user_func_array('req_query_without', array_merge(array($_SERVER['QUERY_STRING']), $unset));

    return trim($query, '?');
}
