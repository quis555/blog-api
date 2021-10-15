<?php

use Fig\Http\Message\StatusCodeInterface;

class UserLoginWithRefreshTokenCest
{
    private const ENDPOINT = '/user/login/refresh';

    public function tryToLoginUserWithEmptyRefreshToken(ApiTester $I): void
    {
        $I->sendPost(self::ENDPOINT);
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_BAD_REQUEST);
        $I->seeResponseIsJson();
        $I->seeResponseContains('The refreshToken is required');
    }

    public function tryToLoginUserWithTooShortRefreshToken(ApiTester $I): void
    {
        $request = $this->exampleRequest();
        $request['refreshToken'] = 'x';
        $I->sendPost(self::ENDPOINT, $request);
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_BAD_REQUEST);
        $I->seeResponseIsJson();
        $I->seeResponseContains('The refreshToken minimum is 20');
    }

    public function tryToLoginUserWithNotExistingRefreshToken(ApiTester $I): void
    {
        $I->sendPost(self::ENDPOINT, $this->exampleRequest());
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_NOT_FOUND);
        $I->seeResponseIsJson();
        $I->seeResponseContains('Invalid token');
    }

    public function tryToLoginUserWithExpiredRefreshToken(ApiTester $I): void
    {
        $user = $I->haveUserInDatabase();
        $token = $I->haveRefreshTokenInDatabase($user['id'], '-5 minutes');
        $request = $this->exampleRequest();
        $request['refreshToken'] = $token['token'];
        $I->sendPost(self::ENDPOINT, $request);
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_NOT_FOUND);
        $I->seeResponseIsJson();
        $I->seeResponseContains('Invalid token');
    }

    public function tryToLoginUserWithUsedRefreshToken(ApiTester $I): void
    {
        $user = $I->haveUserInDatabase();
        $token = $I->haveRefreshTokenInDatabase($user['id'], '+5 minutes', true);
        $request = $this->exampleRequest();
        $request['refreshToken'] = $token['token'];
        $I->sendPost(self::ENDPOINT, $request);
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_NOT_FOUND);
        $I->seeResponseIsJson();
        $I->seeResponseContains('Invalid token');
    }

    public function tryToLoginUserWithValidRefreshToken(ApiTester $I): void
    {
        $user = $I->haveUserInDatabase();
        $token = $I->haveRefreshTokenInDatabase($user['id']);
        $request = $this->exampleRequest();
        $request['refreshToken'] = $token['token'];
        $I->sendPost(self::ENDPOINT, $request);
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_CREATED);
        $I->seeResponseIsJson();
        $accessToken = $I->grabDataFromResponseByJsonPath('$.accessToken.token')[0] ?? null;
        $refreshToken = $I->grabDataFromResponseByJsonPath('$.refreshToken.token')[0] ?? null;
        $I->seeInDatabase('access_tokens', ['user_id' => $user['id'], 'token' => $accessToken]);
        $I->seeInDatabase('refresh_tokens', ['user_id' => $user['id'], 'token' => $refreshToken]);
        $I->seeInDatabase('refresh_tokens', ['id' => $token['id'], 'used' => 1]);
    }

    private function exampleRequest(): array
    {
        return [
            'refreshToken' => 'notExistingRefreshToken',
        ];
    }
}