<?php

/**
 * @brief Base class for all modules
 */
abstract class Module
{
    /**
     * @brief The suffix of a module class name
     *
     * @var string
     */
    private const MODULE_CLASS_NAME_SUFFIX = "Module";

    /*========================================================================
                                 Private Methods                              
      ========================================================================*/

    /*========================================================================
                                 Public Methods                              
      ========================================================================*/
    
    /**
     * @brief Tries to load the module and create new instance of the module
     * 
     * @param string $module_class_name The basis of the module name
     * @param string $module_params The parameter to construct the module
     * 
     * @return Module New module object
     */
    public static function makeModule(
	        string $module_class_name,
            string $module_params
	    )
    {
        $full_class_name = $module_class_name .
                self::MODULE_CLASS_NAME_SUFFIX;
	    if (!class_exists($full_class_name))
	    {
            require_once dirname(__FILE__) . '/modules/' . $module_class_name .
                    '.php';
	    }
        
        return new $full_class_name($module_params);
    }

    /**
     * @brief When extended this method should return body of the module
     * 
     * @return string Markdown output
     */
    public abstract function getOutput(
            );
}
