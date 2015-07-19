<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Validator;

use Countable;
use Zend\Validator\Result\ValidationResult;

/**
 * Validator chain
 */
final class ValidatorChain implements ValidatorInterface, Countable
{
    const DEFAULT_PRIORITY = 1;

    /**
     * @var array|\Zend\Validator\ValidatorInterface[]
     */
    private $validators = [];

    /**
     * @var bool
     */
    private $isSorted = true;

    /**
     * Construct a new validator chain with a set of validators
     *
     * You can either specify a validator/callable, or an array with an optional priority:
     *
     *      $chain = new ValidatorChain([
     *          $validatorInstance,
     *          [$validatorInstance, '2'] // with a priority of 2
     *      ]);
     *
     * @param array $validators
     */
    public function __construct(array $validators = [])
    {
        foreach ($validators as $validatorOrArray) {
            if (is_array($validatorOrArray)) {
                $this->addValidator($validatorOrArray[0], $validatorOrArray[1]);
            } else {
                $this->addValidator($validatorOrArray);
            }
        }
    }

    /**
     * Add a new validator, with an optional priority
     *
     * @param callable $validator
     * @param int      $priority
     */
    public function addValidator(callable $validator, $priority = self::DEFAULT_PRIORITY)
    {
        $this->validators = [$priority, $validator];
        $this->isSorted   = false;
    }

    /**
     * Remove a validator from the chain
     *
     * You can either pass an instance or a class name that allow to remove it. Please note that this method needs to iterate
     * through all validators and can be quite expensive
     *
     * @param  callable|string $validator
     * @return bool True if properly removed, false otherwise
     */
    public function removeValidator($validator)
    {
        if (is_string($validator)) {
            return $this->removeValidatorByName($validator);
        } else {
            return $this->removeValidatorByInstance($validator);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function validate($data, $context = null)
    {
        if (!$this->isSorted) {
            rsort($this->validators);
            $this->isSorted = true;
        }

        $errorMessages = $messageVariables = [];

        foreach ($this->validators as $validator) {
            $validationResult = $validator->validate($data, $context);

            if (!$validationResult->isValid()) {
                $errorMessages    = array_merge($errorMessages, $validationResult->getRawMessages());
                $messageVariables = array_merge($messageVariables, $validationResult->getMessagesVariables());
            }
        }

        return new ValidationResult($data, $context, $errorMessages, $messageVariables);
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke($data, $context = null)
    {
        return $this->validate($data, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return count($this->validators);
    }

    /**
     * Remove a validator by its class name
     *
     * @param  string $validatorClass
     * @return bool
     */
    private function removeValidatorByName($validatorClass)
    {
        $returnValue = false;

        foreach ($this->validators as $key => $validator) {
            if ($validator instanceof $validatorClass) {
                unset($this->validators[$key]);
                $returnValue = true;
            }
        }

        return $returnValue;
    }

    /**
     * Remove a validator by doing a comparison against the instance
     *
     * @param  callable $validatorInstance
     * @return bool
     */
    private function removeValidatorByInstance(callable $validatorInstance)
    {
        $returnValue = false;

        foreach ($this->validators as $key => $validator) {
            if ($validator === $validatorInstance) {
                unset($this->validators[$key]);
                $returnValue = true;
            }
        }

        return $returnValue;
    }
}