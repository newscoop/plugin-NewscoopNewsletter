<?php
/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2014 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\NewsletterPluginBundle\Services;

use Newscoop\NewsletterPluginBundle\TemplateList\ListCriteria;
use Newscoop\NewsletterPluginBundle\Entity\NewsletterList;
use Newscoop\NewsletterPluginBundle\Entity\NewsletterGroup;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Newscoop\Entity\User;

/**
 * Newsletter service.
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
     * Subscribe not registered user to given list id.
     *
     * @param string $id   Newsletter list id
     * @param string $type Newsletter type Html or text
     */
    public function subscribePublic($id, $type)
    {
        try {
            $this->initMailchimp()->lists->subscribe($id,
                array(
                    'email' => $request->request->get('newsletter-lists-public-email'),
                ),
                array(
                    'FNAME' => $request->request->get('newsletter-lists-public-firstname'),
                    'LNAME' => $request->request->get('newsletter-lists-public-lastname'),
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
     * Subscribe user to given list id.
     *
     * @param string $id     Newsletter list id
     * @param string $type   Newsletter type Html or text
     * @param array  $groups Groups
     */
    public function subscribeUser($id, $type, array $groups = array())
    {
        try {
            $mergeVars = array(
                'FNAME' => $this->user->getCurrentUser()->getFirstName(),
                'LNAME' => $this->user->getCurrentUser()->getLastName(),
            );

            if (!empty($groups)) {
                $groupings = array();
                $groupings[] = array(
                    'id' => $groups['id'],
                    'groups' => !empty($groups[0]) ? $groups[0] : array(''),
                );

                $mergeVars['GROUPINGS'] = array($groupings[0]);
            }

            $this->initMailchimp()->lists->subscribe($id,
                array(
                    'email' => $this->user->getCurrentUser()->getEmail(),
                ),
                $mergeVars,
                $type, false, true, true, true
            );
        } catch (\Mailchimp_List_AlreadySubscribed $e) {
            $messageArray = explode('.', $e->getMessage());
            unset($messageArray[count($messageArray) - 2]);

            throw new \Exception(implode('.', $messageArray));
        }
    }

    /**
     * Test if user email is subscribed to list.
     *
     * @param string $email  User email
     * @param string $listId List id
     *
     * @return bool
     */
    public function isSubscribed($email, $listId)
    {
        try {
            $lists = $this->getLists(array('email' => $email));
            $result = array();
            foreach ($lists as $list) {
                if ($listId == $list['id']) {
                    $result[] = true;
                } else {
                    $result[] = false;
                }
            }

            return in_array(true, $result);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Unsubscribe email from list.
     *
     * @param string $email  User email
     * @param string $listId List id
     *
     * @return void|Exception
     */
    public function unsubscribe($email, $listId)
    {
        try {
            $this->initMailchimp()->lists->unsubscribe($listId, array('email' => $email));
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Get groups for given list id.
     *
     * @param string $listId
     *
     * @return array
     */
    public function getListGroups($listId)
    {
        try {
            return $this->initMailchimp()->lists->interestGroupings($listId);
        } catch (\Exception $e) {
            return array(array('groups' => array()));
       }
    }

    /**
     * Get groups for given user and list.
     *
     * @param Newscoop\Entity\User $user
     * @param string               $listId
     *
     * @return array
     */
    public function getUserGroups($listId, $groupName)
    {
        $user = $this->user->getCurrentUser();
        if ($user) {
            $info = $this->initMailchimp()->lists->memberInfo($listId, array(array('email' => $user->getEmail())));
            if (!$info['success_count']) {
                return array();
            }

            $groups = array();
            foreach ($info['data'] as $userinfo) {
                foreach ($userinfo['merges']['GROUPINGS'] as $grouping) {
                    $groups[$grouping['id']] = $grouping['groups'];
                }
            }

            foreach ($groups as $key => $value) {
                foreach ($value as $k => $v) {
                    if ($v['name'] == $groupName) {
                        return $v['interested'];
                    }
                }
            }
        }
    }

    /**
     * Get lists email is subscribed to.
     *
     * @param string $email User email
     *
     * @return array
     */
    public function getLists($email)
    {
        $lists = $this->initMailchimp()->helper->listsForEmail($email);

        return $lists ?: array();
    }

    /**
     * Find by criteria.
     *
     * @param ListCriteria $criteria
     *
     * @return Newscoop\ListResult
     */
    public function findByCriteria(ListCriteria $criteria)
    {
        return $this->getRepository()->getListByCriteria($criteria);
    }

    /**
     * Initialize MailChimp library.
     *
     * @return Mailchimp
     */
    public function initMailchimp()
    {
        return new \Mailchimp($this->preferencesService->mailchimp_apikey);
    }

    /**
     * Get mailchimp lists.
     *
     * @param array $listId
     *
     * @return array
     */
    public function getMailchimpLists($listId = array())
    {
        return $this->initMailchimp($this->preferencesService->mailchimp_apikey)->lists->getList($listId);
    }

    /**
     * Count by given criteria.
     *
     * @param array $criteria
     *
     * @return int
     */
    public function countBy(array $criteria = array())
    {
        return $this->getRepository()->findByCount($criteria);
    }

    /**
     * Get repository.
     *
     * @return NewsletterList
     */
    public function getRepository()
    {
        return $this->em->getRepository('Newscoop\NewsletterPluginBundle\Entity\NewsletterList');
    }

    /**
     * Synchronizes all lists.
     */
    public function synchronizeAllLists()
    {
        $lists = $this->getMailchimpLists();
        $existingLists = $this->getRepository()
            ->createQueryBuilder('a')
            ->where('a.is_active = true')
            ->getQuery()
            ->getResult();

        $listsData = $lists['data'];
        foreach ($listsData as $data) {
            if (count($existingLists) === 0 || $lists['total'] >= count($existingLists)) {
                $this->addList($data);
            }

            if ($lists['total'] < count($existingLists)) {
                foreach ($existingLists as $list) {
                    $this->removeList($list, $listsData);
                }
            }
        }

        $this->updateLists($listsData, $existingLists);
        $this->em->flush();
    }

    private function addList(array $data = array())
    {
        $oldList = $this->getRepository()->findOneBy(array(
            'listId' => $data['id'],
        ));

        if ($oldList) {
            $this->updateList($oldList, $data);
        } else {
            $this->createList($data);
        }
    }

    private function createList(array $data = array())
    {
        $list = $this->createNew();
        $list->setListId($data['id']);
        $list->setName($data['name']);
        $list->setSubscribersCount($data['stats']['member_count']);
        $list->setLastSynchronized(new \DateTime('now'));
        $list->setCreatedAt(new \DateTime($data['date_created']));

        $listGroups = $this->getListGroups($list->getListId());
        foreach ($listGroups[0]['groups'] as $group) {
            $newsletterGroup = $this->createNewGroup();
            $newsletterGroup->setList($list);
            $newsletterGroup->setGroupId($listGroups[0]['id']);
            $newsletterGroup->setName($group['name']);
            $newsletterGroup->setSubscribersCount(is_null($group['subscribers']) ? 0 : $group['subscribers']);
            $list->addGroup($newsletterGroup);
        }

        $this->em->persist($list);
    }

    /**
     * Create new instance of NewsletterList class.
     *
     * @return NewsletterList Newsletter list object
     */
    public function createNew()
    {
        return new NewsletterList();
    }

    /**
     * Removes the newsletter list.
     *
     * @param NewsletterList $list Newsletter list to remove
     * @param array          $data List data
     */
    public function removeList(NewsletterList $list, array $data = array())
    {
        if (!$this->searchArray($list->getListId(), 'id', $data)) {
            $this->em->remove($list);
        }
    }

    private function searchArray($value, $key, $array)
    {
        foreach ($array as $val) {
            if ($val[$key] == $value) {
                return true;
            }
        }

        return false;
    }

    private function updateLists(array $lists = array(), array $existingLists = array())
    {
        foreach ($lists as $data) {
            foreach ($existingLists as $list) {
                $this->updateList($list, $data);
            }
        }
    }

    /**
     * Updates single newsletter list based on MailChimp list.
     *
     * @param NewsletterList $list Newsletter list object
     * @param array          $data An array with MailChimp list data
     *
     * @return NewsletterList
     */
    public function updateList(NewsletterList $list, array $data = array())
    {
        if ($list->getListId() == $data['id']) {
            if ($list->getName() !== $data['name'] ||
                $list->getSubscribersCount() !== $data['stats']['member_count']) {
                $list->setListId($data['id']);
                $list->setName($data['name']);
                $list->setSubscribersCount($data['stats']['member_count']);
                $list->setLastSynchronized(new \DateTime('now'));
            }
        }

        return $list;
    }

    /**
     * Create new instance of NewsletterGroup class.
     *
     * @return NewsletterGroup Newsletter group object
     */
    public function createNewGroup()
    {
        return new NewsletterGroup();
    }
}
