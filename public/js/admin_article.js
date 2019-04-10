$(function () {
    $('.btn-delete').click(function (event) {
        // empêche d'aller vers la page de suppression
        event.preventDefault();

        // ouvre la modal de confirmation
        $('#modal-confirm-delete').modal('show');

        // l'url de la page de suppression de l'article
        var href = $(this).attr('href');

        // au clic du bouton de confirmation, redirection vers la page de suppression
        $('.btn-confirm-delete').click(function () {
            location.href = href;
        });
    });

    $('.btn-article-content').click(function (event) {
        event.preventDefault();

        var href = $(this).attr('href');

        $.get(
            href,
            function (response) {

                var $modal = $('#modal-article-content');

                // retour en texte brut
                $modal.find('.modal-body').html(response);

                // retour en JSON
                //$modal.find('.modal-body').html(response.content);

                $modal.modal('show');
            }
        );
    });
});











