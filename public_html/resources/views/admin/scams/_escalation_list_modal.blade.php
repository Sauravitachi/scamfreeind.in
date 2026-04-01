<div>
    <div class="modal modal-blur fade" id="escalation-list-modal" tabindex="-1" style="display: none;" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Escalations List</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h4>
                        List of already created escalations of this scam.
                    </h4>

                    <div class="mt-2" id="escalation-list-container">

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Close</button>
                    <x-admin.button label="New Escalation" icon='ti ti-plus'
                        onclick="showCreateEscalationModal(selectedScamId);" />
                </div>
            </div>
        </div>
    </div>
    <div id="escalation-list-item-container" style="display: none;">
        <div class="card mt-2">
            <div class="card-body">
                <div class="row g-2 align-items-center">
                    {{-- <div class="col-auto">
                        <span class="avatar avatar-lg"
                            style="background-image: url({{ asset('static/avatars/000m.jpg') }})"></span>
                    </div> --}}
                    <div class="col">
                        <h4 class="card-title m-0">
                            <a href="{link_1}" target="_blank">#{track_id} - <span
                                    class="text-secondary">{created_at}</span></a>
                        </h4>
                        <div class="text-secondary">
                            {type}
                        </div>
                        <div class="small mt-1">
                            <span class="badge" style="background-color: {status_color};"></span> {status}
                        </div>
                    </div>
                    <div class="col-auto">
                        <a href="javascript:;" onclick="openChatWindow({escalationId});" class="btn">
                            <i class="ti ti-brand-hipchat me-2"></i> Chat
                        </a>
                    </div>
                    <div class="col-auto">
                        <div class="dropdown">
                            <a href="#" class="btn-action" data-bs-toggle="dropdown" aria-expanded="false">
                                <!-- Download SVG icon from http://tabler-icons.io/i/dots-vertical -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" class="icon">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                    <path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                                    <path d="M12 19m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                                    <path d="M12 5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                                </svg>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="{link_2}" target="_blank" class="dropdown-item">Open in new window</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
