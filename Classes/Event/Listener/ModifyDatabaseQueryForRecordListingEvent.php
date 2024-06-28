<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Event\Listener;


use TYPO3\CMS\Backend\View\Event\ModifyDatabaseQueryForRecordListingEvent as Event;
use Zeroseven\Countries\Database\QueryRestriction\CountryQueryRestriction;
use Zeroseven\Countries\Service\CountryService;
use Zeroseven\Countries\Service\TCAService;

class ModifyDatabaseQueryForRecordListingEvent
{
    public const PARAMETER = 'tx_z7country';

    protected function getCountryParameter(): int
    {
        return (int)($_GET[self::PARAMETER] ?? 0);
    }

    public function __invoke(Event $event)
    {
        if (
            ($countryId = $this->getCountryParameter())
            && TCAService::hasCountryConfiguration($event->getTable())
            && ($country = CountryService::getCountryByUid($countryId))
            && ($queryBuilder = $event->getQueryBuilder())
        ) {
            $expression = CountryQueryRestriction::getExpression($event->getQueryBuilder()->expr(), $event->getTable(), $country);
            $queryBuilder->andWhere($expression);
        }
    }
}
