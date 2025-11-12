@extends('admin.layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">{{__("Lessons Management")}}</h1>
        </div>
        @include('admin.message')
        <div class="row">
            <div class="col-md-2"></div>
            <div class="col-md-8">

                <div id="lecture_management" v-cloak>
                    <div class="d-flex justify-content-end mb-4" v-if="sections.length > 0">
                        <button @click="openSectionForm" class="btn btn-info btn-sm" type="button">
                            <i class="fa fa-plus"></i> {{__("Add Section")}}
                        </button>
                    </div>
                    <div class="panel" v-for="row in sections">
                        <div class="panel-title d-flex justify-content-between align-items-center row">
                            <div class="col-sm-8 flex-left cursor-pointer" @click="openSectionForm($event,row)">
                                <i v-bind:class="{'text-success':row.active == 1,'text-danger':row.active == 0}" class=" fa fa-circle"></i>
                                <strong>@{{ row.title }} </strong>
                            </div>
                            <div class="col-sm-4 d-flex justify-content-end align-items-center">
                                <div class="btn-group">
                                    <button class="btn btn-outline-primary dropdown-toggle btn-sm ml-1" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fa fa-plus"></i> {{__("Add lecture")}}
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                                        <a href="#" class="dropdown-item" @click.prevent="addLecture('video',row)"><i class="fa fa-play-circle-o"></i> {{__("Add video")}}</a>
                                        <a href="#" class="dropdown-item" @click.prevent="addLecture('presentation',row)"><i class="fa fa-file-powerpoint-o"></i> {{__("Add presentation")}}</a>
                                        <a href="#" class="dropdown-item" @click.prevent="addLecture('iframe',row)"><i class="fa fa-desktop"></i> {{__("Add Iframe")}}</a>
                                        <a href="#" class="dropdown-item" @click.prevent="addLecture('scorm',row)"><i class="fa fa-bookmark"></i> {{__("Add SCORM")}}</a>
                                    </div>
                                </div>

                                <div class="d-inline-flex">
                                    <button class="btn btn-default btn-sm ml-1" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <svg fill="#333333" height="15px" width="20px" id="Layer_1" data-name="Layer 1"
                                             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
                                            <path class="cls-1" d="M8,6.5A1.5,1.5,0,1,1,6.5,8,1.5,1.5,0,0,1,8,6.5ZM.5,8A1.5,1.5,0,1,0,2,6.5,1.5,1.5,0,0,0,.5,8Zm12,0A1.5,1.5,0,1,0,14,6.5,1.5,1.5,0,0,0,12.5,8Z"/>
                                        </svg>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                                        <a href="#" class="dropdown-item" @click.prevent="openSectionForm($event,row)"><i class="fa fa-edit"></i> {{__("Edit section")}}</a>
                                        <a href="#" class="dropdown-item" @click.prevent="deleteSection(row)"><i class="fa fa-trash"></i> {{__("Remove section")}}</a>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="form-group-item">
                                <div class="g-items-header">
                                    <div class="row">
                                        <div class="col-md-5">{{__("Title")}}</div>
                                        <div class="col-md-2">{{__("Type")}}</div>
                                        <div class="col-md-2">{{__("Duration")}}</div>
                                        <div class="col-md-1">{{__("Order")}}</div>
                                        <div class="col-md-2"></div>
                                    </div>
                                </div>
                                <div class="g-items">
                                    <div class="item" v-for="module in row.modules">
                                        <div class="row">
                                            <div class="col-md-5">
                                                <i v-bind:class="{'text-success':module.active == 1,'text-danger':module.active == 0}" class=" fa fa-circle"></i>
                                                @{{module.title}}</div>
                                            <div class="col-md-2 text-center">@{{module.type}}</div>
                                            <div class="col-md-2 text-center">@{{module.duration}}</div>
                                            <div class="col-md-1 text-center">@{{module.display_order}}</div>
                                            <div class="col-md-2 text-center">
                                                <span class="btn btn-warning btn-sm" @click="editLecture(module,row)"><i class="fa fa-pencil"></i></span>
                                                <span class="btn btn-danger btn-sm ml-1" @click="deleteLecture(module,row)"><i class="fa fa-trash"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button @click="openSectionForm" class="btn btn-info btn-sm" type="button">
                            <i class="fa fa-plus"></i> {{__("Add Section")}}
                        </button>
                    </div>

                    <div id="add_lecture_modal" class="modal" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <span v-if="!lecture_form.id">@{{ add_lecture_title }}</span>
                                        <span v-else>{{__("Edit Lecture")}}</span>
                                    </h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label>{{__("Lecture name")}} <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" v-model="lecture_form.title">
                                    </div>
                                    <div class="form-group" v-if="['iframe'].indexOf(lecture_form.type) < 0">
                                        <label>{{__("File")}} </label>
                                        <file-picker :type="lecture_form.type" v-model="lecture_form.file_id"></file-picker>
                                    </div>
                                    <div class="form-group" >
                                        <label>{{__("File URL")}}</label>
                                        <input type="text"  class="form-control" v-model="lecture_form.url">
                                    </div>
                                    <div class="form-group">
                                        <label>{{__("Duration (minute)")}} <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" placeholder="{{__("in minutes")}}" v-model="lecture_form.duration">
                                    </div>
                                    <div class="form-group" >
                                        <label>{{__("Preview Url")}}</label>
                                        <input type="text"  class="form-control" v-model="lecture_form.preview_url">
                                    </div>
                                    <div class="form-group" >
                                        <label>{{__("Status")}}</label>
                                        <select v-model="lecture_form.active" class="form-control">
                                            <option value="1">{{__("Active")}}</option>
                                            <option value="0">{{__("Inactive")}}</option>
                                        </select>
                                    </div>
                                    <div class="form-group" >
                                        <label>{{__("Display Order")}}</label>
                                        <input type="number" min="0" step="1" v-model="lecture_form.display_order" class="form-control">
                                    </div>
                                    <div class="alert alert-danger" v-if="error.length" v-html="error.join('<br>')"></div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('Close')}}</button>
                                    <button type="button" class="btn btn-primary" @click="saveLecture">{{__("Save changes")}}
                                        <i class="fa-spin fa fa-spinner icon-loading" v-show="onSaving"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="add_section_modal" class="modal" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <span v-if="!section_form.id">{{ __("Add Section") }}</span>
                                        <span v-else>{{__("Edit Section")}}</span>
                                    </h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label>{{__("Section name")}} <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" v-model="section_form.title">
                                    </div>
                                    <div class="form-group" >
                                        <label>{{__("Status")}}</label>
                                        <select v-model="section_form.active" class="form-control">
                                            <option value="1">{{__("Active")}}</option>
                                            <option value="0">{{__("Inactive")}}</option>
                                        </select>
                                    </div>
                                    <div class="form-group" >
                                        <label>{{__("Display Order")}}</label>
                                        <input type="number" min="0" step="1" v-model="section_form.display_order" class="form-control">
                                    </div>
                                    <div class="alert alert-danger" v-if="error.length" v-html="error.join('<br>')"></div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('Close')}}</button>
                                    <button type="button" class="btn btn-primary" @click="saveSection">{{__("Save changes")}}
                                        <i class="fa-spin fa fa-spinner icon-loading" v-show="onSaving"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <script>
            var course_sections_data = {!! json_encode($row->admin_js_data) !!};
        </script>
    </div>
@endsection
