<div x-data="bookingForm" class="bc_single_book_wrap mb-3">
    <form wire:submit.prevent="addToCart" class="bc_single_book">
        <div id="bc_visa_book_app">
            @if ($row->discount_percent)
                <div class="tour-sale-box">
                    <span class="sale_class box_sale sale_small">{{ $row->discount_percent }} %</span>
                </div>
            @endif
            <div class="form-head">
                <div class="price">
                    <span class="label">
                        {{ __('from') }}
                    </span>
                    <span class="value">
                        @if ($row->original_price)
                            <span class="onsale">{{ format_money($row->original_price) }}</span>
                        @endif
                        <span class="text-lg">{{ format_money($row->price) }}</span>
                    </span>
                </div>
            </div>
            <div class="nav-enquiry">
                <div class="enquiry-item active">
                    <span>{{ __('Book') }}</span>
                </div>
            </div>
            <div class="form-book">
                <div class="form-content">
                    <div class="form-group form-guest-search">
                        <div class="guest-wrapper d-flex justify-content-between align-items-center">
                            <div class="flex-grow-1">
                                <label>{{ __('Applications') }}</label>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="input-number-group">
                                    <i class="icon ion-ios-remove-circle-outline" x-on:click="setGuests(Math.max(1, guests - 1))"></i>
                                    <span class="input"><input type="number" x-bind:value="guests" min="1"
                                        x-on:change="setGuests(parseInt($event.target.value))" /></span>
                                    <i class="icon ion-ios-add-circle-outline" x-on:click="setGuests(guests + 1)"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <template x-if="total_price > 0">
                        <ul class="form-section-total list-unstyled">
                            <li>
                                <label>{{ __('Total') }}</label>
                                <span class="price" x-text="bc_format_money(total_price)"></span>
                            </li>
                        </ul>
                    </template>
                </div>
                <div class="submit-group">
                    <button class="btn btn-primary" type="submit" x-bind:disabled="total_price <= 0">
                        {{ __('Book Now') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@script
    <script>
        Alpine.data('bookingForm', () => ({
            guests: $wire.entangle('guests'),
            bookingData: $wire.entangle('bookingData'),
            setGuests(guests){
                $wire.set('guests', guests, false);
                this.guests = guests;
            },
            get total_price_html(){
                return bc_format_money(this.total_price);
            },
            get total_price(){
                return this.bookingData.price * this.guests;
            }
        }));
    </script>
@endscript