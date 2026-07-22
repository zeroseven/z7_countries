<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Context\Context;
use Zeroseven\Countries\Context\CountryRequestContext;
use Zeroseven\Countries\Service\CountryService;

final readonly class InitializeCountryRequestContext implements MiddlewareInterface
{
    public function __construct(private Context $context) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $country = CountryService::getCountryByUri($request->getUri());
        $this->context->setAspect('country.request', new CountryRequestContext($country));

        return $handler->handle($request);
    }
}
