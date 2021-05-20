<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Database\QueryRestriction;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression;
use TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\AbstractRestrictionContainer;
use TYPO3\CMS\Core\Database\Query\Restriction\EnforceableQueryRestrictionInterface;
use TYPO3\CMS\Core\Http\ApplicationType;
use Zeroseven\Countries\Service\CountryService;
use Zeroseven\Countries\Service\TCAService;

class CountryQueryRestriction extends AbstractRestrictionContainer implements EnforceableQueryRestrictionInterface
{
    protected function isFrontend(): bool
    {
        return isset($GLOBALS['TYPO3_REQUEST']) && $GLOBALS['TYPO3_REQUEST'] instanceof ServerRequestInterface && ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend();
    }

    protected function getCountry(): ?array
    {
        return $GLOBALS['TYPO3_CONF_VARS']['USER']['z7_countries']['cache']['restrictionCountry'] ?? ($GLOBALS['TYPO3_CONF_VARS']['USER']['z7_countries']['cache']['restrictionCountry'] = CountryService::getCountryByUri());
    }

    public static function getExpression(ExpressionBuilder $expressionBuilder, string $mode, string $list, array $country = null)
    {
        return empty($country) ? $expressionBuilder->in($mode, ['0', '2']) : $expressionBuilder->orX(
            $expressionBuilder->eq($mode, 0),
            $expressionBuilder->andX(
                $expressionBuilder->in($mode, ['1', '2']),
                $expressionBuilder->inSet($list, (string)$country['uid'])
            )
        );
    }

    public function buildExpression(array $queriedTables, ExpressionBuilder $expressionBuilder): CompositeExpression
    {
        $constraints = [];

        if ($this->isFrontend()) {
            $country = $this->getCountry();

            foreach ($queriedTables as $tableAlias => $tableName) {
                if ($fields = TCAService::getEnableColumn($tableAlias)) {
                    $constraints[] = self::getExpression($expressionBuilder, $fields['mode'], $fields['list'], $country);
                }
            }
        }

        return $expressionBuilder->andX(...$constraints);
    }

    public function isEnforced(): bool
    {
        return true;
    }
}
