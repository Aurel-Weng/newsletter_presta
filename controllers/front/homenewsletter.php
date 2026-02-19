<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/../../classes/ApiNewsletter.php';

class Wgl_NewsletterHomenewsletterModuleFrontController extends ModuleFrontController
{
    public $ajax = true;

    public function init() {
        $this->logInfo('Lancement des req api');
        parent::init();

        if (!$this->isXmlHttpRequest()) {
            $this->logInfo('Erreur méthode');

            $this->ajaxRender(json_encode([
                'etat'   => 'error',
                'message'=> 'Accès refusé, méthode invalide.'
            ]));
            return;
        }
        $this->displayAjax();
    }

    public function displayAjax()
    {
        header('Content-Type: application/json');

        $api = new ApiNewsletter();
        $response = [
            'etat' => 'error',
            'message' => 'Requête invalide'
        ];

        if (!$this->isXmlHttpRequest()) {
            $this->logInfo('Erreur méthode');

            die(json_encode([
                'etat' => 'error',
                'message' => 'Accès non autorisé'
            ]));
        }

        // Partie connexion
        if (Tools::isSubmit('wgl_ns_connexion')) {
            $this->logInfo('Form Connexion');

            // On regarde s'il y a une inscription pour ce mail
            $exist = $api->existe([
                'email' => Tools::getValue('email')
            ]);

            if (isset($exist['etat']) && $exist['etat'] === 'success') {

                $data = [
                    'email' => Tools::getValue('email'),
                    'id_client' => (string)$this->context->customer->id,
                ];

                $result = $api->liaison($data);

                if (isset($result['etat']) && $result['etat'] === 'success') {
                    $response = [
                        'etat' => 'success',
                        'message' => 'Connexion réussie'
                    ];
                } else {
                    $response = [
                        'etat' => 'error',
                        'message' => 'Erreur lors de la connexion, veuillez réessayer plus tard'
                    ];
                }
            } else {
                $response = [
                    'etat' => 'error',
                    'message' => 'Aucunes inscriptions avec cette adresse mail'
                ];
            }
        }
        // Partie inscription
        elseif (Tools::isSubmit('wgl_ns_inscription')) {
            $this->logInfo('Form inscription');

            $data = [
                'name'      => Tools::getValue('nom'),
                'email'     => Tools::getValue('email'),
                'secteurs'  => Tools::getValue('secteurs'),
                'platform'  => 'prestashop',
                'id_client' => (string)$this->context->customer->id,
                'site'      => null,
            ];

            $result = $api->inscription($data);

            if (isset($result['etat']) && $result['etat'] === 'success') {
                $response = [
                    'etat' => 'success',
                    'message' => 'Inscription réussie'
                ];
            } else {
                $response = [
                    'etat' => 'error',
                    'message' => $result['message'] ?? 'Erreur lors de l\'inscription'
                ];
            }
        }

        header('Content-Type: application/json');
        die(json_encode($response));
    }

    public function display() {}

    /**
     * Fonction qui vérifie que c'est une requête ajax
     */
    public function isXmlHttpRequest()
    {
        return (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) || Tools::getValue('ajax') === '1';
    }

    /**
     * Méthode pour logger les erreurs
     * 
     * @param string $message Message d'erreur
     */
    private function logError($message)
    {
        $this->writeLog('ERROR', $message);
        PrestaShopLogger::addLog(
            '[WGL_NEWSLETTER_AJAX] ' . $message,
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
