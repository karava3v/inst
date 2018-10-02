<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 28.08.18
 * Time: 4:17 PM
 */

namespace instagram\models;


class Contact
{

    /**
     * @var string $first_name
     */
    protected $first_name;

    /**
     * @var string $last_name
     */
    protected $last_name;

    /**
     * @var string $contact
     */
    protected $contact;

    /**
     * Contact constructor.
     *
     * @param string $first_name
     * @param string $last_name
     * @param string $contact
     */
    public function __construct($first_name, $last_name, $contact)
    {
        $this->setFirstName($first_name);
        $this->setContact($contact);
        $this->setLastName($last_name);
    }


    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * @param string $first_name
     */
    public function setFirstName($first_name)
    {
        $this->first_name = $first_name;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * @param string $last_name
     */
    public function setLastName($last_name)
    {
        $this->last_name = $last_name;
    }

    /**
     * @return string
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param string $contact
     */
    public function setContact($contact)
    {
        $this->contact = $contact;
    }

}