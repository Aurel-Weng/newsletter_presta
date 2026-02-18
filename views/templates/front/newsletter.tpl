{extends file='customer/page.tpl'}

{block name='page_title'}
    {l s='My newsletter subscription' mod='wgl_newsletter'}
{/block}

{block name='page_content'}
    <div class="wgl_ns_container">
        {if isset($etat) && isset($message)}
            <div id="wgl_ns_result_req" class="wgl_ns_{$etat}">
                <p style="margin: auto">{$message}</p>
            </div>
        {/if}

        <form action="" method="post" class="wgl_ns_formulaire_modif">
            <div class="wgl_ns_informations">
                <div class="wgl_ns_email">
                    <label for="email">Votre email :</label>
                    <input type="text" name="email" id="email" value="{$subs['email']}" required>
                </div>

                <div class="wgl_ns_nom">
                    <label for="">Votre nom :</label>
                    <input type="text" name="nom" id="nom" value="{$subs['nom']}" required>
                </div>
            </div>

            <div class="wgl_ns_details">
                <div class="wgl_ns_secteurs">
                    <label>Secteurs inscrits :</label>

                    <div class="wgl_ns_liste">
                        {foreach $secteurs as $secteur}
                            <div class="wgl_ns_option-item">
                                <input type="checkbox" 
                                    name="secteurs[]" 
                                    id="{$secteur['name']}" 
                                    value="{$secteur['id']}"
                                    {if isset($secteur['chk']) && $secteur['chk'] == 'true' }
                                        checked
                                    {/if}
                                >
                                <label for="{$secteur['name']}">{$secteur['name']}</label>
                            </div>
                        {/foreach}
                    </div>
                </div>

                <div class="wgl_ns_confirmer">
                    <button type="submit" name="modification_subs">Enregistrer</button>
                </div>
            </div>
        </form>

        <form action="" method="post" class="wgl_ns_formulaire_supp">
            <div class="wgl_ns_confirmer">
                <input type="hidden" name="email" value="{$subs['email']}">
                <button type="button" class="wgl_ns_desinscrire">Se désinscrire</button>
                <div class="wgl_ns_supp_conf_container">
                    <p>Êtes-vous sûr de vous désinscrire ?</p>
                    <button type="button" class="wgl_ns_annuler">Annuler</button>
                    <button type="submit" class="wgl_ns_supp_confirme" name="suppression_subs">Confirmer</button>
                </div>
            </div>

        </form>
    </div>
    
    
{/block}