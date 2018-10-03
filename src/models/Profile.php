<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 24.08.18
 * Time: 12:49 PM
 */

namespace instagram\models;

use instagram\helpers\Request;

/**
 * Class User
 * For obtain data from instagram web-interface.
 * @package instagram\models
 */

class Profile
{
    /**
     * @var $profile_pic_url string
     */
    public $profile_pic_url_hd;
    
    /**
     * @var Request;
     */
    protected $request;

    /**
     * @var string
     */
    public $biography;

    /**
     * @var string
     */
    public $date_joined;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $gender;

    /**
     * @var bool
     */
    public $private_account;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $phone_number;

    /**
     * @var string
     */
    public $profile_pic_url;

    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $connected_facebook_account_date_of_birth;

    /**
     * @var string
     */
    public $date_of_birth;

    /**
     * @var string
     */
    public $business_category;

    /**
     * @var string
     */
    public $business_email;

    /**
     * @var string
     */
    public $business_facebook_page_name;

    /**
     * @param $request
     *
     * @return Profile
     */
    public function setRequest( $request ) {
        $this->request = $request;

        return $this;
    }

    /**
     * Fetch data from instagram and return user array
     *
     * @param string $username
     * @return array
     * @throws \Exception if user not found
     */
    public function getFromInstagram( $username ) {
        if ( !$this->request )
            throw new \Error('No request class defined');

        $html = $this->request->send("https://www.instagram.com/$username");

        $arr = explode('window._sharedData = ',$html);

        if ( empty( $arr[1] ) ) {
            throw new \Exception("Can't fetch html from @$username");
        }

        $arr = explode(';</script>',$arr[1]);
        $obj = json_decode($arr[0] , true);

        if ( empty( $obj['entry_data']['ProfilePage'][0]['graphql']['user']) ) {
            throw new \Exception('No user found: @' . $username);
        }

        return $obj['entry_data']['ProfilePage'][0]['graphql']['user'];
    }


    /**
     *
     * @param $user array
     *
     * @return Profile
     * @throws \Exception if user's array wasn't filled
     */
    public function fill( $user ) {

        if ( !is_array($user) && (empty( $user['username'] )) )
            throw new \Exception('No user');

        foreach ( $user as $item => $value) {

            if (property_exists($this, $item)) {
                $method = 'set'.implode('',array_map('ucfirst', explode('_', $item)));

                if ( method_exists($this, $method) ) {
                    $this->{$method}($value);
                }
           }
        }

        return $this;
    }

    /**
     * @param string $profile_pic_url_hd
     */
    public function setProfilePicUrlHd($profile_pic_url_hd)
    {
        $this->profile_pic_url_hd = $profile_pic_url_hd;
    }


    /**
     * @param string $biography
     */
    public function setBiography($biography)
    {
        $this->biography = $biography;
    }

    /**
     * @param string $date_joined
     */
    public function setDateJoined($date_joined)
    {
        $this->date_joined = $date_joined;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }


    /**
     * @param string $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * @return bool
     */
    public function isPrivateAccount()
    {
        return $this->private_account;
    }

    /**
     * @param bool $private_account
     */
    public function setPrivateAccount($private_account)
    {
        $this->private_account = $private_account;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $phone_number
     */
    public function setPhoneNumber($phone_number)
    {
        $this->phone_number = $phone_number;
    }

    /**
     * @param string $profile_pic_url
     */
    public function setProfilePicUrl($profile_pic_url)
    {
        $this->profile_pic_url = $profile_pic_url;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @param string $connected_facebook_account_date_of_birth
     */
    public function setConnectedFacebookAccountDateOfBirth($connected_facebook_account_date_of_birth)
    {
        $this->connected_facebook_account_date_of_birth = $connected_facebook_account_date_of_birth;
    }

    /**
     * @param string $date_of_birth
     */
    public function setDateOfBirth($date_of_birth)
    {
        $this->date_of_birth = $date_of_birth;
    }

    /**
     * @param string $business_category
     */
    public function setBusinessCategory($business_category)
    {
        $this->business_category = $business_category;
    }

    /**
     * @param string $business_email
     */
    public function setBusinessEmail($business_email)
    {
        $this->business_email = $business_email;
    }


    /**
     * @param string $business_facebook_page_name
     */
    public function setBusinessFacebookPageName($business_facebook_page_name)
    {
        $this->business_facebook_page_name = $business_facebook_page_name;
    }


    /**
     *
     * @param $username string username for search
     * @param $list Profile[] all initialized profiles
     *
     * @return boolean
     */
    public function searchInList($username, $list) {
        return array_key_exists($username, $list);
    }

}