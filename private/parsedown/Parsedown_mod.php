<?php

require_once dirname(__FILE__) . "/Parsedown.php";


/**
 * @brief Modified Parsedown class
 * 
 */
class ParsedownMod extends Parsedown
{
    /**
     * @brief Callback used for processing URLs
     * 
     * @var callable
     */
    protected $linkCallback;

    /**
     * @brief Callback used for handling modules
     * 
     * @var callable
     */
    protected $moduleHandler;

    /**
     * @brief Array code blocks to be handled in a special way
     * 
     * @var array
     */
    protected $specialCodeBlocks = array(
        'math' => 'KatexMath'
    );

    /**
     * @brief Default link processor
     * 
     * @param array $element Parsedown element array
     * 
     * @return array Processed Parsedown element array
     */
    private static function defaultLinkCallback(
            array $element
            )
    {
        return $element;
    }

    /*========================================================================
                                 Protected Methods                              
      ========================================================================*/


    protected function blockFencedCode($Line)
    {
        $block = parent::blockFencedCode($Line);
        if (isset($block['element']['text']['attributes']['class']))
        {
            $css_class = $block['element']['text']['attributes']['class'];
            $code_type = substr($css_class, strpos($css_class, '-') + 1);
            if (!array_key_exists($code_type, $this->specialCodeBlocks))
            {
                return $block;
            }
        }
    }

    #
    # Comment

    protected function blockComment($Line)
    {
        if ($this->markupEscaped or $this->safeMode)
        {
            return;
        }

        if (isset($Line['text'][3]) and $Line['text'][3] === '-' and $Line['text'][2] === '-' and $Line['text'][1] === '!')
        {
            $Block = array('markup' => '',);

            if (preg_match('/-->$/', $Line['text']))
            {
                $Block['closed'] = true;
            }

            return $Block;
        }
    }

    protected function blockCommentContinue($Line, array $Block)
    {
        if (isset($Block['closed']))
        {
            return;
        }

        if (preg_match('/-->$/', $Line['text']))
        {
            $Block['closed'] = true;
        }

        return $Block;
    }

    protected function inlineLink($Excerpt)
    {
        $original =  parent::inlineLink($Excerpt);
        if (!isset($original))
        {
            return;
        }
        else
        {
            // Preprocess links
            $original['element'] = call_user_func(
                    $this->linkCallback,
                    $original['element']);
            return $original;
        }
    }

    protected function inlineMath($excerpt)
    {
        if (preg_match('/^\$`([^\$]*)`\$/', $excerpt['text'], $matches))
        {
            $equation = $matches[1];

            return array(
                'extent' => strlen($matches[0]), 
                'element' => array(
                    'name' => 'span',
                    'text' => $equation,
                    'attributes' => array(
                        'title' => $equation,
                        'class' => 'equation'
                    )
                )
            );
        }
    }

    protected function inlineModule($excerpt)
    {
        if (preg_match('/\[\[\s*([a-z0-9][a-z0-9\-]*)\s*:\s*(.*[^\\\\])\]\]/',
                $excerpt['text'], $matches))
        {
            $module = $matches[1];
            $arguments = stripcslashes($matches[2]);

            return array(
                'extent' => strlen($matches[0]),
                'markup' => call_user_func($this->moduleHandler,
                    $module, $arguments)
            );
        }
        else
        {
            return;
        }
    }

    #
    # Special fenced code

    protected function blockSpecialFencedCode($Line)
    {
        if (preg_match('/^['.$Line['text'][0].']{3,}[ ]*([^`]+)?[ ]*$/', $Line['text'], $matches))
        {
            if (!array_key_exists($matches[1], $this->specialCodeBlocks))
            {
                return;
            }

            $Block = array(
                'char' => $Line['text'][0],
                'codeType' => $matches[1],
                'code' => ''
            );

            return $Block;
        }
    }

    protected function blockSpecialFencedCodeContinue($Line, $Block)
    {
        if (isset($Block['complete']))
        {
            return;
        }

        if (isset($Block['interrupted']))
        {
            $Block['code'] .= "\n";

            unset($Block['interrupted']);
        }

        if (preg_match('/^'.$Block['char'].'{3,}[ ]*$/', $Line['text']))
        {
            $Block['code'] = substr($Block['code'], 1);

            $Block['complete'] = true;

            return $Block;
        }

        $Block['code'] .= "\n".$Line['body'];

        return $Block;
    }

    protected function blockSpecialFencedCodeComplete($Block)
    {
        $codeType = $Block['codeType'];

        $Block = $this->{'specialCode' .
            $this->specialCodeBlocks[$codeType]}($Block);

        unset($Block['code']);
        unset($Block['codetype']);

        return $Block;
    }

    protected function specialCodeKatexMath($Block)
    {
        // Remove new lines to form title of the equation
        $title = preg_replace('/\\\s*\n/', '', $Block['code']);
        $Block['element'] = array(
            'name' => 'p',
            'text' => $Block['code'],
            'attributes' => array(
                'class' => 'equation',
                'title' => $title
            )
        );
        return $Block;
    }


    /*========================================================================
                                 Public Methods                              
      ========================================================================*/
    public function __construct(
            )
    {
        $this->linkCallback = array(__CLASS__, 'defaultLinkCallback');
        $this->BlockTypes['`'][] = 'SpecialFencedCode';
        $this->InlineTypes['$'][] = 'Math';
        $this->inlineMarkerList .= '$';
        $this->specialCharacters[] = '$';
        array_unshift($this->InlineTypes['['], "Module");
    }

    /**
     * @brief Configure link processor callback
     * 
     * @param callable $cb
     */
    public function setLinkCallback(
            callable $cb
            )
    {
        $this->linkCallback = $cb;
    }

    /**
     * @brief Register given module handler
     * 
     * @param callable $cb Handler callback for handling modules
     */
    public function setModuleHandlerCallback(
        callable $cb
    )
    {
        $this->moduleHandler = $cb;
    }

}
