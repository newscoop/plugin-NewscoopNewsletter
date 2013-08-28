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

class DefaultController extends Controller
{

    /**
     * @Route("/newsletter-plugin/widget", name="newscoop_newsletter_plugin_widget")
     * @Route("/newsletter-plugin/subscribe", name="newscoop_newsletter_plugin_subscribe")
     * @Template()
     */
    public function widgetAction(Request $request)
    {
        $mailchimp = new \Mailchimp('cfd271519acfa5a27bafe5298e16333e-us7');
        $lists = $mailchimp->lists->getList();
        $currentUser = $this->get('user')->getCurrentUser();

        if ($request->get('_route') === 'newscoop_newsletter_plugin_subscribe' && $request->isMethod('POST')) {
            $list_ids = $request->get('lists');
            try {
                foreach ($list_ids["ids"] as $value) {
                   $mailchimp->lists->subscribe($value, array('email' => $currentUser->getEmail()));
                }
            } catch (\Mailchimp_List_AlreadySubscribed $e) {
                return $this->container->get('templating')->render('NewscoopNewsletterPluginBundle:Default:widget.html.twig', array(
                    'lists' => $lists, 
                    'error' => $e
                ));
            }
        }

        return array(
            'lists' => $lists,
            'error' => false
        );
    }
}