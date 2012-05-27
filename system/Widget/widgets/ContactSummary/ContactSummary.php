<?php

/**
 * @package Widgets
 *
 * @file Roster.php
 * This file is part of MOVIM.
 *
 * @brief The Roster widget
 *
 * @author Jaussoin Timothée <edhelas@gmail.com>
 *
 * @version 1.0
 * @date 30 August 2010
 *
 * Copyright (C)2010 MOVIM project
 *
 * See COPYING for licensing information.
 */

class ContactSummary extends WidgetBase
{

    function WidgetLoad()
    {
    	$this->addcss('contactsummary.css');
		$this->registerEvent('vcard', 'onVcard');
    }
    
    function onVcard($contact)
    {
        $html = $this->prepareContactSummary($contact);
        RPC::call('movim_fill', 'contactsummary', RPC::cdata($html));
    }
    
	function ajaxRefreshVcard($jid)
	{
		$this->xmpp->getVCard($jid);
	}
    
    private function testIsSet($element)
    {
        if(isset($element) && $element != '')
            return true;
        else
            return false;
    }
	
	function prepareContactSummary($contact)
	{
        $gender = getGender();
        $marital = getMarital();
        
        $presence = PresenceHandler::getPresence($contact->getData('jid'), true);
        $html ='<h1>'.$contact->getTrueName().'</h1><img src="'.$contact->getPhoto().'"/>';
        
        if($contact->getData('vcardreceived') != 1)
            $html .= '<script type="text/javascript">setTimeout(\''.$this->genCallAjax('ajaxRefreshVcard', '"'.$contact->getData('jid').'"').'\', 500);</script>';
            
        if($presence != NULL)
            $html .= '<div id="status">'.$presence['status'].'</div>';
            
        $html .='<h2>'.t('General Informations').'</h2>';
            
        if($this->testIsSet($contact->getData('name')))
            $html .= $contact->getData('name').'<br />';
            
        if($contact->getData('gender') != 'N' && $this->testIsSet($contact->getData('gender')))
            $html .= $gender[$contact->getData('gender')].'<br />';
            
        if($contact->getData('marital') != 'none' && $this->testIsSet($contact->getData('marital')))
            $html .= '<span class="hearth"></span>'.$marital[$contact->getData('marital')].'<br />';
            
        if($contact->getData('date') != '0000-00-00' && $this->testIsSet($contact->getData('date')))
            $html .= '<span class="birth"></span>'.date('j M Y',strtotime($contact->getData('date'))).'<br />';
            
        if($this->testIsSet($contact->getData('jid')))
            $html .= '<span class="address"></span>'.$contact->getData('jid').'<br />';
            
        if($this->testIsSet($contact->getData('url')))
            $html .= '<span class="website"></span>'.'<a target="_blank" href="'.$contact->getData('url').'">'.$contact->getData('url').'</a>';
        //if($this->testIsSet($contact->getData('desc')))
        //    $html .= t('About Me').prepareString($contact->getData('desc'));
        
        if($presence['node'] != '' && $presence['ver'] != '') {
            $clienttype = 
                array(
                    'bot' => t('Bot'),
                    'pc' => t('Desktop'),
                    'phone' => t('Phone')
                    );
            
            $c = new CapsHandler();
            $caps = $c->get($presence['node'].'#'.$presence['ver']);
            

            

            if($this->testIsSet($caps->getData('type'))) {
                if($caps->getData('type') == 'phone')
                    $cinfos = '<span class="mobile"></span>';
            }
            if($this->testIsSet($caps->getData('name')))
                $cinfos .=  $caps->getData('name').'<br />';
            if($cinfos != "")
                $html .='<h2>'.t('Client Informations').'</h2>' . $cinfos;
        }

        return $html;
	}
    
    function build()
    {
        global $sdb;
        $contact = $sdb->select('Contact', array('key' => $this->user->getLogin(), 'jid' => $_GET['f']));
        ?>
        <div id="contactsummary">
        <?php
        if(isset($contact[0])) {
            echo $this->prepareContactSummary($contact[0]);
        ?>
        <div class="config_button" onclick="<?php $this->callAjax('ajaxRefreshVcard', "'".$contact[0]->getData('jid')."'");?>"></div>
        <?php } 
        
        else {
        ?>
        <script type="text/javascript"><?php $this->callAjax('ajaxRefreshVcard', "'".$_GET['f']."'");?></script>
        <?php } ?>
        </div>
        <?php
    }
}
