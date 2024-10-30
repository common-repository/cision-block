;
(($) => {
    $(document).ready(() => {
        $(':checkbox[name=mark_regulatory]').on('change', function () {
            if (this.checked) {
                $('[name=regulatory_text],[name=non_regulatory_text]').prop('disabled', false)
            } else {
                $('[name=regulatory_text],[name=non_regulatory_text]').prop('disabled', true)
            }
        });
        $(':checkbox[name=show_filters]').on('change', function () {
            if (this.checked) {
                $('[name=filter_all_text],[name=filter_regulatory_text],[name=filter_non_regulatory_text]').prop('disabled', false)
            } else {
                $('[name=filter_all_text],[name=filter_regulatory_text],[name=filter_non_regulatory_text]').prop('disabled', true)
            }
        });

        // Handle dismissible notifications.
        $('.cision-block-notice.notice.is-dismissible').each((a, el) => {
            $('.notice-dismiss', el).on('click', () => {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'cision_block_dismiss_notice',
                        _ajax_nonce: data._nonce,
                        id: $(el).attr('id').split('-')[1],
                    },
                })
                    .done(() => {
                        if (data.debug) {
                            console.log('success');
                        }
                        el.remove();
                    })
                    .fail(() => {
                        if (data.debug) {
                            console.log('error');
                        }
                    })
                    .always(() => {
                        if (data.debug) {
                            console.log('complete');
                        }
                    });
            });
        });
    });
})(jQuery);
