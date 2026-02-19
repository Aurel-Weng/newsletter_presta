const slc_btn = document.getElementById('selectButton');
const secteurs = document.getElementById('secteurs');
const all = document.getElementById('tous_secteurs');
const checkboxes = document.querySelectorAll('input[name="secteurs[]"]');
const placeholder = document.getElementById('placeholder');

// Update le texte du champ de select secteur
function updateTxt() {
    const cpt = Array.from(checkboxes).filter(cb => cb.checked).length;
    placeholder.textContent = cpt === 0
        ? 'Choisissez des options...'
        : `${cpt} option${cpt > 1 ? 's' : ''} sélectionnée${cpt > 1 ? 's' : ''}`;
}

// Affiche ou non les secteurs dans les select
slc_btn.addEventListener('click', () => {
    secteurs.classList.toggle('active');
});

document.addEventListener('click', (e) => {
    if (!slc_btn.contains(e.target) && !secteurs.contains(e.target)) {
        secteurs.classList.remove('active');
    }
});

// Coche / décoche tout les secteurs en fonction de la valeur de "Tous" 
all.addEventListener('change', () => {
    checkboxes.forEach(cb => cb.checked = all.checked);
    updateTxt();
});

checkboxes.forEach(cb => cb.addEventListener('change', updateTxt));

// Envoi des formulaire depuis le footer
document.addEventListener('submit', function (e) {
    const form = e.target;
    let submitBtn;

    console.log(form.id);

    if (form.id !== 'connexion_form' && form.id !== 'inscription_form') {
        return;
    }

    e.preventDefault();

    const btns = document.querySelectorAll('.wgl_ns_forms button[type="submit"]');
    btns.forEach(btn => {
        btn.disabled = true;
        btn.style.backgroundColor = '#bfd4008a';
    });

    const formData = new FormData(form);

    if (form.id === 'connexion_form') {
        formData.append('wgl_ns_connexion', '1');
        submitBtn = this.querySelector('#connexion_form button[type="submit"]');
    } else if (form.id === 'inscription_form') {
        formData.append('wgl_ns_inscription', '1');
        submitBtn = this.querySelector('#inscription_form button[type="submit"]');
    }
    submitBtn.innerText = "En cours";

    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        const title = document.querySelector('.wgl_ns_title');
        const container = document.querySelector('.wgl_ns_container_newsletter');

        container.querySelectorAll('.alert').forEach(el => el.remove());

        const alert = document.createElement('div');
        alert.className = 'alert ' + (data.etat == 'success' ? 'alert-success' : 'alert-danger');
        alert.innerText = data.message;
        title.insertAdjacentElement('afterend', alert);

        if (data.etat == 'success') {
            document.querySelector('.wgl_ns_forms')?.remove();
        } else {
            btns.forEach(btn => {
                btn.disabled = false;
                btn.style.backgroundColor = '#bfd400';
            });
        }
    })
    .catch(() => {
        btns.forEach(btn => {
            btn.disabled = false;
            btn.style.backgroundColor = '#bfd400';
        });
    });
});