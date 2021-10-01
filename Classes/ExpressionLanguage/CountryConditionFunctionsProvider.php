<?php

declare(strict_types=1);

namespace Zeroseven\Countries\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Zeroseven\Countries\Model\Country;

class CountryConditionFunctionsProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions(): array
    {
        return [
            $this->getCountryFunction()
        ];
    }

    protected function getCountryFunction(): ExpressionFunction
    {
        return new ExpressionFunction('country', function () {
            // Not implemented, we only use the evaluator
        }, function (...$args) {
            $country = $args[0]['country'];
            $property = (string)$args[1];
            $value = $args[2];
            $respectCaseAndType = (bool)$args[3];

            if(empty($country) || empty($property) || empty($value)) {
                return null;
            }

            $compare = array_map(static function($v) use ($respectCaseAndType) {
                return $respectCaseAndType ? $v : strtolower((string)$v);
            }, [
                $country->getValue($property),
                Country::castValue($value)
            ]);

            return $compare[0] === $compare[1];
        });
    }
}
