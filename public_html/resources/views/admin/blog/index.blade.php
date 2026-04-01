@use(App\Constants\Permission)
@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.blog.index'),
    'buttons' => [
        auth()->user()->can(Permission::BLOG_CREATE->value)
            ? ['label' => 'Add new blog', 'icon' => 'ti ti-plus', 'url' => route('admin.blog.create')]
            : null,
    ],
])

@include('admin.layouts.components.datatable')

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.layouts.components.datatable-header', [
                'id' => 'blog-table',
                'data' => [
                    ['title' => 'Sr.', 'width' => '5%', 'classname' => 'text-center'],
                    ['title' => 'Title', 'width' => '30%'],
                    ['title' => 'Author', 'width' => '15%'],
                    ['title' => 'Status', 'width' => '10%'],
                    ['title' => 'Featured', 'width' => '10%'],
                    ['title' => 'Published At', 'width' => '15%'],
                    ['title' => 'Action', 'width' => '15%'],
                ],
            ])
        </div>
    </div>
@endsection

@push('script')
    <script>
        var dtTable = null;

        const Action = {
            editUrl: @js(route('admin.blog.edit', ':id')),
            deleteUrl: @js(route('admin.blog.destroy', ':id')),
            canEdit: @js(auth()->user()->can(Permission::BLOG_UPDATE->value)),
            canDelete: @js(auth()->user()->can(Permission::BLOG_DELETE->value)),
            edit: function(id) {
                if (!Action.canEdit) return '';
                const url = this.editUrl.replace(':id', id);
                return `<a href="${url}" class="cursor-pointer mx-1"><i class="ti ti-edit text-primary h1"></i></a>`;
            },
            delete: function(id) {
                if (!Action.canDelete) return '';
                return `<a href="javascript:;" data-delete-id="${id}" class="cursor-pointer mx-1"><i class="ti ti-trash text-danger h1"></i></a>`;
            },
        };

        $(document).ready(function() {
            dtTable = $('#blog-table').DataTable({
                responsive: true,
                searchDelay: 500,
                processing: true,
                serverSide: true,
                ajax: @js(route('admin.blog.index')),
                columns: [
                    { data: 'id', name: 'id', className: 'text-center' },
                    { data: 'title', name: 'title' },
                    { data: 'author.username', name: 'author.username', defaultContent: 'Admin' },
                    { data: 'status', name: 'status' },
                    { data: 'is_featured', name: 'is_featured' },
                    { data: 'published_at', name: 'published_at' },
                    {
                        data: 'id',
                        name: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return Action.edit(data) + Action.delete(data);
                        }
                    },
                ],
            }).on('draw', function() {
                $('[data-delete-id]').on('click', deleteBlog);
            });

            function deleteBlog() {
                const id = $(this).data('delete-id');
                Popup.askConfirmation({
                    variant: 'danger',
                    icon: 'ti ti-trash',
                    message: `You are about to delete this <strong>blog post</strong>.<br>This action cannot be undone.`,
                    onConfirm: async function() {
                        const url = Action.deleteUrl.replace(':id', id);
                        await runAjax({
                            url: url,
                            method: 'DELETE',
                            handleToast: true,
                            success: function(response) {
                                dtTable.draw(false);
                            }
                        });
                    }
                });
            }
        });
    </script>
@endpush
