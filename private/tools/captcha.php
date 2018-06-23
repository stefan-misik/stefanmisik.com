<?php

/**
 * @brief Captcha generation tool
 */
class Captcha 
{
    const SESSION_PREFIX = "CAPTCHA_";
    const ANSWER_SEPARATOR = ":";
    const UNKNOWN_DELIMITER = "?";
    
    private static $operators = array("+", "-", "*", "/");    
    private static $numbers = array("zero", "one", "two", "three", "four",
        "five", "six", "seven", "eight", "nine", "ten", "eleven", "twelve",
        "thirteen", "fourteen", "fifteen", "sixteen", "seventeen", "eighteen",
        "nineteen", "twenty"
    );
    
    /**
     * @name of the captcha
     *
     * @var string
     */
    private $name;
    
    /**
     * @brief Answer of the captcha
     *
     * @var string
     */
    private $answer;
    
    /**
     * @brief Hint string
     *
     * @var string
     */
    public $hint;
    
    /*========================================================================
                                 Private Methods                              
      ========================================================================*/

    /**
     * @brief Generate hint text image and output it
     * 
     * @param string $text Text to be shown on the hint image
     */
    private static function outputHintText(
            string $text
            )
    {
        $box = imagettfbbox(12, 0, dirname(__FILE__) . '/coolvetica.ttf',
                $text);
        $width = $box[4];
        // Round up to 20 pixels
        $width = 20 * ceil($width / 20);
        $image = imagecreate($width + 20, 20);
        imagecolorallocate($image, 255, 255, 255);
        $text_color = imagecolorallocate($image, 0, 0, 0);
        imagettftext($image, 12, 0, 10, 15, $text_color,
                dirname(__FILE__) . '/coolvetica.ttf', $text);
        imagepng($image);
        imagedestroy($image);
    }

    /**
     * @brief calculates the answer of the desired operation
     * 
     * @param int $op Operation id
     * @param int $a First operand
     * @param int $b Secont operand
     * 
     * @return int Correct answer
     */
    private static function calcAnswer(
            $op,
            $a,
            $b
            )
    {
        switch ($op)
        {
            case 0:
                return $a + $b;
            case 1:
                return $a - $b;
            case 2: 
                return $a * $b;
            case 3:
                return $a / $b;           
        }
    }
    
    /**
     * @brief Get string of the number randomly as number or word
     * 
     * @param int $num Number
     * @return int|string Number or word representing the string
     */
    private static function getNumberStr(
            $num
            )
    {
        if ($num >= 0 && $num <= 20 && rand(1, 2) == 2)
        {
            return self::$numbers[$num];
        }
        else
        {
            return $num;
        }
    }

    /**
     * @brief Store captcha object information into session variable
     */
    private function serialize(
            )
    {
        $_SESSION[self::SESSION_PREFIX . $this->name] = $this->hint[0] .
                self::UNKNOWN_DELIMITER . $this->hint[1] .
                self::ANSWER_SEPARATOR . $this->answer;
    }

    /**
     * @brief Restore captcha object from session variable
     */
    private function deserialize(
            )
    {
        $sess_var = self::SESSION_PREFIX . $this->name;
        
        if (array_key_exists($sess_var, $_SESSION))
        {
            $data = explode(self::ANSWER_SEPARATOR, $_SESSION[$sess_var]);
            if (2 == count($data))
            {
                $this->hint = explode(self::UNKNOWN_DELIMITER, $data[0]);
                $this->answer = filter_var($data[1], FILTER_VALIDATE_INT);
            }
        }
    }
    
    /*========================================================================
                                 Public Methods                              
      ========================================================================*/
    
    /**
     * @brief Captcha constructor
     * 
     * @param string $name Name of the captcha
     */
    public function __construct(
            $name
            ) 
    {
        $this->name = $name;
        ensureSession();
        $this->deserialize();
    }
    
    /**
     * @brief Get the name of the captcha
     * 
     * @return string Name of the captcha
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @brief Generate one of two hint png images and output it
     * 
     * @param int $id Image ID; either 0 or 1
     */
    public function outputHintImage(
            int $id
            )
    {
        self::outputHintText($this->hint[$id]);
    }

    /**
     * @brief Check the provided answer
     * 
     * @param mixed $answer Provided answer to check
     * 
     * @return bool TRUE if ok
     */
    public function checkAnswer(
            $answer
            )
    {
        return filter_var($answer, FILTER_VALIDATE_INT) === $this->answer;
    }
    
    /**
     * @brief Randomly generate the captcha
     * 
     */
    public function generate(
            )
    {
        /* Operation */
        $op = rand(0, 3);
        /* Generate second number */
        $b = rand(1, 10);
        /* Generate first number */
        if ($op == 3)
        {
            $a = rand(1, 10) * $b;
        }        
        else
        {            
           $a = rand(1, 10);
        }
        
        /* Result */
        $result = self::calcAnswer($op, $a, $b);
        
        switch (rand(0, 2))
        {
            case 2:
                $this->hint[0] = "";
                $this->hint[1] = self::$operators[$op] .  " " .
                    self::getNumberStr($b) . " = " .
                    self::getNumberStr($result);
                $this->answer = $a;
                break;
            case 1:
                $this->hint[0] = self::getNumberStr($a) . " " . 
                    self::$operators[$op];
                $this->hint[1] = "= " . self::getNumberStr($result);
                $this->answer = $b;
                break;
            default:
                $this->hint[0] = self::getNumberStr($a) . " " . 
                    self::$operators[$op] . " " . self::getNumberStr($b) . 
                    " =";
                $this->hint[1] = "";
                $this->answer = $result;
                break;
        }
        $this->serialize();
    }
}
