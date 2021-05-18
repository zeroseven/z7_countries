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

class CountryQueryRestriction extends AbstractRestrictionContainer implements EnforceableQueryRestrictionInterface
{
    protected function isFrontend(): bool
    {
        return isset($GLOBALS['TYPO3_REQUEST']) && $GLOBALS['TYPO3_REQUEST'] instanceof ServerRequestInterface && ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend();
    }

    public function buildExpression(array $queriedTables, ExpressionBuilder $expressionBuilder): CompositeExpression
    {
        $constraints = [];

        if ($this->isFrontend() && !empty($country = CountryService::getCountryByUri())) {
            foreach ($queriedTables as $tableAlias => $tableName) {
                if (
                    ($setup = $GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['countries'] ?? null)
                    && ($mode = $tableAlias . '.' . $setup['mode'])
                    && ($list = $tableAlias . '.' . $setup['list'])
                ) {
                    $constraints[] = $expressionBuilder->orX(
                        $expressionBuilder->eq($mode, 0),
                        $expressionBuilder->andX(
                            $expressionBuilder->eq($mode, 1),
                            $expressionBuilder->inSet($list, (string)$country['uid'])
                        )
                    );
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
