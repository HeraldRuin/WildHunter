@extends('layouts.user')
@section('content')
    <h2 class="title-bar">
        {{__("Settings")}}
        <a href="{{route('user.change_password')}}" class="btn-change-password">{{__("Change Password")}}</a>
    </h2>
    @include('admin.message')
    <form action="{{route('user.profile.update')}}" method="post" class="input-has-icon">
        @csrf
        <div class="row row-profile-width">
            <div class="col-md-6">
                <div class="form-title">
                    <strong>{{__("Personal Information")}}</strong>
                </div>
                <div class="form-group">
                    <label>{{__("User name")}} <span class="text-danger">*</span></label>
                    <input type="text" required minlength="4" name="user_name" value="{{old('user_name',$user->user_name)}}" placeholder="{{__("User name")}}" class="form-control">
                    <i class="fa fa-user input-icon"></i>
                </div>
                <div class="form-group">
                    <label>{{__("E-mail")}}</label>
                    <input type="text" name="email" value="{{old('email',$user->email)}}" placeholder="{{__("E-mail")}}" class="form-control">
                    <i class="fa fa-envelope input-icon"></i>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{__("First name")}}</label>
                            <input type="text" value="{{old('first_name',$user->first_name)}}" name="first_name" placeholder="{{__("First name")}}" class="form-control">
                            <i class="fa fa-user input-icon"></i>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{__("Last name")}}</label>
                            <input type="text" value="{{old('last_name',$user->last_name)}}" name="last_name" placeholder="{{__("Last name")}}" class="form-control">
                            <i class="fa fa-user input-icon"></i>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>{{__("Phone Number")}}</label>
                    <input type="text" value="{{old('phone',$user->phone)}}" name="phone" placeholder="{{__("Phone Number")}}" class="form-control">
                    <i class="fa fa-phone input-icon"></i>
                </div>
                <div class="form-group">
                    <label>{{__("Birthday")}}</label>
                    <input type="text" value="{{ old('birthday',$user->birthday? display_date($user->birthday) :'') }}" name="birthday" placeholder="{{__("Birthday")}}" class="form-control date-picker">
                    <i class="fa fa-birthday-cake input-icon"></i>
                </div>
                <div class="form-group">
                    <label>{{__("About Yourself")}}</label>
                    <textarea name="bio" rows="5" class="form-control">{{old('bio',$user->bio)}}</textarea>
                </div>
                <div class="form-group">
                    <label>{{__("Avatar")}}</label>
                    <div class="upload-btn-wrapper">
                        <div class="input-group">
                            <span class="input-group-btn">
                                <span class="btn btn-default btn-file">
                                    {{__("Browse")}}… <input type="file">
                                </span>
                            </span>
                            <input type="text" data-error="{{__("Error upload...")}}" data-loading="{{__("Loading...")}}" class="form-control text-view" readonly value="{{ get_file_url( old('avatar_id',$user->avatar_id) ) ?? $user->getAvatarUrl()?? __("No Image")}}">
                        </div>
                        <input type="hidden" class="form-control" name="avatar_id" value="{{ old('avatar_id',$user->avatar_id)?? ""}}">
                        <img class="image-demo" src="{{ get_file_url( old('avatar_id',$user->avatar_id) ) ??  $user->getAvatarUrl() ?? ""}}"/>
                    </div>
                </div>
            </div>
{{--            <div class="col-md-6" style="margin-top:33px;">--}}
{{--                <div class="form-group">--}}
{{--                    <label>{{__("Number hunter billet")}}</label>--}}
{{--                    <input type="text" value="{{old('address',$user->hunter_billet_number)}}" name="hunter_billet_number" placeholder="{{__("Add Number")}}" class="form-control">--}}
{{--                </div>--}}
{{--                <div class="form-group">--}}
{{--                    <label>{{ __("License") }}</label>--}}
{{--                    <div class="row align-items-center">--}}
{{--                        <div class="col-md-6 d-flex align-items-center">--}}
{{--                            <span class="mr-2">{{ __('Numb') }}</span>--}}
{{--                            <input type="text"--}}
{{--                                   name="hunter_license_number"--}}
{{--                                   value="{{ old('hunter_license_number', $user->hunter_license_number) }}"--}}
{{--                                   placeholder="{{ __('Add License') }}"--}}
{{--                                   class="form-control">--}}
{{--                        </div>--}}
{{--                        <div class="col-md-6 d-flex align-items-center">--}}
{{--                            <span class="mr-2">{{ __('Date') }}</span>--}}
{{--                            <input type="date"--}}
{{--                                   name="hunter_license_date"--}}
{{--                                   value="{{ old('hunter_license_date', $user->hunter_license_date) }}"--}}
{{--                                   class="form-control">--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="form-group" id="weapon-app">--}}
{{--                    <label>{{ __("Weapon type") }}</label>--}}
{{--                    <select name="weapon_type_id" class="form-control">--}}
{{--                        <option value="">{{ __('Add Weapon') }}</option>--}}
{{--                        @foreach($weapons as $weapon)--}}
{{--                            <option value="{{ $weapon->id }}"--}}
{{--                                {{ $user->weapon_type_id == $weapon->id ? 'selected' : '' }}>--}}
{{--                                {{ $weapon->title }}--}}
{{--                            </option>--}}
{{--                        @endforeach--}}
{{--                    </select>--}}
{{--                </div>--}}
{{--                <div class="form-group">--}}
{{--                    <label>{{__("Caliber")}}</label>--}}
{{--                    <select name="caliber" class="form-control">--}}
{{--                        <option value="">{{ __('Add Caliber') }}</option>--}}
{{--                        @foreach($calibers as $caliber)--}}
{{--                            <option value="{{ $caliber->id }}"--}}
{{--                                {{ $user->caliber == $caliber->id ? 'selected' : '' }}>--}}
{{--                                {{ $caliber->title }}--}}
{{--                            </option>--}}
{{--                        @endforeach--}}
{{--                    </select>--}}
{{--                </div>--}}
{{--            </div>--}}
            <div class="col-md-6" style="margin-top:33px;">
                <div class="form-group">
                    <label>{{__("Number hunter billet")}}</label>
                    <input type="text" value="{{old('address',$user->hunter_billet_number)}}" name="hunter_billet_number" placeholder="{{__("Add Number")}}" class="form-control">
                </div>
                <div id="weapon-app">
                    <div class="weapon-row border p-3 mb-2"
                         v-for="(weaponItem, index) in weapons"
                         :key="weaponItem.id ">

                        <div class="form-group">
                            <label>{{ __("License") }}</label>
                            <div class="row align-items-center">
                                <div class="col-md-6 d-flex align-items-center">
                                    <span class="mr-2">{{ __('Numb') }}</span>
                                    <input type="text"
                                           :name="`hunter_license_number`"
                                           v-model="weaponItem.hunter_license_number"
                                           oninput="this.value = this.value.replace(/\D/g, '')"
                                           placeholder="{{ __('Add License') }}"
                                           class="form-control">
                                </div>

                                <div class="col-md-6 d-flex align-items-center">
                                    <span class="mr-2">{{ __('Date') }}</span>
                                    <input type="date"
                                           :name="`hunter_license_date`"
                                           v-model="weaponItem.hunter_license_date"
                                           class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>{{ __("Weapon type") }}</label>
                            <select class="form-control"
                                    :name="`weapon_type_id`"
                                    v-model="weaponItem.weapon_type_id">
                                <option value="">{{ __('Add Weapon') }}</option>
                                @foreach($weapons as $weapon)
                                    <option value="{{ $weapon->id }}">
                                        {{ $weapon->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>{{__("Caliber")}}</label>
                            <select class="form-control"
                                    :name="`caliber`"
                                    v-model="weaponItem.caliber">
                                <option value="">{{ __('Add Caliber') }}</option>
                                @foreach($calibers as $caliber)
                                    <option value="{{ $caliber->id }}">
                                        {{ $caliber->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button v-if="weaponItem.id" type="button" class="btn btn-danger"
                                @click="removeWeapon(weaponItem.id)">
                            Удалить оружие
                        </button>
                    </div>

                    <button type="button"
                            class="btn btn-primary mt-2"
                            @click="addNewRow()">
                        Добавить оружие
                    </button>
                    <button type="button"
                            class="btn btn-secondary mt-2 ml-2"
                            v-if="hasUnsavedWeapon"
                            @click="cancelLastWeapon()">
                        Отмена
                    </button>

                </div>

            </div>

            <div class="col-md-12">
                <hr>
                <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> {{__('Save Changes')}}</button>
            </div>
        </div>
    </form>
    @if(!empty(setting_item('user_enable_permanently_delete')) and !is_admin())
    <hr>
    <div class="row">
        <div class="col-md-12">
            <h4 class="text-danger">
                {{__("Delete account")}}
            </h4>
            <div class="mb-4 mt-2">
                {!! clean(setting_item_with_lang('user_permanently_delete_content','',__('Your account will be permanently deleted. Once you delete your account, there is no going back. Please be certain.'))) !!}
            </div>
            <a data-toggle="modal" data-target="#permanentlyDeleteAccount" class="btn btn-danger" href="">{{__('Delete your account')}}</a>
        </div>

        <!-- Modal -->
        <div class="modal  fade" id="permanentlyDeleteAccount" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content ">
                    <div class="modal-header">
                        <h5 class="modal-title">{{__('Confirm permanently delete account')}}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="my-3">
                            {!! clean(setting_item_with_lang('user_permanently_delete_content_confirm')) !!}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('Close')}}</button>
                        <a href="{{route('user.permanently.delete')}}" class="btn btn-danger">{{__('Confirm')}}</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
    @endif

@endsection
<script>
    window.initialWeapons = @json($userWeapons);
</script>
