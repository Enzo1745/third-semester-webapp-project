/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
document.addEventListener('DOMContentLoaded', function () {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

document.querySelector('form').addEventListener('submit', (event) => {
    event.preventDefault(); // block form
    const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
    confirmModal.show();

    // Gestion du clic sur "Confirmer" dans le modal de confirmation
    document.getElementById('confirmSave').addEventListener('click', () => {
        event.target.submit(); //submit form after click
    });
});

// Change between summer and winter value
document.getElementById('showSummer').addEventListener('click', () => {
    document.getElementById('normsSummer').style.display = 'block'; // show
    document.getElementById('normsWinter').style.display = 'none'; // hide
});

document.getElementById('showWinter').addEventListener('click', () => {
    document.getElementById('normsSummer').style.display = 'none'; //show
    document.getElementById('normsWinter').style.display = 'block'; //hide
});

// Fonction pour afficher le bon formulaire dans le modal
document.getElementById('normsModal').addEventListener('show.bs.modal', () => {
    const isSummerVisible = document.getElementById('normsSummer').style.display === 'block';

    if (isSummerVisible) {
        document.getElementById('formSummer').style.display = 'block';
        document.getElementById('formWinter').style.display = 'none';
    } else {
        document.getElementById('formWinter').style.display = 'block';
        document.getElementById('formSummer').style.display = 'none';
    }
});