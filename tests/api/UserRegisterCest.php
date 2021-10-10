<?php

use App\Security\PasswordEncoderInterface;
use Codeception\Example;
use Fig\Http\Message\StatusCodeInterface;

class UserRegisterCest
{
    private const ENDPOINT = '/user/register';

    public function tryToRegisterUserWithEmptyLogin(ApiTester $I): void
    {
        $request = $this->exampleRequest();
        unset($request['login']);
        $I->sendPost(self::ENDPOINT, $request);
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_BAD_REQUEST);
        $I->seeResponseIsJson();
        $I->seeResponseContains('The login is required');
    }

    public function tryToRegisterUserWithTooShortLogin(ApiTester $I): void
    {
        $request = $this->exampleRequest();
        $request['login'] = 'x';
        $I->sendPost(self::ENDPOINT, $request);
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_BAD_REQUEST);
        $I->seeResponseIsJson();
        $I->seeResponseContains('The login minimum is 4');
    }

    /**
     * @param ApiTester $I
     * @param Example $example
     * @dataProvider invalidLoginsProvider
     */
    public function tryToRegisterUserWithInvalidLogin(ApiTester $I, Example $example): void
    {
        $request = $this->exampleRequest();
        $request['login'] = $example['login'];
        $I->sendPost(self::ENDPOINT, $request);
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_BAD_REQUEST);
        $I->seeResponseIsJson();
        $I->seeResponseContains('The login only allows a-z, 0-9, _ and -');
    }

    public function tryToRegisterUserWithAlreadyExistsLoginAndLogin(ApiTester $I): void
    {
        $request = $this->exampleRequest();
        $I->sendPost(self::ENDPOINT, $request);
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_CREATED);
        $I->seeResponseIsJson();
        $I->sendPost(self::ENDPOINT, $request);
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_CONFLICT);
        $I->seeResponseContains('login ' . $request['login'] . ' has been used');
        $I->seeResponseContains('email ' . $request['email'] . ' has been used');
    }

    public function tryToRegisterUserWithEmptyEmail(ApiTester $I): void
    {
        $request = $this->exampleRequest();
        unset($request['email']);
        $I->sendPost(self::ENDPOINT, $request);
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_BAD_REQUEST);
        $I->seeResponseContains('The email is required');
    }

    /**
     * @param ApiTester $I
     * @param Example $example
     * @dataProvider invalidEmailsProvider
     */
    public function tryToRegisterUserWithInvalidEmail(ApiTester $I, Example $example): void
    {
        $request = $this->exampleRequest();
        $request['email'] = $example['email'];
        $I->sendPost(self::ENDPOINT, $request);
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_BAD_REQUEST);
        $I->seeResponseContains('The email is not valid email');
    }

    public function tryToRegisterUserWithEmptyPassword(ApiTester $I): void
    {
        $request = $this->exampleRequest();
        unset($request['password']);
        $I->sendPost(self::ENDPOINT, $request);
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_BAD_REQUEST);
        $I->seeResponseContains('The password is required');
    }

    public function tryToRegisterUserWithTooShortPassword(ApiTester $I): void
    {
        $request = $this->exampleRequest();
        $request['password'] = 'x';
        $I->sendPost(self::ENDPOINT, $request);
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_BAD_REQUEST);
        $I->seeResponseContains('The password minimum is 6');
    }

    public function tryToRegisterUserWithEmptyDisplayName(ApiTester $I): void
    {
        $request = $this->exampleRequest();
        unset($request['displayName']);
        $I->sendPost(self::ENDPOINT, $request);
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_BAD_REQUEST);
        $I->seeResponseContains('The displayName is required');
    }

    public function tryToRegisterUserWithTooShortDisplayName(ApiTester $I): void
    {
        $request = $this->exampleRequest();
        $request['displayName'] = 'x';
        $I->sendPost(self::ENDPOINT, $request);
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_BAD_REQUEST);
        $I->seeResponseIsJson();
        $I->seeResponseContains('The displayName minimum is 4');
    }

    /**
     * @param ApiTester $I
     * @param Example $example
     * @dataProvider invalidDisplayNamesProvider
     */
    public function tryToRegisterUserWithInvalidDisplayName(ApiTester $I, Example $example): void
    {
        $request = $this->exampleRequest();
        $request['displayName'] = $example['displayName'];
        $I->sendPost(self::ENDPOINT, $request);
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_BAD_REQUEST);
        $I->seeResponseIsJson();
        $I->seeResponseContains('The displayName may only allows alphabet and spaces');
    }

    public function tryToRegisterUserCorrectly(ApiTester $I): void
    {
        $request = $this->exampleRequest();
        $I->sendPost(self::ENDPOINT, $request);
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_CREATED);
        $I->seeResponseIsJson();
        $createdId = (int)$I->grabDataFromResponseByJsonPath('$.id')[0];
        $I->seeInDatabase('users', [
            'id' => $createdId,
            'login' => $request['login'],
            'email' => $request['email'],
            'display_name' => $request['displayName']
        ]);
        $password = $I->grabFromDatabase('users', 'password', [
            'id' => $createdId
        ]);
        /** @var PasswordEncoderInterface $passwordEncoder */
        $passwordEncoder = $I->grabServiceFromContainer(PasswordEncoderInterface::class);
        $I->assertTrue($passwordEncoder->verify($request['password'], $password), 'Password validation failed');
    }

    private function invalidLoginsProvider(): array
    {
        return [
            ['login' => '2412@'],
            ['login' => 'Test@!%^!#'],
            ['login' => '*&^$(&*#@!('],
            ['login' => ':;.,-=-='],
            ['login' => 'Test 123 ^*@'],
            ['login' => 'Test 123'],
            ['login' => 'Test"123'],
            ['login' => 'test%%'],
        ];
    }

    private function invalidEmailsProvider(): array
    {
        return [
            ['email' => 'x'],
            ['email' => 'testttt'],
            ['email' => '%^&*'],
            ['email' => '123@'],
            ['email' => '123@test.'],
            ['email' => 'test@.com'],
            ['email' => 'test123@.com'],
            ['email' => '879231@com'],
            ['email' => 'test@com'],
        ];
    }

    private function invalidDisplayNamesProvider(): array
    {
        return [
            ['displayName' => '2412342134321'],
            ['displayName' => 'Test@!%^!#'],
            ['displayName' => '*&^$(&*#@!('],
            ['displayName' => '::;;.,-=-='],
            ['displayName' => 'Test 123 ^*@'],
        ];
    }

    private function exampleRequest(): array
    {
        $generatedNum = random_int(1, 99999);
        return [
            'login' => 'testUser_' . $generatedNum,
            'email' => 'testUser_' . $generatedNum . '@test.com',
            'password' => 'testUserPassword',
            'displayName' => 'Test User'
        ];
    }
}