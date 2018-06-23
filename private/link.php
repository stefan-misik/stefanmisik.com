<?php

class Link
{
    const TYPE_FILE = 0;
    const TYPE_LINK = 1;

    /**
     * @brief Array of links
     * 
     * @var array
     */
    private static $links = array(
        "misik-stefan-cv.pdf" => array(
            "type" => self::TYPE_FILE,
            "data_file" => "cv.pdf",
            "mime" => "application/pdf"
        ),
        "get-my-cv" => array(
            "type" => self::TYPE_LINK,
            "redirect" => "/misik-stefan-cv.pdf"
        ),
        "djxyw0g0t6-bc" => array(
            "type" => self::TYPE_LINK,
            "redirect" => "/djxyw0g0t6-bc-stefan-misik.vcf"
        ),
        "djxyw0g0t6-bc-stefan-misik.vcf" => array(
            "type" => self::TYPE_FILE,
            "data_file" => "stefan-misik.vcf",
            "mime" => "text/vcard"
        )
    );

    /*========================================================================
                                 Public Methods                              
      ========================================================================*/

    /**
     * @brief Try to handle passed link and return result
     * 
     * @param string $link Link to handle
     * 
     * @return bool TRUE in case the link was handled
     */
    public static function linkHandle($link)
    {
        if (!array_key_exists($link, self::$links))
        {
            return FALSE;
        }

        $link = self::$links[$link];

        switch($link["type"])
        {
            case self::TYPE_FILE:
                $file_name = DATA_HOME . $link["data_file"];
                if(!file_exists($file_name))
                {
                    return FALSE;
                }

                if (array_key_exists("mime", $link))
                {
                    header("Content-Type: " . $link["mime"]);
                }
                readfile($file_name);
            break;

            case self::TYPE_LINK:
                header("Location: " . $link["redirect"], TRUE, 302);
            break;
        }

        return TRUE;
    }
}
