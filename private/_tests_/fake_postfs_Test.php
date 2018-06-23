<?php

require_once(dirname(__FILE__) . "/fake/postfs.php");


class PostFsTestCase extends PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        PostFs::register(array());
    }

    protected function tearDown(): void
    {
        PostFs::unregister();
    }


    public function testListIncorrectDir(): void
    {
        $this->assertEmpty(@scandir("postfs://abc/"));
    }

    public function testListEmptyDir(): void
    {
        $this->assertEquals(array(), scandir("postfs://"));
    }

    public function testListPosts(): void
    {
        PostFs::setPost("post-name.md", "test");
        PostFs::setPost("post-name-2.md", "test2");

        $this->assertEquals(array("post-name-2.md", "post-name.md"), scandir("postfs://"));
    }

    public function testOpenNonExistentFile(): void
    {
        PostFs::setPost("post-name.md", "test");
        $fd = @fopen("postfs://post-name-2.md", "r");
        $this->assertEquals(FALSE, $fd);
    }

    public function testOpenFileInWrongMode(): void
    {
        PostFs::setPost("post-name.md", "test");
        $fd = @fopen("postfs://post-name.md", "w");
        $this->assertEquals(FALSE, $fd);
    }

    public function testOpenClose(): void
    {
        PostFs::setPost("post-name.md", "test1\ntest2\ntest3");
        $fd = @fopen("postfs://post-name.md", "r");
        $this->assertIsResource($fd);
        $this->assertEquals(TRUE, @fclose($fd));
    }

    public function testReadFile(): void
    {
        PostFs::setPost("post-name.md", "test");
        $fd = @fopen("postfs://post-name.md", "r");
        $this->assertEquals("te", fread($fd, 2));
        $this->assertEquals("st", fread($fd, 2));
        @fclose($fd);
    }

    public function testReadMoreThanAvailable(): void
    {
        PostFs::setPost("post-name.md", "test");
        $fd = @fopen("postfs://post-name.md", "r");
        $this->assertEquals("test", fread($fd, 9));
        $this->assertEquals("", fread($fd, 2));
        @fclose($fd);
    }

    public function testReadLineByLine(): void
    {
        PostFs::setPost("post-name.md", "test1\ntest2\ntest3");
        $fd = @fopen("postfs://post-name.md", "r");
        $this->assertEquals("test1\n", fgets($fd));
        $this->assertEquals("test2\n", fgets($fd));
        $this->assertEquals("test3", fgets($fd));
        $this->assertEquals("", fgets($fd));
        @fclose($fd);
    }

    public function testTellPosition(): void
    {
        PostFs::setPost("post-name.md", "test1\ntest2\ntest3");
        $fd = @fopen("postfs://post-name.md", "r");
        $this->assertEquals(0, ftell($fd));
        fread($fd, 6);
        $this->assertEquals(6, ftell($fd));
        fread($fd, 6);
        $this->assertEquals(12, ftell($fd));
        fread($fd, 5);
        $this->assertEquals(17, ftell($fd));
        @fclose($fd);
    }

    public function testTellPositionAfterReadingMoreThanAvailable(): void
    {
        PostFs::setPost("post-name.md", "test1");
        $fd = @fopen("postfs://post-name.md", "r");
        fread($fd, 10);
        $this->assertEquals(5, ftell($fd));
        @fclose($fd);
    }

    public function testSeekSet(): void
    {
        PostFs::setPost("post-name.md", "test1\ntest2\ntest3");
        $fd = @fopen("postfs://post-name.md", "r");
        $this->assertEquals(0, fseek($fd, 5, SEEK_SET));
        $this->assertEquals(5, ftell($fd));
        $this->assertEquals(0, fseek($fd, 3, SEEK_SET));
        $this->assertEquals(3, ftell($fd));
        $this->assertEquals("t1\n", fgets($fd));
        @fclose($fd);
    }

    public function testSeekCur(): void
    {
        PostFs::setPost("post-name.md", "test1\ntest2\ntest3");
        $fd = @fopen("postfs://post-name.md", "r");
        $this->assertEquals(0, fseek($fd, 8, SEEK_CUR));
        $this->assertEquals(8, ftell($fd));
        $this->assertEquals(0, fseek($fd, 2, SEEK_CUR));
        $this->assertEquals(10, ftell($fd));
        $this->assertEquals(0, fseek($fd, -9, SEEK_CUR));
        $this->assertEquals(1, ftell($fd));
        $this->assertEquals("est1\n", fgets($fd));
        @fclose($fd);
    }

    public function testSeekEnd(): void
    {
        PostFs::setPost("post-name.md", "test1\ntest2\ntest3");
        $fd = @fopen("postfs://post-name.md", "r");
        $this->assertEquals(0, fseek($fd, -7, SEEK_END));
        $this->assertEquals(10, ftell($fd));
        $this->assertEquals("2\n", fgets($fd));
        @fclose($fd);
    }

    public function testSeekSetBeforeStart(): void
    {
        PostFs::setPost("post-name.md", "test1\ntest2\ntest3");
        $fd = @fopen("postfs://post-name.md", "r");
        $this->assertEquals(-1, fseek($fd, -1, SEEK_SET));
        $this->assertEquals(0, ftell($fd));
        @fclose($fd);
    }

    public function testSeekCurBeforeStart(): void
    {
        PostFs::setPost("post-name.md", "test1\ntest2\ntest3");
        $fd = @fopen("postfs://post-name.md", "r");
        fseek($fd, 8, SEEK_CUR);
        $this->assertEquals(-1, fseek($fd, -9, SEEK_CUR));
        $this->assertEquals(8, ftell($fd));
        @fclose($fd);
    }

    public function testSeekEndBeforeStart(): void
    {
        PostFs::setPost("post-name.md", "test1\ntest2\ntest3");
        $fd = @fopen("postfs://post-name.md", "r");
        $this->assertEquals(-1, fseek($fd, -18, SEEK_END));
        $this->assertEquals(0, ftell($fd));
        @fclose($fd);
    }

    public function testFileExistsThatDoesNotExist(): void
    {
        PostFs::setPost("post-name.md", "test1\ntest2\ntest3");
        $this->assertEquals(FALSE,
            file_exists("postfs://post-name-that-does-not-exist.md"));
    }

    public function testFileExists(): void
    {
        PostFs::setPost("post-name.md", "test1\ntest2\ntest3");
        $this->assertEquals(TRUE, file_exists("postfs://post-name.md"));
    }
}
