
<?php 
    $old = !empty($guests) ? $guests : 1;
?>
<div class="form-select-guests" x-data="{guests: {{$old}} }">
    <div class="form-group">
        <i class="field-icon icofont-travelling"></i>
        <div class="form-content">
            <div class="wrapper-more">
                <label> {{ $field['title'] }} </label>
                <div class="render guests-input d-flex align-items-center">
                    <span class="btn-minus" data-input="room" x-on:click="guests = Math.max(1, guests - 1)"><i class="icon ion-md-remove"></i></span>
                    <span class="count-display"><input type="number" name="room" x-model="guests" min="1" x-on:change="guests = parseInt(guests)"></span>
                    <span class="btn-add" data-input="room" x-on:click="guests++"><i class="icon ion-ios-add"></i></span>
                </div>
            </div>
        </div>
    </div>
</div>
