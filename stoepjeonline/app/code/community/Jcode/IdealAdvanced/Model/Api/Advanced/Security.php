<?php
/**
* J!Code WebDevelopment
*
* @title 		Magento payment module for iDeal Advanced
* @category 	J!Code
* @package 		Jcode_Community
* @author 		Jeroen Bleijenberg / J!Code WebDevelopment <support@jcode.nl>
* @license  	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/
class Jcode_IdealAdvanced_Model_Api_Advanced_Security
{
    function createCertFingerprint($filename)
    {
        if(is_readable($filename)) {
            $cert = file_get_contents($filename);
        } else {
            return false;
        }

        $data = openssl_x509_read($cert);

        if(!openssl_x509_export($data, $data)) {

            return false;
        }

        $data = str_replace("-----BEGIN CERTIFICATE-----", "", $data);
        $data = str_replace("-----END CERTIFICATE-----", "", $data);

        $data = base64_decode($data);

        $fingerprint = sha1($data);

        $fingerprint = strtoupper( $fingerprint );

        return $fingerprint;
    }

    function signMessage($priv_keyfile, $key_pass, $data)
    {
        $data = preg_replace("/\s/","",$data);
        if (is_readable($priv_keyfile)) {
            $priv_key = file_get_contents($priv_keyfile);

            $params = array($priv_key, $key_pass);
            $pkeyid = openssl_pkey_get_private($params);

            openssl_sign($data, $signature, $pkeyid);

            openssl_free_key($pkeyid);

            return $signature;
        } else {
            return false;
        }
    }

    function verifyMessage($certfile, $data, $signature)
    {
        $ok = 0;
        if (is_readable($certfile)) {
            $cert = file_get_contents($certfile);
        } else {
            return false;
        }

        $pubkeyid = openssl_get_publickey($cert);

        $ok = openssl_verify($data, $signature, $pubkeyid);

        openssl_free_key($pubkeyid);

        return $ok;
    }

    function getCertificateName($fingerprint, $config)
    {
        $count = 0;

        if (isset($config["CERTIFICATE" . $count])) {
            $certFilename = $config["CERTIFICATE" . $count];
        } else {
            return false;
        }

        while( isset($certFilename) ) {
            $buff = $this->createCertFingerprint($certFilename);

            if( $fingerprint == $buff ) {
                return $certFilename;
            }

            $count+=1;
            if (isset($config["CERTIFICATE" . $count])) {
                $certFilename = $config["CERTIFICATE" . $count];
            } else {
                return false;
            }
        }

        return false;
    }
}