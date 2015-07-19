<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Validator\Result;

/**
 * Simple class that holds data and error messages
 */
class ValidationResult implements ValidationResultInterface
{
    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var mixed|null
     */
    protected $context;

    /**
     * @var string[]
     */
    protected $rawMessages = [];

    /**
     * @var string[]
     */
    protected $messagesVariables = [];

    /**
     * Constructor
     *
     * @param mixed        $data
     * @param mixed|null   $context
     * @param string|array $rawMessages
     * @param array        $messagesVariables
     */
    public function __construct($data, $context = null, $rawMessages = [], array $messagesVariables = [])
    {
        $this->data              = $data;
        $this->context           = $context;
        $this->rawMessages       = (array) $rawMessages;
        $this->messagesVariables = $messagesVariables;
    }

    /**
     * {@inheritDoc}
     */
    public function isValid()
    {
        return empty($this->rawMessages);
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * {@inheritDoc}
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * {@inheritDoc}
     */
    public function getRawMessages()
    {
        return $this->rawMessages;
    }

    /**
     * {@inheritDoc}
     */
    public function getMessages()
    {
        // If no message variables then this means no interpolation is needed, so we can return
        // raw error messages immediately
        if (empty($this->messagesVariables)) {
            return $this->rawMessages;
        }

        // We use simple regex here to inject variables into the error messages. Each variable
        // is surrounded by percent sign (eg.: %min%)
        $keys          = array_keys($this->messagesVariables);
        $values        = array_values($this->messagesVariables);
        $errorMessages = [];

        foreach ($this->rawMessages as $rawMessage) {
            $errorMessages[] = str_replace($keys, $values, $rawMessage);
        }

        return $errorMessages;
    }

    /**
     * {@inheritDoc}
     */
    public function getMessagesVariables()
    {
        return $this->messagesVariables;
    }

    /**
     * Serialize the object
     *
     * @return string
     */
    public function serialize()
    {
        return serialize([
            'data'               => $this->getData(),
            'context'            => $this->getContext(),
            'raw_messages'       => $this->getRawMessages(),
            'messages_variables' => $this->getMessagesVariables()
        ]);
    }

    /**
     * Unserialize the object
     *
     * @param  string $serialized
     * @return void
     */
    public function unserialize($serialized)
    {
        $object = unserialize($serialized);

        $this->data              = $object['data'];
        $this->context           = $object['context'];
        $this->rawMessages       = $object['raw_messages'];
        $this->messagesVariables = $object['messages_variables'];
    }

    /**
     * Return error messages that can be serialized by json_encode
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->getMessages();
    }

    /**
     * Get all the error messages as a string
     *
     * @return string
     */
    public function __toString()
    {
        return implode(', ', $this->getMessages());
    }
}