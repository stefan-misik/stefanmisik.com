<?php

/* Load the configuration */
require_once dirname(__FILE__) . '/config.php';

/**
 * @brief Get the requested page
 * 
 * @return string Page address
 */
function requestedPage(
        )
{
    return parse_url(filter_input(INPUT_SERVER, "REQUEST_URI"), PHP_URL_PATH);
}

/**
 * @brief Format time
 *
 * @param int $timestamp Optional timestamp to convert to actual time
 *
 * @return string Formatted time
 */
function formatTime(
        int $timestamp = NULL
        )
{
    if(NULL == $timestamp)
    {
        $timestamp = time();
    }

    return date(DATETIME_FORMAT, $timestamp);
}

/**
 * @brief Format short string representation of a post time
 *
 * @param int $timestamp Timestamp to format
 *
 * @return string Formatted time
 */
function formatShortTime(
        int $timestamp
        )
{
    return date(DATETIME_SHORT_FORMAT, $timestamp);
}

/**
 * @brief Format archive group string representation of a post time
 *
 * Posts with the same time representation will be grouped in archive.
 *
 * @param int $timestamp Timestamp to format
 *
 * @return string Formatted time
 */
function formatArchiveGroupTime(
        int $timestamp
        )
{
    return date(DATETIME_ARCHIVE_GROUP, $timestamp);
}

/**
 * @brief Use correctly plural form of a noun
 * 
 * @param int $cnt The count of the specified noun
 * @param string $noun A noun to be formatted
 * @return string Formatted noun
 */
function usePlural(
        $cnt,
        $noun
        )
{
    return $cnt == 1 ? $noun : $noun . "s";
}

/**
 * @brief Transform a string into a slug string
 * 
 * @param string $text Text to transform
 * 
 * @return string The slug string representing the passed text
 */
function toSlug(
       string $text
       )
{
    // replace non letter or digits by -
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);

    // transliterate
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

    // remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);

    // trim
    $text = trim($text, '-');

    // remove duplicate -
    $text = preg_replace('~-+~', '-', $text);

    // lowercase
    $text = strtolower($text);

    if (empty($text)) {
        return 'n-a';
    }

    return $text;
}

/**
 * @brief Process page request according the configured page handlers
 * 
 * @param array $pages Array of regexes and page handlers
 * 
 * @return boolean Was page found
 */
function dispatchPage(
        array $pages
        )
{   
    /* Get the page */
    $page = requestedPage();
    $matches = array();
    
    foreach($pages as $regex => $handler)
    {
        if(preg_match($regex, $page, $matches))
        {
            array_shift($matches);
            
            /* Try to handle the page */
            if(call_user_func_array($handler, $matches))
            {
                return TRUE;
            }
        }
    }
    
    return FALSE;
}

/**
 * @brief Load a view
 * 
 * @param string $view_name The name of a view
 * @param mixed $logic Logic object accessible to the view
 */
function loadView(
        string $view_name,
        $logic
        )
{
    require_once VIEW_HOME . "_common.php";
    require VIEW_HOME . "$view_name.php";
}

/**
 * @brief Get the common name of the web site
 * 
 * @return string The common title of the web site
 */
function getPageTitle(
        )
{
    return PAGE_TITLE;
}

/**
 * @brief Make URL address form file path
 *
 * @param string $file The file path
 *
 * @return string The URL to specified file
 */
function getUrlAddress(
		string $file
		)
{
    if (0 == strlen($file))
    {
        return PAGE_ADDR;
    }
    else
    {
        return PAGE_ADDR . "/" . $file;
    }
}

/**
 * @brief Start session if necessary
 * 
 * Starts session, can be called multiple times. Session is started only 
 * once.
 */
function ensureSession(
        )
{
    if("" === session_id())
    {
        /* Start session */
        session_start();
    }
}

/**
 * @brief Verify whether remote browser is a text-mode browser
 * 
 * @return bool 'TRUE' if browser is a texmode browser
 */
function isTextBased(
        )
{
    $browser = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT',
        FILTER_UNSAFE_RAW);

    return (FALSE !== strpos($browser, "Lynx")) ||
        (FALSE !== strpos($browser, "w3m")) ||
        (FALSE !== strpos($browser, "Links")) ||
        (FALSE !== strpos($browser, "textmode"));
}
