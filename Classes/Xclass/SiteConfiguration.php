<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Xclass;

use TYPO3\CMS\Core\Site\Entity\SiteSettings;

/**
 * Replaces the core SiteConfiguration service (see Configuration/Services.yaml)
 * to create country-aware Site entities: TYPO3 v13 instantiates Site objects
 * with "new" and resolves the service via dependency injection, so neither
 * entity nor service can be exchanged through the XCLASS mechanism anymore.
 *
 * Both methods mirror their core implementation; the only difference is the
 * instantiated Site class (\Zeroseven\Countries\Xclass\Site).
 */
class SiteConfiguration extends \TYPO3\CMS\Core\Configuration\SiteConfiguration
{
    /**
     * Same value as the private constant in the core class, used for the
     * runtime cache shared with getAllExistingSites().
     */
    private const CACHE_IDENTIFIER = 'sites-configuration';

    public function resolveAllExistingSites(bool $useCache = true): array
    {
        $sites = [];
        $siteConfiguration = $this->getAllSiteConfigurationFromFiles($useCache);
        foreach ($siteConfiguration as $identifier => $configuration) {
            // cast $identifier to string, as the identifier can potentially only consist of (int) digit numbers
            $identifier = (string)$identifier;
            $siteSettings = $this->siteSettingsFactory->getSettings($identifier, $configuration);
            $siteTypoScript = $this->getSiteTypoScript($identifier);
            $siteTSconfig = $this->getSiteTSconfig($identifier);
            $configuration['contentSecurityPolicies'] = $this->getContentSecurityPolicies($identifier);

            $rootPageId = (int)($configuration['rootPageId'] ?? 0);
            if ($rootPageId > 0) {
                $site = new Site($identifier, $rootPageId, $configuration, $siteSettings, $siteTypoScript, $siteTSconfig);
                $this->determineInvalidSets($site);
                $sites[$identifier] = $site;
            }
        }
        $this->runtimeCache->set(self::CACHE_IDENTIFIER, $sites);
        return $sites;
    }

    public function resolveAllExistingSitesRaw(): array
    {
        $sites = [];
        $siteConfiguration = $this->getAllSiteConfigurationFromFiles(false);
        foreach ($siteConfiguration as $identifier => $configuration) {
            // cast $identifier to string, as the identifier can potentially only consist of (int) digit numbers
            $identifier = (string)$identifier;
            $inlineSettings = $configuration['settings'] ?? [];
            $siteSettings = SiteSettings::createFromSettingsTree($inlineSettings);
            $siteTypoScript = $this->getSiteTypoScript($identifier);

            $rootPageId = (int)($configuration['rootPageId'] ?? 0);
            if ($rootPageId > 0) {
                $site = new Site($identifier, $rootPageId, $configuration, $siteSettings, $siteTypoScript);
                $this->determineInvalidSets($site);
                $sites[$identifier] = $site;
            }
        }
        return $sites;
    }
}
