<div class="btn-upload-private-wrap" x-data="FormFilePicker">
    <div class="private-file-lists mb-2 row">
        @if(!empty($data[$field['id']]))
        @php
            $old = JSON_decode($data[$field['id']], true) ?? [];
            $files = !empty($field['multiple']) ? $old : [$old];
        @endphp
            @foreach($files as $file)
                <div class="col-md-3">
                    <img style="max-width: 100%; height:auto;" src="{{route('simple-form.upload-preview',['path' => $file['path'] ?? '','v' => uniqid()])}}" alt="{{$file['name']}}">
                    <a target="_blank" href="{{route('simple-form.upload-preview',['path' => $file['path'] ?? '','v' => uniqid()])}}" class="file-item">{{$file['name']}} <i class="fa fa-download"></i></a>
                </div>
            @endforeach
        @endif
    </div>
    @if(empty($only_show_data))
        <span class="btn btn-primary btn-sm position-relative" x-bind:disabled="loading"><i class="fa fa-upload"></i> {{__('Select File')}}
            <input x-bind:disabled="loading" x-on:change="upload($event)" data-options="{{ json_encode($options) }}" data-id="{{ $field['id'] }}" class="btn-upload-private-file position-absolute" style="top:0;left:0;right:0;bottom:0;opacity:0;" accept="{{implode(',', $field['mime_types']) }}" data-multiple="" type="file" >
        </span>
    @else
        @if(empty($field['data']))
            <div><strong>{{__('N/A')}}</strong></div>
        @endif
    @endif
</div>