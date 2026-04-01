@use(Diglactic\Breadcrumbs\Breadcrumbs)
@use(Proengsoft\JsValidation\Facades\JsValidatorFacade)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.escalations.show'),
])

@section('content')
    <div class="row">
        <div class="col-12 col-lg-4 mb-4">
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <tbody>
                            <tr>
                                <td class="text-start">
                                    Escalation
                                </td>
                                <td class="text-end">
                                    #{{ $escalation->track_id }}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-start">Customer</td>
                                <td class="text-end">{{ $escalation->scam->customer->fullNameWithFullPhoneNumber }}</td>
                            </tr>
                            <tr>
                                <td class="text-start">Scam</td>
                                <td class="text-end">
                                    #{{ $escalation->scam->track_id }} -
                                    {{ $escalation->scam->scamType?->title }}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-start">Scam Amount</td>
                                <td class="text-end">{{ format_amount($escalation->scam->scam_amount) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="row g-0">
                    <div class="col-12">
                        <div class="card-body scrollable chat-box" style="height: 35rem">
                            <div class="chat">
                                <div class="chat-bubbles" id="chat-area">
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <form action="{{ route('admin.escalation-chats.store', $escalation) }}" method="POST"
                                id="message-form">
                                <div class="input-group input-group-flat shadow-none">
                                    <input type="text" name="message" id="message_input" class="form-control"
                                        autocomplete="off" placeholder="Type message" />
                                    <span class="input-group-text">
                                        <a href="javascript:;" class="link-secondary ms-2" data-bs-toggle="tooltip"
                                            aria-label="Attach file" title="Attach file"
                                            onclick="$('#attachmentInput').click();">
                                            <!-- Download SVG icon from http://tabler-icons.io/i/paperclip -->
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round" class="icon">
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
@endsection

@push('script')
    {!! JsValidatorFacade::formRequest(\App\Http\Requests\Admin\EscalationChatRequest::class, '#message-form') !!}

    <script>
        function refreshEscalationChat({
            scrollToBottom = true,
            success = null,
            showLoader = false
        } = {}) {


            const id = {{ $escalation->id }};
            const selfUserId = {{ auth()->id() }};
            const url = "{{ route('admin.escalations.show', ':id') }}".replace(':id', id);

            const $chatArea = $('#chat-area');

            $.ajax({
                url,
                method: 'GET',
                beforeSend: function() {
                    if (showLoader) {
                        $chatArea.html(
                            `<div class="h2 h-100 d-flex justify-content-center align-items-center">${Loader.spinner}</div>`
                        );
                        $('#message_input').prop('disabled', true);
                    }

                },
                success: function(res) {
                    const escalation = res.data;
                    const chats = escalation.chats;


                    const chatRightItemHtml = $('#chat_right_item_container').html();
                    const chatLeftItemHtml = $('#chat_left_item_container').html();

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

                    $chatArea.html(
                        `<div class="chat"><div class="chat-bubbles">${chatScreen}</div></div>`
                    );

                    scrollToBottom && $(".chat-box").animate({
                        scrollTop: 1e10
                    }, 1000);

                },
                complete: function() {
                    $('#message_input').prop('disabled', false);
                }
            });

        }


        function takeAttachmentInput() {
            if (!$('#message_input').prop('disabled'))
                $('#attachmentInput').click();
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
        });
    </script>
@endpush
