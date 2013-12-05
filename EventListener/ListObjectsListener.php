<?php
/**
 * @package Newscoop\NewsletterPluginBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\NewsletterPluginBundle\EventListener;

use Newscoop\EventDispatcher\Events\CollectObjectsDataEvent;

class ListObjectsListener
{
    /**
     * Register plugin list objects in Newscoop
     * 
     * @param  CollectObjectsDataEvent $event
     */
    public function registerObjects(CollectObjectsDataEvent $event)
    {
        $event->registerListObject('newscoop\newsletterpluginbundle\templatelist\newsletterlists', array(
            'class' => 'Newscoop\NewsletterPluginBundle\TemplateList\NewsletterLists',
            'list' => 'newsletter_lists',
            'url_id' => 'pnlid',
        ));

        $event->registerObjectTypes('newsletter_list', array(
            'class' => '\Newscoop\NewsletterPluginBundle\Meta\MetaNewsletter'
        ));
    }
}