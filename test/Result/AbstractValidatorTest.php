<?php

namespace ZendTest\Validator\Result;

use Zend\Validator\AbstractValidator;

class AbstractValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testCanSetMessageTemplatesAndVariables()
    {
        $messageTemplates = ['foo' => 'bar'];
        $messageVariables = ['min'];

        /** @var AbstractValidator $validator */
        $validator = $this->getMockForAbstractClass(AbstractValidator::class, [
            [
                'message_templates' => $messageTemplates,
                'message_variables' => $messageVariables
            ]
        ]);

        $this->assertEquals($messageTemplates, $validator->getMessageTemplates());
        $this->assertEquals($messageVariables, $validator->getMessageVariables());
    }

    public function testInvokeProxyToValidate()
    {
        /** @var AbstractValidator $validator */
        $validator = $this->getMockForAbstractClass(AbstractValidator::class);

        $validator->expects($this->once())->method('validate')->with(['bar'], ['context']);
        $validator(['bar'], ['context']);
    }
}