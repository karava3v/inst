<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 02.10.18
 * Time: 5:16 PM
 */

namespace instagram\helpers;

class Frequency
{
    /** @var array */
    protected $list;

    /** @var array */
    protected $frequency;

    /** @var Request */
    private $request;

    /** @var array */
    protected $users;

    /** @var $new_profiles array */
    protected $new_profiles = [];

    /** @var array */
    protected $result;

    /** @var int */
    protected $index;


    /**
     * Frequency constructor.
     * @param array $list
     * @param Request $request
     * @param int $index array's index with username
     */
    public function __construct($list, $request, $index)
    {
        $this->request = $request;
        $this->list = $list;
        $this->index = $index;
    }

    /**
     * @param $users array
     */
    public function setUsers($users) {
        $this->users = $users;
    }

    public function getUsers() {
        return $this->users;
    }

    /**
     * @throws \Exception
     * @return array
     */
    public function calculate( ) {

        $frequency = [];

        foreach ($this->list as $itemsCategory => $itemsList) {

            foreach ($itemsList as $itemID => $item) {
                (isset($frequency[$item[ $this->index ]])) ? $frequency[$item[$this->index]]++ : $frequency[$item[$this->index]] = 1;
            }
        }

        arsort($frequency);
        $frequency = array_slice($frequency, 0, 10);

        $this->setResult($frequency);

        return $this->getResult();
    }

    public function setResult($result) {
        $this->result = $result;
    }

    public function getResult() {
        return $this->result;
    }

    /**
     * @param $owner_username string
     * @return array (users from comments.json) which not found in users array
     */
    public function searchUsers( $owner_username ) {

        foreach ($this->list as $itemsCategory => $itemsList) {

            foreach ($itemsList as $itemID => $item) {

                //filter by owners username
                if ($item[ $this->index ] == $owner_username)
                    continue;

                if (!array_key_exists($item[$this->index], $this->users)) {
                    $this->new_profiles[] = $item[$this->index];
                }
            }
        }

        return $this->new_profiles;
    }

}