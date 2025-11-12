<div class="form-group">
    <label>{{ $field['label'] }}:</label>
    <?php
    $fieldValue = $fieldsMapById[$field['id']]['value'] ?? null;
    $fieldValueText = $fieldsMapById[$field['id']]['value_text'] ?? null;
    ?>
    @if($field['type'] == 'file_picker')
        <div class="row">
            @if(!empty($fieldValue))
            <div class="col-md-3">
                <img style="max-width: 100%; height:auto;" src="{{ route('simple-form.upload-preview',['path' => $fieldValue['path'] ?? '','v' => uniqid()]) }}" alt="{{ $fieldValue['name'] ?? '' }}">
                <a target="_blank" href="{{ route('simple-form.upload-preview',['path' => $fieldValue['path'] ?? '','v' => uniqid()]) }}"> {{ $fieldValue['name'] ?? '' }} <i class="fa fa-download"></i> </a>
            </div>
            @endif
        </div>
    @else
    <strong>{{ $fieldValueText ?? '' }}</strong>
    @endif
</div>