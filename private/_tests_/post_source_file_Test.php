<?php

require_once(dirname(__FILE__) . "/fake/postfs.php");
require_once(dirname(__FILE__) . "/../common.php");
require_once(dirname(__FILE__) . "/../post_source/file.php");


class PostSourceFileTestCase extends PHPUnit\Framework\TestCase
{
    /**
     *  @brief Tested object
     *
     *  @var IPostSource
     */
    protected $tested;

    /**
     * @brief Test double used to replace tested file format
     *
     * @var FileFormat
     */
    protected $file_format_stub;

    /**
     * @brief Array containing the fake records to be used by the mock
     *
     * @var array
     */
    protected $fake_records;

    protected function addFakeRecord(string $slug, array $record): void
    {
        $filename = $slug . ".md";
        $this->fake_records[$filename] = $record;
        // Just a fake file with no content for directory listing
        PostFs::setPost($filename, "");
    }

    protected function setUp(): void
    {
        $this->tested = new FilePostSource("postfs://");

        $this->file_format_stub =
        $this->getMockBuilder(FileFormat::class)->getMock();
        $this->file_format_stub->method("tryToLoadPost")->will($this->returnCallback(
            function (string $filename, bool $meta_only = FALSE)
            {
                $base_filename = basename($filename);
                if (array_key_exists($base_filename, $this->fake_records))
                {
                    return $this->fake_records[$base_filename];
                }
                else
                {
                    return NULL;
                }
            }
        ));

        $file_format_accessor = new ReflectionProperty(
            FilePostSource::class, "file_format");
        $file_format_accessor->setAccessible(TRUE);
        $file_format_accessor->setValue($this->tested,
            $this->file_format_stub);

        PostFs::register(array());

        // -- Register fake post records

        $this->addFakeRecord("post-1", array(
            "title" => "Post 1 Title",
            "published" => 1530781200,
            "updated" => 1530781200,
            "tags" => array("tag1" => "tag1", "tag2" => "tag2"),
            "hidden" => FALSE,
            "excerpt" => "This is first post's excerpt.",
            "content" => "This is the test post."
        ));

        $this->addFakeRecord("post-2", array(
            "title" => "Post 2 Title",
            "published" => 1530777600,
            "updated" => 1530777600,
            "tags" => array("tag3" => "tag3", "tag2" => "tag2",
                "multiword-tag" => "Multiword tag"),
            "hidden" => FALSE,
            "excerpt" => "This is second post's excerpt.",
            "content" => "This is the second test post."
        ));

        $this->addFakeRecord("post-3", array(
            "title" => "Post 3 Title",
            "published" => 1530754000,
            "updated" => 1530864000,
            "tags" => array("tag3" => "tag3", "tag4" => "tag4",
                "tag5" => "tag5"),
            "hidden" => FALSE,
            "excerpt" => "This is third post's excerpt.",
            "content" => "This is the third test post.\n" .
                "And another line."
        ));

        $this->addFakeRecord("post-4", array(
            "title" => "Post 4 Title",
            "published" => 1562400000,
            "updated" => 1562400000,
            "tags" => array("tag1" => "tag1", "tag2" => "tag2",
                "tag3" => "tag3", "tag4" => "tag4", "tag5" => "tag5"),
            "hidden" => TRUE,
            "excerpt" => "This is fourth post's excerpt.",
            "content" => "This is the fourth test post."
        ));
    }

    protected function tearDown(): void
    {
        PostFs::unregister();
        $this->fake_records = array();
    }

    public function testGetSingleNonExistentPost()
    {
        $this->assertEquals(FALSE,
            $this->tested->querySource(array(
                    'slug' => 'post-that-does-not-exist')));
        $this->assertEmpty($this->tested->getNextPost());
    }

    public function testGetSingleHiddenPost()
    {
        $this->assertEquals(TRUE,
            $this->tested->querySource(array(
                'slug' => 'post-4',
                'hidden' => TRUE
            )));

        $this->assertEquals(TRUE, $this->tested->getNextPost()["hidden"]);
        $this->assertEmpty($this->tested->getNextPost());
    }

    public function testGetMultiplePosts()
    {
        $this->tested->querySource(array());

        $this->assertEquals("Post 1 Title",
            $this->tested->getNextPost()["title"]);
        $this->assertEquals("Post 2 Title",
            $this->tested->getNextPost()["title"]);
        $this->assertEquals("Post 3 Title",
            $this->tested->getNextPost()["title"]);
        $this->assertEquals(NULL,
            $this->tested->getNextPost());
    }

    public function testFilterByTag()
    {
        $this->tested->querySource(array('tag' => 'tag3'));

        $this->assertEquals("Post 2 Title",
            $this->tested->getNextPost()["title"]
        );
        $this->assertEquals("Post 3 Title",
            $this->tested->getNextPost()["title"]
        );
        $this->assertEquals(NULL, $this->tested->getNextPost());
    }

    public function testFilterByTagSlug()
    {
        $this->tested->querySource(array('tag' => 'multiword-tag'));

        $this->assertEquals("Post 2 Title",
            $this->tested->getNextPost()["title"]
        );
        $this->assertEquals(NULL, $this->tested->getNextPost());
    }

    public function testSortPostsFromOldest()
    {
        $this->tested->querySource(array('sortfrom' => 'oldest'));

        $this->assertEquals("Post 2 Title",
            $this->tested->getNextPost()["title"]);
        $this->assertEquals("Post 1 Title",
            $this->tested->getNextPost()["title"]);
        $this->assertEquals("Post 3 Title",
            $this->tested->getNextPost()["title"]);
        $this->assertEquals(NULL,
            $this->tested->getNextPost());
    }

    public function testSortPostsFromNewest()
    {
        $this->tested->querySource(array('sortfrom' => 'newest'));

        $this->assertEquals("Post 3 Title",
            $this->tested->getNextPost()["title"]);
        $this->assertEquals("Post 1 Title",
            $this->tested->getNextPost()["title"]);
        $this->assertEquals("Post 2 Title",
            $this->tested->getNextPost()["title"]);
        $this->assertEquals(NULL,
            $this->tested->getNextPost());
    }

    public function testListOnlyPublishedBeforeCertainTime()
    {
        $this->tested->querySource(array(
            'publishedbefore' => 1530781100));

        $this->assertEquals("Post 2 Title",
            $this->tested->getNextPost()["title"]);
        $this->assertEquals("Post 3 Title",
            $this->tested->getNextPost()["title"]);
        $this->assertEquals(NULL,
            $this->tested->getNextPost());
    }

    public function testCountTheResults()
    {
        $this->tested->querySource(array());
        $this->assertEquals(3, $this->tested->countResults());
    }

    public function testLimitThePostCount()
    {
        $this->tested->querySource(array(
            'sortfrom' => 'newest',
            'limit' => 2
        ));

        $this->assertEquals("Post 3 Title",
            $this->tested->getNextPost()["title"]);
        $this->assertEquals("Post 1 Title",
            $this->tested->getNextPost()["title"]);
        $this->assertEquals(NULL,
            $this->tested->getNextPost());
    }
}
