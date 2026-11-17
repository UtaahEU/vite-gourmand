// Filtres des menus sans rechargement de la page (fetch + JSON)
const formulaire = document.getElementById('form-filtres');
const resultats = document.getElementById('resultats');
const compteur = document.getElementById('nombre-menus');
let minuteur;

function texteNombre(n) {
    return n + ' menu' + (n > 1 ? 's' : '') + ' trouvé' + (n > 1 ? 's' : '');
}

async function filtrer() {
    const parametres = new URLSearchParams(new FormData(formulaire));
    // On retire les champs vides pour garder une URL propre
    for (const[cle, valeur] of [...parametres]) {
        if (valeur === '') parametres.delete(cle);
    }

    try {
        const reponse = await fetch('/api/menus?' + parametres);
        if (!reponse.ok) throw new Error('Erreur ' + reponse.status);

        const donnees = await reponse.json();
        resultats.innerHTML = donnees.html; // HTML déjà échappé côté serveur
        compteur.textContent = texteNombre(donnees.nombre);

        // L'URL reflète les filtres : on peut partager le lien ou recharger la page
        const requete = parametres.toString();
        history.replaceState(null, '', '/menus' + (requete ? '?' + requete : ''));
    } catch (erreur) {
        compteur.textContent = 'Impossible de charger les menus, veuillez réessayer.';
    }
}

// On attend 300 ms après la dernière frappe pour ne pas lancer une requête par touche
formulaire.addEventListener('input', () => {
    clearTimeout(minuteur);
    minuteur = setTimeout(filtrer, 300);
});

formulaire.addEventListener('submit', (evenement) => {
    evenement.preventDefault();
    filtrer();
});

document.getElementById('reinitialiser').addEventListener('click', (evenement) => {
    evenement.preventDefault();
    formulaire.querySelectorAll('input').forEach((champ) => (champ.value = ''));
    formulaire.querySelectorAll('select').forEach((liste) => (liste.selectedIndex = 0));
    filtrer();
});

// Avec JS, le bouton "Filter" est inutile : on le masque
document.querySelector('.js-masquer')?.classList.add('d-none');

