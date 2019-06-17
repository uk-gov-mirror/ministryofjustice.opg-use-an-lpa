<?php

declare(strict_types=1);

namespace App\Handler;

use App\Exception\BadRequestException;
use App\Service\User\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class UserHandler
 * @package App\Handler
 */
class UserHandler implements RequestHandlerInterface
{
    /**
     * @var UserService
     */
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \Exception
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $data = [];

        if ($request->getMethod() === 'POST') {
            $requestData = $request->getParsedBody();

            if (!isset($requestData['email']) || !isset($requestData['password'])) {
                throw new BadRequestException('Email address and password must be provided');
            }

            $this->userService->add($requestData);
        } else {
            //  If not a post then try to get a user
            $params = $request->getQueryParams();

            if (!isset($params['email'])) {
                throw new BadRequestException('Email address must be provided');
            }

            $data = $this->userService->get($params['email']);
        }

        return new JsonResponse($data);
    }
}
