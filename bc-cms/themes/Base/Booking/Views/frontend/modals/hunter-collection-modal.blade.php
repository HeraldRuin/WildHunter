<div class="mb-4">
    <div v-for="(hunterSlot, index) in hunterSlots" :key="index" class="position-relative mb-3">
        <div class="d-flex align-items-start">
            <div class="flex-grow-1 position-relative">
                <input
                    type="text"
                    class="form-control me-2"
                    :placeholder="'{{ __('Hunter nickname / last_name / email / ID') }}'"
                    v-model="hunterSlot.query"
                    :disabled="hunterSlot.hunter && hunterSlot.hunter.invited"
                    @input="searchHunterForSlot(index, {{ $booking->id }})"
                    @change="handleHunterInputChange(index)"
                    @focus="hunterSlot.showResults = true"
                    @blur="setTimeout(() => { hunterSlot.showResults = false; }, 200)">
                <!-- Результаты поиска для этого слота -->
                <div v-if="hunterSlot.showResults"
                     class="position-absolute w-100 bg-white border rounded shadow-sm mt-1"
                     style="z-index: 1000; max-height: 300px; overflow-y: auto;">
                    <!-- Спиннер при поиске -->
                    <div v-if="hunterSlot.isSearching" class="p-2 text-muted text-center">
                        {{ __('Searching...') }}
                    </div>
                    <!-- Результаты -->
                    <div v-else-if="hunterSlot.results.length">
                        <div v-for="hunter in hunterSlot.results"
                             :key="hunter.id"
                             :class="['p-2 border-bottom', (hunter.invited && hunter.invitation_status !== 'declined') ? 'bg-light text-muted' : 'cursor-pointer hover-bg-light']"
                             @click="!(hunter.invited && hunter.invitation_status !== 'declined') && selectHunterForSlot(index, hunter, {{ $booking->id }})"
                             @mousedown.prevent>
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <div>
                                                            <span class="text-muted small">
                                                                <template v-if="hunter.user_name">
                                                                    <strong>ID: @{{ hunter.id }}</strong> ( ник <strong
                                                                        style="font-size: 14px;">@{{ hunter.user_name }}</strong> )
                                                                </template>
                                                                <template v-else>
                                                                    ID: @{{ hunter.id }} ( ник не задан )
                                                                </template>
                                                            </span>
                                        <span
                                            class="text-muted ms-2">@{{ hunter.first_name }} @{{ hunter.last_name }}</span>
                                    </div>
                                    <div class="text-muted small">@{{ hunter.email }}</div>
                                    <div class="mt-1">
                                                            <span
                                                                v-if="hunter.invited && hunter.invitation_status !== 'declined'"
                                                                class="badge bg-success">
                                                                {{ __('Already invited') }}
                                                            </span>
                                        <span v-else-if="hunter.invitation_status === 'declined'"
                                              class="badge bg-danger">
                                                                {{ __('Invitation declined') }}
                                                            </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else-if="hunterSlot.noResults && !hunterSlot.isSearching" class="p-2 border-bottom">
                        <div class="text-muted small mb-2">
                            {{ __('Hunters not found') }}
                        </div>
                        <button
                            type="button"
                            class="btn btn-outline-primary btn-sm"
                            @click="inviteByEmailForSlot(index, {{ $booking->id }}, $event)">
                            <i class="fa fa-envelope me-1"></i>
                            {{ __('Send invitation by email') }}
                        </button>
                    </div>
                </div>
                <!-- Информация о выбранном охотнике (показываем только если текст в поле соответствует выбранному охотнику) -->
                <div
                    v-if="hunterSlot.hunter && hunterSlot.query && ((hunterSlot.hunter.is_external && hunterSlot.query.trim() === hunterSlot.hunter.email) || (!hunterSlot.hunter.is_external && hunterSlot.query.trim() === ((hunterSlot.hunter.user_name || (hunterSlot.hunter.first_name + ' ' + hunterSlot.hunter.last_name)).trim())))"
                    class="mt-2">
                    <div class="d-flex align-items-center mb-1">
                        <span class="text-muted small">@{{ hunterSlot.hunter.email }}</span>
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-start">
                <button
                    v-if="hunterSlot.hunter && hunterSlot.query && ((hunterSlot.hunter.is_external && hunterSlot.query.trim() === hunterSlot.hunter.email) || (!hunterSlot.hunter.is_external && hunterSlot.query.trim() === ((hunterSlot.hunter.user_name || (hunterSlot.hunter.first_name + ' ' + hunterSlot.hunter.last_name)).trim())))"
                    type="button"
                    class="btn btn-sm me-2 ml-2"
                    :class="(hunterSlot.hunter && hunterSlot.hunter.invited && hunterSlot.hunter.invitation_status !== 'declined') ? 'btn-success' : 'btn-outline-primary'"
                    :disabled="!hunterSlot.hunter || (hunterSlot.hunter.invited && hunterSlot.hunter.invitation_status !== 'declined') || hunterSlot.hunter.is_external"
                    @click="inviteHunterForSlot(index, {{ $booking->id }}, $event)">
                    <span
                        v-text="(hunterSlot.hunter && hunterSlot.hunter.invited && hunterSlot.hunter.invitation_status === 'accepted') ? acceptedText : ((hunterSlot.hunter && hunterSlot.hunter.invited && hunterSlot.hunter.invitation_status !== 'declined') ? invitedText : inviteText)"></span>
                </button>
            </div>
        </div>
    </div>
</div>

История отказавшихся охотников
<div v-if="declinedHunters && declinedHunters.length > 0" class="mt-4 mb-4">
    <h6 class="mb-3 text-muted">История приглашений (отказались)</h6>
    <div class="list-group">
        <div v-for="(hunter, index) in declinedHunters" :key="index" class="list-group-item">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center mb-1">
                        <strong>@{{ hunter.first_name }} @{{ hunter.last_name }}</strong>
                    </div>
                    <div class="text-muted small mb-1" v-if="hunter.user_name">
                        <strong>ID: @{{ hunter.id }}</strong> ( ник <strong>@{{ hunter.user_name }}</strong> )
                    </div>
                    <div class="text-muted small">@{{ hunter.email }}</div>
                </div>
                <span class="badge bg-danger ms-2">{{ __('Declined') }}</span>
            </div>
        </div>
    </div>
</div>

<div class="mt-4 p-3 border rounded">
    <div class="d-flex justify-content-center gap-2 flex-wrap">
        <button
            type="button"
            class="btn btn-info mx-2 btn-extend-collection"
            data-booking-id="{{ $booking->id }}"
            disabled
            @click="startCollection($event, {{ $booking->id }})">
            {{ __('Extend collection') }}
        </button>
        <button
            type="button"
            class="btn btn-info"
            @click="cancelCollection($event, {{ $booking->id }})">
            {{ __('Cancel collection') }}
        </button>
        <button
            type="button"
            class="btn btn-info mx-2 btn-finish-collection"
            data-booking-id="{{ $booking->id }}"
            @click="finishCollection($event, {{ $booking->id }})">
            {{ __('Finish collection') }}
        </button>
        <button
            type="button"
            class="btn btn-info mx-2">
            {{ __('Opens collection') }}
        </button>
    </div>
</div>
