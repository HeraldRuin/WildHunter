@extends ('admin.layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">{{ __('All Orders') }}</h1>
        </div>
        @include('Layout::admin.message')
        <div class="filter-div d-flex justify-content-between">
            <div class="col-left">
                <form method="post" action="{{ route('order.admin.bulkEdit') }}"
                    class="filter-form filter-form-left d-flex justify-content-start">
                    @csrf
                    <select name="action" class="form-control">
                        <option value="">{{ __('-- Bulk Actions --') }}</option>
                        @if (!empty($statues))
                            @foreach ($statues as $key => $status)
                                <option value="{{ $key }}">{{ __('Mark as: :name', ['name' => ucfirst($status)]) }}
                                </option>
                            @endforeach
                        @endif
                        <option value="delete">{{ __('DELETE orders') }}</option>
                    </select>
                    <button data-confirm="{{ __('Do you want to delete?') }}"
                        class="btn-default btn btn-icon dungdt-apply-form-btn" type="button">{{ __('Apply') }}</button>
                </form>
            </div>
            <div class="col-left">
                <form method="get" action="" class="filter-form filter-form-right d-flex justify-content-end">
                    @if (!empty($booking_manage_others))
                        <?php
                        $user = !empty(Request()->vendor_id) ? App\User::find(Request()->vendor_id) : false;
                        \App\Helpers\AdminForm::select2(
                            'vendor_id',
                            [
                                'configs' => [
                                    'ajax' => [
                                        'url' => url('/admin/module/user/getForSelect2'),
                                        'dataType' => 'json',
                                    ],
                                    'allowClear' => true,
                                    'placeholder' => __('-- Vendor --'),
                                ],
                            ],
                            !empty($user->id) ? [$user->id, $user->name_or_email . ' (#' . $user->id . ')'] : false,
                        );
                        ?>
                    @endif
                    <input type="text" name="s" value="{{ Request()->s }}"
                        placeholder="{{ __('Search by name or ID') }}" class="form-control">
                    <button class="btn-default btn btn-icon" type="submit">{{ __('Filter') }}</button>
                </form>
            </div>
        </div>
        <div class="text-right">
            <p><i>{{ __('Found :total items', ['total' => $rows->total()]) }}</i></p>
        </div>
        <div class="panel booking-history-manager">
            <div class="panel-title">{{ __('Orders') }}</div>
            <div class="panel-body">
                <form action="" class="bc-form-item bc-form-item">
                    <table class="table table-hover bc-list-item">
                        <thead>
                            <tr>
                                <th width="80px"><input type="checkbox" class="check-all"></th>
                                <th>{{ __('Customer') }}</th>
                                <th>{{ __('Total') }}</th>
                                <th width="80px">{{ __('Status') }}</th>
                                <th width="150px">{{ __('Payment Method') }}</th>
                                <th width="120px">{{ __('Created At') }}</th>
                                <th width="80px">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                <tr>
                                    <td width="6%">
                                        <input type="checkbox" class="check-item" name="ids[]"
                                            value="{{ $row->id }}">#{{ $row->id }}
                                    </td>
                                    <td>
                                        @php
                                            $billing = $row->getJsonMeta('billing');
                                            $note = $row->getMeta('note');
                                        @endphp
                                        @if (!empty($billing))
                                            <ul>
                                                <li> {{ __('Full Name:') }} {{ $billing['first_name'] ?? '' }} {{ $billing['last_name'] ?? '' }} </li>
                                                <li> {{ __('Email:') }} {{ $row->email }}</li>
                                                <li> {{ __('Phone:') }} {{ $billing['phone'] ?? '' }}</li>
                                                <li> {{ __('Address:') }} {{ $billing['address'] ?? '' }}</li>

                                                @if($note)
                                                    <li> {{ __('Note:') }} {{ $note }}</li>
                                                @endif
                                            </ul>
                                        @endif
                                    </td>
                                    <td>{{ format_money($row->total) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $row->status_badge }}">{{ $row->status_text }}</span>
                                    </td>
                                    <td>
                                        {{ $row->gatewayObj ? $row->gatewayObj->getDisplayName() : '' }}
                                    </td>
                                    <td>{{ display_datetime($row->order_date ?: $row->created_at) }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle btn-sm" type="button"
                                                id="dropdownMenuButton" data-toggle="dropdown" aria-expanded="false">
                                                {{ __('Actions') }}
                                            </button>
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                <a class="dropdown-item" data-toggle="modal" data-target="#modal-order"
                                                    data-id="{{ $row->id }}"
                                                    data-ajax="{{ route('order.modal', ['code' => $row->code]) }}"
                                                    type="button"><i class="fa fa-eye"></i> {{ __('Detail') }}</a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
        <div class="d-flex justify-content-end">
            {{ $rows->withQueryString()->links() }}
        </div>
    </div>
    <div class="modal" tabindex="-1" id="modal-order">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Order ID: #') }} <span class="order_id"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-center">{{ __('Loading...') }}</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script>
        $('#modal-order').on('show.bs.modal', function(e) {
            console.log(e)
            var btn = $(e.relatedTarget);
            $(this).find('.order_id').html(btn.data('id'));
            $(this).find('.modal-body').html(
                '<div class="d-flex justify-content-center">{{ __('Loading...') }}</div>');
            var modal = $(this);
            $.get(btn.data('ajax'), function(html) {
                modal.find('.modal-body').html(html);
            })
        })
    </script>
@endpush
