<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use Zeroseven\Countries\Model\Country;
use Zeroseven\Countries\Service\LanguageManipulationService;

/**
 * Protects system locales when site languages are adapted to a country.
 */
final class LanguageManipulationServiceTest extends TestCase
{
    /**
     * Country adaptation must preserve the original locale and routing rules.
     */
    #[DataProvider('localeProvider')]
    public function testCountryAdaptationPreservesLocale(string $locale, bool $countryIsConfigured): void
    {
        $language = new SiteLanguage(4, $locale, new Uri('https://example.org/international/'), [
            'title' => 'Original language',
            'hreflang' => 'original',
            'countries' => $countryIsConfigured ? '3,4' : '3',
        ]);
        $country = new Country([
            'uid' => 4,
            'title' => 'France',
            'iso_code' => 'FR',
            'parameter' => 'fr',
        ]);
        $originalLocale = $language->getLocale()->posixFormatted();
        $languageCode = $language->getLocale()->getLanguageCode();

        $adaptedLanguage = LanguageManipulationService::getManipulatedLanguage($language, $country);

        self::assertSame($originalLocale, $adaptedLanguage->getLocale()->posixFormatted());
        self::assertSame($language->getLocale()->getPosixCodeSet(), $adaptedLanguage->getLocale()->getPosixCodeSet());
        self::assertSame($language->getLanguageId(), $adaptedLanguage->getLanguageId());
        self::assertSame($language->getTitle(), $adaptedLanguage->getTitle());
        self::assertSame($languageCode . '-FR', $adaptedLanguage->getHreflang());
        self::assertSame(
            $countryIsConfigured ? '/' . $languageCode . '-fr/' : '/international/',
            $adaptedLanguage->getBase()->getPath()
        );
        self::assertSame('https://example.org/international/', (string)$language->getBase());
        self::assertSame('original', $language->getHreflang());
        self::assertSame($originalLocale, $language->getLocale()->posixFormatted());
    }

    /**
     * Covers UTF-8 spellings, legacy encodings, modifiers and plain locales.
     *
     * @return iterable<string, array{string, bool}>
     */
    public static function localeProvider(): iterable
    {
        foreach (['fr_FR.utf8', 'it_IT.utf8', 'en_US.UTF-8', 'pt_PT.utf8', 'de_DE.ISO8859-15@euro', 'de_DE@euro', 'fr_FR'] as $locale) {
            foreach ([true, false] as $countryIsConfigured) {
                yield $locale . ($countryIsConfigured ? ' with country URL' : ' with original URL') => [$locale, $countryIsConfigured];
            }
        }
    }
}
