
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">{{__("Visa Types")}}</h1>
        </div>
        @include('admin.message')
        <div class="row">
            <div class="col-md-4 mb40">
                <div class="panel">
                    <div class="panel-title">{{__("Add Visa Type")}}</div>
                    <div class="panel-body">
                        <form wire:submit.prevent="store" method="post">
                            @include('Visa::admin.type.form')
                            <div class="">
                                <button class="btn btn-primary" type="submit">{{__("Add new")}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="filter-div d-flex justify-content-between ">
                    <div class="col-left">
                        @if(!empty($rows))
                            <form wire:submit.prevent="bulkEdit" class="filter-form filter-form-left d-flex justify-content-start">
                                <select wire:model="action" class="form-control">
                                    <option value="">{{__(" Bulk Action ")}}</option>
                                    <option value="publish">{{__(" Publish ")}}</option>
                                    <option value="draft">{{__(" Move to Draft ")}}</option>
                                    <option value="delete">{{__(" Delete ")}}</option>
                                </select>
                                <button data-confirm="{{__("Do you want to delete?")}}" class="btn-info btn btn-icon" type="submit">{{__('Apply')}}</button>
                            </form>
                        @endif
                    </div>
                    <div class="col-left">
                        <div class="filter-form filter-form-right d-flex justify-content-end" role="search">
                            <input type="text" wire:model="s" value="{{ Request()->s }}" class="form-control" placeholder="{{__("Search by name")}}">
                            <button wire:click="$refresh" class="btn-info btn btn-icon btn_search" id="search-submit" type="submit">{{__('Search')}}</button>
                        </div>
                    </div>
                </div>
                <div class="panel">
                    <div class="panel-body">
                        <div class="bc-form-item">
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th width="60px"><input type="checkbox" class="check-all"></th>
                                    <th>{{__("Name")}}</th>
                                    <th class="status">{{__("Status")}}</th>
                                    <th class="date ">{{__("Date")}}</th>
                                    <th>{{__("Actions")}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if($rows->total() > 0)
                                    @foreach ($rows as $row)
                                    <tr>
                                        <td><input type="checkbox" wire:model="ids" name="ids[]" value="{{$row->id}}" class="check-item">
                                        <td class="title">
                                            <a wire:navigate href="{{route('visa.admin.type.edit',['id'=>$row->id])}}">{{$row->name}}</a>
                                        </td>
                                        <td><span class="badge badge-{{ $row->status }}">{{ $row->status }}</span></td>
                                        <td>{{ display_date($row->updated_at)}}</td>
                                        <td>
                                            <div class="btn-group">
                                                <a wire:navigate href="{{route('visa.admin.type.edit',['id'=>$row->id])}}" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="5">{{__("No data")}}</td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                            {{$rows->links()}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
