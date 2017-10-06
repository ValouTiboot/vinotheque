<?php



require_once(dirname(__FILE__) . '/classes/dump.php');

class Dolist extends Module
{
    /**
    * Soap Object
    */
    public $client;

    public function __construct()
    {
        $this->name = 'dolist';
        $this->version = '1.0.0';
        $this->author = 'yateo';
        $this->need_instance = 0;

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->trans('DoList Connect', array(), 'Modules.DoList.Admin');
        $this->description = $this->trans('Connect to dolist api and populate dolist database.', array(), 'Modules.DoList.Admin');

        $this->ps_versions_compliancy = array('min' => '1.7.1.0', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        return parent::install() 
            && Configuration::updateValue('DOLIST_API_LOGIN', '123')
            && Configuration::updateValue('DOLIST_API_TOKEN', 'ujnhtgnhqEEiZtWlbezl4+wgrOb2tJtTwz0fPj3RAG0rbqWF/9fwfdfrtKZSsi6mGmI3JMwUycDAeReZvEDkOqg==')
            && $this->registerHook('actionCustomerAccountAdd') 
            && $this->registerHook('actionCustomerAccountUpdate') 
        ;
    }

    public function uninstall()
    {
        return parent::uninstall() 
            && $this->unregisterHook('actionCustomerAccountAdd') 
            && $this->unregisterHook('actionCustomerAccountUpdate')
        ;
    }

    public function getContent()
    {
        return $this->postProcess().$this->renderForm();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitDolistConf')) 
        {
            Configuration::updateValue('DOLIST_API_LOGIN', Tools::getValue('DOLIST_API_LOGIN'));
            Configuration::updateValue('DOLIST_API_TOKEN', Tools::getValue('DOLIST_API_TOKEN'));

            if (!$this->testConnect())
                return $this->displayError($this->_errors);

            return $this->displayConfirmation($this->trans('The settings have been updated.', array(), 'Admin.Notifications.Success'));
        }
    }

    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->trans('Settings', array(), 'Admin.Global'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->trans('API login', array(), 'Modules.Cartgift.Admin'),
                        'name' => 'DOLIST_API_LOGIN',
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('API Token', array(), 'Modules.Cartgift.Admin'),
                        'name' => 'DOLIST_API_TOKEN',
                        'required' => true,
                    ),
                ),
                'submit' => array(
                    'title' => $this->trans('Save', array(), 'Admin.Actions')
                )
            ),
        );

        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->default_form_language = $lang->id;
        $helper->module = $this;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitDolistConf';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            // 'uri' => $this->getPathUri(),
            'fields_value' => array('DOLIST_API_LOGIN' => Configuration::get('DOLIST_API_LOGIN'), 'DOLIST_API_TOKEN' => Configuration::get('DOLIST_API_TOKEN')),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    public function hookActionCustomerAccountAdd($params)
    {
        return $this->addCustomerEmail($params);
    }

    public function hookActionCustomerAccountUpdate($params)
    {
        return $this->addCustomerEmail($params);
    }

    public function addCustomerEmail($params)
    {
        if ($params['customer']->newsletter != 1)
            return;

        if (($token = $this->testConnect()) !== false)
        {
            try 
            { 
                if (!empty($token))
                {
                    // Url du contrat wsdl
                    $proxywsdlContact = "http://api.dolist.net/V2/ContactManagementService.svc?wsdl";
                    $locationContact = "http://api.dolist.net/V2/ContactManagementService.svc/soap1.1";
                    
                    // Génération du proxy
                    $clientContact = new SoapClient($proxywsdlContact, array('trace' => 1, 'location' => $locationContact));
                     
                    // Création du jeton
                    $token = array('AccountID' => Configuration::get('DOLIST_API_LOGIN'),'Key' => $token);
                    
                    $fields = array('Name' => 'firstname', 'Value' => $params['customer']->firstname);
                    $fields = array('Name' => 'lastname', 'Value' => $params['customer']->lastname);
                    
                    $interests = array();
                    
                    $contact = array(
                        'Email' => $params['customer']->email,
                        'Fields' => $fields,
                        'InterestsToAdd' => $interests, //la liste des identifiants des interets déclarés à associer au contact
                        'InterestsToDelete' => $interests, //la liste des identifiants des interets déclarés à supprimer sur le contact
                        'OptoutEmail' => 0, //0: inscription, 1:désinscription
                        'OptoutMobile'=> 1 //0: inscription, 1:désinscription
                    );
                    
                    $contactRequest = array('token'=> $token,'contact'=> $contact);
                    
                    // Enregistrement du contact
                    $result = $clientContact->SaveContact($contactRequest);

                    if (!is_null($result->SaveContactResult) and $result->SaveContactResult != '')
                    {
                        $ticket = $result->SaveContactResult;
                    
                        // print "Ticket de la demande:".$ticket;
                        // print "<br/>";
                        
                        $contactRequest = array(
                            'token'=> $token,
                            'ticket'=> $ticket
                        );
                        
                        //recuperation de rsultat de l'opération (peut ne pas être disponible de suite)
                        $resultContact = $clientContact->GetStatusByTicket($contactRequest);
                        
                        // DEBUG
                        // var_dump($resultContact->GetStatusByTicketResult);die();
                    }
                    else
                        $this->_errors[] = "Erreur sur la mise à jour du contact";         
                }
                else 
                    $this->_errors[] = "Le token d'authentification est null.";
            }
            //Gestion d'erreur
            catch(SoapFault $fault) 
            {
                // print dumpVar($fault);
                $Detail = $fault->detail;
                $this->_errors[] = "Message : ".$Detail->ServiceException->Message;
                $this->_errors[] = "Description : ".$Detail->ServiceException->Description;   
            }
        }
    }

    public function testConnect()
    {
        try 
        {
            ini_set("soap.wsdl_cache_enabled", "0");
            ini_set("default_socket_timeout", 480);  
            
            // Url du contrat wsdl
            $proxywsdl = "http://api.dolist.net/V2/AuthenticationService.svc?wsdl";
            $location = "http://api.dolist.net/V2/AuthenticationService.svc/soap1.1";
            
            // Génération du proxy
            $this->client = new SoapClient($proxywsdl, array('trace' => 1, 'location' => $location));            

            // Renseigner la clé d'authentification avec l'identifiant client
            $authenticationInfos    = array('AuthenticationKey' => Configuration::get('DOLIST_API_TOKEN'), 'AccountID' => Configuration::get('DOLIST_API_LOGIN'));
            $authenticationRequest  = array('authenticationRequest' => $authenticationInfos);

            // Demande du jeton d'authentification
            $result = $this->client->GetAuthenticationToken($authenticationRequest);
            
            if (!is_null($result->GetAuthenticationTokenResult) and $result->GetAuthenticationTokenResult != '') {
                if ($result->GetAuthenticationTokenResult->Key != '') {
                    return $result->GetAuthenticationTokenResult->Key;
                }
                else {
                    $this->_errors[] = $result->GetAuthenticationTokenResult;
                }
            }
            else 
            {
                $this->_errors[] = "Le token d'authentification est null.";
            }

            if (count($this->_errors))
                return false;
        }
        //Gestion d'erreur
        catch(SoapFault $fault) 
        {
            // print dumpVar($fault);
            $Detail = $fault->detail;

            $this->_errors[] = "Message : ".$Detail->ServiceException->Message;
            $this->_errors[] = "Description : ".$Detail->ServiceException->Description;
        }
    }
}