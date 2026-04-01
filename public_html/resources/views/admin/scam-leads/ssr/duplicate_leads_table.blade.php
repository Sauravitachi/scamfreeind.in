@use(App\Utilities\Html)
@use(App\Constants\Permission)

@php
    $country = country($leads->first()?->country_code ?? 'in');
    $countryLabel = $country->getEmoji() . ' ' . $country->getName();
@endphp

<div class="container mt-4">
    <div class="table-responsive">
        <table class="table">
            <thead class="table-dark">
                <tr>
                    <th>Sr.</th>
                    <th>{!! Html::icon('ti ti-alert-square-rounded fs-2') !!}</th>
                    <th>{!! Html::icon('ti ti-user fs-2') !!} </th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Country</th>
                    <th>Phone Number</th>
                    <th>Scam Type</th>
                    <th>Scam Amount</th>
                    <th>Lead Source</th>
                    <th>Registered At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($leads as $lead)
                @php
                    $hasErrors = $lead->errors && !empty($lead->errors);
                @endphp
                    <tr>
                        <td class="text-secondary">
                            #{{ $loop->iteration }}
                        </td>
                        <td>
                            @if ($hasErrors)
                                <span class="bg-danger text-white avatar avatar-xs avatar-rounded" role="button" onclick="showRowErrorsModal(`{{ json_encode($lead->errors) }}`)">1</span>
                            @else
                            @endif
                        </td>
                        <td>
                            @if ($lead->existingCustomer)
                                <i class="ti ti-user-hexagon fs-1 text-primary"></i>
                            @endif
                        </td>
                        <td>{{ $lead->name }}</td>
                        <td>
                            @if ($lead->email)
                                {{$lead->email}}
                            @else
                                <span class="text-secondary">N/A</span>
                            @endif
                        </td>
                        <td>{{ $countryLabel }}</td>
                        <td>{{ $lead->phone_number }}</td>
                        <td>
                            @if ($lead->scamType)
                                {{ $lead->scamType?->title }}
                            @else
                                <span class="text-secondary">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if ($lead->scam_amount)
                                {{ format_amount($lead->scam_amount) }}
                            @else
                                <span class="text-secondary">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if ($lead->scamSource?->title)
                                {{ $lead->scamSource?->title }}
                            @else
                                <span class="text-secondary">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if ($lead->created_at)
                                {{ format_date($lead->created_at) }}
                            @else
                                <span class="text-secondary">N/A</span>
                            @endif
                        </td>
                        <td>
                            @can(Permission::SCAM_LEAD_TRANSFER)
                                @if(!$lead->errors || empty($lead->errors))
                                    <a href="javascript:;" onclick="transferLead({{ $lead->id }});" class="cursor-pointer mx-1">
                                        <i class="ti ti-transfer text-success h1"></i>
                                    </a>
                                @endif
                            @endcan
                            @can(Permission::SCAM_LEAD_DELETE)
                                <a href="javascript:;" onclick="deleteLead({{ $lead->id }});" class="cursor-pointer mx-1"><i class="ti ti-trash text-danger h1"></i></a>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
