@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.lawyers.show', $lawyer),
])

@section('content')
    <div class="row">
        <div class="col-lg-8 col-12 mx-auto">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Lawyer Information</h3>
                    <a href="{{ route('admin.lawyers.edit', $lawyer) }}" class="btn btn-primary btn-sm">
                        <i class="ti ti-edit"></i> Edit Details
                    </a>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <th class="text-muted" style="width: 30%">Avatar:</th>
                                <td>
                                    <div class="avatar-preview" style="width: 60px; height: 60px; border-radius: 100%; border: 2px solid #f0f2f5; overflow: hidden; box-shadow: 0px 2px 5px rgba(0,0,0,0.05);">
                                        <div style="width: 100%; height: 100%; background-size: cover; background-repeat: no-repeat; background-position: center; background-image: url({{ $lawyer->image ? asset('storage/' . $lawyer->image) : 'https://ui-avatars.com/api/?name=' . urlencode($lawyer->name) . '&background=random&color=fff&bold=true' }});"></div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Name:</th>
                                <td><strong>{{ $lawyer->name }}</strong></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Email:</th>
                                <td>{{ $lawyer->email ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Phone:</th>
                                <td>{{ $lawyer->phone ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Address:</th>
                                <td>{{ $lawyer->address ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Status:</th>
                                <td>
                                    @if ($lawyer->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Registered:</th>
                                <td>{{ $lawyer->created_at->format('F d, Y h:i A') }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Specializations:</th>
                                <td>
                                    @forelse ($lawyer->specializations as $spec)
                                        <span class="badge bg-primary me-1 mb-1">{{ $spec->title }}</span>
                                    @empty
                                        <span class="text-muted">No specializations assigned</span>
                                    @endforelse
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
