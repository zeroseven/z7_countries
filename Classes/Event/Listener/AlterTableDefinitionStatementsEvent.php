<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Event\Listener;

use Zeroseven\Countries\Service\TCAService;

class AlterTableDefinitionStatementsEvent
{
    public function addCountryConfiguration(\TYPO3\CMS\Core\Database\Event\AlterTableDefinitionStatementsEvent $event): void
    {
        $singleSelectTemplate = PHP_EOL . 'CREATE TABLE %s (' . PHP_EOL . '  %s int(1) DEFAULT \'0\' NOT NULL' . PHP_EOL . ');' . PHP_EOL;
        $multiSelectTemplate = PHP_EOL . 'CREATE TABLE %s (' . PHP_EOL . '  %s varchar(255) DEFAULT \'\' NOT NULL' . PHP_EOL . ');' . PHP_EOL;

        foreach ($GLOBALS['TCA'] ?? [] as $table => $config) {
            if (!empty($fields = TCAService::getEnableColumns($table))) {
                foreach ($fields as $field) {
                    $template = $GLOBALS['TCA'][$table]['columns'][$field]['config']['renderType'] === 'selectSingle' ? $singleSelectTemplate : $multiSelectTemplate;
                    $event->addSqlData(sprintf($template, $table, $field));
                }
            }
        }
    }
}
