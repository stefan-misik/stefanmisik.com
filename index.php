<?php

/* Load the common definitions */
require 'private/common.php';


/*
 * Show home-page
 */
$pages_config[
    "/^\/?$/"
] = static function()
{
    require "private/homepage.php";

    try
    {
        $homepage = new Homepage();

        header("Content-Type: text/html");
        loadView("homepage", $homepage);

        return TRUE;
    }
    catch(Exception $ex)
    {}

    return FALSE;
};

/*
 * RSS Feed
 */
$pages_config[
    "/^\/rss.xml$/"
] = static function()
{
    require "private/post_source.php";
    require "private/post.php";

    /* Create the list of posts */
    $post_source = GetPostSource();
    $post_source->querySource(
        array(
            "hidden" => FALSE, 
            "sortfrom" => "newest",
            "publishedbefore" => time(),
            "metaonly" => TRUE
        )
    );

    header("Content-Type: application/rss+xml");
    loadView("rss", $post_source);

    return TRUE;
};

/*
 * Robots.txt
 */
$pages_config[
    "/^\/robots.txt$/"
] = static function()
{
    header("Content-Type: text/plain");
?>
# robots.txt for <?php echo(getUrlAddress("") . "\n"); ?>

User-agent: *
Crawl-delay: 10
<?php
    return TRUE;
};

/*
 * Print the current UTC time
 */
$pages_config[
    "/^\/time$/"
] = static function()
{
    header("Content-Type: text/plain");
    echo gmdate(DATE_ISO8601);
    return TRUE;
};

/*
 * Show a post
 */
$pages_config[
    "/^\/post\/(" . SLUG_PATTERN . ")(?:\/|\.html)?$/"
] = static function($post_slug)
{
    require "private/post_source.php";
    require "private/post.php";

    $post_source = GetPostSource();
    /* Try to ge the post */
    if ($post_source->querySource(array(
        "slug" => $post_slug,
        "hidden" => TRUE
    )))
	{
        $post = new Post($post_source->getNextPost());

        header("Content-Type: text/html");
        loadView("post", $post);

        return TRUE;
	}

    return FALSE;
};

/*
 * Get the raw post
 */
$pages_config[
    "/^\/post\/(" . SLUG_PATTERN . ")\." . POST_EXT . "$/"
] = static function($post_slug)
{
    require "private/post_source.php";

    $post_source = GetPostSource();
    /* Try to ge the post */
    if ($post_source->querySource(array(
        "slug" => $post_slug,
        "hidden" => TRUE,
        "metaonly" => TRUE
    )))
	{
        header("Content-Type: text/plain");
        readfile($post_source->getPostFilename(
            $post_source->getNextPost()["slug"]));
        return TRUE;
    }

    return FALSE;
};

/*
 * Show post's media
 */
$pages_config[
    "/^\/post\/(" . SLUG_PATTERN . ")\/media\/([^\/]+)$/"
] = static function($post, $media_file)
{
    $media_file = MEDIA_HOME . $media_file;
    if (!file_exists($media_file))
    {
        return FALSE;
    }

    header("Content-Type: " . mime_content_type($media_file));
    readfile($media_file);
    return TRUE;
};

/*
 * List tag
 */
$pages_config[
    "/^\/tag\/(" . SLUG_PATTERN . ")\/?$/"
] = static function($tag_slug)
{
    require "private/post_source.php";
    require "private/post.php";

    /* Create the list of posts */
    $post_source = GetPostSource();
    $post_source->querySource(
        array(
            "hidden" => FALSE, 
            "sortfrom" => "newest",
            "publishedbefore" => time(),
            "metaonly" => TRUE,
            "tag" => $tag_slug
        )
    );

    header("Content-Type: text/html");
    loadView("tag", array(
        "tag" => $tag_slug,
        "source" => $post_source
        )
    );

    return TRUE;
};

/*
 * List all posts
 */
$pages_config[
    "/^\/archive\/?$/"
] = static function()
{
    require "private/post_source.php";
    require "private/post.php";

    /* Create the list of posts */
    $post_source = GetPostSource();
    $post_source->querySource(
        array(
            "hidden" => FALSE, 
            "sortfrom" => "newest",
            "publishedbefore" => time(),
            "metaonly" => TRUE
        )
    );

    header("Content-Type: text/html");
    loadView("archive", $post_source);

    return TRUE;
};

/*
 * Get the captcha hint image
 */
$pages_config[
    "/^\/captcha\/(" . SLUG_PATTERN . ")\/([01]).png$/"
] = static function($captcha_name, $image_id)
{
    require 'private/tools/captcha.php';

    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Content-Type: image/png");

    $captcha_obj = new Captcha($captcha_name);
    $captcha_obj->outputHintImage($image_id);

    return TRUE;
};

/*
 * Ignore access to .git repository
 * This is a hotfix.
 */
$pages_config[
    "/^\/\.git\/?/"
] = static function()
{
    return TRUE;
};

/*
 * Try to look for universal link
 */
$pages_config[
    "/^\/(" . LINK_PATTERN . ")$/"
] = static function($link)
{
    require 'private/link.php';

    return Link::linkHandle($link);
};

/*============================================================================*/


if(!dispatchPage($pages_config))
{
    header("HTTP/1.0 404 Not Found");
    header("Content-Type: text/html");
    loadView("404", NULL);
}
