<?php

require_once dirname(__FILE__) . "/formatter.php";

/**
 * @brief class describing a single post
 */
class Post
{
    /**
     * @brief Formatted post
     *
     * @var string
     */
    private $html;

    /**
     * @brief Raw post record
     *
     * @var array
     */
    private $record;

    /*========================================================================
                                 Private Methods
      ========================================================================*/

    /*========================================================================
                                 Public Methods
      ========================================================================*/

    /**
     * @brief Post constructor
     *
     * @param $record Raw post record
     */
    public function __construct(
            array $post_record
            )
    {
        $this->record = $post_record;
    }

    /**
     * @brief Get the post's title
     *
     * @return string Title
     */
    public function getTitle(
            )
    {
        return $this->record["title"];
    }

    /**
     * @brief Get the post's publish timestamp
     *
     * @return int Publish timestamp
     */
    public function getPublishTime(
            )
    {
        return $this->record["published"];
    }

    /**
     * @brief Get the update time of last change or publish time
     *
     * @return int Update timestamp
     */
    public function getUpdateTime(
            )
    {
        return $this->record["updated"];
    }

    /**
     * @brief Determine whether the post was updated
     *
     * @return bool TRUE if was updated, FALSE otherwise
     */
    public function isUpdated(
            )
    {
        return $this->record["updated"] != $this->record["published"];
    }

    /**
     * @brief Get the post's excerpt (summary)
     *
     * @return string Post excerpt
     */
    public function getExcerpt(
            )
    {
        return strip_tags($this->record["excerpt"]);
    }

    /**
     * @brief Get the HTML representation of the post
     *
     * @return string Parsed HTML of the post
     *
     * @throws Exception In case the post file can not be opened
     */
    public function getHtml(
            )
    {
        /* If there is no html yet, read and parse the markdown */
        if(!isset($this->html))
        {
            $formatter = new Formatter();
            /* Configure formatter */
            $formatter->setRelativeUrlPrefix(getUrlAddress(
                    "post/" . $this->getSlug() . "/"));
            $formatter->setAbsoluteUrlPrefix(getUrlAddress(""));
            $this->html = $formatter->text($this->record["excerpt"] .
                    "\n\n" . $this->record["content"]);
        }

        return $this->html;
    }

    /**
     * @brief Get the post's tags
     *
     * @return array An associative array of string tags keyed by tag slug
     */
    public function getTags(
            )
    {
        return $this->record["tags"];
    }

    /**
     * @brief Get the hidden status of the post
     *
     * @return bool Is the post hidden
     */
    public function isHidden(
            )
    {
        return $this->record["hidden"];
    }

    /**
     * @brief Get the slug string of the post
     *
     * @return string Slug
     */
    public function getSlug(
            )
    {
        return $this->record["slug"];
    }

    /**
     * @brief Get link to the post
     *
     * @return string URL link
     */
    public function getLink(
            )
    {
        return getUrlAddress("post/" . $this->getSlug());
    }

    /**
     * @brief Get link to the post's source code
     *
     * @return string URL link
     */
    public function getSrcLink(
            )
    {
        return getUrlAddress("post/" . $this->getSlug() . "." . POST_EXT);
    }
}
