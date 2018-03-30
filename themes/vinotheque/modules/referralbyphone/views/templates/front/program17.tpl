{*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author Snegurka <site@web-esse.ru>
*  @copyright  2007-2017 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{extends file='page.tpl'}

{block name='page_title'}
  {l s='Referral program Plus' mod='referralbyphone'}
{/block}

{block name="page_content"}
    {if $referralbyphoneis17 != 1}
{capture name=path}<a href="{$link->getPageLink('my-account', true)|escape:'htmlall':'UTF-8'}" title="{l s='Manage my account' mod='referralbyphone'}" rel="nofollow">{l s='My account' mod='referralbyphone'}</a><span class="navigation-pipe">{$navigationPipe|escape:'htmlall':'UTF-8'}</span>{l s='Referral Program' mod='referralbyphone'}{/capture}


<h2>{l s='Referral program Plus' mod='referralbyphone'}</h2>
{/if}

{if $error}
    <p class="error alert-danger alert">
        {if $error == 'conditions not valided'}
            {l s='You need to agree to the conditions of the referral program!' mod='referralbyphone'}
        {elseif $error == 'email invalid'}
            {l s='At least one e-mail address is invalid!' mod='referralbyphone'}
        {elseif $error == 'name invalid'}
            {l s='At least one first name or last name is invalid!' mod='referralbyphone'}
        {elseif $error == 'email exists'}
            {l s='Someone with this e-mail address has already been sponsored!' mod='referralbyphone'}: {foreach from=$mails_exists item=mail}{$mail} {/foreach}
        {elseif $error == 'no revive checked'}
            {l s='Please mark at least one checkbox' mod='referralbyphone'}
        {elseif $error == 'cannot add friends'}
            {l s='Cannot add friends to database' mod='referralbyphone'}
        {/if}
    </p>
{/if}

{if $invitation_sent}
    <p class="success">
    {if $nbInvitation > 1}
        {l s='E-mails have been sent to your friends!' mod='referralbyphone'}
    {else}
        {l s='An e-mail has been sent to your friend!' mod='referralbyphone'}
    {/if}
    </p>
{/if}

{if $revive_sent}
    <p class="success">
    {if $nbRevive > 1}
        {l s='Reminder e-mails have been sent to your friends!' mod='referralbyphone'}
    {else}
        {l s='A reminder e-mail has been sent to your friend!' mod='referralbyphone'}
    {/if}
    </p>
{/if}

<div class="tabs">

<ul id="ws_referral_tabs" class="nav nav-tabs">
    <li class="nav-item"><a data-toggle="tab" href="#idTab1" class="tab-pane active" title="{l s='Sponsor my friends' mod='referralbyphone'}" rel="nofollow">{l s='Sponsor my friends' mod='referralbyphone'}</a></li>
    <li class="nav-item"><a data-toggle="tab" href="#idTab2" class="tab-pane {*{if $activeTab eq 'pending'} active{/if}*}" title="{l s='List of pending friends' mod='referralbyphone'}" rel="nofollow">{l s='Pending friends' mod='referralbyphone'}</a></li>
    <li class="nav-item"><a data-toggle="tab" href="#idTab3" class="tab-pane {*{if $activeTab eq 'subscribed'} active{/if}*}" title="{l s='List of friends I sponsored' mod='referralbyphone'}" rel="nofollow">{l s='Friends I sponsored' mod='referralbyphone'}</a></li>
    <li class="nav-item"><a data-toggle="tab" href="#idTab4" class="tab-pane {*{if $activeTab eq 'sponsor'} active{/if}*}" rel="nofollow">{l s='Statistics' mod='referralbyphone'}</a></li>
</ul>

<div class="tab-content">
    <div id="idTab1" class="tab-pane active">
        <p class="bold">
            <strong>{l s='Get a discount for you and your friends by recommending this Website.' mod='referralbyphone'}</strong>
        </p>
        <div class="s_vouchers">
	        {if $referralbyphoneis17 == 1}<i class="material-icons">euro_symbol</i>{else}<i class="icon-money" aria-hidden="true"></i>{/if}
	        <div class="s_vouchers_content">
	        {if isset($s_discount_acc) && $s_discount_acc}
	            {l s='You get a %1$s for each new customer.' sprintf=[$s_discount_acc] mod='referralbyphone'}<br />
	        {/if}
	        
	        {if isset($s_discount_ord) && $s_discount_ord}
	            {l s='You get a %1$s for each friend which place an order on this Website.' sprintf=[$s_discount_ord] mod='referralbyphone'}<br />
	        {/if}
	        
	        {if isset($s_discount_f_ord) && $s_discount_f_ord}
	            {l s='You get a %1$s  for each friend which place an order on this Website. (only first order)' sprintf=[$s_discount_f_ord] mod='referralbyphone'}<br />
	        {/if}
	        </div>
        </div>
        {if $canSendInvitations}
        <p>
            {l s='It is quick and it is easy.' mod='referralbyphone'}<br/>
            {l s='You can invite your friends in different ways:' mod='referralbyphone'}
        </p>
        <ul>
            <li class="reff_way">{if $referralbyphoneis17 == 1}<i class="material-icons">check</i>{else}<i class="icon-check-circle-o"></i>{/if}{l s='Give them your e-mail or' mod='referralbyphone'} <b>{l s='Your Rewards Code' mod='referralbyphone'}</b> <b class="text_green">{$ref_code}</b> {l s='to enter in the registration form.' mod='referralbyphone'}</li>
            <li class="reff_way">{if $referralbyphoneis17 == 1}<i class="material-icons">check</i>{else}<i class="icon-check-circle-o"></i>{/if}{l s='Share ready-made link. Copy and Share these ready-made Sponsor link to your friends, or post it on internet (forums, blog...)' mod='referralbyphone'}<br>{$sponsor_url|escape:'htmlall':'UTF-8'}</li>
            <li class="reff_way">{if $referralbyphoneis17 == 1}<i class="material-icons">check</i>{else}<i class="icon-check-circle-o"></i>{/if}{l s='Share products. Go to your favorite product. Next, click on the Share button to share your Sponsor Rewards links of these pages with others.' mod='referralbyphone'}</li>
            <li class="reff_way">{if $referralbyphoneis17 == 1}<i class="material-icons">check</i>{else}<i class="icon-check-circle-o"></i>{/if}{l s='Just fill in the first name, last name, and e-mail address(es) of your friend(s) in the fields below.' mod='referralbyphone'}</li>
        </ul>

        <form method="post" action="{$link->getModuleLink('referralbyphone', 'program', [], true)|escape:'htmlall':'UTF-8'}" class="std">
            <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="first_item">&nbsp;</th>
                    <th class="item">{l s='Last name' mod='referralbyphone'}</th>
                    <th class="item">{l s='First name' mod='referralbyphone'}</th>
                    <th class="last_item">{l s='E-mail' mod='referralbyphone'}</th>
                </tr>
            </thead>
            <tbody>
                {section name=friends start=0 loop=$nbFriends step=1}
                <tr class="{if $smarty.section.friends.index % 2}item{else}alternate_item{/if}">
                    <td class="align_right">{$smarty.section.friends.iteration|escape:'htmlall':'UTF-8'}</td>
                    <td><input type="text" class="form-control" name="friendsLastName[{$smarty.section.friends.index|escape:'htmlall':'UTF-8'}]" size="14" value="{if isset($smarty.post.friendsLastName[$smarty.section.friends.index])}{$smarty.post.friendsLastName[$smarty.section.friends.index]|escape:'htmlall':'UTF-8'}{/if}" /></td>
                    <td><input type="text" class="form-control" name="friendsFirstName[{$smarty.section.friends.index|escape:'htmlall':'UTF-8'}]" size="14" value="{if isset($smarty.post.friendsFirstName[$smarty.section.friends.index])}{$smarty.post.friendsFirstName[$smarty.section.friends.index]|escape:'htmlall':'UTF-8'}{/if}" /></td>
                    <td><input type="text" class="form-control" name="friendsEmail[{$smarty.section.friends.index|escape:'htmlall':'UTF-8'}]" size="20" value="{if isset($smarty.post.friendsEmail[$smarty.section.friends.index])}{$smarty.post.friendsEmail[$smarty.section.friends.index]|escape:'htmlall':'UTF-8'}{/if}" /></td>
                </tr>
                {/section}
            </tbody>
            </table>
            <p class="bold">
                <strong>{l s='Important: Your friends\' e-mail addresses will only be used in the referral program. They will never be used for other purposes.' mod='referralbyphone'}</strong>
            </p>
            <p class="checkbox">
                <input type="checkbox" name="conditionsValided" id="conditionsValided" value="1" {if isset($smarty.post.conditionsValided) AND $smarty.post.conditionsValided eq 1}checked="checked"{/if} />
                <label for="conditionsValided">{l s='I agree to the terms of service and adhere to them unconditionally.' mod='referralbyphone'}</label>
                {*<a href="{$link->getModuleLink('referralbyphone', 'rules', ['height' => '500', 'width' => '400'], true)|escape:'htmlall':'UTF-8'}" id="referral_rules"  title="{l s='Conditions of the referral program' mod='referralbyphone'}" rel="nofollow">{l s='Read conditions.' mod='referralbyphone'}</a>*}
                    <a id="referral_rules_link" href="#referral_rules" rel="{l s='Read conditions.' mod='referralbyphone'}">{l s='Read conditions.' mod='referralbyphone'}</a>
                    <div class="hidden">
                        <div id="referral_rules">
                        {if isset($xml)}
							<div id="referralbyphone_rules">
							    {if isset($xml->body->$paragraph)}<div class="rte">{$xml->body->$paragraph|replace:"\'":"'"|replace:'\"':'"'|escape:'quotes':'UTF-8' nofilter}</div>{/if}
							</div>
						{/if}
                    </div>
                </div>
            </p>
            {*
            <p class="see_email">
                {l s='Preview' mod='referralbyphone'}
                {assign var="file" value="{$lang_iso}/referralbyphone-invitation.html"}
                <a href="{$link->getModuleLink('referralbyphone', 'email', ['height' => '500', 'width' => '600', 'mail' => {$file|escape:'htmlall':'UTF-8'}], true)|escape:'htmlall':'UTF-8'}" class="thickbox" title="{l s='Invitation e-mail' mod='referralbyphone'}" rel="nofollow">{l s='the default e-mail' mod='referralbyphone'}</a> {l s='that will be sent to your friend(s).' mod='referralbyphone'}
            </p>
            *}
            <p class="submit">
                <button type="submit" id="submitSponsorFriends" name="submitSponsorFriends" class="btn btn-default button button-medium"><span>{l s='Validate' mod='referralbyphone'}<i class="icon-chevron-right right"></i></span></button>
            </p>
        </form>
        {else}
        <p class="alert alert-warning">
            {l s='To become a sponsor, you need to have completed at least' mod='referralbyphone'} {$orderQuantity|escape:'htmlall':'UTF-8'} {if $orderQuantity > 1}{l s='orders' mod='referralbyphone'}{else}{l s='order' mod='referralbyphone'}{/if}.
        </p>
        {/if}
    </div>

    <div id="idTab2" class="tab-pane">
    {if $pendingFriends AND $pendingFriends|@count > 0}
        <p>
            {l s='These friends have not yet placed an order on this Website since you sponsored them, but you can try again! To do so, mark the checkboxes of the friend(s) you want to remind, then click on the button "Remind my friend(s)"' mod='referralbyphone'}
        </p>
        <form method="post" action="{$link->getModuleLink('referralbyphone', 'program', [], true)|escape:'htmlall':'UTF-8'}" class="std">
            <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="first_item">&nbsp;</th>
                    <th class="item">{l s='Last name' mod='referralbyphone'}</th>
                    <th class="item">{l s='First name' mod='referralbyphone'}</th>
                    <th class="item">{l s='E-mail' mod='referralbyphone'}</th>
                    <th class="last_item"><b>{l s='Last invitation' mod='referralbyphone'}</b></th>
                </tr>
            </thead>
            <tbody>
            {foreach from=$pendingFriends item=pendingFriend name=myLoop}
                <tr>
                    <td>
                        <input type="checkbox" name="friendChecked[{$pendingFriend.id_referralbyphone|escape:'htmlall':'UTF-8'}]" id="friendChecked[{$pendingFriend.id_referralbyphone|escape:'htmlall':'UTF-8'}]" value="1" />
                    </td>
                    <td>
                        <label for="friendChecked[{$pendingFriend.id_referralbyphone|escape:'htmlall':'UTF-8'}]">{$pendingFriend.lastname|escape:'htmlall':'UTF-8'|substr:0:22}</label>
                    </td>
                    <td>{$pendingFriend.firstname|escape:'htmlall':'UTF-8'|substr:0:22}</td>
                    <td>{$pendingFriend.email|escape:'htmlall':'UTF-8'}</td>
                    <td>{dateFormat date=$pendingFriend.date_upd full=1}</td>
                </tr>
            {/foreach}
            </tbody>
            </table>
            <p class="submit">
                <button type="submit" name="revive" id="revive" class="button_large btn btn-default">{l s='Remind my friend(s)' mod='referralbyphone'}</button>
            </p>
        </form>
        {else}
            <p class="alert alert-warning">
                {if $subscribeFriends AND $subscribeFriends|@count > 0}
                    {l s='You have no pending invitations.' mod='referralbyphone'}
                {else}
                    {l s='You have not sponsored any friends yet.' mod='referralbyphone'}
                {/if}
            </p>
        {/if}
    </div>

    <div id="idTab3" class="tab-pane">
    {if $subscribeFriends AND $subscribeFriends|@count > 0}
        <p>
            {l s='Here are sponsored friends who have accepted your invitation:' mod='referralbyphone'}
        </p>
        <table class="table table-bordered">
        <thead>
            <tr>
                <th class="first_item">&nbsp;</th>
                <th class="item">{l s='Last name' mod='referralbyphone'}</th>
                <th class="item">{l s='First name' mod='referralbyphone'}</th>
                <th class="item">{l s='E-mail' mod='referralbyphone'}</th>
                <th class="item">{l s='Placed orders' mod='referralbyphone'}</th>
                <th class="item">{l s='Customers sponsored by this friend' mod='referralbyphone'}</th>
                <th class="last_item">{l s='Inscription date' mod='referralbyphone'}</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$subscribeFriends item=subscribeFriend name=myLoop}
            <tr>
                <td>{$smarty.foreach.myLoop.iteration|escape:'htmlall':'UTF-8'}.</td>
                <td>{$subscribeFriend.lastname|escape:'htmlall':'UTF-8'|substr:0:22}</td>
                <td>{$subscribeFriend.firstname|escape:'htmlall':'UTF-8'|substr:0:22}</td>
                <td>{$subscribeFriend.email|escape:'htmlall':'UTF-8'}</td>
                <td>{$subscribeFriend.orders_count|escape:'htmlall':'UTF-8'}</td>
                <td>{$subscribeFriend.sponsored_friend_count|escape:'htmlall':'UTF-8'}</td>
                <td>{dateFormat date=$subscribeFriend.date_upd full=1}</td>
            </tr>
            {/foreach}
        </tbody>
        </table>
    {else}
        <p class="alert alert-warning">
            {l s='No sponsored friends have accepted your invitation yet.' mod='referralbyphone'}
        </p>
    {/if}
    </div>
    
    <div id="idTab4" class="tab-pane">
            <p>
            {l s='Here is your detailed statistics:' mod='referralbyphone'}
        </p>
        <table class="table table-bordered">
        <tbody>
            <tr>
                <td>{l s='Sponsored customers:' mod='referralbyphone'}</td>
                <td>{count($subscribeFriends)|intval}</td>
            </tr>
            <tr>
                <td>{l s='Total friends orders:' mod='referralbyphone'}</td>
                <td>{$friends_total_orders|escape:'htmlall':'UTF-8'}</td>
            </tr>
        </tbody>
        </table>
    </div>
    
    </div>
</div>

<ul class="footer_links clearfix">
    <li>
        <a class="btn btn-default button button-small" href="{$link->getPageLink('my-account', true)|escape:'htmlall':'UTF-8'}" title="{l s='Back to Your Account' mod='referralbyphone'}" rel="nofollow">
        <span><i class="icon-chevron-left"></i> {l s='Back to Your Account' mod='referralbyphone'}</span></a>
    </li>
    {*
    <li><a class="btn btn-default button button-small" href="{if isset($force_ssl) && $force_ssl}{$base_dir_ssl|escape:'htmlall':'UTF-8'}{else}{$base_dir|escape:'htmlall':'UTF-8'}{/if}" title="{l s='Home' mod='referralbyphone'}"><span><i class="icon-chevron-left"></i>{l s='Home' mod='referralbyphone'}</span></a></li>
    *}
</ul>

{/block}