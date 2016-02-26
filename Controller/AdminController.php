<?php
/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\NewsletterPluginBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Newscoop\NewsletterPluginBundle\Form\Type\SettingsType;

class AdminController extends Controller
{
    /**
     * @Route("/admin/newsletter-plugin")
     * @Route("/admin/newsletter-plugin/configure", name="newscoop_newsletterplugin_admin_configure")
     */
    public function indexAction(Request $request)
    {
        $translator = $this->container->get('translator');
        $newsletterService = $this->container->get('newscoop_newsletter_plugin.service');
        $preferencesService = $this->container->get('system_preferences_service');
        $message = null;
        $form = $this->container->get('form.factory')->create(new SettingsType(), array(
            'apiKey' => $preferencesService->mailchimp_apikey,
        ), array());

        if (!$preferencesService->mailchimp_apikey) {
            $message = $translator->trans('plugin.newsletter.msg.fillapikey');
        }

        if ($request->get('_route') === 'newscoop_newsletterplugin_admin_configure') {
            if ($request->isMethod('POST')) {
                $form->handleRequest($request);
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

        $newsletterLists = $newsletterService->getRepository()
            ->createQueryBuilder('a')
            ->where('a.is_active = true')
            ->getQuery()
            ->getArrayResult();

        return $this->render('NewscoopNewsletterPluginBundle:Admin:index.html.twig', array(
            'form' => $form->createView(),
            'lists' => $newsletterLists,
            'message' => $message,
        ));
    }

    /**
     * @Route("/admin/newsletter-plugin/synchronize-list/{id}")
     * @Method("POST")
     */
    public function synchronizeListAction($id)
    {
        try {
            $entityManager = $this->container->get('em');
            $isRemoved = false;
            $newsletterService = $this->container->get('newscoop_newsletter_plugin.service');
            $newsletterList = $newsletterService->getRepository()->findOneBy(array(
                'is_active' => true,
                'listId' => $id,
            ));

            if (!$newsletterList) {
                return new JsonResponse(array('status' => false));
            }

            $lists = $newsletterService->getMailchimpLists(array('list_id' => $id));
            if ($lists['total'] === 1) {
                $lastSyncAt = $newsletterList->getLastSynchronized();
                $newsletterService->updateList($newsletterList, $lists['data'][0]);
                if ($lastSyncAt === $newsletterList->getLastSynchronized()) {
                    return new JsonResponse(array('sync' => false));
                }
            } else {
                $newsletterService->removeList($newsletterList, $lists['data']);
                $isRemoved = true;
            }

            $entityManager->flush();

            return new JsonResponse(array(
                'status' => true,
                'subscribers' => $newsletterList->getSubscribersCount(),
                'listName' => $newsletterList->getName(),
                'lastSync' => $newsletterList->getLastSynchronized(),
                'isRemoved' => $isRemoved,
            ));
        } catch (\Exception $e) {
            return new JsonResponse(array('status' => false));
        }
    }

    /**
     * @Route("/admin/newsletter-plugin/synchronize-all-lists")
     */
    public function synchronizeAllListsAction()
    {
        try {
            $translator = $this->container->get('translator');
            $newsletterService = $this->container->get('newscoop_newsletter_plugin.service');
            $newsletterService->synchronizeAllLists();

            $this->get('session')->getFlashBag()->add('success', $translator->trans('plugin.newsletter.msg.syncsuccess'));

            return $this->redirect($this->generateUrl('newscoop_newsletterplugin_admin_index'));
        } catch (\Exception $e) {
            $this->get('session')->getFlashBag()->add('error', $translator->trans('plugin.newsletter.msg.syncallerror'));

            return $this->redirect($this->generateUrl('newscoop_newsletterplugin_admin_index'));
        }
    }

    /**
     * @Route("/admin/newsletter-plugin/disable-list/{id}")
     * @Route("/admin/newsletter-plugin/enable-list/{id}", name="newscoop_newsletterplugin_admin_enablelist")
     * @Method("POST")
     */
    public function disableListAction(Request $request, $id)
    {
        $entityManager = $this->container->get('em');
        $newsletterList = $entityManager->getRepository('Newscoop\NewsletterPluginBundle\Entity\NewsletterList')->findOneBy(array(
            'is_active' => true,
            'listId' => $id,
        ));

        if ($newsletterList) {
            if ($request->get('_route') === 'newscoop_newsletterplugin_admin_disablelist') {
                $newsletterList->setIsEnabled(false);
            } else {
                $newsletterList->setIsEnabled(true);
            }

            $entityManager->flush();

            return new JsonResponse(array('status' => true));
        }

        return new JsonResponse(array('status' => false));
    }
}
