<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 28.08.18
 * Time: 11:17 AM
 */

namespace instagram\models\comments;


abstract class Comment
{

    /**
     * @var $date string
     */
    protected $date;

    /**
     * @var $text string
     */
    protected $text;

    /**
     * @var $recipient string
     */
    protected $recipient;

    public function __construct($date, $text, $recipient)
    {
        $this->setDate($date);
        $this->setText($text);
        $this->setRecipient($recipient);
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

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @param string $recipient
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;
    }


}