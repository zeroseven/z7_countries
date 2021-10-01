<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Model;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use Zeroseven\Countries\Exception\CountryException;
use Zeroseven\Countries\Exception\ValidationException;

class Country
{
    protected array $data;

    public function __construct(array $row)
    {

        // Check required fields of a valid country object
        foreach (['uid', 'title', 'iso_code', 'parameter'] as $property) {
            if (empty($row[$property])) {
                throw new CountryException(sprintf('Required property "%s" is missing. Instance of %s cannot be created.', $property, __CLASS__), 1625127363);
            }
        }

        // Store data in object
        foreach ($row as $key => $value) {
            $this->setValue($key, $value);
        }
    }

    public static function makeInstance(array $row): self
    {
        return GeneralUtility::makeInstance(self::class, $row);
    }

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

    protected function setValue(string $key, $value)
    {
        try {
            $castedValue = self::castValue($value);
        } catch (ValidationException $e) {
            throw new ValidationException(sprintf('Value of field "%s" in %s cannot be interpreted as an integer or string.', $key, __CLASS__), 1625127364);
        }

        return $this->data[$key] = $castedValue;
    }

    public function getValue(string $key)
    {
        return $this->data[$key];
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function __call($name, $arguments)
    {
        if (preg_match('/(get|set|has|is)((?:[A-Z][a-z]+)+)/', $name, $matches)) {
            $action = $matches[1];
            $key = GeneralUtility::camelCaseToLowerCaseUnderscored($matches[2]);

            // Check if key exists in data array
            if (array_key_exists($key, $this->data)) {

                // Get/check value
                if ($action === 'get' || $action === 'has' || $action === 'is') {
                    if (count($arguments)) {
                        throw new Exception(sprintf('The method "%s()" in class %s does not allow any arguments.', $name, __CLASS__), 1625127366);
                    }

                    return $action === 'get' ? $this->getValue($key) : (bool)$this->getValue($key);
                }

                // Set value
                if ($action === 'set') {
                    if (count($arguments) !== 1) {
                        throw new Exception(sprintf('Wrong number of parameters in "%s()" of %s. Please use exactly 1 argument.', $name, __CLASS__), 1625127367);
                    }

                    return $this->setValue($key, $arguments[0]);
                }
            }
        }

        throw new Exception(sprintf('Method "%s()" not found in %s.', $name, __CLASS__), 1625127368);
    }
}
