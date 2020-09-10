<?php

namespace App\Tests\Functional;

class UserControllerTest extends AbstractWebTestCase
{

    public function testEditProfile()
    {
        $crawler = $this->client->request('GET', '/users/editProfile');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('Speichern')->form();
        self::assertEquals('rick@rick.com', $form->getValues()['edit_profile[email]']);
        self::assertEquals('rick', $form->getValues()['edit_profile[username]']);
        $form->setValues([
            'edit_profile[email]' => 'invalidmail',
            'edit_profile[username]' => 'changedUsername',
        ]);
        $crawler = $this->client->submit($form);

        $form = $crawler->selectButton('Speichern')->form();
        self::assertEquals('invalidmail', $form->getValues()['edit_profile[email]']);
        self::assertEquals('changedUsername', $form->getValues()['edit_profile[username]']);
        self::assertTrue(strpos($this->client->getResponse()->getContent(), 'This value is not a valid email address.') > 0);

        $form->setValues(['edit_profile[email]' => 'newmail@gmail.com']);
        $this->client->submit($form);
        self::assertTrue($this->client->getResponse()->isRedirect('/'));

        $this->client->setServerParameter('PHP_AUTH_USER', 'changedUsername');
        $crawler = $this->client->request('GET', '/');
        self::assertEquals('Dein Profil wurde erfolgreich gespeichert!', $crawler->filter('.ui.positive.message')->text());

        $crawler = $this->client->request('GET', '/users/editProfile');
        $form = $crawler->selectButton('Speichern')->form();
        self::assertEquals('newmail@gmail.com', $form->getValues()['edit_profile[email]']);
        self::assertEquals('changedUsername', $form->getValues()['edit_profile[username]']);
    }

    public function testChangePassword()
    {
        $crawler = $this->client->request('GET', '/users/changePassword');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('Speichern')->form();
        $form->setValues([
            'change_password[oldPassword]' => 'invalid',
            'change_password[newPassword][first]' => 'abc',
            'change_password[newPassword][second]' => 'abc',
        ]);
        $crawler = $this->client->submit($form);
        $this->assertResponseContains('Da hast du dich scheinbar vertippt, das aktuelle Passwort stimmt nicht!');

        $form = $crawler->selectButton('Speichern')->form();
        $form->setValues([
            'change_password[oldPassword]' => 'supersecurepassword!',
            'change_password[newPassword][first]' => 'abc',
            'change_password[newPassword][second]' => 'abcde',
        ]);
        $crawler = $this->client->submit($form);
        $this->assertResponseContains('Die Passwörter müssen übereinstimmen!');

        $form = $crawler->selectButton('Speichern')->form();
        $form->setValues([
            'change_password[oldPassword]' => 'supersecurepassword!',
            'change_password[newPassword][first]' => 'abc',
            'change_password[newPassword][second]' => 'abc',
        ]);
        $crawler = $this->client->submit($form);
        $this->assertResponseContains('Dein neues Passwort muss mindestens 6 Zeichen lang sein!');

        $form = $crawler->selectButton('Speichern')->form();
        $form->setValues([
            'change_password[oldPassword]' => 'supersecurepassword!',
            'change_password[newPassword][first]' => 'abcdef',
            'change_password[newPassword][second]' => 'abcdef',
        ]);
        $this->client->submit($form);

        self::assertTrue($this->client->getResponse()->isRedirect('/'));

        $this->client->request('GET', '/');
        $this->assertSuccessMessage('Dein Passwort wurde erfolgreich geändert!');
    }


}
