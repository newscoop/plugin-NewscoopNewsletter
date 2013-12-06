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
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Newsletter service
 */
class NewsletterListsService
{
    /** @var Doctrine\ORM\EntityManager */
    protected $em;

    /** @var Newscoop\NewscoopBundle\Services\SystemPreferencesService */
    protected $preferencesService;

    /** @var Symfony\Component\HttpFoundation\Request */
    protected $request;

    /** @var Newscoop\Entity\User */
    protected $user;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->em = $container->get('em');
        $this->preferencesService = $container->get('system_preferences_service');
        $this->request = $container->get('request');
        $this->user = $container->get('user');
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
                if (count($listIds["ids"]) != 1) {
                    foreach ($listIds["ids"] as $value) {
                        $this->subscribeUser($value);
                    }
                } else {
                    $this->subscribeUser($listIds["ids"]);
                }
            }
        }
    }

    /**
     * Subscribe user on kernel request
     *
     * @param  GetResponseEvent $event
     * @return void
     */
    public function onKernelRequest(GetResponseEvent $event)
    {   
        if ($event->getRequest()->request->has('newsletter-lists')) {
            $listIds = $event->getRequest()->request->get('newsletter-lists');
            $username = $event->getRequest()->request->get('username');
            if (count($listIds["ids"]) != 1) {
                foreach ($listIds["ids"] as $value) {
                    $this->subscribeUser($value);
                }
            } else {
                $this->subscribeUser($listIds["ids"]);
            }
        }

        if ($event->getRequest()->request->has('newsletter-lists-public')) {
            $listIds = $event->getRequest()->request->get('newsletter-lists-public');
            foreach ($listIds["ids"] as $value) {
                try {
                    $this->initMailchimp()->lists->subscribe($value, 
                        array(
                            'email' => $event->getRequest()->request->get('newsletter-lists-public-email')
                        ),
                        array(
                            'FNAME' => $event->getRequest()->request->get('newsletter-lists-public-firstname'), 
                            'LNAME' => $event->getRequest()->request->get('newsletter-lists-public-lastname')
                        )
                    );
                } catch (\Exception $e) {

                }
            }
        }
    }

    /**
     * Subscribe user to give list id
     *
     * @param  string $id
     * @return void
     */
    public function subscribeUser($id) {
        try {
            $this->initMailchimp()->lists->subscribe($id, 
                array(
                    'email' => $this->user->getCurrentUser()->getEmail()
                ), 
                array(
                    'FNAME' => $this->user->getCurrentUser()->getFirstName(), 
                    'LNAME' => $this->user->getCurrentUser()->getLastName()
                )
            );
        } catch (\Exception $e) {
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