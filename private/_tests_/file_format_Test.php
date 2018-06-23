<?php

require_once(dirname(__FILE__) . "/fake/postfs.php");
require_once(dirname(__FILE__) . "/../common.php");
require_once(dirname(__FILE__) . "/../post_source/file_format.php");


class PostFileFormatTestCase extends PHPUnit\Framework\TestCase
{
    /**
     *  @brief Tested object
     *
     *  @var FileFormat
     */
    protected $tested = NULL;

    protected function setUp(): void
    {
        $this->tested = new FileFormat();

        PostFs::register(array());

        PostFs::setPost("post-1.md",
            "# Post 1 Title\n" .
            " - published: 2018-07-05T09:00:00+0000\n" .
            " - tags: tag1, tag2\n" .
            "This is first post's excerpt.\n" .
            "\n" .
            "This is the test post."
        );

        PostFs::setPost("post-2.md",
            "# Post 2 Title\n" .
            " - published: 2018-07-05T08:00:00+0000\n" .
            " - tags: tag3, tag2, Multiword tag\n" .
            " - hidden: no\n" .
            "This is second post's excerpt.\n" .
            "  \n" .
            "This is the second test post."
        );

        PostFs::setPost("post-3.md",
            "# Post 3 Title\n" .
            " - published: 2018-07-06T08:00:00+0000\n" .
            " - tags: tag3, tag4, tag5\n" .
            "\n" .
            "\n" .
            "This is third post's excerpt.\n" .
            "This is another excerpt line.\n" .
            "\n" .
            "\n" .
            "This is the third test post.\n" .
            "And another line."
        );

        PostFs::setPost("post-4.md",
            "# Post 4 Title\n" .
            " - published: 2019-07-06T08:00:00+0000\n" .
            " - tags: tag1, tag2, tag3, tag4, tag5\n" .
            " - hidden: yes\n" .
            "This is fourth post's excerpt.\n" .
            "\t\n" .
            "This is the fourth test post."
        );

        PostFs::setPost("post-5.md",
            "# Post 5 Title\n" .
            " - published: 2018-07-05T08:00:00+0000\n" .
            " - updated: 2018-07-05T09:10:00+0000\n" .
            " - tags: tag3, tag2\n" .
            " - hidden: no\n" .
            "This is fifth post's excerpt.\n"
        );
    }

    protected function tearDown(): void
    {
        PostFs::unregister();
    }

    public function testGetSingleNonExistentPost()
    {
        $this->assertEmpty($this->tested->tryToLoadPost(
            "postfs://post-that-does-not-exist.md"));
    }

    public function testGetSinglePostWithMissingTitle()
    {
        PostFs::setPost("post.md",
            " - published: 2018-07-05T08:00:00+0000\n" .
            " - tags: lua, makefile, git, programming\n" .
            " - hidden: yes\n" .
            "This is test post."
        );
        $this->assertEmpty($this->tested->tryToLoadPost("postfs://post.md"));
    }

    public function testGetSinglePostWithMissingPublishedTime()
    {
        PostFs::setPost("post.md",
            "# Post\n" .
            " - tags: lua, makefile, git, programming\n" .
            " - hidden: yes\n" .
            "This is test post."
        );
        $this->assertEmpty($this->tested->tryToLoadPost("postfs://post.md"));
    }

    public function testGetSinglePostWithMissingTags()
    {
        PostFs::setPost("post.md",
            "# Post\n" .
            " - published: 2018-07-05T08:00:00+0000\n" .
            " - hidden: yes\n" .
            "This is test post."
        );
        $this->assertEmpty($this->tested->tryToLoadPost("postfs://post.md"));
    }

    public function testGetSinglePostWithMissingHiddenFlag()
    {
        $this->assertEquals(
            array(
                "title" => "Post 1 Title",
                "published" => 1530781200,
                "updated" => 1530781200,
                "tags" => array("tag1" => "tag1", "tag2" => "tag2"),
                "hidden" => FALSE,
                "excerpt" => "This is first post's excerpt.",
                "content" => "This is the test post."
            ),
            $this->tested->tryToLoadPost("postfs://post-1.md")
        );
    }

    public function testGetSinglePostWithMissingContent()
    {
        PostFs::setPost("post-without-content.md",
            "# Title\n" .
            " - published: 2018-07-05T08:00:00+0000\n" .
            " - tags: tag3, tag4\n" .
            " - hidden: no\n" .
            "\n" .
            "\n"
        );

        $this->assertEquals(
            array(
                "title" => "Title",
                "published" => 1530777600,
                "updated" => 1530777600,
                "tags" => array("tag3" => "tag3", "tag4" => "tag4"),
                "hidden" => FALSE,
                "excerpt" => "",
                "content" => ""
            ),
            $this->tested->tryToLoadPost("postfs://post-without-content.md")
        );
    }

    public function testGetSinglePostWithSpaceBetweenMetaAndContent()
    {
        $this->assertEquals("This is the third test post.\n" .
            "And another line.",
            $this->tested->tryToLoadPost("postfs://post-3.md")["content"]);
    }

    public function testGetSinglePostWithMultilineExcerpt()
    {
        $this->assertEquals("This is third post's excerpt.\n" .
            "This is another excerpt line.",
            $this->tested->tryToLoadPost("postfs://post-3.md")["excerpt"]);
    }


    public function testMultipleTags()
    {
        PostFs::setPost("post.md",
            "# Post Title\n" .
            " - published: 2018-07-05T08:00:00+0000\n" .
            " - tags: tag1, tag2, tag1\n" .
            "Content line."
        );

        $this->assertEquals(array("tag1" => "tag1", "tag2" => "tag2"),
            $this->tested->tryToLoadPost("postfs://post.md")["tags"]);
    }

    public function testMultiwordTag()
    {
        $this->assertEquals(array("multiword-tag" => "Multiword tag",
            "tag3" => "tag3", "tag2" => "tag2"),
            $this->tested->tryToLoadPost("postfs://post-2.md")["tags"]);
    }

    public function testPublisedTime()
    {
        $this->assertEquals(1530864000,
            $this->tested->tryToLoadPost("postfs://post-3.md")["published"]);
    }

    public function testUpdatedTime()
    {
        $this->assertEquals(1530781800,
            $this->tested->tryToLoadPost("postfs://post-5.md")["updated"]);
    }

    public function testUpdatedTimeNotSpecified()
    {
        $post = $this->tested->tryToLoadPost("postfs://post-4.md");
        $this->assertEquals($post["published"], $post["updated"]);
    }

    public function testGetOnlyMetaData()
    {
        $this->assertEquals(
            array(
                "title" => "Post 1 Title",
                "published" => 1530781200,
                "updated" => 1530781200,
                "tags" => array("tag1" => "tag1", "tag2" => "tag2"),
                "hidden" => FALSE,
                "excerpt" => "This is first post's excerpt."
            ),
            $this->tested->tryToLoadPost("postfs://post-1.md", TRUE));
    }
}
