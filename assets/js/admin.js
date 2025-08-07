jQuery(function($) {
    console.log('admin loaded.');

    let currentTopic = '';

    $('#btn-generate-outline').on('click', function(e) {
        e.preventDefault(); // Stop default form submission

        let topic = $('#blog_topic').val().trim();
        if (!topic) {
            alert('Please enter a blog topic.');
            return;
        }

        currentTopic = topic;
        $('#aipca-loader').show();

        $.post(AIPCA_Vars.ajax_url, {
            action: 'aipca_generate_outline',
            security: AIPCA_Vars.nonce,
            topic: topic
        }, function(res) {
            $('#aipca-loader').hide();
            if (res.success) {
                $('#aipca-outline-content').html(res.data.content);
                $('#aipca-outline-loader').hide(); // 💡 hide modal overlay loader just in case
                new bootstrap.Modal('#aipcaOutlineModal').show();
            } else {
                alert(res.data.message || 'Error generating outline.');
            }
        }).fail(() => {
            $('#aipca-loader').hide();
            alert('Something went wrong.');
        });
    });

    // Remake outline
    $('#aipca-outline-remake').on('click', function () {
        if (!currentTopic) return;

        // Show overlay loader and disable buttons
        $('#aipca-outline-loader').removeClass('d-none');
        $('#aipca-outline-remake').prop('disabled', true);
        $('#aipca-outline-to-full').prop('disabled', true);
        $('#aipca-outline-copy').prop('disabled', true);
        $('#aipca-modal-remove').prop('disabled', true);

        $.post(AIPCA_Vars.ajax_url, {
            action: 'aipca_generate_outline',
            security: AIPCA_Vars.nonce,
            topic: currentTopic
        }, function (res) {
            $('#aipca-outline-loader').hide();

            $('#aipca-outline-remake').prop('disabled', false);
            $('#aipca-outline-to-full').prop('disabled', false);
            $('#aipca-outline-copy').prop('disabled', false);
            $('#aipca-modal-remove').prop('disabled', false);

            if (res.success) {
                $('#aipca-outline-loader').addClass('d-none');
                $('#aipca-outline-content').html(res.data.content);
            } else {
                alert(res.data.message || 'Error regenerating outline.');
            }
        }).fail(() => {
            $('#aipca-outline-loader').hide();
            $('#aipca-outline-remake').prop('disabled', false);
            $('#aipca-outline-to-full').prop('disabled', false);
            $('#aipca-outline-copy').prop('disabled', false);
            $('#aipca-modal-remove').prop('disabled', false);
            alert('Something went wrong.');
        });
    });

    // Copy outline
    $('#aipca-outline-copy').on('click', function() {
        navigator.clipboard.writeText($('#aipca-outline-content').text())
            .then(() => alert('Outline copied to clipboard!'));
    });

    // Create full post from outline
    $('#aipca-outline-to-full').on('click', function() {
        alert('Full post generation from outline will go here.');
    });

    // remove modal and clear input field
    $('#aipca-modal-remove').on('click', function() {
        $('#blog_topic').val(''); // clear the input
        currentTopic = ''; // reset the internal topic too
    });

    // Generate full post
    $('#btn-generate-full').on('click', function () {
        let topic = $('#blog_topic').val().trim();
        if (!topic) {
            alert('Please enter a blog topic.');
            return;
        }

        $('#aipca-loader').show();

        $.post(AIPCA_Vars.ajax_url, {
            action: 'aipca_generate_full_post',
            security: AIPCA_Vars.nonce,
            topic: topic
        }, function (res) {
            $('#aipca-loader').hide();
            // $('#aipca-full-loader').addClass('d-none');

            if (res.success) {
                $('#aipcaFullPostModal').modal('show');
                $('#aipca-full-content').html(res.data.content);
            } else {
                $('#aipca-full-content').html(`<div class="alert alert-danger">${res.data.message || 'Error generating post.'}</div>`);
            }
        }).fail(() => {
            $('#aipca-loader').hide();
            // $('#aipca-full-loader').addClass('d-none');
            $('#aipca-full-content').html('<div class="alert alert-danger">Something went wrong.</div>');
        });
    });

    // Download full post as .docx
    $('#aipca-full-download').on('click', function () {
        const postHtml = $('#aipca-full-content').html();

        const form = $('<form>', {
            method: 'POST',
            action: AIPCA_Vars.ajax_url,
            target: '_blank'
        });

        form.append($('<input>', { type: 'hidden', name: 'action', value: 'aipca_download_docx' }));
        form.append($('<input>', { type: 'hidden', name: 'security', value: AIPCA_Vars.nonce }));
        form.append($('<input>', { type: 'hidden', name: 'html', value: postHtml }));

        $('body').append(form);
        form.submit();
        form.remove();
    });

});
