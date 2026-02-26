<?php

declare(strict_types=1);

namespace App\Model;

use Nette;
use Nette\Security\SimpleAuthenticator;
use Nette\Security;
class MyAuthenticator implements Nette\Security\Authenticator
{
    private $database;
    private $passwords;
    public function __construct
    (
        $database,
        Nette\Security\Passwords $passwords
	)
    {
        $this->database = $database;
        $this->passwords = $passwords;
    }

    public function authenticate(string $username, string $password): Nette\Security\SimpleIdentity
    {
        $row = $this->database->table('users')
            ->where('username', $username)
            ->fetch();

        if (!$row) {
            throw new Nette\Security\AuthenticationException('Uživatel nebyl nalezen nebo není aktivní');
        }

        if (!$this->passwords->verify($password, $row->password)) {
            throw new Nette\Security\AuthenticationException('Nesprávné heslo.');
        }

        return new Nette\Security\SimpleIdentity(
            $row->id,
            null,
            ['id' => $row->id,'first_name' => $row->first_name, 'last_name' => $row->last_name, 'nick_name' => $row->nick_name, 'username' => $row->username],
        );
    }
}