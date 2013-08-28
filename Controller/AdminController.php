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
        $test = new \Mailchimp('cfd271519acfa5a27bafe5298e16333e-us7');

        $lists2 = $test->lists->members('091ccc3c0d', 'subscribed');
       
        return array('subscribers' => $lists2);
    }

}