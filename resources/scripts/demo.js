//
// Scripting for demo on the landing page
//
// Marking as done
// fixme: deduplicate code
$('#demo-note-1 .demo-done').on('click', function(e) {
    e.preventDefault();

    //var $note = $(this).closest('article.note');
    var $note = $('#demo-note-1');

    if ($note.hasClass('note-default')) {
        $note.removeClass('note-default');
        $note.addClass('note-success');
        $(this).find('.text').text('Undone');
    } else {
        $note.removeClass('note-success');
        $note.addClass('note-default');
        $(this).find('.text').text('Done');
    }
});
$('#demo-note-2 .demo-done').on('click', function(e) {
    e.preventDefault();

    //var $note = $(this).closest('article.note');
    var $note = $('#demo-note-2');

    if ($note.hasClass('note-info')) {
        $note.removeClass('note-info');
        $note.addClass('note-success');
        $note.find('.note-expired').addClass('sr-only');
        $(this).find('.text').text('Undone');
    } else if ($note.hasClass('note-warning')) {
        $note.removeClass('note-warning');
        $note.addClass('note-success');
        $note.find('.note-expired').addClass('sr-only');
    } else {
        $note.removeClass('note-success');
        $note.addClass('note-info');
        $note.find('.note-expired').removeClass('sr-only');
        $(this).find('.text').text('Done');
    }
});

// Editing note, same action for both
$('.demo-edit').on('click', function(e) {
    e.preventDefault();

    var $note = $(this).closest('article.note');

    $note.find('.note-content p').text('Get Codice to try out all of the features!');
});

// Removing note
// Allow to remove first one, change second one into blah blah
$('#demo-note-1 .demo-remove').on('click', function(e) {
    e.preventDefault();

    var $note = $('#demo-note-1');

    $note.fadeOut(450, function() {
        $(this).remove();
    });
});
$('#demo-note-2 .demo-remove').on('click', function(e) {
    e.preventDefault();

    var $note = $('#demo-note-2');
    newText = 'Baby don\'t hurt me, no more! Get Codice below and remove any note you want, any time you want.';

    $note.find('.note-content p').text(newText);
    $note.removeClass('note-success');
    $note.removeClass('note-info');
    $note.addClass('note-warning');
    $note.find('.note-expired').removeClass('sr-only'); // Ensure expiration time is shown
});
