@extends('Layout::admin.app')
@section('content')
    <form action="{{route('visa.admin.store', $row->id)}}" method="post">
        @csrf
        <div class="container-fluid">
            <div class="d-flex justify-content-between mb20">
                <div class="">
                    <h1 class="title-bar">{{$row->id ? __('Edit: ').$row->title : __('Add new visa')}}</h1>
                </div>

                @if($row->slug)
                <div>
                    <a href="{{route('visa.detail',['slug'=>$row->slug])}}" class="btn btn-default">{{__('View on website')}}</a>
                </div>
                @endif
            </div>
            @include('admin.message')
            @if($row->id)
                @include('Language::admin.navigation')
            @endif
            <div class="lang-content-box">
                <div class="row">
                    <div class="col-md-9">
                        @include('Visa::admin.visa.content')
                        @include('Core::admin/seo-meta/seo-meta')
                    </div>
                    <div class="col-md-3">
                        <div class="panel">
                            <div class="panel-title"><strong>{{__('Publish')}}</strong></div>
                            <div class="panel-body">
                                @if(is_default_lang())
                                    <div>
                                        <label><input @if($row->status == 'publish') checked @endif type="radio" name="status" value="publish"> {{__("Publish")}}
                                        </label></div>
                                    <div>
                                        <label><input @if($row->status == 'draft') checked @endif type="radio" name="status" value="draft"> {{__("Draft")}}
                                        </label></div>
                                    <div>
                                        <label><input @if($row->status == 'pending') checked @endif type="radio" name="status" value="pending"> {{__("Pending")}}
                                        </label></div>
                                @endif
                                <div class="text-right">
                                    <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> {{__('Save Changes')}}</button>
                                </div>
                            </div>
                        </div>
                        @if(is_default_lang())
                        <div class="panel">
                            <div class="panel-title"><strong>{{__("Author Setting")}}</strong></div>
                            <div class="panel-body">
                                <div class="form-group">
                                    <?php
                                    $user = !empty($row->author_id) ? App\User::find($row->author_id) : false;
                                    \App\Helpers\AdminForm::select2('author_id', [
                                        'configs' => [
                                            'ajax'        => [
                                                'url' => url('/admin/module/user/getForSelect2'),
                                                'dataType' => 'json'
                                            ],
                                            'allowClear'  => true,
                                            'placeholder' => __('-- Select User --')
                                        ]
                                    ], !empty($user->id) ? [
                                        $user->id,
                                        $user->display_name . ' (#' . $user->id . ')'
                                    ] : false, false, [
                                        'wire:model' => 'author_id',
                                        'wire:ignore' => true,
                                    ])
                                    ?>
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="panel">
                            <div class="panel-title"><strong>{{__('Featured Image')}}</strong></div>
                            <div class="panel-body">
                                <div class="form-group">
                                    {!! \Modules\Media\Helpers\FileHelper::fieldUpload('image_id', $row->image_id) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection