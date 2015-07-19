<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Validator;

use Zend\Validator\Exception;
use Zend\Validator\Result\ValidationResult;

/**
 * Provides basic features shared for all validators
 *
 * If you create your own validators, you should always extend this class, and make sure that your constructor calls
 * the parent constructor to properly set the message templates
 */
abstract class AbstractValidator implements ValidatorInterface
{
    /**
     * An array of key value that maps a constant to an error message. This message can contains variables using
     * the interpolation syntax: %variable%
     *
     * @var array
     */
    protected $messageTemplates = [];

    /**
     * @var array
     */
    protected $messageVariables = [];

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->messageTemplates = isset($options['message_templates']) ? $options['message_templates'] : $this->messageTemplates;
        $this->messageVariables = isset($options['message_variables']) ? $options['message_variables'] : $this->messageVariables;
    }

    /**
     * Get the message templates
     *
     * @return array
     */
    public function getMessageTemplates()
    {
        return $this->messageTemplates;
    }

    /**
     * Get the message variables
     */
    public function getMessageVariables()
    {
        return $this->messageVariables;
    }

    /**
     * {@inheritdoc}
     */
    function __invoke($data, $context = null)
    {
        return $this->validate($data, $context);
    }

    /**
     * Build a validation result based on the error key
     *
     * @param  mixed        $data The data that failed validation
     * @param  string|array $keys The keys of the error message template
     * @throws Exception\InvalidArgumentException
     * @return Result\ValidationResultInterface
     */
    protected function buildValidationResult($data, $keys)
    {
        // We cast to array to keep the same logic, as some validator may throw
        // two error messages
        $keys          = (array) $keys;
        $errorMessages = [];

        foreach ($keys as $key) {
            if (!isset($this->messageTemplates[$key])) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'No error message template was found for key "%s" in %s',
                    $key,
                    __CLASS__
                ));
            }

            $errorMessages[] = $this->messageTemplates[$key];
        }

        $variables = [];

        foreach ($this->messageVariables as $messageVariable) {
            $property                = str_replace('_', '', $messageVariable);
            $variableKey             = '%' . $messageVariable . '%';
            $variables[$variableKey] = $this->$property;
        }

        return new ValidationResult($data, $errorMessages, $variables);
    }
}