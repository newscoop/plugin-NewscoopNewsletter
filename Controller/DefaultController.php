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

class DefaultController extends Controller
{

    /**
     * @Route("/newsletter-plugin/widget", name="newscoop_newsletter_plugin_widget")
     * @Route("/newsletter-plugin/subscribe", name="newscoop_newsletter_plugin_subscribe")
     * @Template()
     */
    public function widgetAction(Request $request)
    {   
        $preferencesService = $this->container->get('system_preferences_service');
        $mailchimp = new \Mailchimp("cfd271519acfa5a27bafe5298e16333e-us7");
        $lists = $mailchimp->lists->getList();
        
        if ($request->get('_route') === 'newscoop_newsletter_plugin_subscribe') {

            $currentUser = $this->get('user')->getCurrentUser();
            $lists = $mailchimp->lists->getList();
            $list_ids = $request->get('lists');
           
            if ($list_ids) {
                try {

                    foreach ($list_ids["ids"] as $value) {
                        $mailchimp->lists->subscribe($value, 
                            array(
                                'email' => $currentUser->getEmail()
                            ), 
                            array(
                                'FNAME' => $currentUser->getFirstName(), 'LNAME' => $currentUser->getLastName()
                            )
                        );
                    }

                } catch (\Mailchimp_List_AlreadySubscribed $e) {
                    
                    return new Response(json_encode(array(
                        'error' => substr($e->getMessage(), 0, -35),
                        'status' => false
                    )));
                }

                return new Response(json_encode(array('status' => true)));
            }

            return new Response(json_encode(array('error' => false)));
        }

        return array(
            'lists' => $lists,
        );
    }

}