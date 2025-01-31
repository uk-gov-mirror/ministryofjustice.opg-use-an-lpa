<?php

declare(strict_types=1);

namespace Actor\Handler;

use Common\Handler\{AbstractHandler, CsrfGuardAware, Traits\CsrfGuard, Traits\Session as SessionTrait, UserAware};
use Actor\Form\RequestActivationKey;
use Common\Handler\Traits\User;
use Common\Middleware\Session\SessionTimeoutException;
use Mezzio\Authentication\UserInterface;
use Mezzio\Session\SessionInterface;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Laminas\Diactoros\Response\HtmlResponse;

/**
 * Class RequestActivationKeyHandler
 * @package Actor\Handler
 * @codeCoverageIgnore
 */
class RequestActivationKeyHandler extends AbstractHandler implements UserAware, CsrfGuardAware
{
    use User;
    use CsrfGuard;
    use SessionTrait;

    private RequestActivationKey $form;
    private ?SessionInterface $session;
    private ?UserInterface $user;

    public function __construct(
        TemplateRendererInterface $renderer,
        AuthenticationInterface $authenticator,
        UrlHelper $urlHelper
    ) {
        parent::__construct($renderer, $urlHelper);

        $this->setAuthenticator($authenticator);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->form = new RequestActivationKey($this->getCsrfGuard($request));
        $this->user = $this->getUser($request);
        $this->session = $this->getSession($request, 'session');

        switch ($request->getMethod()) {
            case 'POST':
                return $this->handlePost($request);
            default:
                return $this->handleGet($request);
        }
    }

    public function handleGet(ServerRequestInterface $request): ResponseInterface
    {
        $this->form->setData($this->session->toArray());

        return new HtmlResponse($this->renderer->render('actor::request-activation-key', [
            'user' => $this->user,
            'form' => $this->form->prepare()
        ]));
    }

    public function handlePost(ServerRequestInterface $request): ResponseInterface
    {
        $this->form->setData($request->getParsedBody());

        if ($this->form->isValid()) {
            $postData = $this->form->getData();

            //  Set the data in the session and pass to the check your answers handler
            $this->session->set('opg_reference_number', $postData['opg_reference_number']);
            $this->session->set('first_names', $postData['first_names']);
            $this->session->set('last_name', $postData['last_name']);
            $this->session->set('postcode', $postData['postcode']);
            $this->session->set(
                'dob',
                [
                    'day' => $postData['dob']['day'],
                    'month' => $postData['dob']['month'],
                    'year' => $postData['dob']['year']
                ]
            );
            return $this->redirectToRoute('lpa.check-answers');
        }

        return new HtmlResponse($this->renderer->render('actor::request-activation-key', [
            'user' => $this->user,
            'form' => $this->form->prepare()
        ]));
    }
}
