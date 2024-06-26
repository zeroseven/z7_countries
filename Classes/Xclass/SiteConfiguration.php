<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Xclass;

use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteSettings;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SiteConfiguration extends \TYPO3\CMS\Core\Configuration\SiteConfiguration
{
    private function getXClassSites(array &$sites, string $identifier, array $configuration, $siteSettings): void
    {
        $rootPageId = (int)($configuration['rootPageId'] ?? 0);

        if ($rootPageId > 0) {
            $sites[$identifier] = GeneralUtility::makeInstance(Site::class, $identifier, $rootPageId, $configuration, $siteSettings);
        }
    }

    public function resolveAllExistingSites(bool $useCache = true): array
    {
        $sites = [];
        $siteConfiguration = $this->getAllSiteConfigurationFromFiles($useCache);
        foreach ($siteConfiguration as $identifier => $configuration) {
            // cast $identifier to string, as the identifier can potentially only consist of (int) digit numbers
            $identifier = (string)$identifier;
            $siteSettings = $this->getSiteSettings($identifier, $configuration);
            $configuration['contentSecurityPolicies'] = $this->getContentSecurityPolicies($identifier);

            /**
             * $rootPageId = (int)($configuration['rootPageId'] ?? 0);
             * if ($rootPageId > 0) {
             * $sites[$identifier] = GeneralUtility::makeInstance(Site::class, $identifier, $rootPageId, $configuration, $siteSettings);
             * }
             *
             * This part must be overwritten by the following line for the extension to work with TYPO3 12. Sorry!
             */

            $this->getXClassSites($sites, $identifier, $configuration, $siteSettings);
        }
        $this->firstLevelCache = $sites;
        return $sites;
    }

    public function resolveAllExistingSitesRaw(): array
    {
        $sites = [];
        $siteConfiguration = $this->getAllSiteConfigurationFromFiles(false);
        foreach ($siteConfiguration as $identifier => $configuration) {
            // cast $identifier to string, as the identifier can potentially only consist of (int) digit numbers
            $identifier = (string)$identifier;
            $siteSettings = new SiteSettings($configuration['settings'] ?? []);

            /**
             * $rootPageId = (int)($configuration['rootPageId'] ?? 0);
             * if ($rootPageId > 0) {
             * $sites[$identifier] = GeneralUtility::makeInstance(Site::class, $identifier, $rootPageId, $configuration, $siteSettings);
             * }
             *
             * This part must be overwritten by the following line for the extension to work with TYPO3 12. Sorry!
             */

            $this->getXClassSites($sites, $identifier, $configuration, $siteSettings);
        }
        return $sites;
    }
}
