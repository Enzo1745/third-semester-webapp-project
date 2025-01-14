
    document.querySelector('form').addEventListener('submit', (event) => {
        event.preventDefault(); // block form
        const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
        confirmModal.show();


        document.getElementById('confirmSave').addEventListener('click', () => {
            event.target.submit(); //submit form after click
        });
    });



    // Change between summer and winter value
    document.getElementById('showSummer').addEventListener('click', () => {
        document.getElementById('normsSummer').style.display = 'block'; // Afficher normes techniques d'été
        document.getElementById('normsWinter').style.display = 'none'; // Masquer normes techniques d'hiver

        document.getElementById('comfortSummer').style.display = 'block'; // Afficher normes confort d'été
        document.getElementById('comfortWinter').style.display = 'none'; // Masquer normes confort d'hiver
    });

    document.getElementById('showWinter').addEventListener('click', () => {
        document.getElementById('normsSummer').style.display = 'none'; // Masquer normes techniques d'été
        document.getElementById('normsWinter').style.display = 'block'; // Afficher normes techniques d'hiver

        document.getElementById('comfortSummer').style.display = 'none'; // Masquer normes confort d'été
        document.getElementById('comfortWinter').style.display = 'block'; // Afficher normes confort d'hiver
    });


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




