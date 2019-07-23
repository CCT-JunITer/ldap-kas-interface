<?php
/**
 * Created by PhpStorm.
 * User: nilskoppelmann
 * Date: 05.09.18
 * Time: 01:05
 */

/*
 * Class used for IMAP Authentication against
 * the all-inkl IMAP Server
 */

require_once __DIR__ . '/../config.php';

class Imap
{

    private $server;
    private $port;

    private $mailbox;

    private $user;
    private $pass;

    public function __construct($email, $password)
    {
        global $config;

        $this->server = $config['imap']['host'];
        $this->port = $config['imap']['port'];

        $this->user = $email;
        $this->pass = $password;

        $this->mailbox = '{' . $this->server . ':' . $this->port . '}';

    }

    public function auth()
    {

        $mbox = imap_open($this->mailbox, $this->user, $this->pass);

        return $mbox !== false;

    }

}