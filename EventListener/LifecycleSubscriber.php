<?php
/**
 * @package Newscoop\NewsletterPluginBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\NewsletterPluginBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Newscoop\EventDispatcher\Events\GenericEvent;

/**
 * Event lifecycle management
 */
class LifecycleSubscriber implements EventSubscriberInterface
{
    private $em;

    public function __construct($em) {
        $this->em = $em;
    }

    public function install(GenericEvent $event)
    {
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $tool->updateSchema($this->getClasses(), true);

        // Generate proxies for entities
        $this->em->getProxyFactory()->generateProxyClasses($this->getClasses(), __DIR__ . '/../../../../library/Proxy');
    }

    public function update(GenericEvent $event)
    {
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $tool->updateSchema($this->getClasses(), true);

        // Generate proxies for entities
        $this->em->getProxyFactory()->generateProxyClasses($this->getClasses(), __DIR__ . '/../../../../library/Proxy');
    }

    public function remove(GenericEvent $event)
    {
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $tool->dropSchema($this->getClasses(), true);

        $removeAPIKey = $this->em->getRepository('Newscoop\NewscoopBundle\Entity\SystemPreferences')->findOneBy(array(
            'option' => 'mailchimp_apikey'
        ));

        $this->em->remove($removeAPIKey);
        $this->em->flush();
    }

    public static function getSubscribedEvents()
    {
        return array(
            'plugin.install.newscoop_newsletter_plugin_bundle' => array('install', 1),
            'plugin.update.newscoop_newsletter_plugin_bundle' => array('update', 1),
            'plugin.remove.newscoop_newsletter_plugin_bundle' => array('remove', 1),
        );
    }

    private function getClasses(){
        return array(
          $this->em->getClassMetadata('Newscoop\NewsletterPluginBundle\Entity\NewsletterGroup'),
          $this->em->getClassMetadata('Newscoop\NewsletterPluginBundle\Entity\NewsletterList'),
        );
    }
}