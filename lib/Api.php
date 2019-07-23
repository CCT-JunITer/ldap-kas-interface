<?php
/**
 * Created by PhpStorm.
 * User: nilskoppelmann
 * Date: 05.09.18
 * Time: 01:12
 */

class Api
{

    /*
     * This class can be used to:
     * - create a new user
     * - modify an existing user
     * -
     */

    private $user;
    private $kas;
    private $ldap;

    public function __construct()
    {

        $this->kas = new Kas();
        $this->ldap = new Ldap();

    }

    public function changePwd($email, $newPassword)
    {

//        $this->kas->changePwd($email, $newPassword);
        $this->ldap->changePwd($email, $newPassword);

    }

}