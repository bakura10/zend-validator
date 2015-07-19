<?php

namespace ZendTest\Validator\Result;

use Zend\Validator\Result\ValidationResult;

class ValidationResultTest extends \PHPUnit_Framework_TestCase
{
    public function testValidationResultCanBeCreatedWithoutError()
    {
        $validationResult = new ValidationResult();
        $this->assertEmpty($validationResult->getMessages());
        $this->assertEmpty($validationResult->getRawMessages());
        $this->assertEmpty($validationResult->getMessagesVariables());
        $this->assertTrue($validationResult->isValid());
    }

    public function testStringErrorIsConvertedToArray()
    {
        $validationResult = new ValidationResult('An error message');
        $this->assertInternalType('array', $validationResult->getRawMessages());
        $this->assertCount(1, $validationResult->getRawMessages());
        $this->assertCount(1, $validationResult->getMessages());
    }

    public function testCanGetErrorMessagesWithoutInterpolation()
    {
        $validationResult = new ValidationResult('An error message');
        $expected         = ['An error message'];

        $this->assertEquals($expected, $validationResult->getMessages());
        $this->assertEquals($expected, $validationResult->getRawMessages());
    }

    public function testCanInterpolate()
    {
        $validationResult    = new ValidationResult('Length must be %min%', ['%min%' => 4]);
        $expectedRaw         = ['Length must be %min%'];
        $expectedInterpolate = ['Length must be 4'];

        $this->assertEquals(['%min%' => 4], $validationResult->getMessagesVariables());
        $this->assertEquals($expectedInterpolate, $validationResult->getMessages());
        $this->assertEquals($expectedRaw, $validationResult->getRawMessages());
    }

    public function testCanInterpolateComplex()
    {
        $validationResult = new ValidationResult(
            ['Length must be %min%', 'Does not validate %pattern%'],
            ['%min%' => 4, '%pattern%' => 'abc']
        );

        $expectedRaw         = ['Length must be %min%', 'Does not validate %pattern%'];
        $expectedInterpolate = ['Length must be 4', 'Does not validate abc'];

        $this->assertEquals(['%min%' => 4, '%pattern%' => 'abc'], $validationResult->getMessagesVariables());
        $this->assertEquals($expectedInterpolate, $validationResult->getMessages());
        $this->assertEquals($expectedRaw, $validationResult->getRawMessages());
    }

    public function testCanConvertToString()
    {
        $validationResult = new ValidationResult(['Message 1', 'Message 2']);
        $this->assertEquals('Message 1, Message 2', (string) $validationResult);
    }

    public function testCanSerialize()
    {
        $validationResult = new ValidationResult('Length must be %min%', ['%min%' => 4]);

        $serialize   = serialize($validationResult);
        $unserialize = unserialize($serialize);

        $this->assertFalse($unserialize->isValid());
        $this->assertEquals(['%min%' => 4], $unserialize->getMessagesVariables());
        $this->assertEquals(['Length must be %min%'], $unserialize->getRawMessages());
        $this->assertEquals(['Length must be 4'], $unserialize->getMessages());
    }

    public function testCanJsonSerialize()
    {
        $validationResult = new ValidationResult('Length must be %min%', ['%min%' => 4]);
        $json             = json_encode($validationResult);

        $this->assertEquals('["Length must be 4"]', $json);
    }
}