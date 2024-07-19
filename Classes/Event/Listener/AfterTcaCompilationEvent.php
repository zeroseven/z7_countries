<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Event\Listener;

use TYPO3\CMS\Core\Configuration\Event\AfterTcaCompilationEvent as Event;
use Zeroseven\Countries\Exception\BackendException as Exception;
use Zeroseven\Countries\Service\RegistrationService;
use Zeroseven\Countries\Service\TCAService;

class AfterTcaCompilationEvent
{
    private array $TCA = [];

    protected function getPalette(): string
    {
        return '--palette--;LLL:EXT:z7_countries/Resources/Private/Language/locallang_db.xlf:*.palette.' . TCAService::PALETTE_NAME . ';' . TCAService::PALETTE_NAME;
    }

    protected function isDisallowedTable(string $table): bool
    {
        return in_array($table, $GLOBALS['TYPO3_CONF_VARS']['USER']['z7_countries']['disallowedTables'] ?? [], true);
    }

    /** @throws Exception */
    protected function addEnableColumns(string $table): void
    {
        if ($this->isDisallowedTable($table)) {
            throw new Exception('The table "' . $table . '" is not supported for country restrictions.', 1625165946);
        }

        if (isset($this->TCA[$table]) && !isset($this->TCA[$table]['ctrl']['enablecolumns']['countries'])) {
            $this->TCA[$table]['ctrl']['enablecolumns'][TCAService::FIELD_KEY_MODE] = TCAService::FIELD_NAME_MODE;
            $this->TCA[$table]['ctrl']['enablecolumns'][TCAService::FIELD_KEY_LIST] = TCAService::FIELD_NAME_LIST;
        }
    }

    protected function addFields(string $table): void
    {
        if (isset($this->TCA[$table]) && !isset($this->TCA[$table]['columns'][TCAService::FIELD_NAME_MODE], $this->TCA[$table]['columns'][TCAService::FIELD_NAME_LIST])) {
            $this->TCA[$table]['columns'][TCAService::FIELD_NAME_MODE] = [
                'label' => 'LLL:EXT:z7_countries/Resources/Private/Language/locallang_db.xlf:*.' . TCAService::FIELD_NAME_MODE,
                'exclude' => true,
                'l10n_mode' => 'exclude',
                'onChange' => 'reload',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => [
                        [
                            'label' => 'LLL:EXT:z7_countries/Resources/Private/Language/locallang_db.xlf:*.' . TCAService::FIELD_NAME_MODE . '.0',
                            'value' => '0'
                        ],
                        [
                            'label' => 'LLL:EXT:z7_countries/Resources/Private/Language/locallang_db.xlf:*.' . TCAService::FIELD_NAME_MODE . '.1',
                            'value' => '1'
                        ],
                        [
                            'label' => 'LLL:EXT:z7_countries/Resources/Private/Language/locallang_db.xlf:*.' . TCAService::FIELD_NAME_MODE . '.2',
                            'value' => '2'
                        ],
                    ],
                    'default' => '0'
                ]
            ];

            $this->TCA[$table]['columns'][TCAService::FIELD_NAME_LIST] = [
                'label' => 'LLL:EXT:z7_countries/Resources/Private/Language/locallang_db.xlf:*.' . TCAService::FIELD_NAME_LIST,
                'exclude' => true,
                'l10n_mode' => 'exclude',
                'displayCond' => 'FIELD:' . TCAService::FIELD_NAME_MODE . ':REQ:true',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectCheckBox',
                    'foreign_table' => 'tx_z7countries_country',
                    'foreign_table_where' => 'AND tx_z7countries_country.hidden = 0',
                    'default' => ''
                ]
            ];
        }
    }

    protected function addPalette(string $table): void
    {
        if (isset($this->TCA[$table]) && !isset($this->TCA[$table]['palettes'][TCAService::PALETTE_NAME])) {
            $this->TCA[$table]['palettes'][TCAService::PALETTE_NAME] = [
                'showitem' => TCAService::FIELD_NAME_MODE . ',--linebreak--,' . TCAService::FIELD_NAME_LIST
            ];

            foreach ($this->TCA[$table]['types'] as &$type) {
                if ($hiddenField = $this->TCA[$table]['ctrl']['enablecolumns']['disabled'] ?? null) {

                    // If field is in palette?
                    $palette = array_key_first(array_filter($this->TCA[$table]['palettes'], static fn($p) => str_contains($p['showitem'] ?? '', $hiddenField)));
                    $replacement = $palette ?? $hiddenField;

                    $type['showitem'] = str_replace($replacement, $replacement . ',' . $this->getPalette(), $type['showitem'] ?? '');
                } else {
                    $type['showitem'] = trim($type['showitem'] ?? '', ',') . ',' . $this->getPalette();
                }
            }
        }
    }

    protected function addInlineRecordConfiguration(string $table, string $inlineRecordTable, string $inlineRecordField): void
    {
        if (
            ($config = $this->TCA[$inlineRecordTable]['columns'][$inlineRecordField]['config'] ?? null)
            && isset($config['type'], $config['foreign_table'], $config['overrideChildTca'])
            && ($config['type'] === 'inline' || $config['type'] === 'file')
            && $config['foreign_table'] === $table
        ) {
            foreach (array_keys($config['overrideChildTca']['types']) as $type) {
                if (isset($config['overrideChildTca']['types'][(string)$type])) {
                    $typeConfig = &$this->TCA[$inlineRecordTable]['columns'][$inlineRecordField]['config']['overrideChildTca']['types'][(string)$type];
                    $typeConfig['showitem'] = trim($typeConfig['showitem'], ',') . ',' . $this->getPalette();
                }
            }
        }
    }

    /** @throws Exception */
    public function __invoke(Event $event): void
    {
        $this->TCA = $event->getTca();

        foreach (RegistrationService::getTables() as $table) {
            $this->addEnableColumns($table);
            $this->addFields($table);
            $this->addPalette($table);
        }

        foreach (RegistrationService::getInlineRecords() as [$table, $inlineRecordTable, $inlineRecordField]) {
            $this->addEnableColumns($table);
            $this->addFields($table);
            $this->addPalette($table);
            $this->addInlineRecordConfiguration($table, $inlineRecordTable, $inlineRecordField);
        }

        $event->setTca($this->TCA);
    }
}
