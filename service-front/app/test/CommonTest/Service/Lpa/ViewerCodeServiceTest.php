<?php

declare(strict_types=1);

namespace CommonTest\Service\Lpa;

use ArrayObject;
use Common\Service\ApiClient\Client;
use Common\Service\Lpa\ViewerCodeService;
use PHPUnit\Framework\TestCase;
use DateTime;

class ViewerCodeServiceTest extends TestCase
{
    const IDENTITY_TOKEN = '01234567-01234-01234-01234-012345678901';

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy|Client
     */
    private $apiClientProphecy;

    public function setUp()
    {
        $this->apiClientProphecy = $this->prophesize(Client::class);

        // all methods in this
        $this->apiClientProphecy
            ->setUserTokenHeader(self::IDENTITY_TOKEN)
            ->shouldBeCalled();
    }

    /** @test */
    public function it_creates_a_new_viewercode_given_correct_details()
    {
        $lpaId      = '700000000047';
        $viewerCode = '123456789012';
        $expiry     = (new \DateTime('now +30 days'))->format('c');
        $orgName    = 'Test Org';

        $return = [
            'code'         => $viewerCode,
            'expiry'       => $expiry,
            'organisation' => $orgName
        ];

        $this->apiClientProphecy
            ->httpPost('/v1/lpas/' . $lpaId . '/codes', ['organisation' => $orgName])
            ->willReturn($return);

        $viewerCodeService = new ViewerCodeService($this->apiClientProphecy->reveal());

        $codeData = $viewerCodeService->createShareCode(self::IDENTITY_TOKEN, $lpaId, $orgName);

        $this->assertInstanceOf(ArrayObject::class, $codeData);
        $this->assertEquals($viewerCode, $codeData->code);
        $this->assertInstanceOf(\DateTime::class, new \DateTime($codeData->expiry));
        $this->assertEquals($orgName, $codeData->organisation);
    }

    /** @test */
    public function it_gets_share_codes_for_a_given_lpa()
    {
        $lpaId = '98765432-01234-01234-01234-012345678901';

        $return = [
            [
                'UserLpaActor' => $lpaId
            ]
        ];

        $this->apiClientProphecy
            ->httpGet('/v1/lpas/' . $lpaId . '/codes')
            ->willReturn($return);

        $viewerCodeService = new ViewerCodeService($this->apiClientProphecy->reveal());

        $shareCodes = $viewerCodeService->getShareCodes(self::IDENTITY_TOKEN, $lpaId, false);

        $this->assertInstanceOf(ArrayObject::class, $shareCodes);
        $this->assertEquals($lpaId, $shareCodes[0]['UserLpaActor']);
    }

    /** @test */
    public function returns_empty_array_if_no_share_codes_generated()
    {
        $lpaId = '98765432-01234-01234-01234-012345678902';

        $this->apiClientProphecy
            ->httpGet('/v1/lpas/' . $lpaId . '/codes')
            ->willReturn([]);

        $viewerCodeService = new ViewerCodeService($this->apiClientProphecy->reveal());

        $shareCodes = $viewerCodeService->getShareCodes(self::IDENTITY_TOKEN, $lpaId, false);

        $this->assertInstanceOf(ArrayObject::class, $shareCodes);
        $this->assertEmpty($shareCodes);
    }

    /** @test */
    public function gets_number_of_active_codes_for_lpa()
    {
        $lpaId = '98765432-01234-01234-01234-012345678902';
        $now =  (new DateTime('now'))->format('Y-m-d');
        $futureWeek = (new DateTime('+ 1 week'))->format('Y-m-d');
        $pastWeek = (new DateTime('- 1 week'))->format('Y-m-d');

        $return = [
            [
                'UserLpaActor' => $lpaId,
                'Expires' => $pastWeek,
            ],
            [
                'UserLpaActor' => $lpaId,
                'Expires' => $futureWeek,
            ],
            [
                'UserLpaActor' => $lpaId,
                'Expires' => $now,
            ],
        ];

        $this->apiClientProphecy
            ->httpGet('/v1/lpas/' . $lpaId . '/codes')
            ->willReturn($return);

        $viewerCodeService = new ViewerCodeService($this->apiClientProphecy->reveal());

        $shareCodes = $viewerCodeService->getShareCodes(self::IDENTITY_TOKEN, $lpaId, true);

        $this->assertInstanceOf(ArrayObject::class, $shareCodes);
        $this->assertEquals($lpaId, $shareCodes[0]['UserLpaActor']);
        $this->assertLessThan($now, $shareCodes[0]['Expires']);
        $this->assertGreaterThan($now, $shareCodes[1]['Expires']);
        $this->assertEquals($now, $shareCodes[2]['Expires']);
        $this->assertEquals(1, $shareCodes['activeCodeCount']);
    }
}