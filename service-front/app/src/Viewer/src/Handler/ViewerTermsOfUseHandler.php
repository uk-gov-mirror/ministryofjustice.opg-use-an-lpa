<?php

declare(strict_types=1);

namespace Viewer\Handler;

use Common\Handler\AbstractHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\HtmlResponse;

/**
 * Class ViewerTermsOfUseHandler
 * @package Viewer\Handler
 * @codeCoverageIgnore
 */
class ViewerTermsOfUseHandler extends AbstractHandler
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $referer = $request->getHeaders()['referer'][0];

        return new HtmlResponse($this->renderer->render('viewer::viewer-terms-of-use', [
            'referer' => $referer
        ]));
    }
}
