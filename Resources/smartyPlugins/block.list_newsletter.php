<?php
/**
 * @package Newscoop\NewsletterPluginBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */


/**
 * Newscoop newsletter block
 *
 * Type:     block
 * Name:     list_newslleter
 * Purpose:  Displays a form to subscribe to newsletter
 *
 * @param string
 *     $params
  * @param string
 *     $content
 * @param string
 *     $smarty
 *
 * @return
 *
 */
function smarty_block_list_newsletter($params, $content, &$smarty, &$repeat)
{
    $context = $smarty->getTemplateVars('gimme');

    if (!isset($content)) { // init
        $start = $context->next_list_start('Newscoop\NewsletterPluginBundle\TemplateList\NewsletterListsList');
        $list = \Zend_Registry::get('container')->get('newscoop.template_lists.newsletter');
        $list->getList($start, $params);
        if ($list->isEmpty()) {
            $context->setCurrentList($list, array());
            $context->resetCurrentList();
            $repeat = false;
            return;
        }

        $context->setCurrentList($list, array('newsletter_list'));
        $context->newsletter_list = $context->current_newsletter_lists_list->current;
        $repeat = true;
    } else { // next
        $context->current_newsletter_lists_list->defaultIterator()->next();
        if (!is_null($context->current_newsletter_lists_list->current)) {
            $context->newsletter_list = $context->current_newsletter_lists_list->current;
            $repeat = true;
        } else {
            $context->resetCurrentList();
            $repeat = false;
        }
    }

    return $content;
}
