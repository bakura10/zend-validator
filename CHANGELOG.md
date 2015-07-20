# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 3.0.0

### Added

- Zend\Validator architecture has changed to be stateless. This modifies the way validators must be used. 

For instance, here is the code in ZF2:

```
$validator = new MyValidator();
if ($validator->isValid(true)) {
   // Do something
} else {
  $error = $validator->getErrorMessages();
}
```

In ZF3:

```
$validator        = new MyValidator();
$validationResult = $validator->validate($value);

if ($validationResult->isValid()) {
   // Do something
} else {
   $error = $validationResult->getMessages();
}

// You can also access all the parameters that were used to generate the error messages:

$validatedData = $validationResult->getData();
$variables     = $validationResult->getMessagesVariables();
```

- `getErrorMessages` has been renamed to `getMessages` to provide more consistent usage with `getRawMessages` and `getMessagesVariables`.

- Some validators have been moved. For instance, Db validators have been moved to the Zend\Db repository. On the other hand, i18n validators have been moved
to this component. The rule of thumb is as follows: "basic" validators that have no dependencies will belong to this repository. Validators that rely on
other components (like Zend\Db or Zend\Barcode) will be moved to their own repositories. Here is a complete list of those changes:

  * Moved back to `Zend\Validator`: `PhoneNumber`, `Alnum`, `Alpha`, `DateTime`, `IsFloat`, `IsInt`, `PostCode`.
  * Moved to `Zend\Db`: `NoRecordExists`, `RecordExists`
  * Moved to `Zend\Barcode`: all the Barcode validators
  
- `ValidatorChain` has been completely refactored to be more efficient and provide a simpler experience. One important change is that `ValidatorChain` no
longer rely on the `ValidatorPluginManager`. This means that validators must be fetched user-land, and pass directly to a `ValidatorChain`. As a consequence,
you cannot add a validator chain from a name. Here is a simple example that demonstrate the usage:

```php
$validatorChain = new ValidatorChain([
    $validatorWithoutPriority,
    [$validatorWithPriority, 2], 
    function($value) { // With a callable
        return false;
    }
]

// Add a new validator to the chain
$validatorChain->addValidator($anotherValidator, $priority);

// Remove a validator using an instance
$validatorChain->removeValidator($anotherValidator);

// Remove a validator using a class name (this will remove ALL validators with this class name)
$validatorChain->removeValidator(Between::class);

// Run the validator
$result = $validatorChain->validate('value', $context);
```

### Deprecated

- Nothing.

### Removed

- This component no longer has a dependency with `Zend\ServiceManager`. If you need to use it inside the context of a ZF app, please use the
additional module that will provide the plugin manager and its integration within the framework.

- Dependency to `Zend\Translator` has been removed. Because `Zend\Validator` is now stateless, messages can be translated in a much more clean way.

- Options cannot be changed once validator is created. Instead, you must create a new validator. This will allow interesting optimizations to allow to cache
more aggressively validators.

- Any variables you define in the `messageVariables` array now must be defined as a property of the validator, rather than into a generic `options` array.

As a consequence, you no longer need to define an associative array, but rather just an array of key names.

In ZF2:

```php
class Between extends AbstractValidator
{
    const NOT_BETWEEN        = 'notBetween';
    const NOT_BETWEEN_STRICT = 'notBetweenStrict';
    
    protected $messageTemplates = [
        self::NOT_BETWEEN        => "The input is not between '%min%' and '%max%', inclusively",
        self::NOT_BETWEEN_STRICT => "The input is not strictly between '%min%' and '%max%'"
    ];
    
    protected $messageVariables = [
        'min' => ['options' => 'min'],
        'max' => ['options' => 'max'],
    ];
    
    protected $options = [
        'inclusive' => true,  // Whether to do inclusive comparisons, allowing equivalence to min and/or max
        'min'       => 0,
        'max'       => PHP_INT_MAX,
    ];
    
    // ...
}
```

In ZF3:

```php
class Between extends AbstractValidator
{
    const NOT_BETWEEN        = 'notBetween';
    const NOT_BETWEEN_STRICT = 'notBetweenStrict';
        
    protected $messageTemplates = [
        self::NOT_BETWEEN        => "The input is not between '%min%' and '%max%', inclusively",
        self::NOT_BETWEEN_STRICT => "The input is not strictly between '%min%' and '%max%'"
    ];
        
    protected $messageVariables = ['min', 'max'];
    
    protected $inclusive;
    protected $min = 0;
    protected $max = PHP_INT_MAX;
    
    // ...
}
```

### Fixed

- In ZF2, the Validator component did not used the same conventions for options. It used a ZF-1 style (camelCase) instead of ZF2 style (underscore_separated). As
a consequence, this has been changed and all options must now follow the new conventions.

## 2.5.3 - TBD

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.5.2 - 2015-07-16

### Added

- [#8](https://github.com/zendframework/zend-validator/pull/8) adds a "strict"
  configuration option; when enabled (the default), the length of the address is
  checked to ensure it follows the specification.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#8](https://github.com/zendframework/zend-validator/pull/8) fixes bad
  behavior on the part of the `idn_to_utf8()` function, returning the original
  address in the case that the function fails.
- [#11](https://github.com/zendframework/zend-validator/pull/11) fixes
  `ValidatorChain::prependValidator()` so that it works on HHVM.
- [#12](https://github.com/zendframework/zend-validator/pull/12) adds "6772" to
  the Maestro range of the `CreditCard` validator.
