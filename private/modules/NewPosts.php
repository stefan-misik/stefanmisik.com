<?php

require_once dirname(__FILE__) . "/../post_source.php";
require_once dirname(__FILE__) . "/../post.php";

/**
 * @brief Module for listing new posts
 */
class NewPostsModule extends Module
{
    /**
     * @brief The count of newest posts to show
     * 
     * @var int
     */
    private $post_count;
    
    /*========================================================================
                                 Private Methods                              
      ========================================================================*/

    /*========================================================================
                                 Public Methods                              
      ========================================================================*/
    
    /**
     * @brief Base module constructor
     * 
     * @param string $params Module parameters
     */
    public function __construct(
            string $params
            )
    {
        $this->post_count = (int)$params;
    }
    
    /**
     * @brief Get the module name
     * 
     * @return string Module name as a slug-like string
     */
    public static function getModuleName(
            )
    {
        return "new-posts";
    }
    
    /**
     * @brief Get body of the module
     * 
     * @return string Markdown output
     */
    public function getOutput(
            )
    {
        $post_source = GetPostSource();
        $post_source->querySource(
            array(
                "hidden" => FALSE,
                "sortfrom" => "newest",
                "publishedbefore" => time(),
                "metaonly" => TRUE,
                "limit" => $this->post_count
            )
        );

        $html = "<nav><ul>\n";

        while($post_record = $post_source->getNextPost())
        {
            $post = new Post($post_record);
            $html .= "  <li><a href=\"" . $post->getLink() . "\">" .
                    $post->getTitle() . "</a></li>\n";
        }

        $html .= "  <li><a href=\"" . getUrlAddress("archive") .
            "\">&#8226; &#8226; &#8226;</a></li>\n";

        $html .= "</ul></nav>";

        return $html;
    }
}
