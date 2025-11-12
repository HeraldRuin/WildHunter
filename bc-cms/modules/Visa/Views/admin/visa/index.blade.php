
    <div class="container-fluid" x-data="{
        ids: [],
        toggleAll(event) {
            if (event.target.checked) {
                $wire.set('ids', {{ $rows->pluck('id') }}, false);
                this.ids = {{ $rows->pluck('id') }};
            } else {
                $wire.set('ids', [], false);
                this.ids = [];
            }
        },
        toggleItem(id) {
            if (this.ids.includes(id)) {
                this.ids = this.ids.filter((item) => item !== id);
            } else {
                this.ids.push(id);
            }
            $wire.set('ids', this.ids, false);
        },
            
        }">
        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">{{__("All Visa")}}</h1>
            <div class="title-actions">
                <a wire:navigate href="{{route('visa.admin.create')}}" class="btn btn-primary">{{__("Add new visa")}}</a>
            </div>
        </div>
        @include('admin.message')
        <div class="filter-div d-flex justify-content-between ">
            <div class="col-left">
                @if(!empty($rows))
                    <form wire:submit.prevent="bulkEdit"  class="filter-form filter-form-left d-flex justify-content-start">
                        <select wire:model="action" class="form-control">
                            <option value="">{{__(" Bulk Actions ")}}</option>
                            <option value="publish">{{__(" Publish ")}}</option>
                            <option value="draft">{{__(" Move to Draft ")}}</option>
                            <option value="pending">{{__("Move to Pending")}}</option>
                            <option value="clone">{{__(" Clone ")}}</option>
                            <option value="delete">{{__(" Delete ")}}</option>
                        </select>
                        <button data-confirm="{{__("Do you want to delete?")}}" class="btn-info btn btn-icon" type="submit">{{__('Apply')}}</button>
                    </form>
                @endif
            </div>
            <div class="col-left">
                <form method="get" wire:submit.prevent="search" class="filter-form filter-form-right d-flex justify-content-end flex-column flex-sm-row" role="search">
                    <input type="text" wire:model="s" placeholder="{{__('Search by name')}}" class="form-control">
                    <button class="btn-info btn btn-icon btn_search" type="submit">{{__('Search')}}</button>
                </form>
            </div>
        </div>
        <div class="text-right">
            <p><i>{{__('Found :total items',['total'=>$rows->total()])}}</i></p>
        </div>
        <div class="panel">
            <div class="panel-body">
                <form action="" class="bc-form-item">
                    <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th width="60px"><input type="checkbox" x-on:change="toggleAll($event)" ></th>
                            <th> {{ __('Title')}}</th>
                            <th> {{ __('Country')}}</th>
                            <th width="130px"> {{ __('Type')}}</th>
                            <th width="130px"> {{ __('Code')}}</th>
                            <th width="100px"> {{ __('Status')}}</th>
                            <th width="100px"> {{ __('Date')}}</th>
                            <th width="100px"> {{ __('Actions')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if($rows->total() > 0)
                            @foreach($rows as $row)
                                <tr class="{{$row->status}}">
                                    <td><input type="checkbox" name="ids[]" class="check-item" x-bind:value="{{$row->id}}" x-on:change="toggleItem({{$row->id}})">
                                    </td>
                                    <td class="title">
                                        <a href="{{route('visa.admin.edit',['id'=>$row->id])}}">{{$row->title}}</a>
                                    </td>
                                    <td>{{get_country_name($row->to_country ?? '')}}</td>
                                    <td>{{$row->visaType->name ?? ''}}</td>
                                    <td>{{$row->code ?? ''}}</td>
                                    <td><span class="badge badge-{{ $row->status }}">{{ $row->status }}</span></td>
                                    <td>{{ display_date($row->updated_at)}}</td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-info dropdown-toggle btn-sm" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-th"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                                                <a class="dropdown-item" href="{{route('visa.admin.edit',['id'=>$row->id])}}" >{{__('Edit')}}
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7">{{__("No data found")}}</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                    </div>
                </form>
                {{$rows->links()}}
            </div>
        </div>
    </div>