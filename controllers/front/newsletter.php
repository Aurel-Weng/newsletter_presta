<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/../../classes/ApiNewsletter.php';

class Wgl_NewsletterNewsletterModuleFrontController extends ModuleFrontController {
    public $auth = true;
    public $id_client = null;
    public $exist = false;
    public $error = [];
    public $api;

    /**
     * Initialise le controller
     */
    public function init() {
        parent::init();

        $this->api = new ApiNewsletter();

        if (!$this->context->customer->isLogged()) {
            Tools::redirect($this->context->link->getPageLink('authentification', true, null, 'back=' . urlencode($this->context->link->getModuleLink($this->module->name, 'newsletter'))));
        } else {
            $this->id_client = (int)$this->context->customer->id;

            // Regarde si l'utilisateur est connecté à ses souscriptions
            $response_subs = $this->api->affichage(['id' => $this->id_client, 'site' => null]);
            if (empty($response_subs['erreur'])) {
                $this->exist = true;
            }
        }
    }

    /**
     * Initialise le rendu
     */
    public function initContent() {
        parent::initContent();

        try {

            if (Tools::getValue('etat')) {
                $this->context->smarty->assign([
                    'etat'    => Tools::getValue('etat'),
                    'message' => Tools::getValue('message'),
                ]);
            }

            $data = $this->getInfo();

            $this->context->smarty->assign([
                'secteurs'  => $data['secteurs'],
                'subs'      => $data['subs'],
            ]);

        } catch (\Exception $e) {
            $this->logError('Exception in initContent: ' . $e->getMessage());
            $this->errors[] = (string) $this->trans(
                'An error occurred while loading your subscriptions.',
                [],
                'Modules.Wglnewsletter.Shop'
            );
        }

        $this->setMedia();
        $this->setTemplate('module:wgl_newsletter/views/templates/front/newsletter.tpl');
    }

    /**
     * Requête sur les formulaires
     */
    public function postProcess() {
        $result = null;

        // Partie modification des souscriptions
        if (Tools::isSubmit('modification_subs')) {
            $data = [
                'name'      => Tools::getValue('nom'),
                'email'     => Tools::getValue('email'),
                'secteurs'  => Tools::getValue('secteurs'),
                'platform'  => 'prestashop',
            ];

            if (!$this->exist) {
                $data['id_client'] = (string) $this->id_client;
                $data['site'] = null;

                $result = $this->api->inscription($data);
            } else {
                echo "Modif : \n";
                $result = $this->api->modification($data);
            }
            Tools::redirect($this->context->link->getModuleLink($this->module->name, 'newsletter', $result));
        }
        
        // Partie de désinscription
        if (Tools::isSubmit('suppression_subs')) {
            $data = [
                'email'     => Tools::getValue('email'),
                'platform'  => 'prestashop',
            ];

            $result = $this->api->desinscription($data);

            Tools::redirect($this->context->link->getModuleLink($this->module->name, 'newsletter', $result));
        }         
    }

    /**
     * Récupération des infos
     * 
     * @return array Les infos (souscriptions et secteurs)
     */
    public function getInfo() {
        $data = [];

       // Partie récupération des inscriptions du user
        $response_subs = $this->api->affichage(['id' => $this->id_client, 'site' => null]);
        if (!empty($response_subs['erreur'])) {
            $data['subs'] = [
                'email'     => '',
                'nom'       => '',
                'secteurs'  => [],
            ];
        } else {
            $data['subs'] = [
                'email'     => $response_subs['email'],
                'nom'       => $response_subs['nom'],
                'secteurs'  => $response_subs['secteurs'],
            ];
        }

        // Partie récupération des secteurs
        $response_sect = $this->api->secteurs([]);

        $data['secteurs'] = array_map(function ($secteur) {
             return [
                 'id'    => (string) $secteur['id'],
                 'name'  => (string) $secteur['name'],
                 'state' => (string) $secteur['state'],
                 'chk'   => false,
             ];
        }, $response_sect);

        foreach ($data['secteurs'] as $key => $value) {
            if (in_array( $value['id'], $data['subs']['secteurs']) || in_array($value['name'], $data['subs']['secteurs'])) {
                $data['secteurs'][$key]['chk'] = true;
            }
        }

        return $data;
    }

    /**
     * Ajoute les médias (js et css)
     */
    public function setMedia()
    {
        parent::setMedia();

        $this->registerStylesheet(
            'module-wgl-newsletter-style',
            'modules/'.$this->module->name.'/views/css/liste.css',
            [
                'media' => 'all',
                'priority' => 150,
            ]
        );

        $this->registerJavascript(
            'module-wgl-newsletter-script',
            'modules/'.$this->module->name.'/views/js/liste.js',
            [
                'position' => 'bottom',
                'priority' => 150,
            ]
        );
    }

    /**
     * Met à jour la fils d'ariane
     */
    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        
        $breadcrumb['links'][] = [
            'title' => $this->l('My Account', 'newsletter'),
            'url' => $this->context->link->getPageLink('my-account')
        ];
        
        $breadcrumb['links'][] = [
            'title' => $this->l('My subscriptions', 'newsletter'),
            'url' => ''
        ];
        
        return $breadcrumb;
    }

    private function logError($message)
    {
        $this->writeLog('ERROR', $message);
        PrestaShopLogger::addLog(
            '[WGL_NEWSLETTER] ' . $message,
            3, // Niveau ERROR
            null,
            'Module',
            null,
            true
        );
    }

    /**
     * Méthode pour logger les informations
     * 
     * @param string $message Message d'information
     */
    private function logInfo($message)
    {
        $this->writeLog('INFO', $message);
    }

    /**
     * Méthode pour écrire dans le fichier de log personnalisé
     * 
     * @param string $level Niveau du log (ERROR, WARNING, INFO)
     * @param string $message Message à logger
     */
    private function writeLog($level, $message)
    {
        $logDir = _PS_ROOT_DIR_ . '/var/logs/';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . 'wgl_newsletter.log';
        $timestamp = date('Y-m-d H:i:s');
        $ip = Tools::getRemoteAddr();
        $customer = $this->context->customer->isLogged() ? 
            ' [Customer: ' . $this->context->customer->id . ']' : 
            ' [Guest]';
        
        $logEntry = sprintf(
            "[%s] [%s] %s %s - %s\n",
            $timestamp,
            $level,
            $ip,
            $customer,
            $message
        );
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}