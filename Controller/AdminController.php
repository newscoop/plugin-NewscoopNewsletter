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
use Newscoop\NewsletterPluginBundle\Form\Type\SettingsType;

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
        $preferencesService = $this->container->get('system_preferences_service');
        $form = $this->container->get('form.factory')->create(new SettingsType(), array(
            'apiKey' => $preferencesService->mailchimp_apikey
        ), array());

        $mailchimp = new \Mailchimp($preferencesService->mailchimp_apikey);
        $lists = $mailchimp->lists->getList();

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
        //var_dump($lists);die;
       /// $lists2 = $test->lists->members('091ccc3c0d', 'subscribed');
        /*$lists2 = $test->call('lists/clients',array(
                'id'                => '091ccc3c0d'));*/
        //var_dump($lists2);die;
        /*foreach ($lists2 as $key => $value) {
            var_dump($value);
        }
        die;*/
        //var_dump($lists2['data']);die;
        /*$result = $test->call('lists/subscribe', array(
                'id'                => '091ccc3c0d',
                'email'             => array('email'=>'rmuszynski1@gmail.com'),
                'merge_vars'        => array('FNAME'=>'Davy', 'LNAME'=>'Jones'),
                'double_optin'      => false,
                'update_existing'   => true,
                'replace_interests' => false,
                'send_welcome'      => false,
            ));*/
       // var_dump($result);die;
        //return array();
        return array(
            'form' => $form->createView(),
            'lists' => $lists
        );
    }
}