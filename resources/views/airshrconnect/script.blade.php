@extends('layout.main')

@section('styles')
    @parent
    <link href="/js/bootstrap-editable/css/bootstrap-editable.css" media="all" rel="stylesheet" type="text/css" />
    <link href="/js/jcrop/css/Jcrop.min.css" media="all" rel="stylesheet" type="text/css" />
    <link href="/js/bootstrap.slider/css/bootstrap-slider.min.css" media="all" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="/css/mobileeditor.css?v={{ \Config::get('app.ConnectWebAppVersion') }}">
    <link rel="stylesheet" href="/css/script.css?v={{ \Config::get('app.ConnectWebAppVersion') }}">
    <script src='//cdn.tinymce.com/4/tinymce.min.js'></script>
    </head>
@endsection

@section('content')

    <div class="content-sub-header">
        <h1 class="content-sub-header-title" id="content_title">Script</h1>

        <div class="content-sub-header-actions">
            <span class="saveProgress"></span>
            <a class="btn-action" href="#sendModal" data-toggle="modal" title="Send"><i class="mdi mdi-email"></i></a>
        </div>
    </div>

    <div id="script">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-18 col-sm-18">

                    <!--Top form-->
                    <div id="script_top_form">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <div class="input-group">
                                        <span class="input-group-addon">Client</span>
                                        <input type="text" class="form-control" v-model="client" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <div class="input-group">
                                        <span class="input-group-addon">Campaign</span>
                                        <input type="text" class="form-control"  v-model="campaign" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <div class="input-group">
                                        <span class="input-group-addon">Sales Rep</span>
                                        <input type="text" class="form-control"  v-model="salesRep" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <div class="input-group">
                                        <span class="input-group-addon">Producer</span>
                                        <input type="text" class="form-control"  v-model="producer" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--Script tabs and show less/more button-->
                    <div class="row">
                        <div class="col-sm-24">
                            <ul class="nav nav-tabs">
                                <li>
                                    <a href="javascript:void(0)" v-on:click="hideForm">
                                        <i class="mdi" v-bind:class="{ 'mdi-chevron-up' : !formHidden, 'mdi-chevron-down' : formHidden }"></i>
                                        <span v-show="!formHidden">Show Less</span>
                                        <span v-show="formHidden">Show More</span>
                                    </a>
                                </li>
                                <template v-for="script in scripts">
                                    <li v-bind:class="{ 'active' : script.isActive }">
                                        <a v-show="!script.renaming" href="javascript:void(0)" v-on:click="changeScript(script.id)">@{{script.name}}<span class="close" v-on:click="removeScript(script.id)"><i class="mdi mdi-close"></i></span></a>
                                        <input v-show="script.renaming" type="text" class="form-control rename-script-field" v-model="script.name" v-on:keyup.enter="renameScript" v-on:blur="renameScript">
                                    </li>
                                </template>
                                <li><a href="javascript:void(0)" v-on:click="addScript"><i class="mdi mdi-plus"></i></a></li>
                            </ul>
                        </div>
                    </div>

                    <div id="script_form">

                        <div class="row">
                            <div class="col-md-16">
                                <div class="col-md-12 col-sm-12">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <span class="input-group-addon">Key Number</span>
                                            <input type="text" class="form-control" v-model="scripts[currentScriptIndex].adKey" />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <span class="input-group-addon">Feature</span>
                                            <input type="text" class="form-control" v-model="scripts[currentScriptIndex].feature"  />
                                        </div>
                                    </div>

                                </div>

                                <div class="col-md-12 col-sm-12">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <span class="input-group-addon">Duration</span>
                                            <input type="text" class="form-control" v-model="scripts[currentScriptIndex].duration"  />
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <span class="input-group-addon">Start</span>
                                                    <input type="text" class="form-control" v-model="scripts[currentScriptIndex].start"  />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <span class="input-group-addon">End</span>
                                                    <input type="text" class="form-control" v-model="scripts[currentScriptIndex].end"  />
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>


                                <div class="col-sm-24">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <span class="input-group-addon">Headline</span>
                                            <input type="text" class="form-control" v-model="scripts[currentScriptIndex].headline" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-8 col-sm-8">
                                <div class="row">
                                    <div class="col-md-14 col-sm-14">
                                        <div class="form-group">
                                            <select class="form-control" v-model="scripts[currentScriptIndex].broadcastType" >
                                                <option value="none">Recorded/Live</option>
                                                <option value="recorded">Recorded</option>
                                                <option value="live">Live</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <select class="form-control" v-model="scripts[currentScriptIndex].voiceGender" >
                                                <option value="none">Voice Gender</option>
                                                <option value="male">Male</option>
                                                <option value="female">Female</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <div class="input-group">
                                                <span class="input-group-addon">Voice Type</span>
                                                <input type="text" class="form-control" v-model="scripts[currentScriptIndex].voiceType" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-10 col-sm-10">

                                        <button type="button" class="btn btn-lg" v-bind:class="{ 'btn-default' : !isRecording, 'btn-danger' : isRecording }" v-on:click="record" style="width:100%;">
                                            <i class="mdi mdi-microphone"></i>
                                            <div v-show="!isRecording">Record <br /> Demo</div>
                                            <div v-show="isRecording">Stop <br /> (<span>@{{ formatSeconds(recordLength) }}</span>)</div>
                                        </button>

                                        <audio v-bind:src="scripts[currentScriptIndex].audioRecording.url" id="demo_recording" controls style="width:100%;">Your browser does not support audio recording.</audio>
                                        <a id="demo_download_link" style="color:black;" v-bind:href="scripts[currentScriptIndex].audioRecording.url" v-bind:download="scripts[currentScriptIndex].audioRecording.downloadName">
                                            @{{ scripts[currentScriptIndex].audioRecording.downloadName ? scripts[currentScriptIndex].audioRecording.downloadName : "No Recording"}}
                                        </a>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="input-group">
                                        <label for="">Other Considerations</label>
                                        <textarea class="form-control" v-model="scripts[currentScriptIndex].considerations" ></textarea>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <br />

                    <textarea id="script_content" v-tinymce-editor="scripts[currentScriptIndex].scriptContent">
                    </textarea>
                </div>

                <!--https://jsfiddle.net/Wc4xt/1052/-->
                <div id="sidebar_container" class="col-md-6 col-sm-6" style="overflow-y:auto;">
                    <div class="panel-group" id="sidebar">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title" style="text-align:center;font-weight:bolder;">
                                    BRIEF
                                </h4>
                            </div>
                        </div>
                        <div class="panel panel-default" id="panel1">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" data-target="#collapseOne"
                                       href="#collapseOne">
                                        Instructions
                                    </a>
                                </h4>

                            </div>
                            <div id="collapseOne" class="panel-collapse collapse in">
                                <div class="panel-body">Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.</div>
                            </div>
                        </div>
                        <div class="panel panel-default" id="panel2">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" data-target="#collapseTwo"
                                       href="#collapseTwo" class="collapsed">
                                        Voices
                                    </a>
                                </h4>

                            </div>
                            <div id="collapseTwo" class="panel-collapse collapse">
                                <div class="panel-body">Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.</div>
                            </div>
                        </div>
                        <div class="panel panel-default" id="panel3">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" data-target="#collapseThree"
                                       href="#collapseThree" class="collapsed">
                                        Main Message in Adverts
                                    </a>
                                </h4>

                            </div>
                            <div id="collapseThree" class="panel-collapse collapse">
                                <div class="panel-body">Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.</div>
                            </div>
                        </div>
                        <div class="panel panel-default" id="panel4">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" data-target="#collapseFour"
                                       href="#collapseFour" class="collapsed">
                                        What is the Unique Selling Point?
                                    </a>
                                </h4>

                            </div>
                            <div id="collapseFour" class="panel-collapse collapse">
                                <div class="panel-body">Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.</div>
                            </div>
                        </div>
                        <div class="panel panel-default" id="panel5">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" data-target="#collapseFive"
                                       href="#collapseFive" class="collapsed">
                                        Special Instructions
                                    </a>
                                </h4>

                            </div>
                            <div id="collapseFive" class="panel-collapse collapse">
                                <div class="panel-body">Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.</div>
                            </div>
                        </div>
                        <div class="panel panel-default" id="panel6">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" data-target="#collapseSix"
                                       href="#collapseSix" class="collapsed">
                                        Purchasing Barrier
                                    </a>
                                </h4>

                            </div>
                            <div id="collapseSix" class="panel-collapse collapse">
                                <div class="panel-body">Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.</div>
                            </div>
                        </div>
                        <div class="panel panel-default" id="panel7">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" data-target="#collapseSeven"
                                       href="#collapseSeven" class="collapsed">
                                        What do you want the customer to do?
                                    </a>
                                </h4>

                            </div>
                            <div id="collapseSeven" class="panel-collapse collapse">
                                <div class="panel-body">Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.</div>
                            </div>
                        </div>
                        <div class="panel panel-default" id="panel8">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" data-target="#collapseEight"
                                       href="#collapseEight" class="collapsed">
                                        Explain here
                                    </a>
                                </h4>

                            </div>
                            <div id="collapseEight" class="panel-collapse collapse">
                                <div class="panel-body">Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div id="sendModal" class="modal fade" role="dialog">
            <div class="modal-dialog">

                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <img id="sending_icon" src="/img/ajax-loader.gif" style="float:right; margin-right:10px; display:none;">
                        <h4 class="modal-title">Send</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row" style="border-bottom:1px solid #e5e5e5;">
                            <div class="col-sm-12" style="text-align: center;">
                                <label class="control-label">Client</label> @{{ client }}
                            </div>
                            <div class="col-sm-12" style="text-align: center;">
                                <label class="control-label">Campaign</label> @{{ campaign }}
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <h5>Select Documents</h5>
                                <div class="checkbox">
                                    <label class="control-label"><input type="checkbox" value="">Brief</label>
                                </div>
                                <template v-for="script in scripts">
                                    <div class="checkbox">
                                        <label class="control-label"><input type="checkbox" value="">@{{ script.name }}</label>
                                    </div>
                                </template>
                            </div>

                            <div class="col-sm-12">
                                <h5>Select Stakeholder</h5>
                                <select class="form-control">
                                    <option>Client</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary">Send</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection


@section('scripts')
    @parent
    <script src="/js/timepicker/jquery.timepicker.min.js" type="text/javascript"></script>
    <script src="/js/bootstrap-editable/js/bootstrap-editable.min.js"></script>
    <script src="/js/typeaheadjs.js"></script>

    <script src="/js/jcrop/js/Jcrop.js"></script>
    <script src="/js/bootstrap.slider/bootstrap-slider.min.js"></script>
    <script src="/js/bootstrap-modal-popover/bootstrap-modal-popover.js"></script>

    <script src="/js/recorder/recorder.js"></script>
    <script src="/js/script.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>
@endsection