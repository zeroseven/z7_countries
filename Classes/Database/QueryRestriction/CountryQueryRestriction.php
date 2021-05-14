<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Database\QueryRestriction;

use TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression;
use TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\AbstractRestrictionContainer;

class CountryQueryRestriction extends AbstractRestrictionContainer
{
    public function buildExpression(array $queriedTables, ExpressionBuilder $expressionBuilder): CompositeExpression
    {
        $constraints = [];

        foreach ($queriedTables as $tableAlias => $tableName) {
            if (
                ($setup = $GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['countries'] ?? null)
                && ($mode = $tableAlias . '.' . $setup['mode'])
                && ($list = $tableAlias . '.' . $setup['list'])
            ) {
                $constraints[] = $expressionBuilder->orX(

                    // Mode is null, or 0, or ''
                    $expressionBuilder->eq($mode, 0),

                    // Check for whitelisted countries
                    $expressionBuilder->andX(
                        $expressionBuilder->eq($mode, 1),
                        $expressionBuilder->inSet($list, '1') // Todo: Add dynamic value
//                    ),
//
//                    // Check for blacklisted countries
//                    // Todo: "NOT FIND_IN_SET(…
//                    $expressionBuilder->andX(
//                        $expressionBuilder->eq($mode, 2),
//                        $expressionBuilder->notIn($list, '1')
                    )
                );
            }
        }

        return $expressionBuilder->andX(...$constraints);
    }

    public function isEnforced(): bool
    {
        return true;
    }
}
