<?php
/**
 * @package Newscoop\NewsletterPluginBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\FacebookNewscoopBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * Newsletter settings entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="plugin_newsletter_settings")
 */
class Settings 
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string", name="api_key")
     * @var string
     */
    private $api_key;

    /**
     * @ORM\Column(type="integer", name="default_list")
     * @var int
     */
    private $default_list;

    /**
     * @ORM\Column(type="boolean", name="ssl")
     * @var string
     */
    private $ssl;

    /**
     * @ORM\Column(type="datetime", name="created_at")
     * @var string
     */
    private $created_at;

    /**
     * @ORM\Column(type="boolean", name="is_active")
     * @var boolean
     */
    private $is_active;

    public function __construct() {
        $this->setCreatedAt(new \DateTime());
        $this->setIsActive(true);
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get API key
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->api_key;
    }

    /**
     * Set API key
     *
     * @param  integer $api_key
     * @return integer
     */
    public function setApiKey($api_key)
    {
        $this->api_key = $api_key;
        
        return $this;
    }

    /**
     * Get default list id
     *
     * @return integer
     */
    public function getDefaultList()
    {
        return $this->default_list;
    }

    /**
     * Set default list id
     *
     * @param  integer $default_list
     * @return integer
     */
    public function setDefaultList($default_list)
    {
        $this->default_list = $default_list;
        
        return $this;
    }

    /**
     * Get ssl
     *
     * @return bool
     */
    public function getSsl()
    {
        return $this->ssl;
    }

    /**
     * Set ssl
     *
     * @param  bool $ssl
     * @return bool
     */
    public function setSsl($ssl)
    {
        $this->ssl = $ssl;
        
        return $this;
    }

    /**
     * Get status
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->is_active;
    }

    /**
     * Set status
     *
     * @param  boolean $is_active
     * @return boolean
     */
    public function setIsActive($is_active)
    {
        $this->is_active = $is_active;
        
        return $this;
    }

    /**
     * Get create date
     *
     * @return datetime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set create date
     *
     * @param  datetime $created_at
     * @return datetime
     */
    public function setCreatedAt(\DateTime $created_at)
    {
        $this->created_at = $created_at;
        
        return $this;
    }
}