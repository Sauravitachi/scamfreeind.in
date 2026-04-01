@use(Diglactic\Breadcrumbs\Breadcrumbs)
@use(App\Constants\ActivityEvent)
@use(App\Constants\Permission)

@extends('admin.layouts.app', [
    'pageTitle' => 'User Activities',
    'breadcrumbs' => Breadcrumbs::render('admin.user-activities.index'),
    'filters' => true,
])

@include('admin.layouts.components.datepicker')
@include('admin.layouts.components.datatable')
@include('admin.layouts.components.select2')

@section('filters-body')
    <div class="row">
        @can(Permission::VIEW_ALL_USERS_ACTIVITIES)
            <div class="col-12">
                @php($options = $users->pluck('name_with_username', 'id')->toArray())
                <x-admin.select name='filter_user_id' label='User' class="filter-select2" :options="$options"
                    placeholder="Select" :selected="request()->input('user_id', null)" />
            </div>
        @endcan
        <div class="col-12 ">
            @php($options = array_combine(
                array_map(fn(ActivityEvent $event) => $event->value, ActivityEvent::cases()), 
                array_map(fn(ActivityEvent $event) => $event->label(), ActivityEvent::cases())
            ))
            <x-admin.select name='filter_event' label='Event' class="filter-select2" :options="$options"
                placeholder="Select" />
        </div>
        <div class="col-12">
            <x-admin.input type='text' name='filter_ip_address' label='Ip Address.'
                placeholder='Enter ip address to search.' />
        </div>
        <div class="col-12">
            <x-admin.input class="date_range_picker" name='filter_created_at' label='Created At' placeholder='Select Range' />
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.layouts.components.datatable-header', [
                'id' => 'user-activities-table',
                'data' => [
                    ['title' => 'User', 'width' => '30%'],
                    ['title' => 'Event', 'width' => '5%'],
                    ['title' => 'Log', 'width' => '35%'],
                    ['title' => 'Ip Address', 'width' => '10%'],
                    ['title' => 'Date/Time', 'width' => '20%'],
                ],
            ])
        </div>
    </div>
@endsection

@push('script')
    <script>
        var dtTable = null;

        const Action = {
            show: function(id) {
                const url = '';
                return `<a href="${url}" class="cursor-pointer mx-1"><i class="ti ti-eye text-warning h1"></i></a>`;
            }
        };

        $(document).ready(function() {
            dtTable = $('#user-activities-table').DataTable({
                responsive: true,
                searchDelay: 500,
                processing: true,
                serverSide: true,
                ajax: {
                    url: @js(route('admin.user-activities.index')),
                    data: function(d) {
                        d = withFilterData(d);
                    }
                },
                order: [
                    [4, 'desc'] // created_at
                ],
                oLanguage: {
                    sLengthMenu: "_MENU_ entries per page",
                },
                columns: [{
                        data: 'user',
                        name: 'user',
                        render: function(data, type, row, meta) {
                            const id = row['id'];
                            const avatar = avatarField(row['profile_avatar'], 'me-2')
                            const html =
                                `<div class="d-flex align-items-center">${avatar} ${data}</div>`;
                            return html;
                        }
                    },
                    {
                        data: 'event',
                        name: 'event',
                        render: function(data, type, row, meta) {
                            return data;
                        }
                    },
                    {
                        data: 'description',
                        name: 'description',
                        render: function(data, type, row, meta) {
                            return data;
                        }
                    },
                    {
                        data: 'ip_address',
                        name: 'ip_address',
                        render: function(data, type, row, meta) {
                            return data;
                        }
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        render: function(data, type, row, meta) {
                            return data ?? noContentText();
                        }
                    }
                ],
            });

            FilterModule.registerDatatable(dtTable);
        });
    </script>
@endpush
