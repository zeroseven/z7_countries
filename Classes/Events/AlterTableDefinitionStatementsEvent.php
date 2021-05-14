<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Events;

class AlterTableDefinitionStatementsEvent
{
    public function addCountryConfiguration(\TYPO3\CMS\Core\Database\Event\AlterTableDefinitionStatementsEvent $event): void
    {
        $singleSelectTemplate = PHP_EOL . 'CREATE TABLE %s (' . PHP_EOL . '  %s int(1) DEFAULT \'0\' NOT NULL' . PHP_EOL . ');' . PHP_EOL;
        $multiSelectTemplate = PHP_EOL . 'CREATE TABLE %s (' . PHP_EOL . '  %s varchar(255) DEFAULT \'\' NOT NULL' . PHP_EOL . ');' . PHP_EOL;

        foreach ($GLOBALS['TCA'] ?? [] as $table => $config) {
            if ($countryColumns = $GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['countries'] ?? null) {
                foreach ($countryColumns as $field) {
                    $template = $GLOBALS['TCA'][$table]['columns'][$field]['config']['renderType'] === 'selectSingle' ? $singleSelectTemplate : $multiSelectTemplate;
                    $event->addSqlData(sprintf($template, $table, $field));
                }
            }
        }
    }
}
