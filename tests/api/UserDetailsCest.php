<?php

use Fig\Http\Message\StatusCodeInterface;

class UserDetailsCest
{
    private const ENDPOINT = '/user';

    public function tryToGetCurrentUserWithoutAuthorization(ApiTester $I): void
    {
        $I->sendGet(self::ENDPOINT);
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_UNAUTHORIZED);
        $I->seeResponseIsJson();
        $I->seeResponseContains('Authorization token is empty');
    }

    public function tryToGetCurrentUserWithInvalidAuthorization(ApiTester $I): void
    {
        $I->haveHttpHeader('Authorization', 'invalidAuthorizationToken');
        $I->sendGet(self::ENDPOINT);
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_UNAUTHORIZED);
        $I->seeResponseIsJson();
        $I->seeResponseContains('Authorization token not found');
    }

    public function tryToGetCurrentUserWithExpiredAuthorization(ApiTester $I): void
    {
        $user = $I->haveUserInDatabase();
        $token = $I->haveAccessTokenInDatabase($user['id'], '-5 minutes');
        $I->haveHttpHeader('Authorization', $token['token']);
        $I->sendGet(self::ENDPOINT);
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_UNAUTHORIZED);
        $I->seeResponseIsJson();
        $I->seeResponseContains('Authorization token expired');
    }

    public function tryToGetCurrentUserWithValidAuthorization(ApiTester $I): void
    {
        $user = $I->haveUserInDatabase();
        $token = $I->haveAccessTokenInDatabase($user['id']);
        $I->haveHttpHeader('Authorization', $token['token']);
        $I->sendGet(self::ENDPOINT);
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_OK);
        $I->seeResponseIsJson();
        $id = $I->grabDataFromResponseByJsonPath('$.id')[0] ?? null;
        $login = $I->grabDataFromResponseByJsonPath('$.login')[0] ?? null;
        $email = $I->grabDataFromResponseByJsonPath('$.email')[0] ?? null;
        $displayName = $I->grabDataFromResponseByJsonPath('$.displayName')[0] ?? null;
        $registeredAt = $I->grabDataFromResponseByJsonPath('$.registeredAt')[0] ?? null;
        $lastLoginAt = $I->grabDataFromResponseByJsonPath('$.lastLoginAt')[0] ?? null;
        $I->assertEquals($user['id'], $id, 'Invalid user id');
        $I->assertEquals($user['login'], $login, 'Invalid user login');
        $I->assertEquals($user['email'], $email, 'Invalid user email');
        $I->assertEquals($user['displayName'], $displayName, 'Invalid user displayName');
        $I->assertNotEmpty($registeredAt);
        $I->assertNull($lastLoginAt);
    }
}