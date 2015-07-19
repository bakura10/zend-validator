<?php

namespace ZendTest\Validator\Result;

use Zend\Validator\Result\ValidationResult;

class ValidationResultTest extends \PHPUnit_Framework_TestCase
{
    public function testValidationResultCanBeCreatedWithoutError()
    {
        $validationResult = new ValidationResult('data');
        $this->assertEquals('data', $validationResult->getData());
        $this->assertEmpty($validationResult->getMessages());
        $this->assertEmpty($validationResult->getRawMessages());
        $this->assertEmpty($validationResult->getMessagesVariables());
        $this->assertTrue($validationResult->isValid());
    }

    public function testStringErrorIsConvertedToArray()
    {
        $validationResult = new ValidationResult('data', 'An error message');
        $this->assertInternalType('array', $validationResult->getRawMessages());
        $this->assertCount(1, $validationResult->getRawMessages());
        $this->assertCount(1, $validationResult->getMessages());
    }

    public function testCanMerge()
    {
        $validationResult1 = new ValidationResult('data', 'First error', ['key1' => 'var1']);
        $validationResult2 = new ValidationResult('data', 'Second error', ['key2' => 'var2']);

        $validationResult1->merge($validationResult2);

        $this->assertEquals('data', $validationResult1->getData());
        $this->assertCount(2, $validationResult1->getRawMessages());
        $this->assertCount(2, $validationResult1->getMessagesVariables());
        $this->assertFalse($validationResult1->isValid());
    }

    public function testCanGetErrorMessagesWithoutInterpolation()
    {
        $validationResult = new ValidationResult('data', 'An error message');
        $expected         = ['An error message'];

        $this->assertEquals($expected, $validationResult->getMessages());
        $this->assertEquals($expected, $validationResult->getRawMessages());
    }

    public function testCanInterpolate()
    {
        $validationResult    = new ValidationResult('data', 'Length must be %min%', ['%min%' => 4]);
        $expectedRaw         = ['Length must be %min%'];
        $expectedInterpolate = ['Length must be 4'];

        $this->assertEquals(['%min%' => 4], $validationResult->getMessagesVariables());
        $this->assertEquals($expectedInterpolate, $validationResult->getMessages());
        $this->assertEquals($expectedRaw, $validationResult->getRawMessages());
    }

    public function testCanInterpolateComplex()
    {
        $validationResult = new ValidationResult(
            'data',
            array('Length must be %min%', 'Does not validate %pattern%'),
            ['%min%' => 4, '%pattern%' => 'abc']
        );

        $expectedRaw         = ['Length must be %min%', 'Does not validate %pattern%'];
        $expectedInterpolate = ['Length must be 4', 'Does not validate abc'];

        $this->assertEquals(['%min%' => 4, '%pattern%' => 'abc'], $validationResult->getMessagesVariables());
        $this->assertEquals($expectedInterpolate, $validationResult->getMessages());
        $this->assertEquals($expectedRaw, $validationResult->getRawMessages());
    }

    public function testCanSerialize()
    {
        $validationResult = new ValidationResult('data', 'Length must be %min%', ['%min%' => 4]);

        $serialize   = serialize($validationResult);
        $unserialize = unserialize($serialize);

        $this->assertFalse($unserialize->isValid());
        $this->assertEquals('data', $unserialize->getData());
        $this->assertEquals(['%min%' => 4], $unserialize->getMessagesVariables());
        $this->assertEquals(['Length must be %min%'], $unserialize->getRawMessages());
        $this->assertEquals(['Length must be 4'], $unserialize->getMessages());
    }

    public function testCanJsonSerialize()
    {
        $validationResult = new ValidationResult('data', 'Length must be %min%', ['%min%' => 4]);
        $json             = json_encode($validationResult);

        $this->assertEquals('["Length must be 4"]', $json);
    }
}