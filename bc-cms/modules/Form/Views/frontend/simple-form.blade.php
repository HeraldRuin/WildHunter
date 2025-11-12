<div>
    <form wire:submit.prevent="saveStep" class="bc-simple-form">
        @if(!empty($this->steps))
        <div class="row">
            <div class="col-md-3">
                <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                    @foreach($this->steps as $index=>$step)
                        <button 
                            class="border-0 nav-link {{ $index == $this->stepIndex ? 'active' : '' }} mb-2" 
                            @if($index > $this->maxStepIndex) disabled @elseif($index !== $this->stepIndex) wire:click="setStep('{{ $index }}')" @endif 
                            id="{{ $step['id'] }}-tab" 
                            type="button" 
                            role="tab" 
                            aria-controls="{{ $step['id'] }}" 
                            aria-selected="{{ $index == $this->stepIndex ? 'true' : 'false' }}"
                        >
                        {{ $index + 1 }}. {{ $step['label'] }}
                        </button>
                    @endforeach
                </div>
            </div>
            <div class="col-md-9">
                @if($this->currentStep)
                    <h4>{{ $this->currentStep['label'] }}</h4>
                    <hr>
                    <div class="row">
                        @foreach($this->currentStep['children'] as $child)
                            @include('Form::frontend.simple-field', ['field' => $child])
                        @endforeach
                    </div>
                    <div class="text-center">
                        <button class="btn btn-primary">{{ __('Next Step') }}</button>
                    </div>
                @endif
            </div>
        </div>
        @else
            @foreach($this->form as $field)
                @include('Form::frontend.simple-field', ['field' => $field])
            @endforeach
        @endif
    </form>
</div>

@script
<script>
    Alpine.data("FormFilePicker", ()=> {
        return {
            loading: false,
            upload(event) {
                const file = event.target.files[0];
                if (!file) return;
                this.loading = true;
                var me = $(event.target);
                var p = me.closest('.btn-upload-private-wrap');
                var lists = p.find('.private-file-lists');

                const fieldId = me.data('id');
                const options = me.data('options');

                const formData = new FormData();
                formData.append('file', file);
                formData.append('field_id', fieldId);

                if(options){
                    for (const key in options) {
                        formData.append('options[' + key + ']', options[key]);
                    }
                }

                $.ajax({
                    url: bookingCore.url + '/simple-form/upload-file',
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: (res) => {
                        this.loading = false;
                        me.val('');
                        if(res.data){
                            var div = $('<div/>');
                            div.addClass('col-md-3');

                            if(res.data.is_image){
                                div.append("<img style='max-width: 100%; height:auto;' src='" + res.data.download + "' alt='" + res.data.name + "'>");
                                div.append("<a target='_blank' href='" + res.data.download + "'> " + res.data.name + '.' + res.data.file_extension + " <i class=\"fa fa-download\"></i> </a>");
                            }

                            const keys = ['name', 'path', 'file_extension', 'file_type', 'size', 'driver'];
                            const dataToSave = keys.reduce((acc, key) => {
                                if (key in res.data) {
                                    acc[key] = res.data[key];
                                }
                                return acc;
                            }, {});
                            
                            if (me.data('multiple')) {
                                lists.append(div);

                                const old = $wire.get('data.' + fieldId);
                                $wire.set('data.' + fieldId, [...old, JSON.stringify(dataToSave)], false);
                            } else {
                                lists.html(div);
                                $wire.set('data.' + fieldId, JSON.stringify(dataToSave), false);
                            }
                        }

                        if(res.message){
                            alert(res.message);
                        }
                        
                    },
                    error: (e) => {
                        this.loading = false;
                        bookingCoreApp.showAjaxError(e);
                        me.val('');
                    }
                })
            }
        }
    })
</script>
@endscript