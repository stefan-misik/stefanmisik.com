<?php

interface IPostSource
{
    /**
     * @brief Query post source with given parameters
     *
     * @c $param can have following fields:
     * * @c slug - string specifying the post slug
     * * @c tag - Load all posts with given tag
     * * @c hidden - Specify whether hidden posts are also loaded
     * * @c sortfrom - Ordering:
     *   * @c oldest
     *   * @c newest
     * * @c updatedbefore - Get only post updated (published) before given time
     * * @c metaonly - load only metadata
     * * @c limit - maximum number of post records to obtain
     *
     * @param array $param Query parameters
     * @return bool @c TRUE when at least one post matched the query
     */
    public function querySource(array $param): bool;

    /**
     * @brief Get next result of the query
     *
     * Use this method to obtain the next item from the query initiated by
     * calling @ref querySource().
     *
     * @return ?array Associative array containing the post data or NULL
     */
    public function getNextPost(): ?array;

    /**
     * @brief Get the count of the query results
     * 
     * @return int Number of posts returned by the query
     */
    public function countResults(): int;
}

require_once(dirname(__FILE__) . "/post_source/file.php");

/**
 * @brief Get an instance of a post source
 *
 * @return IPostSource Post source instance
 */
function GetPostSource(): IPostSource
{
    return new FilePostSource(POST_HOME);
}
