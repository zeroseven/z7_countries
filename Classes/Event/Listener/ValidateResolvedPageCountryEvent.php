<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Event\Listener;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\ErrorController;
use TYPO3\CMS\Frontend\Event\AfterPageAndLanguageIsResolvedEvent;
use TYPO3\CMS\Frontend\Page\PageAccessFailureReasons;
use TYPO3\CMS\Frontend\Page\PageInformation;
use Zeroseven\Countries\Context\CountryRequestContext;
use Zeroseven\Countries\Model\Country;
use Zeroseven\Countries\Service\CountryService;

final readonly class ValidateResolvedPageCountryEvent
{
    public function __construct(private Context $context) {}

    private function getRequestedPageRecord(PageInformation $pageInformation): array
    {
        return $pageInformation->getOriginalShortcutPageRecord()
            ?? $pageInformation->getOriginalMountPointPageRecord()
            ?? $pageInformation->getPageRecord();
    }

    private function createPageNotFoundResponse(AfterPageAndLanguageIsResolvedEvent $event, ?Country $country): ResponseInterface
    {
        $reasonCode = PageAccessFailureReasons::LANGUAGE_NOT_AVAILABLE;
        $message = GeneralUtility::makeInstance(PageAccessFailureReasons::class)->getMessageForReason($reasonCode);
        $response = GeneralUtility::makeInstance(ErrorController::class)
            ->pageNotFoundAction($event->getRequest(), $message, ['code' => $reasonCode])
            ->withHeader('X-Extension', 'z7_countries');

        return $country === null ? $response : $response->withHeader('X-Country', $country->getIsoCode());
    }

    public function __invoke(AfterPageAndLanguageIsResolvedEvent $event): void
    {
        $country = $this->context->getPropertyFromAspect('country.request', 'country');
        $pageRecord = $this->getRequestedPageRecord($event->getPageInformation());

        if (!CountryService::isRecordAvailableForCountry('pages', $pageRecord, $country)) {
            $event->setResponse($this->createPageNotFoundResponse($event, $country));
            return;
        }

        $this->context->setAspect('country.request', new CountryRequestContext($country, true));
    }
}
