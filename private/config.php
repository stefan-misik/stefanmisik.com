<?php

/* The page title */
define("PAGE_TITLE", "Stefan Misik");
/* Allow to have PAGE_ADDR defined by router.php in off-line */
if (!defined("PAGE_ADDR"))
{
    /* The root address of the page */
    define("PAGE_ADDR", "https://www.stefanmisik.com");
}
/* Directory containing all the views */
define("VIEW_HOME", "views/");

/* The markdown file of the homepage */
define("HOME_PAGE", "home.md");

/* Slug regex pattern */
define("SLUG_PATTERN", "[a-z0-9][a-z0-9\-]*");
/* Link regex pattern */
define("LINK_PATTERN", "[a-z0-9][a-z0-9\-\.]*");

/* Define the post home directory */
define("POST_HOME", "posts/");
/* Post file extension */
define("POST_EXT", "md");
/* Maximum length of raw post body */
define("POST_MAX_SIZE", 512 * 1024);
/* Define the media home directory */
define("MEDIA_HOME", POST_HOME . "media/");

/* Location of arbitrary files */
define("DATA_HOME", "data/");

/* Default time format */
define("DATETIME_FORMAT", "M jS, Y H:i:s");
/* Short Date-time format */
define("DATETIME_SHORT_FORMAT", "F j, Y");
/* Archive group time format */
define("DATETIME_ARCHIVE_GROUP", "F Y");
