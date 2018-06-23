<?php

require_once dirname(__FILE__) . "/formatter.php";

/**
 * @brief class describing a home-page
 */
class Homepage
{
    /**
     * @brief The translated home-page
     *
     * @var string
     */
    private $html = NULL;

    /**
     * @brief Post formatter
     *
     * @var Formatter
     */
    private $formatter = NULL;

    /*========================================================================
                                 Private Methods                              
      ========================================================================*/
    
    /*========================================================================
                                 Public Methods                              
      ========================================================================*/

   /**
     * @brief Home-page constructor
     * 
     * @throws Exception In case the home-page file can not be opened
     */
    public function __construct(
            ) 
    {
        $this->formatter = new Formatter();

        try
        {
            $raw_page = file_get_contents(HOME_PAGE);
        }catch (Exception $ex)
        {
            throw new Exception("Could not open the home page.");
        }

        /* Configure formatter */
        $this->formatter->setRelativeUrlPrefix(getUrlAddress("") . "/");
        $this->formatter->setAbsoluteUrlPrefix(getUrlAddress(""));
        $this->formatter->registerModules();
        
        /* Do the modules */
        $this->html = $this->formatter->text($raw_page);
    }

    /**
     * @brief Get the HTML representation of the home-page
     * 
     * @return string Parsed HTML of the home-page
     * 
     * @throws Exception In case the home-page file can not be opened
     */
    public function getHtml(
            )
    {
        return $this->html;
    }
}
