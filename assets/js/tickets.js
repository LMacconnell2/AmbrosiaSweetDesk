document.addEventListener('DOMContentLoaded', () => {

    const modal = document.getElementById(
        'sweetdesk-ticket-modal'
    );

    const closeBtn = document.getElementById(
        'sd-modal-close'
    );

    const ticketRows = document.querySelectorAll(
        '.sd-ticket-row'
    );

    /*
    |--------------------------------------------------------------------------
    | Open Modal
    |--------------------------------------------------------------------------
    */

    ticketRows.forEach((row) => {

        row.addEventListener('click', () => {
            modal.classList.add('active');
        });

    });

    /*
    |--------------------------------------------------------------------------
    | Close Modal Button
    |--------------------------------------------------------------------------
    */

    closeBtn.addEventListener('click', () => {
        modal.classList.remove('active');
    });

    /*
    |--------------------------------------------------------------------------
    | Click Outside Modal
    |--------------------------------------------------------------------------
    */

    modal.addEventListener('click', (e) => {

        if (e.target === modal) {
            modal.classList.remove('active');
        }

    });

});