<?php

declare(strict_types=1);

namespace Actor\Handler;

use Actor\Form\CheckYourAnswers;
use Common\Exception\ApiException;
use Common\Handler\{AbstractHandler,
    CsrfGuardAware,
    LoggerAware,
    Traits\CsrfGuard,
    Traits\Logger,
    Traits\Session as SessionTrait,
    Traits\User,
    UserAware};
use Common\Middleware\Session\SessionTimeoutException;
use Common\Service\Lpa\LpaService;
use DateTime;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Authentication\{AuthenticationInterface, UserInterface};
use Mezzio\Helper\UrlHelper;
use Mezzio\Session\SessionInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Log\LoggerInterface;

/**
 * Class CheckYourAnswersHandler
 * @package Actor\Handler
 * @codeCoverageIgnore
 */
class CheckYourAnswersHandler extends AbstractHandler implements UserAware, CsrfGuardAware, LoggerAware
{
    use User;
    use CsrfGuard;
    use SessionTrait;
    use Logger;

    private CheckYourAnswers $form;
    private ?SessionInterface $session;
    private ?UserInterface $user;
    private array $data;
    private LpaService $lpaService;
    private ?string $identity;

    public function __construct(
        TemplateRendererInterface $renderer,
        AuthenticationInterface $authenticator,
        UrlHelper $urlHelper,
        LpaService $lpaService,
        LoggerInterface $logger

    ) {
        parent::__construct($renderer, $urlHelper, $logger);

        $this->setAuthenticator($authenticator);
        $this->lpaService = $lpaService;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->form = new CheckYourAnswers($this->getCsrfGuard($request));
        $this->user = $this->getUser($request);
        $this->session = $this->getSession($request, 'session');
        $this->identity = (!is_null($this->user)) ? $this->user->getIdentity() : null;

        if (
            is_null($this->session)
            || is_null($this->session->get('opg_reference_number'))
            || is_null($this->session->get('first_names'))
            || is_null($this->session->get('last_name'))
            || is_null($this->session->get('dob')['day'])
            || is_null($this->session->get('dob')['month'])
            || is_null($this->session->get('dob')['year'])
            || is_null($this->session->get('postcode'))
        ) {
            throw new SessionTimeoutException();
        }

        $dobString = sprintf(
            '%s/%s/%s',
            $this->session->get('dob')['day'],
            $this->session->get('dob')['month'],
            $this->session->get('dob')['year']
        );

        $this->data = [
            'reference_number'  => $this->session->get('opg_reference_number'),
            'first_names'       => $this->session->get('first_names'),
            'last_name'         => $this->session->get('last_name'),
            'dob'               => $dobString,
            'postcode'          => $this->session->get('postcode')
        ];

        switch ($request->getMethod()) {
            case 'POST':
                return $this->handlePost($request);
            default:
                return $this->handleGet($request);
        }
    }

    public function handleGet(ServerRequestInterface $request): ResponseInterface
    {
        return new HtmlResponse($this->renderer->render('actor::check-your-answers', [
            'user'  => $this->user,
            'form'  => $this->form,
            'data'  => $this->data
        ]));
    }

    public function handlePost(ServerRequestInterface $request): ResponseInterface
    {
        $this->form->setData($request->getParsedBody());

        if ($this->form->isValid()) {
            try {
                 $this->lpaService->checkLPAMatchAndRequestLetter(
                     $this->identity,
                     $this->data
                 );
            } catch (ApiException $apiEx) {
                if ($apiEx->getCode() === StatusCodeInterface::STATUS_BAD_REQUEST) {
                    if ($apiEx->getMessage() === 'LPA not eligible') {
                        return new HtmlResponse($this->renderer->render('actor::cannot-send-activation-key'));
                    } elseif ($apiEx->getMessage() === 'LPA details does not match') {
                        return new HtmlResponse($this->renderer->render('actor::cannot-send-activation-key'));
                    } else {
                        return new HtmlResponse($this->renderer->render('actor::already-have-activation-key'));
                    }
                }
                if ($apiEx->getCode() === StatusCodeInterface::STATUS_NOT_FOUND) {
                    if ($apiEx->getMessage() === 'LPA not found') {
                        return new HtmlResponse($this->renderer->render('actor::cannot-find-lpa'));
                    }
                }
            }

            //LPA check match and letter request sent
            $twoWeeksFromNowDate = (new DateTime())->modify('+2 week');
            return new HtmlResponse($this->renderer->render('actor::send-activation-key-confirmation', [
                'date' => $twoWeeksFromNowDate,
            ]));
        }
    }
}
