@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.specializations.show', $specialization),
])

@section('content')
    <div class="row">
        <div class="col-lg-8 col-12 mx-auto">
            <div class="card mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Specialization Details</h3>
                    <a href="{{ route('admin.specializations.edit', $specialization) }}" class="btn btn-primary btn-sm">
                        <i class="ti ti-edit"></i> Edit Details
                    </a>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <th class="text-muted" style="width: 30%">Title:</th>
                                <td><strong>{{ $specialization->title }}</strong></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Slug:</th>
                                <td><code>{{ $specialization->slug }}</code></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Is Default Assignment Category?</th>
                                <td>
                                    @if ($specialization->is_default)
                                        <span class="badge bg-success">Yes (Default)</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Registered:</th>
                                <td>{{ $specialization->created_at?->format('F d, Y h:i A') ?? 'N/A' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-light">
                    <h4 class="mb-0">Lawyers with this Specialization</h4>
                </div>
                <div class="card-body">
                    @php
                        $lawyers = $specialization->lawyers;
                    @endphp
                    @if($lawyers->isNotEmpty())
                        <div class="list-group">
                            @foreach ($lawyers as $lawyer)
                                <a href="{{ route('admin.lawyers.show', $lawyer) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <span>
                                        <strong>{{ $lawyer->name }}</strong>
                                        <small class="text-muted d-block">{{ $lawyer->email }}</small>
                                    </span>
                                    @if ($lawyer->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="ti ti-users text-muted d-block mb-2" style="font-size: 2.5rem;"></i>
                            <span class="text-muted">No lawyers currently mapped to this specialization.</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
