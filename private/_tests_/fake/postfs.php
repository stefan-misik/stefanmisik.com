<?php

class PostFs
{
    const PROTOCOL = "postfs";

    /**
     * @brief Associative array containing the files and their contents
     * 
     * @var array
     */
    private static $posts = NULL;

    /**
     * @brief Register the virtual post file system
     *
     * @param $posts Associative array containing the post files
     */
    public static function register(array $posts): bool
    {
        self::$posts = $posts;
        return stream_wrapper_register(self::PROTOCOL, __CLASS__);
    }

    /**
     * @brief Register the virtual post file system
     *
     * @param $posts Associative array containing the post files
     */
    public static function unregister(): bool
    {
        self::$posts = NULL;
        return stream_wrapper_unregister(self::PROTOCOL);
    }

    /**
     * @brief Set the contents of given post file
     *
     * @param $filename Name of the posy file
     * @param $contents Contents of the post
     */
    public static function setPost(string $filename, string $contents): void
    {
        self::$posts[$filename] = $contents;
    }

    /**
     * @brief Remove protocol part of the path
     *
     * @param $path Path provided by the client code
     *
     * @return Filename inside the virtual file system
     */
    private static function getFilename(string $path): string
    {
        return substr($path, strlen(self::PROTOCOL . "://"));
    }

    /*========================================================================
                              Stream Wrapper Data
      ========================================================================*/

    /**
     * @brief Position in file
     *
     * @var int
     */
    protected $position;

    /**
     * @brief Name of the currently opened file
     *
     * @var string
     */
    protected $filename;

    /*========================================================================
                              Stream Wrapper Methods
      ========================================================================*/

    public function dir_opendir($path, $options): bool
    {
        if ("" == self::getFilename($path))
        {
            $this->position = 0;
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    public function dir_readdir()
    {
        $filenames = array_keys(self::$posts);
        if ($this->position < count($filenames))
        {
            $filename = $filenames[$this->position];
            $this->position ++;
            return $filename;
        }
        else
        {
            return FALSE;
        }
    }

    public function stream_open($path, $mode, $options, &$opened_path)
    {
        $this->filename = self::getFilename($path);

        if (array_key_exists($this->filename, self::$posts) && "r" === $mode)
        {
            $this->position = 0;
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    public function stream_read(int $count)
    {
        $read = substr(self::$posts[$this->filename],
                $this->position,
                $count);

        $this->position += strlen($read);
        return $read;
    }

    public function stream_eof (): bool
    {
        return strlen(self::$posts[$this->filename]) <= $this->position;
    }

    public function stream_tell(): int
    {
        return $this->position;
    }

    public function stream_seek($offset, $whence = SEEK_SET): bool
    {
        $new_pos = 0;
        switch($whence)
        {
            case SEEK_SET:
                $new_pos = $offset;
                break;
            case SEEK_CUR:
                $new_pos = $this->position + $offset;
                break;
            case SEEK_END:
                $new_pos = strlen(self::$posts[$this->filename]) + $offset;
                break;
        }

        if ($new_pos < 0)
        {
            return FALSE;
        }
        else
        {
            $this->position = $new_pos;
            return TRUE;
        }
    }

    public function url_stat(string $path, int $flags)
    {
        $filename = self::getFilename($path);
        if (!array_key_exists($filename, self::$posts))
        {
            return NULL;
        }

        return array(
            0, // Device number
            array_search($filename, array_keys(self::$posts)), // Inode
            0, // inode protection number
            0, // number of links
            0, // uid
            0, // gid
            0, // device type
            strlen(self::$posts[$filename]), // Size in bytes
            0, // time of last access
            0, // time of last modification
            0, // time of last inode change
            -1, // blocksize of filesystem IO
            -1, // number of 512-byte blocks allocated
        );
    }
}
