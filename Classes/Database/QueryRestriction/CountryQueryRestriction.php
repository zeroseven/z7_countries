<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Database\QueryRestriction;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression;
use TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\AbstractRestrictionContainer;
use TYPO3\CMS\Core\Database\Query\Restriction\EnforceableQueryRestrictionInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Zeroseven\Countries\Model\Country;
use Zeroseven\Countries\Service\TCAService;

class CountryQueryRestriction extends AbstractRestrictionContainer implements EnforceableQueryRestrictionInterface
{
    protected function isFrontend(): bool
    {
        return GeneralUtility::makeInstance(Context::class)->hasAspect('country.request');
    }

    public static function getExpression(ExpressionBuilder $expressionBuilder, string $tableName, ?Country $country = null, ?string $tableAlias = null)
    {
        $queriedTable = $tableAlias ?: $tableName;
        $mode = $queriedTable . '.' . TCAService::getModeColumn($tableName);
        $list = $queriedTable . '.' . TCAService::getListColumn($tableName);

        return $country === null ? $expressionBuilder->in($mode, ['0', '2']) : $expressionBuilder->or(
            $expressionBuilder->eq($mode, 0),
            $expressionBuilder->and(
                $expressionBuilder->in($mode, ['1', '2']),
                $expressionBuilder->inSet($list, (string)$country->getUid())
            )
        );
    }

    public function buildExpression(array $queriedTables, ExpressionBuilder $expressionBuilder): CompositeExpression
    {
        $constraints = [];

        if ($this->isFrontend()) {
            $country = GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('country.request', 'country');

            foreach ($queriedTables as $tableAlias => $tableName) {
                if (TCAService::hasCountryConfiguration($tableName)) {
                    $constraints[] = self::getExpression($expressionBuilder, $tableName, $country, $tableAlias);
                }
            }
        }

        return $expressionBuilder->and(...$constraints);
    }

    public function isEnforced(): bool
    {
        return true;
    }
}
