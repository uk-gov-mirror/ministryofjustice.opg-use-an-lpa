<?php

declare(strict_types=1);

namespace Common\Service\Security;

use function hash;

class UserIdentificationService
{
    /**
     * @var string
     */
    private $hashSalt;

    /**
     * UserIdentificationService constructor.
     *
     * @param string $hashSalt A salt to add to the components that make up the hash
     */
    public function __construct(string $hashSalt)
    {
        $this->hashSalt = $hashSalt;
    }

    /**
     * Builds a unique userId that can be used to identify users for security tracking.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return string
     */
    public function id(\Psr\Http\Message\ServerRequestInterface $request): string
    {
        $headersToHash = [
            'accept',
            'accept-encoding',
            'accept-language',
            'user-agent',
            'x-forwarded-for'
        ];

        // pull each header value out (if it exists)
        foreach ($headersToHash as $header) {
            $headersToHash[$header] =
                $request->hasHeader($header)
                    ? $request->getHeader($header)
                    : $header;
        }

        return hash('sha256', $this->hashSalt . implode('', $headersToHash));
    }
}