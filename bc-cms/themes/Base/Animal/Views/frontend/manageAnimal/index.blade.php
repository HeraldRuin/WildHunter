@extends('layouts.user')
@section('content')
        <h2 class="title-bar">
            {{!empty($recovery) ?__('Recovery Animal') : __("Manage Animals")}}
            @if(Auth::user()->hasPermission('animal_create') && empty($recovery))
                <div style="position:absolute; z-index:1000; background:#fff; width:460px; top:19px; right:30px;" id="animal-app" data-bulk-url="{{ route('animal.vendor.bulk_attach') }}">
                    <select v-model="animalIdToAttach" class="form-control" @change="attachAnimal">
                        <option value="">{{ __('Select animal') }}</option>
                        @foreach($animal_list as $animal)
                            <option value="{{ $animal->id }}">{{ $animal->title }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </h2>

        <div class="container-fluid list-animal-width">
        <div class="panel">
            <div class="panel-body">
                <form action="" class="bc-form-item">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th> {{ __('Name')}}</th>
                                <th width="100px"></th>
                            </tr>
                            </thead>
                            <tbody>

                            @if($rows->total() > 0)
                                @foreach($rows as $row)
                                    <tr class="{{$row->status}}">
                                        <td class="title">
                                            @if($row->is_featured)
                                                <span class="badge badge-primary">{{ __("Featured") }}</span>
                                            @endif
{{--                                            <a href="{{route('animal.admin.edit',['id'=>$row->id])}}">{{$row->title}}</a>--}}
                                            <span>{{$row->title}}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route("animal.vendor.bulk_detach",[$row->id]) }}" class="btn btn-danger btn-sm">{{__("Delete")}}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="7">{{__("No animal found")}}</td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>
                </form>
                {{$rows->appends(request()->query())->links()}}
            </div>
        </div>
    </div>
@endsection




