const btn = document.querySelector('.wgl_ns_desinscrire');
const annule = document.querySelector('.wgl_ns_annuler');
const container = document.querySelector('.wgl_ns_supp_conf_container');

console.log('JS chargée');

document.addEventListener('DOMContentLoaded', () => {
    btn.addEventListener('click', () => {
        console.log('Test');
        container.classList.toggle('active');
    });
    
    document.addEventListener('click', (e) => {
        if (annule && annule.contains(e.target)) {
            container.classList.remove('active');
            return;
        }
    
        if (btn && !btn.contains(e.target) && !container.contains(e.target)) {
            container.classList.remove('active');
        }
    });
})