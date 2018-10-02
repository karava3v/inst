<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 24.08.18
 * Time: 1:19 PM
 */

namespace instagram\helpers;


class Request
{
    /**
     * @var string
     */
    protected $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36';

    /**
     * @var resource
     */
    protected $curl;


    /**
     * @param $options array
     * @return Request
     */

    public function setOptions( $options = [] ) {

        //default options
        curl_setopt($this->curl,CURLOPT_USERAGENT, $this->userAgent);

        $header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,";
        $header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
        $header[] = "Cache-Control: max-age=0";
        $header[] = "Connection: keep-alive";
        $header[] = "Keep-Alive: 300";
        $header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
        $header[] = "Accept-Language: en-us,en;q=0.5";
        $header[] = "Pragma: ";

        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $header);

        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl,   CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($this->curl,   CURLOPT_FOLLOWLOCATION, 1);

        //override default options

        foreach ( $options as $option => $value ) {
            curl_setopt( $this->curl, $option, $value );
        }

        return $this;
    }


    public function send($url) {

        $this->curl = curl_init();

        $this->setOptions([CURLOPT_URL => $url]);

        $result = curl_exec($this->curl);

        curl_close( $this->curl );

        return $result;
    }

}

