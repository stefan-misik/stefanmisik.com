<?php

require_once(dirname(__FILE__) . "/../common.php");
require_once(dirname(__FILE__) . "/../post.php");


class PostTestCase extends PHPUnit\Framework\TestCase
{
    protected $test_record;

    protected function setUp(): void
    {
        $this->test_record = array(
            "slug" => "test-post",
            "title" => "Test Post Title",
            "published" => 1530777600,
            "updated" => 1530787600,
            "tags" => array(
                "tag3" => "tag3",
                "multiword-tag" => "Multiword tag"
            ),
            "hidden" => FALSE,
            "excerpt" => "This is <b>excerpt</b><>.",
            "content" => "This is content."
        );
    }

    protected function tearDown(): void
    {
    }


    public function testGetPostSlug()
    {
        $post = new Post($this->test_record);
        $this->assertEquals("test-post", $post->getSlug());
    }

    public function testGetPostLink()
    {
        $post = new Post($this->test_record);
        $this->assertEquals("https://www.stefanmisik.com/post/test-post",
            $post->getLink());
    }

    public function testGetPostSourceLink()
    {
        $post = new Post($this->test_record);
        $this->assertEquals("https://www.stefanmisik.com/post/test-post.md",
            $post->getSrcLink());
    }

    public function testGetIsHidden()
    {
        $post = new Post($this->test_record);
        $this->assertEquals(FALSE, $post->isHidden());

        $this->test_record["hidden"] = TRUE;
        $post = new Post($this->test_record);
        $this->assertEquals(TRUE, $post->isHidden());
    }

    public function testGetTags()
    {
        $post = new Post($this->test_record);
        $this->assertEquals(array( "tag3" => "tag3",
            "multiword-tag" => "Multiword tag"),
            $post->getTags());
    }

    public function testGetExcerpt()
    {
        $post = new Post($this->test_record);
        $this->assertEquals("This is excerpt.", $post->getExcerpt());
    }

    public function testGetHtml()
    {
        $post = new Post($this->test_record);
        $this->assertEquals("<p>This is <b>excerpt</b>&lt;&gt;.</p>\n" .
                "<p>This is content.</p>", $post->getHtml());
    }

    public function testGetPublishTime()
    {
        $post = new Post($this->test_record);
        $this->assertEquals(1530777600, $post->getPublishTime());
    }

    public function testGetUpdateTime()
    {
        $post = new Post($this->test_record);
        $this->assertEquals(1530787600, $post->getUpdateTime());
    }

    public function testGetUpdateTimeOfAPostThatHasNotBeenUpdated()
    {
        // Not-updated posts have the same update and publish time
        $this->test_record["updated"] = $this->test_record["published"];

        $post = new Post($this->test_record);
        $this->assertEquals($this->test_record["published"],
                $post->getUpdateTime());
    }

    public function testGetUpdateStatus()
    {
        $post = new Post($this->test_record);
        $this->assertEquals(TRUE, $post->isUpdated());
    }

    public function testGetUpdateStatusOfAPostThatHasNotBeenUpdated()
    {
        // Not-updated posts have the same update and publish time
        $this->test_record["updated"] = $this->test_record["published"];

        $post = new Post($this->test_record);
        $this->assertEquals(FALSE, $post->isUpdated());
    }

    public function testTitle()
    {
        $post = new Post($this->test_record);
        $this->assertEquals("Test Post Title", $post->getTitle());
    }
}

