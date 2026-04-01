@use(Proengsoft\JsValidation\Facades\JsValidatorFacade)
@use(Diglactic\Breadcrumbs\Breadcrumbs)
@use(App\Enums\PreferenceKey)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.preferences.index'),
])

@include('admin.layouts.components.select2')

@push('style')
    <style>
        .preferences-table {
            border-collapse: collapse;
            width: 100%;
        }

        .preferences-table tr,
        .preferences-table td {
            border: none;
            padding: 10px 15px;
        }

        .preferences-table {
            border-collapse: separate;
            border-spacing: 0 12px;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-5">
                    <form action="{{ route('admin.preferences.update') }}" method="POST" id="user-preference-form">
                        @csrf
                        @method('PUT')

                        <table class="table preferences-table">
                            <tbody>
                                <tr>
                                    <td><strong>Theme</strong></td>
                                    <td>
                                        <x-admin.select name='theme' class="select2" :options="['light' => 'Light', 'dark' => 'Dark']" :selected="$preferences->get('theme', 'light')" />
                                    </td>
                                </tr>

                                <tr>
                                    <td><strong>Menu Layout</strong></td>
                                    <td>
                                        <x-admin.radio name='menu_layout' value='vertical' label='Vertical'
                                            :checked="$preferences->get('menu_layout', 'vertical') === 'vertical'" />

                                        <x-admin.radio name='menu_layout' value='horizontal' label='Horizontal'
                                            :checked="$preferences->get('menu_layout', 'vertical') === 'horizontal'" />
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Save Preferences</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    {!! JsValidatorFacade::formRequest(
        \App\Http\Requests\Admin\UserPreferenceRequest::class,
        '#user-preference-form',
    ) !!}
@endpush
