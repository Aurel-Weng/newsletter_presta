<div class="wgl_ns_container_newsletter">
    <h2 class="wgl_ns_title">Newsletter</h2>
    <div class="wgl_ns_forms">
        <div class="wgl_ns_connexion_container" style="">
            <h2>Déjà abonné ?</h2>
            <p class="wgl_ns_tips">Renseigné votre adresse mail afin de pouvoir gérer vos souscriptions dans votre compte.</p>
            <form action="{$url}" id="connexion_form" method="post">
                <div class="wgl_ns_email">
                    <label for="email">Votre email :</label>
                    <input type="email" name="email" id="" placeholder="Votre nom" value="{$email}" required>
                </div>
            <button type="submit" name="wgl_ns_connexion" class="wgl_ns_btn btn-success" style="background-color: {$color} !important; border-color: {$color}">Connexion</button>
            </form>
        </div>

        <div class="wgl_ns_inscription_container" style="">
            <h2>Nouvelle inscription</h2>
            <form action="{$url}" id="inscription_form" method="post">
                <div class="wgl_ns_informations">
                    <div class="wgl_ns_email">
                        <label for="email">Votre email :</label>
                        <input type="email" name="email" id="" placeholder="votre@email.com" value="{$email}" required>
                    </div>

                    <div class="wgl_ns_nom">
                        <label for="nom">Votre nom :</label>
                        <input type="text" name="nom" id="" placeholder="Votre nom" value="{$nom}" required>
                    </div>

                </div>
                <div class="wgl_ns_container_secteur">
                    <div class="wgl_ns_select-container">
                        <label for="secteurs[]">Secteurs :</label>
                        <div class="wgl_ns_select-button" id="selectButton">
                            <span id="placeholder">Choisissez des options...</span>
                            <div class="wgl_ns_arrow"></div>
                        </div>
                    
                        <div class="wgl_ns_select-options" id="secteurs">
                            <div class="wgl_ns_option-item">
                                <input type="checkbox" name="tous" id="tous_secteurs">
                                <label for="tous_secteurs">Tous</label>
                            </div>

                            {foreach $secteurs as $secteur}
                                <div class="wgl_ns_option-item">
                                    <input type="checkbox"
                                        name="secteurs[]"
                                        id="{$secteur['name']}"
                                        value="{$secteur['id']}"
                                    >
                                    <label for="{$secteur['name']}">{$secteur['name']}</label>
                                </div>
                            {/foreach}

                        </div>
                
                    </div>

                    <div class="wgl_ns_conf">
                        <input type="checkbox" name="politiques_conf" id="politiques_conf" required>
                        <label for="politiques_conf">J'accepte de recevoir vos emails et je confirme avoir pris connaissance de la politique de confidentialité et mentions légale.</label>
                    </div>
                </div>

                <button type="submit" name="wgl_ns_inscription" class="wgl_ns_btn btn-success" style="background-color: {$color} !important; border-color: {$color}"">Inscrire</button>
            </form>
        </div>
    </div>
</div>