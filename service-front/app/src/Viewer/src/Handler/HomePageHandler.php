<?php

declare(strict_types=1);

namespace Viewer\Handler;

use Common\Handler\AbstractHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\HtmlResponse;

/**
 * Class HomePageHandler
 * @package Viewer\Handler
 */
class HomePageHandler extends AbstractHandler
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new HtmlResponse($this->renderer->render('viewer::home-page'));
    }
}
