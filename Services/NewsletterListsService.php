<?php
/**
 * @package Newscoop\NewsletterPluginBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\NewsletterPluginBundle\Services;

use Doctrine\ORM\EntityManager;
use Newscoop\NewsletterPluginBundle\TemplateList\ListCriteria;
use Newscoop\NewsletterPluginBundle\Entity\NewsletterList;
use Symfony\Component\EventDispatcher\GenericEvent;
/**
 * Newsletter service
 */
class NewsletterListsService
{
    /** @var Doctrine\ORM\EntityManager */
    protected $em;

    /**
     * @param Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Subscribe for newsletter list
     *
     * @param  GenericEvent $event
     * @return void
     */
    public function subscribe(GenericEvent $event)
    {
        $params = $event->getArguments();
        $user = array_key_exists('user', $params) ? $params['user'] : null;
        unset($params['user']);

        if ($params['newsletter']) {
            $preferencesService = $this->container->get('system_preferences_service');
            $mailchimp = new \Mailchimp($preferencesService->mailchimp_apikey);
            //TODO subscribe for selected list
        }
    }

    /**
     * Find by criteria
     *
     * @param  ListCriteria        $criteria
     * @return Newscoop\ListResult
     */
    public function findByCriteria(ListCriteria $criteria)
    {
        return $this->getRepository()->getListByCriteria($criteria);
    }

   
    /**
     * Count by given criteria
     *
     * @param array $criteria
     * @return int
     */
    public function countBy(array $criteria = array())
    {
        return $this->getRepository()->findByCount($criteria);
    }

    /**
     * Get repository
     *
     * @return NewsletterList
     */
    protected function getRepository()
    {
        return $this->em->getRepository('Newscoop\NewsletterPluginBundle\Entity\NewsletterList');
    }
}