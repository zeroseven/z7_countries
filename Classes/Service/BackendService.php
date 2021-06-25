<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Service;

class BackendService
{
    public static function enableConfiguration(string $table, string $position = null, string $typeList = null): ?array
    {
        if (TCAService::isDisallowedTable($table)) {
            throw new \Exception('The table "' . $table . '" is not supported for country restrictions. 🤔', 1621109882);
        }

        TCAService::addEnableColumns($table);
        TCAService::addFields($table);
        TCAService::addPalette($table, $position, $typeList);

        return TCAService::getEnableColumns($table);
    }
}
