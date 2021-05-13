<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Events;

class AlterTableDefinitionStatementsEvent
{
    /** @var string */
    protected $template;

    protected function getTemplate(): string
    {
        return $this->template ?: ($this->template = str_repeat(PHP_EOL, 3) . 'CREATE TABLE %s (' . PHP_EOL . '  %s int(11) DEFAULT \'0\' NOT NULL' . PHP_EOL . ');' . str_repeat(PHP_EOL, 3));
    }

    public function addCountryConfiguration(\TYPO3\CMS\Core\Database\Event\AlterTableDefinitionStatementsEvent $event): void
    {
        foreach ($GLOBALS['TCA'] ?? [] as $table => $config) {
            if ($countryColumns = $GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['countries'] ?? null) {
                foreach ($countryColumns as $field) {
                    $event->addSqlData(sprintf($this->getTemplate(), $table, $field));
                }
            }
        }
    }
}
