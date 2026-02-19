<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class Wgl_Newsletter extends Module
{
    public function __construct()
    {
        $this->name = 'wgl_newsletter';
        $this->tab = 'customers';
        $this->version = '1.0.0';
        $this->author = 'Aurélien';
        $this->need_instance = 0;
        $this->bootstrap = true;

        $this->ps_versions_compliancy = array(
            'min' => '1.7.0.0',
            'max' => _PS_VERSION_,
        );

        parent::__construct();

        $this->displayName = 'Wengel - Newsletter';
        $this->description = 'Module de newsletter lié au WordPress & tracking';
    }

    public function install() {
        return parent::install()
            && Configuration::updateValue('wgl_newsletter_url', '')
            && Configuration::updateValue('wgl_newsletter_token', '')
            && Configuration::updateValue('wgl_newsletter_color', '#fff')
            && $this->registerHook('displayCustomerAccount')
            && $this->registerHook('displayFooterBefore')
            && $this->registerHook('actionFrontControllerSetMedia');
    }

    public function uninstall() {
        return parent::uninstall()
            && Configuration::deleteByName('wgl_newsletter_url', '')
            && Configuration::deleteByName('wgl_newsletter_token', '')
            && Configuration::deleteByName('wgl_newsletter_color', '');
    }

    /**
     * Affiche le formulaire de configuration
     */
    public function getContent() {
        $output = '';

        if (Tools::isSubmit('submitWgl_NewsletterModule')) {
            $urlVal = trim((string) Tools::getValue('wgl_newsletter_url'));
            $tokenVal = trim((string) Tools::getValue('wgl_newsletter_token'));
            $colorVal = trim((string) Tools::getValue('wgl_newsletter_color'));

            $errors = [];

            if (empty($urlVal) || !Validate::isUrl($urlVal)) {
                $errors[] = $this->l('URL invalide');
            }

            if (empty($tokenVal) || !Validate::isReference($tokenVal)) {
                $errors[] = $this->l('Token invalide');
            }

            if (empty($colorVal) || !Validate::isColor($colorVal)) {
                $errors[] = $this->l('Couleur invalide');
            }

            if ($errors) {
                $output = $this->displayError(implode('<br>', $errors));
            } else {
                Configuration::updateValue('wgl_newsletter_url', $urlVal);
                Configuration::updateValue('wgl_newsletter_token', $tokenVal, Tools::encrypt(''));
                Configuration::updateValue('wgl_newsletter_color', $colorVal);
            }
        }
        return $output . $this->renderForm();
    }

    /**
     * Créer l'affichage du formulaire de configuration
     */
    protected function renderForm() {
        $form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon'  => 'icon-cogs',
                ],
                'input' => [
                    [
                        'col'   => 5,
                        'prefix'=> '<i class="icon icon-external-link"></i>',
                        'type'  => 'text',
                        'label' => $this->l('Url'),
                        'name'  => 'wgl_newsletter_url',
                        'required'  => true,
                    ],
                    [
                        'col'   => 5,
                        'prefix'=> '<i class="icon icon-lock"></i>',
                        'type'  => 'text',
                        'label' => $this->l('Token'),
                        'name'  => 'wgl_newsletter_token',
                        'required'  => true,
                    ],
                    [
                        'col'   => 5,
                        'type'  => 'text',
                        'label' => $this->l('Couleur'),
                        'name'  => 'wgl_newsletter_color',
                        'class' => 'jscolor',
                    ]
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];

        $this->context->controller->addJS($this->_path . 'views/js/jscolor.js');

        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;

        $helper->submit_action = 'submitWgl_NewsletterModule';
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->fields_value['wgl_newsletter_url'] = Tools::getValue('wgl_newsletter_url', Configuration::get('wgl_newsletter_url'));
        $helper->fields_value['wgl_newsletter_token'] = Tools::getValue('wgl_newsletter_token', Configuration::get('wgl_newsletter_token'));
        $helper->fields_value['wgl_newsletter_color'] = Tools::getValue('wgl_newsletter_color', Configuration::get('wgl_newsletter_color'));

        return $helper->generateForm([$form]);
    }

    public function hookDisplayCustomerAccount() {
        $this->context->smarty->assign([
            'subsUrl' => $this->context->link->getModuleLink($this->name, 'newsletter')
        ]);

        return $this->display(__FILE__, 'views/templates/hook/account-subs-link.tpl');
    }

    public function hookDisplayFooterBefore() {
        require_once dirname(__FILE__) . '/classes/ApiNewsletter.php';

        $api = new ApiNewsletter();
        $id_client = null;

        if ($this->context->customer->isLogged()) {
            $id_client = (int)$this->context->customer->id;

            $co = $api->connecte(['id_client'=>$id_client, 'site'=>null]);

            if (isset($co['etat']) && $co['etat'] != 'success') {
                $response_sect = $api->secteurs([]);
                $data = array_map(function ($secteur) {
                    return [
                        'id'    => (string) $secteur['id'],
                        'name'  => (string) $secteur['name'],
                        'state' => (string) $secteur['state'],
                    ];
                }, $response_sect);

                $couleur = Configuration::get('wgl_newsletter_color');
                $shadows = "box-shadow: 0 0 20px 0px ".$couleur."c4 !important; -webkit-box-shadow: 0 0 20px 0px ".$couleur."c4; !important -moz-box-shadow: 0 0 20px 0px ".$couleur."c4 !important;";

                $this->context->smarty->assign([
                    'url' => $this->context->link->getModuleLink($this->name, 'homenewsletter', [], true),
                    'shadow' => $shadows,
                    'color'  => $couleur,
                    'secteurs' => $data
                ]);

                return $this->fetch('module:' . $this->name . '/views/templates/front/home-newsletter.tpl');
            }
        }
    }

    public function hookActionFrontControllerSetMedia() {
        $this->context->controller->registerStylesheet('module-wgl_newsletter_front-css', 'modules/' . $this->name . '/views/css/front.css');
        $this->context->controller->registerJavascript('module-wgl_newsletter_front-js', 'modules/' . $this->name . '/views/js/front.js');
    }
}