<?php
/**
 * @package Newscoop\NewsletterPluginBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\NewsletterPluginBundle\TemplateList;

use Newscoop\Criteria;

/**
 * Available criteria for newsletter lists.
 */
class ListCriteria extends Criteria
{
    /**
     * @var int
     */
    public $listId;

    /**
     * @var int
     */
    public $length;

    /**
     * @var string
     */
    public $name;

    /**
     * @var bool
     */
    public $isEnabled;

    /**
     * @var array
     */
    public $created_at = array('created_at' => 'asc');
}