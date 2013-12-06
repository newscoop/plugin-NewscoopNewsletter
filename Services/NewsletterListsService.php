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
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Newsletter service
 */
class NewsletterListsService
{
    /** @var Doctrine\ORM\EntityManager */
    protected $em;

    /** @var Newscoop\NewscoopBundle\Services\SystemPreferencesService */
    protected $preferencesService;

    protected $request;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->em = $container->get('em');
        $this->preferencesService = $container->get('system_preferences_service');
        $this->request = $container->get('request');
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

        if ($this->request->has('newsletter-lists')) {
            $listIds = $this->request->get('newsletter-lists');
            foreach ($listIds["ids"] as $value) {
                $this->initMailchimp()->lists->subscribe($value, 
                    array(
                        'email' => $user->getEmail()
                    ), 
                    array(
                        'FNAME' => $user->getFirstName(), 'LNAME' => $user->getLastName()
                    )
                );
            }
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
     * Initialize MailChimp library
     *
     * @return Mailchimp
     */
    public function initMailchimp()
    {   
        return new \Mailchimp($this->preferencesService->mailchimp_apikey);
    }

    /**
     * Get mailchimp lists
     *
     * @param  array $listId
     * @return array
     */
    public function getMailchimpLists($listId = array())
    {   
        return $this->initMailchimp($this->preferencesService->mailchimp_apikey)->lists->getList($listId);
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