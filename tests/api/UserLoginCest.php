<?php

use Fig\Http\Message\StatusCodeInterface;

class UserLoginCest
{
    private const ENDPOINT = '/user/login';

    public function tryToLoginUserWithEmptyLogin(ApiTester $I): void
    {
        $request = $this->exampleRequest();
        unset($request['login']);
        $I->sendPost(self::ENDPOINT, $request);
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_BAD_REQUEST);
        $I->seeResponseIsJson();
        $I->seeResponseContains('The login is required');
    }

    public function tryToLoginUserWithTooShortLogin(ApiTester $I): void
    {
        $request = $this->exampleRequest();
        $request['login'] = 'x';
        $I->sendPost(self::ENDPOINT, $request);
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_BAD_REQUEST);
        $I->seeResponseIsJson();
        $I->seeResponseContains('The login minimum is 4');
    }

    public function tryToLoginUserWithInvalidLogin(ApiTester $I): void
    {
        $request = $this->exampleRequest();
        $request['login'] = 'invalid$login';
        $I->sendPost(self::ENDPOINT, $request);
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_BAD_REQUEST);
        $I->seeResponseIsJson();
        $I->seeResponseContains('The login only allows a-z, 0-9, _ and -');
    }

    public function tryToLoginUserWithEmptyPassword(ApiTester $I): void
    {
        $request = $this->exampleRequest();
        unset($request['password']);
        $I->sendPost(self::ENDPOINT, $request);
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_BAD_REQUEST);
        $I->seeResponseIsJson();
        $I->seeResponseContains('The password is required');
    }

    public function tryToLoginUserWithTooShortPassword(ApiTester $I): void
    {
        $request = $this->exampleRequest();
        $request['password'] = 'x';
        $I->sendPost(self::ENDPOINT, $request);
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_BAD_REQUEST);
        $I->seeResponseIsJson();
        $I->seeResponseContains('The password minimum is 6');
    }

    public function tryToLoginUserWithNotExistingLogin(ApiTester $I): void
    {
        $request = $this->exampleRequest();
        $I->sendPost(self::ENDPOINT, $request);
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_NOT_FOUND);
        $I->seeResponseIsJson();
        $I->seeResponseContains('User not found');
    }

    public function tryToLoginUserWithNotExistingEmail(ApiTester $I): void
    {
        $request = $this->exampleRequest();
        $request['email'] = 'not_existing_email@test.com';
        $I->sendPost(self::ENDPOINT, $request);
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_NOT_FOUND);
        $I->seeResponseIsJson();
        $I->seeResponseContains('User not found');
    }

    public function tryToLoginUserByLoginWithNotMatchingPassword(ApiTester $I): void
    {
        $request = $this->exampleRequest();
        $user = $I->haveUserInDatabase();
        $request['login'] = $user['login'];
        $request['password'] = 'INVALID';
        $I->sendPost(self::ENDPOINT, $request);
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_NOT_FOUND);
        $I->seeResponseIsJson();
        $I->seeResponseContains('User not found');
    }

    public function tryToLoginUserByEmailWithNotMatchingPassword(ApiTester $I): void
    {
        $request = $this->exampleRequest();
        $user = $I->haveUserInDatabase();
        $request['login'] = $user['email'];
        $request['password'] = 'INVALID';
        $I->sendPost(self::ENDPOINT, $request);
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_NOT_FOUND);
        $I->seeResponseIsJson();
        $I->seeResponseContains('User not found');
    }

    public function tryToSuccessfullyLoginUserWithLogin(ApiTester $I): void
    {
        $request = $this->exampleRequest();
        $user = $I->haveUserInDatabase();
        $request['login'] = $user['login'];
        $request['password'] = $user['password'];
        $I->sendPost(self::ENDPOINT, $request);
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_CREATED);
        $I->seeResponseIsJson();
        $accessToken = $I->grabDataFromResponseByJsonPath('$.accessToken.token')[0] ?? null;
        $refreshToken = $I->grabDataFromResponseByJsonPath('$.refreshToken.token')[0] ?? null;
        $I->seeInDatabase('access_tokens', ['user_id' => $user['id'], 'token' => $accessToken]);
        $I->seeInDatabase('refresh_tokens', ['user_id' => $user['id'], 'token' => $refreshToken]);
    }

    public function tryToSuccessfullyLoginUserWithEmail(ApiTester $I): void
    {
        $request = $this->exampleRequest();
        $user = $I->haveUserInDatabase();
        $request['login'] = $user['email'];
        $request['password'] = $user['password'];
        $I->sendPost(self::ENDPOINT, $request);
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_CREATED);
        $I->seeResponseIsJson();
        $accessToken = $I->grabDataFromResponseByJsonPath('$.accessToken.token')[0] ?? null;
        $refreshToken = $I->grabDataFromResponseByJsonPath('$.refreshToken.token')[0] ?? null;
        $I->seeInDatabase('access_tokens', ['user_id' => $user['id'], 'token' => $accessToken]);
        $I->seeInDatabase('refresh_tokens', ['user_id' => $user['id'], 'token' => $refreshToken]);
    }

    private function exampleRequest(): array
    {
        return [
            'login' => 'testUser',
            'password' => 'testUserPassword',
        ];
    }
}