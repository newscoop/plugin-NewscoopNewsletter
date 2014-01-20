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
use Symfony\Component\HttpFoundation\JsonResponse;
use Newscoop\NewsletterPluginBundle\Entity\SubscribedUser;

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

    /** @var Symfony\Component\Translation\Translator */
    protected $translator;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->em = $container->get('em');
        $this->preferencesService = $container->get('system_preferences_service');
        $this->request = $container->get('request');
        $this->user = $container->get('user');
        $this->translator = $container->get('translator');
    }

    /**
     * Subscribe for newsletter list
     *
     * @param GenericEvent $event
     *
     * @return void
     */
    public function subscribeOnRegister(GenericEvent $event)
    {
        $params = $event->getArguments();
        $user = array_key_exists('user', $params) ? $params['user'] : null;
        unset($params['user']);

        if ($this->request->has('newsletter-lists')) {
            $listIds = $this->request->get('newsletter-lists');
            foreach ($listIds["ids"] as $value) {
                if (count($listIds["ids"]) != 1) {
                    foreach ($listIds["ids"] as $value) {
                        $this->subscribeUser($value, 'html');
                    }
                } else {
                    $this->subscribeUser($listIds["ids"]);
                }
            }
        }
    }

    /**
     * Subscribe not registered user to given list id
     *
     * @param string $id   Newsletter list id
     * @param string $type Newsletter type Html or text
     *
     * @return void
     */
    public function subscribePublic($id, $type)
    {
        try {
            $this->initMailchimp()->lists->subscribe($value,
                array(
                    'email' => $request->request->get('newsletter-lists-public-email')
                ),
                array(
                    'FNAME' => $request->request->get('newsletter-lists-public-firstname'),
                    'LNAME' => $request->request->get('newsletter-lists-public-lastname')
                ),
                $type
            );
        } catch (\Mailchimp_List_AlreadySubscribed $e) {
            return array(
                'message' => substr($e->getMessage(), 0, -35),
                'status' => false,
            );
        }

        return array(
            'message' => $this->translator->trans('plugin.newsletter.msg.successfully'),
            'status' => true,
        );
    }

    /**
     * Subscribe user to given list id
     *
     * @param string $id   Newsletter list id
     * @param string $type Newsletter type Html or text
     *
     * @return void
     */
    public function subscribeUser($id, $type)
    {
        try {
            $this->initMailchimp()->lists->subscribe($id,
                array(
                    'email' => $this->user->getCurrentUser()->getEmail()
                ),
                array(
                    'FNAME' => $this->user->getCurrentUser()->getFirstName(),
                    'LNAME' => $this->user->getCurrentUser()->getLastName()
                ),
                $type
            );
        } catch (\Mailchimp_List_AlreadySubscribed $e) {
            return array(
                'message' => substr($e->getMessage(), 0, -35),
                'status' => false,
            );
        }

        return array(
            'message' => $this->translator->trans('plugin.newsletter.msg.successfully'),
            'status' => true,
        );
    }

    /**
     * Test if user email is subscribed to list
     *
     * @param string $email  User email
     * @param string $listId List id
     *
     * @return bool
     */
    public function isSubscribed($email, $listId)
    {
        try {
            return in_array($listId, $this->getLists(array('email' => $email))[0]);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Unsubscribe email from list
     *
     * @param string $email  User email
     * @param string $listId List id
     *
     * @return void|Exception
     */
    public function unsubscribe($email, $listId)
    {
        try {
            return $this->initMailchimp()->lists->unsubscribe($listId, array('email' => $email));
        } catch (\Exception $e) {
            return new JsonResponse(array(
                'message' => $e->getMessage(),
                'status' => false,
                'listId' => $listId
            ));
        }
    }

    /**
     * Get lists email is subscribed to
     *
     * @param string $email User email
     *
     * @return array
     */
    private function getLists($email)
    {
        $lists = $this->initMailchimp()->helper->listsForEmail($email);

        return $lists ?: array();
    }

    /**
     * Find by criteria
     *
     * @param ListCriteria         $criteria
     *
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
     * @param array $listId
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