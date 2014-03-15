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
     */
    public function subscribeAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            try {
                $newsletterService = $this->container->get('newscoop_newsletter_plugin.service');
                $user = $this->container->get('user')->getCurrentUser();
                $translator = $this->container->get('translator');
                $messages = array();
                $listIdGroups = $request->request->get('newsletter-list-id');
                $type = $request->request->get('newsletter-type');
                if ($request->request->has('newsletter-list')) {
                    $lists = $request->request->get('newsletter-list');
                    $listNames = $request->request->get('newsletter-names');
                    foreach ($lists as $listId => $status) {
                        try {
                            $matches = $newsletterService->getLists(array('email' => $user->getEmail()));
                        } catch(\Exception $e) {
                            $matches = array();
                        }

                        if ($status === 'false' && !empty($matches) && $newsletterService->isSubscribed($user->getEmail(), $listId)) {
                            foreach ($matches as $match) {
                                if ($match['id'] == $listId) {
                                    try {

                                        $newsletterService->unsubscribe($user->getEmail(), $match['id']);
                                        $messages[] = array(
                                            'message' => $translator->trans('plugin.newsletter.msg.unsubscribe', array('%list%' => $match['name'])),
                                            'status' => true,
                                        );
                                    } catch(\Exception $e) {
                                        $messages[] = array(
                                            'message' => $translator->trans('plugin.newsletter.msg.errorunsubscribe', array('%list%' => $match['name'])),
                                            'status' => false,
                                        );
                                    }
                                }
                            }
                        } else if ($status === 'true' && !empty($matches) && !$newsletterService->isSubscribed($user->getEmail(), $listId)) {
                            foreach ($matches as $match) {
                                if ($match['id'] != $listId) {
                                    try {
                                        $newsletterService->subscribeUser($listId, $type);
                                        $messages[] = array(
                                            'message' => $translator->trans('plugin.newsletter.msg.successfully', array('%list%' => $listNames[$listId])),
                                            'status' => true,
                                        );
                                    } catch(\Exception $e) {
                                        $messages[] = array(
                                            'message' => $translator->trans('plugin.newsletter.msg.unsuccessfully', array('%list%' => $listNames[$listId])),
                                            'status' => false,
                                        );
                                    }
                                }
                            }
                        } else if ($status === 'true' && empty($matches)) {
                            try {
                                $newsletterService->subscribeUser($listId, $type);
                                $messages[] = array(
                                    'message' => $translator->trans('plugin.newsletter.msg.successfully', array('%list%' => $listNames[$listId])),
                                    'status' => true,
                                );
                            } catch(\Exception $e) {
                                $messages[] = array(
                                    'message' => $translator->trans('plugin.newsletter.msg.unsuccessfully', array('%list%' => $listNames[$listId])),
                                    'status' => false,
                                );
                            }
                        }
                    }
                }

                if ($request->request->get('groups')) {
                    $groups = array();
                    foreach ($request->request->all() as $key => $value) {
                        if (is_numeric($key)) {
                            $groups['id'] = $key;
                            $groups[] = array_filter($value);
                        }
                    }

                     try {
                        $newsletterService->subscribeUser($listIdGroups, $type, $groups);
                        $messages[] = array(
                            'message' => $translator->trans('plugin.newsletter.msg.saved'),
                            'status' => true,
                        );
                    } catch(\Exception $e) {
                        $messages[] = array(
                            'message' => $translator->trans('plugin.newsletter.msg.notsaved'),
                            'status' => false,
                        );
                    }
                }

                return new JsonResponse(array('response' => $messages));
            } catch(\Exception $e) {
                $messages[] = array(
                    'message' => $translator->trans('plugin.newsletter.msg.servicedown'),
                    'status' => false,
                );
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
