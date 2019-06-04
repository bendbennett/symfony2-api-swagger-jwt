<?php

namespace App\Tests\Controller;

class UsersControllerTest extends AbstractController
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @test
     * @group UsersController::indexAction
     */
    public function itShouldReturn200AndAllUsers()
    {
        $userOnePassword = 'passwordOne';

        $userOne = $this->loadUser('userOne@companyOne.com', $userOnePassword, 'abc123', ['ROLE_ADMIN']);
        $this->loadUser('userTwo@companyTwo.com', 'passwordTwo', 'xyz789', ['ROLE_DIRECTOR']);

        $client = self::createClient();
        $jwt = $this->login($client, $userOne->getEmail(), $userOnePassword);

        $client->request(
            'GET',
            '/users',
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer $jwt")
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $responseAsArray = json_decode($client->getResponse()->getContent(), true);

        $this->assertCount(2, $responseAsArray);
    }

    /**
     * @test
     * @group UsersController::indexAction
     */
    public function itShouldReturn403WhenJwtRoleIsNotAdministrator()
    {
        $userOnePassword = 'passwordOne';
        $userOne = $this->loadUser('userOne@companyOne.com', $userOnePassword, 'abc123', ['ROLE_DIRECTOR']);

        $client = self::createClient();
        $jwt = $this->login($client, $userOne->getEmail(), $userOnePassword);

        $client->request(
            'GET',
            '/users',
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer $jwt")
        );

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     * @group UsersController::showAction
     */
    public function itShouldReturn200AndUserWhenLoggedInUserIsAdministrator()
    {
        $userOnePassword = 'passwordOne';
        $userOne = $this->loadUser('userOne@companyOne.com', $userOnePassword, 'abc123', ['ROLE_ADMIN']);

        $userTwoEmail = 'userTwo@companyTwo.com';
        $userTwoCompanyOneId = 'xyz789';
        $userTwo = $this->loadUser($userTwoEmail, 'passwordTwo', $userTwoCompanyOneId, ['User']);

        $client = self::createClient();
        $jwt = $this->login($client, $userOne->getEmail(), $userOnePassword);

        $client->request(
            'GET',
            '/users/' . $userTwo->getId(),
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer $jwt")
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $user = json_decode($client->getResponse()->getContent());

        $this->assertEquals($userTwo->getEmail(), $user->email);
        $this->assertEquals($userTwoCompanyOneId, $user->userCompanies[0]->companyId);
    }

    /**
     * @test
     * @group UsersController::showAction
     */
    public function itShouldReturn200AndUserWhenLoggedInUserIsDirectorInCompany()
    {
        $userOneEmail = 'userOne@companyOne.com';
        $userOnePassword = 'passwordOne';
        $userOne = $this->loadUser($userOneEmail, $userOnePassword, 'abc123', ['ROLE_DIRECTOR']);

        $userTwoEmail = 'userTwo@companyOne.com';
        $userTwoCompanyOneId = 'abc123';
        $userTwo = $this->loadUser($userTwoEmail, 'passwordTwo', $userTwoCompanyOneId, ['User']);

        $client = self::createClient();
        $jwt = $this->login($client, $userOne->getEmail(), $userOnePassword);

        $client->request(
            'GET',
            '/users/' . $userTwo->getId(),
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer $jwt")
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $user = json_decode($client->getResponse()->getContent());

        $this->assertEquals($userTwoEmail, $user->email);
        $this->assertEquals($userTwoCompanyOneId, $user->userCompanies[0]->companyId);
    }

    /**
     * @test
     * @group UsersController::showAction
     */
    public function itShouldReturn200AndUserWhenLoggedInUserIsUserInRequest()
    {
        $userOneEmail = 'userOne@companyOne.com';
        $userOnePassword = 'passwordOne';
        $userOneCompanyOneId = 'abc123';
        $userOne = $this->loadUser($userOneEmail, $userOnePassword, $userOneCompanyOneId, ['User']);

        $client = self::createClient();
        $jwt = $this->login($client, $userOne->getEmail(), $userOnePassword);

        $client->request(
            'GET',
            '/users/' . $userOne->getId(),
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer $jwt")
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $user = json_decode($client->getResponse()->getContent());

        $this->assertEquals($userOneEmail, $user->email);
        $this->assertEquals($userOneCompanyOneId, $user->userCompanies[0]->companyId);
    }

    /**
     * @test
     * @group UsersController::showAction
     */
    public function itShouldReturn403WhenLoggedInUserIsDirectorInDifferentCompany()
    {
        $userOne = $this->loadUser('userOne@companyOne.com', 'passwordOne', 'abc123', ['ROLE_ADMIN']);
        $userTwo = $this->loadUser('userTwo@companyTwo.com', 'passwordTwo', 'def789', ['ROLE_DIRECTOR']);

        $client = self::createClient();
        $jwt = $this->login($client, $userTwo->getEmail(), 'passwordTwo');

        $client->request(
            'GET',
            '/users/' . $userOne->getId(),
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer $jwt")
        );

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     * @group UsersController::searchAction
     */
    public function itShouldReturn200AndUserWhenLoggedInUserIsAdministratorForSearch()
    {
        $userOnePassword = 'passwordOne';
        $userOne = $this->loadUser('userOne@companyOne.com', $userOnePassword, 'abc123', ['ROLE_ADMIN']);

        $userTwoEmail = 'userTwo@companyTwo.com';
        $userTwoCompanyOneId = 'xyz789';
        $userTwo = $this->loadUser($userTwoEmail, 'passwordTwo', $userTwoCompanyOneId, ['User']);

        $client = self::createClient();
        $jwt = $this->login($client, $userOne->getEmail(), $userOnePassword);

        $client->request(
            'GET',
            '/users/email/' . $userTwo->getEmail(),
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer $jwt")
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $user = json_decode($client->getResponse()->getContent());

        $this->assertEquals($userTwo->getEmail(), $user->email);
        $this->assertEquals($userTwoCompanyOneId, $user->userCompanies[0]->companyId);
    }

    /**
     * @test
     * @group UsersController::searchAction
     */
    public function itShouldReturn403WhenLoggedInUserIsNotAdministratorForSearch()
    {
        $userOne = $this->loadUser('userOne@companyOne.com', 'passwordOne', 'abc123', ['ROLE_ADMIN']);
        $userTwo = $this->loadUser('userTwo@companyTwo.com', 'passwordTwo', 'def789', ['ROLE_DIRECTOR']);

        $client = self::createClient();
        $jwt = $this->login($client, $userTwo->getEmail(), 'passwordTwo');

        $client->request(
            'GET',
            '/users/email/' . $userOne->getEmail(),
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer $jwt")
        );

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     * @group UsersController::storeAction
     */
    public function itShouldReturn201AndUserWhenLoggedInUserIsAdministratorForStore()
    {
        $userOnePassword = 'passwordOne';
        $userOne = $this->loadUser('userOne@companyOne.com', $userOnePassword, 'abc123', ['ROLE_ADMIN']);

        $client = self::createClient();
        $jwt = $this->login($client, $userOne->getEmail(), $userOnePassword);

        $userTwoEmail = 'userTwo@companyTwo.com';
        $userTwoCompanyOneId = 'xyz798';

        $client->request(
            'POST',
            '/users',
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer $jwt"),
            json_encode([
                'email' => $userTwoEmail,
                'password' => 'passwordTwo',
                'userCompanies' => [
                    [
                        'companyId' => $userTwoCompanyOneId,
                        'roles' => ['ROLE_DIRECTOR']
                    ]
                ]
            ])
        );

        $this->assertEquals(201, $client->getResponse()->getStatusCode());
        $user = json_decode($client->getResponse()->getContent());

        $this->assertEquals($userTwoEmail, $user->email);
        $this->assertEquals($userTwoCompanyOneId, $user->userCompanies[0]->companyId);
    }

    /**
     * @test
     * @group UsersController::storeAction
     */
    public function itShouldReturn201AndUserWhenLoggedInUserIsDirectorInSameCompanyForStoreAction()
    {
        $userOnePassword = 'passwordOne';
        $userOne = $this->loadUser('userOne@companyOne.com', $userOnePassword, 'abc123', ['ROLE_DIRECTOR']);

        $client = self::createClient();
        $jwt = $this->login($client, $userOne->getEmail(), $userOnePassword);

        $userTwoEmail = 'userTwo@companyOne.com';
        $userTwoCompanyOneId = 'abc123';

        $client->request(
            'POST',
            '/users',
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer $jwt"),
            json_encode([
                'email' => $userTwoEmail,
                'password' => 'passwordTwo',
                'userCompanies' => [
                    [
                        'companyId' => $userTwoCompanyOneId,
                        'roles' => ['ROLE_DIRECTOR']
                    ]
                ]
            ])
        );

        $this->assertEquals(201, $client->getResponse()->getStatusCode());
        $user = json_decode($client->getResponse()->getContent());

        $this->assertEquals($userTwoEmail, $user->email);
        $this->assertEquals($userTwoCompanyOneId, $user->userCompanies[0]->companyId);
    }

    /**
     * @test
     * @group UsersController::storeAction
     */
    public function itShouldReturn403WhenLoggedInUserIsDirectorInDifferentCompanyForStoreAction()
    {
        $userOnePassword = 'passwordOne';
        $userOne = $this->loadUser('userOne@companyOne.com', $userOnePassword, 'abc123', ['ROLE_DIRECTOR']);

        $client = self::createClient();
        $jwt = $this->login($client, $userOne->getEmail(), $userOnePassword);

        $userTwoEmail = 'userTwo@companyTwo.com';
        $userTwoCompanyOneId = 'xyz789';

        $client->request(
            'POST',
            '/users',
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer $jwt"),
            json_encode([
                'email' => $userTwoEmail,
                'password' => 'passwordTwo',
                'userCompanies' => [
                    [
                        'companyId' => $userTwoCompanyOneId,
                        'roles' => ['ROLE_DIRECTOR']
                    ]
                ]
            ])
        );

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     * @group UsersController::editAction
     */
    public function itShouldReturn200AndUserForEditAction()
    {
        $userOne = $this->loadUser('userOne@companyOne.com', 'passwordOne', 'abc123', ['ROLE_DIRECTOR']);

        $client = self::createClient();
        $jwt = $this->login($client, $userOne->getEmail(), 'passwordOne');

        $userOneNewPassword = 'passwordTwo';
        $userOneNewEmail = 'newemail@companyOne.com';

        $client = self::createClient();
        $client->request(
            'PATCH',
            '/users/' . $userOne->getId(),
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer $jwt"),
            json_encode([
                'email' => $userOneNewEmail,
                'password' => $userOneNewPassword
            ])
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $serializerService = $this->bootedTestKernel->getContainer()->get('App\Service\SerializerService');
        $userManager = $this->bootedTestKernel->getContainer()->get('App\Manager\UserManager');

        $user = $serializerService->deserializeUserFromJson($client->getResponse()->getContent(), 'json', $userOne->getId());
        $userFromDb = $userManager->getUser($userOne->getId());

        $this->assertEquals($userOneNewEmail, $user->getEmail());
        $this->assertTrue($userManager->isPasswordValid($userFromDb, $userOneNewPassword));
    }

    /**
     * @test
     * @group UsersController::deleteAction
     */
    public function itShouldReturn204ForDeleteAction()
    {
        $userOne = $this->loadUser('userOne@companyOne.com', 'passwordOne', 'abc123', ['ROLE_ADMIN']);

        $client = self::createClient();
        $jwt = $this->login($client, $userOne->getEmail(), 'passwordOne');

        $client = self::createClient();
        $client->request(
            'DELETE',
            '/users/' . $userOne->getId(),
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer $jwt")
        );

        $this->assertEquals(204, $client->getResponse()->getStatusCode());

        $userManager = $this->bootedTestKernel->getContainer()->get('App\Manager\UserManager');
        
        $this->expectException(\Doctrine\ODM\MongoDB\DocumentNotFoundException::class);

        $userManager->getUser($userOne->getId());
    }
}
