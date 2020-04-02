<?php

declare(strict_types=1);

namespace Actor\Handler;

use Actor\Form\ConfirmDeleteAccount;
use Common\Handler\AbstractHandler;
use Common\Handler\CsrfGuardAware;
use Common\Handler\Traits\CsrfGuard;
use Common\Handler\Traits\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Common\Handler\UserAware;

/**
 * Class YourDetailsHandler
 *
 * @package Actor\Handler
 * @codeCoverageIgnore
 */
class YourDetailsHandler extends AbstractHandler implements UserAware, CsrfGuardAware
{
    use User;
    use CsrfGuard;

    public function __construct(
        TemplateRendererInterface $renderer,
        AuthenticationInterface $authenticator,
        UrlHelper $urlHelper
    ) {
        parent::__construct($renderer, $urlHelper);

        $this->setAuthenticator($authenticator);
    }

    /**
     * Handles a request and produces a response
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = $this->getUser($request);
        $identity = $user->getIdentity();
        $email = $user->getDetails()['Email'];

        $form = new ConfirmDeleteAccount($this->getCsrfGuard($request));
        $form->setAttribute('action', $this->urlHelper->generate('lpa.confirm-delete-account'));

        $form->setData([
            'account_id' => $identity,
            'user_email' => $email
        ]);

        return new HtmlResponse($this->renderer->render('actor::your-details', [
            'user' => $user,
            'form' => $form->prepare()
        ]));
    }
}
