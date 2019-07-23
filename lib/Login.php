<?php
/**
 * Created by PhpStorm.
 * User: nilskoppelmann
 * Date: 05.09.18
 * Time: 12:23
 */

include __DIR__ . '/../lib/autoload.php';

class Login
{

    private $status = false;

    private $kas = null;
    private $ldap = null;
    private $imap = null;

    private $email = "";
    private $password = "";

    private $kasUser = null;
    private $ldapUser = null;

    public function __construct($email, $password)
    {

        // pass authentication params

        $this->email = $email;
        $this->password = $password;

        // initiate authentication classes

        $this->kas = new Kas();

        $this->ldap = new Ldap();

        $this->imap = new Imap($this->email, $this->password);

        // get user from Kas
        $this->kasUser = $this->kas->findUser($this->email);

    }

    public function auth()
    {



        // check authentication against all services



        // detect if ldap account exists





    }


}