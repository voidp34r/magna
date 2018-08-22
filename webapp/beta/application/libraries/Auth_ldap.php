<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Auth_Ldap
{

    function __construct ()
    {
        $this->ci = & get_instance();
        $this->ci->load->library('session');
        $this->ci->load->config('auth_ldap');
        $this->_init();
    }

    /**
     * @access private
     * @return void
     */
    private function _init ()
    {
        if (!function_exists('ldap_connect'))
        {
            show_error('LDAP functionality not present.  Either load the module ldap php module or use a php with ldap support compiled in.');
        }
        $this->hosts = $this->ci->config->item('hosts');
        $this->ports = $this->ci->config->item('ports');
        $this->basedn = $this->ci->config->item('basedn');
        $this->account_ou = $this->ci->config->item('account_ou');
        $this->login_attribute = $this->ci->config->item('login_attribute');
        $this->use_ad = $this->ci->config->item('use_ad');
        $this->ad_domain = $this->ci->config->item('ad_domain');
        $this->proxy_user = $this->ci->config->item('proxy_user');
        $this->proxy_pass = $this->ci->config->item('proxy_pass');
        $this->roles = $this->ci->config->item('roles');
        $this->auditlog = $this->ci->config->item('auditlog');
        $this->member_attribute = $this->ci->config->item('member_attribute');
    }

    /**
     * @access public
     * @param string $username
     * @param string $password
     * @return bool 
     */
    function login ($username, $password)
    {
        $user_info = $this->_authenticate($username, $password);
        if (!empty($user_info))
        {
            if (empty($user_info['role']) && !empty($this->roles))
            {
                show_error($username . ' succssfully authenticated, but is not allowed because the username was not found in an allowed access group.');
            }
            $customdata = array(
                'usuario' => $username,
                'usuario_nome' => strtoupper($user_info['cn']),
                'usuario_email' => $user_info['mail'],
                'logado' => TRUE
            );
            $this->ci->session->set_userdata($customdata);
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * @access public
     * @return bool
     */
    function is_authenticated ()
    {
        if ($this->ci->session->userdata('logado'))
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * @access public
     */
    function logout ()
    {
        $this->ci->session->set_userdata(array('logado' => FALSE));
        $this->ci->session->sess_destroy();
    }

    /**
     * @access private
     * @param string $username
     * @param string $password
     * @return array 
     */
    private function _authenticate ($username, $password)
    {
        $needed_attrs = array('dn', $this->login_attribute, 'cn', 'mail');

        foreach ($this->hosts as $host)
        {
            $this->ldapconn = ldap_connect($host);
            if ($this->ldapconn)
            {
                break;
            }
        }
        if (!$this->ldapconn)
        {
            show_error('Error connecting to your LDAP server(s).  Please check the connection and try again.');
        }
        ldap_set_option($this->ldapconn, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($this->ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);

        $bind = @ldap_bind($this->ldapconn, $username, $password);
        if (!$bind)
        {
            return FALSE;
        }

        $filter = '(' . $this->login_attribute . '=' . substr(strstr($username, '\\'), 1) . ')';

        $search = @ldap_search($this->ldapconn, $this->basedn, $filter, $needed_attrs);
        if (!$search)
        {
            return FALSE;
        }
        $entries = @ldap_get_entries($this->ldapconn, $search);
        if (!$entries)
        {
            return FALSE;
        }
        $binddn = $entries[0]['dn'];

        $bind = @ldap_bind($this->ldapconn, $binddn, $password);
        if (!$bind)
        {
            return FALSE;
        }
        $cn = $entries[0]['cn'][0];
        $dn = stripslashes($entries[0]['dn']);
        $id = $entries[0][$this->login_attribute][0];
        $mail = !empty($entries[0]['mail'][0]) ? $entries[0]['mail'][0] : '';

        return array(
            'cn' => $cn,
            'dn' => $dn,
            'id' => $id,
            'mail' => $mail,
            'role' => $this->_get_role($id)
        );
    }

    /**
     * @access private
     * @param string $str
     * @param bool $for_dn
     * @return string 
     */
    private function ldap_escape ($str, $for_dn = false)
    {
        if ($for_dn)
        {
            $metaChars = array(',', '=', '+', '<', '>', ';', '\\', '"', '#');
        }
        else
        {
            $metaChars = array('*', '(', ')', '\\', chr(0));
        }
        $quotedMetaChars = array();
        foreach ($metaChars as $key => $value)
        {
            $quotedMetaChars[$key] = '\\' . str_pad(dechex(ord($value)), 2, '0');
        }
        $str = str_replace($metaChars, $quotedMetaChars, $str); //replace them
        return ($str);
    }

    /**
     * @access private
     * @param string $username
     * @return int
     */
    private function _get_role ($username)
    {
        $filter = '(' . $this->member_attribute . '=' . $username . ')';
        $search = @ldap_search($this->ldapconn, $this->basedn, $filter, array('cn'));
        if (!$search)
        {
            return FALSE;
        }
        $results = @ldap_get_entries($this->ldapconn, $search);
        if ($results['count'] != 0)
        {
            for ($i = 0; $i < $results['count']; $i++)
            {
                $role = array_search($results[$i]['cn'][0], $this->roles);
                if ($role !== FALSE)
                {
                    return $role;
                }
            }
        }
        return FALSE;
    }

}
