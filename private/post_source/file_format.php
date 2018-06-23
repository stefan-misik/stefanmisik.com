<?php

class FileFormat
{
    const META_LINE_MAX_LENGTH = 4096;
    const EXCERPT_LINE_MAX_LENGTH = 4096;
    const EXCERPT_MAX_LENGTH = 4096;

    /**
     * @brief File descriptor of currently processed file
     *
     * @var resource
     */
    private $fd = FALSE;

    public function __construct()
    {
        self::fillMetaFields();
    }

    /**
     * @brief Try to load a post with given slug
     *
     * Tries to load and parse post with given slug name. The loaded post will
     * returned as a post record.
     *
     * @param $filename Name of the file containing the post
     * @param $meta_only Load only the metadata of the post(s)
     * @return The post record of given slug or NULL if post was not found or
     *         malformed.
     */
    public function tryToLoadPost(string $filename, bool $meta_only = FALSE): ?array
    {
        $this->fd = @fopen($filename, "r");
        if (FALSE === $this->fd)
        {
            return NULL;
        }

        /* Default values */
        $record = array(
            "hidden" => FALSE,
        );
        /* Read the actual values */
        $record['title'] = $this->postReadTitle();
        while ($meta = $this->postReadMeta())
        {
            $record[$meta["meta"]] = $meta["value"];
        }
        /* Set the implicit value */
        if (!array_key_exists("updated", $record) &&
                array_key_exists("published", $record))
        {
            $record["updated"] = $record["published"];
        }

        $record['excerpt'] = $this->postReadExcerpt();

        if (!$meta_only)
        {
            $record['content'] = $this->postReadContent();
        }

        fclose($this->fd);
        $this->fd = FALSE;
        return self::postRecordIsInvalid($record) ? NULL : $record;
    }


    /*========================================================================
                                 Private Methods
      ========================================================================*/

    /**
     * @brief Associative array containing meta data and how to handle them
     *
     * @var array
     */
    private static $meta_fields = array();

    /**
     * @brief Fill the @ref $meta_fields variable, if empty
     */
    private static function fillMetaFields(): void
    {
        if (empty(self::$meta_fields))
        {
            self::$meta_fields = array(
                "published" => "strtotime",
                "updated" => "strtotime",
                "tags" => static function(string $tags)
                {
                    $tags = preg_split("/\s*,\s*/", $tags);
                    $tags = array_combine(array_map("toSlug", $tags), $tags);
                    /* Remove invalid tags */
                    unset($tags["n-a"]);
                    return $tags;
                },
                "hidden" => static function(string $is_hidden)
                {
                    return "yes" === $is_hidden;
                }
            );
        }
    }

    /**
     * @brief Verify that the record is valid post
     *
     * Post needs to contain title, published time and tags.
     *
     * @param $record Post record array
     *
     * @return @c TRUE if is valid
     */
    private static function postRecordIsInvalid(array $record): bool
    {
        return (NULL === $record["title"] ||
                !array_key_exists("published", $record) ||
                !array_key_exists("tags", $record));
    }

    /**
     * @brief Read the title of the post
     *
     * @return Post title
     */
    private function postReadTitle(): ?string
    {
        $init_pos = ftell($this->fd);
        if(preg_match("/^\s*#\s*(.+)\s*$/",
                fgets($this->fd, self::META_LINE_MAX_LENGTH),
                $matched))
        {
            return $matched[1];
        }
        else
        {
            fseek($this->fd, $init_pos, SEEK_SET);
            return NULL;
        }
    }

    /**
     * @brief Read the meta data from the post file
     *
     * @return Meta data name and its value
     */
    private function postReadMeta(): ?array
    {
        $init_pos = ftell($this->fd);
        if(preg_match("/^\s*\- \s*([a-z]+)\s*:\s*(.+)$/",
                fgets($this->fd, self::META_LINE_MAX_LENGTH),
                $matched))
        {
            if (array_key_exists($matched[1], self::$meta_fields))
            {
                return array(
                    "meta" => $matched[1],
                    "value" => self::$meta_fields[$matched[1]]($matched[2])
                );
            }
        }

        fseek($this->fd, $init_pos, SEEK_SET);
        return NULL;
    }

    /**
     * @brief Read post excerpt
     *
     * @return Post excerpt
     */
    private function postReadExcerpt(): string
    {
        $excerpt = "";

        while ($excerpt = fgets($this->fd, self::EXCERPT_LINE_MAX_LENGTH))
        {
            if (!preg_match("/^\s*$/", $excerpt))
            {
                break;
            }
        }

        $excerpt_length = strlen($excerpt);
        while ($line = fgets($this->fd, self::EXCERPT_LINE_MAX_LENGTH))
        {
            if (preg_match("/^\s*$/", $line))
            {
                break;
            }

            $excerpt_length += strlen($line);
            if ($excerpt_length > self::EXCERPT_MAX_LENGTH)
            {
                break;
            }
            $excerpt .= $line;
        }

        return trim($excerpt);
    }

    /**
     * @brief Read post content
     *
     * @return Post content
     */
    private function postReadContent(): string
    {
        return trim(fread($this->fd, POST_MAX_SIZE));
    }
}

