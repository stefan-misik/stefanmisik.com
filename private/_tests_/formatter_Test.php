<?php

require_once(dirname(__FILE__) . "/../common.php");
require_once(dirname(__FILE__) . "/../formatter.php");


class FormatterTestCase extends PHPUnit\Framework\TestCase
{
    /**
     *  @brief Tested object
     *
     *  @var Formatter
     */
    protected $tested = NULL;

    protected function setUp(): void
    {
        $this->tested = new Formatter();
        $this->tested->setRelativeUrlPrefix("https://mydomain.com/some-folder/");
        $this->tested->setAbsoluteUrlPrefix("https://mydomain.com");
    }

    protected function tearDown(): void
    {
        $this->tested = NULL;
    }

    public function testSingleParagraph()
    {
        $this->assertEquals("<p>Test paragraph.</p>",
            $this->tested->text("Test paragraph."));
    }

    public function testExternalLink()
    {
        $this->assertEquals("<p>" .
            "<a href=\"https://otherpage.com/\" target=\"_blank\" " .
                "class=\"external\">Link</a></p>",
            $this->tested->text("[Link](https://otherpage.com/)"));
    }

    public function testAbsoluteLink()
    {
        $this->assertEquals("<p>" .
            "<a href=\"https://mydomain.com/some-page\">Absolute Link</a></p>",
            $this->tested->text("[Absolute Link](/some-page)"));
    }

    public function testRelativeLink()
    {
        $this->assertEquals("<p>" .
            "<a href=\"https://mydomain.com/some-folder/some-document.txt\">" .
            "Relative Link</a></p>",
            $this->tested->text("[Relative Link](some-document.txt)"));
    }

    public function testExternalImage()
    {
        $this->markTestSkipped("Not sure how to implement this");

        $this->assertEquals("<p>" .
            "<img src=\"https://otherpage.com/img.gif\" alt=\"Image\" " .
                "title=\"Image Title\" /></p>",
            $this->tested->text("![Image](https://otherpage.com/img.gif ".
                "\"Image title\")"));
    }

    public function testAbsoluteImage()
    {
        $this->assertEquals("<p>" .
            "<img src=\"https://mydomain.com/some-image.gif\" " .
                "alt=\"Absolute Image\" title=\"Image Title\" /></p>",
            $this->tested->text("![Absolute Image](/some-image.gif " .
                "\"Image Title\")"));
    }

    public function testRelativeImage()
    {
        $this->assertEquals("<p>" .
            "<img src=\"https://mydomain.com/some-folder/some-image.gif\" " .
                "alt=\"Relative Image\" title=\"Image Title\" /></p>",
            $this->tested->text("![Relative Image](some-image.gif " .
            "\"Image Title\")"));
    }
}
