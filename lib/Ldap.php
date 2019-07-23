<?php
/**
 * Created by PhpStorm.
 * User: nilskoppelmann
 * Date: 05.09.18
 * Time: 01:05
 */

require_once __DIR__ . '/../config.php';

class Ldap
{

    private $base_dn;
    private $ip;
    private $ldaprdn;
    private $ldappass;
    private $ldapconn;

    public function __construct()
    {

        global $config;

        $this->base_dn = $config['ldap']['base_dn'];
        $this->ldaprdn = $config['ldap']['bind'] . ',' . $this->base_dn;
        $this->ldappass = $config['ldap']['pass'];
        $this->ip = $config['ldap']['ip'];

        $this->ldapconn = ldap_connect($this->ip)
        or die("Keine Verbindung zum LDAP server möglich.");

        ldap_set_option($this->ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);

        // binden zum ldap server
        $ldapbind = ldap_bind($this->ldapconn, $this->ldaprdn, $this->ldappass);

        // Bindung überpfrüfen
        if (!$ldapbind) {
            ldap_get_option($this->ldapconn, LDAP_OPT_DIAGNOSTIC_MESSAGE, $err);
            echo nl2br("ldap_get_option: $err");
            die();
        }

    }

    public function __destruct()
    {
        ldap_close($this->ldapconn);
    }

    public function changePwd($email, $newPassword)
    {

        $user = $this->findUser($email);

        if ($user == false) return false;

        $uid = $user[0]['uid'][0];

        $dn = "uid=" . $uid . ',' . $this->base_dn;
        $newEntry = array('userpassword' => $newPassword);

        return ldap_mod_replace($this->ldapconn, $dn, $newEntry);

    }

    public function findUser($email)
    {

        $filter = "(mail=$email)";

        $sr = ldap_search($this->ldapconn, $this->base_dn, $filter);

        $data = ldap_get_entries($this->ldapconn, $sr);

        if (ldap_count_entries($this->ldapconn, $sr) <= 0) return false;

        return $data;

    }

    public function addUser($user, $dn)
    {
        return ldap_add($this->ldapconn, $dn, $user);
    }

    public function addUserToRessort($email, $ressort)
    {
        $user = $this->findUser($email);
        $cn = $user[0]['cn'][0];

        $dn = 'cn=' . $ressort . ',ou=Ressorts,ou=Groups,' . $this->base_dn;
        $entry['member'] = 'cn=' . $cn . ',ou=People,' . $this->base_dn;
        ldap_mod_add($this->ldapconn, $dn, $entry);
    }

    public function addUserStatus($email, $status)
    {
        $user = $this->findUser($email);
        $cn = $user[0]['cn'][0];
        $dn = 'cn=' . $status . ',ou=Status,ou=Groups,' . $this->base_dn;
        $entry['member'] = 'cn=' . $cn . ',ou=People,' . $this->base_dn;
        ldap_mod_add($this->ldapconn, $dn, $entry);
    }

    public function addMemberOf($email, $dn)
    {
        $user = $this->findUser($email);
        $cn = $user[0]['cn'][0];
        $entry['member'] = 'cn=' . $cn . ',ou=People,' . $this->base_dn;
        ldap_mod_add($this->ldapconn, $dn, $entry);
    }

    public function getConn()
    {
        return $this->ldapconn;
    }

    /**
     * @return mixed
     */
    public function getBaseDn()
    {
        return $this->base_dn;
    }


}