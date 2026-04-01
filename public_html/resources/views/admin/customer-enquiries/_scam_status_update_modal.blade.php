<div>
    <div class="modal modal-blur fade" id="scam-status-update-modal" tabindex="-1" style="display: none;" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <input type="hidden" name="scam_id" value />
                <div class="modal-header">
                    <h5 class="modal-title">Update Scam Status</h5>
                    <button type="button" class="btn-close close-modal-btn" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                </div>
            </div>
        </div>
    </div>
</div>



@push('script')

    <script>
        var SCAM_STATUS_UPDATE_MODULE = {

            refreshOnUpdate: {{ $refreshOnUpdate ?? false ? 'true' : 'false' }},

            register: function() {
                this.$modal = $('#scam-status-update-modal');
                this.$container = this.$modal.find('.modal-body');

                this.$modal.find('.close-modal-btn').off('click').on('click', function() {
                    if(SCAM_STATUS_UPDATE_MODULE.refreshOnUpdate) {
                        location.reload();
                        return;
                    }
                    SCAM_STATUS_UPDATE_MODULE.redrawTable();
                });

                $(document).on('app:scam_status_updated app:status_update_with_data_success', function(e, data) {
                    SCAM_STATUS_UPDATE_MODULE.close();
                });
            },

            open: function(enquiry, type) {

                const scam = enquiry.scam;
                const currentScamStatus = enquiry.scam[`${type}_status`];
                let optionStatuses = [];
                let showNullOption = true;

                if(@json($userType !== 'admin')) {
                    optionStatuses = [...(currentScamStatus ? [currentScamStatus] : []), ...(currentScamStatus?.next_statuses ?? [])];
                    if(currentScamStatus) {    
                        showNullOption = false;
                    } else {
                        optionStatuses = [...(firstDraftingStatus ? [firstDraftingStatus] : [])];
                    }
                } else {
                    optionStatuses = scamStatuses;
                }

                let options = '';
                
                optionStatuses.forEach(function(status) {
                    if (status.type != SCAM_STATUS_DRAFTING)
                        return;
                    options +=
                        `<option value="${status.id}" ${currentScamStatus?.id && currentScamStatus?.id == status.id ? 'selected' : ''}>${status.title}</option>`;
                });

                let disable = !pms.drafting_access;

                if(!disable && scam.drafting_status_record?.review === 'pending') {
                    disable = true;   
                }

                if(!disable && (currentScamStatus && currentScamStatus?.is_lock && !pms.update_locked_drafting_status)) {
                    disable = true;
                }

                if(userType === 'sales') {
                    disable = true;
                }
                const selectField = `<select class="form-select table-td-select scam-main-${type}-status-select select2" data-enquiry-id="${enquiry.id}" data-scam-${type}-status="${currentScamStatus?.id ?? null}" data-scam-status="${currentScamStatus?.id ?? null}" data-scam-id="${scam.id}" ${disable ? 'disabled' : ''}>${showNullOption ? `<option value>Select ${type} status</option>` : ``}${options}</select>`;

                this.$container.html(selectField);
           
                this.$modal.modal('show');

                initSelect2($(`select.scam-main-${type}-status-select`), {
                    dropdownParent: this.$container
                });

                $(`select.scam-main-${type}-status-select`).on('change', function(e, data) {
                    if(data?.impact === 'none') {
                        return;
                    }
                    scamStatusSelectHandler({
                        $selectElement: $(this),
                        type: type,
                        originalStatusId: $(this).data(`scam-${type}-status`)
                    });
                });
            },

            close: function() {
                this.$modal.modal('hide');
            },

            redrawTable: function() {
                if (typeof dtTable !== 'undefined' && dtTable) {
                    dtTable.draw(false);
                }
            },

            resetStatus: function() {
                const $select = $('select.scam-main-drafting-status-select');
                console.log('!!!', $select.length > 0);
                if($select.length > 0) {
                    $select.val($select.data('scam-status') ?? null).trigger('change', { impact: 'none' });
                }
            }


        };

        $(document).ready(function() {
            SCAM_STATUS_UPDATE_MODULE.register();

            $(document).on('app:status_change_with_data_modal_closed', function() {
                SCAM_STATUS_UPDATE_MODULE.resetStatus();
            });
        });
    </script>
@endpush