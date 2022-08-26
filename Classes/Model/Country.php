<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Model;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use Zeroseven\Countries\Exception\CountryException;
use Zeroseven\Countries\Exception\ValidationException;

/**
 * @method getUid()
 * @method setUid()
 * @method getEnabled()
 * @method setEnabled()
 * @method getTitle()
 * @method setTitle()
 * @method getIsoCode()
 * @method setIsoCode()
 * @method getParameter()
 * @method setParameter()
 * @method getFlag()
 * @method setFlag()
 */
class Country
{
    protected array $properties;

    /** @throws ValidationException | CountryException */
    public function __construct(array $row)
    {
        // Check required fields of a valid country object
        foreach (['uid', 'title', 'iso_code', 'parameter'] as $property) {
            if (empty($row[$property])) {
                throw new CountryException(sprintf('Required property "%s" is missing. Instance of %s cannot be created.', $property, __CLASS__), 1625127363);
            }
        }

        // Store data in object
        foreach ($row as $propertyName => $value) {
            $this->setProperty($propertyName, $value);
        }
    }

    public static function makeInstance(array $row): self
    {
        return GeneralUtility::makeInstance(self::class, $row);
    }

    /** @throws ValidationException */
    public static function castValue($value)
    {
        if (is_string($value) || is_null($value)) {
            return (string)$value;
        }

        if (is_int($value) || MathUtility::canBeInterpretedAsInteger($value)) {
            return (int)$value;
        }

        if ($value instanceof AbstractDomainObject) {
            return $value->getUid();
        }

        throw new ValidationException(sprintf('Value of type "%s" in %s cannot be interpreted as an integer or string.', gettype($value), __CLASS__), 1625127364);
    }

    /** @throws ValidationException */
    protected function setProperty(string $propertyName, $value)
    {
        $propertyName = GeneralUtility::underscoredToLowerCamelCase($propertyName);

        try {
            $castedValue = self::castValue($value);
        } catch (ValidationException $e) {
            throw new ValidationException(sprintf('Value of field "%s" in %s cannot be interpreted as an integer or string.', $propertyName, __CLASS__), 1625127364);
        }

        return $this->properties[$propertyName] = $castedValue;
    }

    public function getProperty(string $propertyName)
    {
        return $this->properties[$propertyName];
    }

    public function toArray(): array
    {
        return $this->properties;
    }

    public function getRow(): array
    {
        $array = [];

        foreach ($this->properties as $propertyName => $value) {
            $array[GeneralUtility::camelCaseToLowerCaseUnderscored($propertyName)] = $value;
        }

        return $array;
    }

    /** @throws CountryException | ValidationException */
    public function __call($name, $arguments)
    {
        if (preg_match('/(get|set|has|is)((?:[A-Z][a-z]+)+)/', $name, $matches)) {
            $action = $matches[1];
            $propertyName = lcfirst($matches[2]);

            // Check if key exists in data array
            if (array_key_exists($propertyName, $this->properties)) {

                // Get/check value
                if ($action === 'get' || $action === 'has' || $action === 'is') {
                    if (count($arguments)) {
                        throw new CountryException(sprintf('The method "%s()" in class %s does not allow any arguments.', $name, __CLASS__), 1625127366);
                    }

                    return $action === 'get' ? $this->getProperty($propertyName) : (bool)$this->getProperty($propertyName);
                }

                // Set value
                if ($action === 'set') {
                    if (count($arguments) !== 1) {
                        throw new CountryException(sprintf('Wrong number of parameters in "%s()" of %s. Please use exactly 1 argument.', $name, __CLASS__), 1625127367);
                    }

                    return $this->setProperty($propertyName, $arguments[0]);
                }
            }
        }

        throw new CountryException(sprintf('Method "%s()" not found in %s.', $name, __CLASS__), 1625127368);
    }
}
