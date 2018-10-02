<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 28.08.18
 * Time: 11:21 AM
 */

namespace instagram\models\connections;


abstract class Connection
{

    /**
     * @var string $nickname
     */
    protected $nickname;

    /**
     * @var string $date
     */
    protected $date;

    /**
     * Connection constructor.
     *
     * @param string $nickname
     * @param string $date
     */
    public function __construct($nickname, $date)
    {
        $this->setDate($date);
        $this->setNickname($nickname);
    }

    /**
     * @return string
     */
    public function getNickname()
    {
        return $this->nickname;
    }

    /**
     * @param string $nickname
     */
    public function setNickname($nickname)
    {
        $this->nickname = $nickname;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param string $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }
}