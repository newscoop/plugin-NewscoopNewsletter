<?php
/**
 * @package Newscoop\NewsletterPluginBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\NewsletterPluginBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Newscoop\NewsletterPluginBundle\Form\Type\SettingsType;
use Newscoop\NewsletterPluginBundle\Entity\NewsletterList;

class AdminController extends Controller
{

    /**
     * @Route("/admin/newsletter-plugin")
     * @Route("/admin/newsletter-plugin/configure", name="newscoop_newsletterplugin_admin_configure")
     * @Template()
     */
    public function indexAction(Request $request)
    {   
        $translator = $this->container->get('translator');
        $em = $this->container->get('em');
        $preferencesService = $this->container->get('system_preferences_service');
        $message = null;
        $form = $this->container->get('form.factory')->create(new SettingsType(), array(
            'apiKey' => $preferencesService->mailchimp_apikey
        ), array());

        $newsletterListsCount = $em->getRepository('Newscoop\NewsletterPluginBundle\Entity\NewsletterList')
            ->createQueryBuilder('a')
            ->select('count(a)')
            ->where('a.is_active = true')
            ->getQuery()
            ->getSingleScalarResult();

        if ($preferencesService->mailchimp_apikey != null) {
            if ((int)$newsletterListsCount === 0) {
                $mailchimp = new \Mailchimp($preferencesService->mailchimp_apikey);
                $lists = $mailchimp->lists->getList();

                foreach ($lists as $key => $value) {
                    if (is_array($value)) {
                        foreach ($value as $data) {
                            $newsletterList = new NewsletterList();
                            $newsletterList->setListId($data['id']);
                            $newsletterList->setName($data['name']);
                            $newsletterList->setSubscribersCount($data['stats']['member_count']);
                            $newsletterList->setLastSynchronized($newsletterList->getCreatedAt());
                            $em->persist($newsletterList);
                            $em->flush();
                        }
                    }
                }
            }
        } else {
            $message = $translator->trans('plugin.newsletter.msg.fillapikey');
        }

        if ($request->get('_route') === "newscoop_newsletterplugin_admin_configure") {
            if ($request->isMethod('POST')) {
                $form->bind($request);
                if ($form->isValid()) {
                    $data = $form->getData();
                    $preferencesService->mailchimp_apikey = $data['apiKey'];
                    $this->get('session')->getFlashBag()->add('success', $translator->trans('plugin.newsletter.msg.saved'));

                    return $this->redirect($this->generateUrl('newscoop_newsletterplugin_admin_index'));
                }

                $this->get('session')->getFlashBag()->add('error', $translator->trans('plugin.newsletter.msg.error'));

                return $this->redirect($this->generateUrl('newscoop_newsletterplugin_admin_index'));
            }
        }

        $newsletterLists = $em->getRepository('Newscoop\NewsletterPluginBundle\Entity\NewsletterList')
            ->createQueryBuilder('a')
            ->where('a.is_active = true')
            ->getQuery()
            ->getArrayResult();
      
        return array(
            'form' => $form->createView(),
            'lists' => $newsletterLists,
            'message' => $message
        );
    }

    /**
     * @Route("/admin/newsletter-plugin/synchronize-list/{id}")
     */
    public function synchronizeListAction(Request $request, $id)
    {   
        if ($request->isMethod('POST')) {
            try {
                $em = $this->container->get('em');
                $preferencesService = $this->container->get('system_preferences_service');
                $newsletterList = $em->getRepository('Newscoop\NewsletterPluginBundle\Entity\NewsletterList')->findOneBy(array(
                    'is_active' => true,
                    'listId' => $id
                ));

                if ($newsletterList) {
                    $mailchimp = new \Mailchimp($preferencesService->mailchimp_apikey);
                    $lists = $mailchimp->lists->getList(array('list_id' => $id));
                    foreach ($lists as $value) {
                        if (is_array($value)) {
                            foreach ($value as $data) {
                                if ($newsletterList->getName() != $data['name'] || 
                                    $newsletterList->getSubscribersCount() != $data['stats']['member_count']) {
                                    $newsletterList->setListId($data['id']);
                                    $newsletterList->setName($data['name']);
                                    $newsletterList->setSubscribersCount($data['stats']['member_count']);
                                    $newsletterList->setLastSynchronized(new \DateTime('now'));
                                    $em->flush();

                                    return new Response(json_encode(array(
                                        'status' => true,
                                        'subscribers' => $data['stats']['member_count'],
                                        'listName' => $data['name'],
                                        'lastSync' => $newsletterList->getLastSynchronized()
                                    )));
                                } else {
                                    return new Response(json_encode(array('sync' => false)));
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                return new Response(json_encode(array('status' => false)));
            }
        }
    }

    /**
     * @Route("/admin/newsletter-plugin/synchronize-all-lists")
     * @Template()
     */
    public function synchronizeAllListsAction(Request $request)
    {
        try {
            $translator = $this->container->get('translator');
            $em = $this->container->get('em');
            $preferencesService = $this->container->get('system_preferences_service');
            $newsletterList = $em->getRepository('Newscoop\NewsletterPluginBundle\Entity\NewsletterList')->findOneBy(array(
                'is_active' => true,
            ));

            if ($newsletterList) {
                $mailchimp = new \Mailchimp($preferencesService->mailchimp_apikey);
                $lists = $mailchimp->lists->getList();
                foreach ($lists as $value) {
                    if (is_array($value)) {
                        foreach ($value as $data) {
                            if ($newsletterList->getName() != $data['name'] || 
                                $newsletterList->getSubscribersCount() != $data['stats']['member_count']) {
                                $newsletterList->setListId($data['id']);
                                $newsletterList->setName($data['name']);
                                $newsletterList->setSubscribersCount($data['stats']['member_count']);
                                $newsletterList->setLastSynchronized(new \DateTime('now'));
                                $em->flush();
                            }
                        }
                    }
                }

                $this->get('session')->getFlashBag()->add('success', $translator->trans('plugin.newsletter.msg.syncsuccess'));

                return $this->redirect($this->generateUrl('newscoop_newsletterplugin_admin_index'));
            } else {
                $newsletterList = new NewsletterList();
                foreach ($lists as $key => $value) {
                    if (is_array($value)) {
                        foreach ($value as $data) {
                            $newsletterList->setListId($data['id']);
                            $newsletterList->setName($data['name']);
                            $newsletterList->setSubscribersCount($data['stats']['member_count']);
                            $newsletterList->setLastSynchronized($newsletterList->getCreatedAt());
                            $em->persist($newsletterList);
                            $em->flush();
                        }
                    }
                }

                $this->get('session')->getFlashBag()->add('success', $translator->trans('plugin.newsletter.msg.syncsuccess'));

                return $this->redirect($this->generateUrl('newscoop_newsletterplugin_admin_index'));
            }
        } catch (\Exception $e) {
            $this->get('session')->getFlashBag()->add('error', $translator->trans('plugin.newsletter.msg.syncallerror'));
        
            return $this->redirect($this->generateUrl('newscoop_newsletterplugin_admin_index'));
        }
    }

    /**
     * @Route("/admin/newsletter-plugin/disable-list/{id}")
     * @Route("/admin/newsletter-plugin/enable-list/{id}", name="newscoop_newsletterplugin_admin_enablelist")
     */
    public function disableListAction(Request $request, $id)
    {   
        if ($request->isMethod('POST')) {
            $em = $this->container->get('em');
            $newsletterList = $em->getRepository('Newscoop\NewsletterPluginBundle\Entity\NewsletterList')->findOneBy(array(
                'is_active' => true,
                'listId' => $id
            ));

            if ($newsletterList) {
                if ($request->get('_route') === "newscoop_newsletterplugin_admin_disablelist") {
                    $newsletterList->setIsEnabled(false);
                } else {
                    $newsletterList->setIsEnabled(true);
                }
                $em->flush();

                return new Response(json_encode(array('status' => true)));
            }

            return new Response(json_encode(array('status' => false)));
        }
    }
}