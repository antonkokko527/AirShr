@extends('layout.main')

@section('styles')
@parent
<link href="/js/datatables-1.10.7/css/jquery.dataTables.min.css" media="all" rel="stylesheet" type="text/css" />
<!-- <link href="/js/datatable-bootstrap/dataTables.bootstrap.css" media="all" rel="stylesheet" type="text/css" /> -->
<link href="/js/bootstrap-editable/css/bootstrap-editable.css" media="all" rel="stylesheet" type="text/css" />
<link href="/js/jcrop/css/Jcrop.min.css" media="all" rel="stylesheet" type="text/css" />
<link href="/js/bootstrap.slider/css/bootstrap-slider.min.css" media="all" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="/css/mobileeditor.css?v={{ \Config::get('app.ConnectWebAppVersion') }}">
<style>
	tbody tr.selected {
		background-color: #B0BED9 !important;
	}
</style>

@endsection

@section('content')
<div class="content-sub-header">
	<h1 class="content-sub-header-title" id="content_title"></h1>
	<div class="content-sub-header-form">
		<form id="content-sub-header-form">
			<div class="form-group">
				<select class="form-control" id="content_content_type_id">
					<option value="0">Select</option>
					@foreach ($content_type_list_for_connect as $key => $val)
					<option value="{{ $key }}">{{ $val }}</option>
					@endforeach
				</select>
			</div>

		</form>
	</div>
	<div class="content-sub-header-form">
		<form id="content-sub-header-second-form">
			<div class="form-group">
				<input type="text" class="form-control" id="dailylog_date" placeholder="Daily Log Date" />
			</div>
		</form>
	</div>
	
	<div class="content-sub-header-form">
		<form id="content-sub-header-version-form">
			<div class="form-group">
				<select class="form-control" id="content_version">
					<option value="">Version</option>
					@foreach ($content_version_list as $key => $val)
					<option value="{{ $key }}">{{ $val }}</option>
					@endforeach
				</select>
			</div>
		</form>
	</div>
	
	<div class="content-sub-header-form">
		<h1 class="content-sub-header-info" id="content_created_date_info">CREATED 01-Jul 03:22</h1>
	</div>
	
	<div class="content-sub-header-form" id="goBackLinkContainer">
		<a href="javascript:void(0)" class="goBackLink">Return to Daily Log</a>
	</div>
	<div class="content-sub-header-actions">
		<span class="saveProgress"></span>
		<a class="btn-action" title="Search" id="content_btn_search"><i class="mdi mdi-magnify"></i></a>
		<a class="btn-action" title="New" id="content_btn_new"><i class="mdi mdi-plus"></i></a>
		<div class="dropdown" style="float:right; display: inline-block;">
			<a class="dropdown-toggle btn-action" data-toggle="dropdown"><i class="mdi mdi-dots-vertical"></i></a>
			<ul class="dropdown-menu dropdown-menu-right">
				<li><a class="btn-action" title="Print" id="content_btn_print"><i class="mdi mdi-printer"></i> Print</a></li>
				<li><a class="btn-action" title="Copy" id="content_btn_copy"><i class="mdi mdi-content-copy"></i> Copy</a></li>
				<li><a class="btn-action" title="Save" id="content_btn_save"><i class="mdi mdi-content-save"></i> Save</a></li>
				<li><a class="btn-action" title="Remove" id="content_btn_remove"><i class="mdi mdi-delete"></i> Delete</a></li>
				<li><a class="btn-action" title="Preview" id="content_btn_preview"><i class="mdi mdi-eye"></i> Preview</a></li>
			</ul>
		</div>
	</div>
</div>

<div class="content-form container-fluid">
	@include('airshrconnect.mobileeditor')

	<div id="clientModal" class="modal fade" role="dialog">
		<div class="modal-dialog">

			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Select Existing Client</h4>
				</div>
				<div class="modal-body">
					<table class="table table-hover">
						<thead>
						<tr>
							<th>Company Name</th>
							<th>Trading Name</th>
						</tr>
						</thead>
						<tbody id="existing_client_table">
						</tbody>
					</table>
					<button type="button" class="btn btn-primary create-client-button"><i class="mdi mdi-plus"></i>Create New Client</button>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal" id="client_close_button">Close</button>
				</div>
			</div>

		</div>
	</div>

	<form class="content-form" id="content_add_form" style="display: none">
		<div class="row">
		
			<div class="col-sm-12">
			
				<div class="row">
					<div class="col-sm-12">
					
						<div class="form-group" id="content_sub_type_talk_wrapper">
							<select class="form-control" id="content_content_sub_type_id3">
								<option value="0">Type</option>
								@foreach ($content_sub_type_list[4] as $key => $val)
								<option value="{{ $key }}">{{ $val }}</option>
								@endforeach
							</select>
						</div>
						
						<div class="form-group" id="content_session_name_wrapper">
							<input type="text" class="form-control" id="content_session_name" placeholder="Session Name" />
						</div>
						
						
						<div id="content_talk_date_range_wrapper">
							<div class="form-group">
								<div class="row">
									<div class="col-sm-12">
										<input type="text" class="form-control" id="content_talk_start_date" placeholder="Start Date" />
									</div>
									<div class="col-sm-12">
										<input type="text" class="form-control" id="content_talk_end_date" placeholder="End Date" />
									</div>
								</div>
							</div>
						</div>
						
						<div id="content_talk_time_range_wrapper">
							<div class="form-group">
								<div class="row">
									<div class="col-sm-12">
										<input type="text" class="form-control" id="content_talk_start_time" placeholder="Start Time" />
									</div>
									<div class="col-sm-12">
										<input type="text" class="form-control" id="content_talk_end_time" placeholder="End Time" />
									</div>
								</div>
							</div>
						</div>
						
						<div class="form-group" id="content_talk_weekdays_wrapper">
							<div class="row">
								<div class="col-sm-3">M<br/><span class="check-mark big-size deactive check-box" id="content_talk_weekday_1"></span></div>
								<div class="col-sm-3">T<br/><span class="check-mark big-size deactive check-box" id="content_talk_weekday_2"></span></div>
								<div class="col-sm-3">W<br/><span class="check-mark big-size deactive check-box" id="content_talk_weekday_3"></span></div>
								<div class="col-sm-3">Th<br/><span class="check-mark big-size deactive check-box" id="content_talk_weekday_4"></span></div>
								<div class="col-sm-3">F<br/><span class="check-mark big-size deactive check-box" id="content_talk_weekday_5"></span></div>
								<div class="col-sm-3">Sa<br/><span class="check-mark big-size deactive check-box" id="content_talk_weekday_6"></span></div>
								<div class="col-sm-3">Su<br/><span class="check-mark big-size deactive check-box" id="content_talk_weekday_0"></span></div>
							</div>
						</div>
						
						<div class="form-group" id="content_sub_type_ad_length_wrapper">
							<div class="row">
								<div class="col-sm-8">
									<select class="form-control" id="content_content_sub_type_id">
										<option value="0">Type</option>
										@foreach ($content_sub_type_list[1] as $key => $val)
										<option value="{{ $key }}">{{ $val }}</option>
										@endforeach
									</select>
								</div>
								<div class="col-sm-8">
									<select class="form-control" id="content_rec_type">
										<option value="">Rec/Live</option>
										<option value="rec">Rec</option>
										<option value="live">Live</option>
										<option value="sim_live">Sim Live</option>
									</select>
								</div>
								<div class="col-sm-8">
									<select class="form-control" id="content_ad_length">
										<option value="0">Dur.</option>
										@foreach ($ad_duration_list as $key => $val)
										<option value="{{ $key }}">{{ $val }}</option>
										@endforeach
									</select>
								</div>
							</div>
						</div>
						
						<div class="form-group" id="content_client_wrapper">
							<div class="ui-widget">
								<input type="text" class="form-control" id="content_client" placeholder="Client Company" />
							</div>
						</div>
						
						<div class="form-group" id="content_product_only_wrapper">
							<div class="ui-widget">
								<input type="text" class="form-control" id="content_product" placeholder="Product" />
							</div>
						</div>	
						
						<div class="form-group" id="content_product_and_type_wrapper">
							<div class="row">
								<div class="col-sm-12">
									<select class="form-control" id="content_content_sub_type_id2">
										<option value="0">Type</option>
										@foreach ($content_sub_type_list[8] as $key => $val)
										<option value="{{ $key }}">{{ $val }}</option>
										@endforeach
									</select>
								</div>
								<div class="col-sm-12">
									<div class="ui-widget">
										<input type="text" class="form-control" id="content_product2" placeholder="Product" />
									</div>
								</div>
							</div>
						</div>	
						
						<div class="form-group">
							<select class="form-control" id="content_manager_user_id2">
								<option value="0">Account Executive</option>
								@foreach ($executive_list as $user)
								<option value="{{ $user->id }}">{{$user->executive_name}}</option>
								@endforeach
							</select>
						</div>
									
						<div class="form-group" style="display:none">
							<input type="text" class="form-control" id="content_description" placeholder="Desription" />
						</div>
							
						<div id="content_notfortalk_elements_wrapper1">
						<div class="form-group">
							<div class="row">
								<div class="col-sm-12">
									<input type="text" class="form-control" id="content_atb_date" placeholder="ATB / Order #" />
								</div>
								<div class="col-sm-12">
									<input type="text" class="form-control" id="content_line_number" placeholder="Line #" />
								</div>
							</div>
						</div>
						
						<div class="form-group">
							<input type="text" class="form-control" id="content_contact" placeholder="Contact" />
						</div>
						
						<div class="form-group">
							<input type="text" class="form-control" id="content_email" placeholder="Email" />
						</div>
						
						<div class="form-group">
							<input type="text" class="form-control" id="content_phone" placeholder="Phone" />
						</div>
						
						<div class="form-group">
							<input type="text" class="form-control" id="content_instructions" placeholder="Instructions" />
						</div>
						
						<div class="form-group">
							<input type="text" class="form-control" id="content_voices" placeholder="Voices" />
						</div>
						
						
						<div class="form-group">
							<select class="form-control" id="content_manager_user_id">
								<option value="0">Account Executive</option>
								@foreach ($executive_list as $user)
								<option value="{{ $user->id }}">{{$user->executive_name}}</option>
								@endforeach
							</select>
						</div>
						
						<div class="form-group">
							<select class="form-control" id="content_agency_id">
								<option value="0">Agency Name</option>
								@foreach ($agency_list as $agency)
								<option value="{{ $agency->id }}">{{$agency->agency_name}}</option>
								@endforeach
							</select>
						</div>
						
						</div>
						
						<!-- <div class="form-group">
							<div class="row">
								<div class="col-sm-4">
									<div class="checkbox">
									  <label>
									    <input type="checkbox" value="1" id="content_map_included">
									    Map
									  </label>
									</div>
								</div>
								<div class="col-sm-20">
									<div class="form-group">
										<input type="text" class="form-control" id="content_map_address1" placeholder="Address line 1" />
									</div>
									<div class="form-group">
										<input type="text" class="form-control" id="content_map_address2" placeholder="Address line 2" />
									</div>
								</div>
							</div>
						</div> -->
						
					</div>
					
					<div class="col-sm-12" id="add_form_middle_section_wrapper">
					
						<div class="form-group" id="content_ad_key_wrapper">
							<input type="text" class="form-control" id="content_ad_key" placeholder="Key #" />
						</div>
						
						<div id="content_date_range_wrapper">
						
						</div>
						
						<!-- <div class="form-group" id="content_date_range_wrapper">
							<div class="row">
								<div class="col-sm-12">
									<input type="text" class="form-control" id="content_start_date" placeholder="Start Date" />
								</div>
								<div class="col-sm-12">
									<input type="text" class="form-control" id="content_end_date" placeholder="End Date" />
								</div>
							</div>
						</div> -->
						
						<div class="form-group">
							<input type="text" class="form-control" id="content_who" placeholder="Who" />
						</div>	
						<div class="form-group">
							<textarea class="form-control" rows="2" id="content_what" placeholder="What" style="padding: 9px 12px"></textarea>
						</div>	
						<div class="form-group">
							<textarea class="form-control" rows="4" id="content_more" placeholder="More info..." style="padding: 7px 12px"></textarea>
						</div>	
						
						<div class="form-group">
							<input type="text" class="form-control" id="content_map_address" placeholder="Map: Street, Town" />
						</div>	
						
						<div class="form-group">
							<select class="form-control" id="content_action_id">
								<option value="0">Action</option>
								@foreach ($action_list as $action)
								<option value="{{ $action->id }}">{{$action->action_label}}</option>
								@endforeach
							</select>
						</div>		
						<div class="form-group">
							<input type="text" class="form-control" id="content_action_param_phone_number" placeholder="Phone number to call" />
						</div>	
						<div class="form-group">
							<input type="text" class="form-control" id="content_action_param_website" placeholder="Website to visit" />
						</div>	
					</div>
					
					
				</div>
			</div>
			
			<div class="col-sm-12" id="add_form_right_section_wrapper">

				<div class="row">
					<div class="col-sm-24">

						<div class="row">

							<div class="col-sm-8">

								<div class="attachment_wrapper same-height dropzone" id="attachment_image1">
									<div class="attachment-preview nofile">
										<div class="attachment-preview-content">
											<div class="attachment-preview-content-nofile">
												<h3>Image 1</h3>
												<h4>Click or drop file</h4>
												<h5>min. 800 * 600</h5>
											</div>
										</div>
									</div>
									<div class="attachment-preview" id="attachment_image1_drop"></div>
									<div class="attachment-preview file" style="display: none" id="attachment_image1_preview">
										<div class="attachment-preview-content">
											<img class="attachment-preview-image aspect-fit" />
											<a class="attachment-remove-link"><i class="mdi mdi-close"></i></a>
											<span class="attachment-filename"></span>
										</div>
									</div>

									<div class="attachment-desc">
										<div class="row">
											<div class="col-sm-6">
												<div class="checkbox">
												  <label>
												    <input type="checkbox" class="video-checkbox" value="">
												    Video
												  </label>
												</div>
											</div>
											<div class="col-sm-18">
												<input type="text" class="form-control video-textbox" placeholder="Video link" />
											</div>
										</div>

									</div>

								</div>

							</div>

							<div class="col-sm-8">

								<div class="attachment_wrapper same-height dropzone" id="attachment_image2">
									<div class="attachment-preview nofile">
										<div class="attachment-preview-content">
											<div class="attachment-preview-content-nofile">
												<h3>Image 2</h3>
												<h4>Click or drop file</h4>
												<h5>min. 800 * 600</h5>
											</div>
										</div>
									</div>
									<div class="attachment-preview" id="attachment_image2_drop"></div>
									<div class="attachment-preview file" style="display: none" id="attachment_image2_preview">
										<div class="attachment-preview-content">
											<img class="attachment-preview-image aspect-fit" />
											<a class="attachment-remove-link"><i class="mdi mdi-close"></i></a>
											<span class="attachment-filename"></span>
										</div>
									</div>

									<div class="attachment-desc">
										<div class="row">
											<div class="col-sm-6">
												<div class="checkbox">
												  <label>
												    <input type="checkbox" class="video-checkbox" value="">
												    Video
												  </label>
												</div>
											</div>
											<div class="col-sm-18">
												<input type="text" class="form-control video-textbox" placeholder="Video link" />
											</div>
										</div>

									</div>

								</div>

							</div>

							<div class="col-sm-8">

								<div class="attachment_wrapper same-height dropzone" id="attachment_image3">
									<div class="attachment-preview nofile">
										<div class="attachment-preview-content">
											<div class="attachment-preview-content-nofile">
												<h3>Image 3</h3>
												<h4>Click or drop file</h4>
												<h5>min. 800 * 600</h5>
											</div>
										</div>
									</div>
									<div class="attachment-preview" id="attachment_image3_drop"></div>
									<div class="attachment-preview file" style="display: none" id="attachment_image3_preview">
										<div class="attachment-preview-content">
											<img class="attachment-preview-image aspect-fit" />
											<a class="attachment-remove-link"><i class="mdi mdi-close"></i></a>
											<span class="attachment-filename"></span>
										</div>
									</div>

									<div class="attachment-desc">
										<div class="row">
											<div class="col-sm-6">
												<div class="checkbox">
												  <label>
												    <input type="checkbox" class="video-checkbox" value="">
												    Video
												  </label>
												</div>
											</div>
											<div class="col-sm-18">
												<input type="text" class="form-control video-textbox" placeholder="Video link" />
											</div>
										</div>

									</div>

								</div>

							</div>

						</div>

					</div>
				</div>

				<div class="row" style="margin-top: 10px">
					<div class="col-sm-24">

						<div class="row">

							<div class="col-sm-8">

								<div class="attachment_wrapper same-height dropzone" id="attachment_logo">
									<div class="attachment-preview nofile">
										<div class="attachment-preview-content">
											<div class="attachment-preview-content-nofile">
												<h3>Logo</h3>
												<h4>Click or drop file</h4>
												<h5>min. 200 * 200</h5>
											</div>
										</div>
									</div>
									<div class="attachment-preview" id="attachment_logo_drop"></div>
									<div class="attachment-preview file" style="display: none" id="attachment_logo_preview">
										<div class="attachment-preview-content">
											<img class="attachment-preview-image aspect-fit" />
											<a class="attachment-remove-link"><i class="mdi mdi-close"></i></a>
											<span class="attachment-filename"></span>
										</div>
									</div>

									<div class="attachment-desc">

									</div>

								</div>

							</div>

							<div class="col-sm-8">

								<div class="attachment_wrapper same-height dropzone" id="attachment_audio">
									<div class="attachment-preview nofile">
										<div class="attachment-preview-content">
											<div class="attachment-preview-content-nofile">
												<h3>Audio</h3>
												<h4>Click or drop file</h4>
												<h5>(.mp3 or .wav)</h5>
											</div>
										</div>
									</div>
									<div class="attachment-preview" id="attachment_audio_drop"></div>
									<div class="attachment-preview file" style="display: none" id="attachment_audio_preview">
										<div class="attachment-preview-content">
											<a class="attachment-remove-link"><i class="mdi mdi-close"></i></a>
											<span class="attachment-filename"></span>
											<span class="attachment-audio-preview"><i class="mdi mdi-music-note"></i></span>
										</div>
									</div>

									<div class="attachment-desc">

									</div>

								</div>

							</div>

							<div class="col-sm-8">

								<div class="form-group" id="content_ready_to_print_wrapper">
									<div class="checkbox">
									  <label>
									    <input type="checkbox" value="1" id="content_ready_to_print">
										Ready to Print
									  </label>
									</div>
								</div>

								<div class="form-group">
									<div class="checkbox">
									  <label>
									    <input type="checkbox" value="1" id="content_text_enabled">
										Text
									  </label>
									</div>
								</div>

								<div class="form-group" id="content_audio_enabled_wrapper">
									<div class="checkbox">
									  <label>
									    <input type="checkbox" value="1" id="content_audio_enabled">
										Audio
									  </label>
									</div>
								</div>

								<div class="form-group">
									<div class="checkbox">
									  <label>
									    <input type="checkbox" value="1" id="content_image_enabled">
										Images
									  </label>
									</div>
								</div>

								<div class="form-group">
									<div class="checkbox">
									  <label>
									    <input type="checkbox" value="1" id="content_action_enabled">
										Action
									  </label>
									</div>
								</div>

								<div class="form-group">
									<div class="checkbox">
									  <label>
									    <input type="checkbox" value="1" id="content_is_ready">
										Ready to AirShr
									  </label>
									</div>
								</div>

								<div id="content_talk_segment_assign_wrapper">

									<div class="form-group">

										<button type="button" class="btn btn-primary" id="content_talk_assign_btn">Assign</button>

									</div>

									<div class="form-group">
										<div class="checkbox">
										  <label>
										    <input type="checkbox" value="1" id="content_is_competition">
											Competition
										  </label>
										</div>
									</div>

								</div>


								<div id="content_create_demo_tag_wrapper">

									<div class="form-group">

										<button type="button" class="btn btn-primary" id="content_create_demo_tag">Create Demo Tag</button>

									</div>

									<div class="form-group">
										<input type="text" class="form-control" id="content_demo_tag_ids" placeholder="" readonly/>
									</div>

								</div>


							</div>

						</div>

					</div>
				</div>

			</div>
			
			
		</div>
	</form>


	
	<form class="content-form" id="content_client_add_form" style="display: none">
		<div class="row">
			<div class="col-sm-6">
				
				<div class="form-group">
					<input type="text" class="form-control" id="content_client_name" placeholder="Client Company" />
				</div>
				
				<div class="form-group">
					<div class="ui-widget">
						<input type="text" class="form-control" id="content_client_product" placeholder="Product" />
					</div>
				</div>
				
				<div class="form-group">
					<input type="text" class="form-control" id="content_client_contact" placeholder="Contact" />
				</div>
				
				<div class="form-group">
					<input type="text" class="form-control" id="content_client_email" placeholder="Email" />
				</div>
				
				<div class="form-group">
					<input type="text" class="form-control" id="content_client_phone" placeholder="Phone" />
				</div>
						
			</div>
			<div class="col-sm-6 text-center">
				
				<div class="inline-container-80">
					<div class="attachment_wrapper same-height dropzone" id="client_attachment_logo">
						<div class="attachment-preview nofile">
							<div class="attachment-preview-content">
								<div class="attachment-preview-content-nofile">
									<h3><i class="mdi mdi-plus"></i></h3>
									<h4>Add Logo</h4>
								</div>
							</div>
						</div>
						<div class="attachment-preview" id="client_attachment_logo_drop"></div>
						<div class="attachment-preview file" style="display: none" id="client_attachment_logo_preview">
							<div class="attachment-preview-content">
								<img class="attachment-preview-image aspect-fit" />
								<a class="attachment-remove-link"><i class="mdi mdi-close"></i></a>
								<span class="attachment-filename"></span>
							</div>
						</div>
						
						<div class="attachment-desc">
							
						</div>
					
					</div>
				</div>
				
			</div>
			<div class="col-sm-6">
				
				<div class="form-group">
					<input type="text" class="form-control" id="content_client_who" placeholder="Who" />
				</div>
				
				<div class="form-group">
					<input type="text" class="form-control" id="content_client_map_address" placeholder="Map: Street, Town" />
				</div>	
				
				<div class="form-group">
				
				</div>
				
				<div class="form-group">
					<select class="form-control" id="content_client_manager_user_id">
						<option value="0">Account Executive</option>
						@foreach ($executive_list as $user)
						<option value="{{ $user->id }}">{{$user->executive_name}}</option>
						@endforeach
					</select>
				</div>
				
				<div class="form-group">
					<select class="form-control" id="content_client_agency_id">
						<option value="0">Agency Name</option>
						@foreach ($agency_list as $agency)
						<option value="{{ $agency->id }}">{{$agency->agency_name}}</option>
						@endforeach
					</select>
				</div>
				
				<div class="form-group text-right">
					<div class="checkbox">
					  <label>
					    <input type="checkbox" value="1" id="content_client_enabled"/>
						Client Info ready
					  </label>
					</div>
				</div>
						
			</div>
			<div class="col-sm-6">
				
			</div>
		</div>
	</form>
	
	<form class="content-form" id="content_search_form" style="display: none">
		
		<div class="row" id="content_search_content_form">
		
			<div class="col-sm-12">
		
				<div class="row">
				
					<div class="col-sm-12">
					
						<div class="form-group" id="search_content_sub_type_talk_wrapper">
							<select class="form-control" id="search_content_sub_type_id3">
								<option value="0">Type</option>
								@foreach ($content_sub_type_list[4] as $key => $val)
								<option value="{{ $key }}">{{ $val }}</option>
								@endforeach
							</select>
						</div>
						
						<div class="form-group" id="search_content_session_name_wrapper">
							<input type="text" class="form-control" id="search_content_session_name" placeholder="Session Name" />
						</div>
						
						<div class="form-group" id="search_adtype_length_wrapper">
							<div class="row">
								<div class="col-sm-8">
									<select class="form-control" id="search_content_sub_type_id">
										<option value="0">Type</option>
										@foreach ($content_sub_type_list[1] as $key => $val)
										<option value="{{ $key }}">{{ $val }}</option>
										@endforeach
									</select>
								</div>
								<div class="col-sm-8">
									<select class="form-control" id="search_content_rec_type">
										<option value="">Rec/Live</option>
										<option value="rec">Rec</option>
										<option value="live">Live</option>
										<option value="sim_live">Sim Live</option>
									</select>
								</div>
								<div class="col-sm-8">
									<select class="form-control" id="search_ad_length">
										<option value="0">Dur.</option>
										@foreach ($ad_duration_list as $key => $val)
										<option value="{{ $key }}">{{ $val }}</option>
										@endforeach
									</select>
								</div>
							</div>
						</div>
						
						<div class="form-group" id="search_content_client_wrapper">
							<div class="ui-widget">
								<input type="text" class="form-control" id="search_content_client" placeholder="Client Company" />
							</div>
						</div>
					
						<div class="form-group" id="search_content_atb_line_wrapper">
							<div class="row">
								<div class="col-sm-12">
									<input type="text" class="form-control" id="search_atb_date" placeholder="ATB / Order #" />
								</div>
								<div class="col-sm-12">
									<input type="text" class="form-control" id="search_line_number" placeholder="Line #" />
								</div>
							</div>
						</div>
						
						<div class="form-group" id="search_start_end_date_wrapper">
							<div class="row">
								<div class="col-sm-12">
									<input type="text" class="form-control" id="search_start_date" placeholder="Start Date" />
								</div>
								<div class="col-sm-12">
									<input type="text" class="form-control" id="search_end_date" placeholder="End Date" />
								</div>
							</div>
						</div>
						
						<div id="search_content_talk_time_range_wrapper">
							<div class="form-group">
								<div class="row">
									<div class="col-sm-12">
										<input type="text" class="form-control" id="search_content_talk_start_time" placeholder="Start Time" />
									</div>
									<div class="col-sm-12">
										<input type="text" class="form-control" id="search_content_talk_end_time" placeholder="End Time" />
									</div>
								</div>
							</div>
						</div>
						
						<div class="form-group" id="search_content_talk_weekdays_wrapper">
							<div class="row">
								<div class="col-sm-3">M<br/><span class="check-mark big-size deactive check-box" id="search_content_talk_weekday_1"></span></div>
								<div class="col-sm-3">T<br/><span class="check-mark big-size deactive check-box" id="search_content_talk_weekday_2"></span></div>
								<div class="col-sm-3">W<br/><span class="check-mark big-size deactive check-box" id="search_content_talk_weekday_3"></span></div>
								<div class="col-sm-3">Th<br/><span class="check-mark big-size deactive check-box" id="search_content_talk_weekday_4"></span></div>
								<div class="col-sm-3">F<br/><span class="check-mark big-size deactive check-box" id="search_content_talk_weekday_5"></span></div>
								<div class="col-sm-3">Sa<br/><span class="check-mark big-size deactive check-box" id="search_content_talk_weekday_6"></span></div>
								<div class="col-sm-3">Su<br/><span class="check-mark big-size deactive check-box" id="search_content_talk_weekday_0"></span></div>
							</div>
						</div>
						
						<div class="form-group" id="search_content_type_created_date">
							<div class="row">
								<div class="col-sm-12">
									<select class="form-control" id="search_content_sub_type_id2">
										<option value="0">Type</option>
										@foreach ($content_sub_type_list[8] as $key => $val)
										<option value="{{ $key }}">{{ $val }}</option>
										@endforeach
									</select>
								</div>
								<div class="col-sm-12">
									<input type="text" class="form-control" id="search_created_date" placeholder="Created" />
								</div>
							</div>
						</div>
						
					</div>
					
					<div class="col-sm-12">
					
						<div class="form-group" id="search_ad_key_wrapper">
							<input type="text" class="form-control" id="search_ad_key" placeholder="Key #" />
						</div>	
						
						<div class="form-group" id="search_content_product_wrapper">
							<div class="ui-widget">
								<input type="text" class="form-control" id="search_content_product" placeholder="Product" />
							</div>
						</div>	
						
						<div class="form-group" id="search_content_client_wrapper2">
							<div class="ui-widget">
								<input type="text" class="form-control" id="search_content_client3" placeholder="Client Company" />
							</div>
						</div>
						
						<div id="search_content_who_what_wrapper">
							<div class="form-group">
								<input type="text" class="form-control" id="search_content_who" placeholder="Who" />
							</div>	
							<div class="form-group">
								<textarea class="form-control" rows="2" id="search_content_what" placeholder="What" style="padding: 9px 12px"></textarea>
							</div>	
						</div>
						
						<div class="form-group">
							<select class="form-control" id="search_manager_user_id">
								<option value="0">Account Executive</option>
								@foreach ($executive_list as $user)
								<option value="{{ $user->id }}">{{$user->executive_name}}</option>
								@endforeach
							</select>
						</div>
						
						<div class="form-group">
							<select class="form-control" id="search_agency_id">
								<option value="0">Agency Name</option>
								@foreach ($agency_list as $agency)
								<option value="{{ $agency->id }}">{{$agency->agency_name}}</option>
								@endforeach
							</select>
						</div>
					
					</div>
				
				</div>
			
			</div>
		
			<div class="col-sm-12">
			
			</div>
		
		</div>
		
		
		<div class="row" id="client_search_form">
		
			<div class="col-sm-12">
		
				<div class="row">
				
					<div class="col-sm-12">

						<div class="form-group">
							<div class="ui-widget">
								<input type="text" class="form-control" id="search_content_client2" placeholder="Client Company" />
							</div>
						</div>
					
					</div>
					
					<div class="col-sm-12">
						
						<div class="form-group">
							<div class="ui-widget">
								<input type="text" class="form-control" id="search_content_product2" placeholder="Product" />
							</div>
						</div>	
						
						<div class="form-group">
							<select class="form-control" id="search_manager_user_id2">
								<option value="0">Account Executive</option>
								@foreach ($executive_list as $user)
								<option value="{{ $user->id }}">{{$user->executive_name}}</option>
								@endforeach
							</select>
						</div>
						
						<div class="form-group">
							<select class="form-control" id="search_agency_id2">
								<option value="0">Agency Name</option>
								@foreach ($agency_list as $agency)
								<option value="{{ $agency->id }}">{{$agency->agency_name}}</option>
								@endforeach
							</select>
						</div>
					
					</div>
				
				</div>
			
			</div>
		
			<div class="col-sm-12">
			
			</div>
		
		</div>
		
		
		<div class="row" id="dailylog_statistics_container">
			
			<div class="col-sm-1">&nbsp;</div>
		
			<div class="col-sm-3 preview_analytics_info" id="preview_analytics_1" data-content-type="1">
				<p class="content-type">Ads</p>
				<p class="tag-count">487</p>
				<p class="content-type-status"><i class="mdi mdi-alert-circle error-red"></i></p>
				<p class="tag-missing-count error-red">2 spots</p>
				<p class="tag-missing-count-unique error-red">2 unique items</p>
			</div>
			
			<div class="col-sm-3 preview_analytics_info" id="preview_analytics_6" data-content-type="6">
				<p class="content-type">Promos</p>
				<p class="tag-count">487</p>
				<p class="content-type-status"><i class="mdi mdi-alert-circle error-red"></i></p>
				<p class="tag-missing-count error-red">2 items</p>
				<p class="tag-missing-count-unique error-red">2 unique items</p>
			</div>
			
			<div class="col-sm-3 preview_analytics_info" id="preview_analytics_4" data-content-type="4">
				<p class="content-type">Talk</p>
				<p class="tag-count">487</p>
				<p class="content-type-status"><i class="mdi mdi-alert-circle error-red"></i></p>
				<p class="tag-missing-count error-red">2 items</p>
			</div>
			
			<div class="col-sm-3 preview_analytics_info" id="preview_analytics_3" data-content-type="3">
				<p class="content-type">News</p>
				<p class="tag-count">487</p>
				<p class="content-type-status"><i class="mdi mdi-alert-circle error-red"></i></p>
				<p class="tag-missing-count error-red">2 items</p>
			</div>
			
			<div class="col-sm-3 preview_analytics_info" id="preview_analytics_2" data-content-type="2">
				<p class="content-type">Music</p>
				<p class="tag-count">487</p>
				<p class="content-type-status"><i class="mdi mdi-alert-circle error-red"></i></p>
				<p class="tag-missing-count error-red">2 items</p>
				<p class="tag-missing-count-unique error-red">2 unique items</p>
			</div>
			
			<div class="col-sm-3 preview_analytics_info" id="preview_analytics_100" data-content-type="100">
				<p class="content-type">Other</p>
				<p class="tag-count">487</p>
				<p class="content-type-status"><i class="mdi mdi-alert-circle error-red"></i></p>
				<p class="tag-missing-count error-red">2 items</p>
			</div>
			
			<div class="col-sm-4 preview_analytics_info general" id="preview_analytics_0" data-content-type="0">
				<p class="content-type">All</p>
				<p class="tag-count">487</p>
				<p class="content-type-status"><i class="mdi mdi-alert-circle error-red"></i></p>
				<p class="tag-missing-count error-red">2 items</p>
			</div>
		
			<div class="col-sm-1">&nbsp;</div>
			
		</div>
		
	</form>
	
	
	
	<form class="content-form" id="content_audio_upload_form" style="display: none">
		
		<div class="row">
		
			<div class="col-sm-12">
		
				<div class="attachment_wrapper dropzone" id="audio_builk_dropzone_wrapper">
					<div class="attachment-preview nofile">
						<div class="attachment-preview-content">
							<div class="attachment-preview-content-nofile">
								<p>
									<strong>Audio Drop Zone</strong><br/>
									<strong>(.mp3 or .wav)</strong><br/>	
									Click to select file/s to upload<br/>
									or<br/>
									Drag and drop file/s here
								</p>
								
								
							</div>
						</div>
					</div>
					<div class="attachment-preview" id="audio_builk_dropzone"></div>
				</div>
			
			</div>
		
			<div class="col-sm-12">
			
				<p style="margin-top: 50px;">
					<strong>IMPORTANT:</strong><br/>
					Filenames should include the key number.<br/>
					eg. G14R11466R2.wav or G15R0561B.mp3
				</p>
			
			</div>
		
		</div>
		
	</form>
	
</div>

<div class="content-bottom full" id="content-bottom-full-wrapper">
	
	<table width="100%" id="content-list-table" class="content-table display table-hover">
		<thead>
			<tr>
				<th width="10%">STARTS</th>
				<th width="10%">ENDS</th>
				<th width="10%">TYPE</th>
				<th>REC/<br/>LIVE</th>
				<th>WHO</th>
				<th>WHAT</th>
				<th width="10%">KEY #</th>
				<th width="1%">
					<a class="help-tooltip" href="#" data-toggle="tooltip"
					   data-placement="left" title="Duration" style="color:black;">
						<i class="mdi mdi-alarm"></i>
					</a>
				</th>
				<th width="1%">
					<a class="help-tooltip" href="#" data-toggle="tooltip"
					   data-placement="left" title="Audio exists" style="color:black;">
						<i class="mdi mdi-microphone"></i>
					</a>
				</th>
				<th width="1%">
					<a class="help-tooltip" href="#" data-toggle="tooltip"
					   data-placement="left" title="Text filled in" style="color:black;">
						<i class="mdi mdi-calendar-text"></i>
					</a>
				</th>
				<th width="1%">
					<a class="help-tooltip" href="#" data-toggle="tooltip"
					   data-placement="left" title="Images exist" style="color:black;">
						<i class="mdi mdi-image-album"></i>
					</a>
				</th>
				<th width="1%">
					<a class="help-tooltip" href="#" data-toggle="tooltip"
					   data-placement="left" title="Action button enabled" style="color:black;">
						<i class="mdi mdi-play-circle"></i>
					</a>
				</th>
				<th width="1%">
					<a class="help-tooltip" href="#" data-toggle="tooltip"
					   data-placement="left" title="Ready to AirShr" style="color:black;">
						<i class="mdi mdi-check"></i>
					</a>
				</th>
			</tr>
		</thead>
	</table>
	
	<table width="100%" id="content-talk-list-table" class="content-table display table-hover">
		<thead>
			<tr>
				<th>TYPE</th>
				<th>WHO</th>
				<th>WHAT</th>
				<th width="10%">START<br/>DATE</th>
				<th width="10%">END<br/>DATE</th>
				<th width="10%">START<br/>TIME</th>
				<th width="10%">END<br/>TIME</th>
				<th width="1%">
					<a class="help-tooltip" href="#" data-toggle="tooltip"
					   data-placement="left" title="Text filled in" style="color:black;">
						<i class="mdi mdi-calendar-text"></i>
					</a>
				</th>
				<th width="1%">
					<a class="help-tooltip" href="#" data-toggle="tooltip"
					   data-placement="left" title="Images exist" style="color:black;">
						<i class="mdi mdi-image-album"></i>
					</a>
				</th>
				<th width="1%">
					<a class="help-tooltip" href="#" data-toggle="tooltip"
					   data-placement="left" title="Action button enabled" style="color:black;">
						<i class="mdi mdi-play-circle"></i>
					</a>
				</th>
				<th width="1%">
					<a class="help-tooltip" href="#" data-toggle="tooltip"
					   data-placement="left" title="Ready to AirShr" style="color:black;">
						<i class="mdi mdi-check"></i>
					</a>
				</th>
			</tr>
		</thead>
	</table>

	<table width="100%" id="content-news-list-table" class="content-table display table-hover">
		<thead>
			<tr>
				<th>WHO</th>
				<th>WHAT</th>
				<th width="10%">START<br/>DATE</th>
				<th width="10%">END<br/>DATE</th>
				<th width="10%">START<br/>TIME</th>
				<th width="10%">END<br/>TIME</th>
				<th width="1%">
					<a class="help-tooltip" href="#" data-toggle="tooltip"
					   data-placement="left" title="Text filled in" style="color:black;">
						<i class="mdi mdi-calendar-text"></i>
					</a>
				</th>
				<th width="1%">
					<a class="help-tooltip" href="#" data-toggle="tooltip"
					   data-placement="left" title="Images exist" style="color:black;">
						<i class="mdi mdi-image-album"></i>
					</a>
				</th>
				<th width="1%">
					<a class="help-tooltip" href="#" data-toggle="tooltip"
					   data-placement="left" title="Action button enabled" style="color:black;">
						<i class="mdi mdi-play-circle"></i>
					</a>
				</th>
				<th width="1%">
					<a class="help-tooltip" href="#" data-toggle="tooltip"
					   data-placement="left" title="Ready to AirShr" style="color:black;">
						<i class="mdi mdi-check"></i>
					</a>
				</th>
			</tr>
		</thead>
	</table>
	
	<table width="100%" id="material-list-table" class="content-table display">
		<thead>
			<tr>
				<th>CLIENT</th>
				<th>PRODUCT</th>
				<th width="10%">ATB/ORDER #</th>
				<th width="10%">LINE #</th>
				<th width="10%">VERSION</th>
				<th width="10%">CREATED TIME</th>
				<th width="1%"><i class="mdi mdi-check"></i></th>
			</tr>
		</thead>
	</table>
	
	<table width="100%" id="material-ad-list-table" class="content-table display">
		<thead>
			<tr>
				<th><i class="mdi mdi-plus" id="material_add_ad_btn"></i></th>
				<th>REC/<br/>LIVE</th>
				<th>STARTS</th>
				<th>ENDS</th>
				<th>INSTRUCTIONS</th>
				<th>HEADLINE</th>
				<th>KEY #</th>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
				<th>
					<a class="help-tooltip" href="#" data-toggle="tooltip"
					   data-placement="left" title="Duration" style="color:black;">
						<i class="mdi mdi-alarm"></i>
					</a>
				</th>
				<th><span style="font-size: 20px">%</span></th>
				<th>
					<a class="help-tooltip" href="#" data-toggle="tooltip"
					   data-placement="left" title="Audio exists" style="color:black;">
						<i class="mdi mdi-microphone"></i>
					</a>
				</th>
				<th>
					<a class="help-tooltip" href="#" data-toggle="tooltip"
					   data-placement="left" title="Text filled in" style="color:black;">
						<i class="mdi mdi-calendar-text"></i>
					</a>
				</th>
				<th>
					<a class="help-tooltip" href="#" data-toggle="tooltip"
					   data-placement="left" title="Ready to AirShr" style="color:black;">
						<i class="mdi mdi-check"></i>
					</a>
				</th>
				<th>&nbsp;&nbsp;&nbsp;</th>
			</tr>
		</thead>
	</table>
	
	<table width="100%" id="audio-upload-list-table" class="content-table display">
		<thead>
			<tr>
				<th>&nbsp;</th>
				<th>FILENAME</th>
				<th>Entered</th>
				<th>WHO</th>
				<th>WHAT</th>
				<th>KEY #</th>
				<th>
					<a class="help-tooltip" href="#" data-toggle="tooltip"
					   data-placement="left" title="Text filled in" style="color:black;">
						<i class="mdi mdi-calendar-text"></i>
					</a>
				</th>
				<th>
					<a class="help-tooltip" href="#" data-toggle="tooltip"
					   data-placement="left" title="Images exist" style="color:black;">
						<i class="mdi mdi-image-album"></i>
					</a>
				</th>
				<th>
					<a class="help-tooltip" href="#" data-toggle="tooltip"
					   data-placement="left" title="Action button enabled" style="color:black;">
						<i class="mdi mdi-play-circle"></i>
					</a>
				</th>
				<th>
					<a class="help-tooltip" href="#" data-toggle="tooltip"
					   data-placement="left" title="Ready to AirShr" style="color:black;">
						<i class="mdi mdi-check"></i>
					</a>
				</th>
				<th>&nbsp;</th>
			</tr>
		</thead>
	</table>
	
	<table width="100%" id="dailylog-tag-list-table" class="content-table display" tabindex="1">
		<thead>
			<tr>
				<th width="10%">TIME</th>
				<th width="1%">&nbsp;</th>
				<th>WHO</th>
				<th>WHAT</th>
				<th>ZETTA ID #</th>
				<th>KEY #</th>
				<th>
					<a class="help-tooltip" href="#" data-toggle="tooltip"
					   data-placement="left" title="Duration" style="color:black;">
						<i class="mdi mdi-alarm"></i>
					</a>
				</th>
				<th>
					<a class="help-tooltip" href="#" data-toggle="tooltip"
					   data-placement="left" title="Audio exists" style="color:black;">
						<i class="mdi mdi-microphone"></i>
					</a>
				</th>
				<th>
					<a class="help-tooltip" href="#" data-toggle="tooltip"
					   data-placement="left" title="Text filled in" style="color:black;">
						<i class="mdi mdi-calendar-text"></i>
					</a>
				</th>
				<th>
					<a class="help-tooltip" href="#" data-toggle="tooltip"
					   data-placement="left" title="Images/Logo exists" style="color:black;">
						<i class="mdi mdi-image-album"></i>
					</a>
				</th>
				<th>
					<a class="help-tooltip" href="#" data-toggle="tooltip"
					   data-placement="left" title="Action button enabled" style="color:black;">
						<i class="mdi mdi-play-circle"></i>
					</a>
				</th>
				<th>
					<a class="help-tooltip" href="#" data-toggle="tooltip"
					   data-placement="left" title="Ready to AirShr" style="color:black;">
						<i class="mdi mdi-check"></i>
					</a>
				</th>
			</tr>
		</thead>
	</table>
	
	<table width="100%" id="clients-list-table" class="content-table display" tabindex="1">
		<thead>
			<tr>
				<th>CLIENT COMPANY</th>
				<th>TRADING NAME</th>
				<th>PRODUCT/INDUSTRY</th>
				<th>CLIENT EXECUTIVE</th>
				<th>
					<a class="help-tooltip" href="#" data-toggle="tooltip"
					   data-placement="left" title="Text filled in" style="color:black;">
						<i class="mdi mdi-calendar-text"></i>
					</a>
				</th>

				<th>
					<a class="help-tooltip" href="#" data-toggle="tooltip"
					   data-placement="left" title="Logo exists" style="color:black;">
						<i class="mdi mdi-image-filter-tilt-shift"></i>
					</a>
				</th>
				<th>
					<a class="help-tooltip" href="#" data-toggle="tooltip"
					   data-placement="left" title="Images exist" style="color:black;">
						<i class="mdi mdi-image-album"></i>
					</a>
				</th>
				<th>
					<a class="help-tooltip" href="#" data-toggle="tooltip"
					   data-placement="left" title="Ready to AirShr" style="color:black;">
						<i class="mdi mdi-check"></i>
					</a>
				</th>
			</tr>
		</thead>
	</table>
	
	<div class="hide loading" id="content_table_loader">
		<img src="/img/ajax-loader.gif" class="loader-img">
	</div>
	
</div>

<div class="content-modal-overlay" id="image_editor_overlay" style="display: none">

	<div class="content-sub-header">
		<h1 class="content-sub-header-title">Image Editor</h1>
		<div class="content-sub-header-actions">
			<a class="btn-action" title="Confirm" id="content_btn_img_confirm"><i class="mdi mdi-check"></i></a>
			<a class="btn-action" title="Cancel" id="content_btn_img_cancel"><i class="mdi mdi-close"></i></a>
		</div>
	</div>
	
	<div class="container-fluid image-editor-content">
		<div class="image-editor-main-wrapper">
			<div class="image-editor-main-area">
				<div id="image-editor-cropper-div">
					<img id="image-editor-cropper-img" />
				</div>
				<div id="image-editor-zoom-container">
					<input id="image-editor-zoom-slider" class="image-editor-slider" type="text" data-slider-min="10" data-slider-max="300" data-slider-step="10" data-slider-value="100" data-slider-orientation="vertical"/>
				</div>
			</div>
			
			<p class="success-green image-status-icon"><i class="mdi mdi-checkbox-marked-circle"></i></p>
			<p class="image-status-description success-green">Image resolution is OK</p>
			<p>Uploaded image is <span class="image-information">900w * 700h (2:1)</span></p>
			<p>Minimum required is 800w * 600h (4:3)</p>
		
		</div>
		<div class="content-air-preview">
			@include('airshrconnect.mobilepreview', ['mode' => 'image_editor_preview', 'sliderContainerID' => '', 'displayFormOption' => 'false', 'displayFormCloseOption' => 'false'])
		</div>
	</div>
	
</div>

<div class="content-modal-sidebar right-sidebar hidden" id="mobilepreview_sidebar">

	@include('airshrconnect.mobilepreview', ['mode' => 'tag_preview', 'sliderContainerID' => 'mobilepreview_slider_container', 'displayFormOption' => 'true', 'displayFormCloseOption' => 'true'])

</div>


<div class="content-modal-sidebar right-sidebar hidden" id="onair_sidebar">

	<div class="sidebar_header">
		<div class="sidebar_header_title">Select Talk Segments to update</div>
		<div class="sidebar_header_action">
			<a class="btn-action" title="Confirm" id="talk_assign_yes_btn"><i class="mdi mdi-check"></i></a>
			<a class="btn-action" title="Close" id="talk_assign_no_btn"><i class="mdi mdi-close"></i></a>
		</div>
		<div class="clear"></div>
	</div>
	<div class="content-air-tag-container">
	
		
	</div>
	
</div>



@endsection


@section('scripts')



@parent 

<script>
<!--

	var clientCompanyList = <?php echo json_encode($client_list); ?>; 
	var clientProductList =  <?php echo json_encode($product_list); ?>; 

	var durationValuesList = new Array();
	@foreach ($ad_duration_list as $key => $val)
		durationValuesList.push({value: {{$key}}, text: '{{$val}}'});
	@endforeach

	var adPercentValuesList = new Array();
	@foreach ($ad_percent_list as $key => $val)
		adPercentValuesList.push({value: {{$key}}, text: '{{$val}}'});
	@endforeach

	var talkSubType = new Array();
	@foreach ($content_sub_type_list[4] as $key => $val)
		talkSubType.push({value: {{$key}}, text: '{{$val}}'});
	@endforeach


	var WebSocketURL = '{{ $WebSocketURL }}';

	var StationInfo = <?php echo $station_info; ?>;


	var initialFormMode = '{{ $initialFormMode }}';
	var initialContentID = {{ $initialContentID }};
	var initialContentTypeID = {{ $initialContentTypeID }};
	
	var prevPage = '{{ $prevPage }}';
	
-->
</script>

<script src="/js/datatables-1.10.7/js/jquery.dataTables.min.js"></script>
<!-- <script src="/js/datatable-bootstrap/dataTables.bootstrap.min.js"></script> -->
<script src="/js/bootstrap-editable/js/bootstrap-editable.min.js"></script>
<script src="/js/typeaheadjs.js"></script>
<script src="//cdn.datatables.net/plug-ins/1.10.7/sorting/datetime-moment.js"></script>
<script src="/js/jcrop/js/Jcrop.js"></script>
<script src="/js/bootstrap.slider/bootstrap-slider.min.js"></script>
<script type="text/javascript" src="/js/bootstrap.progressbar/bootstrap-progressbar.min.js"></script>

<script src="/js/image_editor.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>
<script src="/js/mobilepreview.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>
<script src="/js/content.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>
{{--<script src="/js/content.js"></script>--}}

<script src="/js/websocket.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>
<script src="/js/onair.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>

<script>
	// Highlighting and up down navigation in client info and daily log table
	var currentRow = 0;
	var currentClientRow=0;
	$('#clients-list-table').bind('keydown', function(e) {
		if(e.keyCode == 40) {
			e.preventDefault();
			currentClientRow++;
			$('#clients-list-table tr[role="row"].selected').removeClass('selected');
			$('#clients-list-table tr[role="row"]:nth-child('+currentClientRow+')').trigger('click').addClass('selected');
		} else if(e.keyCode == 38) {
			e.preventDefault();
			if(currentClientRow <= 1) return;
			currentClientRow--;
			$('#clients-list-table tr[role="row"].selected').removeClass('selected');
			$('#clients-list-table tr[role="row"]:nth-child('+currentClientRow+')').trigger('click').addClass('selected');
		}
	});

	$('#dailylog-tag-list-table').bind('keydown', function(e) {
		if(e.keyCode == 40) {
			e.preventDefault();
			currentRow++;
			$('#dailylog-tag-list-table tr[role="row"].selected').removeClass('selected');
			$('#dailylog-tag-list-table tr[role="row"]:nth-child('+currentRow+')').trigger('click').addClass('selected');
		} else if(e.keyCode == 38) {
			e.preventDefault();
			if(currentRow <= 1) return;
			currentRow--;
			$('#dailylog-tag-list-table tr[role="row"].selected').removeClass('selected');
			$('#dailylog-tag-list-table tr[role="row"]:nth-child('+currentRow+')').trigger('click').addClass('selected');
		}
	});
	$(function() {
		$('#dailylog-tag-list-table').focus();
	});
	$(document).ready(function() {
		$('[data-toggle="tooltip"]').tooltip();
		$('#dailylog-tag-list-table').on('click', 'tbody tr', (function(e) {
			currentRow = ($(this).index()+1);
			$('#dailylog-tag-list-table tr[role="row"].selected').removeClass('selected');
			$('#dailylog-tag-list-table tr[role="row"]:nth-child('+currentRow+')').addClass('selected');
			$scrollBody = $('#dailylog-tag-list-table_wrapper .dataTables_scrollBody');
			$scrollBody.animate({
				scrollTop: $(this).offset().top - ($scrollBody.offset().top - $scrollBody.scrollTop())
			});
		}));
		$('#clients-list-table').on('click', 'tbody tr', (function(e) {
			currentClientRow = ($(this).index()+1);
			$('#clients-list-table tr[role="row"].selected').removeClass('selected');
			$('#clients-list-table tr[role="row"]:nth-child('+currentClientRow+')').addClass('selected');
			$scrollBody = $('#clients-list-table_wrapper .dataTables_scrollBody');
			$scrollBody.animate({
				scrollTop: $(this).offset().top - ($scrollBody.offset().top - $scrollBody.scrollTop())
			});
		}));
	});

//	$(window).click(function(e) {
//		var nearest = $.nearest({x: e.pageX, y: e.pageY}, '#dailylog-tag-list-table tr[role="row"]');
////		if(currentRow > 0) {
////			currentRow = nearest.index() + 1;
////		}
////		console.log(currentRow);
//		nearest.addClass('selected');
//	});
</script>

<script>
	<!--
	var previewType = 'preview';
	-->
</script>

<script src="/js/bootstrap-modal-popover/bootstrap-modal-popover.js"></script>
<script src="/js/mobileeditor.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>

@endsection