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
    $('#aipca-outline-remake').on('click', function() {
        if (!currentTopic) return;
        $('#aipca-loader').show();
        $.post(AIPCA_Vars.ajax_url, {
            action: 'aipca_generate_outline',
            security: AIPCA_Vars.nonce,
            topic: currentTopic
        }, function(res) {
            $('#aipca-loader').hide();
            if (res.success) {
                $('#aipca-outline-content').html(res.data.content);
            }
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
});
