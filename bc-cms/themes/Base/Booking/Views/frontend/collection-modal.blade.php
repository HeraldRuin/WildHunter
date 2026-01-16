<div class="modal fade" id="collectionModal{{ $booking->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Open collection for booking') }} #{{ $booking->id }}</h5>
{{--                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>--}}
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <label class="form-label">{{ __('Find by nickname or last name') }}</label>
                    <div class="d-flex align-items-center">
                        <input
                            type="text"
                            class="form-control"
                            placeholder="{{ __('Start typing nickname or last name') }}"
                            v-model="hunterSearchQuery"
                            @input="searchHunterDebounced">
                        <button type="button" class="btn btn-outline-danger btn-sm ms-2 ml-2" style="min-width: 40px;"
                                @click="hunterSearchQuery = ''; hunterSearchResults = []; hunterNoResults = false;">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                    <div v-if="hunterIsSearching" class="text-muted mt-2">
                        {{ __('Searching...') }}
                    </div>
                    <div v-if="hunterNoResults && hunterSearchQuery.length >= 4" class="text-danger mt-2">
                        {{ __('Hunters not found') }}
                    </div>
                    <div v-if="hunterSearchResults.length" class="mt-2">
                        <div
                            v-for="hunter in hunterSearchResults"
                            :key="hunter.id"
                            class="d-flex justify-content-between align-items-start p-2 mb-1 border rounded">
                            <div class="flex-grow-1">
                                <span class="text-muted small">
                                    <template v-if="hunter.user_name">
                                        (ник <strong style="font-size: 14px;">@{{ hunter.user_name }}</strong>)
                                    </template>
                                    <template v-else>
                                        (ник не задан)
                                    </template>
                                </span>
                                <span class="text-muted ms-2">@{{ hunter.first_name }} @{{ hunter.last_name }}</span>
                                <div class="mt-1">
                                    <span class="text-muted small">@{{ hunter.email }}</span>
                                    <button
                                        v-if="!hunter.showEmailInput"
                                        type="button"
                                        class="btn btn-outline-secondary btn-sm ms-2"
                                        @click="hunter.showEmailInput = true">
                                        {{ __('Send email') }}
                                    </button>
                                </div>
                                <div v-if="hunter.showEmailInput" class="mt-2 d-flex align-items-start">
                                    <textarea
                                        class="form-control form-control-sm"
                                        rows="2"
                                        v-model="hunter.emailMessage"
                                        placeholder="Введите сообщение"></textarea>
                                    <button
                                        type="button"
                                        class="btn btn-info btn-sm ms-3 ml-2"
                                        @click="sendHunterEmail(hunter, {{ $booking->id }}, $event)">
                                        Отправить
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm ms-2 ml-2" style="min-width: 40px;"
                                            @click="hunter.showEmailInput = false">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <button
                                type="button"
                                class="btn btn-sm ms-2"
                                :class="hunter.invited ? 'btn-success' : 'btn-info'"
                                :disabled="hunter.invited"
                                @click.stop="!hunter.invited && inviteHunter(hunter, {{ $booking->id }}, $event)">
                                <span v-text="hunter.invited ? invitedText : inviteText"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex align-items-center mb-2">
                        <input type="text" class="form-control me-2" placeholder="{{ __('Hunter nickname / name') }}">
                        <button type="button" class="btn btn-outline-primary btn-sm">
                            {{ __('Invite') }}
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm ms-2" style="min-width: 40px;">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <input type="text" class="form-control me-2" placeholder="{{ __('Hunter nickname / name') }}">
                        <button type="button" class="btn btn-outline-primary btn-sm">
                            {{ __('Invite') }}
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm ms-2" style="min-width: 40px;">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>

                <div class="mt-4 p-3 border rounded">
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <button
                            type="button"
                            class="btn btn-info"
                            @click="startCollection($event, {{ $booking->id }})">
                            {{ __('Open collection') }}
                        </button>
                        <button type="button" class="btn btn-info mx-2">
                            {{ __('Extend collection') }}
                        </button>
                        <button type="button" class="btn btn-info">
                            {{ __('Cancel collection') }}
                        </button>
                        <button type="button" class="btn btn-info mx-2">
                            {{ __('Finish collection') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
