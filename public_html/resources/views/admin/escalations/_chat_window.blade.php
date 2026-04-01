@use(Proengsoft\JsValidation\Facades\JsValidatorFacade)

<div>
    <div class="modal modal-blur fade" id="chat_window_modal" tabindex="-1" style="display: none;" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Escalation Chat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div>
                        <x-admin.alert variant='danger' id="esc-chat-alert" message='' style="display: none;" />
                    </div>
                    <div class="d-flex justify-content-end mb-2">
                        <div class="reject-button-container me-2"></div>
                        <div class="close-button-container"></div>
                    </div>
                    <div class="card">
                        <div class="row g-0">
                            <div class="col-12">
                                <div class="card-body scrollable chat-box" id="chat-area" style="height: 30rem">
                                </div>
                                <div class="card-footer">
                                    <form action="" method="POST" id="message-form">
                                        <div class="input-group input-group-flat shadow-none">
                                            <input type="text" name="message" id="message_input" class="form-control"
                                                autocomplete="off" placeholder="Type message" />
                                            <span class="input-group-text">
                                                <a href="javascript:;" class="link-secondary ms-2"
                                                    data-bs-toggle="tooltip" aria-label="Attach file"
                                                    title="Attach file" onclick="takeAttachmentInput();">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                        height="24" viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round" class="icon">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path
                                                            d="M15 7l-6.5 6.5a1.5 1.5 0 0 0 3 3l6.5 -6.5a3 3 0 0 0 -6 -6l-6.5 6.5a4.5 4.5 0 0 0 9 9l6.5 -6.5" />
                                                    </svg>
                                                </a>
                                            </span>
                                        </div>
                                    </form>
                                </div>
                                <input type="file" style="display:none;" id="attachmentInput" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="chat_right_item_container" style="display: none;">
        <div class="chat-item">
            <div class="row align-items-end justify-content-end">
                <div class="col col-lg-6">
                    <div class="chat-bubble chat-bubble-me">
                        <div class="chat-bubble-title">
                            <div class="row">
                                <div class="col chat-bubble-author">{user_name}</div>
                                <div class="col-auto chat-bubble-date">{time}</div>
                            </div>
                        </div>
                        <div class="chat-bubble-body">
                            {body}
                        </div>
                    </div>
                </div>
                <div class="col-auto"><span class="avatar avatar-rounded"
                        style="background-image: url({avatar_url})"></span>
                </div>
            </div>
        </div>
    </div>
    <div id="chat_left_item_container" style="display: none;">
        <div class="chat-item">
            <div class="row align-items-end">
                <div class="col-auto"><span class="avatar avatar-rounded"
                        style="background-image: url({avatar_url})"></span>
                </div>
                <div class="col col-lg-6">
                    <div class="chat-bubble">
                        <div class="chat-bubble-title">
                            <div class="row">
                                <div class="col chat-bubble-author">{user_name}</div>
                                <div class="col-auto chat-bubble-date">{time}</div>
                            </div>
                        </div>
                        <div class="chat-bubble-body">
                            {body}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="reject_button_template" style="display: none;">
        <x-admin.button variant='danger' label="Reject" icon='ti ti-ban' class="btn-sm" onclick="rejectEscalation()" />
    </div>
    <div id="close_button_template" style="display: none;">
        <x-admin.button variant='success' label="Close" icon='ti ti-logout' class="btn-sm"
            onclick="closeEscalation()" />
    </div>
</div>
@push('script')
    {!! JsValidatorFacade::formRequest(\App\Http\Requests\Admin\EscalationChatRequest::class, '#message-form') !!}

    <script>
        var openedEscalation = null;

        function refreshEscalationChat({
            scrollToBottom = true,
            success = null,
            showLoader = false
        } = {}) {

            if (!openedEscalation)
                return;

            const id = openedEscalation.id;
            const selfUserId = {{ auth()->id() }};
            const url = "{{ route('admin.escalations.show', ':id') }}".replace(':id', id);

            const $chatArea = $('#chat-area');
            const $alert = $('#esc-chat-alert');

            const $messageInput = $('#message_input');

            // pre setup
            showLoader && $alert.hide();
            showLoader && escHideButtons();

            $.ajax({
                url,
                method: 'GET',
                beforeSend: function() {
                    if (showLoader) {
                        $chatArea.html(
                            `<div class="h2 h-100 d-flex justify-content-center align-items-center">${Loader.spinner}</div>`
                        );
                        $messageInput.prop('disabled', true);
                    }
                },
                success: function(res) {
                    const escalation = res.data;
                    const chats = escalation.chats;


                    const chatRightItemHtml = $('#chat_right_item_container').html();
                    const chatLeftItemHtml = $('#chat_left_item_container').html();
                    const rejectButtonHtml = $('#reject_button_template').html();
                    const closeButtonHtml = $('#close_button_template').html();



                    const alertMessages = [];

                    // reject button visibility
                    if (!escalation.is_rejected && escalation.can_reject) {
                        $rejBtn = $(rejectButtonHtml);
                        $('.reject-button-container').html($rejBtn.outerHtml());
                    } else {
                        $('.reject-button-container').empty();
                    }

                    // close button visibility
                    if (!escalation.is_closed && escalation.can_close) {
                        $closeBtn = $(closeButtonHtml);
                        $('.close-button-container').html($closeBtn.outerHtml());
                    } else {
                        $('.close-button-container').empty();
                    }

                    // rejected alert
                    if (escalation.is_rejected) {
                        alertMessages.push(
                            'Escalation is currently rejected by the assignee.');
                    }
                    // closed alert
                    if (escalation.is_closed) {
                        alertMessages.push(HtmlTag.span('Escalation has been closed.', 'fw-bold'));
                    }

                    // disabling chat if closed
                    $messageInput.prop('disabled', escalation.is_closed);


                    let chatScreen = '';

                    chats.forEach(function(chat) {

                        let chatItem = chat.user.id === selfUserId ? chatRightItemHtml :
                            chatLeftItemHtml;

                        chatItem = chatItem.replace('{user_name}', chat.user.username_with_role_name)
                            .replace('{avatar_url}', chat.user.profile_avatar)
                            .replace('{time}', chat.created_at_chat_formatted);

                        let body = '';

                        if (chat.file) {
                            if (chat.file.is_previewable_file) {
                                body +=
                                    `<div class="text-center"><img width="300px" src="${chat.file.url}" class="my-3"></img></div>`;
                            }

                            body +=
                                `<div><a target="_blank" href="${chat.file.url}">${chat.file.original_name}</a></div>`;
                        }

                        if (chat.message) {
                            body += `<p>${chat.message}</p>`;
                        }

                        chatItem = chatItem.replace('{body}', body)

                        chatScreen += chatItem;

                        success && success();
                    });

                    // appending alert messages
                    if (alertMessages.length > 0) {
                        var $alertMsg = $alert.find('.message');
                        $alertMsg.empty();
                        alertMessages.forEach(function(message) {
                            $alertMsg.append(HtmlTag.icon('ti ti-arrow-big-right-line me-2',
                                    'text-danger') +
                                message +
                                '<br>');
                        });
                        $alert.show();
                    }

                    $chatArea.html(
                        `<div class="chat"><div class="chat-bubbles">${chatScreen}</div></div>`
                    );

                    scrollToBottom && $(".chat-box").animate({
                        scrollTop: 1e10
                    }, 1000);

                },
            });

        }

        function escHideButtons() {
            $('.reject-button-container, .close-button-container').empty();
        }

        function takeAttachmentInput() {
            if (!$('#message_input').prop('disabled'))
                $('#attachmentInput').click();
        }

        function escalationStateAjax({
            url
        } = {}) {
            const id = openedEscalation.id;
            if (!id) {
                alert('Invalid Action Attempt!');
                return;
            }

            url = url.replace(':id', id);

            const $chatModal = $('#chat_window_modal');

            $.ajax({
                url: url,
                method: 'POST',
                beforeSend: function() {
                    overlayLoader.show();
                },
                success: function(res) {
                    if (res.toast)
                        toast.open(res.toast);
                    dtTable && dtTable.draw(false);
                    $chatModal.modal('hide');
                },
                complete: function() {
                    overlayLoader.hide();
                }
            });
        }

        function rejectEscalation() {
            escalationStateAjax({
                url: @js(route('admin.escalations.reject', ':id'))
            });
        }

        function closeEscalation() {
            escalationStateAjax({
                url: @js(route('admin.escalations.close', ':id'))
            });
        }


        $(document).ready(function() {

            refreshEscalationChat();

            setInterval(() => refreshEscalationChat({
                scrollToBottom: false
            }), 5000);

            ajaxForm('#message-form', {
                handleToast: true,
                success: function(res) {
                    refreshEscalationChat();
                    $('#message_input').val('');
                    delay(() => $('#message_input').focus(), 10);
                }
            });

            $('#attachmentInput').on('change', function() {
                var formData = new FormData();
                var fileInput = $('#attachmentInput')[0];
                if (fileInput.files.length > 0) {
                    formData.append('file', fileInput.files[0]);
                    $.post({
                        url: $('#message-form').attr('action'),
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(res) {
                            if (res.success && res.toast) {
                                toast.open(res.toast);
                            }
                            refreshEscalationChat();
                        },
                        error: function(xhr) {
                            var res = xhr.responseJSON || xhr.responseText;
                            if (xhr.status === 422 && res.errors?.file) {
                                const message = res.errors?.file[0];
                                toast.open({
                                    type: 'error',
                                    message
                                });
                            }
                        }
                    });
                }
            });


            $('#chat_window_modal').on('hide.bs.modal', function(event) {
                openedEscalation = null;
            });

        });
    </script>
@endpush
