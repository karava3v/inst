<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 30.08.18
 * Time: 12:23 PM
 */

namespace instagram\models;


class Setting
{
    /**
     * @var string
     */
    protected $allow_comments_from;

    /**
     * @var array
     */
    protected $blocked_commenters;

    /**
     * @var array
     */
    protected $filtered_keywords;

    /**
     * @return string
     */
    public function getAllowCommentsFrom()
    {
        return $this->allow_comments_from;
    }

    /**
     * @param string $allow_comments_from
     */
    public function setAllowCommentsFrom( $allow_comments_from )
    {
        $this->allow_comments_from = $allow_comments_from;
    }

    /**
     * @return array
     */
    public function getBlockedCommenters()
    {
        return $this->blocked_commenters;
    }

    /**
     * @param array $blocked_commenters
     */
    public function setBlockedCommenters( $blocked_commenters )
    {
        $this->blocked_commenters = $blocked_commenters;
    }

    /**
     * @return array
     */
    public function getFilteredKeywords()
    {
        return $this->filtered_keywords;
    }

    /**
     * @param array $filtered_keywords
     */
    public function setFilteredKeywords( $filtered_keywords )
    {
        $this->filtered_keywords = $filtered_keywords;
    }
}