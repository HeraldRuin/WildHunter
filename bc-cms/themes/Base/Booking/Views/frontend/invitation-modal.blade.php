{{-- Модальное окно для просмотра приглашения (похоже на collectionModal, но без поиска) --}}
@php
    $invitation = $booking->getCurrentUserInvitation();
@endphp
@if($invitation)
    <div class="modal fade" id="invitationModal{{ $booking->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Open collection for hunting') }}</h5>
                </div>
                <div class="modal-body">
                    @if($invitation->status === 'invited')
                        <div class="mt-4 p-3 border rounded">
                            <div class="d-flex justify-content-center gap-2 flex-wrap">
                                <button
                                    type="button"
                                    class="btn btn-success mr-2"
                                    onclick="acceptInvitation({{ $booking->id }})">
                                    {{ __('Accept') }}
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-danger"
                                    onclick="declineInvitation({{ $booking->id }})">
                                    {{ __('Decline') }}
                                </button>
                            </div>
                        </div>
                    @endif
                    {{-- Список всех приглашенных охотников (исключая текущего пользователя, если он еще не принял приглашение) --}}
                    @php
                        $allInvitations = $booking->getAllInvitations();
                        $currentUserId = Auth::id();
                        // Фильтруем приглашения: исключаем текущего пользователя только если он еще не принял приглашение
                        $otherInvitations = $allInvitations->filter(function($inv) use ($currentUserId) {
                            if (!$inv->hunter) {
                                return false;
                            }
                            // Если это текущий пользователь, показываем его только если он принял приглашение
                            if ($inv->hunter_id == $currentUserId) {
                                return $inv->status === 'accepted';
                            }
                            // Всех остальных показываем всегда
                            return true;
                        });
                    @endphp
                    @if($otherInvitations && $otherInvitations->count() > 0)
                        <div class="mt-4" v-pre>
                            <h6 class="mb-3">{{ __('Invited Hunters') }}</h6>
                            @foreach($otherInvitations as $inv)
                                <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center">
                                            <span class="me-2">
                                                @if($inv->hunter->user_name)
                                                    <strong>{{ $inv->hunter->user_name }}</strong>
                                                @endif
                                                {{ $inv->hunter->first_name }} {{ $inv->hunter->last_name }}
                                            </span>
                                            <span class="badge
                                                @if($inv->status === 'accepted') bg-success
                                                @elseif($inv->status === 'declined') bg-danger
                                                @else bg-info
                                                @endif">
                                                @if($inv->status === 'accepted')
                                                    {{ __('Accepted') }}
                                                @elseif($inv->status === 'declined')
                                                    {{ __('Declined') }}
                                                @else
                                                    {{ __('Invited') }}
                                                @endif
                                            </span>
                                        </div>
                                        @if($inv->invited_at)
                                            <small class="text-muted">{{ __('Invited At') }}: {{ display_datetime($inv->invited_at) }}</small>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
