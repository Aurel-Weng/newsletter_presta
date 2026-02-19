<?php

class ApiNewsletter {
    protected $url;
    protected $token;

    public function __construct() {
        $this->url = Tools::getValue('wgl_newsletter_url', Configuration::get('wgl_newsletter_url'));
        $this->token = Tools::getValue('wgl_newsletter_token', Configuration::get('wgl_newsletter_token'));
    }

    /**
     * Requête api
     * 
     * @return json Le résultat des requêtes
     */
    public function getData($req, $data=null, $methode='POST') {
        if (!empty($this->url) && !empty($this->token)) {
            $ch = curl_init($this->url . $req);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $this->token,
                'Content-type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $methode);

            if ($data != null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }

            $response = curl_exec($ch);

            curl_close($ch);

            return json_decode($response, true);
        }
    }

    // Requête pour voir si un utilisateur est inscrit à une newsletter
    public function existe($data) {
        return $this->getData('/api/subs_newsletter/existe', $data);
    }

    // Requête pour s'inscrire
    public function inscription($data) {
        return $this->getData('/api/subs_newsletter/inscription', $data);
    }

    // Requête pour modifier ses souscriptions
    public function modification($data) {
        return $this->getData('/api/subs_newsletter/modification', $data);
    }

    // Requête pour se désinscrire
    public function desinscription($data) {
        return $this->getData('/api/subs_newsletter/supprimer/' . $data ['email'], $data);
    }

    // Requête pour récupérer les souscriptions
    public function affichage($data) {
        $path = '/api/subs_newsletter/getmysubs/' . $data['id'] . '/' . $data['site'];
        return $this->getData($path);
    }

    // Requête pour connecter le compte aux souscriptions
    public function liaison($data) {
        return $this->getData('/api/subs_newsletter/liaison', $data);
    }

    // Requête pour voir si le compte est connecté à des souscriptions
    public function connecte($data) {
        return $this->getData('/api/subs_newsletter/liaison_existe', $data);
    }

    // Requête pour récupérer les ecteurs
    public function secteurs($data) {
        return $this->getData('/api/secteurs/', null, 'GET');
    }
}