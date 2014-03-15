<?php
/**
 * @package Newscoop\NewsletterPluginBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\NewsletterPluginBundle\Meta;

use Doctrine\ORM\EntityManager;
use Newscoop\NewsletterPluginBundle\Entity\NewsletterList;

/**
 * Meta Newsletter class
 */
class MetaNewsletter
{
    /**
     * @var NewsletterList
     */
    private $list;

    /**
     * @var string
     */
    public $id;

    /**
     * @var boolean
     */
    public $enabled;

    /**
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $subscribers_count;

    /**
     * @var string
     */
    public $created;

    /**
     * @var Newscoop\NewsletterBundle\Services\NewsletterService
     */
    private $service;

    /**
     * @var array
     */
    public $groups;

    /**
     * @param NewsletterList $list
     */
    public function __construct(NewsletterList $list = null)
    {
        if (!$list) {
            return;
        }

        $this->service = \Zend_Registry::get('container')->getService('newscoop_newsletter_plugin.service');
        $this->created = $this->getCreated($list);
        $this->enabled = $this->getEnabled($list);
        $this->name = $this->getName($list);
        $this->id = $this->getListId($list);
        $this->subscribers_count = $this->getSubscribers($list);
        $this->groups = $this->getGroups($list);
    }


    /**
     * Get list id
     *
     * @param NewsletterList $list
     *
     * @return string
     */
    protected function getListId($list)
    {
        return $list->getListId() ? $list->getListId() : null;
    }

    /**
     * Get list id
     *
     * @param NewsletterList $list
     *
     * @return string
     */
    protected function getGroups($list)
    {
        return $list->getGroups();
    }

    /**
     * Chceck is user email is subscribed to list
     *
     * @param string $email  User email
     * @param string $listId List id
     *
     * @return bool
     */
    public function isSubscribed($email, $listId)
    {
        return $this->service->isSubscribed($email, $listId);
    }

    /**
     * Chceck is user email is subscribed to list
     *
     * @param string $listId    List id
     * @param string $groupName Group name
     *
     * @return bool
     */
    public function isSubscribedToGroup($listId, $groupName)
    {
        return $this->service->getUserGroups($listId, $groupName);
    }

    /**
     * Get subscribers count
     *
     * @param NewsletterList $list
     *
     * @return int
     */
    protected function getSubscribers($list)
    {
        return $list->getSubscribersCount() ? $list->getSubscribersCount() : 0;
    }

    /**
     * Get list name
     *
     * @param NewsletterList $list
     *
     * @return string
     */
    protected function getName($list)
    {
        return $list->getName() ? $list->getName() : null;
    }

    /**
     * Get enabled list
     *
     * @param NewsletterList $list
     *
     * @return boolean
     */
    protected function getEnabled($list)
    {  
        return $list->getIsEnabled() ? $list->getIsEnabled() : null;
    }

    /**
     * Get created date
     *
     * @param NewsletterList $list
     *
     * @return string
     */
    protected function getCreated($list)
    {
        $date = $list->getCreatedAt();

        return $date->format('d.m.Y H:i:s');
    }
}