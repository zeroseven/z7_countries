<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Service;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\Uri;

class ParameterService
{
    private const DELIMITER = '_';

    private static function createUri(): UriInterface
    {
        return new Uri((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . ':// . ' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    }

    private static function getCountries(): array
    {
        // TODO: Create dynamic list
        return ['de', 'en', 'fr'];
    }

    public static function getCountry(UriInterface $uri = null): ?string
    {
        $path = ($uri ?: self::createUri())->getPath();

        return
            preg_match('/^\/?[a-z]+' . self::DELIMITER . '([a-z]+)/i', $path, $matches)
            && ($country = $matches[1])
            && ($countries = self::getCountries())
            && (in_array($country, $countries, true)) ? $country : null;
    }

    public static function hasCountry(UriInterface $uri = null): bool
    {
        return (bool)self::getCountry($uri);
    }

    public static function addCountry(string $country, UriInterface $uri = null): UriInterface
    {
        if ($uri === null) {
            $uri = self::createUri();
        }

        return $uri->withPath(rtrim($uri->getPath(), '/') . self::DELIMITER . $country . '/');
    }

    public static function removeCountry(string $country = null, UriInterface $uri = null): UriInterface
    {
        if ($uri === null) {
            $uri = self::createUri();
        }

        if ($country === null) {
            $country = self::getCountry($uri);
        }

        if ($country) {
            $path = $uri->getPath();

            return $uri->withPath(preg_replace('/^(\/?[a-z])(' . self::DELIMITER . $country . ')(.*)/i', '$1$3', $path));
        }

        return $uri;
    }
}
