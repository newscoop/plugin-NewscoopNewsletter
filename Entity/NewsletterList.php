<?php
/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\NewsletterPluginBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Newsletter list entity.
 *
 * @ORM\Entity(repositoryClass="Newscoop\NewsletterPluginBundle\Entity\Repository\NewsletterListRepository")
 * @ORM\Table(name="plugin_newsletter_lists")
 */
class NewsletterList
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="string", name="list_id")
     *
     * @var string
     */
    protected $listId;

    /**
     * @ORM\OneToMany(
     *     targetEntity="NewsletterGroup",
     *     mappedBy="list",
     *     orphanRemoval=true,
     *     cascade={"persist"}
     * )
     *
     * @var ArrayCollection
     */
    protected $groups;

    /**
     * @ORM\Column(type="string", name="list_name")
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type="integer", name="subscribers_count")
     *
     * @var int
     */
    protected $subscribersCount;

    /**
     * @ORM\Column(type="boolean", name="enabled")
     *
     * @var bool
     */
    protected $isEnabled;

    /**
     * @ORM\Column(type="datetime", name="created_at")
     *
     * @var datetime
     */
    protected $created_at;

    /**
     * @ORM\Column(type="datetime", name="last_sync")
     *
     * @var datetime
     */
    protected $lastSynchronized;

    /**
     * @ORM\Column(type="boolean", name="is_active")
     *
     * @var bool
     */
    protected $is_active;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
        $this->setIsEnabled(true);
        $this->setIsActive(true);
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get list id.
     *
     * @return string
     */
    public function getListId()
    {
        return $this->listId;
    }

    /**
     * Set list id.
     *
     * @param string $listId
     *
     * @return string
     */
    public function setListId($listId)
    {
        $this->listId = $listId;

        return $this;
    }

    /**
     * Get groups.
     *
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Get list name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set list name.
     *
     * @param string $name
     *
     * @return string
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get subscribers count.
     *
     * @return int
     */
    public function getSubscribersCount()
    {
        return $this->subscribersCount;
    }

    /**
     * Set subscribers count.
     *
     * @param int $subscribersCount
     *
     * @return int
     */
    public function setSubscribersCount($subscribersCount)
    {
        $this->subscribersCount = $subscribersCount;

        return $this;
    }

    /**
     * Get list enable/disable.
     *
     * @return bool
     */
    public function getIsEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * Set list enable/disable.
     *
     * @param bool $isEnabled
     *
     * @return bool
     */
    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    /**
     * Get status.
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->is_active;
    }

    /**
     * Set status.
     *
     * @param bool $is_active
     *
     * @return bool
     */
    public function setIsActive($is_active)
    {
        $this->is_active = $is_active;

        return $this;
    }

    /**
     * Get create date.
     *
     * @return datetime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set create date.
     *
     * @param datetime $created_at
     *
     * @return datetime
     */
    public function setCreatedAt(\DateTime $created_at)
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * Get synchronization date.
     *
     * @return datetime
     */
    public function getLastSynchronized()
    {
        return $this->lastSynchronized;
    }

    /**
     * Set synchronization date.
     *
     * @param datetime $lastSynchronized
     *
     * @return datetime
     */
    public function setLastSynchronized(\DateTime $lastSynchronized)
    {
        $this->lastSynchronized = $lastSynchronized;

        return $this;
    }

    public function addGroup(NewsletterGroup $group)
    {
        $this->groups->add($group);
        $group->setList($this);
    }

    public function removeGroup(NewsletterGroup $group)
    {
        $this->groups->removeElement($group);
        $group->setList(null);
    }
}
