<?php
/**
 * @package Newscoop\NewsletterPluginBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2014 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\NewsletterPluginBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
* Newsletter controller
*/
class DefaultController extends Controller
{

    /**
     * @Route("/newsletter-plugin/subscribe", name="newscoop_newsletter_plugin_subscribe")
     * @Route("/newsletter-plugin/unsubscribe", name="newscoop_newsletter_plugin_unsubscribe")
     */
    public function subscribeAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            $newsletterService = $this->container->get('newscoop_newsletter_plugin.service');
            $translator = $this->container->get('translator');
            $user = $this->container->get('user')->getCurrentUser();
            if ($request->request->has('newsletter-lists')) {
                if ($request->get('_route') === "newscoop_newsletter_plugin_unsubscribe") {
                    if ($user) {
                        if ($request->request->has('newsletter-lists')) {
                            $listId = $request->request->get('newsletter-lists');
                            $messages = array();
                            $messages[] = $newsletterService->unsubscribe($user->getEmail(), $listId);

                            return new JsonResponse(array('result' => $messages));
                        }
                    }
                } else {
                    $listId = $request->request->get('newsletter-lists');
                    $type = $request->request->get('newsletter-type');
                    $messages = array();
                    $messages[] = $newsletterService->subscribeUser($listId, $type);

                    return new JsonResponse(array('result' => $messages));
                }
            }
        }
    }

    /**
     * @Route("/newsletter-plugin/subscribe-public", name="newscoop_newsletter_plugin_subscribepublic")
     */
    public function subscribePublicAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            $newsletterService = $this->container->get('newscoop_newsletter_plugin.service');
            $translator = $this->container->get('translator');
            if ($request->request->has('newsletter-lists-public')) {
                $listIds = $request->request->get('newsletter-lists-public');
                if (count($listIds["ids"]) != 1) {
                    foreach ($listIds["ids"] as $value) {
                        $newsletterService->subscribePublic($value, $type);
                    }

                    return new JsonResponse(array(
                        'message' => $translator->trans('plugin.newsletter.msg.successfully'),
                        'status' => true,
                    ));
                } else {
                    return new JsonResponse($this->subscribePublic($listIds["ids"], $type));
                }
            }

            return new JsonResponse(array(
                'message' => $translator->trans('plugin.newsletter.msg.selectone'),
                'status' => false
            ));
        }
    }
}
