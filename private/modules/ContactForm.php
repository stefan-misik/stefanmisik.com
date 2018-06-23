<?php


require_once dirname(__FILE__) . "/../tools/captcha.php";

/**
 * @brief Module for adding contact form
 */
class ContactFormModule extends Module
{
    const EMAIL_MAX_LENGTH   = 255;
    const MESSAGE_MAX_LENGTH = 4096;
    const CAPTCHA_MAX_LENGTH = 10;

    /**
     * @brief Error flags
     *
     * @var int
     */
    private $error;
    const ERR_NONE      = 0;
    const ERR_EMAIL     = 1;
    const ERR_MESSAGE   = 2;
    const ERR_CAPTCHA   = 4;
    const ERR_SEND_MAIL = 8;

    /**
     * @brief Recepient email address
     * 
     * @var string
     */
    private $email;
    /**
     * @brief Subject of the email
     *
     * @var string
     */
    private $subject;

    /**
     * @brief Object used to perform anti-spam protection
     *
     * @var Captcha
     */
    private $captcha;

    /**
     * @brief Mail was successfully sent
     *
     * @var boolean
     */
    private $was_sent;

    /**
     * @brief Contact form was submitted
     *
     * @var boolean
     */
    private $cf_submited;
    /**
     * @brief Contact form submitter email
     *
     * @var string
     */
    private $cf_email;
    /**
     * @brief Contact form message to be sent
     *
     * @var string
     */
    private $cf_message;

    /*========================================================================
                                 Private Methods                              
      ========================================================================*/

    /**
     * @brief Try to send an email
     * 
     */
    private function tryToSend()
    {
        if (self::ERR_NONE === $this->error && $this->cf_submited)
        {
            $headers  = "Content-Type: text/plain; charset=UTF-8\r\n";
            $headers .= "From: " . $this->cf_email . "\r\n";            
            
            if (mail($this->email, $this->subject, $this->cf_message, $headers))
            {
                /* After successfully sending the mail, re-initialize */
                $this->initFormData();
                $this->was_sent = TRUE;
            }
            else
            {
                $this->error |= self::ERR_SEND_MAIL;
            }
        }
    }

    /**
     * @brief Reset the contact form data
     * 
     */
    private function initFormData()
    {
        $this->error = self::ERR_NONE;
        $this->cf_email = NULL;
        $this->cf_message = NULL;
        $this->cf_submited = FALSE;
    }

    /**
     * @brief Get the inputs from the form
     * 
     */
    private function getInputs()
    {
        $this->cf_submited = array_key_exists("cf_send", $_POST);
        if ($this->cf_submited)
        {
            /* Get the email address */
            $this->cf_email = substr(filter_input(INPUT_POST, "cf_email", 
                    FILTER_SANITIZE_EMAIL), 0, self::EMAIL_MAX_LENGTH);
            $this->cf_email = filter_var($this->cf_email,
                    FILTER_VALIDATE_EMAIL);
            if (FALSE === $this->cf_email)
            {
                $this->error |= self::ERR_EMAIL;
            }

            /* Get the message */
            $this->cf_message = substr(
                    filter_input(INPUT_POST, "cf_message", 
                            FILTER_SANITIZE_STRING, 
                            FILTER_FLAG_NO_ENCODE_QUOTES),
                    0, self::MESSAGE_MAX_LENGTH);
            if (0 == strlen($this->cf_message))
            {
                $this->error |= self::ERR_MESSAGE;
            }

            /* Get captcha */
            $captcha = substr(
                    filter_input(INPUT_POST, "cf_antispam",
                            FILTER_SANITIZE_STRING, 
                            FILTER_FLAG_NO_ENCODE_QUOTES),
                    0, self::CAPTCHA_MAX_LENGTH);
            /* Check captcha */
            if (!$this->captcha->checkAnswer($captcha))
            {
                $this->error |= self::ERR_CAPTCHA;
            }
        }
    }

    /**
     * @brief Get the email field of the contact form
     * 
     * @return string HTML of the email field
     */
    private function getEmailField(
            )
    {
        $field  = "<label for=\"cf_email\">Your email address:</label>\n";
        $field .= "<input placeholder=\"your@email.com\" name=\"cf_email\" " .
                "id=\"cf_email\" type=\"email\" maxlength=\"" .
                self::EMAIL_MAX_LENGTH . "\" " . "value=\"" . $this->cf_email .
                "\" style=\"width: 50%;\" " . "class=\"" .
                $this->errorCssClass(self::ERR_EMAIL) . "\">";
        return self::makeFormRow($field);
    }

    /**
     * @brief Get the message field of the contact form
     * 
     * @return string HTML of the message field
     */
    private function getMessageField(
            )
    {
        $field  = "<label for=\"cf_message\">Your message:</label>\n";
        $field .= "<textarea placeholder=\"Hello...\" name=\"cf_message\" " .
                "id=\"cf_message\" maxlength=\"" . self::MESSAGE_MAX_LENGTH .
                "\" style=\"width: 100%; height: 10em; resize: vertical;\" " .
                "class=\"" . $this->errorCssClass(self::ERR_MESSAGE) . "\">" .
                $this->cf_message . "</textarea>\n";
        return self::makeFormRow($field);
    }

    /**
     * @brief Get the submit line of the contact form
     * 
     * @return string HTML of the submit line
     */
    private function getSubmitLine(
            )
    {
        $this->captcha->generate();
        $field  = "<img src=\"" . getUrlAddress("captcha/" .
                $this->captcha->getName() . "/0.png") .
                "\"> <input placeholder=\"##\" name=\"cf_antispam\" " .
                "type=\"text\" maxlength=\"" . self::CAPTCHA_MAX_LENGTH .
                "\" autocomplete=\"off\" style=\"width: 3em;\" class=\"" .
                $this->errorCssClass(self::ERR_CAPTCHA) . "\"> <img src=\"" .
                getUrlAddress("captcha/" . $this->captcha->getName() .
                "/1.png") . "\">";
        $field .= "<input name=\"cf_send\" value=\"Submit\" type=\"submit\" " .
                "class=\"button-primary\" style=\"float: right;\">\n";
        return self::makeFormRow($field);
    }

    /**
     * @brief Return error CSS class name if specific error is detected
     * 
     * @param int $error_flag Error flag to test for
     * 
     * @return string Error CSS class name or an empty string
     */
    private function errorCssClass(
            int $error_flag
            )
    {
        if ($this->error & $error_flag)
        {
            return "cf-error";
        }
        else
        {
            return "";
        }
    }

    /**
     * @brief Get result message
     * 
     * @return string The HTML of result message
     */
    private function getResultMessage(
            )
    {
        if ($this->was_sent)
        {
            return self::makeFormRow(
                    "<p><strong>Your message was sent.</strong></p>\n");
        }
        elseif ($this->error & self::ERR_SEND_MAIL)
        {
            return self::makeFormRow(
                    "<p class=\"cf-error\"><strong>" .
                    "Sorry, could not send your message. " .
                    "Please try again later.</strong></p>\n");
        }
        else
        {
            return "";
        }
    }

    /**
     * @brief Decorate contact form row with necessary tags
     * 
     * @param string $row Raw contact form row
     * 
     * @return string The contact form row
     */
    private static function makeFormRow(
            string $row
            )
    {
        $row = "<div class=\"cf-row\">\n" . $row;
        $row = $row . "</div>\n";
        return $row;
    }

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
        $params_arr = preg_split("/\s*;\s*/", $params);
        $this->email = $params_arr[0];
        $this->subject = $params_arr[1];
        $this->captcha = new Captcha("contact-form");
        $this->was_sent = FALSE;
        $this->initFormData();
        $this->getInputs();
    }
    
    /**
     * @brief Get body of the module
     * 
     * @return string Markdown output
     */
    public function getOutput(
            )
    {
        $this->tryToSend();

        $cf  = "<form method=\"post\">\n";
        $cf .= $this->getEmailField();
        $cf .= $this->getMessageField();
        $cf .= $this->getSubmitLine();
        $cf .= "</form>\n";

        $cf .= $this->getResultMessage();

        return $cf;
    }
}