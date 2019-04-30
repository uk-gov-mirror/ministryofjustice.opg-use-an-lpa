<?php

declare(strict_types=1);

namespace ViewerTest\Handler;

use Viewer\Handler\ViewLpaHandler;
use Viewer\Handler\ViewLpaHandlerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Viewer\Service\Lpa\LpaService;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Template\TemplateRendererInterface;

class ViewLpaHandlerFactoryTest extends TestCase
{
    public function testInvoke()
    {
        $container = $this->prophesize(ContainerInterface::class);

        $renderer = $this->prophesize(TemplateRendererInterface::class);
        $container->get(TemplateRendererInterface::class)
            ->willReturn($renderer);

        $urlHelper = $this->prophesize(UrlHelper::class);
        $container->get(UrlHelper::class)
            ->willReturn($urlHelper);

        $lpaService = $this->prophesize(LpaService::class);
        $container->get(LpaService::class)
            ->willReturn($lpaService);

        $factory = new ViewLpaHandlerFactory();

        $this->assertInstanceOf(ViewLpaHandlerFactory::class, $factory);

        $handler = $factory($container->reveal());

        $this->assertInstanceOf(ViewLpaHandler::class, $handler);
    }
}
