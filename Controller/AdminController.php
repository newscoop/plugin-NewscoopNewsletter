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

class AdminController extends Controller
{

    /**
     * @Route("/admin/newsletter-plugin")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $mailchimp = new \Mailchimp('cfd271519acfa5a27bafe5298e16333e-us7');
        $lists = $mailchimp->lists->getList();
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
        return array('lists' => $lists);
    }

    /**
     * @Route("/admin/newsletter-plugin/configure")
     * @Template()
     */
    public function configureAction(Request $request)
    {
        return array();
    }

}