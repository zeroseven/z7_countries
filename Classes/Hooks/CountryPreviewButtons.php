<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Hooks;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\Route;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\Components\Buttons\LinkButton;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Zeroseven\Countries\Service\CountryService;
use Zeroseven\Countries\Service\IconService;
use Zeroseven\Countries\Service\TCAService;

class CountryPreviewButtons
{
    protected const TABLE = 'pages';

    protected function getPageRecord(): ?array
    {
        if (
            ($GLOBALS['TYPO3_REQUEST'] ?? null) instanceof ServerRequestInterface
            && ($queryParams = $GLOBALS['TYPO3_REQUEST']->getQueryParams())
            && ($route = $GLOBALS['TYPO3_REQUEST']->getAttribute('route')) instanceof Route
            && ($routeIdentifier = $route->getOption('_identifier'))
        ) {
            if ($routeIdentifier === 'web_layout' && $id = $queryParams['id'] ?? null) {
                if (($moduleData = BackendUtility::getModuleData([], null, 'web_layout')) && $language = $moduleData['language'] ?? null) {
                    $data = BackendUtility::getRecordLocalization(self::TABLE, (int)$id, (int)$language);

                    return $data[0] ?? null;
                }

                return BackendUtility::getRecord(self::TABLE, $id);
            }

            if ($routeIdentifier === 'web_list' && $id = $queryParams['id'] ?? null) {
                return BackendUtility::getRecord(self::TABLE, $id);
            }

            if ($routeIdentifier === 'record_edit' && isset($queryParams['edit'][self::TABLE]) && $id = array_key_first($queryParams['edit'][self::TABLE])) {
                return BackendUtility::getRecord(self::TABLE, $id);
            }
        }

        return null;
    }

    protected function needButtons(array $data): bool
    {
        $tsConfig = BackendUtility::getPagesTSconfig((int)$data['uid']);

        $excludedDoktypes = array_merge(
            [
                PageRepository::DOKTYPE_RECYCLER,
                PageRepository::DOKTYPE_SYSFOLDER,
                PageRepository::DOKTYPE_SPACER,
            ],
            ($listConfig = $tsConfig['mod.']['web_list.']['noViewWithDokTypes'] ?? null) ? GeneralUtility::intExplode($listConfig) : [],
            ($pageConfig = $tsConfig['TCEMAIN.']['preview.']['disableButtonForDokType'] ?? null) ? GeneralUtility::intExplode($pageConfig) : [],
        );

        return !in_array((int)$data['doktype'], $excludedDoktypes, true);
    }

    protected function disablePreview(array &$buttons): void
    {
        foreach ($buttons as $button) {
            if (is_array($button)) {
                $this->disablePreview($button);
            }

            if ($button instanceof LinkButton && $button->getIcon()->getIdentifier() === 'actions-view-page') {
                $button->setDisabled(true);
            }
        }
    }

    public function add(array $params, ButtonBar $buttonBar): array
    {
        $buttons = $params['buttons'] ?? [];

        if (($data = $this->getPageRecord()) && $this->needButtons($data)) {

            // Get list of enabled countries
            $modeField = TCAService::getModeColumn(self::TABLE);
            $listField = TCAService::getListColumn(self::TABLE);
            $enabledCountries = ($list = empty($data[$modeField]) ? null : $data[$listField] ?? null) && is_string($list) ? GeneralUtility::intExplode(',', $list) : [];

            // Disable orginial "actions-view-page" icon
            if ((int)($data[$modeField] ?? 0) === 1) {
                $this->disablePreview($buttons);
            }

            foreach (CountryService::getAllCountries() ?: [] as $country) {
                $enabled = empty($enabledCountries) || in_array($country->getUid(), $enabledCountries, true);

                $button = $buttonBar->makeLinkButton()
                    ->setDataAttributes([])
                    ->setTitle('test')
                    ->setIcon(GeneralUtility::makeInstance(IconFactory::class)->getIcon('actions-view-page', Icon::SIZE_SMALL, IconService::getCountryIdentifier($country)))
                    ->setDisabled(!$enabled)
                    ->setHref('#');

                $buttons['left'][self::class][] = $button;
            }
        }

        return $buttons;
    }
}
