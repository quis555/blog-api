<?php

use Fig\Http\Message\StatusCodeInterface;

class DocCest
{
    private const ENDPOINT = '/doc';

    public function tryToRequestForDocUI(ApiTester $I): void
    {
        $I->sendGET(self::ENDPOINT);
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_OK);
        $I->seeResponseContains('Blog API');
    }

    public function tryToRequestForDocYaml(ApiTester $I): void
    {
        $I->sendGET(self::ENDPOINT .'.yaml');
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_OK);
        $I->seeHttpHeader('content-type', 'application/x-yaml');
        $I->seeResponseContains('openapi');
    }

    public function tryToRequestForDocJson(ApiTester $I): void
    {
        $I->sendGET(self::ENDPOINT .'.json');
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_OK);
        $I->seeHttpHeader('content-type', 'application/json');
        $I->seeResponseIsJson();
        $I->seeResponseContains('openapi');
    }
}