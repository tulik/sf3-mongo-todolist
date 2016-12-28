document.onscroll = function() {
    if (window.innerHeight + window.scrollY > document.body.clientHeight) {
        document.getElementById('timer').style.display='none';
    }else{
        document.getElementById('timer').style.display='block';
    }
};
$('#modalWindow').on('show.bs.modal', function (e) {
    var button = $(e.relatedTarget);
    console.log();
    var modal = $(this);

    $.ajax({
        url: button.attr('data-url'),
        type: 'GET'
    }).done(function (data) {
        modal.find('.modal-content').html(data);
    });
});