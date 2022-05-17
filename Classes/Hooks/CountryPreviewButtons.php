<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Hooks;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\Route;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\Components\Buttons\LinkButton;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Zeroseven\Countries\Service\CountryService;
use Zeroseven\Countries\Service\IconService;
use Zeroseven\Countries\Service\LanguageManipulationService;
use Zeroseven\Countries\Service\TCAService;

class CountryPreviewButtons
{
    protected const TABLE = 'pages';

    protected ?array $data;
    protected int $languageUid;
    protected int $pageUid;
    protected ?Site $site = null;
    protected ?SiteLanguage $siteLanguage = null;

    public function __construct()
    {
        $this->data = $this->getPageRecord();

        if ($this->data !== null) {
            $languageField = $GLOBALS['TCA'][self::TABLE]['ctrl']['languageField'] ?? null;
            $languageUid = (int)($this->data[$languageField][0] ?? ($this->data[$languageField] ?? 0));

            $this->pageUid = (int)($languageUid > 0 ? $this->data[$GLOBALS['TCA'][self::TABLE]['ctrl']['transOrigPointerField']] : $this->data['uid']);
            $this->site = $this->getSite();
            $this->siteLanguage = $this->getSiteLanguage($languageUid);
        }
    }

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

            if ($routeIdentifier === 'record_edit' && isset($queryParams['edit']['tt_content']) && $id = array_key_first($queryParams['edit']['tt_content'])) {
                $languageField = $GLOBALS['TCA']['tt_content']['ctrl']['languageField'];
                $content = BackendUtility::getRecord('tt_content', $id, 'pid,' . $languageField);
                $pageUid = (int)($content['pid'] ?? 0);
                $languageUid = (int)($content[$languageField] ?? 0);

                if ($pageUid) {
                    if($languageUid > 0) {
                        $data = BackendUtility::getRecordLocalization(self::TABLE, $pageUid, $languageUid);

                        return $data[0] ?? null;
                    }

                    return BackendUtility::getRecord(self::TABLE, $pageUid);
                }
            }
        }

        return null;
    }

    protected function getSite(): ?Site
    {
        try {
            if ($this->pageUid) {
                return GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($this->pageUid);
            }
        } catch (SiteNotFoundException $e) {
        }

        return null;
    }

    protected function getSiteLanguage(int $languageUid): ?SiteLanguage
    {
        if (empty($this->site)) {
            return null;
        }

        return $this->site->getLanguageById($languageUid);
    }

    protected function needButtons(): bool
    {
        $tsConfig = BackendUtility::getPagesTSconfig($this->pageUid);

        $excludedDoktypes = array_merge(
            [
                PageRepository::DOKTYPE_RECYCLER,
                PageRepository::DOKTYPE_SYSFOLDER,
                PageRepository::DOKTYPE_SPACER,
            ],
            ($listConfig = $tsConfig['mod.']['web_list.']['noViewWithDokTypes'] ?? null) ? GeneralUtility::intExplode($listConfig) : [],
            ($pageConfig = $tsConfig['TCEMAIN.']['preview.']['disableButtonForDokType'] ?? null) ? GeneralUtility::intExplode($pageConfig) : [],
        );

        return !in_array((int)$this->data['doktype'], $excludedDoktypes, true);
    }

    protected function disablePreview(array &$buttons): void
    {
        foreach ($buttons as $button) {
            if (is_array($button)) {
                $this->disablePreview($button);
            }

            if ($button instanceof LinkButton && ($icon = $button->getIcon()) && ($icon->getIdentifier() === 'actions-view-page' || $icon->getIdentifier() === 'actions-view')) {
                $button->setDisabled(true);
            }
        }
    }

    public function add(array $params, ButtonBar $buttonBar): array
    {
        $buttons = $params['buttons'] ?? [];

        if ($this->siteLanguage && $this->needButtons()) {

            // Get list of enabled countries
            $modeField = TCAService::getModeColumn(self::TABLE);
            $listField = TCAService::getListColumn(self::TABLE);
            $enabledCountries = ($list = empty($this->data[$modeField] ?? null) ? null : $this->data[$listField] ?? null) === null ? null : GeneralUtility::intExplode(',', $list);

            // Disable original "actions-view-page" icon
            if ((int)($this->data[$modeField] ?? 0) === 1) {
                $this->disablePreview($buttons);
            }

            // Get the original preview url
            $url = BackendUtility::getPreviewUrl($this->pageUid, '', null, '', '', '&L=' . $this->siteLanguage->getLanguageId());

            // Button title
            $title = ($GLOBALS['LANG'] ?? null) instanceof LanguageService ? $GLOBALS['LANG']->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.showPage') : 'Preview';

            // Create split button
            foreach (CountryService::getAllCountries() ?: [] as $country) {
                $enabled = $enabledCountries === null || in_array($country->getUid(), $enabledCountries, true);

                $buttons[isset($buttons['left']) ? 'left' : $buttons[array_key_first($buttons)]][self::class][] = $buttonBar->makeLinkButton()
                    ->setDataAttributes($enabled ? [
                        'dispatch-action' => 'TYPO3.WindowManager.localOpen',
                        'dispatch-args' => json_encode([
                            LanguageManipulationService::manipulateUrl($url, $this->siteLanguage, $country),
                            null,
                            'newTYPO3frontendWindow'
                        ])
                    ] : [])
                    ->setTitle($title . ' (' . LanguageManipulationService::getHreflang($this->siteLanguage, $country) . ')')
                    ->setIcon(GeneralUtility::makeInstance(IconFactory::class)->getIcon('actions-preview', Icon::SIZE_SMALL, IconService::getCountryIdentifier($country)))
                    ->setDisabled(!$enabled)
                    ->setHref('#');
            }
        }

        return $buttons;
    }
}
