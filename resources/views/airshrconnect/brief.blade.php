@extends('layout.main')

@section('styles')
    @parent
    <link href="/js/bootstrap-editable/css/bootstrap-editable.css" media="all" rel="stylesheet" type="text/css" />
    <link href="/js/jcrop/css/Jcrop.min.css" media="all" rel="stylesheet" type="text/css" />
    <link href="/js/bootstrap.slider/css/bootstrap-slider.min.css" media="all" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="/css/mobileeditor.css?v={{ \Config::get('app.ConnectWebAppVersion') }}">
    <link rel="stylesheet" href="/css/brief.css?v={{ \Config::get('app.ConnectWebAppVersion') }}">
    </head>
@endsection

@section('content')

    <div class="content-sub-header">
        <h1 class="content-sub-header-title" id="content_title">Brief</h1>
        <div class="content-sub-header-form">
        </div>

        <div class="content-sub-header-actions">
            <span class="saveProgress"></span>
        </div>
    </div>

    <div id="brief">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-8">
                    <form class="brief-form-left">

                        <div class="form-group">
                            <label for="" >ORDER NUMBER/ATB</label>
                            <input type="text" class="form-control" id=""/>
                        </div>

                        <div class="form-group" style="margin-top:30px;">
                            <div class="input-group">
                                <span class="input-group-addon">Account Exec</span>
                                <input type="text" class="form-control" id=""/>
                            </div>
                        </div>

                        <div class="form-group" style="margin-top:80px;">
                            <div class="input-group">
                                <span class="input-group-addon">Client</span>
                                <input type="text" class="form-control" id="" />
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon">Contact</span>
                                <input type="text" class="form-control" id="" />
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon">Email</span>
                                <input type="text" class="form-control" id="" />
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon">Product</span>
                                <input type="text" class="form-control" id="" />
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon">Mobile</span>
                                <input type="text" class="form-control" id="" />
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon">Fax</span>
                                <input type="text" class="form-control" id="" />
                            </div>
                        </div>

                    </form>

                </div>

                <div id="sidebar_container" class="col-md-16 col-sm-16" style="overflow-y:auto;">
                    <div class="panel-group" id="sidebar">
                        <div class="panel panel-default" id="panel0">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" data-target="#collapseZero"
                                       href="#collapseZero">
                                        Spots
                                    </a>
                                </h4>

                            </div>
                            <div id="collapseZero" class="panel-collapse collapse in">
                                <div class="panel-body">
                                    <spot v-for="spot in spots" :spot="spot"></spot>
                                    <button class="btn btn-default" v-on:click="addSpot">Add New Spot</button>
                                </div>
                            </div>
                        </div>
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title" style="text-align:center;font-weight:bold;">
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
                                <div class="panel-body"><textarea class="form-control" id=""></textarea></div>
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
                                <div class="panel-body"><textarea class="form-control" id=""></textarea></div>
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
                                <div class="panel-body"><textarea class="form-control" id=""></textarea></div>
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
                                <div class="panel-body"><textarea class="form-control" id=""></textarea></div>
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
                                <div class="panel-body"><textarea class="form-control" id=""></textarea></div>
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
                                <div class="panel-body"><textarea class="form-control" id=""></textarea></div>
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
                                <div class="panel-body"><textarea class="form-control" id=""></textarea></div>
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
                                <div class="panel-body"><textarea class="form-control" id=""></textarea></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <template id="spot-template">
        <div id="spot-@{{ spot.id }}">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <select class="form-control">
                            <option>Recorded/Live</option>
                            <option value="recorded">Recorded</option>
                            <option value="live">Live</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon">Length</span>
                            <input type="text" class="form-control" id="" v-model="spot.adLength" />
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon">Start Date</span>
                            <input type="text" class="form-control start-date" v-model="spot.startDate"/>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon">End Date</span>
                            <input type="text" class="form-control end-date" id="" v-model="spot.endDate"/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-22">
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon">Comments</span>
                            <textarea class="form-control" id="" v-model="spot.comments"></textarea>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <a href="javascript:void(0)" v-on:click="removeSpot(spot.id)"><i class="mdi mdi-close"></i>Remove Spot</a>
                </div>
            </div>
            <hr>
        </div>
    </template>
@endsection

@section('scripts')
    @parent
    <script src="/js/timepicker/jquery.timepicker.min.js" type="text/javascript"></script>
    <script src="/js/bootstrap-editable/js/bootstrap-editable.min.js"></script>
    <script src="/js/typeaheadjs.js"></script>

    <script src="/js/jcrop/js/Jcrop.js"></script>
    <script src="/js/bootstrap.slider/bootstrap-slider.min.js"></script>
    <script src="/js/bootstrap-modal-popover/bootstrap-modal-popover.js"></script>
    <script src="/js/brief.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>

    <script>
        //get id
    </script>
@endsection