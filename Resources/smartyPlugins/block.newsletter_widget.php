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
 * Name:     newslleter
 * Purpose:  Displays a form to subscribe to newsletter
 *
 * @param string
 *     $p_params
 * @param string
 *     $p_smarty
 * @param string
 *     $p_content
 *
 * @return
 *
 */
function smarty_block_newsletter_widget($params, $p_content, &$smarty, &$p_repeat)
{
    if (!isset($p_content)) {
        return '';
    }

    $smarty->smarty->loadPlugin('smarty_shared_escape_special_chars');
    $context = $smarty->getTemplateVars('gimme');

    $html = "
        <div class='js-newsletter-widget-container'></div>
        <script type='text/javascript'>
        $.ajax({
            type: 'POST',
            url: '/newsletter-plugin/widget',
            dataType: 'html',
            success: function(msg){
                $('.js-newsletter-widget-container').html(msg);
            }
        });
        </script>";

    return $html;
}
