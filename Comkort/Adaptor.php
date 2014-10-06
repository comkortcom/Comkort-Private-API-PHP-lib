<?php

Class Comkort_Adaptor {
    private $ch = NULL;

    private $secret_key;
    private $public_key;
    private $host = 'https://api.comkort.com/v1/private/';

    public function __construct($secret_key, $public_key, $host = NULL) {
        $this->secret_key = $secret_key;
        $this->public_key = $public_key;

        if(!is_null($host)) {
            $this->host = $host;
        }
    }

    public function api_query($url, $method, array $req = array()) {
        // generate the POST data string
        $post_data = http_build_query($req, '', '&');

        $sign = hash_hmac("sha512", $post_data, $this->secret_key);
        $mt = explode(' ', microtime());
        // generate the extra headers
        $headers = array(
            'sign: '.$sign,
            'apikey: '.$this->public_key,
            'nonce: '. substr($mt[1],-6).substr($mt[0], 2, 3) // You can increase nonce anyway you want
        );

        if (is_null($this->ch)) {
            $this->ch = curl_init();
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($this->ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; Comkort API PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
        }

        switch($method) {
            case 'GET':
                if(!empty($post_data))
                    $url .= '?'.$post_data;
                break;
            default:
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $post_data);
        }

        curl_setopt($this->ch, CURLOPT_URL, $this->host.$url);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $res = curl_exec($this->ch);

        if ($res === false) throw new Exception('Could not get reply: '.curl_error($this->ch));
        curl_close($this->ch);
        $this->ch = NULL;
        return $res;
    }
}