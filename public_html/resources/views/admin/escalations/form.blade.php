@use(Proengsoft\JsValidation\Facades\JsValidatorFacade)

@include('admin.layouts.components.select2')

<form action="{{ $form_url }}" method="POST" id="escalation-form" enctype="multipart/form-data">
    @csrf
    @method($form_method)
    <div class="row">

        <div class="col-12">
            <x-admin.select2-ajax name='scam_id' label='Scam' id="scam_id" placeholder='Search Scam' :route="route('admin.scams.select-search')"
                paginate required />
        </div>

        <div class="col-12">
            @php($options = \App\Enums\EscalationType::selectArray())
            <x-admin.select name='type' label='Type' class="select2" :options="$options" placeholder="Select"
                disablePlaceholder required />
        </div>

        <div class="col-12">
            <x-admin.textarea name='message' label='Message' placeholder='Enter message (max 1000 characters)'
                rows='4' required />
        </div>

        <div class="col-12">
            <x-admin.input name='file' label='File' type='file' />
        </div>


        <div class="col-12">
            <div class="text-end">
                <x-admin.button label='Save Changes' submit />
            </div>
        </div>

    </div>
</form>

@push('script')
    {!! JsValidatorFacade::formRequest(\App\Http\Requests\Admin\EscalationRequest::class, '#escalation-form') !!}
    <script>
        $(document).ready(function() {

            ajaxForm('#escalation-form', {
                responseRedirect: true,
                disableFormAfterSuccess: true
            });
        });
    </script>
@endpush
