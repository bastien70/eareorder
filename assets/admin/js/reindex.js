import { Sortable } from 'sortablejs';

const el = document.getElementById('items');
const toastr = require('toastr');
require("toastr/build/toastr.min.css");

const $ = require("jquery");

toastr.options = {
    "closeButton": true,
    "debug": false,
    "newestOnTop": true,
    "progressBar": true,
    "positionClass": "toast-bottom-right",
    "preventDuplicates": false,
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "5000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
};

const sortable = Sortable.create(el, {
    handle: '.drag-handler',
    animation: 150,
    draggable: ".item",
    onEnd: () => {
        $.ajax({
            url: $(el).closest('.reindex-page').data('url'),
            method: 'POST',
            data: JSON.stringify(sortable.toArray())
        }).then(function() {
            // Display success notification ?
            // https://codeseven.github.io/toastr/demo.html
            toastr['success']("Modifications enregistrées");
        }).catch(function() {
            toastr['error']("Une erreur est survenue");
        })
    }
});

require('../css/reindex.scss');