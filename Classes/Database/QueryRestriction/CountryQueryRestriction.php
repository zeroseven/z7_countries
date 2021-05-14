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
                $constraints = [
                    $expressionBuilder->orX(

                        // Mode is null, or 0, or ''
                        $expressionBuilder->isNull($mode),
                        $expressionBuilder->eq($mode, $expressionBuilder->literal('')),
                        $expressionBuilder->eq($mode, $expressionBuilder->literal('0')),

                        // Check for whitelisted countries
                        $expressionBuilder->andX(
                            $expressionBuilder->eq($mode, $expressionBuilder->literal('1')),
                            $expressionBuilder->in($list, 1)
                        ),

                        // Check for blacklisted countries
                        $expressionBuilder->andX(
                            $expressionBuilder->eq($mode, $expressionBuilder->literal('2')),
                            $expressionBuilder->notIn($list, 1)
                        )
                    )
                ];
            }
        }

        return $expressionBuilder->andX(...$constraints);
    }

    public function isEnforced(): bool
    {
        return true;
    }
}
