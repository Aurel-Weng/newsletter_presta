<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

// require_once dirname(__FILE__) . '../../classes/ApiNewsletter.php';

class Wgl_NewsletterController extends ModuleFrontController {
    public $auth = true;
    public $ssl  = false;
    public $self_p = 'newsletter';

    public function init() {
        parent::init();

        if (!$this->context->customer->isLogged()) {
            Tools::redirect($this->context->link->getPageLink('authentification', true, null, 'back=' . urlencode($this->context->link->getModuleLink($this->module->name, 'newsletter'))));
        }
    }

    public function initContent() {
        parent::initContent();

        try {
            $this->setTemplate('module:wgl_newsletter/views/templates/front/inscription.tpl')
        } catch (\Exception $e) {
            $this->logError('Exception in initContent: ' . $e->getMessage());
            $this->errors[] = $this->trans('An error occurred while loading your subscriptions.', [], 'Modules.Wglnewsletter.Shop');
        }
    }

    public function getBreadcrumlinks() {
        $bc = parent::getBreadcrumlinks();

        $bc['links'][] = [
            'title' => $this->l('My Account', 'inscriptions'),
            'url'   => $this->context->link->getPageLink('my-account')
        ];

        $bc['links'][] = [
            'title' => $this->l('My newsletter subscriptions', 'inscriptions'),
            'url'   => ''
        ];

        return $bc;
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
            '[WGL_FAVORITEPRODUCTS] ' . $message,
            3, // Niveau ERROR
            null,
            'Module',
            $this->id,
            true
        );
    }
}